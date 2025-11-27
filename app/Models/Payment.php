<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_number',
        'invoice_id',
        'student_id',
        'amount',
        'payment_method',
        'payment_date',
        'reference_number',
        'gateway_transaction_id',
        'gateway_response',
        'screenshot_path',
        'status',
        'processed_by',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'datetime',
        'gateway_response' => 'array',
    ];

    // Relationships
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function gatewayTransaction()
    {
        return $this->hasOne(PaymentGatewayTransaction::class);
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeRefunded($query)
    {
        return $query->where('status', 'refunded');
    }

    public function scopeByCash($query)
    {
        return $query->where('payment_method', 'cash');
    }

    public function scopeByQR($query)
    {
        return $query->where('payment_method', 'qr');
    }

    public function scopeByOnline($query)
    {
        return $query->where('payment_method', 'online_gateway');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('payment_date', today());
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('payment_date', now()->month)
                     ->whereYear('payment_date', now()->year);
    }

    // Helpers
    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public static function generatePaymentNumber()
    {
        $lastPayment = static::latest('id')->first();
        $number = $lastPayment ? intval(substr($lastPayment->payment_number, 3)) + 1 : 1;
        
        return 'PAY' . str_pad($number, 6, '0', STR_PAD_LEFT);
    }
}
