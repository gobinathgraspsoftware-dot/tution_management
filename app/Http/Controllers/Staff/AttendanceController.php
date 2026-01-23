<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Services\AttendanceService;
use App\Models\ClassModel;
use App\Models\ClassSession;
use App\Models\StudentAttendance;
use App\Models\TeacherAttendance;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\StudentAttendanceExport;

class AttendanceController extends Controller
{
    protected $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    /**
     * Display attendance dashboard for staff.
     */
    public function index()
    {
        // Get today's stats
        $todayStats = $this->attendanceService->getTodayStats();
        
        // Get week stats
        $weekStats = $this->attendanceService->getWeekStats();

        // Get today's sessions
        $todaySessions = ClassSession::whereDate('session_date', Carbon::today())
            ->with(['class.subject', 'class.teacher.user', 'attendances'])
            ->orderBy('start_time')
            ->get();

        // Get recent attendance records
        $recentStudentAttendance = StudentAttendance::with(['student.user', 'classSession.class', 'markedBy'])
            ->latest()
            ->limit(15)
            ->get();

        $recentTeacherAttendance = TeacherAttendance::with(['teacher.user', 'markedBy'])
            ->latest()
            ->limit(10)
            ->get();

        // Get classes for filters
        $classes = ClassModel::where('status', 'active')
            ->with('subject')
            ->orderBy('name')
            ->get();

        return view('staff.attendance.index', compact(
            'todayStats',
            'weekStats',
            'todaySessions',
            'recentStudentAttendance',
            'recentTeacherAttendance',
            'classes'
        ));
    }

    /**
     * Show the student attendance marking form.
     */
    public function markStudent(Request $request)
    {
        $classes = ClassModel::where('status', 'active')
            ->with(['subject', 'teacher.user'])
            ->orderBy('name')
            ->get();

        // Get sessions for today by default
        $selectedDate = $request->input('date', Carbon::today()->toDateString());
        $selectedClassId = $request->input('class_id');

        $sessions = [];
        $students = collect();
        $existingAttendance = collect();

        if ($selectedClassId) {
            $sessions = ClassSession::where('class_id', $selectedClassId)
                ->whereDate('session_date', $selectedDate)
                ->with(['class.subject'])
                ->orderBy('start_time')
                ->get();

            // Get enrolled students for selected class
            $students = Enrollment::where('class_id', $selectedClassId)
                ->where('status', 'active')
                ->with('student.user')
                ->get()
                ->pluck('student');

            // Get existing attendance for selected date and session
            if ($request->input('session_id')) {
                $existingAttendance = StudentAttendance::where('class_session_id', $request->input('session_id'))
                    ->get()
                    ->keyBy('student_id');
            }
        }

        return view('staff.attendance.mark-student', compact(
            'classes',
            'selectedDate',
            'selectedClassId',
            'sessions',
            'students',
            'existingAttendance'
        ));
    }

    /**
     * Store student attendance records.
     */
    public function storeStudent(Request $request)
    {
        $request->validate([
            'session_id' => 'required|exists:class_sessions,id',
            'date' => 'required|date',
            'attendance' => 'required|array',
            'attendance.*.status' => 'nullable|in:present,absent,late,excused',
            'send_notifications' => 'nullable|boolean',
        ]);

        try {
            $result = $this->attendanceService->markStudentAttendance([
                'session_id' => $request->session_id,
                'date' => $request->date,
                'attendance' => $request->attendance,
                'send_notifications' => $request->boolean('send_notifications'),
            ]);

            $message = "Attendance marked successfully. {$result['marked_count']} students recorded.";
            if ($result['notifications_sent'] > 0) {
                $message .= " {$result['notifications_sent']} notifications sent to parents.";
            }

            return redirect()->route('staff.attendance.student.mark', [
                'class_id' => $request->class_id,
                'date' => $request->date,
            ])->with('success', $message);

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to mark attendance: ' . $e->getMessage());
        }
    }

    /**
     * Show the student attendance calendar.
     */
    public function studentCalendar(Request $request)
    {
        $classes = ClassModel::where('status', 'active')
            ->with('subject')
            ->orderBy('name')
            ->get();

        $students = Student::with('user')
            ->whereHas('user', fn($q) => $q->where('status', 'active'))
            ->orderBy('student_id')
            ->get();

        $selectedClassId = $request->input('class_id');
        $selectedStudentId = $request->input('student_id');
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        $calendarData = [];
        $stats = [];

        if ($selectedStudentId) {
            $calendarData = $this->attendanceService->getStudentAttendanceCalendar(
                $selectedStudentId,
                $month,
                $year
            );
            $stats = $this->attendanceService->getStudentStats($selectedStudentId, $month, $year);
        } elseif ($selectedClassId) {
            $calendarData = $this->attendanceService->getClassAttendanceCalendar(
                $selectedClassId,
                "{$year}-{$month}"
            );
        }

        return view('staff.attendance.student-calendar', compact(
            'classes',
            'students',
            'selectedClassId',
            'selectedStudentId',
            'month',
            'year',
            'calendarData',
            'stats'
        ));
    }

