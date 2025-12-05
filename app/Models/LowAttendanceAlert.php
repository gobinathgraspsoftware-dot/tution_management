<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LowAttendanceAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'class_id',
        'attendance_percentage',
        'threshold',
        'alert_message',
        'status',
        'notified_by',
        'notified_at',
        'parent_response',
        'responded_at',
    ];

    protected $casts = [
        'attendance_percentage' => 'decimal:2',
        'threshold' => 'decimal:2',
        'notified_at' => 'datetime',
        'responded_at' => 'datetime',
    ];

    // Relationships
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function notifiedBy()
    {
        return $this->belongsTo(User::class, 'notified_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopeAcknowledged($query)
    {
        return $query->where('status', 'acknowledged');
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
                     ->whereYear('created_at', now()->year);
    }

    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeForClass($query, $classId)
    {
        return $query->where('class_id', $classId);
    }

    // Helper methods
    public function markAsSent()
    {
        $this->update([
            'status' => 'sent',
            'notified_at' => now(),
        ]);
    }

    public function markAsAcknowledged($response = null)
    {
        $this->update([
            'status' => 'acknowledged',
            'parent_response' => $response,
            'responded_at' => now(),
        ]);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'pending' => 'warning',
            'sent' => 'info',
            'acknowledged' => 'success',
            'escalated' => 'danger',
            default => 'secondary',
        };
    }

    public function getAttendanceStatusAttribute(): string
    {
        if ($this->attendance_percentage >= 90) {
            return 'excellent';
        } elseif ($this->attendance_percentage >= 75) {
            return 'good';
        } elseif ($this->attendance_percentage >= 60) {
            return 'warning';
        } else {
            return 'critical';
        }
    }
}
