<?php

namespace App\Services;

use App\Models\ClassModel;
use App\Models\ClassSchedule;
use Illuminate\Support\Facades\DB;

class ClassService
{
    /**
     * Create a new class with automatic code generation.
     * Code is ALWAYS auto-generated to prevent duplicate entry conflicts
     * with soft-deleted records.
     */
    public function createClass(array $data)
    {
        return DB::transaction(function () use ($data) {
            // Always auto-generate class code to prevent duplicate conflicts
            $data['code'] = $this->generateClassCode($data['subject_id']);

            // Create class
            $class = ClassModel::create($data);

            return $class;
        });
    }

    /**
     * Update an existing class.
     */
    public function updateClass(ClassModel $class, array $data)
    {
        return DB::transaction(function () use ($class, $data) {
            // Remove code from update data - code is immutable after creation
            unset($data['code']);

            $class->update($data);

            // Check if capacity changed and update status accordingly
            if (isset($data['capacity'])) {
                if ($class->current_enrollment >= $class->capacity) {
                    $class->update(['status' => 'full']);
                } elseif ($class->status === 'full') {
                    $class->update(['status' => 'active']);
                }
            }

            return $class;
        });
    }

    /**
     * Generate unique class code.
     * 
     * FIXED: Uses withTrashed() to check ALL records including soft-deleted.
     * Uses REGEXP to only match clean sequential codes (excludes _del_ suffixed codes).
     * Uses do-while uniqueness loop as final safety net.
     * 
     * Pattern: {SUBJECT_PREFIX}{3-digit-number} e.g., SEJ001, MAT002, SCI015
     */
    public function generateClassCode($subjectId)
    {
        $subject = \App\Models\Subject::find($subjectId);

        if (!$subject) {
            $prefix = 'CLS';
        } else {
            $prefix = strtoupper(substr($subject->code ?? $subject->name, 0, 3));
        }

        // Sanitize prefix - only alphanumeric characters allowed
        $prefix = preg_replace('/[^A-Z0-9]/', '', $prefix);
        if (empty($prefix)) {
            $prefix = 'CLS';
        }

        // Find the highest numbered code with this prefix INCLUDING soft-deleted records
        // REGEXP ensures we only match clean sequential codes like SEJ001, SEJ002
        // and NOT deleted codes like SEJ001_del_1707465600
        $lastClass = ClassModel::withTrashed()
            ->where('code', 'REGEXP', '^' . $prefix . '[0-9]+$')
            ->orderByRaw('CAST(SUBSTRING(code, ' . (strlen($prefix) + 1) . ') AS UNSIGNED) DESC')
            ->first();

        if ($lastClass) {
            $numberPart = substr($lastClass->code, strlen($prefix));
            $number = intval($numberPart) + 1;
        } else {
            $number = 1;
        }

        // Safety do-while loop guarantees absolute uniqueness
        // Handles edge cases: manually inserted codes, race conditions, etc.
        $maxAttempts = 100;
        $attempts = 0;

        do {
            $code = $prefix . str_pad($number, 3, '0', STR_PAD_LEFT);
            $exists = ClassModel::withTrashed()->where('code', $code)->exists();

            if ($exists) {
                $number++;
            }
            $attempts++;
        } while ($exists && $attempts < $maxAttempts);

        // Final fallback: if somehow all sequential codes are taken, use timestamp
        if ($exists) {
            $code = $prefix . date('ymdHis');
        }

        return $code;
    }

    /**
     * Release class code on soft-delete by renaming it.
     * This frees up the original code for reuse in new classes.
     * 
     * Pattern: SEJ001 → SEJ001_del_1707465600
     * The _del_ suffix ensures REGEXP in generateClassCode() naturally excludes it.
     * 
     * @param ClassModel $class
     * @return string The original code that was released
     */
    public function releaseClassCode(ClassModel $class)
    {
        $originalCode = $class->code;
        $releasedCode = $originalCode . '_del_' . time();

        // Directly update code in DB to bypass model events
        DB::table('classes')
            ->where('id', $class->id)
            ->update(['code' => $releasedCode]);

        return $originalCode;
    }

    /**
     * Preview what code would be generated for a subject.
     * Used by AJAX endpoint in ClassController::generateCode().
     * 
     * @param int $subjectId
     * @return string
     */
    public function previewClassCode($subjectId)
    {
        return $this->generateClassCode($subjectId);
    }

