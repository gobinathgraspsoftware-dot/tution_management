<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Package extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'description',
        'type',
        'duration_months',
        'price',
        'online_fee',
        'includes_materials',
        'max_students',
        'features',
        'status',
    ];

    protected $casts = [
        'duration_months' => 'integer',
        'price' => 'decimal:2',
        'online_fee' => 'decimal:2',
        'includes_materials' => 'boolean',
        'max_students' => 'integer',
        'features' => 'array',
    ];

    // Relationships
    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'package_subjects')
                    ->withPivot('sessions_per_month')
                    ->withTimestamps();
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function enrollmentFeeHistory()
    {
        return $this->hasMany(EnrollmentFeeHistory::class);
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

    public function scopeHybrid($query)
    {
        return $query->where('type', 'hybrid');
    }

    // Helpers
    public function getTotalPriceAttribute()
    {
        $total = $this->price;
        
        if ($this->type === 'online') {
            $total += $this->online_fee;
        }
        
        return $total;
    }
}
