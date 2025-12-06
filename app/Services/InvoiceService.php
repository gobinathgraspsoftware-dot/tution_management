<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\Package;
use App\Models\DiscountUsage;
use App\Models\ReferralVoucher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class InvoiceService
{
    /**
     * Default online payment fee (RM)
     */
    const DEFAULT_ONLINE_FEE = 130.00;

    /**
     * Generate a single invoice for an enrollment
     */
    public function generateInvoice(Enrollment $enrollment, array $options = []): ?Invoice
    {
        // Check if enrollment is active
        if (!$enrollment->isActive()) {
            Log::warning("Cannot generate invoice for inactive enrollment", [
                'enrollment_id' => $enrollment->id,
                'status' => $enrollment->status
            ]);
            return null;
        }

        $billingStart = $options['billing_start'] ?? Carbon::now()->startOfMonth();
        $billingEnd = $options['billing_end'] ?? Carbon::now()->endOfMonth();
        $dueDate = $options['due_date'] ?? $this->calculateDueDate($enrollment);
        $type = $options['type'] ?? 'monthly';

        // Check for existing invoice in the same period
        $existingInvoice = Invoice::where('enrollment_id', $enrollment->id)
            ->where('billing_period_start', $billingStart)
            ->where('billing_period_end', $billingEnd)
            ->whereNotIn('status', ['cancelled', 'refunded'])
            ->first();

        if ($existingInvoice) {
            Log::info("Invoice already exists for this period", [
                'enrollment_id' => $enrollment->id,
                'invoice_id' => $existingInvoice->id
            ]);
            return $existingInvoice;
        }

        try {
            DB::beginTransaction();

            // Calculate amounts
            $subtotal = $enrollment->monthly_fee ?? $enrollment->package->price;
            $onlineFee = $this->calculateOnlineFee($enrollment);
            $discount = $this->calculateDiscount($enrollment, $subtotal + $onlineFee);
            $tax = 0; // No tax for educational services in Malaysia
            $totalAmount = $subtotal + $onlineFee - $discount + $tax;

            // Create invoice
            $invoice = Invoice::create([
                'invoice_number' => Invoice::generateInvoiceNumber(),
                'student_id' => $enrollment->student_id,
                'enrollment_id' => $enrollment->id,
                'type' => $type,
                'billing_period_start' => $billingStart,
                'billing_period_end' => $billingEnd,
                'subtotal' => $subtotal,
                'online_fee' => $onlineFee,
                'discount' => $discount,
                'discount_reason' => $discount > 0 ? $this->getDiscountReason($enrollment) : null,
                'tax' => $tax,
                'total_amount' => $totalAmount,
                'paid_amount' => 0,
                'due_date' => $dueDate,
                'status' => 'pending',
                'reminder_count' => 0,
                'notes' => $options['notes'] ?? null,
            ]);

            // Record discount usage if applicable
            if ($discount > 0) {
                $this->recordDiscountUsage($enrollment, $invoice, $discount);
            }

            DB::commit();

            Log::info("Invoice generated successfully", [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'enrollment_id' => $enrollment->id,
                'total_amount' => $totalAmount
            ]);

            return $invoice;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to generate invoice", [
                'enrollment_id' => $enrollment->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Generate invoices for all active enrollments (bulk generation)
     */
    public function generateMonthlyInvoices(?Carbon $forMonth = null): array
    {
        $forMonth = $forMonth ?? Carbon::now();
        $billingStart = $forMonth->copy()->startOfMonth();
        $billingEnd = $forMonth->copy()->endOfMonth();

        $results = [
            'generated' => 0,
            'skipped' => 0,
            'failed' => 0,
            'invoices' => [],
            'errors' => []
        ];

        // Get all active enrollments that need invoicing
        $enrollments = Enrollment::active()
            ->with(['student', 'package'])
            ->whereHas('student', function($q) {
                $q->where('status', 'approved');
            })
            ->get();

        foreach ($enrollments as $enrollment) {
            // Check if it's time to generate invoice based on payment cycle
            if (!$this->shouldGenerateInvoice($enrollment, $forMonth)) {
                $results['skipped']++;
                continue;
            }

            try {
                $invoice = $this->generateInvoice($enrollment, [
                    'billing_start' => $billingStart,
                    'billing_end' => $billingEnd,
                    'type' => 'monthly'
                ]);

                if ($invoice) {
                    $results['generated']++;
                    $results['invoices'][] = $invoice;
                } else {
                    $results['skipped']++;
                }
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = [
                    'enrollment_id' => $enrollment->id,
                    'error' => $e->getMessage()
                ];
            }
        }

        return $results;
    }

    /**
     * Generate invoices for specific students
     */
    public function generateInvoicesForStudents(array $studentIds, array $options = []): array
    {
        $results = [
            'generated' => 0,
            'failed' => 0,
            'invoices' => [],
            'errors' => []
        ];

        $enrollments = Enrollment::active()
            ->whereIn('student_id', $studentIds)
            ->with(['student', 'package'])
            ->get();

        foreach ($enrollments as $enrollment) {
            try {
                $invoice = $this->generateInvoice($enrollment, $options);
                if ($invoice) {
                    $results['generated']++;
                    $results['invoices'][] = $invoice;
                }
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = [
                    'enrollment_id' => $enrollment->id,
                    'error' => $e->getMessage()
                ];
            }
        }

        return $results;
    }

    /**
     * Check if invoice should be generated for enrollment
     */
    public function shouldGenerateInvoice(Enrollment $enrollment, Carbon $forMonth): bool
    {
        // Get payment cycle day (default: 1st of month)
        $cycleDay = $enrollment->payment_cycle_day ?? 1;
        $today = Carbon::today();

        // Check if we're within 7 days before the cycle day
        $cycleDateThisMonth = Carbon::create(
            $forMonth->year,
            $forMonth->month,
            min($cycleDay, $forMonth->daysInMonth)
        );

        $generateFrom = $cycleDateThisMonth->copy()->subDays(7);

        // Only generate if we're in the generation window
        if ($today->lt($generateFrom)) {
            return false;
        }

        // Check if invoice already exists for this period
        $existingInvoice = Invoice::where('enrollment_id', $enrollment->id)
            ->where('billing_period_start', $forMonth->copy()->startOfMonth())
            ->where('billing_period_end', $forMonth->copy()->endOfMonth())
            ->whereNotIn('status', ['cancelled', 'refunded'])
            ->exists();

        return !$existingInvoice;
    }

    /**
     * Calculate due date based on enrollment payment cycle
     */
    public function calculateDueDate(Enrollment $enrollment): Carbon
    {
        $cycleDay = $enrollment->payment_cycle_day ?? 1;
        $now = Carbon::now();

        // Due date is the cycle day of next month
        $dueDate = Carbon::create(
            $now->month == 12 ? $now->year + 1 : $now->year,
            $now->month == 12 ? 1 : $now->month + 1,
            min($cycleDay, Carbon::create($now->year, $now->month + 1, 1)->daysInMonth)
        );

        return $dueDate;
    }

    /**
     * Calculate online fee if applicable
     */
    protected function calculateOnlineFee(Enrollment $enrollment): float
    {
        $package = $enrollment->package;

        // Only charge online fee for online or hybrid packages
        if (in_array($package->type, ['online', 'hybrid'])) {
            return $package->online_fee ?? self::DEFAULT_ONLINE_FEE;
        }

        return 0;
    }

    /**
     * Calculate applicable discounts
     */
    protected function calculateDiscount(Enrollment $enrollment, float $amount): float
    {
        $totalDiscount = 0;

        // Check for referral vouchers
        $voucher = ReferralVoucher::where('student_id', $enrollment->student_id)
            ->where('status', 'active')
            ->where(function($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->first();

        if ($voucher) {
            $totalDiscount += min($voucher->amount, $amount);
        }

        return $totalDiscount;
    }

    /**
     * Get discount reason
     */
    protected function getDiscountReason(Enrollment $enrollment): ?string
    {
        $reasons = [];

        $voucher = ReferralVoucher::where('student_id', $enrollment->student_id)
            ->where('status', 'active')
            ->first();

        if ($voucher) {
            $reasons[] = 'Referral Voucher Applied';
        }

        return !empty($reasons) ? implode(', ', $reasons) : null;
    }

    /**
     * Record discount usage
     */
    protected function recordDiscountUsage(Enrollment $enrollment, Invoice $invoice, float $amount): void
    {
        // Mark voucher as used if applicable
        $voucher = ReferralVoucher::where('student_id', $enrollment->student_id)
            ->where('status', 'active')
            ->first();

        if ($voucher) {
            $voucher->update([
                'status' => 'used',
                'used_at' => now(),
                'used_on_invoice_id' => $invoice->id
            ]);

            DiscountUsage::create([
                'invoice_id' => $invoice->id,
                'discount_rule_id' => null,
                'voucher_id' => $voucher->id,
                'discount_amount' => min($voucher->amount, $amount),
                'description' => 'Referral Voucher'
            ]);
        }
    }

    /**
     * Update overdue invoices
     */
    public function updateOverdueInvoices(): int
    {
        $count = Invoice::where('status', 'pending')
            ->where('due_date', '<', Carbon::today())
            ->update(['status' => 'overdue']);

        Log::info("Updated {$count} invoices to overdue status");

        return $count;
    }

    /**
     * Get invoice statistics
     */
    public function getInvoiceStatistics(?Carbon $month = null): array
    {
        $month = $month ?? Carbon::now();
        $startOfMonth = $month->copy()->startOfMonth();
        $endOfMonth = $month->copy()->endOfMonth();

        return [
            'total_invoices' => Invoice::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count(),
            'pending_invoices' => Invoice::pending()->count(),
            'overdue_invoices' => Invoice::overdue()->count(),
            'paid_invoices' => Invoice::paid()->whereBetween('updated_at', [$startOfMonth, $endOfMonth])->count(),
            'partial_invoices' => Invoice::partial()->count(),
            'total_invoiced' => Invoice::whereBetween('created_at', [$startOfMonth, $endOfMonth])->sum('total_amount'),
            'total_collected' => Invoice::whereBetween('created_at', [$startOfMonth, $endOfMonth])->sum('paid_amount'),
            'total_outstanding' => Invoice::whereIn('status', ['pending', 'overdue', 'partial'])
                ->selectRaw('SUM(total_amount - paid_amount) as outstanding')
                ->value('outstanding') ?? 0,
        ];
    }

    /**
     * Apply discount to existing invoice
     */
    public function applyDiscount(Invoice $invoice, float $amount, string $reason): Invoice
    {
        $invoice->discount += $amount;
        $invoice->discount_reason = $invoice->discount_reason
            ? $invoice->discount_reason . '; ' . $reason
            : $reason;
        $invoice->total_amount = $invoice->subtotal + $invoice->online_fee - $invoice->discount + $invoice->tax;
        $invoice->save();

        return $invoice;
    }

    /**
     * Cancel an invoice
     */
    public function cancelInvoice(Invoice $invoice, ?string $reason = null): bool
    {
        if ($invoice->paid_amount > 0) {
            throw new \Exception("Cannot cancel invoice with payments. Please refund first.");
        }

        $invoice->update([
            'status' => 'cancelled',
            'notes' => $invoice->notes
                ? $invoice->notes . "\nCancelled: " . ($reason ?? 'No reason provided')
                : "Cancelled: " . ($reason ?? 'No reason provided')
        ]);

        return true;
    }

    /**
     * Generate registration/first invoice for new enrollment
     */
    public function generateRegistrationInvoice(Enrollment $enrollment): Invoice
    {
        $startDate = $enrollment->start_date ?? Carbon::now();

        // Pro-rate if starting mid-month
        $daysInMonth = $startDate->daysInMonth;
        $remainingDays = $daysInMonth - $startDate->day + 1;
        $proRateFactor = $remainingDays / $daysInMonth;

        $package = $enrollment->package;
        $subtotal = round(($enrollment->monthly_fee ?? $package->price) * $proRateFactor, 2);
        $onlineFee = $this->calculateOnlineFee($enrollment);

        return $this->generateInvoice($enrollment, [
            'billing_start' => $startDate,
            'billing_end' => $startDate->copy()->endOfMonth(),
            'due_date' => $startDate->copy()->addDays(7),
            'type' => 'registration',
            'notes' => "Registration invoice (pro-rated from {$startDate->format('d M Y')})"
        ]);
    }
}
