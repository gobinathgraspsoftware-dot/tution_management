<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Models\Invoice;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PaymentCycleService
{
    /**
     * Get payment cycle summary for a specific month
     */
    public function getPaymentCycleSummary(Carbon $month): array
    {
        $startOfMonth = $month->copy()->startOfMonth();
        $endOfMonth = $month->copy()->endOfMonth();

        $enrollments = Enrollment::active()
            ->with(['student.user', 'package', 'invoices' => function ($q) use ($startOfMonth, $endOfMonth) {
                $q->whereBetween('billing_period_start', [$startOfMonth, $endOfMonth]);
            }])
            ->get();

        $summary = [
            'total_enrollments' => $enrollments->count(),
            'total_expected' => 0,
            'total_collected' => 0,
            'pending' => 0,
            'overdue' => 0,
        ];

        $details = [];

        foreach ($enrollments as $enrollment) {
            $invoice = $enrollment->invoices->first();
            $amountDue = $enrollment->monthly_fee;
            $amountPaid = $invoice ? $invoice->paid_amount : 0;
            $status = 'pending';

            if ($invoice) {
                $summary['total_expected'] += $invoice->total_amount;
                $summary['total_collected'] += $invoice->paid_amount;

                if ($invoice->status === 'paid') {
                    $status = 'paid';
                } elseif ($invoice->isOverdue()) {
                    $status = 'overdue';
                    $summary['overdue']++;
                } else {
                    $summary['pending']++;
                }
            } else {
                $summary['total_expected'] += $amountDue;
                $summary['pending']++;
            }

            $details[] = [
                'enrollment_id' => $enrollment->id,
                'student_id' => $enrollment->student_id,
                'student_name' => $enrollment->student->user->name ?? 'Unknown',
                'student_code' => $enrollment->student->student_id ?? 'N/A',
                'package' => $enrollment->package->name ?? 'Unknown',
                'payment_cycle_day' => $enrollment->payment_cycle_day ?? 1,
                'amount_due' => $amountDue,
                'amount_paid' => $amountPaid,
                'balance' => $amountDue - $amountPaid,
                'status' => $status,
                'invoice_id' => $invoice?->id,
                'invoice_number' => $invoice?->invoice_number,
                'due_date' => $invoice?->due_date?->format('Y-m-d'),
            ];
        }

        $summary['collection_rate'] = $summary['total_expected'] > 0 
            ? round(($summary['total_collected'] / $summary['total_expected']) * 100, 2)
            : 0;

        return [
            'month' => $month->format('F Y'),
            'summary' => $summary,
            'details' => $details,
        ];
    }

    /**
     * Get payment cycles for a specific student
     */
    public function getStudentPaymentCycles(Student $student, int $monthsBack = 12): Collection
    {
        $enrollments = $student->enrollments()->with('package')->get();
        $cycles = collect();

        foreach ($enrollments as $enrollment) {
            $invoices = Invoice::where('enrollment_id', $enrollment->id)
                ->where('created_at', '>=', Carbon::now()->subMonths($monthsBack))
                ->orderBy('billing_period_start', 'desc')
                ->get();

            foreach ($invoices as $invoice) {
                $cycles->push([
                    'enrollment_id' => $enrollment->id,
                    'package' => $enrollment->package->name ?? 'Single Class',
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'billing_period' => $invoice->billing_period_start->format('M Y'),
                    'billing_start' => $invoice->billing_period_start,
                    'billing_end' => $invoice->billing_period_end,
                    'due_date' => $invoice->due_date,
                    'total_amount' => $invoice->total_amount,
                    'paid_amount' => $invoice->paid_amount,
                    'balance' => $invoice->balance,
                    'status' => $invoice->status,
                    'is_overdue' => $invoice->isOverdue(),
                    'payment_cycle_day' => $enrollment->payment_cycle_day ?? 1,
                ]);
            }
        }

        return $cycles->sortByDesc('billing_start');
    }

    /**
     * Update payment cycle day for an enrollment
     * Payment cycle day must be between 1 and 15
     */
    public function updatePaymentCycleDay(Enrollment $enrollment, int $newCycleDay): Enrollment
    {
        if ($newCycleDay < 1 || $newCycleDay > 15) {
            throw new \InvalidArgumentException("Payment cycle day must be between 1 and 15");
        }

        $enrollment->update(['payment_cycle_day' => $newCycleDay]);

        return $enrollment;
    }

    /**
     * Get upcoming payment cycles (due within X days)
     */
    public function getUpcomingPaymentCycles(int $daysAhead = 7): Collection
    {
        $today = Carbon::today();
        $targetDate = Carbon::today()->addDays($daysAhead);

        return Invoice::whereIn('status', ['pending', 'partial'])
            ->whereBetween('due_date', [$today, $targetDate])
            ->with(['student.user', 'enrollment.package'])
            ->orderBy('due_date')
            ->get()
            ->map(function($invoice) {
                return [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'student_id' => $invoice->student_id,
                    'student_name' => $invoice->student->user->name ?? 'Unknown',
                    'student_code' => $invoice->student->student_id ?? 'N/A',
                    'package' => $invoice->enrollment->package->name ?? 'Unknown',
                    'due_date' => $invoice->due_date,
                    'days_until_due' => Carbon::today()->diffInDays($invoice->due_date, false),
                    'total_amount' => $invoice->total_amount,
                    'balance' => $invoice->balance,
                    'status' => $invoice->status,
                ];
            });
    }

    /**
     * Get overdue payment cycles
     */
    public function getOverduePaymentCycles(): Collection
    {
        return Invoice::overdue()
            ->with(['student.user', 'student.parent.user', 'enrollment.package'])
            ->orderBy('due_date')
            ->get()
            ->map(function($invoice) {
                return [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'student_id' => $invoice->student_id,
                    'student_name' => $invoice->student->user->name ?? 'Unknown',
                    'student_code' => $invoice->student->student_id ?? 'N/A',
                    'parent_name' => $invoice->student->parent->user->name ?? 'No Parent',
                    'parent_phone' => $invoice->student->parent->whatsapp_number ?? 'N/A',
                    'package' => $invoice->enrollment->package->name ?? 'Unknown',
                    'due_date' => $invoice->due_date,
                    'days_overdue' => Carbon::today()->diffInDays($invoice->due_date),
                    'total_amount' => $invoice->total_amount,
                    'balance' => $invoice->balance,
                    'status' => $invoice->status,
                ];
            });
    }

    /**
     * Get students with multiple overdue invoices
     */
    public function getStudentsWithMultipleOverdue(int $minOverdue = 2): Collection
    {
        return Student::whereHas('invoices', function ($q) {
                $q->overdue();
            }, '>=', 2)
            ->with(['user', 'parent.user', 'invoices' => function ($q) {
                $q->overdue()->orderBy('due_date');
            }])
            ->get()
            ->map(function ($student) {
                return [
                    'student_id' => $student->id,
                    'student_name' => $student->user->name ?? 'Unknown',
                    'student_code' => $student->student_id ?? 'N/A',
                    'parent_name' => $student->parent->user->name ?? 'No Parent',
                    'parent_phone' => $student->parent->whatsapp_number ?? 'N/A',
                    'overdue_count' => $student->invoices->count(),
                    'total_overdue' => $student->invoices->sum('balance'),
                    'oldest_overdue' => $student->invoices->first()?->due_date,
                    'oldest_days' => $student->invoices->first()?->due_date?->diffInDays(Carbon::today()),
                ];
            });
    }

    /**
     * Calculate enrollment course duration tracking
     */
    public function getCourseDurationStatus(Enrollment $enrollment): array
    {
        $startDate = $enrollment->start_date;
        $endDate = $enrollment->end_date;
        $today = Carbon::today();

        if (!$startDate) {
            return [
                'status' => 'not_started',
                'message' => 'Enrollment has not started yet',
                'start_date' => null,
                'end_date' => $endDate,
                'days_remaining' => null,
                'progress_percentage' => 0,
            ];
        }

        // If no end date, it's an ongoing enrollment
        if (!$endDate) {
            $monthsEnrolled = $startDate->diffInMonths($today);
            return [
                'status' => 'ongoing',
                'message' => "Active enrollment for {$monthsEnrolled} month(s)",
                'start_date' => $startDate,
                'end_date' => null,
                'days_remaining' => null,
                'months_enrolled' => $monthsEnrolled,
                'progress_percentage' => null,
            ];
        }

        $totalDays = $startDate->diffInDays($endDate);
        $daysElapsed = $startDate->diffInDays($today);
        $daysRemaining = max(0, $today->diffInDays($endDate, false));
        $progressPercentage = $totalDays > 0 ? min(100, round(($daysElapsed / $totalDays) * 100, 1)) : 0;

        $status = 'active';
        $message = "Active - {$daysRemaining} days remaining";

        if ($today->gt($endDate)) {
            $status = 'expired';
            $message = 'Enrollment has expired';
            $daysRemaining = 0;
            $progressPercentage = 100;
        } elseif ($daysRemaining <= 7) {
            $status = 'expiring_soon';
            $message = "Expiring soon - {$daysRemaining} days remaining";
        } elseif ($daysRemaining <= 30) {
            $status = 'expiring_this_month';
            $message = "Expiring this month - {$daysRemaining} days remaining";
        }

        return [
            'status' => $status,
            'message' => $message,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'days_remaining' => $daysRemaining,
            'days_elapsed' => $daysElapsed,
            'total_days' => $totalDays,
            'progress_percentage' => $progressPercentage,
        ];
    }

    /**
     * Get enrollments expiring within specified days
     */
    public function getExpiringEnrollments(int $daysAhead = 30): Collection
    {
        return Enrollment::active()
            ->whereNotNull('end_date')
            ->whereBetween('end_date', [Carbon::today(), Carbon::today()->addDays($daysAhead)])
            ->with(['student.user', 'package'])
            ->orderBy('end_date')
            ->get()
            ->map(function ($enrollment) {
                return [
                    'enrollment_id' => $enrollment->id,
                    'student_id' => $enrollment->student_id,
                    'student_name' => $enrollment->student->user->name ?? 'Unknown',
                    'student_code' => $enrollment->student->student_id ?? 'N/A',
                    'package' => $enrollment->package->name ?? 'Single Class',
                    'end_date' => $enrollment->end_date,
                    'days_remaining' => Carbon::today()->diffInDays($enrollment->end_date, false),
                ];
            });
    }

    /**
     * Get payment cycle day options (1-15)
     */
    public function getPaymentCycleDayOptions(): array
    {
        $options = [];
        for ($i = 1; $i <= 15; $i++) {
            $suffix = 'th';
            if ($i == 1) $suffix = 'st';
            elseif ($i == 2) $suffix = 'nd';
            elseif ($i == 3) $suffix = 'rd';
            
            $options[$i] = "{$i}{$suffix} of each month";
        }
        return $options;
    }
}
