<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'class_id',
        'subject_id',
        'exam_date',
        'start_time',
        'duration_minutes',
        'max_marks',
        'passing_marks',
        'description',
        'status',
    ];

    protected $casts = [
        'exam_date' => 'date',
        'start_time' => 'datetime:H:i:s',
        'duration_minutes' => 'integer',
        'max_marks' => 'decimal:2',
        'passing_marks' => 'decimal:2',
    ];

    // Relationships
    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function results()
    {
        return $this->hasMany(ExamResult::class);
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

    public function scopeUpcoming($query)
    {
        return $query->where('exam_date', '>=', today())
                     ->where('status', 'scheduled');
    }
}
