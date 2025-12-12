<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_PAID = 'paid';

    const PAYMENT_METHOD_CASH = 'cash';
    const PAYMENT_METHOD_BANK_TRANSFER = 'bank_transfer';
    const PAYMENT_METHOD_CHEQUE = 'cheque';
    const PAYMENT_METHOD_ONLINE = 'online';

    const RECURRING_MONTHLY = 'monthly';
    const RECURRING_QUARTERLY = 'quarterly';
    const RECURRING_YEARLY = 'yearly';

    protected $fillable = [
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

    protected $casts = [
        'amount' => 'decimal:2',
        'budget_amount' => 'decimal:2',
        'expense_date' => 'date',
        'approved_at' => 'date',
        'rejected_at' => 'date',
        'is_recurring' => 'boolean',
    ];

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'category_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ==========================================
    // SCOPES
    // ==========================================

    public function scopeApproved($query)
    {
        return $query->where('expenses.status', self::STATUS_APPROVED);
    }

    public function scopePending($query)
    {
        return $query->where('expenses.status', self::STATUS_PENDING);
    }

    public function scopeRejected($query)
    {
        return $query->where('expenses.status', self::STATUS_REJECTED);
    }

    public function scopePaid($query)
    {
        return $query->where('expenses.status', self::STATUS_PAID);
    }

    public function scopeRecurring($query)
    {
        return $query->where('is_recurring', true);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeByPaymentMethod($query, $method)
    {
        return $query->where('payment_method', $method);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('expense_date', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('expense_date', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('expense_date', now()->month)
                     ->whereYear('expense_date', now()->year);
    }

    public function scopeThisYear($query)
    {
        return $query->whereYear('expense_date', now()->year);
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('expense_date', [$startDate, $endDate]);
    }

    public function scopeOverBudget($query)
    {
        return $query->whereNotNull('budget_amount')
                     ->whereRaw('amount > budget_amount');
    }

    // ==========================================
    // HELPER METHODS
    // ==========================================

    public function approve($userId)
    {
        $this->update([
            'status' => self::STATUS_APPROVED,
            'approved_by' => $userId,
            'approved_at' => now(),
        ]);
    }

    public function reject($userId, $reason)
    {
        $this->update([
            'status' => self::STATUS_REJECTED,
            'approved_by' => $userId,
            'rejected_at' => now(),
            'rejection_reason' => $reason,
        ]);
    }

    public function markAsPaid()
    {
        $this->update(['status' => self::STATUS_PAID]);
    }

    public function isApproved()
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isRejected()
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function isOverBudget()
    {
        return $this->budget_amount && $this->amount > $this->budget_amount;
    }

    public function getVarianceAmount()
    {
        if (!$this->budget_amount) {
            return 0;
        }
        return $this->amount - $this->budget_amount;
    }

    public function getVariancePercentage()
    {
        if (!$this->budget_amount || $this->budget_amount == 0) {
            return 0;
        }
        return (($this->amount - $this->budget_amount) / $this->budget_amount) * 100;
    }

    public function getStatusBadgeClass()
    {
        return match($this->status) {
            self::STATUS_APPROVED => 'success',
            self::STATUS_PENDING => 'warning',
            self::STATUS_REJECTED => 'danger',
            self::STATUS_PAID => 'info',
            default => 'secondary',
        };
    }

    public static function getStatuses()
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_PAID => 'Paid',
        ];
    }

    public static function getPaymentMethods()
    {
        return [
            self::PAYMENT_METHOD_CASH => 'Cash',
            self::PAYMENT_METHOD_BANK_TRANSFER => 'Bank Transfer',
            self::PAYMENT_METHOD_CHEQUE => 'Cheque',
            self::PAYMENT_METHOD_ONLINE => 'Online',
        ];
    }

    public static function getRecurringFrequencies()
    {
        return [
            self::RECURRING_MONTHLY => 'Monthly',
            self::RECURRING_QUARTERLY => 'Quarterly',
            self::RECURRING_YEARLY => 'Yearly',
        ];
    }
}
