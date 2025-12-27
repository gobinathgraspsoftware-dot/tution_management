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

        $students = [];
        $attendanceRecords = [];
        $classInfo = null;

        if ($selectedClassId && $selectedDate) {
            // Get class information
            $classInfo = ClassModel::with(['subject', 'teacher.user'])->find($selectedClassId);

            if ($classInfo) {
                // Get enrolled students for this class
                $students = $classInfo->enrollments()
                    ->with('student.user')
                    ->whereHas('student', function($q) {
                        $q->where('status', 'active');
                    })
                    ->get()
                    ->pluck('student');

                // Get or create a default session for this date and class
                $session = $this->getOrCreateDefaultSession($selectedClassId, $selectedDate);

                // Get existing attendance records for this session
                if ($session) {
                    $attendanceRecords = $this->attendanceService->getSessionAttendance($session->id);
                }
            }
        }

        return view('admin.attendance.student.mark', compact(
            'classes',
            'students',
            'attendanceRecords',
            'selectedDate',
            'selectedClassId',
            'classInfo'
        ));
    }

    /**
     * Get or create a default class session for the date
     */
    protected function getOrCreateDefaultSession($classId, $date)
    {
        $class = ClassModel::with('schedules')->find($classId);
        if (!$class) {
            return null;
        }

        $sessionDate = Carbon::parse($date);

        // Try to find existing session for this class and date
        $session = ClassSession::where('class_id', $classId)
            ->whereDate('session_date', $date)
            ->first();

        if ($session) {
            return $session;
        }

        // Get the class schedule for this day of week
        $dayOfWeek = strtolower($sessionDate->format('l'));
        $schedule = $class->schedules()
            ->where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->first();

        // Create default session
        $startTime = $schedule ? $schedule->start_time : '09:00:00';
        $endTime = $schedule ? $schedule->end_time : '10:00:00';

        $session = ClassSession::create([
            'class_id' => $classId,
            'session_date' => $date,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'topic' => 'Regular Class',
            'status' => 'scheduled',
        ]);

        return $session;
    }

    /**
     * Store student attendance (bulk)
     */
    public function storeStudent(StudentAttendanceRequest $request)
    {
        try {
            // Get or create session for the class and date
            $session = $this->getOrCreateDefaultSession(
                $request->class_id,
                $request->date
            );

            if (!$session) {
                return back()
                    ->withInput()
                    ->with('error', 'Failed to create class session. Please check class configuration.');
            }

            // Add session_id to the data
            $data = $request->validated();
            $data['session_id'] = $session->id;

            $result = $this->attendanceService->markStudentAttendance($data);

            return redirect()
                ->route('admin.attendance.student.mark', [
                    'class_id' => $request->class_id,
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
     * Get sessions for a class on a date (AJAX) - DEPRECATED but kept for backward compatibility
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
