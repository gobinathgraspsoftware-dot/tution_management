<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ClassModel;
use App\Models\ClassSession;
use App\Models\StudentAttendance;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * Display attendance dashboard for teacher.
     */
    public function index(Request $request)
    {
        $teacher = Auth::user()->teacher;

        if (!$teacher) {
            abort(403, 'Teacher profile not found.');
        }

        // Get teacher's classes
        $classes = ClassModel::where('teacher_id', $teacher->id)
            ->where('status', 'active')
            ->with(['subject', 'schedules'])
            ->get();

        // Get today's sessions
        $todaySessions = ClassSession::whereHas('class', function ($q) use ($teacher) {
                $q->where('teacher_id', $teacher->id);
            })
            ->whereDate('session_date', Carbon::today())
            ->with(['class.subject', 'attendances'])
            ->orderBy('start_time')
            ->get();

        // Get upcoming sessions (next 7 days)
        $upcomingSessions = ClassSession::whereHas('class', function ($q) use ($teacher) {
                $q->where('teacher_id', $teacher->id);
            })
            ->whereDate('session_date', '>', Carbon::today())
            ->whereDate('session_date', '<=', Carbon::today()->addDays(7))
            ->with(['class.subject'])
            ->orderBy('session_date')
            ->orderBy('start_time')
            ->limit(10)
            ->get();

        // Get recent attendance records
        $recentAttendance = StudentAttendance::whereHas('classSession.class', function ($q) use ($teacher) {
                $q->where('teacher_id', $teacher->id);
            })
            ->with(['student.user', 'classSession.class'])
            ->latest()
            ->limit(20)
            ->get();

        // Calculate statistics
        $stats = $this->calculateStats($teacher);

        return view('teacher.attendance.index', compact(
            'classes',
            'todaySessions',
            'upcomingSessions',
            'recentAttendance',
            'stats'
        ));
    }

    /**
     * Show attendance marking form for a session.
     */
    public function markAttendance(ClassSession $session)
    {
        $teacher = Auth::user()->teacher;

        // Verify teacher owns this class
        if ($session->class->teacher_id !== $teacher->id) {
            abort(403, 'Unauthorized access.');
        }

        $session->load(['class.subject', 'attendances.student.user']);

        // Get enrolled students for this class
        $enrolledStudents = Enrollment::where('class_id', $session->class_id)
            ->where('status', 'active')
            ->with('student.user')
            ->get()
            ->pluck('student');

        // Get existing attendance records
        $existingAttendance = $session->attendances->keyBy('student_id');

        return view('teacher.attendance.mark', compact('session', 'enrolledStudents', 'existingAttendance'));
    }

    /**
     * Store attendance records for a session.
     */
    public function storeAttendance(Request $request, ClassSession $session)
    {
        $teacher = Auth::user()->teacher;

        // Verify teacher owns this class
        if ($session->class->teacher_id !== $teacher->id) {
            abort(403, 'Unauthorized access.');
        }

        $request->validate([
            'attendance' => 'required|array',
            'attendance.*.student_id' => 'required|exists:students,id',
            'attendance.*.status' => 'required|in:present,absent,late,excused',
            'attendance.*.remarks' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->attendance as $record) {
                StudentAttendance::updateOrCreate(
                    [
                        'class_session_id' => $session->id,
                        'student_id' => $record['student_id'],
                    ],
                    [
                        'status' => $record['status'],
                        'remarks' => $record['remarks'] ?? null,
                        'marked_by' => Auth::id(),
                        'marked_at' => now(),
                    ]
                );
            }

            // Update session status
            $session->update(['status' => 'completed']);

            DB::commit();

            return redirect()->route('teacher.attendance.session-details', $session)
                ->with('success', 'Attendance marked successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to mark attendance: ' . $e->getMessage());
        }
    }

    /**
     * View attendance history for a class.
     */
    public function classHistory(ClassModel $class, Request $request)
    {
        $teacher = Auth::user()->teacher;

        // Verify teacher owns this class
        if ($class->teacher_id !== $teacher->id) {
            abort(403, 'Unauthorized access.');
        }

        $query = ClassSession::where('class_id', $class->id)
            ->with(['attendances.student.user'])
            ->orderBy('session_date', 'desc');

        // Filter by date range
        if ($request->filled('from_date')) {
            $query->whereDate('session_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('session_date', '<=', $request->to_date);
        }

        // Filter by month
        if ($request->filled('month')) {
            $query->whereMonth('session_date', $request->month);
        }

        $sessions = $query->paginate(15)->withQueryString();

        // Get enrolled students for summary
        $enrolledStudents = Enrollment::where('class_id', $class->id)
            ->where('status', 'active')
            ->with('student.user')
            ->get()
            ->pluck('student');

        // Calculate attendance summary per student
        $studentSummary = [];
        foreach ($enrolledStudents as $student) {
            $records = StudentAttendance::where('student_id', $student->id)
                ->whereHas('classSession', function ($q) use ($class) {
                    $q->where('class_id', $class->id);
                })
                ->get();

            $total = $records->count();
            $present = $records->where('status', 'present')->count();

            $studentSummary[$student->id] = [
                'student' => $student,
                'total' => $total,
                'present' => $present,
                'absent' => $records->where('status', 'absent')->count(),
                'late' => $records->where('status', 'late')->count(),
                'excused' => $records->where('status', 'excused')->count(),
                'rate' => $total > 0 ? round(($present / $total) * 100, 1) : 0,
            ];
        }

        return view('teacher.attendance.class-history', compact('class', 'sessions', 'studentSummary'));
    }

    /**
     * View specific session details.
     */
    public function sessionDetails(ClassSession $session)
    {
        $teacher = Auth::user()->teacher;

        // Verify teacher owns this class
        if ($session->class->teacher_id !== $teacher->id) {
            abort(403, 'Unauthorized access.');
        }

        $session->load(['class.subject', 'attendances.student.user']);

        // Get enrolled students
        $enrolledStudents = Enrollment::where('class_id', $session->class_id)
            ->where('status', 'active')
            ->with('student.user')
            ->get()
            ->pluck('student');

        // Calculate session stats
        $attendances = $session->attendances;
        $stats = [
            'total' => $enrolledStudents->count(),
            'marked' => $attendances->count(),
            'present' => $attendances->where('status', 'present')->count(),
            'absent' => $attendances->where('status', 'absent')->count(),
            'late' => $attendances->where('status', 'late')->count(),
            'excused' => $attendances->where('status', 'excused')->count(),
        ];

        return view('teacher.attendance.session-details', compact('session', 'enrolledStudents', 'stats'));
    }

    /**
     * Get sessions for calendar (AJAX).
     */
    public function getSessions(Request $request)
    {
        $teacher = Auth::user()->teacher;

        $start = $request->input('start');
        $end = $request->input('end');

        $sessions = ClassSession::whereHas('class', function ($q) use ($teacher) {
                $q->where('teacher_id', $teacher->id);
            })
            ->whereBetween('session_date', [$start, $end])
            ->with(['class.subject', 'attendances'])
            ->get();

        $events = $sessions->map(function ($session) {
            $attendanceMarked = $session->attendances->count() > 0;
            
            return [
                'id' => $session->id,
                'title' => $session->class->name,
                'start' => $session->session_date->format('Y-m-d') . 'T' . $session->start_time,
                'end' => $session->session_date->format('Y-m-d') . 'T' . $session->end_time,
                'backgroundColor' => $attendanceMarked ? '#28a745' : '#ffc107',
                'borderColor' => $attendanceMarked ? '#28a745' : '#ffc107',
                'extendedProps' => [
                    'subject' => $session->class->subject->name ?? 'N/A',
                    'attendance_marked' => $attendanceMarked,
                ],
            ];
        });

        return response()->json($events);
    }

    /**
     * Calculate attendance statistics for teacher.
     */
    private function calculateStats($teacher)
    {
        $thisMonth = Carbon::now()->startOfMonth();

        // Total sessions this month
        $totalSessions = ClassSession::whereHas('class', function ($q) use ($teacher) {
                $q->where('teacher_id', $teacher->id);
            })
            ->whereDate('session_date', '>=', $thisMonth)
            ->count();

        // Sessions with attendance marked
        $markedSessions = ClassSession::whereHas('class', function ($q) use ($teacher) {
                $q->where('teacher_id', $teacher->id);
            })
            ->whereDate('session_date', '>=', $thisMonth)
            ->whereHas('attendances')
            ->count();

        // Overall attendance rate
        $attendanceRecords = StudentAttendance::whereHas('classSession.class', function ($q) use ($teacher) {
                $q->where('teacher_id', $teacher->id);
            })
            ->whereHas('classSession', function ($q) use ($thisMonth) {
                $q->whereDate('session_date', '>=', $thisMonth);
            })
            ->get();

        $totalRecords = $attendanceRecords->count();
        $presentRecords = $attendanceRecords->where('status', 'present')->count();
        $attendanceRate = $totalRecords > 0 ? round(($presentRecords / $totalRecords) * 100, 1) : 0;

        return [
            'total_sessions' => $totalSessions,
            'marked_sessions' => $markedSessions,
            'pending_sessions' => $totalSessions - $markedSessions,
            'attendance_rate' => $attendanceRate,
            'total_students_marked' => $totalRecords,
        ];
    }
}
