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
     * Get the API base URL - dynamically from configuration
     */
    public function getBaseUrl(): string
    {
        // Get from configuration (database) first, then fallback to config file
        if ($this->isSandbox) {
            return $this->config->configuration['sandbox_url'] ?? 
                   config('payment_gateways.gateways.eghl.sandbox_url', 'https://test2pay.ghl.com/IPGSG/Payment.aspx');
        }
        
        return $this->config->configuration['production_url'] ?? 
               config('payment_gateways.gateways.eghl.production_url', 'https://pay.ghl.com/IPGSG/Payment.aspx');
    }

    /**
     * Get Merchant ID - dynamically from configuration
     */
    protected function getMerchantId(): string
    {
        return $this->config->configuration['merchant_id'] ?? 
               $this->config->merchant_id ?? 
               config('variables.eghl_online_merchant_id', '');
    }

    /**
     * Get Merchant Password (Service ID) - dynamically from configuration
     */
    protected function getMerchantPassword(): string
    {
        $password = $this->config->configuration['merchant_password'] ?? 
                   config('variables.eghl_online_merchant_password', '');
        
        // Decrypt if encrypted
        if (!empty($password)) {
            try {
                return \Crypt::decryptString($password);
            } catch (\Exception $e) {
                // If decryption fails, return as-is (might be plain text from config)
                return $password;
            }
        }
        
        return '';
    }

    /**
     * Get Merchant Registered Name - dynamically from configuration
     */
    protected function getMerchantRegisteredName(): string
    {
        return $this->config->configuration['merchant_registered_name'] ?? 
               config('variables.eghl_online_merchant_registered_name', 'Your Company Name');
    }

    /**
     * Create a payment
     */
    public function createPayment(Invoice $invoice, array $customerData): array
    {
        $merchantId = $this->getMerchantId();
        $password = $this->getMerchantPassword();

        if (empty($merchantId) || empty($password)) {
            return [
                'success' => false,
                'message' => 'EGHL merchant credentials not configured',
            ];
        }

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
            'MerchantName' => $this->getMerchantRegisteredName(),
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

        // Build full payment URL - dynamically from configuration
        $paymentUrl = $this->getBaseUrl();
        
        // Remove query parameters if already in URL
        if (strpos($paymentUrl, '?') !== false) {
            $paymentUrl = strtok($paymentUrl, '?');
        }
        
        $fullPaymentUrl = $paymentUrl . '?' . http_build_query($params);

        $this->log('Payment URL generated', [
            'payment_id' => $transactionId,
            'amount' => $amount,
            'merchant_id' => $merchantId,
            'payment_url' => $paymentUrl,
            'sandbox' => $this->isSandbox,
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
        return $this->getBaseUrl();
    }

    /**
     * Verify callback signature
     */
    public function verifyCallback(array $data): bool
    {
        if (!isset($data['HashValue'])) {
            $this->log('Callback verification failed: HashValue missing', $data);
            return false;
        }

        $merchantId = $this->getMerchantId();
        $password = $this->getMerchantPassword();

        $receivedHash = $data['HashValue'];
        $calculatedHash = $this->generateCallbackHash($data, $merchantId, $password);

        if ($receivedHash !== $calculatedHash) {
            $this->log('Callback verification failed: Hash mismatch', [
                'received' => $receivedHash,
                'calculated' => $calculatedHash,
            ]);
            return false;
        }

        $this->log('Callback verified successfully');
        return true;
    }

    /**
     * Process callback from EGHL
     */
    public function processCallback(array $data): array
    {
        $this->log('Processing callback', $data);

        // Extract transaction details
        $transactionId = $data['TxnID'] ?? null;
        $paymentId = $data['PaymentID'] ?? null;
        $status = $data['TxnStatus'] ?? null;
        $amount = $data['Amount'] ?? null;
        $authCode = $data['AuthCode'] ?? null;
        $message = $data['TxnMessage'] ?? '';

        // Map EGHL status to our status
        // TxnStatus: 0 = Success, 1 = Failed, 2 = Pending
        $mappedStatus = match($status) {
            '0' => 'completed',
            '2' => 'pending',
            default => 'failed',
        };

        return [
            'success' => $status === '0',
            'transaction_id' => $transactionId,
            'payment_id' => $paymentId,
            'status' => $mappedStatus,
            'amount' => $amount,
            'currency' => $data['CurrencyCode'] ?? 'MYR',
            'reference' => $authCode,
            'message' => $message,
            'raw_data' => $data,
        ];
    }

    /**
     * Get transaction status
     */
    public function getTransactionStatus(string $transactionId): array
    {
        $merchantId = $this->getMerchantId();
        $password = $this->getMerchantPassword();

        // Build query parameters
        $params = [
            'MerchantID' => $merchantId,
            'ServiceID' => $password,
            'PaymentID' => $transactionId,
        ];

        // Generate hash
        $hashString = $password . $merchantId . $password . $transactionId;
        $params['HashValue'] = hash('sha256', $hashString);

        // Query URL - dynamically based on mode
        $queryUrl = $this->isSandbox 
            ? config('payment_gateways.gateways.eghl.query_url_sandbox')
            : config('payment_gateways.gateways.eghl.query_url_production');

        try {
            $response = $this->makeRequest($queryUrl, $params, 'GET');
            
            $status = $response['TxnStatus'] ?? null;
            
            return [
                'success' => true,
                'status' => match($status) {
                    '0' => 'completed',
                    '2' => 'pending',
                    default => 'failed',
                },
                'transaction_id' => $transactionId,
                'data' => $response,
            ];
        } catch (\Exception $e) {
            $this->log('Transaction status query failed', [
                'transaction_id' => $transactionId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'status' => 'unknown',
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Generate payment hash
     */
    protected function generateHash(array $params, string $merchantId, string $password): string
    {
        // EGHL Hash = SHA256(ServiceID + MerchantID + ServiceID + PaymentID + 
        //                    MerchantReturnURL + Amount + CurrencyCode + CustIP + PageTimeout)
        
        $hashString = $password . 
                     $merchantId . 
                     $password . 
                     $params['PaymentID'] . 
                     $params['MerchantReturnURL'] . 
                     $params['Amount'] . 
                     $params['CurrencyCode'] . 
                     $params['CustIP'] . 
                     $params['PageTimeout'];

        return hash('sha256', $hashString);
    }

    /**
     * Generate callback hash for verification
     */
    protected function generateCallbackHash(array $data, string $merchantId, string $password): string
    {
        // EGHL Callback Hash = SHA256(ServiceID + TxnID + ServiceID + PaymentID + 
        //                             TxnStatus + Amount + CurrencyCode + AuthCode)
        
        $hashString = $password . 
                     ($data['TxnID'] ?? '') . 
                     $password . 
                     ($data['PaymentID'] ?? '') . 
                     ($data['TxnStatus'] ?? '') . 
                     ($data['Amount'] ?? '') . 
                     ($data['CurrencyCode'] ?? '') . 
                     ($data['AuthCode'] ?? '');

        return hash('sha256', $hashString);
    }

    /**
     * Format phone number for EGHL (needs country code)
     */
    protected function formatPhone(?string $phone): string
    {
        if (empty($phone)) {
            return '60123456789'; // Default Malaysian number
        }

        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Add Malaysian country code if not present
        if (!str_starts_with($phone, '60')) {
            // Remove leading 0 if present
            $phone = ltrim($phone, '0');
            $phone = '60' . $phone;
        }

        return $phone;
    }

    /**
     * Build payment description
     */
    protected function buildDescription(Invoice $invoice): string
    {
        return "Payment for Invoice #{$invoice->invoice_number}";
    }

    /**
     * Test connection to EGHL
     */
    public function testConnection(): array
    {
        $merchantId = $this->getMerchantId();
        $password = $this->getMerchantPassword();
        $merchantName = $this->getMerchantRegisteredName();
        $sandboxUrl = $this->config->configuration['sandbox_url'] ?? config('payment_gateways.gateways.eghl.sandbox_url');
        $productionUrl = $this->config->configuration['production_url'] ?? config('payment_gateways.gateways.eghl.production_url');

        if (empty($merchantId)) {
            return [
                'success' => false,
                'message' => 'Merchant ID is not configured',
            ];
        }

        if (empty($password)) {
            return [
                'success' => false,
                'message' => 'Merchant Password (Service ID) is not configured',
            ];
        }

        try {
            // Get current URL based on mode
            $currentUrl = $this->getBaseUrl();
            
            // Simple validation that credentials are set
            return [
                'success' => true,
                'message' => 'EGHL credentials configured successfully',
                'details' => [
                    'merchant_id' => $merchantId,
                    'merchant_name' => $merchantName,
                    'mode' => $this->isSandbox ? 'Sandbox (Development)' : 'Production (Live)',
                    'current_url' => $currentUrl,
                    'sandbox_url' => $sandboxUrl,
                    'production_url' => $productionUrl,
                ],
            ];
        } catch (\Exception $e) {
            $this->log('Connection test failed', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Connection test failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Make HTTP request
     */
    protected function makeRequest(string $url, array $data, string $method = 'POST'): array
    {
        $ch = curl_init();

        if ($method === 'GET') {
            $url .= '?' . http_build_query($data);
            curl_setopt($ch, CURLOPT_HTTPGET, true);
        } else {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, !$this->isSandbox);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);

        if ($error) {
            throw new \Exception('cURL Error: ' . $error);
        }

        if ($httpCode !== 200) {
            throw new \Exception('HTTP Error: ' . $httpCode);
        }

        // Parse response (EGHL returns URL encoded data)
        parse_str($response, $parsedResponse);

        return $parsedResponse;
    }

    /**
     * Log for debugging
     */
    protected function log(string $message, array $context = []): void
    {
        \Log::info('[EGHL Gateway] ' . $message, $context);
    }
}
