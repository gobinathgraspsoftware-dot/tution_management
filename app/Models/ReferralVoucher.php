<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferralVoucher extends Model
{
    use HasFactory;

    public $timestamps = false;
    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    protected $fillable = [
        'referral_id', 'student_id', 'voucher_code', 'amount', 'status',
        'used_at', 'used_on_invoice_id', 'expires_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'used_at' => 'datetime',
        'expires_at' => 'date',
        'created_at' => 'datetime',
    ];

    public function referral()
    {
        return $this->belongsTo(Referral::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function usedOnInvoice()
    {
        return $this->belongsTo(Invoice::class, 'used_on_invoice_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                     ->where(function($q) {
                         $q->whereNull('expires_at')
                           ->orWhere('expires_at', '>=', now());
                     });
    }

    public function scopeUsed($query)
    {
        return $query->where('status', 'used');
    }
}
