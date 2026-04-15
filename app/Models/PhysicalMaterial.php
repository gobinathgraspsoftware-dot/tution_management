<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhysicalMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'subject_id',
        'grade_level_id',       // CHANGED: was 'grade_level' (varchar) → now FK to grade_levels
        'class_id',             // ADDED: FK to classes table
        'month',
        'year',
        'description',
        'quantity_total',
        'quantity_available',
        'minimum_quantity',
        'status',
    ];

    protected $casts = [
        'year' => 'integer',
        'quantity_total' => 'integer',
        'quantity_available' => 'integer',
        'minimum_quantity' => 'integer',
    ];

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * ADDED: Grade level relationship (FK → grade_levels.id).
     */
    public function gradeLevel()
    {
        return $this->belongsTo(GradeLevel::class);
    }

    /**
     * ADDED: Class relationship (FK → classes.id).
     */
    public function classModel()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function collections()
    {
        return $this->hasMany(PhysicalMaterialCollection::class);
    }

    // ==========================================
    // SCOPES
    // ==========================================

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function scopeByGradeLevel($query, $gradeLevelId)
    {
        return $query->where('grade_level_id', (int) $gradeLevelId);
    }

    public function scopeByClass($query, $classId)
    {
        return $query->where('class_id', (int) $classId);
    }
}
