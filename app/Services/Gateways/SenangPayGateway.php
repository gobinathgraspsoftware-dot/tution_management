<?php

namespace App\Services\Gateways;

use App\Models\Invoice;
use App\Models\PaymentGatewayConfig;

class SenangPayGateway extends BaseGateway
{
    /**
     * Constructor
     */
    public function __construct(PaymentGatewayConfig $config)
    {
        parent::__construct($config);
    }

    /**
     * Get the API base URL
     */
    public function getBaseUrl(): string
    {
        if ($this->isSandbox) {
            return config('payment_gateways.gateways.senangpay.sandbox_url', 'https://sandbox.senangpay.my');
        }

        return config('payment_gateways.gateways.senangpay.production_url', 'https://app.senangpay.my');
    }

    /**
     * Create a payment
     */
    public function createPayment(Invoice $invoice, array $customerData): array
    {
        $merchantId = $this->getMerchantId();
        $secretKey = $this->getApiSecret();

        $orderId = $invoice->invoice_number . '_' . time();
        $amount = number_format($invoice->balance, 2, '.', '');
        $name = $customerData['name'] ?? $invoice->student->user->name;
        $email = $customerData['email'] ?? $invoice->student->user->email;
        $phone = $this->formatPhone($customerData['phone'] ?? $invoice->student->parent?->whatsapp_number);

        // Generate hash for verification
        $hash = $this->generateHash($secretKey, $merchantId, $orderId, $amount);

        // Build payment URL with parameters
        $paymentUrl = $this->getBaseUrl() . '/payment/' . $merchantId;

        $params = [
            'detail' => $this->buildDescription($invoice),
            'amount' => $amount,
            'order_id' => $orderId,
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'hash' => $hash,
        ];

        $fullPaymentUrl = $paymentUrl . '?' . http_build_query($params);

        $this->log('Payment URL generated', [
            'order_id' => $orderId,
            'amount' => $amount,
        ]);

        return [
            'success' => true,
            'transaction_id' => $orderId,
            'order_id' => $orderId,
            'payment_url' => $fullPaymentUrl,
            'status' => 'pending',
            'callback_url' => route('payment.callback', ['gateway' => 'senangpay']),
            'return_url' => route('payment.callback', ['gateway' => 'senangpay']),
        ];
    }

    /**
     * Get payment URL for redirect
     */
    public function getPaymentUrl(string $billCode): string
    {
        return $this->getBaseUrl() . '/payment/' . $this->getMerchantId();
    }

    /**
     * Verify callback signature
     */
    public function verifyCallback(array $data): bool
    {
        $statusId = $data['status_id'] ?? '';
        $orderId = $data['order_id'] ?? '';
        $transactionId = $data['transaction_id'] ?? '';
        $msg = $data['msg'] ?? '';
        $hash = $data['hash'] ?? '';

        // Generate expected hash
        $secretKey = $this->getApiSecret();
        $expectedHash = md5($secretKey . $statusId . $orderId . $transactionId . $msg);

        $isValid = $hash === $expectedHash;

        if (!$isValid) {
            $this->log('Invalid callback hash', [
                'expected' => $expectedHash,
                'received' => $hash,
            ], 'warning');
        }

        return $isValid;
    }

    /**
     * Process callback data
     */
    public function processCallback(array $data): array
    {
        $statusId = $data['status_id'] ?? null;
        $orderId = $data['order_id'] ?? null;
        $transactionId = $data['transaction_id'] ?? null;
        $msg = $data['msg'] ?? '';

        // Map SenangPay status to our status
        // 1 = Success, 0 = Failed
        $statusMap = [
            '1' => 'completed',
            '0' => 'failed',
        ];

        $status = $statusMap[$statusId] ?? 'failed';

        $this->log('Processing callback', [
            'order_id' => $orderId,
            'status_id' => $statusId,
            'status' => $status,
        ]);

        return [
            'success' => $status === 'completed',
            'status' => $status,
            'transaction_id' => $orderId,
            'gateway_transaction_id' => $transactionId,
            'reference_number' => $transactionId,
            'gateway_status' => $statusId,
            'message' => $msg,
            'raw_data' => $data,
        ];
    }

