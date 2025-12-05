<?php

namespace App\Services;

use App\Models\StudentAttendance;
use App\Models\TeacherAttendance;
use App\Models\ClassSession;
use App\Models\ClassAttendanceSummary;
use App\Models\ActivityLog;
use App\Models\Student;
use App\Models\LowAttendanceAlert;
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

    // ==================== REPORT METHODS (NEW - Chat 14) ====================

    /**
     * Get parent's children attendance statistics
     */
    public function getChildrenAttendanceStats(int $parentId): array
    {
        $parent = \App\Models\Parents::with('students.user')->findOrFail($parentId);
        $stats = [];

        foreach ($parent->students as $student) {
            $stats[$student->id] = $this->getStudentMonthlyStats($student->id);
        }

        return $stats;
    }

    /**
     * Get student monthly stats
     */
    public function getStudentMonthlyStats(int $studentId, ?int $month = null, ?int $year = null): array
    {
        $month = $month ?? now()->month;
        $year = $year ?? now()->year;

        $records = StudentAttendance::where('student_id', $studentId)
            ->whereHas('classSession', function($q) use ($month, $year) {
                $q->whereMonth('session_date', $month)
                  ->whereYear('session_date', $year);
            })
            ->get();

        $total = $records->count();
        $present = $records->where('status', 'present')->count();
        $absent = $records->where('status', 'absent')->count();
        $late = $records->where('status', 'late')->count();
        $excused = $records->where('status', 'excused')->count();

        return [
            'total_sessions' => $total,
            'present' => $present,
            'absent' => $absent,
            'late' => $late,
            'excused' => $excused,
            'percentage' => $total > 0 ? round(($present / $total) * 100, 2) : 0,
        ];
    }

    /**
     * Get student class-wise attendance stats
     */
    public function getStudentClasswiseStats(int $studentId, ?int $month = null, ?int $year = null): \Illuminate\Support\Collection
    {
        $month = $month ?? now()->month;
        $year = $year ?? now()->year;

        $records = StudentAttendance::where('student_id', $studentId)
            ->whereHas('classSession', function($q) use ($month, $year) {
                $q->whereMonth('session_date', $month)
                  ->whereYear('session_date', $year);
            })
            ->with('classSession.class.subject')
            ->get();

        return $records->groupBy('classSession.class_id')
            ->map(function($classRecords) {
                $class = $classRecords->first()->classSession->class;
                $total = $classRecords->count();
                $present = $classRecords->where('status', 'present')->count();

                return [
                    'class_id' => $class->id,
                    'class_name' => $class->name,
                    'subject' => $class->subject->name ?? 'N/A',
                    'total' => $total,
                    'present' => $present,
                    'absent' => $classRecords->where('status', 'absent')->count(),
                    'late' => $classRecords->where('status', 'late')->count(),
                    'percentage' => $total > 0 ? round(($present / $total) * 100, 2) : 0,
                ];
            })->values();
    }

    /**
     * Get student attendance calendar data
     */
    public function getStudentAttendanceCalendar(int $studentId, int $month, int $year): array
    {
        $records = StudentAttendance::where('student_id', $studentId)
            ->whereHas('classSession', function($q) use ($month, $year) {
                $q->whereMonth('session_date', $month)
                  ->whereYear('session_date', $year);
            })
            ->with('classSession.class')
            ->get();

        $calendar = [];
        foreach ($records as $record) {
            $dateKey = $record->classSession->session_date->format('Y-m-d');
            
            // If multiple sessions on same day, prioritize worst status
            if (!isset($calendar[$dateKey]) || $this->getStatusPriority($record->status) > $this->getStatusPriority($calendar[$dateKey]['status'])) {
                $calendar[$dateKey] = [
                    'status' => $record->status,
                    'class_name' => $record->classSession->class->name,
                    'time' => $record->classSession->start_time->format('H:i'),
                ];
            }
        }

        return $calendar;
    }

    /**
     * Get status priority for comparison (higher = worse)
     */
    protected function getStatusPriority(string $status): int
    {
        return match($status) {
            'present' => 1,
            'excused' => 2,
            'late' => 3,
            'absent' => 4,
            default => 0,
        };
    }

    /**
     * Get recent attendance records for a student
     */
    public function getRecentAttendance(int $studentId, int $limit = 10)
    {
        return StudentAttendance::where('student_id', $studentId)
            ->with('classSession.class.subject')
            ->latest('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get overall attendance percentage for a student
     */
    public function getOverallAttendancePercentage(int $studentId): float
    {
        $totalRecords = StudentAttendance::where('student_id', $studentId)->count();
        
        if ($totalRecords === 0) {
            return 0;
        }

        $presentRecords = StudentAttendance::where('student_id', $studentId)
            ->where('status', 'present')
            ->count();

        return round(($presentRecords / $totalRecords) * 100, 2);
    }

    /**
     * Get parent's overall children attendance
     */
    public function getParentOverallAttendance(int $parentId): float
    {
        $parent = \App\Models\Parents::with('students')->findOrFail($parentId);
        
        if ($parent->students->isEmpty()) {
            return 0;
        }

        $totalPercentage = 0;
        $childCount = 0;

        foreach ($parent->students as $student) {
            $percentage = $this->getOverallAttendancePercentage($student->id);
            if ($percentage > 0) {
                $totalPercentage += $percentage;
                $childCount++;
            }
        }

        return $childCount > 0 ? round($totalPercentage / $childCount, 2) : 0;
    }

    /**
     * Check and auto-create low attendance alerts
     */
    public function autoCreateLowAttendanceAlerts(float $threshold = 75): int
    {
        $summaries = ClassAttendanceSummary::where('attendance_percentage', '<', $threshold)
            ->where('month', now()->month)
            ->where('year', now()->year)
            ->get();

        $alertsCreated = 0;

        foreach ($summaries as $summary) {
            $existingAlert = LowAttendanceAlert::where('student_id', $summary->student_id)
                ->where('class_id', $summary->class_id)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->exists();

            if (!$existingAlert) {
                LowAttendanceAlert::create([
                    'student_id' => $summary->student_id,
                    'class_id' => $summary->class_id,
                    'attendance_percentage' => $summary->attendance_percentage,
                    'threshold' => $threshold,
                    'status' => 'pending',
                ]);
                $alertsCreated++;
            }
        }

        return $alertsCreated;
    }

    /**
     * Get pending low attendance alerts for a parent
     */
    public function getParentLowAttendanceAlerts(int $parentId)
    {
        $parent = \App\Models\Parents::with('students')->findOrFail($parentId);
        $studentIds = $parent->students->pluck('id');

        return LowAttendanceAlert::whereIn('student_id', $studentIds)
            ->with(['student.user', 'class'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
    }

    /**
     * Export attendance history
     */
    public function exportAttendanceHistory(array $filters): \Illuminate\Support\Collection
    {
        $query = StudentAttendance::with([
            'student.user',
            'classSession.class.subject',
            'markedBy'
        ]);

        if (!empty($filters['student_id'])) {
            $query->where('student_id', $filters['student_id']);
        }

        if (!empty($filters['class_id'])) {
            $query->whereHas('classSession', fn($q) => $q->where('class_id', $filters['class_id']));
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereHas('classSession', fn($q) => 
                $q->whereDate('session_date', '>=', $filters['date_from'])
            );
        }

        if (!empty($filters['date_to'])) {
            $query->whereHas('classSession', fn($q) => 
                $q->whereDate('session_date', '<=', $filters['date_to'])
            );
        }

        return $query->orderBy('created_at', 'desc')->get();
    }
}