    /**
     * Show the teacher attendance marking form.
     */
    public function markTeacher(Request $request)
    {
        $selectedDate = $request->input('date', Carbon::today()->toDateString());

        // Get all active teachers
        $teachers = Teacher::with('user')
            ->whereHas('user', fn($q) => $q->where('status', 'active'))
            ->get();

        // Get existing attendance for selected date
        $existingAttendance = TeacherAttendance::whereDate('date', $selectedDate)
            ->get()
            ->keyBy('teacher_id');

        return view('staff.attendance.mark-teacher', compact(
            'selectedDate',
            'teachers',
            'existingAttendance'
        ));
    }

    /**
     * Store teacher attendance records.
     */
    public function storeTeacher(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'attendance' => 'required|array',
            'attendance.*.status' => 'nullable|in:present,absent,half_day,on_leave',
            'attendance.*.time_in' => 'nullable|date_format:H:i',
            'attendance.*.time_out' => 'nullable|date_format:H:i',
            'attendance.*.remarks' => 'nullable|string|max:255',
        ]);

        try {
            $result = $this->attendanceService->markTeacherAttendance([
                'date' => $request->date,
                'attendance' => $request->attendance,
            ]);

            return redirect()->route('staff.attendance.teacher.mark', [
                'date' => $request->date,
            ])->with('success', "Teacher attendance marked successfully. {$result['marked_count']} records saved.");

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to mark attendance: ' . $e->getMessage());
        }
    }

    /**
     * Show the teacher attendance calendar.
     */
    public function teacherCalendar(Request $request)
    {
        $teachers = Teacher::with('user')
            ->whereHas('user', fn($q) => $q->where('status', 'active'))
            ->get();

        $selectedTeacherId = $request->input('teacher_id');
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        $calendarData = [];
        $stats = [];

        if ($selectedTeacherId) {
            $calendarData = $this->attendanceService->getTeacherAttendanceCalendar(
                $selectedTeacherId,
                $month,
                $year
            );
            $stats = $this->attendanceService->getTeacherStats($selectedTeacherId, $month, $year);
        }

        return view('staff.attendance.teacher-calendar', compact(
            'teachers',
            'selectedTeacherId',
            'month',
            'year',
            'calendarData',
            'stats'
        ));
    }

    /**
     * Get sessions for a class and date (AJAX).
     */
    public function getSessions(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'date' => 'required|date',
        ]);

        $sessions = ClassSession::where('class_id', $request->class_id)
            ->whereDate('session_date', $request->date)
            ->with(['class.subject'])
            ->orderBy('start_time')
            ->get();

        return response()->json([
            'sessions' => $sessions->map(function ($session) {
                return [
                    'id' => $session->id,
                    'time' => Carbon::parse($session->start_time)->format('h:i A') . ' - ' . 
                              Carbon::parse($session->end_time)->format('h:i A'),
                    'topic' => $session->topic ?? 'Regular Class',
                    'status' => $session->status,
                    'attendance_count' => $session->attendances->count(),
                ];
            }),
        ]);
    }

    /**
     * Get session attendance summary (AJAX).
     */
    public function getSessionSummary(ClassSession $session)
    {
        $summary = $this->attendanceService->getSessionSummary($session->id);
        
        return response()->json($summary);
    }

    /**
     * Export student attendance report.
     */
    public function exportStudent(Request $request)
    {
        $request->validate([
            'class_id' => 'nullable|exists:classes,id',
            'student_id' => 'nullable|exists:students,id',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'status' => 'nullable|in:present,absent,late,excused',
        ]);

        $filters = $request->only(['class_id', 'student_id', 'date_from', 'date_to', 'status']);
        $filename = 'student_attendance_' . now()->format('Y-m-d_His') . '.xlsx';

        return Excel::download(new StudentAttendanceExport($filters), $filename);
    }

    /**
     * Show attendance reports dashboard.
     */
    public function reports(Request $request)
    {
        $classes = ClassModel::where('status', 'active')
            ->with('subject')
            ->orderBy('name')
            ->get();

        $students = Student::with('user')
            ->whereHas('user', fn($q) => $q->where('status', 'active'))
            ->orderBy('student_id')
            ->get();

        // Get filter values
        $classId = $request->input('class_id');
        $studentId = $request->input('student_id');
        $dateFrom = $request->input('date_from', now()->startOfMonth()->toDateString());
        $dateTo = $request->input('date_to', now()->toDateString());

        // Get attendance data based on filters
        $attendanceData = collect();
        $summary = null;

        if ($classId || $studentId) {
            $query = StudentAttendance::with([
                'student.user',
                'classSession.class.subject',
                'markedBy'
            ]);

            if ($classId) {
                $query->whereHas('classSession', fn($q) => $q->where('class_id', $classId));
            }

            if ($studentId) {
                $query->where('student_id', $studentId);
            }

            if ($dateFrom) {
                $query->whereHas('classSession', fn($q) => 
                    $q->whereDate('session_date', '>=', $dateFrom)
                );
            }

            if ($dateTo) {
                $query->whereHas('classSession', fn($q) => 
                    $q->whereDate('session_date', '<=', $dateTo)
                );
            }

            $attendanceData = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

            // Calculate summary
            $allRecords = StudentAttendance::query();
            if ($classId) {
                $allRecords->whereHas('classSession', fn($q) => $q->where('class_id', $classId));
            }
            if ($studentId) {
                $allRecords->where('student_id', $studentId);
            }
            if ($dateFrom) {
                $allRecords->whereHas('classSession', fn($q) => 
                    $q->whereDate('session_date', '>=', $dateFrom)
                );
            }
            if ($dateTo) {
                $allRecords->whereHas('classSession', fn($q) => 
                    $q->whereDate('session_date', '<=', $dateTo)
                );
            }

            $records = $allRecords->get();
            $total = $records->count();
            $present = $records->where('status', 'present')->count();

            $summary = [
                'total' => $total,
                'present' => $present,
                'absent' => $records->where('status', 'absent')->count(),
                'late' => $records->where('status', 'late')->count(),
                'excused' => $records->where('status', 'excused')->count(),
                'attendance_rate' => $total > 0 ? round(($present / $total) * 100, 1) : 0,
            ];
        }

        return view('staff.attendance.reports', compact(
            'classes',
            'students',
            'classId',
            'studentId',
            'dateFrom',
            'dateTo',
            'attendanceData',
            'summary'
        ));
    }

    /**
     * Edit student attendance record.
     */
    public function editStudent($id)
    {
        $attendance = StudentAttendance::with([
            'student.user',
            'classSession.class.subject',
            'markedBy'
        ])->findOrFail($id);

        return view('staff.attendance.edit-student', compact('attendance'));
    }

    /**
     * Update student attendance record.
     */
    public function updateStudent(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:present,absent,late,excused',
            'check_in_time' => 'nullable|date_format:H:i',
            'remarks' => 'nullable|string|max:500',
        ]);

        try {
            $attendance = StudentAttendance::findOrFail($id);
            
            $attendance->update([
                'status' => $request->status,
                'check_in_time' => $request->check_in_time,
                'remarks' => $request->remarks,
                'marked_by' => auth()->id(),
            ]);

            return redirect()->route('staff.attendance.reports')
                ->with('success', 'Attendance record updated successfully.');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to update attendance: ' . $e->getMessage());
        }
    }

    /**
     * Edit teacher attendance record.
     */
    public function editTeacher($id)
    {
        $attendance = TeacherAttendance::with([
            'teacher.user',
            'markedBy'
        ])->findOrFail($id);

        return view('staff.attendance.edit-teacher', compact('attendance'));
    }

    /**
     * Update teacher attendance record.
     */
    public function updateTeacher(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:present,absent,half_day,on_leave',
            'time_in' => 'nullable|date_format:H:i',
            'time_out' => 'nullable|date_format:H:i',
            'remarks' => 'nullable|string|max:500',
        ]);

        try {
            $attendance = TeacherAttendance::findOrFail($id);
            
            // Calculate hours worked
            $hoursWorked = null;
            if ($request->time_in && $request->time_out) {
                $timeIn = Carbon::parse($request->time_in);
                $timeOut = Carbon::parse($request->time_out);
                $hoursWorked = $timeOut->diffInMinutes($timeIn) / 60;
            }

            $attendance->update([
                'status' => $request->status,
                'time_in' => $request->time_in,
                'time_out' => $request->time_out,
                'hours_worked' => $hoursWorked,
                'remarks' => $request->remarks,
                'marked_by' => auth()->id(),
            ]);

            return redirect()->route('staff.attendance.teacher.mark')
                ->with('success', 'Teacher attendance record updated successfully.');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to update attendance: ' . $e->getMessage());
        }
    }
}