    /**
     * Get transaction status from gateway
     */
    public function getTransactionStatus(string $transactionId): array
    {
        // SenangPay uses order_id for status check
        $response = $this->makeRequest('GET', '/apiv1/query_order_status', [
            'merchant_id' => $this->getMerchantId(),
            'order_id' => $transactionId,
            'hash' => $this->generateStatusQueryHash($transactionId),
        ]);

        if (!$response['success']) {
            return [
                'success' => false,
                'status' => 'unknown',
                'error' => 'Failed to retrieve transaction status',
            ];
        }

        $data = $response['data'];
        $status = $data['status'] ?? 'unknown';

        $statusMap = [
            'paid' => 'completed',
            'pending' => 'pending',
            'failed' => 'failed',
        ];

        return [
            'success' => true,
            'status' => $statusMap[$status] ?? 'unknown',
            'gateway_status' => $status,
            'amount' => $data['amount'] ?? 0,
            'transaction_id' => $data['fpx_transaction_id'] ?? null,
            'data' => $data,
        ];
    }

    /**
     * Generate hash for payment request
     */
    protected function generateHash(string $secretKey, string $merchantId, string $orderId, string $amount): string
    {
        return md5($secretKey . urldecode($merchantId) . urldecode($orderId) . urldecode($amount));
    }

    /**
     * Generate hash for status query
     */
    protected function generateStatusQueryHash(string $orderId): string
    {
        return md5($this->getApiSecret() . $this->getMerchantId() . $orderId);
    }

    /**
     * Format phone number for SenangPay
     */
    protected function formatPhone(?string $phone): string
    {
        if (!$phone) {
            return '';
        }

        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Remove leading 60 or +60, and add 60 prefix
        if (str_starts_with($phone, '60')) {
            return $phone;
        }

        // Remove leading 0 and add 60
        if (str_starts_with($phone, '0')) {
            $phone = substr($phone, 1);
        }

        return '60' . $phone;
    }

    /**
     * Build payment description
     */
    protected function buildDescription(Invoice $invoice): string
    {
        $description = "Invoice #{$invoice->invoice_number}";

        if ($invoice->enrollment && $invoice->enrollment->package) {
            $description .= " - {$invoice->enrollment->package->name}";
        }

        return substr($description, 0, 100);
    }

    /**
     * Get recurring payment URL
     */
    public function createRecurringPayment(Invoice $invoice, array $customerData, string $recurringId): array
    {
        $merchantId = $this->getMerchantId();
        $secretKey = $this->getApiSecret();

        $orderId = $invoice->invoice_number . '_' . time();
        $amount = number_format($invoice->balance, 2, '.', '');

        $hash = md5($secretKey . $merchantId . $orderId . $amount . $recurringId);

        $paymentUrl = $this->getBaseUrl() . '/recurring/' . $merchantId;

        $params = [
            'detail' => $this->buildDescription($invoice),
            'amount' => $amount,
            'order_id' => $orderId,
            'name' => $customerData['name'] ?? $invoice->student->user->name,
            'email' => $customerData['email'] ?? $invoice->student->user->email,
            'phone' => $this->formatPhone($customerData['phone'] ?? null),
            'recurring_id' => $recurringId,
            'hash' => $hash,
        ];

        return [
            'success' => true,
            'transaction_id' => $orderId,
            'payment_url' => $paymentUrl . '?' . http_build_query($params),
            'status' => 'pending',
        ];
    }

    /**
     * Cancel recurring payment
     */
    public function cancelRecurringPayment(string $recurringId): array
    {
        $response = $this->makeRequest('POST', '/apiv1/cancel_recurring', [
            'merchant_id' => $this->getMerchantId(),
            'recurring_id' => $recurringId,
            'hash' => md5($this->getApiSecret() . $this->getMerchantId() . $recurringId),
        ]);

        return [
            'success' => $response['success'],
            'data' => $response['data'] ?? null,
            'error' => $response['error'] ?? null,
        ];
    }
}
