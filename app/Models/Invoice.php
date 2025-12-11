<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'invoice_number',
        'student_id',
        'enrollment_id',
        'type',
        'billing_period_start',
        'billing_period_end',
        'subtotal',
        'online_fee',
        'discount',
        'discount_reason',
        'tax',
        'total_amount',
        'paid_amount',
        'due_date',
        'status',
        'reminder_count',
        'last_reminder_at',
        'notes',
    ];

    protected $casts = [
        'billing_period_start' => 'date',
        'billing_period_end' => 'date',
        'subtotal' => 'decimal:2',
        'online_fee' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_date' => 'date',
        'reminder_count' => 'integer',
        'last_reminder_at' => 'datetime',
    ];

    /**
     * Invoice types
     */
    const TYPE_MONTHLY = 'monthly';
    const TYPE_REGISTRATION = 'registration';
    const TYPE_MATERIAL = 'material';
    const TYPE_EXAM = 'exam';
    const TYPE_RENEWAL = 'renewal';
    const TYPE_OTHER = 'other';

    /**
     * Invoice statuses
     */
    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING = 'pending';
    const STATUS_PARTIAL = 'partial';
    const STATUS_PAID = 'paid';
    const STATUS_OVERDUE = 'overdue';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Get all invoice types
     */
    public static function getTypes(): array
    {
        return [
            self::TYPE_MONTHLY => 'Monthly Fee',
            self::TYPE_REGISTRATION => 'Registration Fee',
            self::TYPE_MATERIAL => 'Material Fee',
            self::TYPE_EXAM => 'Exam Fee',
            self::TYPE_RENEWAL => 'Renewal Fee',
            self::TYPE_OTHER => 'Other',
        ];
    }

    /**
     * Get all invoice statuses
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
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

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function installments()
    {
        return $this->hasMany(Installment::class);
    }

    public function reminders()
    {
        return $this->hasMany(PaymentReminder::class);
    }

    public function discountUsage()
    {
        return $this->hasMany(DiscountUsage::class);
    }

    public function gatewayTransactions()
    {
        return $this->hasMany(PaymentGatewayTransaction::class);
    }

    // ==========================================
    // SCOPES
    // ==========================================

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopePaid($query)
    {
        return $query->where('status', self::STATUS_PAID);
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', self::STATUS_OVERDUE)
                     ->orWhere(function($q) {
                         $q->where('status', self::STATUS_PENDING)
                           ->where('due_date', '<', now());
                     });
    }

    public function scopePartial($query)
    {
        return $query->where('status', self::STATUS_PARTIAL);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', self::STATUS_CANCELLED);
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

    public function scopeOverdueDays($query, $days)
    {
        return $query->whereIn('status', [self::STATUS_PENDING, self::STATUS_PARTIAL, self::STATUS_OVERDUE])
                     ->where('due_date', '<', now()->subDays($days));
    }

    public function scopeForMonth($query, $month = null, $year = null)
    {
        $month = $month ?? now()->month;
        $year = $year ?? now()->year;

        return $query->whereMonth('billing_period_start', $month)
                     ->whereYear('billing_period_start', $year);
    }

    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeForEnrollment($query, $enrollmentId)
    {
        return $query->where('enrollment_id', $enrollmentId);
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeMonthly($query)
    {
        return $query->where('type', self::TYPE_MONTHLY);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
                     ->whereYear('created_at', now()->year);
    }

    public function scopeThisYear($query)
    {
        return $query->whereYear('created_at', now()->year);
    }

    public function scopeCreatedBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    public function scopeNeedsReminder($query)
    {
        return $query->whereIn('status', [self::STATUS_PENDING, self::STATUS_PARTIAL, self::STATUS_OVERDUE])
                     ->where('due_date', '<=', now())
                     ->where(function($q) {
                         $q->whereNull('last_reminder_at')
                           ->orWhere('last_reminder_at', '<', now()->subDays(7));
                     });
    }

    // ==========================================
    // ACCESSORS & MUTATORS
    // ==========================================

    /**
     * Get the outstanding balance
     */
    public function getBalanceAttribute()
    {
        return max(0, $this->total_amount - $this->paid_amount);
    }

    /**
     * Get days overdue
     */
    public function getDaysOverdueAttribute()
    {
        if (!$this->due_date || !$this->isOverdue()) {
            return 0;
        }
        return $this->due_date->diffInDays(now());
    }

    /**
     * Get payment percentage
     */
    public function getPaymentPercentageAttribute()
    {
        if ($this->total_amount <= 0) {
            return 0;
        }
        return round(($this->paid_amount / $this->total_amount) * 100, 1);
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
            self::STATUS_DRAFT => 'secondary',
            self::STATUS_CANCELLED => 'dark',
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

    /**
     * Get type label
     */
    public function getTypeLabelAttribute(): string
    {
        return self::getTypes()[$this->type] ?? ucfirst($this->type);
    }

    /**
     * Get billing period formatted
     */
    public function getBillingPeriodAttribute(): string
    {
        if (!$this->billing_period_start || !$this->billing_period_end) {
            return 'N/A';
        }
        return $this->billing_period_start->format('d M') . ' - ' . $this->billing_period_end->format('d M Y');
    }

    // ==========================================
    // HELPER METHODS
    // ==========================================

    /**
     * Check if invoice is paid
     */
    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID ||
               $this->paid_amount >= $this->total_amount;
    }

    /**
     * Check if invoice is overdue
     */
    public function isOverdue(): bool
    {
        return !$this->isPaid() &&
               $this->due_date &&
               $this->due_date->isPast();
    }

    /**
     * Check if invoice is draft
     */
    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    /**
     * Check if invoice is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Check if invoice is partial paid
     */
    public function isPartial(): bool
    {
        return $this->status === self::STATUS_PARTIAL ||
               ($this->paid_amount > 0 && $this->paid_amount < $this->total_amount);
    }

    /**
     * Check if invoice is editable
     */
    public function isEditable(): bool
    {
        return !in_array($this->status, [self::STATUS_PAID, self::STATUS_CANCELLED]);
    }

    /**
     * Check if invoice can receive payment
     */
    public function canReceivePayment(): bool
    {
        return !in_array($this->status, [self::STATUS_PAID, self::STATUS_CANCELLED, self::STATUS_DRAFT]);
    }

    /**
     * Record a payment against this invoice
     */
    public function recordPayment($amount): void
    {
        $this->paid_amount += $amount;

        if ($this->paid_amount >= $this->total_amount) {
            $this->status = self::STATUS_PAID;
        } elseif ($this->paid_amount > 0) {
            $this->status = self::STATUS_PARTIAL;
        }

        $this->save();
    }

    /**
     * Send reminder and update tracking
     */
    public function sendReminder(): void
    {
        $this->increment('reminder_count');
        $this->update(['last_reminder_at' => now()]);
    }

    /**
     * Mark invoice as overdue
     */
    public function markAsOverdue(): void
    {
        if ($this->isOverdue() && $this->status !== self::STATUS_OVERDUE) {
            $this->update(['status' => self::STATUS_OVERDUE]);
        }
    }

    /**
     * Cancel invoice
     */
    public function cancel(): void
    {
        $this->update(['status' => self::STATUS_CANCELLED]);
    }

    /**
     * Publish draft invoice
     */
    public function publish(): void
    {
        if ($this->isDraft()) {
            $this->update(['status' => self::STATUS_PENDING]);
        }
    }

    /**
     * Calculate totals
     */
    public function calculateTotal(): float
    {
        $total = $this->subtotal + $this->online_fee - $this->discount;

        if ($this->tax > 0) {
            $total += ($total * ($this->tax / 100));
        }

        return max(0, $total);
    }

    /**
     * Recalculate and save total
     */
    public function recalculateTotal(): void
    {
        $this->total_amount = $this->calculateTotal();
        $this->save();
    }

    /**
     * Check if billing period exists for enrollment
     */
    public static function existsForPeriod($enrollmentId, $startDate, $endDate): bool
    {
        return static::where('enrollment_id', $enrollmentId)
                     ->where('billing_period_start', $startDate)
                     ->where('billing_period_end', $endDate)
                     ->whereNotIn('status', [self::STATUS_CANCELLED])
                     ->exists();
    }

    /**
     * Generate unique invoice number
     */
    public static function generateInvoiceNumber(): string
    {
        $prefix = 'INV';
        $year = date('Y');
        $month = date('m');

        $lastInvoice = static::whereYear('created_at', $year)
                            ->whereMonth('created_at', $month)
                            ->orderBy('id', 'desc')
                            ->first();

        if ($lastInvoice && preg_match('/INV(\d{6})(\d{4})/', $lastInvoice->invoice_number, $matches)) {
            $sequence = intval($matches[2]) + 1;
        } else {
            $sequence = 1;
        }

        return $prefix . $year . $month . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get invoice summary for a period
     */
    public static function getSummary($startDate = null, $endDate = null): array
    {
        $query = static::query();

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        return [
            'total_invoiced' => (clone $query)->sum('total_amount'),
            'total_collected' => (clone $query)->sum('paid_amount'),
            'total_outstanding' => (clone $query)->unpaid()->sum(\DB::raw('total_amount - paid_amount')),
            'invoice_count' => (clone $query)->count(),
            'paid_count' => (clone $query)->paid()->count(),
            'pending_count' => (clone $query)->pending()->count(),
            'overdue_count' => (clone $query)->overdue()->count(),
            'partial_count' => (clone $query)->partial()->count(),
        ];
    }

    /**
     * Get collection rate for a period
     */
    public static function getCollectionRate($startDate = null, $endDate = null): float
    {
        $query = static::query()->whereNotIn('status', [self::STATUS_DRAFT, self::STATUS_CANCELLED]);

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        $totalInvoiced = $query->sum('total_amount');
        $totalCollected = $query->sum('paid_amount');

        if ($totalInvoiced <= 0) {
            return 0;
        }

        return round(($totalCollected / $totalInvoiced) * 100, 1);
    }
}
