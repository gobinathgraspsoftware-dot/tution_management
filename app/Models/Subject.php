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

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

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

    // ==========================================
    // SCOPES
    // ==========================================

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /*
    |----------------------------------------------------------------------
    | CHANGED: scopeByGradeLevel now casts to integer
    |----------------------------------------------------------------------
    | BEFORE: whereJsonContains('grade_levels', $gradeLevel)
    |         → searched for string "Standard 1" inside JSON
    |
    | AFTER:  whereJsonContains('grade_levels', (int) $gradeLevelId)
    |         → searches for integer 1 inside JSON [1, 9, 12]
    |
    | Usage:  Subject::byGradeLevel(3)->get()
    |----------------------------------------------------------------------
    */
    public function scopeByGradeLevel($query, $gradeLevelId)
    {
        return $query->whereJsonContains('grade_levels', (int) $gradeLevelId);
    }

    // ==========================================
    // ACCESSORS (GRADE LEVEL HELPERS)
    // ==========================================

    /*
    |----------------------------------------------------------------------
    | NEW: Resolve stored grade level IDs to their display names
    |----------------------------------------------------------------------
    | The grade_levels column now stores [1, 9, 12] (IDs).
    | This accessor returns ['Standard 1', 'Form 3', 'Pre-University'].
    |
    | Usage in blade:
    |   @foreach($subject->grade_level_names as $name)
    |       <span class="badge">{{ $name }}</span>
    |   @endforeach
    |
    | Usage in code:
    |   $subject->grade_level_names  → ['Standard 1', 'Form 3']
    |----------------------------------------------------------------------
    */
    public function getGradeLevelNamesAttribute(): array
    {
        $ids = $this->grade_levels ?? [];

        if (empty($ids)) {
            return [];
        }

        return GradeLevel::whereIn('id', $ids)
            ->ordered()
            ->pluck('name')
            ->toArray();
    }

    /*
    |----------------------------------------------------------------------
    | NEW: Get GradeLevel model instances for this subject
    |----------------------------------------------------------------------
    | Returns a Collection of GradeLevel models matching stored IDs.
    |
    | Usage:  $subject->gradeLevelModels()  → Collection of GradeLevel
    |----------------------------------------------------------------------
    */
    public function gradeLevelModels()
    {
        $ids = $this->grade_levels ?? [];

        if (empty($ids)) {
            return collect();
        }

        return GradeLevel::whereIn('id', $ids)->ordered()->get();
    }
}
