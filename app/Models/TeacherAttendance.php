<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'date',
        'status',
        'time_in',
        'time_out',
        'hours_worked',
        'remarks',
        'marked_by',
    ];

    protected $casts = [
        'date' => 'date',
        'time_in' => 'datetime:H:i:s',
        'time_out' => 'datetime:H:i:s',
        'hours_worked' => 'decimal:2',
    ];

    // Relationships
    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
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

    public function scopeHalfDay($query)
    {
        return $query->where('status', 'half_day');
    }

    public function scopeLeave($query)
    {
        return $query->where('status', 'leave');
    }

    public function scopeForDate($query, $date)
    {
        return $query->whereDate('date', $date);
    }
}
