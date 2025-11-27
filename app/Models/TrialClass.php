<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrialClass extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id', 'parent_name', 'parent_phone', 'parent_email',
        'student_name', 'class_id', 'scheduled_date', 'scheduled_time',
        'status', 'feedback', 'conversion_status', 'notes',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'scheduled_time' => 'datetime:H:i:s',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeAttended($query)
    {
        return $query->where('status', 'attended');
    }

    public function scopeConverted($query)
    {
        return $query->where('conversion_status', 'converted');
    }
}
