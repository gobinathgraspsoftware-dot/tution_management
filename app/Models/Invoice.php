<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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

    // Relationships
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

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
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

    public function scopePartial($query)
    {
        return $query->where('status', 'partial');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeDueWithin($query, $days = 7)
    {
        return $query->whereIn('status', ['pending', 'partial'])
                     ->whereBetween('due_date', [now(), now()->addDays($days)]);
    }

    // Helpers
    public function getBalanceAttribute()
    {
        return max(0, $this->total_amount - $this->paid_amount);
    }

    public function isPaid()
    {
        return $this->status === 'paid' || 
               $this->paid_amount >= $this->total_amount;
    }

    public function isOverdue()
    {
        return !$this->isPaid() && 
               $this->due_date && 
               $this->due_date->isPast();
    }

    public function isDraft()
    {
        return $this->status === 'draft';
    }

    public function recordPayment($amount)
    {
        $this->paid_amount += $amount;
        
        if ($this->paid_amount >= $this->total_amount) {
            $this->status = 'paid';
        } elseif ($this->paid_amount > 0) {
            $this->status = 'partial';
        }
        
        $this->save();
    }

    public function sendReminder()
    {
        $this->increment('reminder_count');
        $this->update(['last_reminder_at' => now()]);
    }

    public static function generateInvoiceNumber()
    {
        $lastInvoice = static::latest('id')->first();
        $number = $lastInvoice ? intval(substr($lastInvoice->invoice_number, 3)) + 1 : 1;
        
        return 'INV' . str_pad($number, 6, '0', STR_PAD_LEFT);
    }
}
