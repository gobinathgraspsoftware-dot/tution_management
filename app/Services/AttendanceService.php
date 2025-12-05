<?php

namespace App\Services;

use App\Models\StudentAttendance;
use App\Models\TeacherAttendance;
use App\Models\ClassSession;
use App\Models\ClassAttendanceSummary;
use App\Models\ActivityLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Get today's attendance statistics
     */
    public function getTodayStats(): array
    {
        $today = now()->toDateString();

        return [
            'total_sessions' => ClassSession::whereDate('session_date', $today)->count(),
            'completed_sessions' => ClassSession::whereDate('session_date', $today)
                ->where('status', 'completed')->count(),
            'students_present' => StudentAttendance::whereHas('classSession', function($q) use ($today) {
                $q->whereDate('session_date', $today);
            })->where('status', 'present')->count(),
            'students_absent' => StudentAttendance::whereHas('classSession', function($q) use ($today) {
                $q->whereDate('session_date', $today);
            })->where('status', 'absent')->count(),
            'teachers_present' => TeacherAttendance::whereDate('date', $today)
                ->where('status', 'present')->count(),
            'teachers_absent' => TeacherAttendance::whereDate('date', $today)
                ->where('status', 'absent')->count(),
        ];
    }

    /**
     * Get this week's statistics
     */
    public function getWeekStats(): array
    {
        $startOfWeek = now()->startOfWeek()->toDateString();
        $endOfWeek = now()->endOfWeek()->toDateString();

        return [
            'total_sessions' => ClassSession::whereBetween('session_date', [$startOfWeek, $endOfWeek])->count(),
            'attendance_rate' => $this->calculateWeekAttendanceRate($startOfWeek, $endOfWeek),
        ];
    }

    /**
     * Calculate attendance rate for a period
     */
    protected function calculateWeekAttendanceRate($start, $end): float
    {
        $totalRecords = StudentAttendance::whereHas('classSession', function($q) use ($start, $end) {
            $q->whereBetween('session_date', [$start, $end]);
        })->count();

        if ($totalRecords == 0) return 0;

        $presentRecords = StudentAttendance::whereHas('classSession', function($q) use ($start, $end) {
            $q->whereBetween('session_date', [$start, $end]);
        })->where('status', 'present')->count();

        return round(($presentRecords / $totalRecords) * 100, 2);
    }

    /**
     * Mark student attendance
     */
    public function markStudentAttendance(array $data): array
    {
        DB::beginTransaction();
        try {
            $markedCount = 0;
            $notificationsSent = 0;

            foreach ($data['attendance'] as $studentId => $attendanceData) {
                // Skip if no status selected
                if (empty($attendanceData['status'])) {
                    continue;
                }

                // Create or update attendance record
                $attendance = StudentAttendance::updateOrCreate(
                    [
                        'class_session_id' => $data['session_id'],
                        'student_id' => $studentId,
                    ],
                    [
                        'status' => $attendanceData['status'],
                        'check_in_time' => $attendanceData['check_in_time'] ?? null,
                        'remarks' => $attendanceData['remarks'] ?? null,
                        'marked_by' => auth()->id(),
                    ]
                );

                $markedCount++;

                // Send parent notification if enabled
                if (isset($data['send_notifications']) && $data['send_notifications']) {
                    $this->sendAttendanceNotification($attendance);
                    $notificationsSent++;

                    // Mark as notified
                    $attendance->update([
                        'parent_notified' => true,
                        'notified_at' => now(),
                    ]);
                }

                // Log activity
                ActivityLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'marked_student_attendance',
                    'table_name' => 'student_attendance',
                    'record_id' => $attendance->id,
                    'description' => "Marked attendance for student ID: {$studentId} as {$attendanceData['status']}",
                ]);
            }

            // Update class attendance summary
            $this->updateAttendanceSummary($data['session_id']);

            DB::commit();
            return [
                'marked_count' => $markedCount,
                'notifications_sent' => $notificationsSent,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Send attendance notification to parent
     */
    protected function sendAttendanceNotification($attendance)
    {
        $student = $attendance->student;
        $session = $attendance->classSession;

        if ($student->user && $student->user->parent) {
            $parent = $student->user->parent;
            $parentUser = $parent->user;

            $data = [
                'student_name' => $student->user->name,
                'attendance_status' => ucfirst($attendance->status),
                'attendance_date' => $session->session_date->format('d/m/Y'),
                'class_name' => $session->class->name,
                'subject_name' => $session->class->subject->name ?? 'N/A',
                'time' => $session->start_time->format('H:i'),
            ];

            $this->notificationService->send(
                $parentUser,
                'attendance',
                $data,
                ['whatsapp']
            );
        }
    }

    /**
     * Update attendance summary
     */
    protected function updateAttendanceSummary($sessionId)
    {
        $session = ClassSession::findOrFail($sessionId);
        $month = $session->session_date->month;
        $year = $session->session_date->year;

        $students = $session->class->enrollments->pluck('student_id');

        foreach ($students as $studentId) {
            $attendanceRecords = StudentAttendance::whereHas('classSession', function($q) use ($session, $month, $year) {
                $q->where('class_id', $session->class_id)
                  ->whereMonth('session_date', $month)
                  ->whereYear('session_date', $year);
            })->where('student_id', $studentId)->get();

            $totalSessions = $attendanceRecords->count();
            $presentCount = $attendanceRecords->where('status', 'present')->count();
            $absentCount = $attendanceRecords->where('status', 'absent')->count();
            $lateCount = $attendanceRecords->where('status', 'late')->count();
            $excusedCount = $attendanceRecords->where('status', 'excused')->count();

            $percentage = $totalSessions > 0 ? round(($presentCount / $totalSessions) * 100, 2) : 0;

            ClassAttendanceSummary::updateOrCreate(
                [
                    'class_id' => $session->class_id,
                    'student_id' => $studentId,
                    'month' => $month,
                    'year' => $year,
                ],
                [
                    'total_sessions' => $totalSessions,
                    'present_count' => $presentCount,
                    'absent_count' => $absentCount,
                    'late_count' => $lateCount,
                    'excused_count' => $excusedCount,
                    'attendance_percentage' => $percentage,
                ]
            );
        }
    }

    /**
     * Get attendance records for a session
     */
    public function getSessionAttendance($sessionId)
    {
        return StudentAttendance::where('class_session_id', $sessionId)
            ->with('student.user', 'markedBy')
            ->get()
            ->keyBy('student_id');
    }

    /**
     * Get session summary
     */
    public function getSessionSummary($sessionId): array
    {
        $attendance = StudentAttendance::where('class_session_id', $sessionId)->get();

        return [
            'total' => $attendance->count(),
            'present' => $attendance->where('status', 'present')->count(),
            'absent' => $attendance->where('status', 'absent')->count(),
            'late' => $attendance->where('status', 'late')->count(),
            'excused' => $attendance->where('status', 'excused')->count(),
        ];
    }

    /**
     * Get student calendar data
     */
    public function getStudentCalendarData($classId, $month): array
    {
        $date = Carbon::parse($month . '-01');
        $startDate = $date->copy()->startOfMonth();
        $endDate = $date->copy()->endOfMonth();

        $sessions = ClassSession::where('class_id', $classId)
            ->whereBetween('session_date', [$startDate, $endDate])
            ->with('attendance.student.user')
            ->get();

        $calendarData = [];
        foreach ($sessions as $session) {
            $dateKey = $session->session_date->format('Y-m-d');

            if (!isset($calendarData[$dateKey])) {
                $calendarData[$dateKey] = [
                    'sessions' => [],
                    'summary' => ['present' => 0, 'absent' => 0, 'late' => 0, 'excused' => 0],
                ];
            }

            $summary = $this->getSessionSummary($session->id);
            $calendarData[$dateKey]['sessions'][] = [
                'id' => $session->id,
                'topic' => $session->topic,
                'time' => $session->start_time->format('H:i'),
                'summary' => $summary,
            ];

            // Aggregate summary
            foreach (['present', 'absent', 'late', 'excused'] as $status) {
                $calendarData[$dateKey]['summary'][$status] += $summary[$status];
            }
        }

        return $calendarData;
    }

    // ==================== TEACHER ATTENDANCE ====================

    /**
     * Mark teacher attendance
     */
    public function markTeacherAttendance(array $data): array
    {
        DB::beginTransaction();
        try {
            $markedCount = 0;

            foreach ($data['attendance'] as $teacherId => $attendanceData) {
                // Skip if no status selected
                if (empty($attendanceData['status'])) {
                    continue;
                }

                // Calculate hours worked
                $hoursWorked = null;
                if ($attendanceData['time_in'] && $attendanceData['time_out']) {
                    $timeIn = Carbon::parse($attendanceData['time_in']);
                    $timeOut = Carbon::parse($attendanceData['time_out']);
                    $hoursWorked = $timeOut->diffInMinutes($timeIn) / 60;
                }

                // Create or update attendance record
                $attendance = TeacherAttendance::updateOrCreate(
                    [
                        'teacher_id' => $teacherId,
                        'date' => $data['date'],
                    ],
                    [
                        'status' => $attendanceData['status'],
                        'time_in' => $attendanceData['time_in'] ?? null,
                        'time_out' => $attendanceData['time_out'] ?? null,
                        'hours_worked' => $hoursWorked,
                        'remarks' => $attendanceData['remarks'] ?? null,
                        'marked_by' => auth()->id(),
                    ]
                );

                $markedCount++;

                // Log activity
                ActivityLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'marked_teacher_attendance',
                    'table_name' => 'teacher_attendance',
                    'record_id' => $attendance->id,
                    'description' => "Marked attendance for teacher ID: {$teacherId} as {$attendanceData['status']}",
                ]);
            }

            DB::commit();
            return ['marked_count' => $markedCount];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get teacher attendance by date
     */
    public function getTeacherAttendanceByDate($date)
    {
        return TeacherAttendance::whereDate('date', $date)
            ->with('teacher.user', 'markedBy')
            ->get()
            ->keyBy('teacher_id');
    }

    /**
     * Get teacher calendar data
     */
    public function getTeacherCalendarData($teacherId, $month): array
    {
        $date = Carbon::parse($month . '-01');
        $startDate = $date->copy()->startOfMonth();
        $endDate = $date->copy()->endOfMonth();

        $attendance = TeacherAttendance::where('teacher_id', $teacherId)
            ->whereBetween('date', [$startDate, $endDate])
            ->get()
            ->keyBy(function($item) {
                return $item->date->format('Y-m-d');
            });

        $calendarData = [];
        $current = $startDate->copy();

        while ($current <= $endDate) {
            $dateKey = $current->format('Y-m-d');
            $record = $attendance->get($dateKey);

            $calendarData[$dateKey] = [
                'status' => $record ? $record->status : null,
                'time_in' => $record ? $record->time_in?->format('H:i') : null,
                'time_out' => $record ? $record->time_out?->format('H:i') : null,
                'hours_worked' => $record ? $record->hours_worked : null,
                'remarks' => $record ? $record->remarks : null,
            ];

            $current->addDay();
        }

        return $calendarData;
    }
}
