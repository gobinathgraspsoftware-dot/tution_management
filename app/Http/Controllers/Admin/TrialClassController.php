<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\TrialClassRequest;
use App\Models\TrialClass;
use App\Models\ClassModel;
use App\Models\Student;
use App\Models\ActivityLog;
use App\Services\TrialClassService;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TrialClassController extends Controller
{
    protected $trialClassService;
    protected $whatsappService;

    public function __construct(TrialClassService $trialClassService, WhatsAppService $whatsappService)
    {
        $this->trialClassService = $trialClassService;
        $this->whatsappService = $whatsappService;
    }

    /**
     * Display trial class listing.
     */
    public function index(Request $request)
    {
        $query = TrialClass::with(['student.user', 'class.subject', 'class.teacher.user']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('student_name', 'like', "%{$search}%")
                  ->orWhere('parent_name', 'like', "%{$search}%")
                  ->orWhere('parent_phone', 'like', "%{$search}%")
                  ->orWhere('parent_email', 'like', "%{$search}%")
                  ->orWhereHas('student.user', fn($q2) => $q2->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('class', fn($q2) => $q2->where('name', 'like', "%{$search}%"));
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by conversion status
        if ($request->filled('conversion_status')) {
            $query->where('conversion_status', $request->conversion_status);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('scheduled_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('scheduled_date', '<=', $request->date_to);
        }

        // Filter by class
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        $trialClasses = $query->latest()->paginate(15)->withQueryString();

        // Statistics
        $stats = [
            'total' => TrialClass::count(),
            'pending' => TrialClass::where('status', 'pending')->count(),
            'approved' => TrialClass::where('status', 'approved')->count(),
            'attended' => TrialClass::where('status', 'attended')->count(),
            'no_show' => TrialClass::where('status', 'no_show')->count(),
            'converted' => TrialClass::where('conversion_status', 'converted')->count(),
            'conversion_rate' => $this->calculateConversionRate(),
            'today' => TrialClass::whereDate('scheduled_date', today())->count(),
            'upcoming' => TrialClass::where('scheduled_date', '>=', today())->whereIn('status', ['pending', 'approved'])->count(),
        ];

        // Get classes for filter dropdown
        $classes = ClassModel::active()->with('subject')->get();

        return view('admin.trial-classes.index', compact('trialClasses', 'stats', 'classes'));
    }

    /**
     * Show create trial class form.
     */
    public function create()
    {
        $classes = ClassModel::active()
            ->with(['subject', 'teacher.user', 'schedules'])
            ->get();

        $students = Student::approved()
            ->with('user')
            ->whereHas('user', fn($q) => $q->where('status', 'active'))
            ->get();

        return view('admin.trial-classes.create', compact('classes', 'students'));
    }

    /**
     * Store new trial class.
     */
    public function store(TrialClassRequest $request)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();

            // If existing student selected
            if ($request->filled('student_id')) {
                $student = Student::with(['user', 'parent.user'])->find($request->student_id);
                $data['student_name'] = $student->user->name;
                $data['parent_name'] = $student->parent->user->name ?? null;
                $data['parent_phone'] = $student->parent->user->phone ?? $student->user->phone;
                $data['parent_email'] = $student->parent->user->email ?? $student->user->email;
            }

            $trialClass = TrialClass::create($data);

            // Send notification
            $this->trialClassService->sendTrialScheduledNotification($trialClass);

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'create',
                'model_type' => 'TrialClass',
                'model_id' => $trialClass->id,
                'description' => "Created trial class for {$data['student_name']}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            DB::commit();
            return redirect()->route('admin.trial-classes.index')
                ->with('success', 'Trial class scheduled successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to create trial class: ' . $e->getMessage());
        }
    }

    /**
     * Display trial class details.
     */
    public function show(TrialClass $trialClass)
    {
        $trialClass->load([
            'student.user',
            'student.parent.user',
            'class.subject',
            'class.teacher.user',
            'class.schedules',
        ]);

        return view('admin.trial-classes.show', compact('trialClass'));
    }

    /**
     * Update trial class status.
     */
    public function updateStatus(Request $request, TrialClass $trialClass)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,attended,no_show,converted,cancelled',
            'feedback' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:1000',
        ]);

        $oldStatus = $trialClass->status;
        $trialClass->update([
            'status' => $request->status,
            'feedback' => $request->feedback ?? $trialClass->feedback,
            'notes' => $request->notes ?? $trialClass->notes,
        ]);

        // Send appropriate notifications
        if ($request->status === 'approved' && $oldStatus === 'pending') {
            $this->trialClassService->sendTrialApprovedNotification($trialClass);
        }

        // Log activity
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'update',
            'model_type' => 'TrialClass',
            'model_id' => $trialClass->id,
            'description' => "Updated trial class status from {$oldStatus} to {$request->status}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return back()->with('success', 'Trial class status updated successfully.');
    }

    /**
     * Mark trial class attendance.
     */
    public function markAttendance(Request $request, TrialClass $trialClass)
    {
        $request->validate([
            'attended' => 'required|boolean',
            'feedback' => 'nullable|string|max:1000',
        ]);

        $status = $request->attended ? 'attended' : 'no_show';

        $trialClass->update([
            'status' => $status,
            'feedback' => $request->feedback,
        ]);

        // Send follow-up notification
        $this->trialClassService->sendTrialFollowUpNotification($trialClass);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'update',
            'model_type' => 'TrialClass',
            'model_id' => $trialClass->id,
            'description' => "Marked trial class as {$status}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return back()->with('success', 'Attendance marked successfully.');
    }

    /**
     * Convert trial to full enrollment.
     */
    public function convert(Request $request, TrialClass $trialClass)
    {
        if ($trialClass->status !== 'attended') {
            return back()->with('error', 'Only attended trial classes can be converted.');
        }

        if ($trialClass->conversion_status === 'converted') {
            return back()->with('error', 'This trial class has already been converted.');
        }

        $trialClass->update([
            'status' => 'converted',
            'conversion_status' => 'converted',
        ]);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'update',
            'model_type' => 'TrialClass',
            'model_id' => $trialClass->id,
            'description' => 'Converted trial class to full enrollment',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        // Redirect to enrollment creation with pre-filled data
        if ($trialClass->student_id) {
            return redirect()->route('admin.enrollments.create', [
                'student_id' => $trialClass->student_id,
                'class_id' => $trialClass->class_id,
            ])->with('success', 'Trial class marked as converted. Please complete the enrollment.');
        }

        return back()->with('success', 'Trial class marked as converted. Student needs to be registered first.');
    }

    /**
     * Mark conversion as declined.
     */
    public function decline(Request $request, TrialClass $trialClass)
    {
        $request->validate([
            'decline_reason' => 'required|string|max:500',
        ]);

        $trialClass->update([
            'conversion_status' => 'declined',
            'notes' => ($trialClass->notes ? $trialClass->notes . "\n" : '') .
                       "Declined: " . $request->decline_reason,
        ]);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'update',
            'model_type' => 'TrialClass',
            'model_id' => $trialClass->id,
            'description' => 'Marked trial conversion as declined',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return back()->with('success', 'Trial conversion marked as declined.');
    }

    /**
     * Delete trial class.
     */
    public function destroy(TrialClass $trialClass)
    {
        $studentName = $trialClass->student_name;
        $trialClass->delete();

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'delete',
            'model_type' => 'TrialClass',
            'model_id' => $trialClass->id,
            'description' => "Deleted trial class for {$studentName}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()->route('admin.trial-classes.index')
            ->with('success', 'Trial class deleted successfully.');
    }

    /**
     * Export trial classes to CSV.
     */
    public function export(Request $request)
    {
        $trialClasses = TrialClass::with(['student.user', 'class.subject'])
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->get();

        $filename = 'trial_classes_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($trialClasses) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Student Name', 'Parent Name', 'Phone', 'Email', 'Class', 'Scheduled Date', 'Time', 'Status', 'Conversion', 'Feedback']);

            foreach ($trialClasses as $tc) {
                fputcsv($file, [
                    $tc->id,
                    $tc->student_name ?? ($tc->student->user->name ?? 'N/A'),
                    $tc->parent_name ?? 'N/A',
                    $tc->parent_phone ?? 'N/A',
                    $tc->parent_email ?? 'N/A',
                    $tc->class->name ?? 'N/A',
                    $tc->scheduled_date->format('Y-m-d'),
                    $tc->scheduled_time ? $tc->scheduled_time->format('H:i') : 'N/A',
                    ucfirst($tc->status),
                    ucfirst($tc->conversion_status),
                    $tc->feedback ?? '',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Calculate conversion rate.
     */
    protected function calculateConversionRate(): float
    {
        $attended = TrialClass::where('status', 'attended')
            ->orWhere('status', 'converted')
            ->count();

        if ($attended === 0) return 0;

        $converted = TrialClass::where('conversion_status', 'converted')->count();

        return round(($converted / $attended) * 100, 2);
    }
}
