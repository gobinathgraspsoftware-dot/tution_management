<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StudentAttendanceRequest;
use App\Http\Requests\TeacherAttendanceRequest;
use App\Models\ClassModel;
use App\Models\ClassSession;
use App\Models\Teacher;
use App\Services\AttendanceService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    protected $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    /**
     * Display attendance dashboard
     */
    public function index()
    {
        $todayStats = $this->attendanceService->getTodayStats();
        $weekStats = $this->attendanceService->getWeekStats();
        $recentSessions = ClassSession::with(['class.subject', 'attendance'])
            ->whereDate('session_date', today())
            ->orderBy('start_time')
            ->limit(10)
            ->get();

        return view('admin.attendance.index', compact('todayStats', 'weekStats', 'recentSessions'));
    }

    // ==================== STUDENT ATTENDANCE ====================

    /**
     * Show student attendance marking form
     */
    public function markStudent(Request $request)
    {
        $classes = ClassModel::with(['subject', 'enrollments.student.user'])
            ->where('status', 'active')
            ->get();

        $selectedDate = $request->input('date', now()->format('Y-m-d'));
        $selectedClassId = $request->input('class_id');
        $selectedSessionId = $request->input('session_id');

        $sessions = [];
        $students = [];
        $attendanceRecords = [];

        if ($selectedClassId) {
            $sessions = ClassSession::where('class_id', $selectedClassId)
                ->whereDate('session_date', $selectedDate)
                ->orderBy('start_time')
                ->get();
        }

        if ($selectedSessionId) {
            $session = ClassSession::with(['class.enrollments.student.user'])->findOrFail($selectedSessionId);
            $students = $session->class->enrollments()
                ->with('student.user')
                ->whereHas('student', function($q) {
                    $q->where('status', 'active');
                })
                ->get()
                ->pluck('student');

            $attendanceRecords = $this->attendanceService->getSessionAttendance($selectedSessionId);
        }

        return view('admin.attendance.student.mark', compact(
            'classes',
            'sessions',
            'students',
            'attendanceRecords',
            'selectedDate',
            'selectedClassId',
            'selectedSessionId'
        ));
    }

    /**
     * Store student attendance (bulk)
     */
    public function storeStudent(StudentAttendanceRequest $request)
    {
        try {
            $result = $this->attendanceService->markStudentAttendance(
                $request->validated()
            );

            return redirect()
                ->route('admin.attendance.student.mark', [
                    'class_id' => $request->class_id,
                    'session_id' => $request->session_id,
                    'date' => $request->date
                ])
                ->with('success', "Attendance marked for {$result['marked_count']} students. {$result['notifications_sent']} notifications sent.");
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to mark attendance: ' . $e->getMessage());
        }
    }

    /**
     * Show student attendance calendar
     */
    public function studentCalendar(Request $request)
    {
        $selectedMonth = $request->input('month', now()->format('Y-m'));
        $selectedClassId = $request->input('class_id');

        $classes = ClassModel::with('subject')
            ->where('status', 'active')
            ->get();

        $calendarData = [];
        if ($selectedClassId) {
            $calendarData = $this->attendanceService->getStudentCalendarData(
                $selectedClassId,
                $selectedMonth
            );
        }

        return view('admin.attendance.student.calendar', compact(
            'classes',
            'calendarData',
            'selectedMonth',
            'selectedClassId'
        ));
    }

    // ==================== TEACHER ATTENDANCE ====================

    /**
     * Show teacher attendance marking form
     */
    public function markTeacher(Request $request)
    {
        $teachers = Teacher::with('user')
            ->where('status', 'active')
            ->get();

        $selectedDate = $request->input('date', now()->format('Y-m-d'));
        $attendanceRecords = $this->attendanceService->getTeacherAttendanceByDate($selectedDate);

        return view('admin.attendance.teacher.mark', compact(
            'teachers',
            'attendanceRecords',
            'selectedDate'
        ));
    }

    /**
     * Store teacher attendance (bulk)
     */
    public function storeTeacher(TeacherAttendanceRequest $request)
    {
        try {
            $result = $this->attendanceService->markTeacherAttendance(
                $request->validated()
            );

            return redirect()
                ->route('admin.attendance.teacher.mark', ['date' => $request->date])
                ->with('success', "Attendance marked for {$result['marked_count']} teachers.");
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to mark attendance: ' . $e->getMessage());
        }
    }

    /**
     * Show teacher attendance calendar
     */
    public function teacherCalendar(Request $request)
    {
        $selectedMonth = $request->input('month', now()->format('Y-m'));
        $selectedTeacherId = $request->input('teacher_id');

        $teachers = Teacher::with('user')
            ->where('status', 'active')
            ->get();

        $calendarData = [];
        if ($selectedTeacherId) {
            $calendarData = $this->attendanceService->getTeacherCalendarData(
                $selectedTeacherId,
                $selectedMonth
            );
        }

        return view('admin.attendance.teacher.calendar', compact(
            'teachers',
            'calendarData',
            'selectedMonth',
            'selectedTeacherId'
        ));
    }

    // ==================== AJAX ENDPOINTS ====================

    /**
     * Get sessions for a class on a date (AJAX)
     */
    public function getSessions(Request $request)
    {
        $sessions = ClassSession::where('class_id', $request->class_id)
            ->whereDate('session_date', $request->date)
            ->orderBy('start_time')
            ->get(['id', 'topic', 'start_time', 'end_time', 'status']);

        return response()->json($sessions);
    }

    /**
     * Get attendance summary for a session (AJAX)
     */
    public function getSessionSummary($sessionId)
    {
        $summary = $this->attendanceService->getSessionSummary($sessionId);
        return response()->json($summary);
    }
}
