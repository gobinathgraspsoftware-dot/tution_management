<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Referral extends Model
{
    use HasFactory;

    protected $fillable = [
        'referrer_student_id', 'referred_student_id', 'referral_code',
        'status', 'completed_at',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    public function referrer()
    {
        return $this->belongsTo(Student::class, 'referrer_student_id');
    }

    public function referred()
    {
        return $this->belongsTo(Student::class, 'referred_student_id');
    }

    public function vouchers()
    {
        return $this->hasMany(ReferralVoucher::class);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
