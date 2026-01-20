<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_id',
        'session_date',
        'start_time',
        'end_time',
        'topic',
        'notes',
        'status',
    ];

    protected $casts = [
        'session_date' => 'date',
        'start_time' => 'datetime:H:i:s',
        'end_time' => 'datetime:H:i:s',
    ];

    // Relationships
    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    /**
     * Get all attendance records for this session.
     * Alias for attendance() - using plural form for hasMany convention
     */
    public function attendances()
    {
        return $this->hasMany(StudentAttendance::class, 'class_session_id');
    }

    /**
     * Get all attendance records for this session.
     * Original method name - kept for backward compatibility
     */
    public function attendance()
    {
        return $this->hasMany(StudentAttendance::class, 'class_session_id');
    }

    // Scopes
    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('session_date', today());
    }

    public function scopeUpcoming($query)
    {
        return $query->where('session_date', '>=', today())
                     ->where('status', 'scheduled')
                     ->orderBy('session_date')
                     ->orderBy('start_time');
    }
}
