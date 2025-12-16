<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Models\Student;
use App\Models\Package;
use App\Models\ClassModel;
use App\Models\Invoice;
use App\Models\EnrollmentFeeHistory;
use App\Models\MaterialAccess;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class EnrollmentService
{
    protected $notificationService;
    protected $invoiceService;

    public function __construct()
    {
        // Use app() to avoid circular dependency issues
        $this->notificationService = app(\App\Services\NotificationService::class);
        $this->invoiceService = app(\App\Services\InvoiceService::class);
    }

    /**
     * Create a new enrollment
     */
    public function createEnrollment(array $data): Enrollment
    {
        DB::beginTransaction();
        try {
            // Calculate end date if not provided
            if (!isset($data['end_date']) && isset($data['package_id'])) {
                $package = Package::findOrFail($data['package_id']);
                $startDate = Carbon::parse($data['start_date']);
                $data['end_date'] = $startDate->copy()->addMonths($package->duration_months);
            }

            // Set enrollment date if not provided
            if (!isset($data['enrollment_date'])) {
                $data['enrollment_date'] = now();
            }

            // Set default status
            if (!isset($data['status'])) {
                $data['status'] = 'active';
            }

            // Create enrollment
            $enrollment = Enrollment::create($data);

            // Grant material access for the class
            if ($enrollment->class_id) {
                $this->grantMaterialAccess($enrollment);
            }

            // Generate first invoice
            if ($enrollment->status === 'active') {
                try {
                    $this->invoiceService->generateRegistrationInvoice($enrollment);
                } catch (\Exception $e) {
                    Log::warning('Invoice generation failed for enrollment ' . $enrollment->id . ': ' . $e->getMessage());
                }
            }

            // Send confirmation notification
            try {
                $this->sendEnrollmentConfirmation($enrollment);
            } catch (\Exception $e) {
                Log::warning('Notification failed for enrollment ' . $enrollment->id . ': ' . $e->getMessage());
            }

            DB::commit();
            return $enrollment->load(['student.user', 'package', 'class']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Enrollment creation failed: ' . $e->getMessage(), [
                'data' => $data,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Enroll student in package (creates multiple enrollments)
     */
    public function enrollInPackage(Student $student, Package $package, array $data): array
    {
        DB::beginTransaction();
        try {
            $enrollments = [];

            // Get all classes in the package
            $classes = $package->subjects()->with('classes')->get()
                ->flatMap(fn($subject) => $subject->classes)
                ->where('status', 'active');

            if ($classes->isEmpty()) {
                throw new \Exception("No active classes found for this package.");
            }

            // Create enrollment for each class
            foreach ($classes as $class) {
                $enrollmentData = [
                    'student_id' => $student->id,
                    'package_id' => $package->id,
                    'class_id' => $class->id,
                    'enrollment_date' => $data['enrollment_date'] ?? now(),
                    'start_date' => $data['start_date'],
                    'end_date' => Carbon::parse($data['start_date'])->addMonths($package->duration_months),
                    'payment_cycle_day' => $data['payment_cycle_day'],
                    'monthly_fee' => $package->price / $classes->count(), // Divide package price
                    'status' => 'active',
                ];

                $enrollment = Enrollment::create($enrollmentData);
                $this->grantMaterialAccess($enrollment);
                $enrollments[] = $enrollment;
            }

            // Generate single invoice for the package
            if (!empty($enrollments)) {
                try {
                    $firstEnrollment = $enrollments[0];
                    $this->invoiceService->generateRegistrationInvoice($firstEnrollment);
                } catch (\Exception $e) {
                    Log::warning('Package invoice generation failed: ' . $e->getMessage());
                }
            }

            // Send confirmation - FIXED: Correct NotificationService call
            try {
                $this->notificationService->send(
                    $student->user,  // User object
                    'enrollment',    // Type
                    [
                        'student_name' => $student->user->name,
                        'package_name' => $package->name,
                        'class_count' => $classes->count(),
                        'start_date' => Carbon::parse($data['start_date'])->format('d M Y'),
                        'message' => "You have been successfully enrolled in {$package->name} package with {$classes->count()} classes.",
                    ],
                    ['whatsapp', 'email']  // Channels
                );
            } catch (\Exception $e) {
                Log::warning('Package enrollment notification failed: ' . $e->getMessage());
            }

            DB::commit();
            return $enrollments;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Package enrollment failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update enrollment details
     */
    public function updateEnrollment(Enrollment $enrollment, array $data): Enrollment
    {
        DB::beginTransaction();
        try {
            // Track fee changes
            if (isset($data['monthly_fee']) && $data['monthly_fee'] != $enrollment->monthly_fee) {
                EnrollmentFeeHistory::create([
                    'enrollment_id' => $enrollment->id,
                    'old_fee' => $enrollment->monthly_fee,
                    'new_fee' => $data['monthly_fee'],
                    'changed_by' => auth()->id(),
                    'change_date' => now(),
                    'reason' => $data['fee_change_reason'] ?? 'Fee updated',
                ]);
            }

            $enrollment->update($data);

            DB::commit();
            return $enrollment->load(['student.user', 'package', 'class']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Cancel enrollment
     */
    public function cancelEnrollment(Enrollment $enrollment, ?string $reason = null): bool
    {
        DB::beginTransaction();
        try {
            $enrollment->update([
                'status' => 'cancelled',
                'cancellation_reason' => $reason,
                'cancelled_at' => now(),
            ]);

            // Revoke material access
            MaterialAccess::where('enrollment_id', $enrollment->id)->delete();

            // Cancel pending invoices
            Invoice::where('enrollment_id', $enrollment->id)
                ->where('status', 'pending')
                ->update(['status' => 'cancelled']);

            // Send cancellation notification - FIXED
            try {
                $this->notificationService->send(
                    $enrollment->student->user,
                    'enrollment',
                    [
                        'student_name' => $enrollment->student->user->name,
                        'class_name' => $enrollment->class->name,
                        'message' => "Your enrollment in {$enrollment->class->name} has been cancelled.",
                        'reason' => $reason,
                    ],
                    ['whatsapp']
                );
            } catch (\Exception $e) {
                Log::warning('Cancellation notification failed: ' . $e->getMessage());
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Suspend enrollment
     */
    public function suspendEnrollment(Enrollment $enrollment, ?string $reason = null): bool
    {
        $enrollment->update([
            'status' => 'suspended',
            'cancellation_reason' => $reason,
        ]);

        // Send suspension notification - FIXED
        try {
            $this->notificationService->send(
                $enrollment->student->user,
                'enrollment',
                [
                    'student_name' => $enrollment->student->user->name,
                    'class_name' => $enrollment->class->name,
                    'message' => "Your enrollment in {$enrollment->class->name} has been suspended.",
                    'reason' => $reason,
                ],
                ['whatsapp']
            );
        } catch (\Exception $e) {
            Log::warning('Suspension notification failed: ' . $e->getMessage());
        }

        return true;
    }

    /**
     * Resume enrollment
     */
    public function resumeEnrollment(Enrollment $enrollment): bool
    {
        $enrollment->update([
            'status' => 'active',
            'cancellation_reason' => null,
        ]);

        // Send resume notification - FIXED
        try {
            $this->notificationService->send(
                $enrollment->student->user,
                'enrollment',
                [
                    'student_name' => $enrollment->student->user->name,
                    'class_name' => $enrollment->class->name,
                    'message' => "Your enrollment in {$enrollment->class->name} has been resumed.",
                ],
                ['whatsapp']
            );
        } catch (\Exception $e) {
            Log::warning('Resume notification failed: ' . $e->getMessage());
        }

        return true;
    }

    /**
     * Renew enrollment
     */
    public function renewEnrollment(Enrollment $enrollment, ?int $months = null): Enrollment
    {
        DB::beginTransaction();
        try {
            // Calculate new end date
            $currentEndDate = $enrollment->end_date ?? now();
            $extensionMonths = $months ?? ($enrollment->package ? $enrollment->package->duration_months : 1);

            $enrollment->update([
                'end_date' => Carbon::parse($currentEndDate)->addMonths($extensionMonths),
                'status' => 'active',
            ]);

            // Generate renewal invoice
            try {
                $this->invoiceService->generateInvoice($enrollment, [
                    'type' => 'renewal',
                    'notes' => 'Enrollment renewal',
                ]);
            } catch (\Exception $e) {
                Log::warning('Renewal invoice generation failed: ' . $e->getMessage());
            }

            // Send renewal confirmation - FIXED
            try {
                $this->notificationService->send(
                    $enrollment->student->user,
                    'enrollment',
                    [
                        'student_name' => $enrollment->student->user->name,
                        'class_name' => $enrollment->class->name,
                        'end_date' => $enrollment->end_date->format('d M Y'),
                        'message' => "Your enrollment in {$enrollment->class->name} has been renewed until " . $enrollment->end_date->format('d M Y'),
                    ],
                    ['whatsapp', 'email']
                );
            } catch (\Exception $e) {
                Log::warning('Renewal notification failed: ' . $e->getMessage());
            }

            DB::commit();
            return $enrollment;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Grant material access for enrollment
     * CRITICAL FIX: MaterialAccess table uses user_id, NOT student_id
     */
    protected function grantMaterialAccess(Enrollment $enrollment): void
    {
        try {
            $class = $enrollment->class;
            if (!$class) {
                Log::warning('Class not found for enrollment ' . $enrollment->id);
                return;
            }

            $student = $enrollment->student;
            if (!$student || !$student->user_id) {
                Log::warning('Student or user_id not found for enrollment ' . $enrollment->id);
                return;
            }

            // Get all materials for this class
            $materials = $class->materials()->where('status', 'published')->get();

            foreach ($materials as $material) {
                // CRITICAL FIX: Use user_id instead of student_id
                MaterialAccess::firstOrCreate([
                    'material_id' => $material->id,
                    'user_id' => $student->user_id,  // ← FIXED: was student_id
                    'enrollment_id' => $enrollment->id,
                ], [
                    'class_id' => $class->id,
                    'access_granted_at' => now(),
                    'granted_by' => auth()->id() ?? 1, // System user if no auth
                ]);
            }

            Log::info('Material access granted for enrollment ' . $enrollment->id, [
                'student_id' => $student->id,
                'user_id' => $student->user_id,
                'class_id' => $class->id,
                'materials_count' => $materials->count(),
            ]);

        } catch (\Exception $e) {
            Log::error('Material access grant failed for enrollment ' . $enrollment->id . ': ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            // Don't throw - allow enrollment to proceed even if material access fails
        }
    }

    /**
     * Send enrollment confirmation notification
     * FIXED: Correct NotificationService call
     */
    protected function sendEnrollmentConfirmation(Enrollment $enrollment): void
    {
        $class = $enrollment->class;
        $package = $enrollment->package;

        $message = "You have been successfully enrolled in ";
        $message .= $package ? "{$package->name} package" : "{$class->name}";
        $message .= ". Your classes start on " . $enrollment->start_date->format('d M Y');

        if ($enrollment->payment_cycle_day) {
            $message .= ". Monthly payment is due on day {$enrollment->payment_cycle_day} of each month.";
        }

        // FIXED: Correct NotificationService::send() call
        $this->notificationService->send(
            $enrollment->student->user,  // User object (not user_id)
            'enrollment',                // Type
            [                            // Data array
                'student_name' => $enrollment->student->user->name,
                'class_name' => $package ? $package->name : $class->name,
                'start_date' => $enrollment->start_date->format('d M Y'),
                'payment_cycle_day' => $enrollment->payment_cycle_day,
                'message' => $message,
            ],
            ['whatsapp', 'email']       // Channels (database is automatic)
        );
    }

    /**
     * Get enrollment statistics
     */
    public function getEnrollmentStats(?Student $student = null): array
    {
        $query = Enrollment::query();

        if ($student) {
            $query->where('student_id', $student->id);
        }

        return [
            'total' => $query->count(),
            'active' => (clone $query)->active()->count(),
            'expired' => (clone $query)->expired()->count(),
            'suspended' => (clone $query)->suspended()->count(),
            'cancelled' => (clone $query)->cancelled()->count(),
            'trial' => (clone $query)->trial()->count(),
            'expiring_soon' => (clone $query)->active()->expiringWithin(30)->count(),
        ];
    }

    /**
     * Check if student can enroll in class
     */
    public function canEnroll(Student $student, ClassModel $class): array
    {
        $errors = [];

        // Check if student is approved
        if ($student->status !== 'approved') {
            $errors[] = 'Student must be approved before enrollment.';
        }

        // Check if class is active
        if ($class->status !== 'active') {
            $errors[] = 'This class is not currently accepting enrollments.';
        }

        // Check class capacity
        if ($class->max_students && $class->enrollments()->active()->count() >= $class->max_students) {
            $errors[] = 'This class has reached maximum capacity.';
        }

        // Check for duplicate enrollment
        $existingEnrollment = Enrollment::where('student_id', $student->id)
            ->where('class_id', $class->id)
            ->whereIn('status', ['active', 'suspended'])
            ->exists();

        if ($existingEnrollment) {
            $errors[] = 'Student is already enrolled in this class.';
        }

        return [
            'can_enroll' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Get available classes for student
     */
    public function getAvailableClasses(Student $student)
    {
        // Get all active classes
        $classes = ClassModel::with(['subject', 'teacher.user', 'schedules'])
            ->where('status', 'active')
            ->get();

        // Filter out classes student is already enrolled in
        $enrolledClassIds = Enrollment::where('student_id', $student->id)
            ->whereIn('status', ['active', 'suspended'])
            ->pluck('class_id')
            ->toArray();

        return $classes->filter(function ($class) use ($enrolledClassIds) {
            return !in_array($class->id, $enrolledClassIds);
        });
    }

    /**
     * Get available packages for student
     */
    public function getAvailablePackages(Student $student)
    {
        // Get all active packages
        $packages = Package::with(['subjects', 'discountRule'])
            ->where('status', 'active')
            ->get();

        // Get enrolled package IDs
        $enrolledPackageIds = Enrollment::where('student_id', $student->id)
            ->whereIn('status', ['active', 'suspended'])
            ->whereNotNull('package_id')
            ->pluck('package_id')
            ->unique()
            ->toArray();

        return $packages->filter(function ($package) use ($enrolledPackageIds) {
            return !in_array($package->id, $enrolledPackageIds);
        });
    }
}
