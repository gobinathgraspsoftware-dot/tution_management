<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\TrialClass;
use App\Models\ClassModel;
use App\Models\Student;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class TrialClassController extends Controller
{
    /**
     * Display a listing of trial classes.
     * Staff can view all trial classes with filtering options.
     */
    public function index(Request $request)
    {
        $query = TrialClass::with(['student.user', 'class.subject', 'class.teacher.user']);

        // Search functionality
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

        // Default: Show upcoming and recent trials first
        $trialClasses = $query->latest('scheduled_date')->paginate(15)->withQueryString();

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
            'upcoming' => TrialClass::where('scheduled_date', '>=', today())
                ->whereIn('status', ['pending', 'approved'])
                ->count(),
        ];

        // Get classes for filter dropdown
        $classes = ClassModel::active()
            ->with('subject')
            ->orderBy('name')
            ->get();

        return view('staff.trial-classes.index', compact('trialClasses', 'stats', 'classes'));
    }

    /**
     * Display the specified trial class.
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

        // Get class schedule for context
        $scheduleInfo = null;
        if ($trialClass->class && $trialClass->class->schedules->count() > 0) {
            $scheduleInfo = $trialClass->class->schedules
                ->map(function ($schedule) {
                    return [
                        'day' => ucfirst($schedule->day_of_week),
                        'time' => $schedule->start_time->format('h:i A') . ' - ' . $schedule->end_time->format('h:i A'),
                    ];
                });
        }

        return view('staff.trial-classes.show', compact('trialClass', 'scheduleInfo'));
    }

    /**
     * Update trial class status (limited actions for staff).
     * Staff can only mark attendance (attended/no_show).
     */
    public function markAttendance(Request $request, TrialClass $trialClass)
    {
        $request->validate([
            'attended' => 'required|boolean',
            'feedback' => 'nullable|string|max:1000',
        ]);

        // Only allow marking attendance for approved trials
        if (!in_array($trialClass->status, ['pending', 'approved'])) {
            return back()->with('error', 'Only pending or approved trial classes can have attendance marked.');
        }

        $status = $request->attended ? 'attended' : 'no_show';
        
        $trialClass->update([
            'status' => $status,
            'feedback' => $request->feedback,
        ]);

        // Log activity
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'update',
            'model_type' => 'TrialClass',
            'model_id' => $trialClass->id,
            'description' => "Staff marked trial class as {$status}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return back()->with('success', 'Attendance marked successfully.');
    }

    /**
     * Calculate conversion rate.
     */
    protected function calculateConversionRate(): float
    {
        $attended = TrialClass::where('status', 'attended')
            ->orWhere('status', 'converted')
            ->count();
        
        if ($attended === 0) {
            return 0;
        }

        $converted = TrialClass::where('conversion_status', 'converted')->count();
        
        return round(($converted / $attended) * 100, 2);
    }

    /**
     * Get today's trial classes (for dashboard widget).
     */
    public function todaysTrials()
    {
        $trials = TrialClass::with(['student.user', 'class.subject'])
            ->whereDate('scheduled_date', today())
            ->whereIn('status', ['pending', 'approved'])
            ->orderBy('scheduled_time')
            ->get();

        return response()->json($trials);
    }

    /**
     * Get upcoming trial classes (for dashboard widget).
     */
    public function upcomingTrials(Request $request)
    {
        $days = $request->get('days', 7);

        $trials = TrialClass::with(['student.user', 'class.subject'])
            ->whereIn('status', ['pending', 'approved'])
            ->whereBetween('scheduled_date', [today(), today()->addDays($days)])
            ->orderBy('scheduled_date')
            ->orderBy('scheduled_time')
            ->get();

        return response()->json($trials);
    }
}
