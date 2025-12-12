<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Installment extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id', 'installment_number', 'amount', 'due_date',
        'paid_amount', 'status', 'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_date' => 'date',
        'paid_at' => 'datetime',
        'installment_number' => 'integer',
    ];

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    // ==========================================
    // SCOPES
    // ==========================================

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePartial($query)
    {
        return $query->where('status', 'partial');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue')
                     ->orWhere(function($q) {
                         $q->where('status', 'pending')
                           ->where('due_date', '<', now());
                     });
    }

    /**
     * Scope for unpaid installments (pending, partial, or overdue)
     * CRITICAL: Required by ArrearsService
     */
    public function scopeUnpaid($query)
    {
        return $query->whereIn('status', ['pending', 'partial', 'overdue']);
    }

    public function scopeDueWithin($query, $days)
    {
        return $query->whereBetween('due_date', [
            now(),
            now()->addDays($days)
        ]);
    }

    public function scopeDueToday($query)
    {
        return $query->whereDate('due_date', today());
    }

    // ==========================================
    // ACCESSORS
    // ==========================================

    /**
     * Get the remaining balance
     */
    public function getBalanceAttribute()
    {
        return max(0, $this->amount - $this->paid_amount);
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

    // ==========================================
    // HELPER METHODS
    // ==========================================

    /**
     * Check if installment is paid
     */
    public function isPaid(): bool
    {
        return $this->status === 'paid' || $this->paid_amount >= $this->amount;
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
     * Check if installment is partially paid
     */
    public function isPartial(): bool
    {
        return $this->status === 'partial' ||
               ($this->paid_amount > 0 && $this->paid_amount < $this->amount);
    }

    /**
     * Record a payment against this installment
     */
    public function recordPayment($amount): void
    {
        $this->paid_amount += $amount;

        if ($this->paid_amount >= $this->amount) {
            $this->status = 'paid';
            $this->paid_at = now();
        } elseif ($this->paid_amount > 0) {
            $this->status = 'partial';
        }

        $this->save();
    }

    /**
     * Mark installment as overdue
     */
    public function markAsOverdue(): void
    {
        if ($this->isOverdue() && $this->status !== 'overdue') {
            $this->update(['status' => 'overdue']);
        }
    }
}
