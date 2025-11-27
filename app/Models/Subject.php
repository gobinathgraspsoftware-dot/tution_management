<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subject extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'description',
        'grade_levels',
        'status',
    ];

    protected $casts = [
        'grade_levels' => 'array',
    ];

    // Relationships
    public function classes()
    {
        return $this->hasMany(ClassModel::class);
    }

    public function packages()
    {
        return $this->belongsToMany(Package::class, 'package_subjects')
                    ->withPivot('sessions_per_month')
                    ->withTimestamps();
    }

    public function materials()
    {
        return $this->hasMany(Material::class);
    }

    public function exams()
    {
        return $this->hasMany(Exam::class);
    }

    public function physicalMaterials()
    {
        return $this->hasMany(PhysicalMaterial::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByGradeLevel($query, $gradeLevel)
    {
        return $query->whereJsonContains('grade_levels', $gradeLevel);
    }
}
