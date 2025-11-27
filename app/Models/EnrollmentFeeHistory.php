<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnrollmentFeeHistory extends Model
{
    use HasFactory;

    protected $table = 'enrollment_fee_history';

    protected $fillable = [
        'enrollment_id', 'package_id', 'monthly_fee', 'online_fee',
        'effective_from', 'effective_until', 'reason', 'notes', 'created_by',
    ];

    protected $casts = [
        'monthly_fee' => 'decimal:2',
        'online_fee' => 'decimal:2',
        'effective_from' => 'date',
        'effective_until' => 'date',
    ];

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
}
