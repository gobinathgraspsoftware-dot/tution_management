<?php

namespace App\Services;

use App\Models\Teacher;
use App\Models\TeacherAttendance;
use App\Models\StudentReview;
use App\Models\Material;
use App\Models\ClassSession;
use Carbon\Carbon;

class TeacherProfileService
{
    /**
     * Get teacher statistics.
     */
    public function getTeacherStatistics(Teacher $teacher): array
    {
        return [
            'total_classes' => $this->getTotalClasses($teacher),
            'active_classes' => $this->getActiveClasses($teacher),
            'total_students' => $this->getTotalStudents($teacher),
            'materials_uploaded' => $this->getMaterialsCount($teacher),
            'sessions_conducted' => $this->getSessionsConducted($teacher),
            'average_rating' => $this->getAverageRating($teacher),
            'attendance_rate' => $this->getAttendanceRate($teacher),
            'classes_this_month' => $this->getClassesThisMonth($teacher),
        ];
    }

    /**
     * Get total classes count.
     */
    public function getTotalClasses(Teacher $teacher): int
    {
        return $teacher->classes()->count();
    }

    /**
     * Get active classes count.
     */
    public function getActiveClasses(Teacher $teacher): int
    {
        return $teacher->classes()->where('status', 'active')->count();
    }

    /**
     * Get total students in all classes.
     */
    public function getTotalStudents(Teacher $teacher): int
    {
        return $teacher->classes()
            ->withCount(['enrollments' => function ($query) {
                $query->where('status', 'active');
            }])
            ->get()
            ->sum('enrollments_count');
    }

    /**
     * Get total materials uploaded.
     */
    public function getMaterialsCount(Teacher $teacher): int
    {
        return $teacher->materials()->count();
    }

    /**
     * Get sessions conducted this month.
     */
    public function getSessionsConducted(Teacher $teacher): int
    {
        return ClassSession::whereHas('class', function ($q) use ($teacher) {
            $q->where('teacher_id', $teacher->id);
        })
        ->where('status', 'completed')
        ->count();
    }

    /**
     * Get average rating from student reviews.
     */
    public function getAverageRating(Teacher $teacher): float
    {
        $reviews = StudentReview::where('teacher_id', $teacher->id)
            ->where('is_approved', true)
            ->avg('rating');

        return round($reviews ?? 0, 1);
    }

    /**
     * Get teacher attendance rate.
     */
    public function getAttendanceRate(Teacher $teacher): float
    {
        $totalRecords = TeacherAttendance::where('teacher_id', $teacher->id)
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->count();

        if ($totalRecords === 0) {
            return 100.0;
        }

        $presentRecords = TeacherAttendance::where('teacher_id', $teacher->id)
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->whereIn('status', ['present', 'half_day'])
            ->count();

        return round(($presentRecords / $totalRecords) * 100, 1);
    }

    /**
     * Get classes conducted this month.
     */
    public function getClassesThisMonth(Teacher $teacher): int
    {
        return ClassSession::whereHas('class', function ($q) use ($teacher) {
            $q->where('teacher_id', $teacher->id);
        })
        ->whereMonth('session_date', now()->month)
        ->whereYear('session_date', now()->year)
        ->where('status', 'completed')
        ->count();
    }

    /**
     * Get teacher performance data for dashboard.
     */
    public function getPerformanceData(Teacher $teacher, int $months = 6): array
    {
        $data = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $month = $date->format('M Y');

            $sessionsCount = ClassSession::whereHas('class', function ($q) use ($teacher) {
                $q->where('teacher_id', $teacher->id);
            })
            ->whereMonth('session_date', $date->month)
            ->whereYear('session_date', $date->year)
            ->where('status', 'completed')
            ->count();

            $materialsCount = Material::where('teacher_id', $teacher->id)
                ->whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->count();

            $data[] = [
                'month' => $month,
                'sessions' => $sessionsCount,
                'materials' => $materialsCount,
            ];
        }

        return $data;
    }

    /**
     * Get recent reviews for teacher.
     */
    public function getRecentReviews(Teacher $teacher, int $limit = 5): \Illuminate\Database\Eloquent\Collection
    {
        return StudentReview::where('teacher_id', $teacher->id)
            ->where('is_approved', true)
            ->with(['student.user', 'class'])
            ->latest()
            ->take($limit)
            ->get();
    }

    /**
     * Get teaching history.
     */
    public function getTeachingHistory(Teacher $teacher): array
    {
        $totalSessions = ClassSession::whereHas('class', function ($q) use ($teacher) {
            $q->where('teacher_id', $teacher->id);
        })->count();

        $completedSessions = ClassSession::whereHas('class', function ($q) use ($teacher) {
            $q->where('teacher_id', $teacher->id);
        })->where('status', 'completed')->count();

        $cancelledSessions = ClassSession::whereHas('class', function ($q) use ($teacher) {
            $q->where('teacher_id', $teacher->id);
        })->where('status', 'cancelled')->count();

        return [
            'total_sessions' => $totalSessions,
            'completed_sessions' => $completedSessions,
            'cancelled_sessions' => $cancelledSessions,
            'completion_rate' => $totalSessions > 0
                ? round(($completedSessions / $totalSessions) * 100, 1)
                : 0,
        ];
    }
}
