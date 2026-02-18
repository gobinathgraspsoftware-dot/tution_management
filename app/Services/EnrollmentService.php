<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Models\Student;
use App\Models\ClassModel;
use App\Models\Package;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EnrollmentService
{
    /**
     * Get enrollment statistics for a student.
     */
    public function getEnrollmentStats(Student $student): array
    {
        $enrollments = $student->enrollments();

        return [
            'total_enrollments' => $enrollments->count(),
            'active_enrollments' => $enrollments->where('status', 'active')->count(),
            'pending_enrollments' => $enrollments->where('status', 'pending')->count(),
            'suspended_enrollments' => $enrollments->where('status', 'suspended')->count(),
            'cancelled_enrollments' => $enrollments->where('status', 'cancelled')->count(),
            'completed_enrollments' => $enrollments->where('status', 'completed')->count(),
            'total_monthly_fee' => $enrollments->where('status', 'active')->sum('monthly_fee'),
        ];
    }

    /**
     * Get available classes for student enrollment.
     */
    public function getAvailableClasses(Student $student)
    {
        // Get classes that:
        // 1. Are active
        // 2. Have available capacity
        // 3. Student is not already enrolled in

        $enrolledClassIds = $student->enrollments()
            ->whereIn('status', ['active', 'pending'])
            ->pluck('class_id')
            ->toArray();

        return ClassModel::active()
            ->whereNotIn('id', $enrolledClassIds)
            ->whereRaw('current_enrollment < capacity')
            ->with(['subject', 'teacher.user', 'schedules'])
            ->orderBy('name')
            ->get();
    }

    /**
     * Get available packages for student enrollment.
     */
    public function getAvailablePackages(Student $student)
    {
        // Get all active packages - removed discountRule relationship
        return Package::active()
            ->with(['subjects'])
            ->orderBy('name')
            ->get();
    }

    /**
     * Check if student can enroll in a class.
     */
    public function canEnroll(Student $student, ClassModel $class): array
    {
        $errors = [];
        $canEnroll = true;

        // Check if class is active
        if ($class->status !== 'active') {
            $errors[] = 'This class is not currently accepting enrollments.';
            $canEnroll = false;
        }

        // Check class capacity
        if ($class->current_enrollment >= $class->capacity) {
            $errors[] = 'This class is currently full.';
            $canEnroll = false;
        }

        // Check if already enrolled
        $existingEnrollment = $student->enrollments()
            ->where('class_id', $class->id)
            ->whereIn('status', ['active', 'pending'])
            ->first();

        if ($existingEnrollment) {
            $errors[] = 'You are already enrolled in this class.';
            $canEnroll = false;
        }

        return [
            'can_enroll' => $canEnroll,
            'errors' => $errors,
        ];
    }

    /**
     * Create a new enrollment.
     */
    public function createEnrollment(array $data): Enrollment
    {
        return DB::transaction(function () use ($data) {
            // Create enrollment
            $enrollment = Enrollment::create($data);

            // Increment class enrollment count
            $class = ClassModel::find($data['class_id']);
            if ($class) {
                $class->increment('current_enrollment');

                // Update class status if full
                if ($class->current_enrollment >= $class->capacity) {
                    $class->update(['status' => 'full']);
                }
            }

            return $enrollment;
        });
    }

    /**
     * Enroll student in a package (all classes in package).
     */
    public function enrollInPackage(Student $student, Package $package, array $data): array
    {
        return DB::transaction(function () use ($student, $package, $data) {
            $enrollments = [];

            // Get all classes from package subjects
            $classes = $package->subjects()
                ->with(['classes' => function ($query) {
                    $query->active()->whereRaw('current_enrollment < capacity');
                }])
                ->get()
                ->flatMap(function ($subject) {
                    return $subject->classes;
                });

            if ($classes->isEmpty()) {
                throw new \Exception('No available classes found in this package.');
            }

            // Create enrollment for each class
            foreach ($classes as $class) {
                // Check if can enroll
                $canEnroll = $this->canEnroll($student, $class);

                if ($canEnroll['can_enroll']) {
                    $enrollmentData = array_merge($data, [
                        'student_id' => $student->id,
                        'package_id' => $package->id,
                        'class_id' => $class->id,
                        'monthly_fee' => $class->monthly_fee,
                        'status' => 'active',
                    ]);

                    $enrollments[] = $this->createEnrollment($enrollmentData);
                }
            }

            if (empty($enrollments)) {
                throw new \Exception('Could not enroll in any classes from this package.');
            }

            return $enrollments;
        });
    }

    /**
     * Update enrollment status.
     */
    public function updateStatus(Enrollment $enrollment, string $status, ?string $reason = null): Enrollment
    {
        return DB::transaction(function () use ($enrollment, $status, $reason) {
            $oldStatus = $enrollment->status;

            $enrollment->update([
                'status' => $status,
                'status_changed_at' => now(),
                'status_change_reason' => $reason,
            ]);

            // Update class enrollment count
            if ($oldStatus === 'active' && $status !== 'active') {
                // Decrement count when deactivating
                $class = $enrollment->class;
                if ($class) {
                    $class->decrement('current_enrollment');

                    // Update class status if was full
                    if ($class->status === 'full' && $class->current_enrollment < $class->capacity) {
                        $class->update(['status' => 'active']);
                    }
                }
            } elseif ($oldStatus !== 'active' && $status === 'active') {
                // Increment count when activating
                $class = $enrollment->class;
                if ($class) {
                    $class->increment('current_enrollment');

                    // Update class status if now full
                    if ($class->current_enrollment >= $class->capacity) {
                        $class->update(['status' => 'full']);
                    }
                }
            }

            return $enrollment->fresh();
        });
    }

    /**
     * Cancel an enrollment.
     */
    public function cancelEnrollment(Enrollment $enrollment, ?string $reason = null): Enrollment
    {
        return $this->updateStatus($enrollment, 'cancelled', $reason);
    }

    /**
     * Suspend an enrollment.
     */
    public function suspendEnrollment(Enrollment $enrollment, ?string $reason = null): Enrollment
    {
        return $this->updateStatus($enrollment, 'suspended', $reason);
    }

    /**
     * Resume a suspended enrollment.
     */
    public function resumeEnrollment(Enrollment $enrollment): Enrollment
    {
        return $this->updateStatus($enrollment, 'active', 'Resumed from suspension');
    }

    /**
     * Get enrollment details with related data.
     */
    public function getEnrollmentDetails(Enrollment $enrollment): Enrollment
    {
        return $enrollment->load([
            'student.user',
            'student.parent.user',
            'class.subject',
            'class.teacher.user',
            'class.schedules',
            'package.subjects',
            'invoices' => function ($query) {
                $query->latest()->limit(10);
            },
            'payments' => function ($query) {
                $query->latest()->limit(10);
            },
        ]);
    }

    /**
     * Calculate next payment due date.
     */
    public function calculateNextPaymentDate(Enrollment $enrollment): Carbon
    {
        $paymentCycleDay = $enrollment->payment_cycle_day;
        $now = now();

        // Get the payment day for current month
        $nextPayment = Carbon::create(
            $now->year,
            $now->month,
            min($paymentCycleDay, $now->daysInMonth)
        );

        // If payment day has passed, move to next month
        if ($nextPayment->isPast()) {
            $nextPayment->addMonth();
            $nextPayment->day = min($paymentCycleDay, $nextPayment->daysInMonth);
        }

        return $nextPayment;
    }

    /**
     * Get student's active enrollments with upcoming payment dates.
     */
    public function getActiveEnrollmentsWithPayments(Student $student)
    {
        return $student->enrollments()
            ->where('status', 'active')
            ->with(['class.subject', 'package'])
            ->get()
            ->map(function ($enrollment) {
                $enrollment->next_payment_date = $this->calculateNextPaymentDate($enrollment);
                return $enrollment;
            });
    }

    /**
     * Check for overdue enrollments.
     */
    public function getOverdueEnrollments(Student $student)
    {
        return $student->enrollments()
            ->where('status', 'active')
            ->whereHas('invoices', function ($query) {
                $query->where('status', 'pending')
                    ->where('due_date', '<', now());
            })
            ->with(['class', 'invoices' => function ($query) {
                $query->where('status', 'pending')
                    ->where('due_date', '<', now());
            }])
            ->get();
    }
}
