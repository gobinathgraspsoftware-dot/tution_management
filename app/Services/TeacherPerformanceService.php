<?php

namespace App\Services;

use App\Models\Teacher;
use App\Models\TeacherAttendance;
use App\Models\ClassSession;
use App\Models\Material;
use App\Models\StudentReview;
use App\Models\TeacherPayslip;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TeacherPerformanceService
{
    /**
     * Get comprehensive performance metrics for a teacher
     */
    public function getPerformanceMetrics(Teacher $teacher, $startDate = null, $endDate = null): array
    {
        $startDate = $startDate ? Carbon::parse($startDate) : now()->startOfMonth();
        $endDate = $endDate ? Carbon::parse($endDate) : now()->endOfMonth();

        return [
            'classes_conducted' => $this->getClassesConducted($teacher, $startDate, $endDate),
            'attendance_rate' => $this->getAttendanceRate($teacher, $startDate, $endDate),
            'punctuality_rate' => $this->getPunctualityRate($teacher, $startDate, $endDate),
            'materials_uploaded' => $this->getMaterialsUploaded($teacher, $startDate, $endDate),
            'average_rating' => $this->getAverageRating($teacher, $startDate, $endDate),
            'total_reviews' => $this->getTotalReviews($teacher, $startDate, $endDate),
            'total_hours' => $this->getTotalHours($teacher, $startDate, $endDate),
            'performance_score' => 0, // Will be calculated separately
        ];
    }

    /**
     * Get classes conducted count
     */
    protected function getClassesConducted(Teacher $teacher, Carbon $startDate, Carbon $endDate): int
    {
        return ClassSession::whereHas('class', function ($q) use ($teacher) {
            $q->where('teacher_id', $teacher->id);
        })
        ->whereBetween('session_date', [$startDate, $endDate])
        ->where('status', 'completed')
        ->count();
    }

    /**
     * Get attendance rate
     */
    protected function getAttendanceRate(Teacher $teacher, Carbon $startDate, Carbon $endDate): float
    {
        $stats = TeacherAttendance::where('teacher_id', $teacher->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN status IN ("present", "half_day") THEN 1 ELSE 0 END) as present
            ')
            ->first();

        if (!$stats || $stats->total == 0) {
            return 100.0;
        }

        return round(($stats->present / $stats->total) * 100, 2);
    }

    /**
     * Get punctuality rate (on-time arrivals)
     */
    protected function getPunctualityRate(Teacher $teacher, Carbon $startDate, Carbon $endDate): float
    {
        $stats = TeacherAttendance::where('teacher_id', $teacher->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->whereIn('status', ['present', 'half_day'])
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN (remarks IS NULL OR remarks NOT LIKE "%late%") THEN 1 ELSE 0 END) as ontime
            ')
            ->first();

        if (!$stats || $stats->total == 0) {
            return 100.0;
        }

        return round(($stats->ontime / $stats->total) * 100, 2);
    }

    /**
     * Get materials uploaded count
     */
    protected function getMaterialsUploaded(Teacher $teacher, Carbon $startDate, Carbon $endDate): int
    {
        return Material::where('teacher_id', $teacher->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();
    }

    /**
     * Get average rating from student reviews
     */
    protected function getAverageRating(Teacher $teacher, Carbon $startDate, Carbon $endDate): float
    {
        $rating = StudentReview::where('teacher_id', $teacher->id)
            ->where('is_approved', true)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->avg('rating');

        return round($rating ?? 0, 2);
    }

    /**
     * Get total reviews count
     */
    protected function getTotalReviews(Teacher $teacher, Carbon $startDate, Carbon $endDate): int
    {
        return StudentReview::where('teacher_id', $teacher->id)
            ->where('is_approved', true)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();
    }

    /**
     * Get total teaching hours
     */
    protected function getTotalHours(Teacher $teacher, Carbon $startDate, Carbon $endDate): float
    {
        $result = DB::table('class_sessions')
            ->join('classes', 'class_sessions.class_id', '=', 'classes.id')
            ->where('classes.teacher_id', $teacher->id)
            ->whereBetween('class_sessions.session_date', [$startDate, $endDate])
            ->where('class_sessions.status', 'completed')
            ->whereNotNull('class_sessions.start_time')
            ->whereNotNull('class_sessions.end_time')
            ->selectRaw('SUM(TIMESTAMPDIFF(MINUTE, class_sessions.start_time, class_sessions.end_time)) as total_minutes')
            ->first();

        return round(($result->total_minutes ?? 0) / 60, 2);
    }

    /**
     * Calculate overall performance score (0-100)
     * Weighted average of different metrics
     */
    public function calculatePerformanceScore(Teacher $teacher, Carbon $startDate, Carbon $endDate): float
    {
        $weights = [
            'attendance' => 0.25,      // 25%
            'punctuality' => 0.15,     // 15%
            'classes' => 0.20,         // 20%
            'materials' => 0.15,       // 15%
            'ratings' => 0.25,         // 25%
        ];

        $metrics = $this->getPerformanceMetrics($teacher, $startDate, $endDate);

        // Normalize classes conducted (assuming 20 classes per month is excellent)
        $classesScore = min(($metrics['classes_conducted'] / 20) * 100, 100);

        // Normalize materials uploaded (assuming 10 materials per month is excellent)
        $materialsScore = min(($metrics['materials_uploaded'] / 10) * 100, 100);

        // Normalize ratings (out of 5)
        $ratingsScore = ($metrics['average_rating'] / 5) * 100;

        $totalScore =
            ($metrics['attendance_rate'] * $weights['attendance']) +
            ($metrics['punctuality_rate'] * $weights['punctuality']) +
            ($classesScore * $weights['classes']) +
            ($materialsScore * $weights['materials']) +
            ($ratingsScore * $weights['ratings']);

        return round($totalScore, 2);
    }

    /**
     * Get monthly performance trend
     */
    public function getMonthlyTrend(Teacher $teacher, int $months = 6): array
    {
        $data = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $startDate = $date->copy()->startOfMonth();
            $endDate = $date->copy()->endOfMonth();

            $metrics = $this->getPerformanceMetrics($teacher, $startDate, $endDate);
            $score = $this->calculatePerformanceScore($teacher, $startDate, $endDate);

            $data[] = [
                'month' => $date->format('M Y'),
                'year_month' => $date->format('Y-m'),
                'classes' => $metrics['classes_conducted'],
                'materials' => $metrics['materials_uploaded'],
                'rating' => $metrics['average_rating'],
                'attendance' => $metrics['attendance_rate'],
                'score' => $score,
            ];
        }

        return $data;
    }

    /**
     * Get comparative performance data for all teachers - OPTIMIZED with BULK QUERIES
     */
    public function getComparativeData($startDate = null, $endDate = null): array
    {
        $startDate = $startDate ? Carbon::parse($startDate) : now()->startOfMonth();
        $endDate = $endDate ? Carbon::parse($endDate) : now()->endOfMonth();

        // Get all active teachers with user relationship
        $teachers = Teacher::with('user')->active()->get();
        $teacherIds = $teachers->pluck('id')->toArray();

        if (empty($teacherIds)) {
            return [];
        }

        // BULK QUERY 1: Classes and Hours
        $classesData = DB::table('class_sessions')
            ->join('classes', 'class_sessions.class_id', '=', 'classes.id')
            ->whereIn('classes.teacher_id', $teacherIds)
            ->whereBetween('class_sessions.session_date', [$startDate, $endDate])
            ->where('class_sessions.status', 'completed')
            ->select(
                'classes.teacher_id',
                DB::raw('COUNT(*) as classes_count'),
                DB::raw('COALESCE(SUM(TIMESTAMPDIFF(MINUTE, class_sessions.start_time, class_sessions.end_time)), 0) as total_minutes')
            )
            ->groupBy('classes.teacher_id')
            ->get()
            ->keyBy('teacher_id');

        // BULK QUERY 2: Attendance rates
        $attendanceData = DB::table('teacher_attendance')
            ->whereIn('teacher_id', $teacherIds)
            ->whereBetween('date', [$startDate, $endDate])
            ->select(
                'teacher_id',
                DB::raw('COUNT(*) as total_records'),
                DB::raw('SUM(CASE WHEN status IN ("present", "half_day") THEN 1 ELSE 0 END) as present_records'),
                DB::raw('SUM(CASE WHEN status IN ("present", "half_day") AND (remarks IS NULL OR remarks NOT LIKE "%late%") THEN 1 ELSE 0 END) as ontime_records')
            )
            ->groupBy('teacher_id')
            ->get()
            ->keyBy('teacher_id');

        // BULK QUERY 3: Materials count
        $materialsData = DB::table('materials')
            ->whereIn('teacher_id', $teacherIds)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select('teacher_id', DB::raw('COUNT(*) as materials_count'))
            ->groupBy('teacher_id')
            ->get()
            ->keyBy('teacher_id');

        // BULK QUERY 4: Reviews ratings
        $reviewsData = DB::table('student_reviews')
            ->whereIn('teacher_id', $teacherIds)
            ->where('is_approved', true)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(
                'teacher_id',
                DB::raw('COALESCE(AVG(rating), 0) as avg_rating'),
                DB::raw('COUNT(*) as reviews_count')
            )
            ->groupBy('teacher_id')
            ->get()
            ->keyBy('teacher_id');

        // Build performance data array
        $data = [];
        foreach ($teachers as $teacher) {
            $teacherId = $teacher->id;

            // Get data from bulk queries
            $classes = $classesData->get($teacherId);
            $classesCount = $classes ? (int)$classes->classes_count : 0;
            $totalHours = $classes ? round($classes->total_minutes / 60, 2) : 0;

            $attendance = $attendanceData->get($teacherId);
            $attendanceRate = 100.0;
            $punctualityRate = 100.0;

            if ($attendance && $attendance->total_records > 0) {
                $attendanceRate = round(($attendance->present_records / $attendance->total_records) * 100, 2);
                if ($attendance->present_records > 0) {
                    $punctualityRate = round(($attendance->ontime_records / $attendance->present_records) * 100, 2);
                }
            }

            $materials = $materialsData->get($teacherId);
            $materialsCount = $materials ? (int)$materials->materials_count : 0;

            $reviews = $reviewsData->get($teacherId);
            $avgRating = $reviews ? round($reviews->avg_rating, 2) : 0;
            $reviewsCount = $reviews ? (int)$reviews->reviews_count : 0;

            // Calculate performance score
            $classesScore = min(($classesCount / 20) * 100, 100);
            $materialsScore = min(($materialsCount / 10) * 100, 100);
            $ratingsScore = ($avgRating / 5) * 100;

            $score = round(
                ($attendanceRate * 0.25) +
                ($punctualityRate * 0.15) +
                ($classesScore * 0.20) +
                ($materialsScore * 0.15) +
                ($ratingsScore * 0.25),
                2
            );

            $data[] = [
                'teacher_id' => $teacherId,
                'teacher_name' => $teacher->user->name,
                'employment_type' => $teacher->employment_type,
                'classes' => $classesCount,
                'hours' => $totalHours,
                'materials' => $materialsCount,
                'rating' => $avgRating,
                'reviews' => $reviewsCount,
                'attendance' => $attendanceRate,
                'punctuality' => $punctualityRate,
                'score' => $score,
            ];
        }

        // Sort by performance score descending
        usort($data, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        return $data;
    }

    /**
     * Get recent student reviews
     */
    public function getRecentReviews(Teacher $teacher, int $limit = 10): array
    {
        return StudentReview::where('teacher_id', $teacher->id)
            ->where('is_approved', true)
            ->with(['student.user', 'class'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($review) {
                return [
                    'student_name' => $review->student->user->name ?? 'Unknown',
                    'class_name' => $review->class->name ?? 'N/A',
                    'rating' => $review->rating,
                    'review' => $review->review,
                    'date' => $review->created_at->format('d M Y'),
                ];
            })
            ->toArray();
    }

    /**
     * Get performance summary for dashboard
     */
    public function getDashboardSummary(Teacher $teacher): array
    {
        $currentMonth = $this->getPerformanceMetrics($teacher);
        $currentMonth['performance_score'] = $this->calculatePerformanceScore($teacher, now()->startOfMonth(), now()->endOfMonth());

        $lastMonth = $this->getPerformanceMetrics(
            $teacher,
            now()->subMonth()->startOfMonth(),
            now()->subMonth()->endOfMonth()
        );
        $lastMonth['performance_score'] = $this->calculatePerformanceScore(
            $teacher,
            now()->subMonth()->startOfMonth(),
            now()->subMonth()->endOfMonth()
        );

        return [
            'current' => $currentMonth,
            'previous' => $lastMonth,
            'trends' => [
                'classes' => $this->calculateTrend($lastMonth['classes_conducted'], $currentMonth['classes_conducted']),
                'materials' => $this->calculateTrend($lastMonth['materials_uploaded'], $currentMonth['materials_uploaded']),
                'rating' => $this->calculateTrend($lastMonth['average_rating'], $currentMonth['average_rating']),
                'score' => $this->calculateTrend($lastMonth['performance_score'], $currentMonth['performance_score']),
            ],
            'recent_reviews' => $this->getRecentReviews($teacher, 5),
        ];
    }

    /**
     * Calculate trend percentage
     */
    protected function calculateTrend($previous, $current): array
    {
        if ($previous == 0) {
            return [
                'direction' => $current > 0 ? 'up' : 'neutral',
                'percentage' => $current > 0 ? 100 : 0,
            ];
        }

        $change = (($current - $previous) / $previous) * 100;

        return [
            'direction' => $change > 0 ? 'up' : ($change < 0 ? 'down' : 'neutral'),
            'percentage' => abs(round($change, 2)),
        ];
    }

    /**
     * Get top performers
     */
    public function getTopPerformers(int $limit = 10, $startDate = null, $endDate = null): array
    {
        $data = $this->getComparativeData($startDate, $endDate);
        return array_slice($data, 0, $limit);
    }

    /**
     * Get performance report data for export
     */
    public function getReportData(Teacher $teacher, $startDate, $endDate): array
    {
        $metrics = $this->getPerformanceMetrics($teacher, $startDate, $endDate);
        $metrics['performance_score'] = $this->calculatePerformanceScore($teacher, $startDate, $endDate);

        $trend = $this->getMonthlyTrend($teacher, 6);
        $reviews = $this->getRecentReviews($teacher, 20);

        return [
            'teacher' => [
                'name' => $teacher->user->name,
                'teacher_id' => $teacher->teacher_id,
                'employment_type' => $teacher->employment_type,
                'join_date' => $teacher->join_date->format('d M Y'),
            ],
            'period' => [
                'start' => Carbon::parse($startDate)->format('d M Y'),
                'end' => Carbon::parse($endDate)->format('d M Y'),
            ],
            'metrics' => $metrics,
            'trend' => $trend,
            'reviews' => $reviews,
        ];
    }
}