    /**
     * Check for schedule conflicts.
     */
    public function checkScheduleConflict($teacherId, $dayOfWeek, $startTime, $endTime, $excludeScheduleId = null)
    {
        return ClassSchedule::whereHas('class', function($query) use ($teacherId) {
                $query->where('teacher_id', $teacherId)
                      ->where('status', 'active');
            })
            ->where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->when($excludeScheduleId, fn($q) => $q->where('id', '!=', $excludeScheduleId))
            ->where(function($query) use ($startTime, $endTime) {
                // Check if times overlap
                $query->where(function($q) use ($startTime, $endTime) {
                    // New schedule starts during existing schedule
                    $q->where('start_time', '<=', $startTime)
                      ->where('end_time', '>', $startTime);
                })->orWhere(function($q) use ($startTime, $endTime) {
                    // New schedule ends during existing schedule
                    $q->where('start_time', '<', $endTime)
                      ->where('end_time', '>=', $endTime);
                })->orWhere(function($q) use ($startTime, $endTime) {
                    // New schedule completely overlaps existing schedule
                    $q->where('start_time', '>=', $startTime)
                      ->where('end_time', '<=', $endTime);
                });
            })
            ->get();
    }

    /**
     * Calculate attendance rate for a class.
     */
    public function calculateAttendanceRate($classId)
    {
        $sessions = \App\Models\ClassSession::where('class_id', $classId)
            ->where('status', 'completed')
            ->count();

        if ($sessions === 0) {
            return 0;
        }

        $totalAttendance = \App\Models\StudentAttendance::whereHas('classSession', function($q) use ($classId) {
                $q->where('class_id', $classId);
            })
            ->count();

        $enrollments = \App\Models\Enrollment::where('class_id', $classId)
            ->where('status', 'active')
            ->count();

        $expectedAttendance = $sessions * $enrollments;

        if ($expectedAttendance === 0) {
            return 0;
        }

        $presentCount = \App\Models\StudentAttendance::whereHas('classSession', function($q) use ($classId) {
                $q->where('class_id', $classId);
            })
            ->where('status', 'present')
            ->count();

        return round(($presentCount / $expectedAttendance) * 100, 2);
    }

    /**
     * Check if class has available capacity.
     */
    public function hasAvailableCapacity(ClassModel $class)
    {
        return $class->current_enrollment < $class->capacity;
    }

    /**
     * Get available seats for a class.
     */
    public function getAvailableSeats(ClassModel $class)
    {
        return max(0, $class->capacity - $class->current_enrollment);
    }

    /**
     * Increment class enrollment.
     */
    public function incrementEnrollment(ClassModel $class)
    {
        return DB::transaction(function () use ($class) {
            $class->increment('current_enrollment');
            
            // Update status if full
            if ($class->current_enrollment >= $class->capacity) {
                $class->update(['status' => 'full']);
            }

            return $class->fresh();
        });
    }

    /**
     * Decrement class enrollment.
     */
    public function decrementEnrollment(ClassModel $class)
    {
        return DB::transaction(function () use ($class) {
            $class->decrement('current_enrollment');
            
            // Update status if was full and now has space
            if ($class->status === 'full' && $class->current_enrollment < $class->capacity) {
                $class->update(['status' => 'active']);
            }

            return $class->fresh();
        });
    }

    /**
     * Get class statistics.
     */
    public function getClassStatistics(ClassModel $class)
    {
        return [
            'total_students' => $class->enrollments()->where('status', 'active')->count(),
            'capacity_utilization' => $class->capacity > 0 ? round(($class->current_enrollment / $class->capacity) * 100, 2) : 0,
            'available_seats' => $this->getAvailableSeats($class),
            'total_sessions' => $class->sessions()->count(),
            'completed_sessions' => $class->sessions()->completed()->count(),
            'upcoming_sessions' => $class->sessions()->upcoming()->count(),
            'attendance_rate' => $this->calculateAttendanceRate($class->id),
            'total_materials' => $class->materials()->count(),
            'active_schedules' => $class->schedules()->active()->count(),
        ];
    }

    /**
     * Get teacher's weekly schedule.
     */
    public function getTeacherWeeklySchedule($teacherId)
    {
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        
        $schedules = ClassSchedule::whereHas('class', function($q) use ($teacherId) {
                $q->where('teacher_id', $teacherId)->where('status', 'active');
            })
            ->where('is_active', true)
            ->with('class.subject')
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        $weeklySchedule = [];
        foreach ($days as $day) {
            $weeklySchedule[$day] = $schedules->where('day_of_week', $day)->values();
        }

        return $weeklySchedule;
    }

    /**
     * Get daily class schedule.
     */
    public function getDailySchedule($date = null)
    {
        if (!$date) {
            $date = now();
        }

        $dayOfWeek = strtolower($date->format('l'));

        return ClassSchedule::where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->whereHas('class', fn($q) => $q->active())
            ->with(['class.subject', 'class.teacher.user'])
            ->orderBy('start_time')
            ->get();
    }
}
