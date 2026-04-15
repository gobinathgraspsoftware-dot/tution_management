<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'class_id',
        'subject_id',
        'grade_level_id',       // ADDED: FK to grade_levels table
        'teacher_id',
        'type',
        'file_path',
        'file_type',
        'file_size',
        'description',
        'access_type',
        'is_approved',
        'approved_by',
        'publish_date',
        'status',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'is_approved' => 'boolean',
        'publish_date' => 'date',
    ];

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * ADDED: Grade level relationship.
     */
    public function gradeLevel()
    {
        return $this->belongsTo(GradeLevel::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function materialAccess()
    {
        return $this->hasMany(MaterialAccess::class);
    }

    public function views()
    {
        return $this->hasMany(MaterialView::class);
    }

    // ==========================================
    // SCOPES
    // ==========================================

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByGradeLevel($query, $gradeLevelId)
    {
        return $query->where('grade_level_id', (int) $gradeLevelId);
    }
}
