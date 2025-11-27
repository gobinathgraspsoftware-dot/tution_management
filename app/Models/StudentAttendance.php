<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentAttendance extends Model
{
    use HasFactory;

    protected $table = 'student_attendance';

    protected $fillable = [
        'class_session_id',
        'student_id',
        'status',
        'check_in_time',
        'remarks',
        'marked_by',
        'parent_notified',
        'notified_at',
    ];

    protected $casts = [
        'check_in_time' => 'datetime:H:i:s',
        'parent_notified' => 'boolean',
        'notified_at' => 'datetime',
    ];

    // Relationships
    public function classSession()
    {
        return $this->belongsTo(ClassSession::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function markedBy()
    {
        return $this->belongsTo(User::class, 'marked_by');
    }

    // Scopes
    public function scopePresent($query)
    {
        return $query->where('status', 'present');
    }

    public function scopeAbsent($query)
    {
        return $query->where('status', 'absent');
    }

    public function scopeLate($query)
    {
        return $query->where('status', 'late');
    }

    public function scopeExcused($query)
    {
        return $query->where('status', 'excused');
    }

    public function scopeNotified($query)
    {
        return $query->where('parent_notified', true);
    }
}
