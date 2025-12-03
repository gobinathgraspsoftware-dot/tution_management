<?php

namespace App\Services;

use App\Models\User;
use App\Models\MessageTemplate;
use App\Models\NotificationLog;
use App\Models\WhatsappQueue;
use App\Models\EmailQueue;
use App\Models\Notification;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    protected $whatsappService;
    protected $emailService;
    protected $smsService;

    public function __construct(
        WhatsappService $whatsappService,
        EmailService $emailService,
        SmsService $smsService
    ) {
        $this->whatsappService = $whatsappService;
        $this->emailService = $emailService;
        $this->smsService = $smsService;
    }

    /**
     * Send notification through specified channels
     */
    public function send(
        User $user,
        string $type,
        array $data = [],
        array $channels = null,
        string $priority = 'normal',
        ?\DateTime $scheduledAt = null
    ): array {
        $results = [];
        $channels = $channels ?? $this->getChannelsForType($type);
        $preferences = $this->getUserPreferences($user);

        foreach ($channels as $channel) {
            if (!$this->isChannelEnabled($channel)) continue;
            if (!$this->userPrefersChannel($preferences, $channel)) continue;

            try {
                $result = match ($channel) {
                    'whatsapp' => $this->queueWhatsapp($user, $type, $data, $priority, $scheduledAt),
                    'email' => $this->queueEmail($user, $type, $data, $priority, $scheduledAt),
                    'sms' => $this->queueSms($user, $type, $data, $priority, $scheduledAt),
                    default => null,
                };
                $results[$channel] = $result;
            } catch (\Exception $e) {
                Log::error("Notification failed for {$channel}: " . $e->getMessage());
                $results[$channel] = ['success' => false, 'error' => $e->getMessage()];
            }
        }

        // Create in-app notification
        $this->createInAppNotification($user, $type, $data);

        return $results;
    }

    /**
     * Send bulk notifications
     */
    public function sendBulk(
        array $users,
        string $type,
        array $data = [],
        array $channels = null,
        string $priority = 'normal'
    ): array {
        $results = [];
        foreach ($users as $user) {
            $results[$user->id] = $this->send($user, $type, $data, $channels, $priority);
        }
        return $results;
    }

    /**
     * Queue WhatsApp message
     */
    protected function queueWhatsapp(User $user, string $type, array $data, string $priority, ?\DateTime $scheduledAt): array
    {
        $phone = $this->getWhatsappNumber($user);
        if (!$phone) {
            return ['success' => false, 'error' => 'No WhatsApp number'];
        }

        $template = $this->getTemplate($type, 'whatsapp');
        $message = $this->parseTemplate($template, $data);

        $queue = WhatsappQueue::create([
            'recipient_phone' => $phone,
            'recipient_name' => $user->name,
            'message' => $message,
            'template_id' => $template?->id,
            'priority' => $priority,
            'status' => 'pending',
            'scheduled_at' => $scheduledAt,
        ]);

        $this->logNotification($user, 'whatsapp', $phone, $type, null, $message, 'pending');

        return ['success' => true, 'queue_id' => $queue->id];
    }

    /**
     * Queue Email
     */
    protected function queueEmail(User $user, string $type, array $data, string $priority, ?\DateTime $scheduledAt): array
    {
        if (!$user->email) {
            return ['success' => false, 'error' => 'No email address'];
        }

        $template = $this->getTemplate($type, 'email');
        $subject = $this->parseTemplate($template, $data, 'subject');
        $body = $this->parseTemplate($template, $data);

        $queue = EmailQueue::create([
            'recipient_email' => $user->email,
            'recipient_name' => $user->name,
            'subject' => $subject ?: $this->getDefaultSubject($type),
            'body' => $body,
            'template_id' => $template?->id,
            'priority' => $priority,
            'status' => 'pending',
            'scheduled_at' => $scheduledAt,
        ]);

        $this->logNotification($user, 'email', $user->email, $type, $subject, $body, 'pending');

        return ['success' => true, 'queue_id' => $queue->id];
    }

    /**
     * Queue SMS
     */
    protected function queueSms(User $user, string $type, array $data, string $priority, ?\DateTime $scheduledAt): array
    {
        $phone = $this->getSmsNumber($user);
        if (!$phone) {
            return ['success' => false, 'error' => 'No phone number'];
        }

        $template = $this->getTemplate($type, 'sms');
        $message = $this->parseTemplate($template, $data);

        // For SMS, we'll use the notification log directly since there's no SMS queue table
        $this->logNotification($user, 'sms', $phone, $type, null, $message, 'pending');

        return ['success' => true, 'phone' => $phone];
    }

    /**
     * Create in-app notification
     */
    protected function createInAppNotification(User $user, string $type, array $data): Notification
    {
        return Notification::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $this->getNotificationTitle($type),
            'message' => $this->getNotificationMessage($type, $data),
            'data' => $data,
        ]);
    }

    /**
     * Get message template
     */
    protected function getTemplate(string $type, string $channel): ?MessageTemplate
    {
        return MessageTemplate::where('category', $type)
            ->where(function ($q) use ($channel) {
                $q->where('channel', $channel)->orWhere('channel', 'all');
            })
            ->where('is_active', true)
            ->first();
    }

    /**
     * Parse template with variables
     */
    protected function parseTemplate(?MessageTemplate $template, array $data, string $field = 'message_body'): string
    {
        if (!$template) {
            return $data['message'] ?? '';
        }

        $content = $field === 'subject' ? ($template->subject ?? '') : $template->message_body;

        foreach ($data as $key => $value) {
            $content = str_replace("{{$key}}", $value, $content);
        }

        // Remove any unparsed placeholders
        $content = preg_replace('/\{[a-z_]+\}/', '', $content);

        return trim($content);
    }

    /**
     * Log notification
     */
    protected function logNotification(
        User $user,
        string $channel,
        string $recipient,
        string $type,
        ?string $subject,
        string $message,
        string $status
    ): NotificationLog {
        return NotificationLog::create([
            'user_id' => $user->id,
            'channel' => $channel,
            'recipient' => $recipient,
            'type' => $type,
            'subject' => $subject,
            'message' => $message,
            'status' => $status,
        ]);
    }

    /**
     * Get channels for notification type
     */
    protected function getChannelsForType(string $type): array
    {
        return config("notification.types.{$type}.channels", ['whatsapp', 'email']);
    }

    /**
     * Check if channel is enabled
     */
    protected function isChannelEnabled(string $channel): bool
    {
        return config("notification.{$channel}.enabled", false);
    }

    /**
     * Get user notification preferences
     */
    protected function getUserPreferences(User $user): array
    {
        // Check if user has parent profile with preferences
        if ($user->parent && $user->parent->notification_preference) {
            return $user->parent->notification_preference;
        }
        return ['whatsapp' => true, 'email' => true, 'sms' => true];
    }

    /**
     * Check if user prefers channel
     */
    protected function userPrefersChannel(array $preferences, string $channel): bool
    {
        return $preferences[$channel] ?? true;
    }

    /**
     * Get WhatsApp number from user
     */
    protected function getWhatsappNumber(User $user): ?string
    {
        // Try parent's WhatsApp number first
        if ($user->parent && $user->parent->whatsapp_number) {
            return $this->formatPhoneNumber($user->parent->whatsapp_number);
        }
        // Fall back to user's phone
        if ($user->phone) {
            return $this->formatPhoneNumber($user->phone);
        }
        return null;
    }

    /**
     * Get SMS number from user
     */
    protected function getSmsNumber(User $user): ?string
    {
        if ($user->phone) {
            return $this->formatPhoneNumber($user->phone);
        }
        if ($user->parent && $user->parent->whatsapp_number) {
            return $this->formatPhoneNumber($user->parent->whatsapp_number);
        }
        return null;
    }

    /**
     * Format phone number with country code
     */
    protected function formatPhoneNumber(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (str_starts_with($phone, '0')) {
            $phone = config('notification.whatsapp.default_country_code') . substr($phone, 1);
        }

        if (!str_starts_with($phone, '+')) {
            $phone = '+' . $phone;
        }

        return $phone;
    }

    /**
     * Get notification title based on type
     */
    protected function getNotificationTitle(string $type): string
    {
        return match ($type) {
            'payment_reminder' => 'Payment Reminder',
            'welcome' => 'Welcome to Arena Matriks',
            'attendance' => 'Attendance Update',
            'exam_result' => 'Exam Results Available',
            'announcement' => 'New Announcement',
            'trial_class' => 'Trial Class Scheduled',
            'enrollment' => 'Enrollment Confirmation',
            default => 'Notification',
        };
    }

    /**
     * Get notification message based on type
     */
    protected function getNotificationMessage(string $type, array $data): string
    {
        return match ($type) {
            'payment_reminder' => "Payment of RM{$data['amount']} is due on {$data['due_date']}",
            'welcome' => "Welcome {$data['student_name']}! Your account has been activated.",
            'attendance' => "{$data['student_name']} was marked {$data['attendance_status']} on {$data['attendance_date']}",
            'exam_result' => "Results for {$data['exam_name']} are now available",
            'announcement' => $data['message'] ?? 'You have a new announcement',
            'trial_class' => "Trial class scheduled for {$data['trial_date']} at {$data['trial_time']}",
            'enrollment' => "Enrollment confirmed for {$data['class_name']}",
            default => $data['message'] ?? 'You have a new notification',
        };
    }

    /**
     * Get default email subject
     */
    protected function getDefaultSubject(string $type): string
    {
        return match ($type) {
            'payment_reminder' => 'Payment Reminder - Arena Matriks',
            'welcome' => 'Welcome to Arena Matriks Edu Group',
            'attendance' => 'Attendance Notification',
            'exam_result' => 'Exam Results Available',
            'announcement' => 'Important Announcement',
            'trial_class' => 'Trial Class Confirmation',
            'enrollment' => 'Enrollment Confirmation',
            default => 'Notification from Arena Matriks',
        };
    }

    /**
     * Get notification statistics
     */
    public function getStatistics(string $period = 'today'): array
    {
        $query = NotificationLog::query();

        switch ($period) {
            case 'today':
                $query->whereDate('created_at', today());
                break;
            case 'week':
                $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case 'month':
                $query->whereMonth('created_at', now()->month);
                break;
        }

        return [
            'total' => $query->count(),
            'sent' => (clone $query)->where('status', 'sent')->count(),
            'delivered' => (clone $query)->where('status', 'delivered')->count(),
            'failed' => (clone $query)->where('status', 'failed')->count(),
            'pending' => (clone $query)->where('status', 'pending')->count(),
            'by_channel' => [
                'whatsapp' => (clone $query)->where('channel', 'whatsapp')->count(),
                'email' => (clone $query)->where('channel', 'email')->count(),
                'sms' => (clone $query)->where('channel', 'sms')->count(),
            ],
        ];
    }

    

}
