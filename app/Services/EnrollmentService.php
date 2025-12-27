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
                throw new \Exception("Class '{$class->name}' is full.");
            }

            // Calculate end date based on package or default
            $startDate = Carbon::parse($data['start_date']);
            $endDate = isset($data['duration_months']) 
                ? $startDate->copy()->addMonths($data['duration_months'])
                : null;

            // Create enrollment
            $enrollment = Enrollment::create([
                'student_id' => $data['student_id'],
                'class_id' => $data['class_id'],
                'package_id' => $data['package_id'] ?? null,
                'enrollment_date' => now(),
                'start_date' => $startDate,
                'end_date' => $endDate,
                'payment_cycle_day' => $data['payment_cycle_day'],
                'monthly_fee' => $data['monthly_fee'] ?? $class->monthly_fee,
                'status' => $data['status'] ?? 'active',
            ]);

            // Increment class enrollment count
            $class->increment('current_enrollment');

            // Check if class is now full
            if ($class->current_enrollment >= $class->capacity) {
                $class->update(['status' => 'full']);
            }

            return $enrollment;
        });
    }

    /**
     * Enroll student in a package (legacy method - enrolls in all classes of package subjects)
     */
    public function enrollInPackage(Student $student, Package $package, array $data): array
    {
        return DB::transaction(function () use ($student, $package, $data) {
            $enrollments = [];
            $startDate = Carbon::parse($data['start_date']);
            $endDate = $startDate->copy()->addMonths($package->duration_months);

            // Get all subjects in the package and their classes
            foreach ($package->subjects as $subject) {
                // Get an available class for this subject
                $class = ClassModel::where('subject_id', $subject->id)
                    ->where('status', 'active')
                    ->whereRaw('current_enrollment < capacity')
                    ->first();

                if ($class) {
                    // Check if already enrolled - skip if yes
                    $existingEnrollment = Enrollment::where('student_id', $student->id)
                        ->where('class_id', $class->id)
                        ->whereIn('status', ['active', 'trial', 'suspended'])
                        ->first();
                    
                    if ($existingEnrollment) {
                        continue; // Skip already enrolled classes
                    }
                    
                    $enrollment = Enrollment::create([
                        'student_id' => $student->id,
                        'package_id' => $package->id,
                        'class_id' => $class->id,
                        'enrollment_date' => now(),
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                        'payment_cycle_day' => $data['payment_cycle_day'],
                        'monthly_fee' => $package->price / $package->subjects->count(),
                        'status' => $data['status'] ?? 'active',
                    ]);

                    $class->increment('current_enrollment');
                    
                    if ($class->current_enrollment >= $class->capacity) {
                        $class->update(['status' => 'full']);
                    }

                    $enrollments[] = $enrollment;
                }
            }

            return $enrollments;
        });
    }

    /**
     * Enroll student in a package with specific class selections for each subject
     * This method SKIPS already enrolled classes instead of throwing an error
     * 
     * @return array ['created' => [], 'skipped' => []]
     */
    public function enrollInPackageWithClasses(Student $student, Package $package, array $subjectClasses, array $data): array
    {
        return DB::transaction(function () use ($student, $package, $subjectClasses, $data) {
            $created = [];
            $skipped = [];
            $startDate = Carbon::parse($data['start_date']);
            $endDate = $startDate->copy()->addMonths($package->duration_months);
            
            // Filter out empty selections
            $validSelections = array_filter($subjectClasses, fn($classId) => !empty($classId));
            
            if (empty($validSelections)) {
                throw new \Exception("No valid class selections were made for the package.");
            }
            
            // Count classes that will actually be created (excluding already enrolled)
            $classesToCreate = 0;
            foreach ($validSelections as $subjectId => $classId) {
                $existingEnrollment = Enrollment::where('student_id', $student->id)
                    ->where('class_id', $classId)
                    ->whereIn('status', ['active', 'trial', 'suspended'])
                    ->first();
                    
                if (!$existingEnrollment) {
                    $classesToCreate++;
                }
            }
            
            // Calculate fee per class based on classes that will be created
            $feePerClass = $classesToCreate > 0 ? $package->price / $classesToCreate : 0;

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
                        'class_name' => 'Unknown',
                        'reason' => 'Invalid class selection or class not active',
                    ];
                    continue;
                }

                // Check for existing enrollment - SKIP instead of throwing error
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
                    continue; // SKIP - Don't throw error
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
                    'payment_cycle_day' => $data['payment_cycle_day'],
                    'monthly_fee' => $feePerClass,
                    'status' => $data['status'] ?? 'active',
                ]);

                // Increment class enrollment count
                $class->increment('current_enrollment');

                // Check if class is now full
                if ($class->current_enrollment >= $class->capacity) {
                    $class->update(['status' => 'full']);
                }

                $created[] = $enrollment;
            }

            // If no enrollments were created, throw error with details
            if (empty($created)) {
                $reasons = array_map(fn($s) => ($s['class_name'] ?? 'Unknown') . ': ' . $s['reason'], $skipped);
                throw new \Exception("No new enrollments could be created. All selected classes were skipped: " . implode('; ', $reasons));
            }

            return [
                'created' => $created,
                'skipped' => $skipped,
            ];
        });
    }

    /**
     * Update enrollment
     */
    public function updateEnrollment(Enrollment $enrollment, array $data): Enrollment
    {
        return DB::transaction(function () use ($enrollment, $data) {
            $oldMonthlyFee = $enrollment->monthly_fee;
            
            // Check if monthly fee changed
            if (isset($data['monthly_fee']) && $data['monthly_fee'] != $oldMonthlyFee) {
                // Record fee change history
                EnrollmentFeeHistory::create([
                    'enrollment_id' => $enrollment->id,
                    'package_id' => $enrollment->package_id,
                    'old_fee' => $oldMonthlyFee,
                    'new_fee' => $data['monthly_fee'],
                    'reason' => $data['fee_change_reason'] ?? 'Manual update',
                    'change_date' => now(),
                    'changed_by' => auth()->id(),
                ]);
            }

            $enrollment->update([
                'start_date' => $data['start_date'] ?? $enrollment->start_date,
                'end_date' => $data['end_date'] ?? $enrollment->end_date,
                'payment_cycle_day' => $data['payment_cycle_day'] ?? $enrollment->payment_cycle_day,
                'monthly_fee' => $data['monthly_fee'] ?? $enrollment->monthly_fee,
                'status' => $data['status'] ?? $enrollment->status,
            ]);

            return $enrollment;
        });
    }

    /**
     * Cancel enrollment
     */
    public function cancelEnrollment(Enrollment $enrollment, string $reason = null): Enrollment
    {
        return DB::transaction(function () use ($enrollment, $reason) {
            // Update enrollment status
            $enrollment->update([
                'status' => 'cancelled',
                'cancellation_reason' => $reason,
                'cancelled_at' => now(),
            ]);

            // Decrement class enrollment count
            if ($enrollment->class) {
                $enrollment->class->decrement('current_enrollment');
                
                // Update class status if it was full
                if ($enrollment->class->status === 'full') {
                    $enrollment->class->update(['status' => 'active']);
                }
            }

            return $enrollment;
        });
    }

    /**
     * Suspend enrollment
     */
    public function suspendEnrollment(Enrollment $enrollment, string $reason = null): Enrollment
    {
        return DB::transaction(function () use ($enrollment, $reason) {
            $enrollment->update([
                'status' => 'suspended',
            ]);

            return $enrollment;
        });
    }

    /**
     * Resume enrollment
     */
    public function resumeEnrollment(Enrollment $enrollment): Enrollment
    {
        return DB::transaction(function () use ($enrollment) {
            // Check if class still has capacity
            if ($enrollment->class && $enrollment->class->current_enrollment >= $enrollment->class->capacity) {
                throw new \Exception("Cannot resume enrollment. Class is now full.");
            }

            $enrollment->update([
                'status' => 'active',
            ]);

            return $enrollment;
        });
    }

    /**
     * Renew enrollment
     */
    public function renewEnrollment(Enrollment $enrollment, int $months = null): Enrollment
    {
        return DB::transaction(function () use ($enrollment, $months) {
            // Determine renewal duration
            if (!$months && $enrollment->package) {
                $months = $enrollment->package->duration_months;
            }
            $months = $months ?? 1;

            // Calculate new end date
            $currentEndDate = $enrollment->end_date ?? now();
            $newEndDate = Carbon::parse($currentEndDate)->addMonths($months);

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
        return [
            'total' => Enrollment::count(),
            'active' => Enrollment::where('status', 'active')->count(),
            'suspended' => Enrollment::where('status', 'suspended')->count(),
            'expired' => Enrollment::where('status', 'expired')->count(),
            'cancelled' => Enrollment::where('status', 'cancelled')->count(),
            'trial' => Enrollment::where('status', 'trial')->count(),
            'expiring_soon' => Enrollment::where('status', 'active')
                ->whereNotNull('end_date')
                ->whereBetween('end_date', [now(), now()->addDays(30)])
                ->count(),
        ];
    }

    /**
     * Check if student is enrolled in a class
     */
    public function isStudentEnrolled(int $studentId, int $classId): bool
    {
        return Enrollment::where('student_id', $studentId)
            ->where('class_id', $classId)
            ->whereIn('status', ['active', 'trial', 'suspended'])
            ->exists();
    }

    /**
     * Get student enrollments by package
     */
    public function getStudentPackageEnrollments(int $studentId, int $packageId)
    {
        return Enrollment::where('student_id', $studentId)
            ->where('package_id', $packageId)
            ->with(['class.subject', 'class.teacher.user'])
            ->get();
    }

    /**
     * Check expiring enrollments and update status
     */
    public function processExpiringEnrollments(): int
    {
        $count = 0;
        
        $expiredEnrollments = Enrollment::where('status', 'active')
            ->whereNotNull('end_date')
            ->where('end_date', '<', now())
            ->get();

        foreach ($expiredEnrollments as $enrollment) {
            $enrollment->update(['status' => 'expired']);
            
            // Decrement class enrollment
            if ($enrollment->class) {
                $enrollment->class->decrement('current_enrollment');
                
                if ($enrollment->class->status === 'full') {
                    $enrollment->class->update(['status' => 'active']);
                }
            }
            
            $count++;
        }

        return $count;
    }

    /**
     * Transfer enrollment to different class
     */
    public function transferEnrollment(Enrollment $enrollment, int $newClassId, string $reason = null): Enrollment
    {
        return DB::transaction(function () use ($enrollment, $newClassId, $reason) {
            $newClass = ClassModel::findOrFail($newClassId);
            
            // Check if new class has capacity
            if ($newClass->current_enrollment >= $newClass->capacity) {
                throw new \Exception("Target class '{$newClass->name}' is full.");
            }
            
            // Check subject compatibility if in package
            if ($enrollment->package_id && $enrollment->class) {
                if ($enrollment->class->subject_id !== $newClass->subject_id) {
                    throw new \Exception("Cannot transfer to a class with a different subject.");
                }
            }
            
            // Check if student is already enrolled in the new class
            $existingEnrollment = Enrollment::where('student_id', $enrollment->student_id)
                ->where('class_id', $newClassId)
                ->whereIn('status', ['active', 'trial', 'suspended'])
                ->where('id', '!=', $enrollment->id)
                ->first();
                
            if ($existingEnrollment) {
                throw new \Exception("Student is already enrolled in the target class.");
            }
            
            // Decrement old class count
            if ($enrollment->class) {
                $enrollment->class->decrement('current_enrollment');
                
                if ($enrollment->class->status === 'full') {
                    $enrollment->class->update(['status' => 'active']);
                }
            }
            
            // Update enrollment
            $enrollment->update([
                'class_id' => $newClassId,
            ]);
            
            // Increment new class count
            $newClass->increment('current_enrollment');
            
            if ($newClass->current_enrollment >= $newClass->capacity) {
                $newClass->update(['status' => 'full']);
            }
            
            return $enrollment->fresh();
        });
    }
}
