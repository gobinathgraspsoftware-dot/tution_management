<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnrollmentFeeHistory extends Model
{
    use HasFactory;

    protected $table = 'enrollment_fee_history';

    protected $fillable = [
        'enrollment_id',
        'package_id',
        'old_fee',
        'new_fee',
        'monthly_fee',
        'online_fee',
        'effective_from',
        'effective_until',
        'change_date',
        'reason',
        'notes',
        'created_by',
        'changed_by',
    ];

    protected $casts = [
        'old_fee' => 'decimal:2',
        'new_fee' => 'decimal:2',
        'monthly_fee' => 'decimal:2',
        'online_fee' => 'decimal:2',
        'effective_from' => 'date',
        'effective_until' => 'date',
        'change_date' => 'date',
    ];

    // Relationships
    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    // Scopes
    public function scopeForEnrollment($query, $enrollmentId)
    {
        return $query->where('enrollment_id', $enrollmentId);
    }

    public function scopeRecent($query, $limit = 10)
    {
        return $query->orderBy('change_date', 'desc')
                     ->orOrderBy('created_at', 'desc')
                     ->limit($limit);
    }
}
