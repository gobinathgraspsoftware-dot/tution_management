<?php

namespace App\Services;

use App\Models\NotificationLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    protected $enabled;
    protected $provider;
    protected $config;

    public function __construct()
    {
        $this->enabled = config('notification.sms.enabled', false);
        $this->provider = config('notification.sms.provider', 'custom');
        $this->config = config("services.sms.{$this->provider}", []);
    }

    /**
     * Send SMS message
     */
    public function send(string $phone, string $message): array
    {
        if (!$this->enabled) {
            return [
                'success' => false,
                'error' => 'SMS service not enabled',
            ];
        }

        $phone = $this->formatPhoneNumber($phone);

        try {
            return match ($this->provider) {
                'twilio' => $this->sendViaTwilio($phone, $message),
                'nexmo' => $this->sendViaNexmo($phone, $message),
                'custom' => $this->sendViaCustom($phone, $message),
                default => ['success' => false, 'error' => 'Unknown SMS provider'],
            };
        } catch (\Exception $e) {
            Log::error('SMS send error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send via Twilio
     */
    protected function sendViaTwilio(string $phone, string $message): array
    {
        $sid = $this->config['sid'] ?? '';
        $token = $this->config['token'] ?? '';
        $from = $this->config['from'] ?? '';

        if (!$sid || !$token || !$from) {
            return ['success' => false, 'error' => 'Twilio not configured'];
        }

        $response = Http::withBasicAuth($sid, $token)
            ->asForm()
            ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                'To' => $phone,
                'From' => $from,
                'Body' => $message,
            ]);

        if ($response->successful()) {
            $data = $response->json();
            return [
                'success' => true,
                'message_id' => $data['sid'] ?? null,
                'response' => $data,
            ];
        }

        return [
            'success' => false,
            'error' => $response->json()['message'] ?? 'Twilio error',
            'response' => $response->json(),
        ];
    }

    /**
     * Send via Nexmo/Vonage
     */
    protected function sendViaNexmo(string $phone, string $message): array
    {
        $key = $this->config['key'] ?? '';
        $secret = $this->config['secret'] ?? '';
        $from = $this->config['from'] ?? '';

        if (!$key || !$secret || !$from) {
            return ['success' => false, 'error' => 'Nexmo not configured'];
        }

        $response = Http::post('https://rest.nexmo.com/sms/json', [
            'api_key' => $key,
            'api_secret' => $secret,
            'to' => $phone,
            'from' => $from,
            'text' => $message,
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $msg = $data['messages'][0] ?? [];

            if (($msg['status'] ?? '1') === '0') {
                return [
                    'success' => true,
                    'message_id' => $msg['message-id'] ?? null,
                    'response' => $data,
                ];
            }

            return [
                'success' => false,
                'error' => $msg['error-text'] ?? 'Nexmo error',
                'response' => $data,
            ];
        }

        return [
            'success' => false,
            'error' => 'Nexmo request failed',
        ];
    }

    /**
     * Send via custom SMS gateway
     */
    protected function sendViaCustom(string $phone, string $message): array
    {
        $apiUrl = $this->config['api_url'] ?? '';
        $apiKey = $this->config['api_key'] ?? '';
        $senderId = $this->config['sender_id'] ?? '';

        if (!$apiUrl || !$apiKey) {
            return ['success' => false, 'error' => 'Custom SMS not configured'];
        }

        // Generic implementation - adjust based on actual SMS provider
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ])->post($apiUrl, [
            'to' => $phone,
            'from' => $senderId,
            'message' => $message,
        ]);

        if ($response->successful()) {
            $data = $response->json();
            return [
                'success' => true,
                'message_id' => $data['message_id'] ?? $data['id'] ?? null,
                'response' => $data,
            ];
        }

        return [
            'success' => false,
            'error' => $response->json()['error'] ?? 'SMS gateway error',
            'response' => $response->json(),
        ];
    }

    /**
     * Format phone number
     */
    protected function formatPhoneNumber(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Handle Malaysian numbers
        if (str_starts_with($phone, '0')) {
            $phone = config('notification.sms.default_country_code', '60') . substr($phone, 1);
        }

        return '+' . $phone;
    }

    /**
     * Process pending SMS from notification log
     */
    public function processPending(int $limit = 50): array
    {
        $results = ['processed' => 0, 'success' => 0, 'failed' => 0];

        $items = NotificationLog::where('channel', 'sms')
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->limit($limit)
            ->get();

        foreach ($items as $item) {
            $results['processed']++;

            $response = $this->send($item->recipient, $item->message);

            if ($response['success']) {
                $item->status = 'sent';
                $item->sent_at = now();
                $item->response = json_encode($response);
                $results['success']++;
            } else {
                $item->status = 'failed';
                $item->error_message = $response['error'];
                $results['failed']++;
            }

            $item->save();

            usleep(100000); // 100ms delay
        }

        return $results;
    }

    /**
     * Check SMS balance/status
     */
    public function checkBalance(): array
    {
        if (!$this->enabled) {
            return ['success' => false, 'error' => 'SMS not enabled'];
        }

        try {
            return match ($this->provider) {
                'twilio' => $this->getTwilioBalance(),
                'nexmo' => $this->getNexmoBalance(),
                default => ['success' => false, 'error' => 'Balance check not supported'],
            };
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get Twilio account balance
     */
    protected function getTwilioBalance(): array
    {
        $sid = $this->config['sid'] ?? '';
        $token = $this->config['token'] ?? '';

        $response = Http::withBasicAuth($sid, $token)
            ->get("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Balance.json");

        if ($response->successful()) {
            $data = $response->json();
            return [
                'success' => true,
                'balance' => $data['balance'] ?? 0,
                'currency' => $data['currency'] ?? 'USD',
            ];
        }

        return ['success' => false, 'error' => 'Could not fetch balance'];
    }

    /**
     * Get Nexmo account balance
     */
    protected function getNexmoBalance(): array
    {
        $key = $this->config['key'] ?? '';
        $secret = $this->config['secret'] ?? '';

        $response = Http::get('https://rest.nexmo.com/account/get-balance', [
            'api_key' => $key,
            'api_secret' => $secret,
        ]);

        if ($response->successful()) {
            $data = $response->json();
            return [
                'success' => true,
                'balance' => $data['value'] ?? 0,
                'currency' => 'EUR',
            ];
        }

        return ['success' => false, 'error' => 'Could not fetch balance'];
    }

    /**
     * Get statistics
     */
    public function getStats(): array
    {
        return [
            'sent_today' => NotificationLog::where('channel', 'sms')
                ->where('status', 'sent')
                ->whereDate('sent_at', today())
                ->count(),
            'failed_today' => NotificationLog::where('channel', 'sms')
                ->where('status', 'failed')
                ->whereDate('created_at', today())
                ->count(),
            'pending' => NotificationLog::where('channel', 'sms')
                ->where('status', 'pending')
                ->count(),
        ];
    }
}
