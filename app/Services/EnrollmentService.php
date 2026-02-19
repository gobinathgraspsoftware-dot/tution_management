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
     * Get enrollment statistics.
     * When called without a student, returns global stats (for admin/staff index page).
     * When called with a student, returns per-student stats.
     *
     * Global keys match blade: $stats['total'], $stats['active'], $stats['expiring_soon'], $stats['expired']
     */
    public function getEnrollmentStats(?Student $student = null): array
    {
        if ($student) {
            // Per-student stats
            $query = $student->enrollments();

            return [
                'total_enrollments'     => (clone $query)->count(),
                'active_enrollments'    => (clone $query)->where('status', 'active')->count(),
                'pending_enrollments'   => (clone $query)->where('status', 'pending')->count(),
                'suspended_enrollments' => (clone $query)->where('status', 'suspended')->count(),
                'cancelled_enrollments' => (clone $query)->where('status', 'cancelled')->count(),
                'completed_enrollments' => (clone $query)->where('status', 'completed')->count(),
                'total_monthly_fee'     => (clone $query)->where('status', 'active')->sum('monthly_fee'),
            ];
        }

        // Global stats — keys MUST match blade: $stats['total'], $stats['active'], etc.
        return [
            'total'         => Enrollment::count(),
            'active'        => Enrollment::where('status', 'active')->count(),
            'expiring_soon' => Enrollment::where('status', 'active')
                                    ->whereNotNull('end_date')
                                    ->whereBetween('end_date', [now(), now()->addDays(7)])
                                    ->count(),
            'expired'       => Enrollment::where('status', 'expired')->count(),
        ];
    }

    /**
     * Get available classes for student enrollment.
     */
    public function getAvailableClasses(Student $student)
    {
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

        if ($class->status !== 'active') {
            $errors[] = 'This class is not currently accepting enrollments.';
            $canEnroll = false;
        }

        if ($class->current_enrollment >= $class->capacity) {
            $errors[] = 'This class is currently full.';
            $canEnroll = false;
        }

        $existingEnrollment = $student->enrollments()
            ->where('class_id', $class->id)
            ->whereIn('status', ['active', 'pending', 'trial', 'suspended'])
            ->first();

        if ($existingEnrollment) {
            $errors[] = 'Student is already enrolled in this class.';
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
            $enrollment = Enrollment::create($data);

            // Increment class enrollment count
            $class = ClassModel::find($data['class_id']);
            if ($class) {
                $class->increment('current_enrollment');

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

            foreach ($classes as $class) {
                $canEnroll = $this->canEnroll($student, $class);

                if ($canEnroll['can_enroll']) {
                    $enrollmentData = array_merge($data, [
                        'student_id'  => $student->id,
                        'package_id'  => $package->id,
                        'class_id'    => $class->id,
                        'monthly_fee' => $class->price ?? 0,
                        'status'      => 'active',
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
     * Enroll student in a package with specific class selections per subject.
     * Called from EnrollmentController@store when enrollment_type = 'package'.
     *
     * @param Student $student
     * @param Package $package
     * @param array   $subjectClasses  e.g. [subject_id => class_id, ...]
     * @param array   $data            Validated form data (start_date, end_date, etc.)
     * @return array  ['created' => [...], 'skipped' => [...]]
     */
    public function enrollInPackageWithClasses(Student $student, Package $package, array $subjectClasses, array $data): array
    {
        return DB::transaction(function () use ($student, $package, $subjectClasses, $data) {
            $created = [];
            $skipped = [];

            foreach ($subjectClasses as $subjectId => $classId) {
                if (empty($classId)) {
                    continue;
                }

                $class = ClassModel::find($classId);
                if (!$class) {
                    continue;
                }

                $canEnroll = $this->canEnroll($student, $class);

                if (!$canEnroll['can_enroll']) {
                    $skipped[] = [
                        'class_id'   => $class->id,
                        'class_name' => $class->name,
                        'reasons'    => $canEnroll['errors'],
                    ];
                    continue;
                }

                $enrollmentData = [
                    'student_id'        => $student->id,
                    'package_id'        => $package->id,
                    'class_id'          => $class->id,
                    'enrollment_date'   => $data['enrollment_date'] ?? $data['start_date'] ?? now()->toDateString(),
                    'start_date'        => $data['start_date'] ?? now()->toDateString(),
                    'end_date'          => $data['end_date'] ?? null,
                    'payment_cycle_day' => $data['payment_cycle_day'] ?? 1,
                    'monthly_fee'       => $class->price ?? 0,
                    'status'            => $data['status'] ?? 'active',
                ];

                $enrollment = $this->createEnrollment($enrollmentData);
                $created[] = $enrollment;
            }

            if (empty($created) && empty($skipped)) {
                throw new \Exception('No classes were selected for enrollment.');
            }

            if (empty($created) && !empty($skipped)) {
                throw new \Exception('Student is already enrolled in all selected classes.');
            }

            return [
                'created' => $created,
                'skipped' => $skipped,
            ];
        });
    }

    /**
     * Update an existing enrollment.
     * Called from EnrollmentController@update.
     */
    public function updateEnrollment(Enrollment $enrollment, array $data): Enrollment
    {
        return DB::transaction(function () use ($enrollment, $data) {
            $oldClassId = $enrollment->class_id;
            $newClassId = $data['class_id'] ?? $oldClassId;

            // If class is being changed and enrollment is active
            if ($newClassId != $oldClassId && $enrollment->isActive()) {
                // Decrement old class count
                $oldClass = ClassModel::find($oldClassId);
                if ($oldClass) {
                    $oldClass->decrement('current_enrollment');
                    if ($oldClass->status === 'full' && $oldClass->current_enrollment < $oldClass->capacity) {
                        $oldClass->update(['status' => 'active']);
                    }
                }

                // Check new class capacity
                $newClass = ClassModel::find($newClassId);
                if ($newClass && $newClass->current_enrollment >= $newClass->capacity) {
                    throw new \Exception("Class '{$newClass->name}' is currently full.");
                }

                // Increment new class count
                if ($newClass) {
                    $newClass->increment('current_enrollment');
                    if ($newClass->current_enrollment >= $newClass->capacity) {
                        $newClass->update(['status' => 'full']);
                    }
                }
            }

            // Filter to only fillable fields
            $updateData = array_intersect_key($data, array_flip($enrollment->getFillable()));
            $enrollment->update($updateData);

            return $enrollment->fresh();
        });
    }

    /**
     * Update enrollment status.
     */
    public function updateStatus(Enrollment $enrollment, string $status, ?string $reason = null): Enrollment
    {
        return DB::transaction(function () use ($enrollment, $status, $reason) {
            $oldStatus = $enrollment->status;

            $updateData = ['status' => $status];

            if ($status === 'cancelled' && $reason) {
                $updateData['cancellation_reason'] = $reason;
                $updateData['cancelled_at'] = now();
            }

            $enrollment->update($updateData);

            // Update class enrollment count
            if ($oldStatus === 'active' && $status !== 'active') {
                $class = $enrollment->class;
                if ($class) {
                    $class->decrement('current_enrollment');
                    if ($class->status === 'full' && $class->current_enrollment < $class->capacity) {
                        $class->update(['status' => 'active']);
                    }
                }
            } elseif ($oldStatus !== 'active' && $status === 'active') {
                $class = $enrollment->class;
                if ($class) {
                    $class->increment('current_enrollment');
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
        return $this->updateStatus($enrollment, 'active', null);
    }

    /**
     * Renew an enrollment by extending the end date.
     * Called from EnrollmentController@renew.
     */
    public function renewEnrollment(Enrollment $enrollment, ?int $months = null): Enrollment
    {
        return DB::transaction(function () use ($enrollment, $months) {
            if (!$months) {
                $months = $enrollment->package && $enrollment->package->duration_months
                    ? $enrollment->package->duration_months
                    : 1;
            }

            $baseDate = $enrollment->end_date && $enrollment->end_date->isFuture()
                ? $enrollment->end_date
                : now();

            $newEndDate = $baseDate->copy()->addMonths($months);

            $oldStatus = $enrollment->status;

            $enrollment->update([
                'end_date' => $newEndDate,
                'status'   => 'active',
            ]);

            // If enrollment was not active, re-increment class count
            if ($oldStatus !== 'active') {
                $class = $enrollment->class;
                if ($class) {
                    $class->increment('current_enrollment');
                    if ($class->current_enrollment >= $class->capacity) {
                        $class->update(['status' => 'full']);
                    }
                }
            }

            return $enrollment->fresh();
        });
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
        ]);
    }

    /**
     * Calculate next payment due date.
     */
    public function calculateNextPaymentDate(Enrollment $enrollment): Carbon
    {
        $paymentCycleDay = $enrollment->payment_cycle_day;
        $now = now();

        $nextPayment = Carbon::create(
            $now->year,
            $now->month,
            min($paymentCycleDay, $now->daysInMonth)
        );

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
