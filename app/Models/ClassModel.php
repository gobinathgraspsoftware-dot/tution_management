<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClassModel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'classes';

    protected $fillable = [
        'name',
        'code',
        'subject_id',
        'teacher_id',
        'type',
        'grade_level',
        'capacity',
        'current_enrollment',
        'description',
        'location',
        'meeting_link',
        'status',
    ];

    protected $casts = [
        'capacity' => 'integer',
        'current_enrollment' => 'integer',
    ];

    // Relationships
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function schedules()
    {
        return $this->hasMany(ClassSchedule::class, 'class_id');
    }

    public function sessions()
    {
        return $this->hasMany(ClassSession::class, 'class_id');
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'class_id');
    }

    public function materials()
    {
        return $this->hasMany(Material::class, 'class_id');
    }

    public function exams()
    {
        return $this->hasMany(Exam::class, 'class_id');
    }

    public function reviews()
    {
        return $this->hasMany(StudentReview::class, 'class_id');
    }

    public function trialClasses()
    {
        return $this->hasMany(TrialClass::class, 'class_id');
    }

    public function announcements()
    {
        return $this->hasMany(Announcement::class, 'target_class_id');
    }

    public function attendanceSummary()
    {
        return $this->hasMany(ClassAttendanceSummary::class, 'class_id');
    }

    public function materialAccess()
    {
        return $this->hasMany(MaterialAccess::class, 'class_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeOnline($query)
    {
        return $query->where('type', 'online');
    }

    public function scopeOffline($query)
    {
        return $query->where('type', 'offline');
    }

    public function scopeFull($query)
    {
        return $query->where('status', 'full');
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'active')
                     ->whereColumn('current_enrollment', '<', 'capacity');
    }

    // Helpers
    public function isFull()
    {
        return $this->current_enrollment >= $this->capacity;
    }

    public function hasSpace()
    {
        return $this->current_enrollment < $this->capacity;
    }

    public function getAvailableSeatsAttribute()
    {
        return max(0, $this->capacity - $this->current_enrollment);
    }

    public function incrementEnrollment()
    {
        $this->increment('current_enrollment');
        
        if ($this->isFull()) {
            $this->update(['status' => 'full']);
        }
    }

    public function decrementEnrollment()
    {
        $this->decrement('current_enrollment');
        
        if ($this->status === 'full' && $this->hasSpace()) {
            $this->update(['status' => 'active']);
        }
    }
}
