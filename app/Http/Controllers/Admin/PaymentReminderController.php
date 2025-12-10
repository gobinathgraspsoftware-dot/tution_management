<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\PaymentReminder;
use App\Models\Student;
use App\Models\Setting;
use App\Services\PaymentReminderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PaymentReminderController extends Controller
{
    protected $reminderService;

    public function __construct(PaymentReminderService $reminderService)
    {
        $this->reminderService = $reminderService;
    }

    /**
     * Display reminders dashboard
     */
    public function index(Request $request)
    {
        $query = PaymentReminder::with(['invoice.student.user', 'student.user']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('reminder_type', $request->type);
        }

        // Filter by channel
        if ($request->filled('channel')) {
            $query->where('channel', $request->channel);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('scheduled_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('scheduled_date', '<=', $request->date_to);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('invoice', function($q2) use ($search) {
                    $q2->where('invoice_number', 'like', "%{$search}%");
                })
                ->orWhereHas('student.user', function($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%");
                });
            });
        }

        $reminders = $query->orderBy('scheduled_date', 'desc')->paginate(20);

        // Get statistics
        $statistics = $this->reminderService->getStatistics();

        // Get pending reminders for today
        $todayReminders = PaymentReminder::forToday()
            ->whereIn('status', ['scheduled', 'pending'])
            ->count();

        return view('admin.reminders.index', compact('reminders', 'statistics', 'todayReminders'));
    }

    /**
     * Show reminder settings
     */
    public function settings()
    {
        $settings = [
            'reminder_enabled' => Setting::get('payment_reminder_enabled', true),
            'reminder_days' => PaymentReminder::getReminderDays(),
            'default_channel' => Setting::get('payment_reminder_channel', 'whatsapp'),
            'max_retry_attempts' => config('payment_reminders.max_retry_attempts', 3),
            'retry_delay_hours' => config('payment_reminders.retry_delay_hours', 2),
            'auto_overdue_reminder' => Setting::get('auto_overdue_reminder', true),
            'overdue_reminder_interval' => Setting::get('overdue_reminder_interval', 7),
        ];

        // Get reminder templates
        $templates = [
            'first' => Setting::get('reminder_template_first'),
            'second' => Setting::get('reminder_template_second'),
            'final' => Setting::get('reminder_template_final'),
            'overdue' => Setting::get('reminder_template_overdue'),
        ];

        return view('admin.reminders.settings', compact('settings', 'templates'));
    }

    /**
     * Update reminder settings
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'reminder_enabled' => 'boolean',
            'default_channel' => 'in:whatsapp,email,sms',
            'auto_overdue_reminder' => 'boolean',
            'overdue_reminder_interval' => 'integer|min:1|max:30',
        ]);

        Setting::set('payment_reminder_enabled', $request->boolean('reminder_enabled'));
        Setting::set('payment_reminder_channel', $request->default_channel);
        Setting::set('auto_overdue_reminder', $request->boolean('auto_overdue_reminder'));
        Setting::set('overdue_reminder_interval', $request->overdue_reminder_interval);

        // Update templates if provided
        if ($request->filled('template_first')) {
            Setting::set('reminder_template_first', $request->template_first);
        }
        if ($request->filled('template_second')) {
            Setting::set('reminder_template_second', $request->template_second);
        }
        if ($request->filled('template_final')) {
            Setting::set('reminder_template_final', $request->template_final);
        }
        if ($request->filled('template_overdue')) {
            Setting::set('reminder_template_overdue', $request->template_overdue);
        }

        return back()->with('success', 'Reminder settings updated successfully.');
    }

    /**
     * Display reminder logs
     */
    public function logs(Request $request)
    {
        $query = PaymentReminder::with(['invoice.student.user', 'createdBy'])
            ->whereIn('status', ['sent', 'delivered', 'failed']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by channel
        if ($request->filled('channel')) {
            $query->where('channel', $request->channel);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('sent_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('sent_at', '<=', $request->date_to);
        }

        $logs = $query->orderBy('sent_at', 'desc')->paginate(25);

        // Statistics
        $stats = [
            'total_sent' => PaymentReminder::sent()->count(),
            'total_delivered' => PaymentReminder::where('status', 'delivered')->count(),
            'total_failed' => PaymentReminder::failed()->count(),
            'success_rate' => $this->calculateSuccessRate(),
        ];

        return view('admin.reminders.logs', compact('logs', 'stats'));
    }

    /**
     * Schedule monthly reminders
     */
    public function scheduleMonthly(Request $request)
    {
        $month = $request->filled('month')
            ? Carbon::parse($request->month)
            : Carbon::now();

        try {
            $results = $this->reminderService->scheduleMonthlyReminders($month);

            $message = "Scheduled {$results['scheduled']} reminders for {$month->format('F Y')}.";
            if ($results['skipped'] > 0) {
                $message .= " Skipped {$results['skipped']} (already exist).";
            }

            return back()->with('success', $message);

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to schedule reminders: ' . $e->getMessage());
        }
    }

    /**
     * Send reminders now (manual trigger)
     */
    public function sendNow()
    {
        try {
            $results = $this->reminderService->sendDueReminders();

            $message = "Sent {$results['sent']} reminders.";
            if ($results['failed'] > 0) {
                $message .= " Failed: {$results['failed']}.";
            }

            return back()->with($results['failed'] > 0 ? 'warning' : 'success', $message);

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to send reminders: ' . $e->getMessage());
        }
    }

    /**
     * Send follow-up reminder for specific invoice
     */
    public function sendFollowUp(Request $request, Invoice $invoice)
    {
        $request->validate([
            'custom_message' => 'nullable|string|max:1000',
        ]);

        try {
            $this->reminderService->sendFollowUpReminder(
                $invoice,
                $request->custom_message
            );

            return back()->with('success', 'Follow-up reminder sent successfully.');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to send follow-up: ' . $e->getMessage());
        }
    }

    /**
     * Send bulk reminders for overdue invoices
     */
    public function sendOverdueReminders()
    {
        try {
            $results = $this->reminderService->sendOverdueReminders();

            $message = "Sent {$results['sent']} overdue reminders.";
            if ($results['skipped'] > 0) {
                $message .= " Skipped {$results['skipped']} (recently reminded).";
            }

            return back()->with('success', $message);

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to send overdue reminders: ' . $e->getMessage());
        }
    }

    /**
     * Retry failed reminders
     */
    public function retryFailed()
    {
        try {
            $results = $this->reminderService->retryFailedReminders();

            $message = "Retried {$results['retried']} failed reminders.";

            return back()->with('success', $message);

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to retry reminders: ' . $e->getMessage());
        }
    }

    /**
     * Cancel a scheduled reminder
     */
    public function cancel(PaymentReminder $reminder)
    {
        if (!in_array($reminder->status, ['scheduled', 'pending'])) {
            return back()->with('error', 'Only scheduled or pending reminders can be cancelled.');
        }

        $reminder->cancel();

        return back()->with('success', 'Reminder cancelled successfully.');
    }

    /**
     * Bulk cancel reminders
     */
    public function bulkCancel(Request $request)
    {
        $request->validate([
            'reminder_ids' => 'required|array',
            'reminder_ids.*' => 'exists:payment_reminders,id',
        ]);

        $cancelled = PaymentReminder::whereIn('id', $request->reminder_ids)
            ->whereIn('status', ['scheduled', 'pending'])
            ->update(['status' => 'cancelled']);

        return back()->with('success', "Cancelled {$cancelled} reminders.");
    }

    /**
     * View reminder details
     */
    public function show(PaymentReminder $reminder)
    {
        $reminder->load([
            'invoice.student.user',
            'invoice.student.parent.user',
            'invoice.enrollment.package',
            'installment',
            'createdBy',
        ]);

        return view('admin.reminders.show', compact('reminder'));
    }

    /**
     * Resend a specific reminder
     */
    public function resend(PaymentReminder $reminder)
    {
        if ($reminder->invoice && $reminder->invoice->isPaid()) {
            return back()->with('error', 'Cannot resend reminder for a paid invoice.');
        }

        try {
            $reminder->resetForRetry();
            $this->reminderService->sendReminder($reminder);

            return back()->with('success', 'Reminder resent successfully.');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to resend reminder: ' . $e->getMessage());
        }
    }

    /**
     * Get upcoming scheduled reminders
     */
    public function upcoming()
    {
        $upcomingReminders = PaymentReminder::with(['invoice.student.user', 'invoice.student.parent.user'])
            ->scheduled()
            ->whereDate('scheduled_date', '>=', today())
            ->whereDate('scheduled_date', '<=', today()->addDays(7))
            ->orderBy('scheduled_date', 'asc')
            ->get()
            ->groupBy(function($reminder) {
                return $reminder->scheduled_date->format('Y-m-d');
            });

        return view('admin.reminders.upcoming', compact('upcomingReminders'));
    }

    /**
     * Export reminder logs
     */
    public function export(Request $request)
    {
        $query = PaymentReminder::with(['invoice.student.user']);

        if ($request->filled('date_from')) {
            $query->whereDate('scheduled_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('scheduled_date', '<=', $request->date_to);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $reminders = $query->orderBy('scheduled_date', 'desc')->get();

        $filename = 'payment_reminders_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($reminders) {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'Invoice #', 'Student', 'Type', 'Channel', 'Scheduled Date',
                'Sent At', 'Status', 'Recipient', 'Attempts', 'Error'
            ]);

            foreach ($reminders as $reminder) {
                fputcsv($file, [
                    $reminder->invoice?->invoice_number ?? 'N/A',
                    $reminder->invoice?->student?->user?->name ?? 'N/A',
                    ucfirst($reminder->reminder_type),
                    ucfirst($reminder->channel),
                    $reminder->scheduled_date?->format('Y-m-d'),
                    $reminder->sent_at?->format('Y-m-d H:i:s'),
                    ucfirst($reminder->status),
                    $reminder->channel === 'whatsapp'
                        ? $reminder->recipient_phone
                        : $reminder->recipient_email,
                    $reminder->attempts,
                    $reminder->error_message,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Calculate success rate
     */
    protected function calculateSuccessRate(): float
    {
        $total = PaymentReminder::whereIn('status', ['sent', 'delivered', 'failed'])->count();
        $successful = PaymentReminder::whereIn('status', ['sent', 'delivered'])->count();

        if ($total === 0) {
            return 0;
        }

        return round(($successful / $total) * 100, 1);
    }

    /**
     * API endpoint for scheduler to trigger reminders
     */
    public function triggerScheduler()
    {
        $results = [
            'date' => now()->toDateTimeString(),
            'actions' => [],
        ];

        // Send due reminders
        $sendResults = $this->reminderService->sendDueReminders();
        $results['actions']['send_due'] = $sendResults;

        // Retry failed
        $retryResults = $this->reminderService->retryFailedReminders();
        $results['actions']['retry_failed'] = $retryResults;

        return response()->json([
            'success' => true,
            'results' => $results,
        ]);
    }
}
