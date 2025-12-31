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
        // NEW: Statutory contribution flags
        'epf_enabled',
        'socso_enabled',
        'socso_type',
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
        'specialization' => 'array',
        // NEW: Cast boolean flags
        'epf_enabled' => 'boolean',
        'socso_enabled' => 'boolean',
    ];

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

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

    // ==========================================
    // SCOPES
    // ==========================================

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

    public function scopeEpfEnabled($query)
    {
        return $query->where('epf_enabled', true);
    }

    public function scopeSocsoEnabled($query)
    {
        return $query->where('socso_enabled', true);
    }

    // ==========================================
    // ACCESSORS
    // ==========================================

    public function getFullNameAttribute()
    {
        return $this->user->name;
    }

    /**
     * Get IC number with formatting (XXXXXX-XX-XXXX)
     */
    public function getFormattedIcNumberAttribute()
    {
        if (empty($this->ic_number)) {
            return null;
        }

        $cleaned = preg_replace('/[^0-9]/', '', $this->ic_number);

        if (strlen($cleaned) === 12) {
            return substr($cleaned, 0, 6) . '-' . substr($cleaned, 6, 2) . '-' . substr($cleaned, 8, 4);
        }

        return $this->ic_number;
    }

    /**
     * Get specialization as subject names
     */
    public function getSpecializationNamesAttribute()
    {
        if (empty($this->specialization)) {
            return [];
        }

        if (is_array($this->specialization)) {
            return \App\Models\Subject::whereIn('id', $this->specialization)
                ->pluck('name')
                ->toArray();
        }

        return [];
    }

    /**
     * Check if teacher requires SOCSO Insurance Only scheme
     * (for employees 60+ years old or foreign workers)
     */
    public function getUsesSocsoInsuranceOnlyAttribute(): bool
    {
        return $this->socso_type === 'insurance_only';
    }

    /**
     * Get statutory contribution status summary
     */
    public function getStatutoryStatusAttribute(): array
    {
        return [
            'epf' => $this->epf_enabled,
            'socso' => $this->socso_enabled,
            'socso_type' => $this->socso_type ?? 'regular',
        ];
    }

    // ==========================================
    // MUTATORS
    // ==========================================

    /**
     * Set IC number - remove hyphens, store only digits
     */
    public function setIcNumberAttribute($value)
    {
        if (empty($value)) {
            $this->attributes['ic_number'] = null;
        } else {
            $this->attributes['ic_number'] = preg_replace('/[^0-9]/', '', $value);
        }
    }

    // ==========================================
    // HELPER METHODS
    // ==========================================

    /**
     * Check if EPF deduction should be applied
     */
    public function shouldDeductEpf(): bool
    {
        return $this->epf_enabled && !empty($this->epf_number);
    }

    /**
     * Check if SOCSO deduction should be applied
     */
    public function shouldDeductSocso(): bool
    {
        return $this->socso_enabled && !empty($this->socso_number);
    }

    /**
     * Get the SOCSO calculation model to use based on socso_type
     */
    public function getSocsoModel(): string
    {
        return $this->socso_type === 'insurance_only' 
            ? SocsoInsurance::class 
            : Socso::class;
    }

    /**
     * Calculate salary for given month/year
     * @deprecated Use TeacherSalaryService instead
     */
    public function calculateSalary($month, $year)
    {
        return 0;
    }
}
