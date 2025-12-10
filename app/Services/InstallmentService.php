<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Installment;
use App\Models\Payment;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class InstallmentService
{
    /**
     * Create installment plan for an invoice
     */
    public function createInstallmentPlan(Invoice $invoice, array $options): array
    {
        if ($invoice->isPaid()) {
            throw new \Exception('Cannot create installment plan for a paid invoice.');
        }

        if ($invoice->is_installment && $invoice->installments()->count() > 0) {
            throw new \Exception('Invoice already has an installment plan.');
        }

        $numberOfInstallments = $options['number_of_installments'] ?? 3;
        $startDate = isset($options['start_date'])
            ? Carbon::parse($options['start_date'])
            : Carbon::now();
        $intervalDays = $options['interval_days'] ?? 30;
        $customAmounts = $options['custom_amounts'] ?? null;

        // Validate number of installments
        if ($numberOfInstallments < 2 || $numberOfInstallments > 12) {
            throw new \Exception('Number of installments must be between 2 and 12.');
        }

        try {
            DB::beginTransaction();

            // Calculate balance to be split
            $balance = $invoice->balance;

            if ($balance <= 0) {
                throw new \Exception('Invoice has no outstanding balance.');
            }

            $installments = [];

            if ($customAmounts && count($customAmounts) === $numberOfInstallments) {
                // Use custom amounts
                $totalCustom = array_sum($customAmounts);
                if (abs($totalCustom - $balance) > 0.01) {
                    throw new \Exception('Custom amounts must equal the invoice balance.');
                }

                foreach ($customAmounts as $index => $amount) {
                    $dueDate = $startDate->copy()->addDays($intervalDays * $index);

                    $installment = Installment::create([
                        'invoice_id' => $invoice->id,
                        'installment_number' => $index + 1,
                        'amount' => $amount,
                        'due_date' => $dueDate,
                        'paid_amount' => 0,
                        'status' => 'pending',
                        'reminder_count' => 0,
                    ]);

                    $installments[] = $installment;
                }
            } else {
                // Equal installments
                $installmentAmount = round($balance / $numberOfInstallments, 2);
                $remainder = $balance - ($installmentAmount * $numberOfInstallments);

                for ($i = 0; $i < $numberOfInstallments; $i++) {
                    $amount = $installmentAmount;

                    // Add remainder to last installment
                    if ($i === $numberOfInstallments - 1) {
                        $amount += $remainder;
                    }

                    $dueDate = $startDate->copy()->addDays($intervalDays * $i);

                    $installment = Installment::create([
                        'invoice_id' => $invoice->id,
                        'installment_number' => $i + 1,
                        'amount' => $amount,
                        'due_date' => $dueDate,
                        'paid_amount' => 0,
                        'status' => 'pending',
                        'reminder_count' => 0,
                    ]);

                    $installments[] = $installment;
                }
            }

            // Update invoice to mark as installment plan
            $invoice->update([
                'is_installment' => true,
                'installment_count' => $numberOfInstallments,
                'installment_notes' => $options['notes'] ?? null,
            ]);

            DB::commit();

            Log::info('Installment plan created', [
                'invoice_id' => $invoice->id,
                'installments' => count($installments),
                'total_amount' => $balance
            ]);

            return [
                'success' => true,
                'invoice' => $invoice->fresh(['installments']),
                'installments' => $installments,
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create installment plan', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Record payment for specific installment
     */
    public function payInstallment(Installment $installment, float $amount, ?int $paymentId = null): Installment
    {
        if ($installment->isPaid()) {
            throw new \Exception('This installment is already paid.');
        }

        if ($amount <= 0) {
            throw new \Exception('Payment amount must be greater than zero.');
        }

        $maxAmount = $installment->balance;
        if ($amount > $maxAmount) {
            throw new \Exception("Payment amount cannot exceed outstanding balance of RM{$maxAmount}.");
        }

        $installment->recordPayment($amount, $paymentId);

        Log::info('Installment payment recorded', [
            'installment_id' => $installment->id,
            'amount' => $amount,
            'payment_id' => $paymentId
        ]);

        return $installment->fresh();
    }

    /**
     * Auto-allocate payment to installments (FIFO - oldest first)
     */
    public function autoAllocatePayment(Invoice $invoice, float $amount, int $paymentId): array
    {
        $allocated = [];
        $remaining = $amount;

        // Get unpaid installments ordered by due date
        $installments = $invoice->installments()
            ->unpaid()
            ->orderBy('due_date', 'asc')
            ->orderBy('installment_number', 'asc')
            ->get();

        foreach ($installments as $installment) {
            if ($remaining <= 0) break;

            $toAllocate = min($remaining, $installment->balance);

            if ($toAllocate > 0) {
                $installment->recordPayment($toAllocate, $paymentId);

                $allocated[] = [
                    'installment_id' => $installment->id,
                    'installment_number' => $installment->installment_number,
                    'amount_allocated' => $toAllocate,
                ];

                $remaining -= $toAllocate;
            }
        }

        return [
            'allocated' => $allocated,
            'total_allocated' => $amount - $remaining,
            'remaining' => $remaining,
        ];
    }

    /**
     * Cancel installment plan
     */
    public function cancelInstallmentPlan(Invoice $invoice): bool
    {
        if (!$invoice->is_installment) {
            throw new \Exception('Invoice does not have an installment plan.');
        }

        // Check if any installment has payment
        $hasPayments = $invoice->installments()
            ->where('paid_amount', '>', 0)
            ->exists();

        if ($hasPayments) {
            throw new \Exception('Cannot cancel installment plan with payments. Please process refund first.');
        }

        try {
            DB::beginTransaction();

            // Cancel all pending installments
            $invoice->installments()
                ->where('status', 'pending')
                ->update(['status' => 'cancelled']);

            // Update invoice
            $invoice->update([
                'is_installment' => false,
                'installment_count' => 0,
                'installment_notes' => $invoice->installment_notes . "\n[Cancelled on " . now()->format('d M Y') . "]",
            ]);

            DB::commit();

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Modify installment plan
     */
    public function modifyInstallment(Installment $installment, array $data): Installment
    {
        if ($installment->isPaid()) {
            throw new \Exception('Cannot modify a paid installment.');
        }

        $updateData = [];

        if (isset($data['amount'])) {
            if ($data['amount'] < $installment->paid_amount) {
                throw new \Exception('Amount cannot be less than already paid amount.');
            }
            $updateData['amount'] = $data['amount'];
        }

        if (isset($data['due_date'])) {
            $updateData['due_date'] = Carbon::parse($data['due_date']);
        }

        if (isset($data['notes'])) {
            $updateData['notes'] = $data['notes'];
        }

        $installment->update($updateData);

        return $installment->fresh();
    }

    /**
     * Get installment plan summary
     */
    public function getInstallmentSummary(Invoice $invoice): array
    {
        if (!$invoice->is_installment) {
            return [];
        }

        $installments = $invoice->installments()->orderBy('installment_number')->get();

        $summary = [
            'total_installments' => $installments->count(),
            'paid_installments' => $installments->where('status', 'paid')->count(),
            'pending_installments' => $installments->whereIn('status', ['pending', 'partial'])->count(),
            'overdue_installments' => $installments->filter(fn($i) => $i->isOverdue())->count(),
            'total_amount' => $installments->sum('amount'),
            'total_paid' => $installments->sum('paid_amount'),
            'total_balance' => $installments->sum('balance'),
            'next_due' => $installments->where('status', '!=', 'paid')
                ->where('status', '!=', 'cancelled')
                ->sortBy('due_date')
                ->first(),
            'completion_percentage' => 0,
        ];

        if ($summary['total_amount'] > 0) {
            $summary['completion_percentage'] = round(
                ($summary['total_paid'] / $summary['total_amount']) * 100,
                1
            );
        }

        return $summary;
    }

    /**
     * Update overdue installments
     */
    public function updateOverdueInstallments(): int
    {
        $count = Installment::whereIn('status', ['pending', 'partial'])
            ->where('due_date', '<', Carbon::today())
            ->update(['status' => 'overdue']);

        Log::info("Updated {$count} installments to overdue status");

        return $count;
    }

    /**
     * Get all installments needing reminders
     */
    public function getInstallmentsNeedingReminders(): \Illuminate\Database\Eloquent\Collection
    {
        return Installment::with(['invoice.student.user', 'invoice.student.parent.user'])
            ->unpaid()
            ->where('due_date', '<=', now()->addDays(7))
            ->where(function($q) {
                $q->whereNull('last_reminder_at')
                  ->orWhere('last_reminder_at', '<', now()->subDays(3));
            })
            ->get();
    }

    /**
     * Get installment statistics
     */
    public function getStatistics(): array
    {
        return [
            'total_active_plans' => Invoice::where('is_installment', true)
                ->whereIn('status', ['pending', 'partial', 'overdue'])
                ->count(),
            'total_installments_pending' => Installment::pending()->count(),
            'total_installments_overdue' => Installment::overdue()->count(),
            'total_amount_due' => Installment::unpaid()->sum(DB::raw('amount - paid_amount')),
            'installments_due_this_week' => Installment::dueWithin(7)->count(),
            'installments_due_today' => Installment::dueToday()->count(),
        ];
    }

    /**
     * Get student's installment history
     */
    public function getStudentInstallmentHistory(Student $student): array
    {
        $invoices = Invoice::where('student_id', $student->id)
            ->where('is_installment', true)
            ->with('installments')
            ->orderBy('created_at', 'desc')
            ->get();

        $history = [];
        foreach ($invoices as $invoice) {
            $history[] = [
                'invoice' => $invoice,
                'summary' => $this->getInstallmentSummary($invoice),
            ];
        }

        return $history;
    }
}
