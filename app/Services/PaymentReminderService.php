<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Installment;
use App\Models\PaymentReminder;
use App\Models\Student;
use App\Models\Setting;
use App\Models\WhatsappQueue;
use App\Models\NotificationLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PaymentReminderService
{
    protected $whatsappService;
    protected $emailService;

    public function __construct(
        WhatsappService $whatsappService,
        EmailService $emailService
    ) {
        $this->whatsappService = $whatsappService;
        $this->emailService = $emailService;
    }

    /**
     * Reminder days configuration (10th, 18th, 24th of month)
     */
    const REMINDER_DAYS = [
        'first' => 10,
        'second' => 18,
        'final' => 24,
    ];

    /**
     * Schedule reminders for current month
     */
    public function scheduleMonthlyReminders(?Carbon $forMonth = null): array
    {
        $forMonth = $forMonth ?? Carbon::now();
        $results = [
            'scheduled' => 0,
            'skipped' => 0,
            'errors' => [],
        ];

        // Get all unpaid invoices
        $invoices = Invoice::with(['student.user', 'student.parent.user'])
            ->unpaid()
            ->whereHas('student', function($q) {
                $q->where('approval_status', 'approved');
            })
            ->get();

        foreach ($invoices as $invoice) {
            foreach (self::REMINDER_DAYS as $type => $day) {
                $scheduledDate = Carbon::create(
                    $forMonth->year,
                    $forMonth->month,
                    min($day, $forMonth->daysInMonth)
                );

                // Skip if date already passed
                if ($scheduledDate->isPast()) {
                    continue;
                }

                // Check if reminder already exists
                $exists = PaymentReminder::where('invoice_id', $invoice->id)
                    ->where('reminder_type', $type)
                    ->whereMonth('scheduled_date', $forMonth->month)
                    ->whereYear('scheduled_date', $forMonth->year)
                    ->exists();

                if ($exists) {
                    $results['skipped']++;
                    continue;
                }

                try {
                    $this->createReminder($invoice, $type, $scheduledDate);
                    $results['scheduled']++;
                } catch (\Exception $e) {
                    $results['errors'][] = [
                        'invoice_id' => $invoice->id,
                        'type' => $type,
                        'error' => $e->getMessage(),
                    ];
                }
            }
        }

        return $results;
    }

    /**
     * Create a single reminder
     */
    public function createReminder(
        Invoice $invoice,
        string $type,
        Carbon $scheduledDate,
        string $channel = 'whatsapp'
    ): PaymentReminder {
        $student = $invoice->student;
        $parent = $student->parent;

        // Get recipient details (prefer parent if exists)
        $recipientPhone = $parent?->user?->phone ?? $student->user->phone ?? null;
        $recipientEmail = $parent?->user?->email ?? $student->user->email ?? null;

        if (!$recipientPhone && $channel === 'whatsapp') {
            throw new \Exception('No phone number available for WhatsApp reminder.');
        }

        return PaymentReminder::create([
            'invoice_id' => $invoice->id,
            'student_id' => $student->id,
            'reminder_type' => $type,
            'reminder_day' => self::REMINDER_DAYS[$type] ?? null,
            'scheduled_date' => $scheduledDate,
            'channel' => $channel,
            'status' => 'scheduled',
            'recipient_phone' => $recipientPhone,
            'recipient_email' => $recipientEmail,
            'attempts' => 0,
        ]);
    }

    /**
     * Send due reminders (called by scheduler)
     */
    public function sendDueReminders(): array
    {
        $results = [
            'sent' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        // Get reminders due to be sent
        $reminders = PaymentReminder::with(['invoice.student.user', 'invoice.student.parent.user'])
            ->dueToSend()
            ->whereHas('invoice', function($q) {
                $q->unpaid(); // Only for unpaid invoices
            })
            ->limit(50) // Process in batches
            ->get();

        foreach ($reminders as $reminder) {
            try {
                $this->sendReminder($reminder);
                $results['sent']++;
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = [
                    'reminder_id' => $reminder->id,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Send a single reminder
     */
    public function sendReminder(PaymentReminder $reminder): bool
    {
        $invoice = $reminder->invoice;

        // Check if invoice is still unpaid
        if ($invoice->isPaid()) {
            $reminder->cancel();
            return false;
        }

        // Build message content
        $message = $this->buildReminderMessage($reminder);
        $reminder->update(['message_content' => $message]);

        $success = false;

        switch ($reminder->channel) {
            case PaymentReminder::CHANNEL_WHATSAPP:
                $success = $this->sendWhatsAppReminder($reminder, $message);
                break;
            case PaymentReminder::CHANNEL_EMAIL:
                $success = $this->sendEmailReminder($reminder, $message);
                break;
            case PaymentReminder::CHANNEL_SMS:
                $success = $this->sendSmsReminder($reminder, $message);
                break;
        }

        // Update invoice reminder count
        if ($success) {
            $invoice->sendReminder();
        }

        return $success;
    }

    /**
     * Send WhatsApp reminder
     */
    protected function sendWhatsAppReminder(PaymentReminder $reminder, string $message): bool
    {
        if (!$reminder->recipient_phone) {
            $reminder->markAsFailed('No phone number available');
            return false;
        }

        try {
            // Queue the message
            WhatsappQueue::create([
                'recipient_phone' => $reminder->recipient_phone,
                'message' => $message,
                'template_name' => 'payment_reminder',
                'status' => 'pending',
                'priority' => $reminder->reminder_type === 'final' ? 2 : 1,
                'scheduled_at' => now(),
                'reference_type' => 'payment_reminder',
                'reference_id' => $reminder->id,
            ]);

            // Log notification
            NotificationLog::create([
                'channel' => 'whatsapp',
                'type' => 'payment_reminder',
                'recipient' => $reminder->recipient_phone,
                'subject' => 'Payment Reminder - ' . $reminder->invoice->invoice_number,
                'message' => $message,
                'status' => 'pending',
                'related_type' => PaymentReminder::class,
                'related_id' => $reminder->id,
            ]);

            $reminder->markAsSent('Queued for delivery');
            return true;

        } catch (\Exception $e) {
            $reminder->markAsFailed($e->getMessage());
            Log::error('WhatsApp reminder failed', [
                'reminder_id' => $reminder->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Send Email reminder
     */
    protected function sendEmailReminder(PaymentReminder $reminder, string $message): bool
    {
        if (!$reminder->recipient_email) {
            $reminder->markAsFailed('No email address available');
            return false;
        }

        try {
            $invoice = $reminder->invoice;
            $student = $invoice->student;

            $subject = $this->getEmailSubject($reminder);

            $this->emailService->send(
                $reminder->recipient_email,
                $subject,
                'emails.payment-reminder',
                [
                    'reminder' => $reminder,
                    'invoice' => $invoice,
                    'student' => $student,
                    'message' => $message,
                ]
            );

            $reminder->markAsSent('Email sent');
            return true;

        } catch (\Exception $e) {
            $reminder->markAsFailed($e->getMessage());
            Log::error('Email reminder failed', [
                'reminder_id' => $reminder->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Send SMS reminder
     */
    protected function sendSmsReminder(PaymentReminder $reminder, string $message): bool
    {
        // SMS implementation placeholder
        $reminder->markAsFailed('SMS not implemented');
        return false;
    }

    /**
     * Build reminder message based on type
     */
    protected function buildReminderMessage(PaymentReminder $reminder): string
    {
        $invoice = $reminder->invoice;
        $student = $invoice->student;
        $parent = $student->parent;

        $parentName = $parent?->user?->name ?? $student->user->name;
        $studentName = $student->user->name;
        $amount = number_format($invoice->balance, 2);
        $dueDate = $invoice->due_date?->format('d M Y') ?? 'N/A';
        $invoiceNumber = $invoice->invoice_number;

        // Get payment link if online payment is enabled
        $paymentLink = route('online-payment.pay', $invoice->id);

        $templates = [
            'first' => "🔔 *Payment Reminder*\n\nDear {$parentName},\n\nThis is a friendly reminder that payment of *RM{$amount}* for {$studentName}'s tuition is due on *{$dueDate}*.\n\n📋 Invoice: {$invoiceNumber}\n\n💳 Pay online: {$paymentLink}\n\nPlease make payment at your earliest convenience.\n\nThank you,\nArena Matriks Edu Group",

            'second' => "⚠️ *2nd Payment Reminder*\n\nDear {$parentName},\n\nWe noticed that payment of *RM{$amount}* for {$studentName} is still pending.\n\n📋 Invoice: {$invoiceNumber}\n📅 Due Date: {$dueDate}\n\n💳 Pay now: {$paymentLink}\n\nPlease settle this payment to avoid any disruption to classes.\n\nIf you have already made payment, please ignore this message.\n\nThank you,\nArena Matriks Edu Group",

            'final' => "🚨 *Final Payment Notice*\n\nDear {$parentName},\n\nThis is our FINAL reminder regarding the outstanding payment of *RM{$amount}* for {$studentName}.\n\n📋 Invoice: {$invoiceNumber}\n📅 Due Date: {$dueDate}\n\n⚠️ Please settle immediately to avoid late fees and service interruption.\n\n💳 Pay now: {$paymentLink}\n\nFor any payment issues, please contact us.\n\nArena Matriks Edu Group\n📞 Contact: [Centre Phone]",

            'overdue' => "❌ *OVERDUE Payment Notice*\n\nDear {$parentName},\n\nPayment of *RM{$amount}* for {$studentName} is now *OVERDUE*.\n\n📋 Invoice: {$invoiceNumber}\n📅 Was due: {$dueDate}\n⏰ Days overdue: {$invoice->days_overdue}\n\n⚠️ Please settle this immediately to avoid further action.\n\n💳 Pay now: {$paymentLink}\n\nArena Matriks Edu Group",

            'follow_up' => "📢 *Payment Follow-up*\n\nDear {$parentName},\n\nFollowing up on the outstanding payment for {$studentName}.\n\nAmount: *RM{$amount}*\nInvoice: {$invoiceNumber}\n\n💳 {$paymentLink}\n\nPlease contact us if you need to discuss payment arrangements.\n\nArena Matriks Edu Group",

            'installment' => "🔔 *Installment Payment Reminder*\n\nDear {$parentName},\n\nReminder for installment payment for {$studentName}.\n\nAmount Due: *RM{$amount}*\nInvoice: {$invoiceNumber}\n\n💳 {$paymentLink}\n\nThank you,\nArena Matriks Edu Group",
        ];

        return $templates[$reminder->reminder_type] ?? $templates['first'];
    }

    /**
     * Get email subject based on reminder type
     */
    protected function getEmailSubject(PaymentReminder $reminder): string
    {
        $subjects = [
            'first' => 'Payment Reminder - ' . $reminder->invoice->invoice_number,
            'second' => '2nd Payment Reminder - ' . $reminder->invoice->invoice_number,
            'final' => 'FINAL Payment Notice - ' . $reminder->invoice->invoice_number,
            'overdue' => 'OVERDUE Payment - ' . $reminder->invoice->invoice_number,
            'follow_up' => 'Payment Follow-up - ' . $reminder->invoice->invoice_number,
            'installment' => 'Installment Payment Due - ' . $reminder->invoice->invoice_number,
        ];

        return $subjects[$reminder->reminder_type] ?? 'Payment Reminder';
    }

    /**
     * Send manual follow-up reminder
     */
    public function sendFollowUpReminder(Invoice $invoice, ?string $customMessage = null): PaymentReminder
    {
        $reminder = $this->createReminder(
            $invoice,
            'follow_up',
            Carbon::now(),
            'whatsapp'
        );

        if ($customMessage) {
            $reminder->update(['message_content' => $customMessage]);
            $this->sendWhatsAppReminder($reminder, $customMessage);
        } else {
            $this->sendReminder($reminder);
        }

        return $reminder->fresh();
    }

    /**
     * Send bulk reminders for overdue invoices
     */
    public function sendOverdueReminders(): array
    {
        $results = [
            'sent' => 0,
            'skipped' => 0,
            'errors' => [],
        ];

        $overdueInvoices = Invoice::overdue()
            ->with(['student.user', 'student.parent.user'])
            ->whereHas('student', function($q) {
                $q->where('approval_status', 'approved');
            })
            ->get();

        foreach ($overdueInvoices as $invoice) {
            // Check if overdue reminder was sent recently
            $recentReminder = PaymentReminder::where('invoice_id', $invoice->id)
                ->where('reminder_type', 'overdue')
                ->where('sent_at', '>', now()->subDays(7))
                ->exists();

            if ($recentReminder) {
                $results['skipped']++;
                continue;
            }

            try {
                $reminder = $this->createReminder($invoice, 'overdue', Carbon::now());
                $this->sendReminder($reminder);
                $results['sent']++;
            } catch (\Exception $e) {
                $results['errors'][] = [
                    'invoice_id' => $invoice->id,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Get reminder statistics
     */
    public function getStatistics(?Carbon $month = null): array
    {
        $month = $month ?? Carbon::now();

        $query = PaymentReminder::whereMonth('scheduled_date', $month->month)
            ->whereYear('scheduled_date', $month->year);

        return [
            'total_scheduled' => (clone $query)->count(),
            'total_sent' => (clone $query)->sent()->count(),
            'total_failed' => (clone $query)->failed()->count(),
            'pending_today' => PaymentReminder::forToday()->scheduled()->count(),
            'by_type' => [
                'first' => (clone $query)->byType('first')->sent()->count(),
                'second' => (clone $query)->byType('second')->sent()->count(),
                'final' => (clone $query)->byType('final')->sent()->count(),
                'overdue' => (clone $query)->byType('overdue')->sent()->count(),
            ],
            'by_channel' => [
                'whatsapp' => (clone $query)->byChannel('whatsapp')->sent()->count(),
                'email' => (clone $query)->byChannel('email')->sent()->count(),
                'sms' => (clone $query)->byChannel('sms')->sent()->count(),
            ],
        ];
    }

    /**
     * Retry failed reminders
     */
    public function retryFailedReminders(): array
    {
        $results = [
            'retried' => 0,
            'skipped' => 0,
            'errors' => [],
        ];

        $failedReminders = PaymentReminder::needsRetry()
            ->whereHas('invoice', function($q) {
                $q->unpaid();
            })
            ->limit(30)
            ->get();

        foreach ($failedReminders as $reminder) {
            try {
                $reminder->resetForRetry();
                $this->sendReminder($reminder);
                $results['retried']++;
            } catch (\Exception $e) {
                $results['errors'][] = [
                    'reminder_id' => $reminder->id,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Cancel pending reminders for paid invoice
     */
    public function cancelRemindersForPaidInvoice(Invoice $invoice): int
    {
        return PaymentReminder::where('invoice_id', $invoice->id)
            ->whereIn('status', ['scheduled', 'pending'])
            ->update(['status' => 'cancelled']);
    }
}
