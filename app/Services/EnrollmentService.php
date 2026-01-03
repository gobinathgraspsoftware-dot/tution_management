<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Models\Student;
use App\Models\Package;
use App\Models\ClassModel;
use App\Models\Invoice;
use App\Models\EnrollmentFeeHistory;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EnrollmentService
{
    /**
     * Create a single class enrollment
     */
    public function createEnrollment(array $data): Enrollment
    {
        return DB::transaction(function () use ($data) {
            $class = ClassModel::findOrFail($data['class_id']);
            
            // Check capacity
            if ($class->current_enrollment >= $class->capacity) {
                throw new \Exception("Class '{$class->name}' is full. Cannot enroll more students.");
            }

            // Calculate end date based on package duration if package_id is provided
            $endDate = null;
            if (!empty($data['package_id'])) {
                $package = Package::find($data['package_id']);
                if ($package && $package->duration_months) {
                    $endDate = Carbon::parse($data['start_date'])->addMonths($package->duration_months);
                }
            }

            // Use class price if monthly_fee not provided
            $monthlyFee = $data['monthly_fee'] ?? $class->price;

            $enrollment = Enrollment::create([
                'student_id' => $data['student_id'],
                'package_id' => $data['package_id'] ?? null,
                'class_id' => $data['class_id'],
                'enrollment_date' => now(),
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'] ?? $endDate,
                'payment_cycle_day' => $data['payment_cycle_day'] ?? 1,
                'monthly_fee' => $monthlyFee,
                'status' => $data['status'] ?? 'active',
            ]);

            // Update class enrollment count
            $class->increment('current_enrollment');

            // Check if class is now full
            if ($class->current_enrollment >= $class->capacity) {
                $class->update(['status' => 'full']);
            }

            return $enrollment;
        });
    }

    /**
     * Enroll student in a package with specific class selections
     */
    public function enrollInPackageWithClasses(
        Student $student,
        Package $package,
        array $subjectClasses,
        array $data
    ): array {
        return DB::transaction(function () use ($student, $package, $subjectClasses, $data) {
            $created = [];
            $skipped = [];

            // Calculate dates
            $startDate = Carbon::parse($data['start_date']);
            $endDate = $startDate->copy()->addMonths($package->duration_months);

            // Filter out empty selections
            $validSelections = array_filter($subjectClasses, function ($classId) {
                return !empty($classId);
            });

            if (empty($validSelections)) {
                throw new \Exception("Please select at least one class for the package enrollment.");
            }

            // Calculate fee per class
            $feePerClass = $package->price / count($validSelections);

            // Create enrollment for each selected class
            foreach ($validSelections as $subjectId => $classId) {
                $class = ClassModel::where('id', $classId)
                    ->where('subject_id', $subjectId)
                    ->where('status', 'active')
                    ->first();

                if (!$class) {
                    // Invalid class selection - skip with warning
                    $skipped[] = [
                        'subject_id' => $subjectId,
                        'class_id' => $classId,
                        'reason' => 'Invalid class selection or class not active',
                    ];
                    continue;
                }

                // Check for existing enrollment - SKIP instead of error
                $existingEnrollment = Enrollment::where('student_id', $student->id)
                    ->where('class_id', $classId)
                    ->whereIn('status', ['active', 'trial', 'suspended'])
                    ->first();

                if ($existingEnrollment) {
                    $skipped[] = [
                        'subject_id' => $subjectId,
                        'class_id' => $classId,
                        'class_name' => $class->name,
                        'reason' => 'Student is already enrolled in this class',
                    ];
                    continue; // Skip this class, don't throw error
                }

                // Check capacity
                if ($class->current_enrollment >= $class->capacity) {
                    $skipped[] = [
                        'subject_id' => $subjectId,
                        'class_id' => $classId,
                        'class_name' => $class->name,
                        'reason' => 'Class is full',
                    ];
                    continue; // Skip full classes
                }

                // Create enrollment
                $enrollment = Enrollment::create([
                    'student_id' => $student->id,
                    'package_id' => $package->id,
                    'class_id' => $class->id,
                    'enrollment_date' => now(),
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'payment_cycle_day' => $data['payment_cycle_day'] ?? 1,
                    'monthly_fee' => $feePerClass,
                    'status' => $data['status'] ?? 'active',
                ]);

                // Update class enrollment count
                $class->increment('current_enrollment');

                // Check if class is now full
                if ($class->current_enrollment >= $class->capacity) {
                    $class->update(['status' => 'full']);
                }

                $created[] = $enrollment;
            }

            // If no enrollments were created, throw error
            if (empty($created)) {
                $reasons = array_map(fn($s) => $s['class_name'] . ': ' . $s['reason'], $skipped);
                throw new \Exception("No enrollments created. All selected classes were skipped: " . implode(', ', $reasons));
            }

            return [
                'created' => $created,
                'skipped' => $skipped,
            ];
        });
    }

    /**
     * Update an existing enrollment
     */
    public function updateEnrollment(Enrollment $enrollment, array $data): Enrollment
    {
        return DB::transaction(function () use ($enrollment, $data) {
            $oldFee = $enrollment->monthly_fee;
            $newFee = $data['monthly_fee'] ?? $oldFee;

            // If fee changed, record in history
            if (floatval($oldFee) != floatval($newFee)) {
                EnrollmentFeeHistory::create([
                    'enrollment_id' => $enrollment->id,
                    'package_id' => $enrollment->package_id,
                    'old_fee' => $oldFee,
                    'new_fee' => $newFee,
                    'monthly_fee' => $newFee, // For backward compatibility
                    'effective_from' => now(),
                    'change_date' => now(),
                    'reason' => $data['fee_change_reason'] ?? 'Fee updated',
                    'created_by' => auth()->id(),
                    'changed_by' => auth()->id(),
                ]);
            }

            $enrollment->update([
                'start_date' => $data['start_date'] ?? $enrollment->start_date,
                'end_date' => $data['end_date'] ?? $enrollment->end_date,
                'payment_cycle_day' => $data['payment_cycle_day'] ?? $enrollment->payment_cycle_day,
                'monthly_fee' => $newFee,
                'status' => $data['status'] ?? $enrollment->status,
            ]);

            return $enrollment->fresh();
        });
    }

    /**
     * Cancel an enrollment
     */
    public function cancelEnrollment(Enrollment $enrollment, ?string $reason = null): Enrollment
    {
        return DB::transaction(function () use ($enrollment, $reason) {
            $enrollment->update([
                'status' => 'cancelled',
                'cancellation_reason' => $reason,
                'cancelled_at' => now(),
            ]);

            // Decrease class enrollment count if class exists
            if ($enrollment->class) {
                $enrollment->class->decrement('current_enrollment');
                
                // If class was full, make it active again
                if ($enrollment->class->status === 'full') {
                    $enrollment->class->update(['status' => 'active']);
                }
            }

            // Cancel pending invoices
            Invoice::where('enrollment_id', $enrollment->id)
                ->whereIn('status', ['pending', 'draft'])
                ->update(['status' => 'cancelled']);

            return $enrollment;
        });
    }

    /**
     * Suspend an enrollment
     */
    public function suspendEnrollment(Enrollment $enrollment, ?string $reason = null): Enrollment
    {
        return DB::transaction(function () use ($enrollment, $reason) {
            $enrollment->update([
                'status' => 'suspended',
                'cancellation_reason' => $reason, // Using same field for suspension reason
            ]);

            return $enrollment;
        });
    }

    /**
     * Resume a suspended enrollment
     */
    public function resumeEnrollment(Enrollment $enrollment): Enrollment
    {
        return DB::transaction(function () use ($enrollment) {
            // Check if end date has passed
            if ($enrollment->end_date && $enrollment->end_date->isPast()) {
                throw new \Exception("Cannot resume - enrollment has expired. Please renew instead.");
            }

            $enrollment->update([
                'status' => 'active',
            ]);

            return $enrollment;
        });
    }

    /**
     * Renew an enrollment
     */
    public function renewEnrollment(Enrollment $enrollment, ?int $months = null): Enrollment
    {
        return DB::transaction(function () use ($enrollment, $months) {
            // Determine months to extend
            $extensionMonths = $months;
            if (!$extensionMonths && $enrollment->package) {
                $extensionMonths = $enrollment->package->duration_months;
            }
            $extensionMonths = $extensionMonths ?? 1;

            // Max 15 months as per new requirement
            $extensionMonths = min($extensionMonths, 15);

            // Calculate new end date
            $baseDate = $enrollment->end_date ?? now();
            $newEndDate = Carbon::parse($baseDate)->addMonths($extensionMonths);

            $enrollment->update([
                'end_date' => $newEndDate,
                'status' => 'active',
            ]);

            return $enrollment;
        });
    }

    /**
     * Get enrollment statistics
     */
    public function getEnrollmentStats(): array
    {
        $total = Enrollment::count();
        $active = Enrollment::where('status', 'active')->count();
        $suspended = Enrollment::where('status', 'suspended')->count();
        $expired = Enrollment::where('status', 'expired')->count();
        $cancelled = Enrollment::where('status', 'cancelled')->count();
        $trial = Enrollment::where('status', 'trial')->count();

        // Expiring within 30 days
        $expiringSoon = Enrollment::where('status', 'active')
            ->whereNotNull('end_date')
            ->whereBetween('end_date', [now(), now()->addDays(30)])
            ->count();

        return [
            'total' => $total,
            'active' => $active,
            'suspended' => $suspended,
            'expired' => $expired,
            'cancelled' => $cancelled,
            'trial' => $trial,
            'expiring_soon' => $expiringSoon,
        ];
    }

    /**
     * Get student's active enrollments
     */
    public function getStudentActiveEnrollments(Student $student): \Illuminate\Database\Eloquent\Collection
    {
        return Enrollment::where('student_id', $student->id)
            ->whereIn('status', ['active', 'trial'])
            ->with(['package', 'class.subject', 'class.teacher.user'])
            ->get();
    }

    /**
     * Check if student can be enrolled in a class
     */
    public function canEnrollInClass(Student $student, ClassModel $class): array
    {
        $result = [
            'can_enroll' => true,
            'reason' => null,
        ];

        // Check existing enrollment
        $existingEnrollment = Enrollment::where('student_id', $student->id)
            ->where('class_id', $class->id)
            ->whereIn('status', ['active', 'trial', 'suspended'])
            ->first();

        if ($existingEnrollment) {
            $result['can_enroll'] = false;
            $result['reason'] = 'Student is already enrolled in this class';
            return $result;
        }

        // Check class capacity
        if ($class->current_enrollment >= $class->capacity) {
            $result['can_enroll'] = false;
            $result['reason'] = 'Class is full';
            return $result;
        }

        // Check class status
        if ($class->status !== 'active') {
            $result['can_enroll'] = false;
            $result['reason'] = 'Class is not active';
            return $result;
        }

        return $result;
    }

    /**
     * Get enrollments expiring within specified days
     */
    public function getExpiringEnrollments(int $days = 30): \Illuminate\Database\Eloquent\Collection
    {
        return Enrollment::where('status', 'active')
            ->whereNotNull('end_date')
            ->whereBetween('end_date', [now(), now()->addDays($days)])
            ->with(['student.user', 'package', 'class.subject'])
            ->orderBy('end_date')
            ->get();
    }

    /**
     * Mark expired enrollments
     */
    public function markExpiredEnrollments(): int
    {
        $count = Enrollment::where('status', 'active')
            ->whereNotNull('end_date')
            ->where('end_date', '<', now()->startOfDay())
            ->update(['status' => 'expired']);

        return $count;
    }

    /**
     * Get enrollment by ID with all relations
     */
    public function getEnrollmentWithRelations(int $enrollmentId): ?Enrollment
    {
        return Enrollment::with([
            'student.user',
            'student.parent.user',
            'package.subjects',
            'class.subject',
            'class.teacher.user',
            'class.schedules',
            'invoices',
            'feeHistory.changedBy',
        ])->find($enrollmentId);
    }
}
