<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_id', 'student_id', 'marks_obtained', 'percentage', 'grade',
        'rank', 'remarks', 'is_published', 'published_at',
    ];

    protected $casts = [
        'marks_obtained' => 'decimal:2',
        'percentage' => 'decimal:2',
        'rank' => 'integer',
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }
}
