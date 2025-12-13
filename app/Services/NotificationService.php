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
        WhatsAppService $whatsappService,
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

    /**
     * Send WhatsApp message via Ultra Messenger API.
     */
    public function sendWhatsApp($phoneNumber, $message, $templateId = null)
    {
        try {
            // Clean phone number (remove spaces, dashes, etc.)
            $phoneNumber = preg_replace('/[^0-9+]/', '', $phoneNumber);

            // Ensure Malaysian format (+60)
            if (!str_starts_with($phoneNumber, '+')) {
                if (str_starts_with($phoneNumber, '0')) {
                    $phoneNumber = '+60' . substr($phoneNumber, 1);
                } else if (str_starts_with($phoneNumber, '60')) {
                    $phoneNumber = '+' . $phoneNumber;
                } else {
                    $phoneNumber = '+60' . $phoneNumber;
                }
            }

            // Ultra Messenger API Configuration
            $apiUrl = config('services.ultramessenger.api_url', 'https://api.ultramsg.com');
            $instanceId = config('services.ultramessenger.instance_id');
            $token = config('services.ultramessenger.token');

            if (!$instanceId || !$token) {
                throw new \Exception('Ultra Messenger API credentials not configured');
            }

            // Send request to Ultra Messenger
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post("{$apiUrl}/{$instanceId}/messages/chat", [
                'token' => $token,
                'to' => $phoneNumber,
                'body' => $message,
            ]);

            // Log notification
            $this->logNotification(
                null,
                'whatsapp',
                $phoneNumber,
                'whatsapp_message',
                null,
                $message,
                $templateId,
                $response->successful() ? 'sent' : 'failed',
                $response->json(),
                $response->successful() ? null : $response->body()
            );

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('WhatsApp sending failed: ' . $e->getMessage());

            $this->logNotification(
                null,
                'whatsapp',
                $phoneNumber,
                'whatsapp_message',
                null,
                $message,
                $templateId,
                'failed',
                null,
                $e->getMessage()
            );

            return false;
        }
    }

    /**
     * Send Email notification.
     */
    public function sendEmail($to, $subject, $view, $data = [])
    {
        try {
            Mail::send($view, $data, function ($message) use ($to, $subject) {
                $message->to($to)
                        ->subject($subject)
                        ->from(config('mail.from.address'), config('mail.from.name'));
            });

            $this->logNotification(
                $data['recipient']->id ?? null,
                'email',
                $to,
                'email_message',
                $subject,
                $view,
                null,
                'sent',
                null,
                null
            );

            return true;
        } catch (\Exception $e) {
            Log::error('Email sending failed: ' . $e->getMessage());

            $this->logNotification(
                $data['recipient']->id ?? null,
                'email',
                $to,
                'email_message',
                $subject,
                $view,
                null,
                'failed',
                null,
                $e->getMessage()
            );

            return false;
        }
    }

    /**
     * Send SMS notification.
     */
    public function sendSMS($phoneNumber, $message)
    {
        try {
            // SMS Gateway Configuration (Example: Twilio)
            $accountSid = config('services.twilio.account_sid');
            $authToken = config('services.twilio.auth_token');
            $fromNumber = config('services.twilio.from_number');

            if (!$accountSid || !$authToken) {
                throw new \Exception('SMS Gateway credentials not configured');
            }

            // Clean phone number
            $phoneNumber = preg_replace('/[^0-9+]/', '', $phoneNumber);
            if (!str_starts_with($phoneNumber, '+')) {
                $phoneNumber = '+60' . ltrim($phoneNumber, '0');
            }

            // Send via Twilio (example)
            $response = Http::withBasicAuth($accountSid, $authToken)
                ->asForm()
                ->post("https://api.twilio.com/2010-04-01/Accounts/{$accountSid}/Messages.json", [
                    'From' => $fromNumber,
                    'To' => $phoneNumber,
                    'Body' => $message,
                ]);

            $this->logNotification(
                null,
                'sms',
                $phoneNumber,
                'sms_message',
                null,
                $message,
                null,
                $response->successful() ? 'sent' : 'failed',
                $response->json(),
                $response->successful() ? null : $response->body()
            );

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('SMS sending failed: ' . $e->getMessage());

            $this->logNotification(
                null,
                'sms',
                $phoneNumber,
                'sms_message',
                null,
                $message,
                null,
                'failed',
                null,
                $e->getMessage()
            );

            return false;
        }
    }

    // =========================================================================
    // MATERIAL NOTIFICATION METHODS
    // =========================================================================

    /**
     * Send notification when physical material is collected.
     */
    public function sendMaterialCollectionNotification($recipient, $student, $material)
    {
        // WhatsApp Notification
        if ($recipient->phone) {
            $message = "Material Collection Alert\n\n";
            $message .= "Student: {$student->user->name}\n";
            $message .= "Material: {$material->name}\n";
            $message .= "Subject: {$material->subject->name}\n";
            $message .= "Collected: " . now()->format('M d, Y H:i') . "\n\n";
            $message .= "This material has been collected successfully.";

            $this->sendWhatsApp($recipient->phone, $message);
        }

        // Email Notification
        if ($recipient->email) {
            $this->sendEmail(
                $recipient->email,
                'Material Collection Confirmation',
                'emails.material-collection',
                [
                    'recipient' => $recipient,
                    'student' => $student,
                    'material' => $material,
                    'collected_at' => now(),
                ]
            );
        }

        // In-app Notification
        \App\Models\Notification::create([
            'user_id' => $recipient->id,
            'type' => 'material_collection',
            'title' => 'Material Collected',
            'message' => "{$student->user->name} has collected {$material->name}",
            'data' => json_encode([
                'student_id' => $student->id,
                'material_id' => $material->id,
                'type' => 'physical_material',
            ]),
        ]);
    }

    /**
     * Send notification about new physical material availability.
     */
    public function sendNewMaterialNotification($recipient, $material)
    {
        // WhatsApp Notification
        if ($recipient->phone) {
            $message = "New Study Material Available!\n\n";
            $message .= "Material: {$material->name}\n";
            $message .= "Subject: {$material->subject->name}\n";

            if ($material->grade_level) {
                $message .= "Grade: {$material->grade_level}\n";
            }

            if ($material->month && $material->year) {
                $message .= "Period: {$material->month} {$material->year}\n";
            }

            $message .= "\nPlease collect this material from the centre.";

            $this->sendWhatsApp($recipient->phone, $message);
        }

        // Email Notification
        if ($recipient->email) {
            $this->sendEmail(
                $recipient->email,
                'New Study Material Available',
                'emails.new-material',
                [
                    'recipient' => $recipient,
                    'material' => $material,
                ]
            );
        }

        // In-app Notification
        \App\Models\Notification::create([
            'user_id' => $recipient->id,
            'type' => 'new_material',
            'title' => 'New Material Available',
            'message' => "New material '{$material->name}' is now available for collection",
            'data' => json_encode([
                'material_id' => $material->id,
                'type' => 'physical_material',
            ]),
        ]);
    }

    /**
     * Send notification when digital material is uploaded (for students).
     */
    public function sendDigitalMaterialNotification($recipient, $material)
    {
        // WhatsApp Notification
        if ($recipient->phone) {
            $message = "New Study Material Uploaded!\n\n";
            $message .= "Title: {$material->title}\n";
            $message .= "Class: {$material->class->name}\n";
            $message .= "Subject: {$material->subject->name}\n";
            $message .= "Type: " . ucfirst($material->type) . "\n";
            $message .= "Teacher: {$material->teacher->user->name}\n\n";
            $message .= "Access: ";
            $message .= $material->access_type == 'view_only' ? 'View Only' : 'Downloadable';
            $message .= "\n\nLogin to view: " . url('/student/materials');

            $this->sendWhatsApp($recipient->phone, $message);
        }

        // Email Notification
        if ($recipient->email) {
            $this->sendEmail(
                $recipient->email,
                'New Digital Material Available',
                'emails.digital-material',
                [
                    'recipient' => $recipient,
                    'material' => $material,
                    'url' => url('/student/materials'),
                ]
            );
        }

        // In-app Notification
        \App\Models\Notification::create([
            'user_id' => $recipient->id,
            'type' => 'digital_material',
            'title' => 'New Material: ' . $material->title,
            'message' => "New {$material->type} uploaded for {$material->class->name}",
            'data' => json_encode([
                'material_id' => $material->id,
                'class_id' => $material->class_id,
                'type' => 'digital_material',
                'url' => '/student/materials',
            ]),
        ]);
    }

    /**
     * Send notification when teacher material is approved.
     */
    public function sendMaterialApprovalNotification($teacher, $material)
    {
        // WhatsApp Notification
        if ($teacher->user->phone) {
            $message = "Material Approved! ✓\n\n";
            $message .= "Your material has been approved:\n";
            $message .= "Title: {$material->title}\n";
            $message .= "Class: {$material->class->name}\n";
            $message .= "Status: Published\n\n";
            $message .= "Students can now access this material.";

            $this->sendWhatsApp($teacher->user->phone, $message);
        }

        // Email Notification
        if ($teacher->user->email) {
            $this->sendEmail(
                $teacher->user->email,
                'Material Approved',
                'emails.material-approved',
                [
                    'teacher' => $teacher,
                    'material' => $material,
                ]
            );
        }

        // In-app Notification
        \App\Models\Notification::create([
            'user_id' => $teacher->user_id,
            'type' => 'material_approved',
            'title' => 'Material Approved',
            'message' => "Your material '{$material->title}' has been approved and published",
            'data' => json_encode([
                'material_id' => $material->id,
                'type' => 'digital_material',
            ]),
        ]);
    }

    // =========================================================================
    // ATTENDANCE NOTIFICATION METHODS (For future use)
    // =========================================================================

    /**
     * Send attendance notification to parent.
     */
    public function sendAttendanceNotification($parent, $student, $attendance)
    {
        if ($parent->user->phone) {
            $status = ucfirst($attendance->status);
            $message = "Attendance Alert\n\n";
            $message .= "Student: {$student->user->name}\n";
            $message .= "Class: {$attendance->classSession->class->name}\n";
            $message .= "Date: " . $attendance->date->format('M d, Y') . "\n";
            $message .= "Status: {$status}\n";

            if ($attendance->remarks) {
                $message .= "Remarks: {$attendance->remarks}\n";
            }

            $this->sendWhatsApp($parent->user->phone, $message);
        }
    }

    // =========================================================================
    // PAYMENT NOTIFICATION METHODS (For future use)
    // =========================================================================

    /**
     * Send invoice notification.
     */
    public function sendInvoiceNotification($user, $invoice)
    {
        if ($user->phone) {
            $message = "New Invoice\n\n";
            $message .= "Invoice: {$invoice->invoice_number}\n";
            $message .= "Amount: RM " . number_format($invoice->total_amount, 2) . "\n";
            $message .= "Due Date: " . $invoice->due_date->format('M d, Y') . "\n";
            $message .= "\nPlease login to view details.";

            $this->sendWhatsApp($user->phone, $message);
        }
    }

    /**
     * Send payment reminder.
     */
    public function sendPaymentReminder($user, $invoice, $daysOverdue)
    {
        if ($user->phone) {
            $message = "Payment Reminder\n\n";
            $message .= "Invoice: {$invoice->invoice_number}\n";
            $message .= "Amount: RM " . number_format($invoice->total_amount, 2) . "\n";
            $message .= "Overdue: {$daysOverdue} days\n";
            $message .= "\nPlease make payment as soon as possible.";

            $this->sendWhatsApp($user->phone, $message);
        }
    }

    /**
     * Send payment confirmation.
     */
    public function sendPaymentConfirmation($user, $payment)
    {
        if ($user->phone) {
            $message = "Payment Received ✓\n\n";
            $message .= "Receipt: {$payment->receipt_number}\n";
            $message .= "Amount: RM " . number_format($payment->amount, 2) . "\n";
            $message .= "Method: " . ucfirst($payment->payment_method) . "\n";
            $message .= "Date: " . $payment->payment_date->format('M d, Y') . "\n";
            $message .= "\nThank you for your payment!";

            $this->sendWhatsApp($user->phone, $message);
        }
    }

}
