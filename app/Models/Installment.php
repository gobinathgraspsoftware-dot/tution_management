<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Installment extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'payment_id',
        'installment_number',
        'amount',
        'due_date',
        'paid_amount',
        'status',
        'paid_at',
        'notes',
        'reminder_count',
        'last_reminder_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_date' => 'date',
        'paid_at' => 'datetime',
        'last_reminder_at' => 'datetime',
        'installment_number' => 'integer',
        'reminder_count' => 'integer',
    ];

    /**
     * Installment statuses
     */
    const STATUS_PENDING = 'pending';
    const STATUS_PARTIAL = 'partial';
    const STATUS_PAID = 'paid';
    const STATUS_OVERDUE = 'overdue';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Get all statuses
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_PARTIAL => 'Partial',
            self::STATUS_PAID => 'Paid',
            self::STATUS_OVERDUE => 'Overdue',
            self::STATUS_CANCELLED => 'Cancelled',
        ];
    }

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function reminders()
    {
        return $this->hasMany(PaymentReminder::class);
    }

    // ==========================================
    // SCOPES
    // ==========================================

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopePartial($query)
    {
        return $query->where('status', self::STATUS_PARTIAL);
    }

    public function scopePaid($query)
    {
        return $query->where('status', self::STATUS_PAID);
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', self::STATUS_OVERDUE)
                     ->orWhere(function($q) {
                         $q->whereIn('status', [self::STATUS_PENDING, self::STATUS_PARTIAL])
                           ->where('due_date', '<', now());
                     });
    }

    public function scopeUnpaid($query)
    {
        return $query->whereIn('status', [
            self::STATUS_PENDING,
            self::STATUS_PARTIAL,
            self::STATUS_OVERDUE
        ]);
    }

    public function scopeDueWithin($query, $days = 7)
    {
        return $query->whereIn('status', [self::STATUS_PENDING, self::STATUS_PARTIAL])
                     ->whereBetween('due_date', [now(), now()->addDays($days)]);
    }

    public function scopeDueToday($query)
    {
        return $query->whereIn('status', [self::STATUS_PENDING, self::STATUS_PARTIAL])
                     ->whereDate('due_date', today());
    }

    public function scopeDueThisMonth($query)
    {
        return $query->whereMonth('due_date', now()->month)
                     ->whereYear('due_date', now()->year);
    }

    public function scopeForInvoice($query, $invoiceId)
    {
        return $query->where('invoice_id', $invoiceId);
    }

    public function scopeNeedsReminder($query)
    {
        return $query->whereIn('status', [self::STATUS_PENDING, self::STATUS_PARTIAL, self::STATUS_OVERDUE])
                     ->where('due_date', '<=', now()->addDays(7))
                     ->where(function($q) {
                         $q->whereNull('last_reminder_at')
                           ->orWhere('last_reminder_at', '<', now()->subDays(3));
                     });
    }

    // ==========================================
    // ACCESSORS
    // ==========================================

    /**
     * Get remaining balance
     */
    public function getBalanceAttribute(): float
    {
        return max(0, $this->amount - $this->paid_amount);
    }

    /**
     * Get days until due (negative if overdue)
     */
    public function getDaysUntilDueAttribute(): int
    {
        if (!$this->due_date) {
            return 0;
        }
        return now()->startOfDay()->diffInDays($this->due_date, false);
    }

    /**
     * Get days overdue
     */
    public function getDaysOverdueAttribute(): int
    {
        if (!$this->due_date || !$this->isOverdue()) {
            return 0;
        }
        return $this->due_date->diffInDays(now());
    }

    /**
     * Get payment percentage
     */
    public function getPaymentPercentageAttribute(): float
    {
        if ($this->amount <= 0) {
            return 0;
        }
        return round(($this->paid_amount / $this->amount) * 100, 1);
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PAID => 'success',
            self::STATUS_PARTIAL => 'info',
            self::STATUS_PENDING => 'warning',
            self::STATUS_OVERDUE => 'danger',
            self::STATUS_CANCELLED => 'secondary',
            default => 'secondary',
        };
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return self::getStatuses()[$this->status] ?? ucfirst($this->status);
    }

    // ==========================================
    // HELPER METHODS
    // ==========================================

    /**
     * Check if installment is paid
     */
    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID ||
               $this->paid_amount >= $this->amount;
    }

    /**
     * Check if installment is overdue
     */
    public function isOverdue(): bool
    {
        return !$this->isPaid() &&
               $this->due_date &&
               $this->due_date->isPast();
    }

    /**
     * Check if installment is partial paid
     */
    public function isPartial(): bool
    {
        return $this->paid_amount > 0 && $this->paid_amount < $this->amount;
    }

    /**
     * Record payment against this installment
     */
    public function recordPayment(float $amount, ?int $paymentId = null): void
    {
        $this->paid_amount += $amount;

        if ($paymentId) {
            $this->payment_id = $paymentId;
        }

        if ($this->paid_amount >= $this->amount) {
            $this->status = self::STATUS_PAID;
            $this->paid_at = now();
        } elseif ($this->paid_amount > 0) {
            $this->status = self::STATUS_PARTIAL;
        }

        $this->save();
    }

    /**
     * Mark as overdue
     */
    public function markAsOverdue(): void
    {
        if ($this->isOverdue() && !in_array($this->status, [self::STATUS_PAID, self::STATUS_CANCELLED])) {
            $this->update(['status' => self::STATUS_OVERDUE]);
        }
    }

    /**
     * Cancel installment
     */
    public function cancel(): void
    {
        $this->update(['status' => self::STATUS_CANCELLED]);
    }

    /**
     * Record reminder sent
     */
    public function recordReminderSent(): void
    {
        $this->increment('reminder_count');
        $this->update(['last_reminder_at' => now()]);
    }

    /**
     * Get student through invoice
     */
    public function getStudentAttribute()
    {
        return $this->invoice?->student;
    }

    /**
     * Check if can receive payment
     */
    public function canReceivePayment(): bool
    {
        return !in_array($this->status, [self::STATUS_PAID, self::STATUS_CANCELLED]);
    }

    /**
     * Get summary for installment plan
     */
    public static function getPlanSummary(int $invoiceId): array
    {
        $installments = static::where('invoice_id', $invoiceId)->get();

        return [
            'total_installments' => $installments->count(),
            'paid_installments' => $installments->where('status', self::STATUS_PAID)->count(),
            'pending_installments' => $installments->where('status', self::STATUS_PENDING)->count(),
            'overdue_installments' => $installments->where('status', self::STATUS_OVERDUE)->count(),
            'total_amount' => $installments->sum('amount'),
            'paid_amount' => $installments->sum('paid_amount'),
            'outstanding_amount' => $installments->sum('amount') - $installments->sum('paid_amount'),
            'next_due' => $installments->whereIn('status', [self::STATUS_PENDING, self::STATUS_PARTIAL])
                                       ->sortBy('due_date')
                                       ->first(),
        ];
    }
}
