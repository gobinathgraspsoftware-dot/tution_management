<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Models\Student;
use App\Models\Invoice;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class SubscriptionService
{
    protected $invoiceService;
    protected $notificationService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    /**
     * Get enrollments expiring within specified days
     */
    public function getExpiringEnrollments(int $daysAhead = 7): Collection
    {
        $today = Carbon::today();
        $targetDate = Carbon::today()->addDays($daysAhead);

        return Enrollment::active()
            ->whereNotNull('end_date')
            ->whereBetween('end_date', [$today, $targetDate])
            ->with(['student.user', 'student.parent.user', 'package'])
            ->orderBy('end_date')
            ->get()
            ->map(function($enrollment) use ($today) {
                $daysUntilExpiry = $today->diffInDays($enrollment->end_date, false);
                return [
                    'enrollment_id' => $enrollment->id,
                    'student_id' => $enrollment->student_id,
                    'student_name' => $enrollment->student->user->name ?? 'Unknown',
                    'student_code' => $enrollment->student->student_id ?? 'N/A',
                    'parent_name' => $enrollment->student->parent->user->name ?? 'No Parent',
                    'parent_phone' => $enrollment->student->parent->whatsapp_number ?? 'N/A',
                    'parent_email' => $enrollment->student->parent->user->email ?? null,
                    'package_name' => $enrollment->package->name ?? 'Unknown',
                    'package_type' => $enrollment->package->type ?? 'Unknown',
                    'start_date' => $enrollment->start_date,
                    'end_date' => $enrollment->end_date,
                    'days_until_expiry' => $daysUntilExpiry,
                    'monthly_fee' => $enrollment->monthly_fee ?? $enrollment->package->price,
                    'urgency' => $this->getExpiryUrgency($daysUntilExpiry),
                ];
            });
    }

    /**
     * Get expired enrollments that haven't been renewed
     */
    public function getExpiredEnrollments(): Collection
    {
        return Enrollment::where(function($q) {
                $q->where('status', 'expired')
                    ->orWhere(function($q2) {
                        $q2->where('status', 'active')
                            ->whereNotNull('end_date')
                            ->where('end_date', '<', Carbon::today());
                    });
            })
            ->with(['student.user', 'student.parent.user', 'package'])
            ->orderBy('end_date')
            ->get()
            ->map(function($enrollment) {
                $daysExpired = $enrollment->end_date
                    ? Carbon::today()->diffInDays($enrollment->end_date)
                    : 0;
                return [
                    'enrollment_id' => $enrollment->id,
                    'student_id' => $enrollment->student_id,
                    'student_name' => $enrollment->student->user->name ?? 'Unknown',
                    'student_code' => $enrollment->student->student_id ?? 'N/A',
                    'parent_name' => $enrollment->student->parent->user->name ?? 'No Parent',
                    'parent_phone' => $enrollment->student->parent->whatsapp_number ?? 'N/A',
                    'package_name' => $enrollment->package->name ?? 'Unknown',
                    'end_date' => $enrollment->end_date,
                    'days_expired' => $daysExpired,
                    'status' => $enrollment->status,
                ];
            });
    }

    /**
     * Get expiry urgency level
     */
    protected function getExpiryUrgency(int $daysUntilExpiry): string
    {
        if ($daysUntilExpiry <= 0) {
            return 'expired';
        } elseif ($daysUntilExpiry <= 3) {
            return 'critical';
        } elseif ($daysUntilExpiry <= 7) {
            return 'high';
        } elseif ($daysUntilExpiry <= 14) {
            return 'medium';
        } else {
            return 'low';
        }
    }

    /**
     * Process subscription expiry alerts
     */
    public function processExpiryAlerts(): array
    {
        $results = [
            'expiring_7_days' => 0,
            'expiring_3_days' => 0,
            'newly_expired' => 0,
            'notifications_sent' => 0,
            'errors' => [],
        ];

        // Get enrollments expiring in 7 days
        $expiring7Days = $this->getExpiringEnrollments(7);
        $results['expiring_7_days'] = $expiring7Days->count();

        // Get enrollments expiring in 3 days
        $expiring3Days = $expiring7Days->filter(fn($e) => $e['days_until_expiry'] <= 3);
        $results['expiring_3_days'] = $expiring3Days->count();

        // Get newly expired (within last 24 hours)
        $newlyExpired = Enrollment::active()
            ->whereNotNull('end_date')
            ->whereBetween('end_date', [Carbon::yesterday(), Carbon::today()])
            ->count();
        $results['newly_expired'] = $newlyExpired;

        // Send notifications for critical expirations
        foreach ($expiring3Days as $enrollment) {
            try {
                $this->sendExpiryNotification($enrollment);
                $results['notifications_sent']++;
            } catch (\Exception $e) {
                $results['errors'][] = [
                    'enrollment_id' => $enrollment['enrollment_id'],
                    'error' => $e->getMessage(),
                ];
            }
        }

        // Mark expired enrollments
        $this->markExpiredEnrollments();

        return $results;
    }

    /**
     * Send expiry notification
     */
    protected function sendExpiryNotification(array $enrollmentData): void
    {
        $enrollment = Enrollment::find($enrollmentData['enrollment_id']);
        if (!$enrollment) return;

        $student = $enrollment->student;
        if (!$student) return;

        // Create in-app notification for parent
        if ($student->parent && $student->parent->user) {
            Notification::create([
                'user_id' => $student->parent->user_id,
                'type' => 'subscription_expiry',
                'title' => 'Subscription Expiring Soon',
                'message' => "{$student->user->name}'s enrollment in {$enrollment->package->name} will expire in {$enrollmentData['days_until_expiry']} day(s). Please renew to continue classes.",
                'data' => json_encode([
                    'enrollment_id' => $enrollment->id,
                    'student_id' => $student->id,
                    'expiry_date' => $enrollment->end_date->format('Y-m-d'),
                ]),
                'read_at' => null,
            ]);
        }

        // Create in-app notification for admin
        $adminUsers = \App\Models\User::role(['super-admin', 'admin'])->get();
        foreach ($adminUsers as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'type' => 'subscription_expiry_admin',
                'title' => 'Student Subscription Expiring',
                'message' => "{$student->user->name}'s enrollment expires on {$enrollment->end_date->format('d M Y')}.",
                'data' => json_encode([
                    'enrollment_id' => $enrollment->id,
                    'student_id' => $student->id,
                    'expiry_date' => $enrollment->end_date->format('Y-m-d'),
                ]),
                'read_at' => null,
            ]);
        }

        Log::info("Expiry notification sent", [
            'enrollment_id' => $enrollment->id,
            'student_id' => $student->id,
            'days_until_expiry' => $enrollmentData['days_until_expiry'],
        ]);
    }

    /**
     * Mark expired enrollments
     */
    public function markExpiredEnrollments(): int
    {
        $count = Enrollment::where('status', 'active')
            ->whereNotNull('end_date')
            ->where('end_date', '<', Carbon::today())
            ->update(['status' => 'expired']);

        if ($count > 0) {
            Log::info("Marked {$count} enrollments as expired");
        }

        return $count;
    }

    /**
     * Renew an enrollment
     */
    public function renewEnrollment(Enrollment $enrollment, int $months = null, bool $generateInvoice = true): Enrollment
    {
        $months = $months ?? $enrollment->package->duration_months ?? 1;

        DB::beginTransaction();
        try {
            $oldEndDate = $enrollment->end_date ?? Carbon::now();
            $newEndDate = $oldEndDate->copy()->addMonths($months);

            $enrollment->update([
                'end_date' => $newEndDate,
                'status' => 'active',
            ]);

            // Generate renewal invoice if requested
            if ($generateInvoice) {
                $this->invoiceService->generateInvoice($enrollment, [
                    'billing_start' => $oldEndDate,
                    'billing_end' => $newEndDate,
                    'type' => 'renewal',
                    'notes' => "Renewal for {$months} month(s)",
                ]);
            }

            DB::commit();

            Log::info("Enrollment renewed", [
                'enrollment_id' => $enrollment->id,
                'old_end_date' => $oldEndDate->format('Y-m-d'),
                'new_end_date' => $newEndDate->format('Y-m-d'),
            ]);

            return $enrollment;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get subscription summary statistics
     */
    public function getSubscriptionSummary(): array
    {
        $today = Carbon::today();

        return [
            'total_active' => Enrollment::active()->count(),
            'expiring_today' => Enrollment::active()
                ->whereDate('end_date', $today)
                ->count(),
            'expiring_this_week' => Enrollment::active()
                ->whereBetween('end_date', [$today, $today->copy()->addDays(7)])
                ->count(),
            'expiring_this_month' => Enrollment::active()
                ->whereBetween('end_date', [$today, $today->copy()->addDays(30)])
                ->count(),
            'expired_not_renewed' => Enrollment::expired()->count(),
            'suspended' => Enrollment::suspended()->count(),
            'cancelled' => Enrollment::cancelled()->count(),
            'without_end_date' => Enrollment::active()
                ->whereNull('end_date')
                ->count(),
        ];
    }

    /**
     * Get students needing renewal attention
     */
    public function getStudentsNeedingAttention(): Collection
    {
        $today = Carbon::today();
        $nextWeek = $today->copy()->addDays(7);

        return Student::whereHas('enrollments', function($q) use ($today, $nextWeek) {
                $q->where('status', 'active')
                    ->whereNotNull('end_date')
                    ->whereBetween('end_date', [$today, $nextWeek]);
            })
            ->with(['user', 'parent.user', 'enrollments' => function($q) use ($today, $nextWeek) {
                $q->where('status', 'active')
                    ->whereNotNull('end_date')
                    ->whereBetween('end_date', [$today, $nextWeek])
                    ->with('package');
            }])
            ->get()
            ->map(function($student) use ($today) {
                $expiringEnrollments = $student->enrollments->map(function($enrollment) use ($today) {
                    return [
                        'enrollment_id' => $enrollment->id,
                        'package' => $enrollment->package->name,
                        'end_date' => $enrollment->end_date,
                        'days_remaining' => $today->diffInDays($enrollment->end_date, false),
                    ];
                });

                return [
                    'student_id' => $student->id,
                    'student_name' => $student->user->name ?? 'Unknown',
                    'student_code' => $student->student_id ?? 'N/A',
                    'parent_name' => $student->parent->user->name ?? 'No Parent',
                    'parent_phone' => $student->parent->whatsapp_number ?? 'N/A',
                    'expiring_enrollments' => $expiringEnrollments,
                    'soonest_expiry' => $expiringEnrollments->min('days_remaining'),
                ];
            })
            ->sortBy('soonest_expiry');
    }

    /**
     * Bulk renew enrollments
     */
    public function bulkRenewEnrollments(array $enrollmentIds, int $months = 1): array
    {
        $results = [
            'renewed' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        foreach ($enrollmentIds as $id) {
            try {
                $enrollment = Enrollment::find($id);
                if ($enrollment) {
                    $this->renewEnrollment($enrollment, $months);
                    $results['renewed']++;
                } else {
                    $results['failed']++;
                    $results['errors'][] = "Enrollment #{$id} not found";
                }
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = "Enrollment #{$id}: " . $e->getMessage();
            }
        }

        return $results;
    }
}
