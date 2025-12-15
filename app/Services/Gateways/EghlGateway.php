<?php

namespace App\Services\Gateways;

use App\Models\Invoice;
use App\Models\PaymentGatewayConfig;

class EghlGateway extends BaseGateway
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
            return config('payment_gateways.gateways.eghl.sandbox_url', 'https://test2pay.ghl.com');
        }
        
        return config('payment_gateways.gateways.eghl.production_url', 'https://pay.ghl.com');
    }

    /**
     * Create a payment
     */
    public function createPayment(Invoice $invoice, array $customerData): array
    {
        $merchantId = $this->getMerchantId();
        $password = $this->getApiSecret(); // Service ID in EGHL

        $transactionId = $invoice->invoice_number . '_' . time();
        $amount = number_format($invoice->balance, 2, '.', '');
        $currencyCode = $this->config->supported_currencies[0] ?? 'MYR';
        
        $customerName = $customerData['name'] ?? $invoice->student->user->name;
        $customerEmail = $customerData['email'] ?? $invoice->student->user->email;
        $customerPhone = $this->formatPhone($customerData['phone'] ?? $invoice->student->parent?->whatsapp_number);

        // Payment type: 'SALE' for immediate payment
        $paymentType = 'SALE';

        // Build payment data
        $params = [
            'TransactionType' => $paymentType,
            'PymtMethod' => 'ANY', // ANY allows all payment methods
            'ServiceID' => $password,
            'PaymentID' => $transactionId,
            'OrderNumber' => $invoice->invoice_number,
            'PaymentDesc' => $this->buildDescription($invoice),
            'MerchantReturnURL' => route('payment.callback', ['gateway' => 'eghl']),
            'Amount' => $amount,
            'CurrencyCode' => $currencyCode,
            'CustIP' => request()->ip(),
            'CustName' => $customerName,
            'CustEmail' => $customerEmail,
            'CustPhone' => $customerPhone,
            'PageTimeout' => '600', // 10 minutes
        ];

        // Generate hash for security
        $params['HashValue'] = $this->generateHash($params, $merchantId, $password);

        // Build payment URL
        $paymentUrl = $this->getBaseUrl() . '/IPGSG/Payment.aspx';
        $fullPaymentUrl = $paymentUrl . '?' . http_build_query($params);

        $this->log('Payment URL generated', [
            'payment_id' => $transactionId,
            'amount' => $amount,
        ]);

        return [
            'success' => true,
            'transaction_id' => $transactionId,
            'payment_id' => $transactionId,
            'payment_url' => $fullPaymentUrl,
            'status' => 'pending',
            'callback_url' => route('payment.callback', ['gateway' => 'eghl']),
            'return_url' => route('payment.callback', ['gateway' => 'eghl']),
        ];
    }

    /**
     * Get payment URL for redirect
     */
    public function getPaymentUrl(string $billCode): string
    {
        return $this->getBaseUrl() . '/IPGSG/Payment.aspx';
    }

    /**
     * Verify callback signature
     */
    public function verifyCallback(array $data): bool
    {
        // EGHL sends hash in the callback
        $receivedHash = $data['HashValue'] ?? $data['HashValue2'] ?? '';
        
        if (empty($receivedHash)) {
            $this->log('No hash value in callback', [], 'warning');
            return false;
        }

        $password = $this->getApiSecret();
        $merchantId = $this->getMerchantId();

        // Generate expected hash
        $expectedHash = $this->generateCallbackHash($data, $password);

        $isValid = hash_equals(strtoupper($expectedHash), strtoupper($receivedHash));

        if (!$isValid) {
            $this->log('Hash verification failed', [
                'expected' => $expectedHash,
                'received' => $receivedHash,
            ], 'warning');
        }

        return $isValid;
    }

    /**
     * Process callback data and return standardized result
     */
    public function processCallback(array $data): array
    {
        $transactionType = $data['TransactionType'] ?? '';
        $paymentId = $data['PaymentID'] ?? '';
        $serviceId = $data['ServiceID'] ?? '';
        $paymentMethod = $data['PymtMethod'] ?? '';
        $amount = $data['Amount'] ?? 0;
        $currencyCode = $data['CurrencyCode'] ?? 'MYR';
        $txnStatus = $data['TxnStatus'] ?? '';
        $txnMessage = $data['TxnMessage'] ?? '';
        $authCode = $data['AuthCode'] ?? '';
        $issuingBank = $data['IssuingBank'] ?? '';

        // Determine status
        // 0 = Success, 1 = Failed, 2 = Pending
        $status = match ($txnStatus) {
            '0' => 'completed',
            '2' => 'pending',
            default => 'failed',
        };

        $this->log('Processing callback', [
            'payment_id' => $paymentId,
            'txn_status' => $txnStatus,
            'status' => $status,
            'message' => $txnMessage,
        ]);

        return [
            'success' => $txnStatus === '0',
            'status' => $status,
            'transaction_id' => $paymentId,
            'gateway_transaction_id' => $authCode,
            'reference_number' => $data['OrderNumber'] ?? null,
            'gateway_status' => $txnStatus,
            'message' => $txnMessage,
            'payment_method' => $paymentMethod,
            'issuing_bank' => $issuingBank,
            'raw_data' => $data,
        ];
    }

    /**
     * Get transaction status from gateway
     */
    public function getTransactionStatus(string $transactionId): array
    {
        $merchantId = $this->getMerchantId();
        $password = $this->getApiSecret();

        // EGHL Query API
        $queryParams = [
            'ServiceID' => $password,
            'PaymentID' => $transactionId,
            'MerchantID' => $merchantId,
        ];

        // Generate hash for query
        $hashString = $password . $merchantId . $transactionId;
        $queryParams['HashValue'] = hash('sha256', $hashString);

        $queryUrl = $this->getBaseUrl() . '/IPGSG/Payment/Query.aspx';
        
        $response = $this->makeRequest('POST', '/IPGSG/Payment/Query.aspx', $queryParams);

        if (!$response['success']) {
            return [
                'success' => false,
                'status' => 'unknown',
                'error' => 'Failed to retrieve transaction status',
            ];
        }

        $data = $response['data'];
        $txnStatus = $data['TxnStatus'] ?? '';

        $status = match ($txnStatus) {
            '0' => 'completed',
            '2' => 'pending',
            default => 'failed',
        };

        return [
            'success' => true,
            'status' => $status,
            'gateway_status' => $txnStatus,
            'amount' => $data['Amount'] ?? 0,
            'transaction_id' => $data['PaymentID'] ?? null,
            'auth_code' => $data['AuthCode'] ?? null,
            'data' => $data,
        ];
    }

    /**
     * Generate hash for payment request
     */
    protected function generateHash(array $params, string $merchantId, string $password): string
    {
        // EGHL hash format: 
        // Hash(password + MerchantID + ServiceID + PaymentID + MerchantReturnURL + Amount + CurrencyCode + CustIP + PageTimeout)
        
        $hashString = 
            $password .
            $merchantId .
            $params['ServiceID'] .
            $params['PaymentID'] .
            $params['MerchantReturnURL'] .
            $params['Amount'] .
            $params['CurrencyCode'] .
            $params['CustIP'] .
            $params['PageTimeout'];

        return hash('sha256', $hashString);
    }

    /**
     * Generate hash for callback verification
     */
    protected function generateCallbackHash(array $data, string $password): string
    {
        // EGHL callback hash format:
        // Hash(password + TxnID + ServiceID + PaymentID + TxnStatus + Amount + CurrencyCode + AuthCode)
        
        $hashString = 
            $password .
            ($data['TxnID'] ?? '') .
            ($data['ServiceID'] ?? '') .
            ($data['PaymentID'] ?? '') .
            ($data['TxnStatus'] ?? '') .
            ($data['Amount'] ?? '') .
            ($data['CurrencyCode'] ?? '') .
            ($data['AuthCode'] ?? '');

        return hash('sha256', $hashString);
    }

    /**
     * Build payment description
     */
    protected function buildDescription(Invoice $invoice): string
    {
        $studentName = $invoice->student->user->name ?? 'Student';
        return "Tuition fee payment for {$studentName} - Invoice #{$invoice->invoice_number}";
    }

    /**
     * Format phone number for EGHL
     */
    protected function formatPhone(?string $phone): string
    {
        if (!$phone) {
            return '';
        }

        // Remove non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // EGHL accepts Malaysian format: 60123456789
        if (!str_starts_with($phone, '60')) {
            // If starts with 0, replace with 60
            if (str_starts_with($phone, '0')) {
                $phone = '60' . substr($phone, 1);
            } else {
                $phone = '60' . $phone;
            }
        }

        return $phone;
    }

    /**
     * Get available payment methods
     */
    public function getPaymentMethods(): array
    {
        // Query payment method endpoint
        $response = $this->makeRequest('GET', '/IPGSG/Payment/Methods.aspx', [
            'ServiceID' => $this->getApiSecret(),
        ]);

        if ($response['success'] && isset($response['data'])) {
            return $response['data'];
        }

        // Default payment methods if query fails
        return [
            'credit_card' => 'Credit/Debit Card',
            'fpx' => 'FPX Online Banking',
            'ewallet' => 'E-Wallet',
        ];
    }

    /**
     * Override makeRequest to handle EGHL's response format
     */
    protected function makeRequest(string $method, string $endpoint, array $data = [], array $headers = []): array
    {
        $url = $this->getBaseUrl() . $endpoint;

        $defaultHeaders = [
            'Accept' => 'application/x-www-form-urlencoded',
            'Content-Type' => 'application/x-www-form-urlencoded',
        ];

        $headers = array_merge($defaultHeaders, $headers);

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
                $options['form_params'] = $data;
            }

            $response = $client->request($method, $url, $options);
            $body = $response->getBody()->getContents();

            $this->log("API Request to {$endpoint}", [
                'method' => $method,
                'response_code' => $response->getStatusCode(),
            ]);

            // EGHL returns query string format or XML
            // Try to parse as query string first
            parse_str($body, $parsed);
            
            return [
                'success' => true,
                'status_code' => $response->getStatusCode(),
                'data' => $parsed ?: $body,
                'raw' => $body,
            ];

        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $this->log("API Request failed to {$endpoint}", [
                'method' => $method,
                'error' => $e->getMessage(),
            ], 'error');

            return [
                'success' => false,
                'status_code' => $e->getCode(),
                'error' => $e->getMessage(),
                'data' => null,
            ];
        }
    }
}
