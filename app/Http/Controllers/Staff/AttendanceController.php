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

        // Get date - today by default
        $selectedDate = $request->input('date', Carbon::today()->toDateString());
        $selectedClassId = $request->input('class_id');

        $students = collect();
        $existingAttendance = collect();

        if ($selectedClassId) {
            // Get enrolled students for selected class - FIX: Use unique to prevent duplicates
            $students = Enrollment::where('class_id', $selectedClassId)
                ->where('status', 'active')
                ->with('student.user')
                ->get()
                ->pluck('student')
                ->unique('id')  // Prevent duplicate students
                ->filter()      // Remove null values
                ->values();     // Re-index collection

            // Get or create session for this class and date
            $session = ClassSession::firstOrCreate(
                [
                    'class_id' => $selectedClassId,
                    'session_date' => $selectedDate,
                ],
                [
                    'start_time' => '08:00:00',
                    'end_time' => '10:00:00',
                    'status' => 'scheduled',
                ]
            );

            // Get existing attendance for selected date
            $existingAttendance = StudentAttendance::where('class_session_id', $session->id)
                ->get()
                ->keyBy('student_id');
        }

        return view('staff.attendance.mark-student', compact(
            'classes',
            'selectedDate',
            'selectedClassId',
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
            'class_id' => 'required|exists:classes,id',
            'date' => 'required|date',
            'attendance' => 'required|array',
            'attendance.*.status' => 'nullable|in:present,absent,late,excused',
            'send_notifications' => 'nullable|boolean',
        ]);

        try {
            // Get or create session for this class and date
            $session = ClassSession::firstOrCreate(
                [
                    'class_id' => $request->class_id,
                    'session_date' => $request->date,
                ],
                [
                    'start_time' => '08:00:00',
                    'end_time' => '10:00:00',
                    'status' => 'scheduled',
                ]
            );

            $result = $this->attendanceService->markStudentAttendance([
                'session_id' => $session->id,
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

        $selectedClassId = $request->input('class_id');
        $selectedStudentId = $request->input('student_id');
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        // Get students based on selected class (like Mark Student Attendance)
        $students = collect();
        if ($selectedClassId) {
            $students = Enrollment::where('class_id', $selectedClassId)
                ->where('status', 'active')
                ->with('student.user')
                ->get()
                ->pluck('student')
                ->unique('id')
                ->filter()
                ->values();
        }

        $calendarData = [];
        $stats = [];
        $selectedStudent = null;

        if ($selectedStudentId) {
            // Individual student calendar
            $selectedStudent = Student::with('user')->find($selectedStudentId);
            $calendarData = $this->attendanceService->getStudentAttendanceCalendar(
                $selectedStudentId,
                $month,
                $year
            );
            $stats = $this->attendanceService->getStudentMonthlyStats($selectedStudentId, $month, $year);
        } elseif ($selectedClassId) {
            // Class calendar - build directly in controller
            $startDate = Carbon::create($year, $month, 1)->startOfMonth();
            $endDate = Carbon::create($year, $month, 1)->endOfMonth();

            $sessions = ClassSession::where('class_id', $selectedClassId)
                ->whereBetween('session_date', [$startDate, $endDate])
                ->with(['attendances.student.user'])
                ->get();

            foreach ($sessions as $session) {
                $dateKey = $session->session_date->format('Y-m-d');
                
                if (!isset($calendarData[$dateKey])) {
                    $calendarData[$dateKey] = [
                        'sessions' => [],
                        'summary' => ['present' => 0, 'absent' => 0, 'late' => 0, 'excused' => 0],
                    ];
                }

                // Get session summary
                $sessionSummary = [
                    'present' => $session->attendances->where('status', 'present')->count(),
                    'absent' => $session->attendances->where('status', 'absent')->count(),
                    'late' => $session->attendances->where('status', 'late')->count(),
                    'excused' => $session->attendances->where('status', 'excused')->count(),
                ];

                $calendarData[$dateKey]['sessions'][] = [
                    'id' => $session->id,
                    'topic' => $session->topic ?? '',
                    'time' => Carbon::parse($session->start_time)->format('H:i'),
                    'summary' => $sessionSummary,
                ];

                // Aggregate summary
                foreach (['present', 'absent', 'late', 'excused'] as $status) {
                    $calendarData[$dateKey]['summary'][$status] += $sessionSummary[$status];
                }
            }

            // Calculate class stats
            $totalPresent = 0;
            $totalAbsent = 0;
            $totalLate = 0;
            $totalExcused = 0;
            
            foreach ($calendarData as $dayData) {
                $totalPresent += $dayData['summary']['present'];
                $totalAbsent += $dayData['summary']['absent'];
                $totalLate += $dayData['summary']['late'];
                $totalExcused += $dayData['summary']['excused'];
            }
            
            $totalRecords = $totalPresent + $totalAbsent + $totalLate + $totalExcused;
            $stats = [
                'total_sessions' => $sessions->count(),
                'present' => $totalPresent,
                'absent' => $totalAbsent,
                'late' => $totalLate,
                'excused' => $totalExcused,
                'percentage' => $totalRecords > 0 ? round(($totalPresent / $totalRecords) * 100, 2) : 0,
            ];
        }

        return view('staff.attendance.student-calendar', compact(
            'classes',
            'students',
            'selectedClassId',
            'selectedStudentId',
            'selectedStudent',
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
            'attendance.*.status' => 'nullable|in:present,absent,half_day,leave',
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
        $selectedTeacher = null;

        if ($selectedTeacherId) {
            $selectedTeacher = Teacher::with('user')->find($selectedTeacherId);
            
            // Build calendar data directly
            $startDate = Carbon::create($year, $month, 1)->startOfMonth();
            $endDate = Carbon::create($year, $month, 1)->endOfMonth();

            $attendanceRecords = TeacherAttendance::where('teacher_id', $selectedTeacherId)
                ->whereBetween('date', [$startDate, $endDate])
                ->get()
                ->keyBy(function($item) {
                    return Carbon::parse($item->date)->format('Y-m-d');
                });

            // Build calendar data for each day
            $currentDate = $startDate->copy();
            while ($currentDate <= $endDate) {
                $dateKey = $currentDate->format('Y-m-d');
                $record = $attendanceRecords->get($dateKey);
                
                if ($record) {
                    $calendarData[$dateKey] = [
                        'id' => $record->id,
                        'status' => $record->status,
                        'time_in' => $record->time_in,
                        'time_out' => $record->time_out,
                        'hours_worked' => $record->hours_worked,
                        'remarks' => $record->remarks,
                    ];
                }
                
                $currentDate->addDay();
            }

            // Calculate stats
            $allRecords = TeacherAttendance::where('teacher_id', $selectedTeacherId)
                ->whereBetween('date', [$startDate, $endDate])
                ->get();

            $stats = [
                'working_days' => $allRecords->count(),
                'present' => $allRecords->where('status', 'present')->count(),
                'absent' => $allRecords->where('status', 'absent')->count(),
                'half_day' => $allRecords->where('status', 'half_day')->count(),
                'leave' => $allRecords->where('status', 'leave')->count(),
                'total_hours' => $allRecords->sum('hours_worked') ?? 0,
            ];
        }

        return view('staff.attendance.teacher-calendar', compact(
            'teachers',
            'selectedTeacherId',
            'selectedTeacher',
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
            'student_id' => 'required|exists:students,id',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
        ]);

        $student = Student::find($request->student_id);
        $filename = "attendance_{$student->student_id}_{$request->date_from}_{$request->date_to}.csv";

        $export = new StudentAttendanceExport(
            (int) $request->student_id,
            $request->date_from,
            $request->date_to,
            $request->class_id ? (int) $request->class_id : null
        );

        return $export->download($filename);
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
            'status' => 'required|in:present,absent,half_day,leave',
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
