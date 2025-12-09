<?php

namespace App\Services\Gateways;

use App\Models\PaymentGatewayConfig;
use App\Models\PaymentGatewayTransaction;
use App\Models\Invoice;
use Illuminate\Support\Facades\Log;

abstract class BaseGateway
{
    /**
     * Gateway configuration model
     */
    protected PaymentGatewayConfig $config;

    /**
     * Gateway name identifier
     */
    protected string $gatewayName;

    /**
     * Whether in sandbox mode
     */
    protected bool $isSandbox = true;

    /**
     * Constructor
     */
    public function __construct(PaymentGatewayConfig $config)
    {
        $this->config = $config;
        $this->gatewayName = $config->gateway_name;
        $this->isSandbox = $config->is_sandbox;
    }

    /**
     * Get the API base URL
     */
    abstract public function getBaseUrl(): string;

    /**
     * Create a payment/bill
     */
    abstract public function createPayment(Invoice $invoice, array $customerData): array;

    /**
     * Get payment URL for redirect
     */
    abstract public function getPaymentUrl(string $billCode): string;

    /**
     * Verify callback/webhook signature
     */
    abstract public function verifyCallback(array $data): bool;

    /**
     * Process callback data and return standardized result
     */
    abstract public function processCallback(array $data): array;

    /**
     * Get transaction status from gateway
     */
    abstract public function getTransactionStatus(string $transactionId): array;

    /**
     * Generate unique transaction reference
     */
    public function generateTransactionReference(): string
    {
        $prefix = config('payment_gateways.transaction.prefix', 'TXN');
        return $prefix . date('YmdHis') . strtoupper(substr(md5(uniqid()), 0, 6));
    }

    /**
     * Create a transaction record
     */
    public function createTransaction(Invoice $invoice, array $gatewayResponse, array $customerData = []): PaymentGatewayTransaction
    {
        return PaymentGatewayTransaction::create([
            'gateway_config_id' => $this->config->id,
            'invoice_id' => $invoice->id,
            'transaction_id' => $gatewayResponse['transaction_id'] ?? $this->generateTransactionReference(),
            'amount' => $invoice->balance,
            'currency' => $this->config->supported_currencies[0] ?? 'MYR',
            'status' => 'pending',
            'gateway_status' => $gatewayResponse['status'] ?? 'pending',
            'gateway_response' => $gatewayResponse,
            'customer_email' => $customerData['email'] ?? null,
            'customer_phone' => $customerData['phone'] ?? null,
            'ip_address' => request()->ip(),
            'callback_url' => $gatewayResponse['callback_url'] ?? null,
            'return_url' => $gatewayResponse['return_url'] ?? null,
        ]);
    }

    /**
     * Update transaction status
     */
    public function updateTransaction(PaymentGatewayTransaction $transaction, string $status, array $gatewayResponse = []): PaymentGatewayTransaction
    {
        $transaction->update([
            'status' => $status,
            'gateway_status' => $gatewayResponse['gateway_status'] ?? $transaction->gateway_status,
            'gateway_response' => array_merge($transaction->gateway_response ?? [], $gatewayResponse),
            'webhook_received_at' => now(),
        ]);

        return $transaction->fresh();
    }

    /**
     * Calculate gateway fees
     */
    public function calculateFees(float $amount): array
    {
        $percentageFee = $amount * ($this->config->transaction_fee_percentage / 100);
        $fixedFee = $this->config->transaction_fee_fixed;
        $totalFee = $percentageFee + $fixedFee;

        return [
            'amount' => $amount,
            'percentage_fee' => round($percentageFee, 2),
            'fixed_fee' => round($fixedFee, 2),
            'total_fee' => round($totalFee, 2),
            'total_with_fee' => round($amount + $totalFee, 2),
        ];
    }

    /**
     * Get API key (decrypted)
     */
    protected function getApiKey(): ?string
    {
        return $this->config->api_key;
    }

    /**
     * Get API secret (decrypted)
     */
    protected function getApiSecret(): ?string
    {
        return $this->config->api_secret;
    }

    /**
     * Get merchant ID
     */
    protected function getMerchantId(): ?string
    {
        return $this->config->merchant_id;
    }

    /**
     * Get webhook secret
     */
    protected function getWebhookSecret(): ?string
    {
        return $this->config->webhook_secret;
    }

    /**
     * Get additional configuration
     */
    protected function getConfiguration(string $key = null, $default = null)
    {
        $config = $this->config->configuration ?? [];

        if ($key === null) {
            return $config;
        }

        return $config[$key] ?? $default;
    }

    /**
     * Log gateway activity
     */
    protected function log(string $message, array $context = [], string $level = 'info'): void
    {
        $context['gateway'] = $this->gatewayName;
        $context['sandbox'] = $this->isSandbox;

        Log::channel('payment')->{$level}("[{$this->gatewayName}] {$message}", $context);
    }

    /**
     * Make HTTP request to gateway
     */
    protected function makeRequest(string $method, string $endpoint, array $data = [], array $headers = []): array
    {
        $url = $this->getBaseUrl() . $endpoint;

        $defaultHeaders = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/x-www-form-urlencoded',
        ];

        $headers = array_merge($defaultHeaders, $headers);

        try {
            $client = new \GuzzleHttp\Client([
                'timeout' => 30,
                'verify' => !$this->isSandbox, // Disable SSL verification in sandbox
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
                'data' => $data,
                'response_code' => $response->getStatusCode(),
            ]);

            // Try to decode JSON response
            $decoded = json_decode($body, true);

            return [
                'success' => true,
                'status_code' => $response->getStatusCode(),
                'data' => $decoded ?? $body,
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

    /**
     * Get supported payment methods
     */
    public function getSupportedMethods(): array
    {
        return config("payment_gateways.gateways.{$this->gatewayName}.supported_methods", []);
    }

    /**
     * Check if gateway is active
     */
    public function isActive(): bool
    {
        return $this->config->is_active;
    }

    /**
     * Get gateway display name
     */
    public function getDisplayName(): string
    {
        return config("payment_gateways.gateways.{$this->gatewayName}.name", ucfirst($this->gatewayName));
    }
}
