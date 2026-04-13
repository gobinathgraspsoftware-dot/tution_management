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
     */
    public function getEnrollmentStats(?Student $student = null): array
    {
        if ($student) {
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
            ->whereIn('status', ['active', 'trial', 'suspended'])
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

    /*
    |==========================================================================
    | NEW: Check if student is already fully enrolled in a package
    |==========================================================================
    | Returns details about how many of the package's classes the student
    | already has. Used by controllers to block pointless re-enrollment.
    |==========================================================================
    */
    public function isStudentFullyEnrolledInPackage(Student $student, Package $package): array
    {
        $package->loadMissing('subjects');

        // Get all active class IDs across the package's subjects
        $packageClassIds = ClassModel::whereIn('subject_id', $package->subjects->pluck('id'))
            ->where('status', 'active')
            ->pluck('id')
            ->toArray();

        if (empty($packageClassIds)) {
            return [
                'fully_enrolled' => false,
                'enrolled_count' => 0,
                'total_classes'  => 0,
                'message'        => 'This package has no active classes.',
            ];
        }

        // Student's active enrollments in those classes
        $enrolledClassIds = $student->enrollments()
            ->whereIn('class_id', $packageClassIds)
            ->whereIn('status', ['active', 'trial', 'suspended'])
            ->pluck('class_id')
            ->unique()
            ->toArray();

        $enrolledCount = count($enrolledClassIds);
        $totalClasses  = count($packageClassIds);
        $fullyEnrolled = $enrolledCount >= $totalClasses;

        return [
            'fully_enrolled' => $fullyEnrolled,
            'enrolled_count' => $enrolledCount,
            'total_classes'  => $totalClasses,
            'message'        => $fullyEnrolled
                ? "Student is already enrolled in all {$totalClasses} class(es) of this package."
                : null,
        ];
    }

    /**
     * Create a new enrollment.
     *
     * CRITICAL: This is the single creation point for ALL enrollments.
     * Contains:
     *  1) enrollment_date auto-fill  (fixes NOT NULL error)
     *  2) lockForUpdate duplicate guard (prevents race-condition duplicates)
     */
    public function createEnrollment(array $data): Enrollment
    {
        return DB::transaction(function () use ($data) {
            // 1) Ensure enrollment_date is always present
            if (empty($data['enrollment_date'])) {
                $data['enrollment_date'] = $data['start_date'] ?? now()->toDateString();
            }

            // 2) DUPLICATE GUARD with row-level lock
            //    lockForUpdate() serialises concurrent requests so the second
            //    one waits for the first to commit, then sees the new row.
            if (!empty($data['student_id']) && !empty($data['class_id'])) {
                $existingEnrollment = Enrollment::where('student_id', $data['student_id'])
                    ->where('class_id', $data['class_id'])
                    ->whereIn('status', ['active', 'trial', 'suspended'])
                    ->lockForUpdate()
                    ->first();

                if ($existingEnrollment) {
                    $className = ClassModel::find($data['class_id'])->name ?? 'Unknown';
                    throw new \Exception(
                        "Duplicate enrollment prevented: student is already enrolled in class '{$className}' (Status: {$existingEnrollment->status})."
                    );
                }
            }

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
     * Enroll student in a package (all classes).
     * Uses createEnrollment() which has the duplicate guard built in.
     */
    public function enrollInPackage(Student $student, Package $package, array $data): array
    {
        return DB::transaction(function () use ($student, $package, $data) {
            $enrollments = [];
            $skipped     = [];

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
                        'student_id'      => $student->id,
                        'package_id'      => $package->id,
                        'class_id'        => $class->id,
                        'enrollment_date' => $data['enrollment_date'] ?? $data['start_date'] ?? now()->toDateString(),
                        'monthly_fee'     => $class->price ?? 0,
                        'status'          => 'active',
                    ]);

                    try {
                        $enrollments[] = $this->createEnrollment($enrollmentData);
                    } catch (\Exception $e) {
                        $skipped[] = [
                            'class_id'   => $class->id,
                            'class_name' => $class->name,
                            'reasons'    => [$e->getMessage()],
                        ];
                    }
                } else {
                    $skipped[] = [
                        'class_id'   => $class->id,
                        'class_name' => $class->name,
                        'reasons'    => $canEnroll['errors'],
                    ];
                }
            }

            if (empty($enrollments)) {
                throw new \Exception('Student is already enrolled in all classes of this package. No new enrollments were created.');
            }

            return $enrollments;
        });
    }

    /**
     * Enroll student in a package with specific class selections per subject.
     * Uses createEnrollment() which has the duplicate guard built in.
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

                try {
                    $enrollment = $this->createEnrollment($enrollmentData);
                    $created[] = $enrollment;

                    if ($package->duration_months && !isset($data['end_date'])) {
                        $enrollment->update([
                            'end_date' => Carbon::parse($enrollmentData['start_date'])->addMonths($package->duration_months),
                        ]);
                    }
                } catch (\Exception $e) {
                    $skipped[] = [
                        'class_id'   => $class->id,
                        'class_name' => $class->name,
                        'reasons'    => [$e->getMessage()],
                    ];
                }
            }

            if (empty($created) && empty($skipped)) {
                throw new \Exception('No classes were selected for enrollment.');
            }

            if (empty($created) && !empty($skipped)) {
                throw new \Exception('Student is already enrolled in all selected classes. No new enrollments were created.');
            }

            return [
                'created' => $created,
                'skipped' => $skipped,
            ];
        });
    }

    /**
     * Update an existing enrollment.
     */
    public function updateEnrollment(Enrollment $enrollment, array $data): Enrollment
    {
        return DB::transaction(function () use ($enrollment, $data) {
            $oldClassId = $enrollment->class_id;
            $newClassId = $data['class_id'] ?? $oldClassId;

            if ($newClassId != $oldClassId && $enrollment->isActive()) {
                // Check for duplicate before changing class
                $duplicateCheck = Enrollment::where('student_id', $enrollment->student_id)
                    ->where('class_id', $newClassId)
                    ->where('id', '!=', $enrollment->id)
                    ->whereIn('status', ['active', 'trial', 'suspended'])
                    ->first();

                if ($duplicateCheck) {
                    throw new \Exception("Student is already enrolled in this class. Cannot move to a duplicate enrollment.");
                }

                $oldClass = ClassModel::find($oldClassId);
                if ($oldClass) {
                    $oldClass->decrement('current_enrollment');
                    if ($oldClass->status === 'full' && $oldClass->current_enrollment < $oldClass->capacity) {
                        $oldClass->update(['status' => 'active']);
                    }
                }

                $newClass = ClassModel::find($newClassId);
                if ($newClass && $newClass->current_enrollment >= $newClass->capacity) {
                    throw new \Exception("Class '{$newClass->name}' is currently full.");
                }

                if ($newClass) {
                    $newClass->increment('current_enrollment');
                    if ($newClass->current_enrollment >= $newClass->capacity) {
                        $newClass->update(['status' => 'full']);
                    }
                }
            }

            $updateData = array_intersect_key($data, array_flip($enrollment->getFillable()));
            $enrollment->update($updateData);

            return $enrollment->fresh();
        });
    }

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

    public function cancelEnrollment(Enrollment $enrollment, ?string $reason = null): Enrollment
    {
        return $this->updateStatus($enrollment, 'cancelled', $reason);
    }

    public function suspendEnrollment(Enrollment $enrollment, ?string $reason = null): Enrollment
    {
        return $this->updateStatus($enrollment, 'suspended', $reason);
    }

    public function resumeEnrollment(Enrollment $enrollment): Enrollment
    {
        return $this->updateStatus($enrollment, 'active', null);
    }

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

    public function getEnrollmentDetails(Enrollment $enrollment): Enrollment
    {
        return $enrollment->load([
            'student.user', 'student.parent.user',
            'class.subject', 'class.teacher.user', 'class.schedules',
            'package.subjects',
            'invoices' => function ($query) { $query->latest()->limit(10); },
        ]);
    }

    public function calculateNextPaymentDate(Enrollment $enrollment): Carbon
    {
        $paymentCycleDay = $enrollment->payment_cycle_day;
        $now = now();
        $nextPayment = Carbon::create($now->year, $now->month, min($paymentCycleDay, $now->daysInMonth));

        if ($nextPayment->isPast()) {
            $nextPayment->addMonth();
            $nextPayment->day = min($paymentCycleDay, $nextPayment->daysInMonth);
        }

        return $nextPayment;
    }

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

    public function getOverdueEnrollments(Student $student)
    {
        return $student->enrollments()
            ->where('status', 'active')
            ->whereHas('invoices', function ($query) {
                $query->where('status', 'pending')->where('due_date', '<', now());
            })
            ->with(['class', 'invoices' => function ($query) {
                $query->where('status', 'pending')->where('due_date', '<', now());
            }])
            ->get();
    }
}
