<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\ActivityLog;
use App\Services\StudentApprovalService;
use Illuminate\Http\Request;

class StudentApprovalController extends Controller
{
    protected $approvalService;

    public function __construct(StudentApprovalService $approvalService)
    {
        $this->approvalService = $approvalService;
    }

    /**
     * Display pending approvals queue.
     */
    public function index(Request $request)
    {
        $query = Student::with(['user', 'parent.user'])
            ->pending()
            ->orderBy('created_at', 'asc');

        // Filter by registration type
        if ($request->filled('registration_type')) {
            $query->where('registration_type', $request->registration_type);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('registration_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('registration_date', '<=', $request->date_to);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('student_id', 'like', "%{$search}%")
                    ->orWhere('ic_number', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        $pendingStudents = $query->paginate(15);

        // Get counts for dashboard
        $counts = [
            'pending' => Student::pending()->count(),
            'online' => Student::pending()->onlineRegistration()->count(),
            'offline' => Student::pending()->offlineRegistration()->count(),
            'today' => Student::pending()->whereDate('created_at', today())->count(),
        ];

        return view('admin.approvals.index', compact('pendingStudents', 'counts'));
    }

    /**
     * Show student details for approval review.
     */
    public function show(Student $student)
    {
        if (!$student->isPending()) {
            return redirect()->route('admin.approvals.index')
                ->with('error', 'This student has already been processed.');
        }

        $student->load([
            'user',
            'parent.user',
            'referredBy.user',
        ]);

        // Get similar students (potential duplicates)
        $similarStudents = Student::with('user')
            ->where('id', '!=', $student->id)
            ->where(function ($q) use ($student) {
                $q->where('ic_number', $student->ic_number)
                    ->orWhereHas('user', function ($q2) use ($student) {
                        $q2->where('email', $student->user->email)
                            ->orWhere('phone', $student->user->phone);
                    });
            })
            ->approved()
            ->get();

        return view('admin.approvals.show', compact('student', 'similarStudents'));
    }

    /**
     * Approve student registration.
     */
    public function approve(Request $request, Student $student)
    {
        if (!$student->isPending()) {
            return back()->with('error', 'This student has already been processed.');
        }

        $request->validate([
            'notes' => 'nullable|string|max:500',
            'send_welcome' => 'boolean',
            'send_whatsapp' => 'boolean',
            'send_email' => 'boolean',
        ]);

        $options = [
            'notes' => $request->notes,
            'send_welcome' => $request->boolean('send_welcome', true),
            'send_whatsapp' => $request->boolean('send_whatsapp', true),
            'send_email' => $request->boolean('send_email', true),
        ];

        $result = $this->approvalService->approve($student, auth()->user(), $options);

        if ($result['success']) {
            return redirect()->route('admin.approvals.index')
                ->with('success', $result['message']);
        }

        return back()->with('error', $result['message']);
    }

    /**
     * Reject student registration.
     */
    public function reject(Request $request, Student $student)
    {
        if (!$student->isPending()) {
            return back()->with('error', 'This student has already been processed.');
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
            'send_notification' => 'boolean',
        ]);

        $options = [
            'rejection_reason' => $request->rejection_reason,
            'send_notification' => $request->boolean('send_notification', true),
        ];

        $result = $this->approvalService->reject($student, auth()->user(), $options);

        if ($result['success']) {
            return redirect()->route('admin.approvals.index')
                ->with('success', $result['message']);
        }

        return back()->with('error', $result['message']);
    }

    /**
     * Bulk approve selected students.
     */
    public function bulkApprove(Request $request)
    {
        $request->validate([
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'exists:students,id',
        ]);

        $students = Student::whereIn('id', $request->student_ids)
            ->pending()
            ->get();

        if ($students->isEmpty()) {
            return back()->with('error', 'No pending students found.');
        }

        $successCount = 0;
        $failCount = 0;

        foreach ($students as $student) {
            $result = $this->approvalService->approve($student, auth()->user(), [
                'send_welcome' => true,
                'send_whatsapp' => true,
                'send_email' => true,
            ]);

            if ($result['success']) {
                $successCount++;
            } else {
                $failCount++;
            }
        }

        $message = "{$successCount} student(s) approved successfully.";
        if ($failCount > 0) {
            $message .= " {$failCount} failed.";
        }

        return back()->with('success', $message);
    }

    /**
     * Request more information from parent.
     */
    public function requestInfo(Request $request, Student $student)
    {
        if (!$student->isPending()) {
            return back()->with('error', 'This student has already been processed.');
        }

        $request->validate([
            'info_request' => 'required|string|max:1000',
        ]);

        $result = $this->approvalService->requestMoreInfo($student, $request->info_request, auth()->user());

        if ($result['success']) {
            return back()->with('success', 'Information request sent to parent.');
        }

        return back()->with('error', $result['message']);
    }

    /**
     * Show approval history.
     */
    public function history(Request $request)
    {
        $query = Student::with(['user', 'approver'])
            ->whereIn('approval_status', ['approved', 'rejected'])
            ->orderBy('approved_at', 'desc');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('approval_status', $request->status);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('approved_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('approved_at', '<=', $request->date_to);
        }

        // Filter by approver
        if ($request->filled('approver_id')) {
            $query->where('approved_by', $request->approver_id);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('student_id', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $processedStudents = $query->paginate(20);

        // Get stats
        $stats = [
            'approved_total' => Student::approved()->count(),
            'rejected_total' => Student::rejected()->count(),
            'approved_this_month' => Student::approved()
                ->whereMonth('approved_at', now()->month)
                ->whereYear('approved_at', now()->year)
                ->count(),
            'rejected_this_month' => Student::rejected()
                ->whereMonth('approved_at', now()->month)
                ->whereYear('approved_at', now()->year)
                ->count(),
        ];

        return view('admin.approvals.history', compact('processedStudents', 'stats'));
    }

    /**
     * Resend welcome notification.
     */
    public function resendWelcome(Request $request, Student $student)
    {
        if (!$student->isApproved()) {
            return back()->with('error', 'Can only resend welcome to approved students.');
        }

        $channels = [];
        if ($request->boolean('whatsapp', true)) {
            $channels[] = 'whatsapp';
        }
        if ($request->boolean('email', true)) {
            $channels[] = 'email';
        }

        $result = $this->approvalService->sendWelcomeNotification($student, $channels);

        if ($result['success']) {
            return back()->with('success', 'Welcome notification resent successfully.');
        }

        return back()->with('error', 'Failed to send notification.');
    }
}
