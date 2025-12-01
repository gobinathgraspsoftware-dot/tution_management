<?php

namespace App\Services;

use App\Models\WhatsappQueue;
use App\Models\NotificationLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsappService
{
    protected $apiUrl;
    protected $instanceId;
    protected $token;
    protected $enabled;

    public function __construct()
    {
        $this->apiUrl = config('notification.whatsapp.api_url');
        $this->instanceId = config('notification.whatsapp.instance_id');
        $this->token = config('notification.whatsapp.token');
        $this->enabled = config('notification.whatsapp.enabled', false);
    }

    /**
     * Send WhatsApp message immediately
     */
    public function send(string $phone, string $message, ?string $mediaUrl = null): array
    {
        if (!$this->enabled || !$this->instanceId || !$this->token) {
            return [
                'success' => false,
                'error' => 'WhatsApp service not configured',
            ];
        }

        $phone = $this->formatPhoneNumber($phone);

        try {
            $endpoint = "{$this->apiUrl}/{$this->instanceId}/messages/chat";

            $payload = [
                'token' => $this->token,
                'to' => $phone,
                'body' => $message,
            ];

            // If media URL provided, send as media message
            if ($mediaUrl) {
                $endpoint = "{$this->apiUrl}/{$this->instanceId}/messages/image";
                $payload['image'] = $mediaUrl;
                $payload['caption'] = $message;
            }

            $response = Http::timeout(30)
                ->asForm()
                ->post($endpoint, $payload);

            $data = $response->json();

            if ($response->successful() && isset($data['sent']) && $data['sent'] === 'true') {
                return [
                    'success' => true,
                    'message_id' => $data['id'] ?? null,
                    'response' => $data,
                ];
            }

            return [
                'success' => false,
                'error' => $data['error'] ?? 'Unknown error',
                'response' => $data,
            ];

        } catch (\Exception $e) {
            Log::error('WhatsApp send error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Process pending queue items
     */
    public function processQueue(int $limit = 50): array
    {
        $results = ['processed' => 0, 'success' => 0, 'failed' => 0];

        $items = WhatsappQueue::where('status', 'pending')
            ->where(function ($q) {
                $q->whereNull('scheduled_at')
                    ->orWhere('scheduled_at', '<=', now());
            })
            ->where('attempts', '<', config('notification.whatsapp.max_attempts', 3))
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'asc')
            ->limit($limit)
            ->get();

        foreach ($items as $item) {
            $results['processed']++;

            $response = $this->send($item->recipient_phone, $item->message, $item->media_url);

            $item->attempts++;

            if ($response['success']) {
                $item->status = 'sent';
                $item->sent_at = now();
                $item->whatsapp_message_id = $response['message_id'] ?? null;
                $results['success']++;

                // Update notification log
                NotificationLog::where('channel', 'whatsapp')
                    ->where('recipient', $item->recipient_phone)
                    ->where('status', 'pending')
                    ->latest()
                    ->first()
                    ?->update(['status' => 'sent', 'sent_at' => now()]);
            } else {
                if ($item->attempts >= config('notification.whatsapp.max_attempts', 3)) {
                    $item->status = 'failed';
                    $item->failed_at = now();
                }
                $item->error_message = $response['error'] ?? 'Unknown error';
                $results['failed']++;

                // Update notification log on final failure
                if ($item->status === 'failed') {
                    NotificationLog::where('channel', 'whatsapp')
                        ->where('recipient', $item->recipient_phone)
                        ->where('status', 'pending')
                        ->latest()
                        ->first()
                        ?->update([
                            'status' => 'failed',
                            'error_message' => $item->error_message
                        ]);
                }
            }

            $item->save();

            // Rate limiting
            usleep(100000); // 100ms delay between messages
        }

        return $results;
    }

    /**
     * Send template message
     */
    public function sendTemplate(string $phone, string $templateName, array $variables = []): array
    {
        // Ultra Messenger doesn't support official WhatsApp templates
        // Build message from template manually
        $message = $this->buildTemplateMessage($templateName, $variables);
        return $this->send($phone, $message);
    }

    /**
     * Build message from template name
     */
    protected function buildTemplateMessage(string $templateName, array $variables): string
    {
        $templates = [
            'payment_reminder' => "🔔 *Payment Reminder*\n\nDear {parent_name},\n\nThis is a reminder that payment of *RM{amount}* for {student_name} is due on *{due_date}*.\n\nInvoice: {invoice_number}\n\nPlease make payment to avoid service interruption.\n\nThank you,\nArena Matriks Edu Group",

            'welcome' => "🎉 *Welcome to Arena Matriks!*\n\nDear {parent_name},\n\nWelcome! {student_name} has been successfully enrolled.\n\nYou can now access the parent portal to view schedules, attendance, and more.\n\nLogin: {login_link}\n\nThank you for choosing Arena Matriks Edu Group!",

            'attendance_present' => "✅ *Attendance Notification*\n\nDear {parent_name},\n\n{student_name} has been marked *PRESENT* for today's class.\n\nDate: {attendance_date}\nClass: {class_name}\n\nThank you,\nArena Matriks",

            'attendance_absent' => "⚠️ *Attendance Alert*\n\nDear {parent_name},\n\n{student_name} was marked *ABSENT* from today's class.\n\nDate: {attendance_date}\nClass: {class_name}\n\nIf this is incorrect, please contact us.\n\nArena Matriks",

            'exam_result' => "📊 *Exam Results*\n\nDear {parent_name},\n\nResults for {exam_name} are now available.\n\nStudent: {student_name}\nScore: {score}\nGrade: {grade}\n\nView detailed results in the parent portal.\n\nArena Matriks",

            'trial_class' => "📅 *Trial Class Confirmation*\n\nDear {parent_name},\n\nTrial class for {student_name} has been scheduled.\n\nDate: {trial_date}\nTime: {trial_time}\nSubject: {subject_name}\n\nWe look forward to meeting you!\n\nArena Matriks",
        ];

        $message = $templates[$templateName] ?? $templateName;

        foreach ($variables as $key => $value) {
            $message = str_replace("{{$key}}", $value, $message);
        }

        return $message;
    }

    /**
     * Format phone number for WhatsApp
     */
    protected function formatPhoneNumber(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Remove leading zeros
        $phone = ltrim($phone, '0');

        // Add country code if not present
        if (!str_starts_with($phone, '60') && strlen($phone) <= 10) {
            $phone = '60' . $phone;
        }

        return $phone;
    }

    /**
     * Check connection status
     */
    public function checkStatus(): array
    {
        if (!$this->enabled || !$this->instanceId || !$this->token) {
            return [
                'connected' => false,
                'error' => 'Not configured',
            ];
        }

        try {
            $response = Http::timeout(10)
                ->get("{$this->apiUrl}/{$this->instanceId}/instance/status", [
                    'token' => $this->token,
                ]);

            $data = $response->json();

            return [
                'connected' => ($data['status']['accountStatus']['status'] ?? '') === 'authenticated',
                'phone' => $data['status']['accountStatus']['phone'] ?? null,
                'response' => $data,
            ];
        } catch (\Exception $e) {
            return [
                'connected' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get queue statistics
     */
    public function getQueueStats(): array
    {
        return [
            'pending' => WhatsappQueue::where('status', 'pending')->count(),
            'sent' => WhatsappQueue::where('status', 'sent')->whereDate('sent_at', today())->count(),
            'failed' => WhatsappQueue::where('status', 'failed')->whereDate('failed_at', today())->count(),
            'delivered' => WhatsappQueue::where('status', 'delivered')->whereDate('delivered_at', today())->count(),
        ];
    }

    /**
     * Retry failed messages
     */
    public function retryFailed(int $limit = 50): int
    {
        $count = WhatsappQueue::where('status', 'failed')
            ->where('attempts', '<', config('notification.whatsapp.max_attempts', 3) + 2) // Allow extra retries
            ->limit($limit)
            ->update([
                'status' => 'pending',
                'error_message' => null,
                'failed_at' => null,
            ]);

        return $count;
    }
}
