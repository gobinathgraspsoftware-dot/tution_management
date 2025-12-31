<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocsoInsurance extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     * This table is for SOCSO Insurance Only scheme (for employees above 60 years old or foreign workers)
     *
     * @var string
     */
    protected $table = 'socso_insurance';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'wage_limit',
        'from_amount',
        'to_amount',
        'employee_contribution',
        'employer_contribution',
        'total_contribution',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'from_amount' => 'decimal:2',
        'to_amount' => 'decimal:2',
        'employee_contribution' => 'decimal:2',
        'employer_contribution' => 'decimal:2',
        'total_contribution' => 'decimal:2',
        'status' => 'integer',
    ];

    /**
     * Scope a query to only include active records.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Get SOCSO Insurance contribution based on salary amount.
     *
     * @param float $salary
     * @return SocsoInsurance|null
     */
    public static function getContributionBySalary($salary)
    {
        return self::active()
            ->where('from_amount', '<=', $salary)
            ->where('to_amount', '>=', $salary)
            ->first();
    }

    /**
     * Calculate SOCSO Insurance contributions for a given salary.
     *
     * @param float $salary
     * @return array|null
     */
    public static function calculateContribution($salary)
    {
        $rate = self::getContributionBySalary($salary);

        if (!$rate) {
            return null;
        }

        return [
            'employee_contribution' => $rate->employee_contribution,
            'employer_contribution' => $rate->employer_contribution,
            'total_contribution' => $rate->total_contribution,
        ];
    }
}
