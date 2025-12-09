<?php

namespace App\Services\Gateways;

use App\Models\Invoice;
use App\Models\PaymentGatewayConfig;

class ToyyibPayGateway extends BaseGateway
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
            return config('payment_gateways.gateways.toyyibpay.sandbox_url', 'https://dev.toyyibpay.com');
        }

        return config('payment_gateways.gateways.toyyibpay.production_url', 'https://toyyibpay.com');
    }

    /**
     * Create a bill/payment
     */
    public function createPayment(Invoice $invoice, array $customerData): array
    {
        $billData = [
            'userSecretKey' => $this->getApiSecret(),
            'categoryCode' => $this->getConfiguration('category_code'),
            'billName' => 'Invoice #' . $invoice->invoice_number,
            'billDescription' => $this->buildBillDescription($invoice),
            'billPriceSetting' => 1, // Fixed amount
            'billPayorInfo' => 1, // Require payer info
            'billAmount' => $this->formatAmount($invoice->balance),
            'billReturnUrl' => route('payment.callback', ['gateway' => 'toyyibpay']),
            'billCallbackUrl' => route('payment.webhook', ['gateway' => 'toyyibpay']),
            'billExternalReferenceNo' => $invoice->invoice_number,
            'billTo' => $customerData['name'] ?? $invoice->student->user->name,
            'billEmail' => $customerData['email'] ?? $invoice->student->user->email,
            'billPhone' => $this->formatPhone($customerData['phone'] ?? $invoice->student->parent?->whatsapp_number),
            'billSplitPayment' => 0,
            'billSplitPaymentArgs' => '',
            'billPaymentChannel' => $this->getConfiguration('payment_channel', '0'), // 0=FPX, 1=Card, 2=Both
            'billContentEmail' => $this->buildEmailContent($invoice),
            'billChargeToCustomer' => $this->getConfiguration('charge_to_customer', 1),
            'billExpiryDate' => now()->addMinutes(config('payment_gateways.transaction.expiry_minutes', 30))->format('d-m-Y H:i:s'),
            'billExpiryDays' => 1,
        ];

        $response = $this->makeRequest('POST', '/index.php/api/createBill', $billData);

        if (!$response['success']) {
            $this->log('Failed to create bill', ['error' => $response['error']], 'error');
            return [
                'success' => false,
                'error' => $response['error'] ?? 'Failed to create payment bill',
            ];
        }

        $data = $response['data'];

        // ToyyibPay returns array with bill code
        if (is_array($data) && isset($data[0]['BillCode'])) {
            $billCode = $data[0]['BillCode'];

            $this->log('Bill created successfully', ['bill_code' => $billCode]);

            return [
                'success' => true,
                'transaction_id' => $billCode,
                'bill_code' => $billCode,
                'payment_url' => $this->getPaymentUrl($billCode),
                'status' => 'pending',
                'callback_url' => route('payment.callback', ['gateway' => 'toyyibpay']),
                'return_url' => route('payment.callback', ['gateway' => 'toyyibpay']),
            ];
        }

        $this->log('Unexpected response format', ['response' => $data], 'error');

        return [
            'success' => false,
            'error' => 'Unexpected response from payment gateway',
        ];
    }

    /**
     * Get payment URL for redirect
     */
    public function getPaymentUrl(string $billCode): string
    {
        return $this->getBaseUrl() . '/' . $billCode;
    }

    /**
     * Verify callback signature
     */
    public function verifyCallback(array $data): bool
    {
        // ToyyibPay callback verification
        // In production, you should verify the callback is from ToyyibPay
        // by checking IP whitelist or using webhook secret

        $requiredFields = ['billcode', 'status_id', 'order_id'];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                $this->log('Missing required callback field', ['field' => $field], 'warning');
                return false;
            }
        }

        return true;
    }

    /**
     * Process callback data
     */
    public function processCallback(array $data): array
    {
        $statusId = $data['status_id'] ?? null;
        $billCode = $data['billcode'] ?? null;
        $orderId = $data['order_id'] ?? null;
        $transactionId = $data['transaction_id'] ?? null;
        $msg = $data['msg'] ?? '';
        $reason = $data['reason'] ?? '';

        // Map ToyyibPay status to our status
        $statusMap = [
            '1' => 'completed', // Success
            '2' => 'pending',   // Pending
            '3' => 'failed',    // Failed
        ];

        $status = $statusMap[$statusId] ?? 'failed';

        $this->log('Processing callback', [
            'bill_code' => $billCode,
            'status_id' => $statusId,
            'status' => $status,
        ]);

        return [
            'success' => $status === 'completed',
            'status' => $status,
            'transaction_id' => $billCode,
            'gateway_transaction_id' => $transactionId,
            'reference_number' => $orderId,
            'gateway_status' => $statusId,
            'message' => $msg ?: $reason,
            'raw_data' => $data,
        ];
    }

    /**
     * Get transaction status from gateway
     */
    public function getTransactionStatus(string $transactionId): array
    {
        $response = $this->makeRequest('POST', '/index.php/api/getBillTransactions', [
            'billCode' => $transactionId,
        ]);

        if (!$response['success'] || empty($response['data'])) {
            return [
                'success' => false,
                'status' => 'unknown',
                'error' => 'Failed to retrieve transaction status',
            ];
        }

        $transactions = $response['data'];

        if (!empty($transactions)) {
            $latestTransaction = $transactions[0];
            $billpaymentStatus = $latestTransaction['billpaymentStatus'] ?? '3';

            $statusMap = [
                '1' => 'completed',
                '2' => 'pending',
                '3' => 'failed',
            ];

            return [
                'success' => true,
                'status' => $statusMap[$billpaymentStatus] ?? 'unknown',
                'gateway_status' => $billpaymentStatus,
                'amount' => $latestTransaction['billpaymentAmount'] ?? 0,
                'transaction_id' => $latestTransaction['billpaymentInvoiceNo'] ?? null,
                'data' => $latestTransaction,
            ];
        }

        return [
            'success' => false,
            'status' => 'pending',
            'message' => 'No transactions found',
        ];
    }

    /**
     * Format amount for ToyyibPay (in cents)
     */
    protected function formatAmount(float $amount): int
    {
        return (int) ($amount * 100);
    }

    /**
     * Format phone number for ToyyibPay
     */
    protected function formatPhone(?string $phone): string
    {
        if (!$phone) {
            return '';
        }

        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Remove leading 60 or +60
        if (str_starts_with($phone, '60')) {
            $phone = substr($phone, 2);
        }

        // Add 0 prefix if not present
        if (!str_starts_with($phone, '0')) {
            $phone = '0' . $phone;
        }

        return $phone;
    }

    /**
     * Build bill description
     */
    protected function buildBillDescription(Invoice $invoice): string
    {
        $description = "Payment for Invoice #{$invoice->invoice_number}";

        if ($invoice->enrollment && $invoice->enrollment->package) {
            $description .= " - {$invoice->enrollment->package->name}";
        }

        if ($invoice->billing_period_start && $invoice->billing_period_end) {
            $description .= " ({$invoice->billing_period_start->format('M Y')})";
        }

        return substr($description, 0, 200); // ToyyibPay limit
    }

    /**
     * Build email content
     */
    protected function buildEmailContent(Invoice $invoice): string
    {
        return "Thank you for your payment for Invoice #{$invoice->invoice_number}. Your payment has been received successfully.";
    }

    /**
     * Create category if not exists
     */
    public function createCategory(string $name, string $description = ''): ?string
    {
        $response = $this->makeRequest('POST', '/index.php/api/createCategory', [
            'catname' => $name,
            'catdescription' => $description,
            'userSecretKey' => $this->getApiSecret(),
        ]);

        if ($response['success'] && isset($response['data']['CategoryCode'])) {
            return $response['data']['CategoryCode'];
        }

        return null;
    }

    /**
     * Get all categories
     */
    public function getCategories(): array
    {
        $response = $this->makeRequest('GET', '/index.php/api/getCategoryDetails', [
            'categoryCode' => '',
            'userSecretKey' => $this->getApiSecret(),
        ]);

        if ($response['success']) {
            return $response['data'] ?? [];
        }

        return [];
    }
}
