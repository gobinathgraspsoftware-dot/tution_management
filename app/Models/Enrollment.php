<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Enrollment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id',
        'package_id',
        'class_id',
        'enrollment_date',
        'start_date',
        'end_date',
        'payment_cycle_day',
        'monthly_fee',
        'status',
        'cancellation_reason',
        'cancelled_at',
    ];

    protected $casts = [
        'enrollment_date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
        'payment_cycle_day' => 'integer',
        'monthly_fee' => 'decimal:2',
        'cancelled_at' => 'datetime',
    ];

    // Relationships
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function feeHistory()
    {
        return $this->hasMany(EnrollmentFeeHistory::class);
    }

    public function materialAccess()
    {
        return $this->hasMany(MaterialAccess::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeSuspended($query)
    {
        return $query->where('status', 'suspended');
    }

    public function scopeTrial($query)
    {
        return $query->where('status', 'trial');
    }

    public function scopeExpiringWithin($query, $days = 7)
    {
        return $query->where('status', 'active')
                     ->whereNotNull('end_date')
                     ->whereBetween('end_date', [now(), now()->addDays($days)]);
    }

    // Helpers
    public function isActive()
    {
        return $this->status === 'active';
    }

    public function isExpired()
    {
        return $this->status === 'expired' || 
               ($this->end_date && $this->end_date->isPast());
    }

    public function getDaysRemainingAttribute()
    {
        if (!$this->end_date) {
            return null;
        }

        return max(0, now()->diffInDays($this->end_date, false));
    }

    public function cancel($reason = null)
    {
        $this->update([
            'status' => 'cancelled',
            'cancellation_reason' => $reason,
            'cancelled_at' => now(),
        ]);
    }

    public function renew($months = null)
    {
        $months = $months ?? $this->package->duration_months;
        
        $this->update([
            'end_date' => $this->end_date 
                ? $this->end_date->addMonths($months) 
                : now()->addMonths($months),
            'status' => 'active',
        ]);
    }
}
