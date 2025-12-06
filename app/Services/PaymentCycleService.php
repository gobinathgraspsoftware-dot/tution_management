<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class PaymentCycleService
{
    /**
     * Get payment cycle status for all active enrollments
     */
    public function getPaymentCycleOverview(?Carbon $month = null): array
    {
        $month = $month ?? Carbon::now();
        $startOfMonth = $month->copy()->startOfMonth();
        $endOfMonth = $month->copy()->endOfMonth();

        $enrollments = Enrollment::active()
            ->with(['student.user', 'package', 'invoices' => function($q) use ($startOfMonth, $endOfMonth) {
                $q->whereBetween('billing_period_start', [$startOfMonth, $endOfMonth])
                    ->orWhereBetween('billing_period_end', [$startOfMonth, $endOfMonth]);
            }])
            ->get();

        $summary = [
            'total_enrollments' => $enrollments->count(),
            'fully_paid' => 0,
            'partially_paid' => 0,
            'pending' => 0,
            'overdue' => 0,
            'no_invoice' => 0,
            'total_expected' => 0,
            'total_collected' => 0,
            'collection_rate' => 0,
        ];

        $details = [];

        foreach ($enrollments as $enrollment) {
            $invoice = $enrollment->invoices->first();
            $status = 'no_invoice';
            $amountDue = $enrollment->monthly_fee ?? $enrollment->package->price;
            $amountPaid = 0;

            if ($invoice) {
                $amountDue = $invoice->total_amount;
                $amountPaid = $invoice->paid_amount;
                $status = $invoice->status;

                if ($invoice->isPaid()) {
                    $summary['fully_paid']++;
                    $status = 'paid';
                } elseif ($invoice->status === 'partial') {
                    $summary['partially_paid']++;
                } elseif ($invoice->isOverdue()) {
                    $summary['overdue']++;
                    $status = 'overdue';
                } else {
                    $summary['pending']++;
                }
            } else {
                $summary['no_invoice']++;
            }

            $summary['total_expected'] += $amountDue;
            $summary['total_collected'] += $amountPaid;

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
                    'package' => $enrollment->package->name,
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
     */
    public function updatePaymentCycleDay(Enrollment $enrollment, int $newCycleDay): Enrollment
    {
        if ($newCycleDay < 1 || $newCycleDay > 28) {
            throw new \InvalidArgumentException("Payment cycle day must be between 1 and 28");
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
                    'days_overdue' => $invoice->due_date->diffInDays(Carbon::today()),
                    'total_amount' => $invoice->total_amount,
                    'paid_amount' => $invoice->paid_amount,
                    'balance' => $invoice->balance,
                    'reminder_count' => $invoice->reminder_count,
                    'last_reminder' => $invoice->last_reminder_at,
                ];
            });
    }

    /**
     * Get payment history summary by month
     */
    public function getMonthlyPaymentSummary(int $monthsBack = 6): Collection
    {
        $summary = collect();

        for ($i = 0; $i < $monthsBack; $i++) {
            $month = Carbon::now()->subMonths($i);
            $startOfMonth = $month->copy()->startOfMonth();
            $endOfMonth = $month->copy()->endOfMonth();

            $invoices = Invoice::whereBetween('billing_period_start', [$startOfMonth, $endOfMonth])->get();
            $payments = Payment::completed()
                ->whereBetween('payment_date', [$startOfMonth, $endOfMonth])
                ->get();

            $summary->push([
                'month' => $month->format('M Y'),
                'month_key' => $month->format('Y-m'),
                'total_invoiced' => $invoices->sum('total_amount'),
                'total_collected' => $payments->sum('amount'),
                'invoices_count' => $invoices->count(),
                'payments_count' => $payments->count(),
                'paid_invoices' => $invoices->where('status', 'paid')->count(),
                'pending_invoices' => $invoices->where('status', 'pending')->count(),
                'overdue_invoices' => $invoices->where('status', 'overdue')->count(),
                'collection_rate' => $invoices->sum('total_amount') > 0
                    ? round(($payments->sum('amount') / $invoices->sum('total_amount')) * 100, 2)
                    : 0,
            ]);
        }

        return $summary;
    }

    /**
     * Get students with payment issues (multiple overdue invoices)
     */
    public function getStudentsWithPaymentIssues(): Collection
    {
        return Student::whereHas('invoices', function($q) {
                $q->overdue();
            })
            ->withCount(['invoices as overdue_count' => function($q) {
                $q->overdue();
            }])
            ->with(['user', 'parent.user', 'invoices' => function($q) {
                $q->overdue()->orderBy('due_date');
            }])
            ->having('overdue_count', '>', 0)
            ->orderByDesc('overdue_count')
            ->get()
            ->map(function($student) {
                return [
                    'student_id' => $student->id,
                    'student_name' => $student->user->name ?? 'Unknown',
                    'student_code' => $student->student_id ?? 'N/A',
                    'parent_name' => $student->parent->user->name ?? 'No Parent',
                    'parent_phone' => $student->parent->whatsapp_number ?? 'N/A',
                    'overdue_count' => $student->overdue_count,
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
        $progressPercentage = $totalDays > 0 ? min(100, round(($daysElapsed / $totalDays) * 100, 2)) : 100;

        if ($today->gt($endDate)) {
            return [
                'status' => 'expired',
                'message' => 'Enrollment has expired',
                'start_date' => $startDate,
                'end_date' => $endDate,
                'days_remaining' => 0,
                'days_overdue' => $endDate->diffInDays($today),
                'progress_percentage' => 100,
            ];
        }

        if ($daysRemaining <= 7) {
            $status = 'expiring_soon';
            $message = "Expires in {$daysRemaining} day(s)";
        } elseif ($daysRemaining <= 30) {
            $status = 'expiring_soon';
            $message = "Expires in {$daysRemaining} day(s)";
        } else {
            $status = 'active';
            $message = "{$daysRemaining} day(s) remaining";
        }

        return [
            'status' => $status,
            'message' => $message,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'days_remaining' => $daysRemaining,
            'total_duration_days' => $totalDays,
            'progress_percentage' => $progressPercentage,
        ];
    }
}
