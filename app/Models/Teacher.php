<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Teacher extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'teacher_id',
        'ic_number',
        'address',
        'qualification',
        'experience_years',
        'specialization',
        'bio',
        'join_date',
        'employment_type',
        'pay_type',
        'hourly_rate',
        'monthly_salary',
        'per_class_rate',
        'bank_name',
        'bank_account',
        'epf_number',
        'socso_number',
        'documents',
        'status',
    ];

    protected $casts = [
        'join_date' => 'date',
        'hourly_rate' => 'decimal:2',
        'monthly_salary' => 'decimal:2',
        'per_class_rate' => 'decimal:2',
        'experience_years' => 'integer',
        'documents' => 'array',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function classes()
    {
        return $this->hasMany(ClassModel::class);
    }

    public function materials()
    {
        return $this->hasMany(Material::class);
    }

    public function attendance()
    {
        return $this->hasMany(TeacherAttendance::class);
    }

    public function payslips()
    {
        return $this->hasMany(TeacherPayslip::class);
    }

    public function reviews()
    {
        return $this->hasMany(StudentReview::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeFullTime($query)
    {
        return $query->where('employment_type', 'full_time');
    }

    public function scopePartTime($query)
    {
        return $query->where('employment_type', 'part_time');
    }

    public function scopeContract($query)
    {
        return $query->where('employment_type', 'contract');
    }

    // Helpers
    public function getFullNameAttribute()
    {
        return $this->user->name;
    }

    public function calculateSalary($month, $year)
    {
        // This would contain salary calculation logic
        // Based on pay_type: hourly, monthly, or per_class
        return 0;
    }
}
