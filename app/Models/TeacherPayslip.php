<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherPayslip extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'payslip_number',
        'period_start',
        'period_end',
        'total_hours',
        'total_classes',
        'basic_pay',
        'allowances',
        'deductions',
        'epf_employee',
        'epf_employer',
        'socso_employee',
        'socso_employer',
        'net_pay',
        'payment_date',
        'payment_method',
        'reference_number',
        'status',
        'notes',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'total_hours' => 'decimal:2',
        'total_classes' => 'integer',
        'basic_pay' => 'decimal:2',
        'allowances' => 'decimal:2',
        'deductions' => 'decimal:2',
        'epf_employee' => 'decimal:2',
        'epf_employer' => 'decimal:2',
        'socso_employee' => 'decimal:2',
        'socso_employer' => 'decimal:2',
        'net_pay' => 'decimal:2',
        'payment_date' => 'date',
    ];

    // Relationships
    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    // Scopes
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    // Helpers
    public static function generatePayslipNumber()
    {
        $lastPayslip = static::latest('id')->first();
        $number = $lastPayslip ? intval(substr($lastPayslip->payslip_number, 3)) + 1 : 1;
        
        return 'PSL' . str_pad($number, 6, '0', STR_PAD_LEFT);
    }
}
