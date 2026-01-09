<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Expense extends Model
{
    use HasFactory;

    /**
     * Status constants
     */
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_PAID = 'paid';

    /**
     * Payment method constants (as per PDF: Cash, Bank Transfer, Cheque)
     */
    const PAYMENT_METHOD_CASH = 'cash';
    const PAYMENT_METHOD_BANK_TRANSFER = 'bank_transfer';
    const PAYMENT_METHOD_CHEQUE = 'cheque';
    const PAYMENT_METHOD_ONLINE = 'online';

    /**
     * Recurring frequency constants
     */
    const RECURRING_MONTHLY = 'monthly';
    const RECURRING_QUARTERLY = 'quarterly';
    const RECURRING_YEARLY = 'yearly';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'voucher_number',
        'category_id',
        'description',
        'amount',
        'expense_date',
        'payment_method',
        'reference_number',
        'receipt_path',
        'is_recurring',
        'recurring_frequency',
        'status',
        'approved_by',
        'approved_at',
        'rejected_at',
        'rejection_reason',
        'created_by',
        'notes',
        'budget_amount',
        'vendor_name',
        'invoice_number'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'budget_amount' => 'decimal:2',
        'expense_date' => 'date',
        'approved_at' => 'date',
        'rejected_at' => 'date',
        'is_recurring' => 'boolean',
    ];

    // ==========================================
    // BOOT METHOD - Auto-generate voucher number
    // ==========================================

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($expense) {
            if (empty($expense->voucher_number)) {
                $expense->voucher_number = self::generateVoucherNumber();
            }
        });
    }

    // ==========================================
    // VOUCHER NUMBER GENERATION
    // ==========================================

    /**
     * Generate unique voucher number
     * Format: EXP-YYYYMM-XXXX (e.g., EXP-202501-0001)
     */
    public static function generateVoucherNumber(): string
    {
        $prefix = 'EXP';
        $yearMonth = now()->format('Ym');
        
        // Get the last voucher number for this month
        $lastVoucher = static::where('voucher_number', 'like', "{$prefix}-{$yearMonth}-%")
            ->orderBy('voucher_number', 'desc')
            ->first();

        if ($lastVoucher) {
            // Extract the sequence number and increment
            $lastNumber = (int) substr($lastVoucher->voucher_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return sprintf('%s-%s-%04d', $prefix, $yearMonth, $newNumber);
    }

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    /**
     * Get the expense category
     */
    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'category_id');
    }

    /**
     * Get the user who approved this expense
     */
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the user who created this expense
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ==========================================
    // SCOPES
    // ==========================================

    /**
     * Scope to get approved expenses
     */
    public function scopeApproved($query)
    {
        return $query->where('expenses.status', self::STATUS_APPROVED);
    }

    /**
     * Scope to get pending expenses
     */
    public function scopePending($query)
    {
        return $query->where('expenses.status', self::STATUS_PENDING);
    }

    /**
     * Scope to get rejected expenses
     */
    public function scopeRejected($query)
    {
        return $query->where('expenses.status', self::STATUS_REJECTED);
    }

    /**
     * Scope to get paid expenses
     */
    public function scopePaid($query)
    {
        return $query->where('expenses.status', self::STATUS_PAID);
    }

    /**
     * Scope to get recurring expenses
     */
    public function scopeRecurring($query)
    {
        return $query->where('is_recurring', true);
    }

    /**
     * Scope to filter by category
     */
    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope to filter by payment method
     */
    public function scopeByPaymentMethod($query, $method)
    {
        return $query->where('payment_method', $method);
    }

    /**
     * Scope for today's expenses
     */
    public function scopeToday($query)
    {
        return $query->whereDate('expense_date', today());
    }

    /**
     * Scope for this week's expenses
     */
    public function scopeThisWeek($query)
    {
        return $query->whereBetween('expense_date', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    /**
     * Scope for this month's expenses
     */
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('expense_date', now()->month)
                     ->whereYear('expense_date', now()->year);
    }

    /**
     * Scope for this year's expenses
     */
    public function scopeThisYear($query)
    {
        return $query->whereYear('expense_date', now()->year);
    }

    /**
     * Scope to filter by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('expense_date', [$startDate, $endDate]);
    }

    /**
     * Scope to get over budget expenses
     */
    public function scopeOverBudget($query)
    {
        return $query->whereNotNull('budget_amount')
                     ->whereRaw('amount > budget_amount');
    }

    /**
     * Scope to search by voucher, vendor, or description
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('voucher_number', 'like', '%' . $search . '%')
              ->orWhere('vendor_name', 'like', '%' . $search . '%')
              ->orWhere('description', 'like', '%' . $search . '%')
              ->orWhere('reference_number', 'like', '%' . $search . '%')
              ->orWhere('invoice_number', 'like', '%' . $search . '%');
        });
    }

    /**
     * Scope to filter by created user
     */
    public function scopeCreatedByUser($query, $userId)
    {
        return $query->where('created_by', $userId);
    }

    // ==========================================
    // STATUS HELPER METHODS
    // ==========================================

    /**
     * Approve the expense
     */
    public function approve($userId)
    {
        $this->update([
            'status' => self::STATUS_APPROVED,
            'approved_by' => $userId,
            'approved_at' => now(),
        ]);
    }

    /**
     * Reject the expense
     */
    public function reject($userId, $reason)
    {
        $this->update([
            'status' => self::STATUS_REJECTED,
            'approved_by' => $userId,
            'rejected_at' => now(),
            'rejection_reason' => $reason,
        ]);
    }

    /**
     * Mark expense as paid
     */
    public function markAsPaid()
    {
        $this->update(['status' => self::STATUS_PAID]);
    }

    /**
     * Check if expense is approved
     */
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Check if expense is pending
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if expense is rejected
     */
    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /**
     * Check if expense is paid
     */
    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    /**
     * Check if expense can be edited
     * As per PDF: Editable before confirmation (pending status)
     */
    public function canBeEdited(): bool
    {
        return $this->isPending();
    }

    /**
     * Check if expense can be deleted
     */
    public function canBeDeleted(): bool
    {
        return $this->isPending() || $this->isRejected();
    }

    // ==========================================
    // BUDGET HELPER METHODS
    // ==========================================

    /**
     * Check if expense is over budget
     */
    public function isOverBudget(): bool
    {
        return $this->budget_amount && $this->amount > $this->budget_amount;
    }

    /**
     * Get variance amount from budget
     */
    public function getVarianceAmount()
    {
        if (!$this->budget_amount) {
            return 0;
        }
        return $this->amount - $this->budget_amount;
    }

    /**
     * Get variance percentage from budget
     */
    public function getVariancePercentage()
    {
        if (!$this->budget_amount || $this->budget_amount == 0) {
            return 0;
        }
        return (($this->amount - $this->budget_amount) / $this->budget_amount) * 100;
    }

    // ==========================================
    // DISPLAY HELPER METHODS
    // ==========================================

    /**
     * Get status badge class for display
     */
    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            self::STATUS_APPROVED => 'success',
            self::STATUS_PENDING => 'warning',
            self::STATUS_REJECTED => 'danger',
            self::STATUS_PAID => 'info',
            default => 'secondary',
        };
    }

    /**
     * Get status label for display
     */
    public function getStatusLabel(): string
    {
        return self::getStatuses()[$this->status] ?? 'Unknown';
    }

    /**
     * Get payment method label for display
     */
    public function getPaymentMethodLabel(): string
    {
        return self::getPaymentMethods()[$this->payment_method] ?? 'Unknown';
    }

    /**
     * Get formatted amount with currency
     */
    public function getFormattedAmount(): string
    {
        return 'RM ' . number_format($this->amount, 2);
    }

    /**
     * Get formatted expense date
     */
    public function getFormattedDate(): string
    {
        return $this->expense_date ? $this->expense_date->format('d/m/Y') : '-';
    }

    /**
     * Get payee name (alias for vendor_name as per PDF)
     */
    public function getPayee(): string
    {
        return $this->vendor_name ?? '-';
    }

    // ==========================================
    // STATIC HELPER METHODS
    // ==========================================

    /**
     * Get all available statuses
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_PAID => 'Paid',
        ];
    }

    /**
     * Get all available payment methods
     */
    public static function getPaymentMethods(): array
    {
        return [
            self::PAYMENT_METHOD_CASH => 'Cash',
            self::PAYMENT_METHOD_BANK_TRANSFER => 'Bank Transfer',
            self::PAYMENT_METHOD_CHEQUE => 'Cheque',
            self::PAYMENT_METHOD_ONLINE => 'Online',
        ];
    }

    /**
     * Get payment methods as per PDF (without online)
     */
    public static function getVoucherPaymentMethods(): array
    {
        return [
            self::PAYMENT_METHOD_CASH => 'Cash',
            self::PAYMENT_METHOD_BANK_TRANSFER => 'Bank Transfer',
            self::PAYMENT_METHOD_CHEQUE => 'Cheque',
        ];
    }

    /**
     * Get all available recurring frequencies
     */
    public static function getRecurringFrequencies(): array
    {
        return [
            self::RECURRING_MONTHLY => 'Monthly',
            self::RECURRING_QUARTERLY => 'Quarterly',
            self::RECURRING_YEARLY => 'Yearly',
        ];
    }

    // ==========================================
    // REPORTING METHODS
    // ==========================================

    /**
     * Get total expenses by category for a date range
     */
    public static function getTotalByCategory($startDate = null, $endDate = null)
    {
        $query = static::query()
            ->whereIn('status', [self::STATUS_APPROVED, self::STATUS_PAID])
            ->select('category_id', DB::raw('SUM(amount) as total_amount'), DB::raw('COUNT(*) as total_count'))
            ->groupBy('category_id')
            ->with('category');

        if ($startDate && $endDate) {
            $query->dateRange($startDate, $endDate);
        }

        return $query->get();
    }

    /**
     * Get total expenses by payment method for a date range
     */
    public static function getTotalByPaymentMethod($startDate = null, $endDate = null)
    {
        $query = static::query()
            ->whereIn('status', [self::STATUS_APPROVED, self::STATUS_PAID])
            ->select('payment_method', DB::raw('SUM(amount) as total_amount'), DB::raw('COUNT(*) as total_count'))
            ->groupBy('payment_method');

        if ($startDate && $endDate) {
            $query->dateRange($startDate, $endDate);
        }

        return $query->get();
    }

    /**
     * Get expense summary for a date range
     */
    public static function getSummary($startDate = null, $endDate = null)
    {
        $query = static::query()
            ->whereIn('status', [self::STATUS_APPROVED, self::STATUS_PAID]);

        if ($startDate && $endDate) {
            $query->dateRange($startDate, $endDate);
        }

        return [
            'total_amount' => $query->sum('amount'),
            'total_count' => $query->count(),
            'average_amount' => $query->avg('amount') ?? 0,
        ];
    }

    /**
     * Get monthly expense trend for current year
     */
    public static function getMonthlyTrend($year = null)
    {
        $year = $year ?? now()->year;

        return static::query()
            ->whereIn('status', [self::STATUS_APPROVED, self::STATUS_PAID])
            ->whereYear('expense_date', $year)
            ->select(
                DB::raw('MONTH(expense_date) as month'),
                DB::raw('SUM(amount) as total_amount'),
                DB::raw('COUNT(*) as total_count')
            )
            ->groupBy(DB::raw('MONTH(expense_date)'))
            ->orderBy('month')
            ->get();
    }
}
