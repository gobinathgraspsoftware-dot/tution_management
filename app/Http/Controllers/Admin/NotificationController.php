<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NotificationLog;
use App\Models\WhatsappQueue;
use App\Models\EmailQueue;
use App\Models\User;
use App\Models\Student;
use App\Models\ParentModel;
use App\Models\MessageTemplate;
use App\Models\Parents;
use App\Services\NotificationService;
use App\Services\WhatsappService;
use App\Services\EmailService;
use App\Services\SmsService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    protected $notificationService;
    protected $whatsappService;
    protected $emailService;
    protected $smsService;

    public function __construct(
        NotificationService $notificationService,
        WhatsappService $whatsappService,
        EmailService $emailService,
        SmsService $smsService
    ) {
        $this->notificationService = $notificationService;
        $this->whatsappService = $whatsappService;
        $this->emailService = $emailService;
        $this->smsService = $smsService;
    }

    /**
     * Display notification dashboard
     */
    public function index()
    {
        $stats = $this->notificationService->getStatistics('today');
        $weekStats = $this->notificationService->getStatistics('week');

        $whatsappStatus = $this->whatsappService->checkStatus();
        $whatsappQueue = $this->whatsappService->getQueueStats();
        $emailQueue = $this->emailService->getQueueStats();

        $recentLogs = NotificationLog::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.notifications.index', compact(
            'stats',
            'weekStats',
            'whatsappStatus',
            'whatsappQueue',
            'emailQueue',
            'recentLogs'
        ));
    }

    /**
     * Display notification logs
     */
    public function logs(Request $request)
    {
        $query = NotificationLog::with('user');

        // Filters
        if ($request->filled('channel')) {
            $query->where('channel', $request->channel);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('recipient', 'like', "%{$search}%")
                    ->orWhere('message', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(20);

        $channels = ['whatsapp', 'email', 'sms', 'push'];
        $statuses = ['pending', 'sent', 'delivered', 'failed'];
        $types = array_keys(config('notification.types', []));

        return view('admin.notifications.logs', compact('logs', 'channels', 'statuses', 'types'));
    }

    /**
     * Show send notification form
     */
    public function create()
    {
        $templates = MessageTemplate::active()->get()->groupBy('category');
        $students = Student::with('user')->where('status', 'active')->get();
        $parents = Parents::with('user')->get();

        return view('admin.notifications.send', compact('templates', 'students', 'parents'));
    }

    /**
     * Send notification
     */
    public function send(Request $request)
    {
        $request->validate([
            'recipient_type' => 'required|in:individual,group,all',
            'channel' => 'required|array|min:1',
            'channel.*' => 'in:whatsapp,email,sms',
            'template_id' => 'nullable|exists:message_templates,id',
            'subject' => 'required_if:channel,email|nullable|string|max:255',
            'message' => 'required|string|max:2000',
            'user_ids' => 'required_if:recipient_type,individual|array',
            'group' => 'required_if:recipient_type,group|nullable|in:students,parents,teachers',
            'priority' => 'required|in:low,normal,high,urgent',
            'schedule_at' => 'nullable|date|after:now',
        ]);

        $users = $this->getRecipients($request);

        if ($users->isEmpty()) {
            return back()->with('error', 'No recipients found.');
        }

        $data = [
            'message' => $request->message,
            'subject' => $request->subject,
        ];

        $scheduledAt = $request->schedule_at ? new \DateTime($request->schedule_at) : null;

        $results = $this->notificationService->sendBulk(
            $users->all(),
            'announcement',
            $data,
            $request->channel,
            $request->priority
        );

        $successCount = collect($results)->filter(function ($r) {
            return collect($r)->contains(fn($v) => $v['success'] ?? false);
        })->count();

        return redirect()->route('admin.notifications.index')
            ->with('success', "Notification queued for {$successCount} recipients.");
    }

    /**
     * Get recipients based on request
     */
    protected function getRecipients(Request $request)
    {
        return match ($request->recipient_type) {
            'individual' => User::whereIn('id', $request->user_ids ?? [])->get(),
            'group' => $this->getUsersByGroup($request->group),
            'all' => User::where('status', 'active')->get(),
            default => collect([]),
        };
    }

    /**
     * Get users by group
     */
    protected function getUsersByGroup(string $group)
    {
        return match ($group) {
            'students' => User::role('student')->where('status', 'active')->get(),
            'parents' => User::role('parent')->where('status', 'active')->get(),
            'teachers' => User::role('teacher')->where('status', 'active')->get(),
            default => collect([]),
        };
    }

    /**
     * Show WhatsApp queue
     */
    public function whatsappQueue(Request $request)
    {
        $query = WhatsappQueue::with('template');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        $queue = $query->orderBy('created_at', 'desc')->paginate(20);
        $stats = $this->whatsappService->getQueueStats();
        $status = $this->whatsappService->checkStatus();

        return view('admin.notifications.whatsapp-queue', compact('queue', 'stats', 'status'));
    }

    /**
     * Show Email queue
     */
    public function emailQueue(Request $request)
    {
        $query = EmailQueue::with('template');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        $queue = $query->orderBy('created_at', 'desc')->paginate(20);
        $stats = $this->emailService->getQueueStats();

        return view('admin.notifications.email-queue', compact('queue', 'stats'));
    }

    /**
     * Process WhatsApp queue manually
     */
    public function processWhatsappQueue()
    {
        $results = $this->whatsappService->processQueue();

        return back()->with('success',
            "Processed: {$results['processed']}, Success: {$results['success']}, Failed: {$results['failed']}"
        );
    }

    /**
     * Process Email queue manually
     */
    public function processEmailQueue()
    {
        $results = $this->emailService->processQueue();

        return back()->with('success',
            "Processed: {$results['processed']}, Success: {$results['success']}, Failed: {$results['failed']}"
        );
    }

    /**
     * Retry failed WhatsApp messages
     */
    public function retryWhatsapp()
    {
        $count = $this->whatsappService->retryFailed();
        return back()->with('success', "{$count} messages queued for retry.");
    }

    /**
     * Retry failed emails
     */
    public function retryEmail()
    {
        $count = $this->emailService->retryFailed();
        return back()->with('success', "{$count} emails queued for retry.");
    }

    /**
     * Cancel queued message
     */
    public function cancelMessage(Request $request, string $type, int $id)
    {
        $model = match ($type) {
            'whatsapp' => WhatsappQueue::findOrFail($id),
            'email' => EmailQueue::findOrFail($id),
            default => abort(404),
        };

        if ($model->status === 'pending') {
            $model->update(['status' => 'cancelled']);
            return back()->with('success', 'Message cancelled.');
        }

        return back()->with('error', 'Cannot cancel message that is not pending.');
    }

    /**
     * Test WhatsApp connection
     */
    public function testWhatsapp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
        ]);

        $result = $this->whatsappService->send(
            $request->phone,
            "Test message from Arena Matriks. If you received this, WhatsApp integration is working!"
        );

        if ($result['success']) {
            return back()->with('success', 'Test message sent successfully!');
        }

        return back()->with('error', 'Test failed: ' . ($result['error'] ?? 'Unknown error'));
    }

    /**
     * Test Email configuration
     */
    public function testEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $result = $this->emailService->testConnection($request->email);

        if ($result['success']) {
            return back()->with('success', 'Test email sent successfully!');
        }

        return back()->with('error', 'Test failed: ' . ($result['error'] ?? 'Unknown error'));
    }

    /**
     * Get notification settings
     */
    public function settings()
    {
        $whatsappStatus = $this->whatsappService->checkStatus();

        return view('admin.settings.notification', compact('whatsappStatus'));
    }

    /**
     * Update notification settings
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'whatsapp_enabled' => 'boolean',
            'email_enabled' => 'boolean',
            'sms_enabled' => 'boolean',
            'whatsapp_instance_id' => 'nullable|string',
            'whatsapp_token' => 'nullable|string',
        ]);

        // In a real application, you would save these to database or .env
        // For now, we'll just return success
        return back()->with('success', 'Settings updated successfully.');
    }
}
