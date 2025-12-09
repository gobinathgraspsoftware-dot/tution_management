<?php

namespace App\Services\Gateways;

use App\Models\Invoice;
use App\Models\PaymentGatewayConfig;

class BillplzGateway extends BaseGateway
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
            return config('payment_gateways.gateways.billplz.sandbox_url', 'https://www.billplz-sandbox.com/api/v3');
        }

        return config('payment_gateways.gateways.billplz.production_url', 'https://www.billplz.com/api/v3');
    }

    /**
     * Create a bill/payment
     */
    public function createPayment(Invoice $invoice, array $customerData): array
    {
        $collectionId = $this->getConfiguration('collection_id');

        if (!$collectionId) {
            $this->log('Collection ID not configured', [], 'error');
            return [
                'success' => false,
                'error' => 'Payment gateway not properly configured',
            ];
        }

        $billData = [
            'collection_id' => $collectionId,
            'email' => $customerData['email'] ?? $invoice->student->user->email,
            'mobile' => $this->formatPhone($customerData['phone'] ?? $invoice->student->parent?->whatsapp_number),
            'name' => $customerData['name'] ?? $invoice->student->user->name,
            'amount' => $this->formatAmount($invoice->balance),
            'callback_url' => route('payment.webhook', ['gateway' => 'billplz']),
            'redirect_url' => route('payment.callback', ['gateway' => 'billplz']),
            'description' => $this->buildDescription($invoice),
            'reference_1_label' => 'Invoice Number',
            'reference_1' => $invoice->invoice_number,
            'reference_2_label' => 'Student ID',
            'reference_2' => $invoice->student->student_id ?? $invoice->student_id,
        ];

        // Add due date if needed
        if ($invoice->due_date) {
            $billData['due_at'] = $invoice->due_date->format('Y-m-d');
        }

        $response = $this->makeRequest('POST', '/bills', $billData, $this->getAuthHeaders());

        if (!$response['success']) {
            $this->log('Failed to create bill', ['error' => $response['error']], 'error');
            return [
                'success' => false,
                'error' => $response['error'] ?? 'Failed to create payment bill',
            ];
        }

        $data = $response['data'];

        if (isset($data['id'])) {
            $this->log('Bill created successfully', ['bill_id' => $data['id']]);

            return [
                'success' => true,
                'transaction_id' => $data['id'],
                'bill_id' => $data['id'],
                'payment_url' => $data['url'] ?? $this->getPaymentUrl($data['id']),
                'status' => 'pending',
                'callback_url' => route('payment.callback', ['gateway' => 'billplz']),
                'return_url' => route('payment.callback', ['gateway' => 'billplz']),
            ];
        }

        $this->log('Unexpected response format', ['response' => $data], 'error');

        return [
            'success' => false,
            'error' => $data['error']['message'] ?? 'Unexpected response from payment gateway',
        ];
    }

    /**
     * Get payment URL for redirect
     */
    public function getPaymentUrl(string $billCode): string
    {
        $baseUrl = $this->isSandbox
            ? 'https://www.billplz-sandbox.com'
            : 'https://www.billplz.com';

        return $baseUrl . '/bills/' . $billCode;
    }

    /**
     * Verify callback signature (X-Signature)
     */
    public function verifyCallback(array $data): bool
    {
        $xSignature = $data['x_signature'] ?? null;

        if (!$xSignature) {
            $this->log('Missing X-Signature in callback', [], 'warning');
            return false;
        }

        // Build the string to sign
        $signData = [];
        foreach ($data as $key => $value) {
            if ($key !== 'x_signature' && $value !== null && $value !== '') {
                $signData[$key] = $value;
            }
        }
        ksort($signData);

        $signString = '';
        foreach ($signData as $key => $value) {
            $signString .= $key . $value;
        }

        $expectedSignature = hash_hmac('sha256', $signString, $this->getApiSecret());

        $isValid = $xSignature === $expectedSignature;

        if (!$isValid) {
            $this->log('Invalid callback signature', [
                'expected' => $expectedSignature,
                'received' => $xSignature,
            ], 'warning');
        }

        return $isValid;
    }

    /**
     * Process callback data
     */
    public function processCallback(array $data): array
    {
        $billId = $data['id'] ?? $data['billplz']['id'] ?? null;
        $paid = $data['paid'] ?? $data['billplz']['paid'] ?? false;
        $paidAmount = $data['paid_amount'] ?? $data['billplz']['paid_amount'] ?? 0;
        $transactionId = $data['transaction_id'] ?? $data['billplz']['transaction_id'] ?? null;

        // Convert paid to boolean
        $isPaid = filter_var($paid, FILTER_VALIDATE_BOOLEAN);

        $status = $isPaid ? 'completed' : 'failed';

        $this->log('Processing callback', [
            'bill_id' => $billId,
            'paid' => $isPaid,
            'status' => $status,
        ]);

        return [
            'success' => $isPaid,
            'status' => $status,
            'transaction_id' => $billId,
            'gateway_transaction_id' => $transactionId,
            'reference_number' => $data['reference_1'] ?? null,
            'gateway_status' => $isPaid ? 'paid' : 'due',
            'message' => $isPaid ? 'Payment successful' : 'Payment failed',
            'amount_paid' => $paidAmount / 100, // Convert cents to ringgit
            'raw_data' => $data,
        ];
    }

    /**
     * Get transaction status from gateway
     */
    public function getTransactionStatus(string $transactionId): array
    {
        $response = $this->makeRequest('GET', "/bills/{$transactionId}", [], $this->getAuthHeaders());

        if (!$response['success']) {
            return [
                'success' => false,
                'status' => 'unknown',
                'error' => 'Failed to retrieve transaction status',
            ];
        }

        $data = $response['data'];
        $paid = $data['paid'] ?? false;

        return [
            'success' => true,
            'status' => $paid ? 'completed' : 'pending',
            'gateway_status' => $data['state'] ?? 'unknown',
            'amount' => ($data['amount'] ?? 0) / 100,
            'paid_amount' => ($data['paid_amount'] ?? 0) / 100,
            'transaction_id' => $data['id'] ?? null,
            'data' => $data,
        ];
    }

    /**
     * Get authorization headers for Billplz API
     */
    protected function getAuthHeaders(): array
    {
        $apiKey = $this->getApiKey();

        return [
            'Authorization' => 'Basic ' . base64_encode($apiKey . ':'),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    /**
     * Override makeRequest for JSON body
     */
    protected function makeRequest(string $method, string $endpoint, array $data = [], array $headers = []): array
    {
        $url = $this->getBaseUrl() . $endpoint;

        try {
            $client = new \GuzzleHttp\Client([
                'timeout' => 30,
                'verify' => !$this->isSandbox,
            ]);

            $options = [
                'headers' => $headers,
            ];

            if (strtoupper($method) === 'GET') {
                $options['query'] = $data;
            } else {
                $options['json'] = $data;
            }

            $response = $client->request($method, $url, $options);
            $body = $response->getBody()->getContents();

            $this->log("API Request to {$endpoint}", [
                'method' => $method,
                'response_code' => $response->getStatusCode(),
            ]);

            return [
                'success' => true,
                'status_code' => $response->getStatusCode(),
                'data' => json_decode($body, true),
                'raw' => $body,
            ];

        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $this->log("API Request failed to {$endpoint}", [
                'method' => $method,
                'error' => $e->getMessage(),
            ], 'error');

            $response = $e->getResponse();
            $errorData = null;

            if ($response) {
                $errorData = json_decode($response->getBody()->getContents(), true);
            }

            return [
                'success' => false,
                'status_code' => $e->getCode(),
                'error' => $errorData['error']['message'] ?? $e->getMessage(),
                'data' => $errorData,
            ];
        }
    }

    /**
     * Format amount for Billplz (in cents)
     */
    protected function formatAmount(float $amount): int
    {
        return (int) ($amount * 100);
    }

    /**
     * Format phone number for Billplz
     */
    protected function formatPhone(?string $phone): string
    {
        if (!$phone) {
            return '';
        }

        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Ensure it starts with 60
        if (str_starts_with($phone, '0')) {
            $phone = '6' . $phone;
        } elseif (!str_starts_with($phone, '60')) {
            $phone = '60' . $phone;
        }

        return $phone;
    }

    /**
     * Build payment description
     */
    protected function buildDescription(Invoice $invoice): string
    {
        $description = "Payment for Invoice #{$invoice->invoice_number}";

        if ($invoice->enrollment && $invoice->enrollment->package) {
            $description .= " - {$invoice->enrollment->package->name}";
        }

        return substr($description, 0, 200);
    }

    /**
     * Create a collection
     */
    public function createCollection(string $title): ?array
    {
        $response = $this->makeRequest('POST', '/collections', [
            'title' => $title,
        ], $this->getAuthHeaders());

        if ($response['success']) {
            return $response['data'];
        }

        return null;
    }

    /**
     * Get all collections
     */
    public function getCollections(): array
    {
        $response = $this->makeRequest('GET', '/collections', [], $this->getAuthHeaders());

        if ($response['success']) {
            return $response['data']['collections'] ?? [];
        }

        return [];
    }

    /**
     * Delete a bill
     */
    public function deleteBill(string $billId): bool
    {
        $response = $this->makeRequest('DELETE', "/bills/{$billId}", [], $this->getAuthHeaders());

        return $response['success'];
    }
}
