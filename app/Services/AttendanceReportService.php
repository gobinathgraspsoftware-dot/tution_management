<?php

namespace App\Services;

use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\TeacherAttendance;
use App\Models\ClassModel;
use App\Models\ClassSession;
use App\Models\ClassAttendanceSummary;
use App\Models\LowAttendanceAlert;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceReportService
{
    /**
     * Get dashboard statistics
     */
    public function getDashboardStats(string $period = 'month'): array
    {
        $dates = $this->getPeriodDates($period);

        $studentStats = $this->getStudentAttendanceStats($dates['start'], $dates['end']);
        $teacherStats = $this->getTeacherAttendanceStats($dates['start'], $dates['end']);

        return [
            'student' => $studentStats,
            'teacher' => $teacherStats,
            'sessions' => [
                'total' => ClassSession::whereBetween('session_date', [$dates['start'], $dates['end']])->count(),
                'completed' => ClassSession::whereBetween('session_date', [$dates['start'], $dates['end']])
                    ->where('status', 'completed')->count(),
            ],
            'period' => $period,
            'date_range' => $dates,
        ];
    }

    /**
     * Get student attendance statistics for period
     */
    protected function getStudentAttendanceStats($startDate, $endDate): array
    {
        $records = StudentAttendance::whereHas('classSession', function($q) use ($startDate, $endDate) {
            $q->whereBetween('session_date', [$startDate, $endDate]);
        })->get();

        $total = $records->count();
        $present = $records->where('status', 'present')->count();
        $absent = $records->where('status', 'absent')->count();
        $late = $records->where('status', 'late')->count();
        $excused = $records->where('status', 'excused')->count();

        return [
            'total' => $total,
            'present' => $present,
            'absent' => $absent,
            'late' => $late,
            'excused' => $excused,
            'percentage' => $total > 0 ? round(($present / $total) * 100, 2) : 0,
            'notified' => $records->where('parent_notified', true)->count(),
        ];
    }

    /**
     * Get teacher attendance statistics for period
     */
    protected function getTeacherAttendanceStats($startDate, $endDate): array
    {
        $records = TeacherAttendance::whereBetween('date', [$startDate, $endDate])->get();

        $total = $records->count();
        $present = $records->where('status', 'present')->count();
        $absent = $records->where('status', 'absent')->count();
        $halfDay = $records->where('status', 'half_day')->count();
        $leave = $records->where('status', 'leave')->count();

        return [
            'total' => $total,
            'present' => $present,
            'absent' => $absent,
            'half_day' => $halfDay,
            'leave' => $leave,
            'percentage' => $total > 0 ? round(($present / $total) * 100, 2) : 0,
            'total_hours' => $records->sum('hours_worked'),
        ];
    }

    /**
     * Get period start and end dates
     */
    protected function getPeriodDates(string $period): array
    {
        $now = Carbon::now();

        return match($period) {
            'today' => [
                'start' => $now->copy()->startOfDay(),
                'end' => $now->copy()->endOfDay(),
            ],
            'week' => [
                'start' => $now->copy()->startOfWeek(),
                'end' => $now->copy()->endOfWeek(),
            ],
            'month' => [
                'start' => $now->copy()->startOfMonth(),
                'end' => $now->copy()->endOfMonth(),
            ],
            'quarter' => [
                'start' => $now->copy()->startOfQuarter(),
                'end' => $now->copy()->endOfQuarter(),
            ],
            'year' => [
                'start' => $now->copy()->startOfYear(),
                'end' => $now->copy()->endOfYear(),
            ],
            default => [
                'start' => $now->copy()->startOfMonth(),
                'end' => $now->copy()->endOfMonth(),
            ],
        };
    }

    /**
     * Get low attendance students
     */
    public function getLowAttendanceStudents(float $threshold = 75, int $limit = 10)
    {
        return ClassAttendanceSummary::with(['student.user', 'class.subject'])
            ->where('attendance_percentage', '<', $threshold)
            ->where('month', now()->month)
            ->where('year', now()->year)
            ->orderBy('attendance_percentage', 'asc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get low attendance query builder
     */
    public function getLowAttendanceQuery(float $threshold = 75)
    {
        return ClassAttendanceSummary::with(['student.user', 'class.subject'])
            ->where('attendance_percentage', '<', $threshold)
            ->where('month', now()->month)
            ->where('year', now()->year)
            ->orderBy('attendance_percentage', 'asc');
    }

    /**
     * Get attendance trends for charts
     */
    public function getAttendanceTrends(string $period = 'month'): array
    {
        $trends = [];
        $dates = $this->getPeriodDates($period);

        if ($period === 'month') {
            // Daily trends for month
            $current = $dates['start']->copy();
            while ($current <= $dates['end']) {
                $dayRecords = StudentAttendance::whereHas('classSession', function($q) use ($current) {
                    $q->whereDate('session_date', $current);
                })->get();

                $total = $dayRecords->count();
                $present = $dayRecords->where('status', 'present')->count();

                $trends[] = [
                    'date' => $current->format('M d'),
                    'total' => $total,
                    'present' => $present,
                    'percentage' => $total > 0 ? round(($present / $total) * 100, 2) : 0,
                ];

                $current->addDay();
            }
        } elseif ($period === 'year') {
            // Monthly trends for year
            for ($month = 1; $month <= 12; $month++) {
                $startOfMonth = Carbon::create(now()->year, $month, 1)->startOfMonth();
                $endOfMonth = Carbon::create(now()->year, $month, 1)->endOfMonth();

                $monthRecords = StudentAttendance::whereHas('classSession', function($q) use ($startOfMonth, $endOfMonth) {
                    $q->whereBetween('session_date', [$startOfMonth, $endOfMonth]);
                })->get();

                $total = $monthRecords->count();
                $present = $monthRecords->where('status', 'present')->count();

                $trends[] = [
                    'date' => $startOfMonth->format('M'),
                    'total' => $total,
                    'present' => $present,
                    'percentage' => $total > 0 ? round(($present / $total) * 100, 2) : 0,
                ];
            }
        } else {
            // Weekly trends
            $current = $dates['start']->copy();
            while ($current <= $dates['end']) {
                $weekEnd = $current->copy()->addDays(6);
                if ($weekEnd > $dates['end']) {
                    $weekEnd = $dates['end'];
                }

                $weekRecords = StudentAttendance::whereHas('classSession', function($q) use ($current, $weekEnd) {
                    $q->whereBetween('session_date', [$current, $weekEnd]);
                })->get();

                $total = $weekRecords->count();
                $present = $weekRecords->where('status', 'present')->count();

                $trends[] = [
                    'date' => $current->format('M d'),
                    'total' => $total,
                    'present' => $present,
                    'percentage' => $total > 0 ? round(($present / $total) * 100, 2) : 0,
                ];

                $current->addWeek();
            }
        }

        return $trends;
    }

    /**
     * Get class attendance comparison
     */
    public function getClassAttendanceComparison(): array
    {
        return ClassModel::where('status', 'active')
            ->with('subject')
            ->get()
            ->map(function($class) {
                $summary = ClassAttendanceSummary::where('class_id', $class->id)
                    ->where('month', now()->month)
                    ->where('year', now()->year)
                    ->get();

                $avgPercentage = $summary->avg('attendance_percentage') ?? 0;

                return [
                    'id' => $class->id,
                    'name' => $class->name,
                    'subject' => $class->subject->name ?? 'N/A',
                    'percentage' => round($avgPercentage, 2),
                    'student_count' => $class->current_enrollment,
                ];
            })
            ->sortByDesc('percentage')
            ->values()
            ->toArray();
    }

    /**
     * Generate student attendance report
     */
    public function generateStudentReport(int $studentId, string $dateFrom, string $dateTo, ?int $classId = null): array
    {
        $student = Student::with(['user', 'parent.user'])->findOrFail($studentId);

        $query = StudentAttendance::with(['classSession.class.subject', 'markedBy'])
            ->where('student_id', $studentId)
            ->whereHas('classSession', function($q) use ($dateFrom, $dateTo) {
                $q->whereBetween('session_date', [$dateFrom, $dateTo]);
            });

        if ($classId) {
            $query->whereHas('classSession', fn($q) => $q->where('class_id', $classId));
        }

        $records = $query->orderBy('created_at', 'desc')->get();

        // Calculate summary
        $total = $records->count();
        $present = $records->where('status', 'present')->count();
        $absent = $records->where('status', 'absent')->count();
        $late = $records->where('status', 'late')->count();
        $excused = $records->where('status', 'excused')->count();

        // Group by class
        $byClass = $records->groupBy(fn($r) => $r->classSession->class_id)
            ->map(function($classRecords) {
                $class = $classRecords->first()->classSession->class;
                $classTotal = $classRecords->count();
                $classPresent = $classRecords->where('status', 'present')->count();

                return [
                    'class_name' => $class->name,
                    'subject' => $class->subject->name ?? 'N/A',
                    'total' => $classTotal,
                    'present' => $classPresent,
                    'absent' => $classRecords->where('status', 'absent')->count(),
                    'late' => $classRecords->where('status', 'late')->count(),
                    'excused' => $classRecords->where('status', 'excused')->count(),
                    'percentage' => $classTotal > 0 ? round(($classPresent / $classTotal) * 100, 2) : 0,
                ];
            })->values();

        // Group by month
        $byMonth = $records->groupBy(fn($r) => $r->classSession->session_date->format('Y-m'))
            ->map(function($monthRecords, $monthKey) {
                $monthTotal = $monthRecords->count();
                $monthPresent = $monthRecords->where('status', 'present')->count();

                return [
                    'month' => Carbon::parse($monthKey . '-01')->format('F Y'),
                    'total' => $monthTotal,
                    'present' => $monthPresent,
                    'absent' => $monthRecords->where('status', 'absent')->count(),
                    'percentage' => $monthTotal > 0 ? round(($monthPresent / $monthTotal) * 100, 2) : 0,
                ];
            })->values();

        return [
            'student' => $student,
            'date_range' => [
                'from' => $dateFrom,
                'to' => $dateTo,
            ],
            'summary' => [
                'total_sessions' => $total,
                'present' => $present,
                'absent' => $absent,
                'late' => $late,
                'excused' => $excused,
                'percentage' => $total > 0 ? round(($present / $total) * 100, 2) : 0,
            ],
            'by_class' => $byClass,
            'by_month' => $byMonth,
            'records' => $records,
        ];
    }

    /**
     * Generate class attendance report
     */
    public function generateClassReport(int $classId, string $dateFrom, string $dateTo): array
    {
        $class = ClassModel::with(['subject', 'teacher.user', 'enrollments.student.user'])
            ->findOrFail($classId);

        $sessions = ClassSession::where('class_id', $classId)
            ->whereBetween('session_date', [$dateFrom, $dateTo])
            ->with('attendance.student.user')
            ->orderBy('session_date', 'desc')
            ->get();

        // Calculate overall stats
        $totalAttendance = StudentAttendance::whereIn('class_session_id', $sessions->pluck('id'))->get();
        $total = $totalAttendance->count();
        $present = $totalAttendance->where('status', 'present')->count();

        // Get student-wise breakdown
        $studentStats = $class->enrollments->map(function($enrollment) use ($sessions) {
            $studentAttendance = StudentAttendance::where('student_id', $enrollment->student_id)
                ->whereIn('class_session_id', $sessions->pluck('id'))
                ->get();

            $studentTotal = $studentAttendance->count();
            $studentPresent = $studentAttendance->where('status', 'present')->count();

            return [
                'student_id' => $enrollment->student_id,
                'student_name' => $enrollment->student->user->name ?? 'N/A',
                'total' => $studentTotal,
                'present' => $studentPresent,
                'absent' => $studentAttendance->where('status', 'absent')->count(),
                'late' => $studentAttendance->where('status', 'late')->count(),
                'excused' => $studentAttendance->where('status', 'excused')->count(),
                'percentage' => $studentTotal > 0 ? round(($studentPresent / $studentTotal) * 100, 2) : 0,
            ];
        })->sortByDesc('percentage')->values();

        // Session-wise breakdown
        $sessionStats = $sessions->map(function($session) {
            $sessionAttendance = $session->attendance;
            $sessionTotal = $sessionAttendance->count();
            $sessionPresent = $sessionAttendance->where('status', 'present')->count();

            return [
                'session_id' => $session->id,
                'date' => $session->session_date->format('d/m/Y'),
                'topic' => $session->topic,
                'time' => $session->start_time->format('H:i') . ' - ' . $session->end_time->format('H:i'),
                'total' => $sessionTotal,
                'present' => $sessionPresent,
                'absent' => $sessionAttendance->where('status', 'absent')->count(),
                'percentage' => $sessionTotal > 0 ? round(($sessionPresent / $sessionTotal) * 100, 2) : 0,
            ];
        });

        return [
            'class' => $class,
            'date_range' => [
                'from' => $dateFrom,
                'to' => $dateTo,
            ],
            'summary' => [
                'total_sessions' => $sessions->count(),
                'total_records' => $total,
                'present' => $present,
                'absent' => $totalAttendance->where('status', 'absent')->count(),
                'late' => $totalAttendance->where('status', 'late')->count(),
                'excused' => $totalAttendance->where('status', 'excused')->count(),
                'percentage' => $total > 0 ? round(($present / $total) * 100, 2) : 0,
                'enrolled_students' => $class->enrollments->count(),
            ],
            'student_stats' => $studentStats,
            'session_stats' => $sessionStats,
            'sessions' => $sessions,
        ];
    }

    /**
     * Check and create low attendance alerts
     */
    public function checkAndCreateAlerts(float $threshold = 75): int
    {
        $lowAttendance = $this->getLowAttendanceStudents($threshold, 1000);
        $alertsCreated = 0;

        foreach ($lowAttendance as $summary) {
            // Check if alert already exists for this month
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
     * Get attendance statistics for a specific student
     */
    public function getStudentStats(int $studentId): array
    {
        $allTime = StudentAttendance::where('student_id', $studentId)->get();
        $thisMonth = StudentAttendance::where('student_id', $studentId)
            ->whereHas('classSession', fn($q) => $q->whereMonth('session_date', now()->month))
            ->get();

        return [
            'all_time' => [
                'total' => $allTime->count(),
                'present' => $allTime->where('status', 'present')->count(),
                'percentage' => $allTime->count() > 0
                    ? round(($allTime->where('status', 'present')->count() / $allTime->count()) * 100, 2)
                    : 0,
            ],
            'this_month' => [
                'total' => $thisMonth->count(),
                'present' => $thisMonth->where('status', 'present')->count(),
                'percentage' => $thisMonth->count() > 0
                    ? round(($thisMonth->where('status', 'present')->count() / $thisMonth->count()) * 100, 2)
                    : 0,
            ],
        ];
    }
}
