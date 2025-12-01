<?php

namespace App\Services;

use App\Models\EmailQueue;
use App\Models\NotificationLog;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;

class EmailService
{
    protected $enabled;
    protected $fromAddress;
    protected $fromName;

    public function __construct()
    {
        $this->enabled = config('notification.email.enabled', false);
        $this->fromAddress = config('notification.email.from_address');
        $this->fromName = config('notification.email.from_name');
    }

    /**
     * Send email immediately
     */
    public function send(string $to, string $subject, string $body, ?string $recipientName = null): array
    {
        if (!$this->enabled) {
            return [
                'success' => false,
                'error' => 'Email service not enabled',
            ];
        }

        try {
            Mail::send([], [], function ($message) use ($to, $subject, $body, $recipientName) {
                $message->to($to, $recipientName)
                    ->from($this->fromAddress, $this->fromName)
                    ->subject($subject)
                    ->html($this->wrapInTemplate($body, $subject));
            });

            return [
                'success' => true,
                'message' => 'Email sent successfully',
            ];
        } catch (\Exception $e) {
            Log::error('Email send error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send email using blade template
     */
    public function sendTemplate(string $to, string $subject, string $template, array $data = [], ?string $recipientName = null): array
    {
        if (!$this->enabled) {
            return [
                'success' => false,
                'error' => 'Email service not enabled',
            ];
        }

        try {
            $body = View::make($template, $data)->render();

            Mail::send([], [], function ($message) use ($to, $subject, $body, $recipientName) {
                $message->to($to, $recipientName)
                    ->from($this->fromAddress, $this->fromName)
                    ->subject($subject)
                    ->html($body);
            });

            return [
                'success' => true,
                'message' => 'Email sent successfully',
            ];
        } catch (\Exception $e) {
            Log::error('Email template send error: ' . $e->getMessage());
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

        $items = EmailQueue::where('status', 'pending')
            ->where(function ($q) {
                $q->whereNull('scheduled_at')
                    ->orWhere('scheduled_at', '<=', now());
            })
            ->where('attempts', '<', config('notification.email.max_attempts', 3))
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'asc')
            ->limit($limit)
            ->get();

        foreach ($items as $item) {
            $results['processed']++;

            $response = $this->send(
                $item->recipient_email,
                $item->subject,
                $item->body,
                $item->recipient_name
            );

            $item->attempts++;

            if ($response['success']) {
                $item->status = 'sent';
                $item->sent_at = now();
                $results['success']++;

                // Update notification log
                NotificationLog::where('channel', 'email')
                    ->where('recipient', $item->recipient_email)
                    ->where('status', 'pending')
                    ->latest()
                    ->first()
                    ?->update(['status' => 'sent', 'sent_at' => now()]);
            } else {
                if ($item->attempts >= config('notification.email.max_attempts', 3)) {
                    $item->status = 'failed';
                    $item->failed_at = now();
                }
                $item->error_message = $response['error'] ?? 'Unknown error';
                $results['failed']++;

                // Update notification log on final failure
                if ($item->status === 'failed') {
                    NotificationLog::where('channel', 'email')
                        ->where('recipient', $item->recipient_email)
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

            // Small delay between emails
            usleep(50000); // 50ms
        }

        return $results;
    }

    /**
     * Wrap body content in email template
     */
    protected function wrapInTemplate(string $body, string $subject): string
    {
        return View::make('emails.notification', [
            'subject' => $subject,
            'body' => $body,
            'centerName' => 'Arena Matriks Edu Group',
            'year' => date('Y'),
        ])->render();
    }

    /**
     * Get queue statistics
     */
    public function getQueueStats(): array
    {
        return [
            'pending' => EmailQueue::where('status', 'pending')->count(),
            'sent' => EmailQueue::where('status', 'sent')->whereDate('sent_at', today())->count(),
            'failed' => EmailQueue::where('status', 'failed')->whereDate('failed_at', today())->count(),
        ];
    }

    /**
     * Retry failed emails
     */
    public function retryFailed(int $limit = 50): int
    {
        $count = EmailQueue::where('status', 'failed')
            ->where('attempts', '<', config('notification.email.max_attempts', 3) + 2)
            ->limit($limit)
            ->update([
                'status' => 'pending',
                'error_message' => null,
                'failed_at' => null,
            ]);

        return $count;
    }

    /**
     * Test email configuration
     */
    public function testConnection(string $testEmail): array
    {
        return $this->send(
            $testEmail,
            'Test Email - Arena Matriks',
            '<p>This is a test email from Arena Matriks Edu Group.</p><p>If you received this email, your email configuration is working correctly.</p>',
            'Test User'
        );
    }
}
