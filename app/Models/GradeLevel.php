<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GradeLevel extends Model
{
    use HasFactory;

    protected $table = 'grade_levels';

    protected $fillable = [
        'name',
    ];

    // ==========================================
    // SCOPES
    // ==========================================

    /**
     * Order alphabetically / by natural insertion order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('id');
    }

    // ==========================================
    // RELATIONSHIPS (extend as needed)
    // ==========================================

    /**
     * If you later link students to grade_levels via FK,
     * add: public function students() { return $this->hasMany(Student::class); }
     */
}
