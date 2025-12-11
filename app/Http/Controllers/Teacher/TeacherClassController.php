<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ClassModel;
use App\Models\ClassSession;
use App\Models\Enrollment;
use App\Models\StudentAttendance;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TeacherClassController extends Controller
{
    /**
     * Display teacher's classes.
     */
    public function index(Request $request)
    {
        $teacher = auth()->user()->teacher;

        $query = $teacher->classes()->with(['subject', 'schedules', 'enrollments']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by subject
        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $classes = $query->latest()->paginate(10)->withQueryString();

        // Get subjects for filter
        $subjects = $teacher->classes()
            ->with('subject')
            ->get()
            ->pluck('subject')
            ->unique('id')
            ->values();

        // Get statistics
        $stats = [
            'total_classes' => $teacher->classes()->count(),
            'active_classes' => $teacher->classes()->where('status', 'active')->count(),
            'total_students' => $teacher->classes()
                ->withCount('enrollments')
                ->get()
                ->sum('enrollments_count'),
            'classes_today' => $teacher->classes()
                ->whereHas('schedules', function ($q) {
                    $q->where('day_of_week', strtolower(now()->format('l')));
                })
                ->count(),
        ];

        return view('teacher.classes.index', compact('classes', 'subjects', 'stats'));
    }

    /**
     * Display class details.
     */
    public function show(ClassModel $class)
    {
        $teacher = auth()->user()->teacher;

        // Ensure teacher owns this class
        if ($class->teacher_id !== $teacher->id) {
            abort(403, 'You are not authorized to view this class.');
        }

        $class->load([
            'subject',
            'schedules' => fn($q) => $q->orderBy('day_of_week')->orderBy('start_time'),
            'enrollments.student.user',
            'sessions' => fn($q) => $q->latest()->take(10),
            'materials' => fn($q) => $q->latest()->take(5),
        ]);

        // Get attendance statistics for this class
        $attendanceStats = $this->getClassAttendanceStats($class);

        // Get upcoming sessions
        $upcomingSessions = ClassSession::where('class_id', $class->id)
            ->where('session_date', '>=', now()->toDateString())
            ->orderBy('session_date')
            ->orderBy('start_time')
            ->take(5)
            ->get();

        // Get recent exam results
        $recentExams = $class->exams()
            ->with('results')
            ->latest()
            ->take(3)
            ->get();

        return view('teacher.classes.show', compact(
            'class',
            'attendanceStats',
            'upcomingSessions',
            'recentExams'
        ));
    }

    /**
     * Get class attendance statistics.
     */
    private function getClassAttendanceStats(ClassModel $class)
    {
        $sessions = $class->sessions()->count();

        if ($sessions === 0) {
            return [
                'total_sessions' => 0,
                'average_attendance' => 0,
                'present_rate' => 0,
                'absent_rate' => 0,
            ];
        }

        $attendanceRecords = StudentAttendance::whereHas('classSession', function ($q) use ($class) {
            $q->where('class_id', $class->id);
        })->get();

        $totalRecords = $attendanceRecords->count();
        $presentCount = $attendanceRecords->where('status', 'present')->count();
        $absentCount = $attendanceRecords->where('status', 'absent')->count();

        return [
            'total_sessions' => $sessions,
            'total_records' => $totalRecords,
            'present_count' => $presentCount,
            'absent_count' => $absentCount,
            'present_rate' => $totalRecords > 0 ? round(($presentCount / $totalRecords) * 100, 1) : 0,
            'absent_rate' => $totalRecords > 0 ? round(($absentCount / $totalRecords) * 100, 1) : 0,
        ];
    }

    /**
     * View class schedule.
     */
    public function schedule(ClassModel $class)
    {
        $teacher = auth()->user()->teacher;

        if ($class->teacher_id !== $teacher->id) {
            abort(403, 'Unauthorized access.');
        }

        $class->load(['subject', 'schedules']);

        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

        // Organize schedules by day
        $weeklySchedule = [];
        foreach ($days as $day) {
            $weeklySchedule[$day] = $class->schedules
                ->where('day_of_week', $day)
                ->sortBy('start_time')
                ->values();
        }

        return view('teacher.classes.schedule', compact('class', 'weeklySchedule', 'days'));
    }

    /**
     * View enrolled students.
     */
    public function students(ClassModel $class)
    {
        $teacher = auth()->user()->teacher;

        if ($class->teacher_id !== $teacher->id) {
            abort(403, 'Unauthorized access.');
        }

        $enrollments = $class->enrollments()
            ->with(['student.user', 'student.parent.user'])
            ->where('status', 'active')
            ->get();

        return view('teacher.classes.students', compact('class', 'enrollments'));
    }

    /**
     * Mark class session as completed.
     */
    public function completeSession(Request $request, ClassSession $session)
    {
        $teacher = auth()->user()->teacher;

        if ($session->class->teacher_id !== $teacher->id) {
            abort(403, 'Unauthorized access.');
        }

        $validated = $request->validate([
            'notes' => 'nullable|string|max:1000',
            'topics_covered' => 'nullable|string|max:500',
        ]);

        try {
            $session->update([
                'status' => 'completed',
                'notes' => $validated['notes'],
                'topics_covered' => $validated['topics_covered'],
                'completed_at' => now(),
            ]);

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'update',
                'model_type' => 'ClassSession',
                'model_id' => $session->id,
                'description' => 'Marked session as completed for class: ' . $session->class->name,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return back()->with('success', 'Session marked as completed.');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update session: ' . $e->getMessage());
        }
    }

    /**
     * Add session notes.
     */
    public function addSessionNotes(Request $request, ClassSession $session)
    {
        $teacher = auth()->user()->teacher;

        if ($session->class->teacher_id !== $teacher->id) {
            abort(403, 'Unauthorized access.');
        }

        $validated = $request->validate([
            'notes' => 'required|string|max:2000',
        ]);

        try {
            $session->update([
                'notes' => $validated['notes'],
            ]);

            return back()->with('success', 'Session notes saved successfully.');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to save notes: ' . $e->getMessage());
        }
    }
}
