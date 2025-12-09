<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentGatewayConfig;
use App\Models\PaymentGatewayTransaction;
use App\Models\ActivityLog;
use App\Services\Gateways\BaseGateway;
use App\Services\Gateways\ToyyibPayGateway;
use App\Services\Gateways\SenangPayGateway;
use App\Services\Gateways\BillplzGateway;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PaymentGatewayService
{
    /**
     * Gateway instances cache
     */
    protected array $gateways = [];

    /**
     * Gateway class mapping
     */
    protected array $gatewayClasses = [
        'toyyibpay' => ToyyibPayGateway::class,
        'senangpay' => SenangPayGateway::class,
        'billplz' => BillplzGateway::class,
    ];

    /**
     * Get gateway instance by name
     */
    public function getGateway(string $gatewayName): ?BaseGateway
    {
        if (isset($this->gateways[$gatewayName])) {
            return $this->gateways[$gatewayName];
        }

        $config = PaymentGatewayConfig::where('gateway_name', $gatewayName)
            ->where('is_active', true)
            ->first();

        if (!$config) {
            return null;
        }

        return $this->createGatewayInstance($config);
    }

    /**
     * Get default gateway
     */
    public function getDefaultGateway(): ?BaseGateway
    {
        $defaultGateway = config('payment_gateways.default', 'toyyibpay');

        $gateway = $this->getGateway($defaultGateway);

        if (!$gateway) {
            // Try to get any active gateway
            $config = PaymentGatewayConfig::where('is_active', true)->first();
            if ($config) {
                $gateway = $this->createGatewayInstance($config);
            }
        }

        return $gateway;
    }

    /**
     * Get all active gateways
     */
    public function getActiveGateways(): array
    {
        $configs = PaymentGatewayConfig::where('is_active', true)->get();
        $gateways = [];

        foreach ($configs as $config) {
            $gateway = $this->createGatewayInstance($config);
            if ($gateway) {
                $gateways[$config->gateway_name] = [
                    'name' => $gateway->getDisplayName(),
                    'gateway' => $gateway,
                    'config' => $config,
                ];
            }
        }

        return $gateways;
    }

    /**
     * Create gateway instance from config
     */
    protected function createGatewayInstance(PaymentGatewayConfig $config): ?BaseGateway
    {
        $gatewayName = $config->gateway_name;

        if (!isset($this->gatewayClasses[$gatewayName])) {
            Log::warning("Unknown gateway type: {$gatewayName}");
            return null;
        }

        $gatewayClass = $this->gatewayClasses[$gatewayName];
        $gateway = new $gatewayClass($config);

        $this->gateways[$gatewayName] = $gateway;

        return $gateway;
    }

    /**
     * Initiate online payment for an invoice
     */
    public function initiatePayment(Invoice $invoice, string $gatewayName = null, array $customerData = []): array
    {
        try {
            // Get gateway
            $gateway = $gatewayName
                ? $this->getGateway($gatewayName)
                : $this->getDefaultGateway();

            if (!$gateway) {
                return [
                    'success' => false,
                    'error' => 'No active payment gateway available',
                ];
            }

            // Validate invoice can receive payment
            if (!$invoice->canReceivePayment()) {
                return [
                    'success' => false,
                    'error' => 'This invoice cannot receive payments',
                ];
            }

            // Check for existing pending transaction
            $existingTransaction = PaymentGatewayTransaction::where('invoice_id', $invoice->id)
                ->where('status', 'pending')
                ->where('created_at', '>', now()->subMinutes(30))
                ->first();

            if ($existingTransaction) {
                // Return existing payment URL if still valid
                return [
                    'success' => true,
                    'transaction' => $existingTransaction,
                    'payment_url' => $gateway->getPaymentUrl($existingTransaction->transaction_id),
                    'existing' => true,
                ];
            }

            // Create payment through gateway
            $response = $gateway->createPayment($invoice, $customerData);

            if (!$response['success']) {
                return [
                    'success' => false,
                    'error' => $response['error'] ?? 'Failed to create payment',
                ];
            }

            // Create transaction record
            $transaction = $gateway->createTransaction($invoice, $response, $customerData);

            // Log activity
            $this->logActivity('payment_initiated', $transaction, [
                'gateway' => $gatewayName ?? config('payment_gateways.default'),
                'amount' => $invoice->balance,
            ]);

            return [
                'success' => true,
                'transaction' => $transaction,
                'payment_url' => $response['payment_url'],
                'gateway' => $gatewayName ?? config('payment_gateways.default'),
            ];

        } catch (\Exception $e) {
            Log::error('Payment initiation failed', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Payment initiation failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Process payment callback
     */
    public function processCallback(string $gatewayName, array $data): array
    {
        try {
            $gateway = $this->getGateway($gatewayName);

            if (!$gateway) {
                return [
                    'success' => false,
                    'error' => 'Gateway not found or inactive',
                ];
            }

            // Verify callback signature
            if (!$gateway->verifyCallback($data)) {
                Log::warning("Invalid callback signature for {$gatewayName}", $data);
                return [
                    'success' => false,
                    'error' => 'Invalid callback signature',
                ];
            }

            // Process callback data
            $result = $gateway->processCallback($data);

            // Find transaction
            $transaction = PaymentGatewayTransaction::where('transaction_id', $result['transaction_id'])
                ->orWhere(function($query) use ($result) {
                    if (isset($result['gateway_transaction_id'])) {
                        $query->where('transaction_id', $result['gateway_transaction_id']);
                    }
                })
                ->first();

            if (!$transaction) {
                Log::warning("Transaction not found for callback", $result);
                return [
                    'success' => false,
                    'error' => 'Transaction not found',
                ];
            }

            // Update transaction
            $transaction = $gateway->updateTransaction($transaction, $result['status'], [
                'gateway_status' => $result['gateway_status'],
                'gateway_transaction_id' => $result['gateway_transaction_id'] ?? null,
                'callback_data' => $result['raw_data'],
            ]);

            // If payment successful, create payment record and update invoice
            if ($result['success'] && $result['status'] === 'completed') {
                $this->processSuccessfulPayment($transaction, $result);
            }

            // Log activity
            $this->logActivity('payment_callback_processed', $transaction, [
                'gateway' => $gatewayName,
                'status' => $result['status'],
            ]);

            return [
                'success' => true,
                'transaction' => $transaction,
                'result' => $result,
            ];

        } catch (\Exception $e) {
            Log::error('Callback processing failed', [
                'gateway' => $gatewayName,
                'error' => $e->getMessage(),
                'data' => $data,
            ]);

            return [
                'success' => false,
                'error' => 'Callback processing failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Process successful payment
     */
    protected function processSuccessfulPayment(PaymentGatewayTransaction $transaction, array $result): void
    {
        DB::transaction(function () use ($transaction, $result) {
            $invoice = $transaction->invoice;

            // Create payment record
            $payment = Payment::create([
                'payment_number' => Payment::generatePaymentNumber(),
                'invoice_id' => $invoice->id,
                'student_id' => $invoice->student_id,
                'amount' => $transaction->amount,
                'payment_method' => 'online_gateway',
                'payment_date' => now(),
                'reference_number' => $result['reference_number'] ?? $transaction->transaction_id,
                'gateway_transaction_id' => $result['gateway_transaction_id'] ?? $transaction->transaction_id,
                'gateway_response' => $result['raw_data'] ?? null,
                'status' => 'completed',
                'notes' => "Online payment via {$transaction->gatewayConfig->gateway_name}",
            ]);

            // Link payment to transaction
            $transaction->update(['payment_id' => $payment->id]);

            // Update invoice
            $invoice->recordPayment($transaction->amount);

            // Send notification (you can integrate with NotificationService)
            $this->sendPaymentNotification($payment);
        });
    }

    /**
     * Send payment success notification
     */
    protected function sendPaymentNotification(Payment $payment): void
    {
        // This can be integrated with your NotificationService
        // For now, we'll just log it
        Log::info('Payment notification should be sent', [
            'payment_id' => $payment->id,
            'amount' => $payment->amount,
        ]);
    }

    /**
     * Get transaction by ID
     */
    public function getTransaction(string $transactionId): ?PaymentGatewayTransaction
    {
        return PaymentGatewayTransaction::where('transaction_id', $transactionId)->first();
    }

    /**
     * Refresh transaction status from gateway
     */
    public function refreshTransactionStatus(PaymentGatewayTransaction $transaction): array
    {
        $config = $transaction->gatewayConfig;
        $gateway = $this->createGatewayInstance($config);

        if (!$gateway) {
            return [
                'success' => false,
                'error' => 'Gateway not available',
            ];
        }

        $status = $gateway->getTransactionStatus($transaction->transaction_id);

        if ($status['success']) {
            $transaction->update([
                'status' => $status['status'],
                'gateway_status' => $status['gateway_status'],
                'gateway_response' => array_merge(
                    $transaction->gateway_response ?? [],
                    ['status_check' => $status]
                ),
            ]);

            // Process if completed
            if ($status['status'] === 'completed' && $transaction->status !== 'completed') {
                $this->processSuccessfulPayment($transaction, $status);
            }
        }

        return $status;
    }

    /**
     * Cancel pending transaction
     */
    public function cancelTransaction(PaymentGatewayTransaction $transaction, string $reason = null): bool
    {
        if ($transaction->status !== 'pending') {
            return false;
        }

        $transaction->update([
            'status' => 'cancelled',
            'gateway_response' => array_merge(
                $transaction->gateway_response ?? [],
                ['cancellation_reason' => $reason, 'cancelled_at' => now()->toIso8601String()]
            ),
        ]);

        return true;
    }

    /**
     * Get gateway statistics
     */
    public function getStatistics(string $gatewayName = null, Carbon $startDate = null, Carbon $endDate = null): array
    {
        $query = PaymentGatewayTransaction::query();

        if ($gatewayName) {
            $config = PaymentGatewayConfig::where('gateway_name', $gatewayName)->first();
            if ($config) {
                $query->where('gateway_config_id', $config->id);
            }
        }

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        $transactions = $query->get();

        return [
            'total_transactions' => $transactions->count(),
            'completed' => $transactions->where('status', 'completed')->count(),
            'pending' => $transactions->where('status', 'pending')->count(),
            'failed' => $transactions->where('status', 'failed')->count(),
            'total_amount' => $transactions->where('status', 'completed')->sum('amount'),
            'average_amount' => $transactions->where('status', 'completed')->avg('amount') ?? 0,
            'success_rate' => $transactions->count() > 0
                ? round(($transactions->where('status', 'completed')->count() / $transactions->count()) * 100, 2)
                : 0,
        ];
    }

    /**
     * Calculate gateway fees for display
     */
    public function calculateFees(float $amount, string $gatewayName = null): array
    {
        $gateway = $gatewayName ? $this->getGateway($gatewayName) : $this->getDefaultGateway();

        if (!$gateway) {
            return [
                'amount' => $amount,
                'fee' => 0,
                'total' => $amount,
            ];
        }

        return $gateway->calculateFees($amount);
    }

    /**
     * Log payment activity
     */
    protected function logActivity(string $action, PaymentGatewayTransaction $transaction, array $data = []): void
    {
        ActivityLog::create([
            'user_id' => auth()->id(),
            'log_type' => 'payment_gateway',
            'model_type' => PaymentGatewayTransaction::class,
            'model_id' => $transaction->id,
            'action' => $action,
            'description' => "Online payment {$action} - Transaction #{$transaction->transaction_id}",
            'changes' => json_encode(array_merge([
                'transaction_id' => $transaction->transaction_id,
                'amount' => $transaction->amount,
                'status' => $transaction->status,
            ], $data)),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Auto-cancel expired pending transactions
     */
    public function cancelExpiredTransactions(): int
    {
        $expiryMinutes = config('payment_gateways.transaction.expiry_minutes', 30);

        $expiredTransactions = PaymentGatewayTransaction::where('status', 'pending')
            ->where('created_at', '<', now()->subMinutes($expiryMinutes))
            ->get();

        $count = 0;

        foreach ($expiredTransactions as $transaction) {
            $this->cancelTransaction($transaction, 'Auto-cancelled due to expiry');
            $count++;
        }

        if ($count > 0) {
            Log::info("Auto-cancelled {$count} expired payment transactions");
        }

        return $count;
    }

    /**
     * Get available gateway options for display
     */
    public function getGatewayOptions(): array
    {
        $activeGateways = $this->getActiveGateways();
        $options = [];

        foreach ($activeGateways as $name => $data) {
            $options[] = [
                'value' => $name,
                'label' => $data['name'],
                'description' => config("payment_gateways.gateways.{$name}.description"),
                'methods' => config("payment_gateways.gateways.{$name}.supported_methods", []),
            ];
        }

        return $options;
    }
}
