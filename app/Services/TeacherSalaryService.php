<?php

namespace App\Services;

use App\Models\Teacher;
use App\Models\TeacherAttendance;
use App\Models\ClassSession;
use App\Models\Epf;
use App\Models\Socso;
use App\Models\SocsoInsurance;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TeacherSalaryService
{
    /**
     * Calculate salary for a teacher for a given period
     * 
     * @param Teacher $teacher
     * @param string $periodStart
     * @param string $periodEnd
     * @param array|null $overrideFlags Optional flags to override teacher's settings
     *                                  ['epf_enabled' => bool, 'socso_enabled' => bool, 'socso_type' => string]
     * @return array
     */
    public function calculateSalary(Teacher $teacher, string $periodStart, string $periodEnd, ?array $overrideFlags = null): array
    {
        $startDate = Carbon::parse($periodStart);
        $endDate = Carbon::parse($periodEnd);

        // Get base calculation based on pay type
        $calculation = match ($teacher->pay_type) {
            'hourly' => $this->calculateHourlySalary($teacher, $startDate, $endDate),
            'monthly' => $this->calculateMonthlySalary($teacher, $startDate, $endDate, $overrideFlags),
            'per_class' => $this->calculatePerClassSalary($teacher, $startDate, $endDate),
            default => $this->getEmptyCalculation(),
        };

        return $calculation;
    }

    /**
     * Calculate hourly based salary
     * Note: Hourly/per-class teachers typically don't have EPF/SOCSO deductions
     */
    protected function calculateHourlySalary(Teacher $teacher, Carbon $startDate, Carbon $endDate): array
    {
        $attendanceHours = TeacherAttendance::where('teacher_id', $teacher->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->whereIn('status', ['present', 'half_day'])
            ->sum('hours_worked');

        $classHours = $this->calculateClassHours($teacher, $startDate, $endDate);
        $totalHours = max($attendanceHours, $classHours);
        $totalClasses = $this->getCompletedClassesCount($teacher, $startDate, $endDate);

        $basicPay = $totalHours * $teacher->hourly_rate;

        return [
            'basic_pay' => round($basicPay, 2),
            'total_hours' => round($totalHours, 2),
            'total_classes' => $totalClasses,
            'hourly_rate' => $teacher->hourly_rate,
            'allowances' => 0,
            'deductions' => 0,
            'epf_employee' => 0,
            'epf_employer' => 0,
            'socso_employee' => 0,
            'socso_employer' => 0,
            'epf_enabled' => false,
            'socso_enabled' => false,
            'socso_type' => null,
        ];
    }

    /**
     * Calculate monthly salary with optional EPF/SOCSO
     */
    protected function calculateMonthlySalary(Teacher $teacher, Carbon $startDate, Carbon $endDate, ?array $overrideFlags = null): array
    {
        $basicPay = $teacher->monthly_salary;

        // Get attendance records
        $attendanceRecords = TeacherAttendance::where('teacher_id', $teacher->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        $totalDays = $attendanceRecords->count();
        $presentDays = $attendanceRecords->whereIn('status', ['present', 'half_day'])->count();
        $absentDays = $attendanceRecords->where('status', 'absent')->count();

        // Calculate deductions for absences
        $workingDaysInMonth = $this->getWorkingDaysInPeriod($startDate, $endDate);
        $perDayRate = $workingDaysInMonth > 0 ? $basicPay / $workingDaysInMonth : 0;
        $absenceDeduction = $absentDays * $perDayRate;

        // Determine EPF/SOCSO flags (override takes precedence)
        $epfEnabled = $overrideFlags['epf_enabled'] ?? $teacher->epf_enabled ?? true;
        $socsoEnabled = $overrideFlags['socso_enabled'] ?? $teacher->socso_enabled ?? true;
        $socsoType = $overrideFlags['socso_type'] ?? $teacher->socso_type ?? 'regular';

        // Calculate statutory contributions
        $epfContributions = $this->calculateEpfContributions($basicPay, $epfEnabled);
        $socsoContributions = $this->calculateSocsoContributions($basicPay, $socsoEnabled, $socsoType);

        $totalClasses = $this->getCompletedClassesCount($teacher, $startDate, $endDate);
        $totalHours = $this->calculateClassHours($teacher, $startDate, $endDate);

        return [
            'basic_pay' => round($basicPay, 2),
            'total_hours' => round($totalHours, 2),
            'total_classes' => $totalClasses,
            'allowances' => 0,
            'deductions' => round($absenceDeduction, 2),
            'epf_employee' => round($epfContributions['employee'], 2),
            'epf_employer' => round($epfContributions['employer'], 2),
            'socso_employee' => round($socsoContributions['employee'], 2),
            'socso_employer' => round($socsoContributions['employer'], 2),
            'working_days' => $workingDaysInMonth,
            'present_days' => $presentDays,
            'absent_days' => $absentDays,
            // Include flags in output for transparency
            'epf_enabled' => $epfEnabled,
            'socso_enabled' => $socsoEnabled,
            'socso_type' => $socsoType,
        ];
    }

    /**
     * Calculate per class based salary
     * Note: Per-class teachers typically don't have EPF/SOCSO deductions
     */
    protected function calculatePerClassSalary(Teacher $teacher, Carbon $startDate, Carbon $endDate): array
    {
        $totalClasses = $this->getCompletedClassesCount($teacher, $startDate, $endDate);
        $totalHours = $this->calculateClassHours($teacher, $startDate, $endDate);
        
        $basicPay = $totalClasses * $teacher->per_class_rate;

        return [
            'basic_pay' => round($basicPay, 2),
            'total_hours' => round($totalHours, 2),
            'total_classes' => $totalClasses,
            'per_class_rate' => $teacher->per_class_rate,
            'allowances' => 0,
            'deductions' => 0,
            'epf_employee' => 0,
            'epf_employer' => 0,
            'socso_employee' => 0,
            'socso_employer' => 0,
            'epf_enabled' => false,
            'socso_enabled' => false,
            'socso_type' => null,
        ];
    }

    /**
     * Calculate EPF contributions using the EPF table
     * 
     * @param float $salary Basic salary amount
     * @param bool $enabled Whether EPF is enabled for this calculation
     * @return array ['employee' => float, 'employer' => float]
     */
    protected function calculateEpfContributions(float $salary, bool $enabled): array
    {
        // If EPF is disabled, return zeros immediately without any database query
        if (!$enabled) {
            return ['employee' => 0, 'employer' => 0];
        }

        try {
            // Use the Epf model's static method to get contribution
            $contribution = Epf::calculateContribution($salary);
            
            if ($contribution) {
                return [
                    'employee' => (float) $contribution['employee_contribution'],
                    'employer' => (float) $contribution['employer_contribution'],
                ];
            }

            // Fallback to percentage-based calculation if no matching bracket found
            // Standard Malaysian EPF rates: Employee 11%, Employer 13%
            Log::info("No EPF bracket found for salary {$salary}, using percentage fallback");
            return [
                'employee' => $salary * 0.11,
                'employer' => $salary * 0.13,
            ];

        } catch (\Exception $e) {
            Log::error("EPF calculation error: " . $e->getMessage());
            // Return percentage-based fallback on error
            return [
                'employee' => $salary * 0.11,
                'employer' => $salary * 0.13,
            ];
        }
    }

    /**
     * Calculate SOCSO contributions using the appropriate SOCSO table
     * 
     * @param float $salary Basic salary amount
     * @param bool $enabled Whether SOCSO is enabled for this calculation
     * @param string $type 'regular' or 'insurance_only'
     * @return array ['employee' => float, 'employer' => float]
     */
    protected function calculateSocsoContributions(float $salary, bool $enabled, string $type = 'regular'): array
    {
        // If SOCSO is disabled, return zeros immediately without any database query
        if (!$enabled) {
            return ['employee' => 0, 'employer' => 0];
        }

        try {
            // Use the appropriate model based on type
            $contribution = $type === 'insurance_only'
                ? SocsoInsurance::calculateContribution($salary)
                : Socso::calculateContribution($salary);

            if ($contribution) {
                return [
                    'employee' => (float) $contribution['employee_contribution'],
                    'employer' => (float) $contribution['employer_contribution'],
                ];
            }

            // Fallback to simplified bracket calculation if no matching bracket found
            Log::info("No SOCSO bracket found for salary {$salary}, using simplified fallback");
            return $this->calculateSocsoFallback($salary);

        } catch (\Exception $e) {
            Log::error("SOCSO calculation error: " . $e->getMessage());
            return $this->calculateSocsoFallback($salary);
        }
    }

    /**
     * Fallback SOCSO calculation when table lookup fails
     * Based on simplified Malaysian SOCSO rates
     */
    protected function calculateSocsoFallback(float $salary): array
    {
        if ($salary <= 1000) {
            return ['employee' => 4.10, 'employer' => 9.50];
        } elseif ($salary <= 2000) {
            return ['employee' => 8.70, 'employer' => 20.15];
        } elseif ($salary <= 3000) {
            return ['employee' => 13.30, 'employer' => 30.80];
        } elseif ($salary <= 4000) {
            return ['employee' => 17.90, 'employer' => 41.45];
        } else {
            return ['employee' => 19.75, 'employer' => 45.75];
        }
    }

    /**
     * Calculate total hours from class sessions
     */
    protected function calculateClassHours(Teacher $teacher, Carbon $startDate, Carbon $endDate): float
    {
        $sessions = ClassSession::whereHas('class', function ($q) use ($teacher) {
            $q->where('teacher_id', $teacher->id);
        })
        ->whereBetween('session_date', [$startDate, $endDate])
        ->where('status', 'completed')
        ->get();

        $totalMinutes = 0;
        foreach ($sessions as $session) {
            if ($session->start_time && $session->end_time) {
                $start = Carbon::parse($session->start_time);
                $end = Carbon::parse($session->end_time);
                $totalMinutes += $end->diffInMinutes($start);
            }
        }

        return $totalMinutes / 60;
    }

    /**
     * Get completed classes count
     */
    protected function getCompletedClassesCount(Teacher $teacher, Carbon $startDate, Carbon $endDate): int
    {
        return ClassSession::whereHas('class', function ($q) use ($teacher) {
            $q->where('teacher_id', $teacher->id);
        })
        ->whereBetween('session_date', [$startDate, $endDate])
        ->where('status', 'completed')
        ->count();
    }

    /**
     * Get working days in period (excluding weekends)
     */
    protected function getWorkingDaysInPeriod(Carbon $startDate, Carbon $endDate): int
    {
        $workingDays = 0;
        $current = $startDate->copy();

        while ($current->lte($endDate)) {
            if ($current->isWeekday()) {
                $workingDays++;
            }
            $current->addDay();
        }

        return $workingDays;
    }

    /**
     * Calculate net pay
     */
    public function calculateNetPay(array $calculation, float $additionalAllowances = 0, float $additionalDeductions = 0): float
    {
        $basicPay = $calculation['basic_pay'];
        $allowances = $calculation['allowances'] + $additionalAllowances;
        $deductions = $calculation['deductions'] + $additionalDeductions;
        $epfEmployee = $calculation['epf_employee'];
        $socsoEmployee = $calculation['socso_employee'];

        $netPay = $basicPay + $allowances - $deductions - $epfEmployee - $socsoEmployee;

        return round(max(0, $netPay), 2);
    }

    /**
     * Get empty calculation structure
     */
    protected function getEmptyCalculation(): array
    {
        return [
            'basic_pay' => 0,
            'total_hours' => 0,
            'total_classes' => 0,
            'allowances' => 0,
            'deductions' => 0,
            'epf_employee' => 0,
            'epf_employer' => 0,
            'socso_employee' => 0,
            'socso_employer' => 0,
            'epf_enabled' => false,
            'socso_enabled' => false,
            'socso_type' => null,
        ];
    }

    /**
     * Get salary breakdown for display
     * 
     * @param Teacher $teacher
     * @param string $periodStart
     * @param string $periodEnd
     * @param array|null $overrideFlags Optional flags to override teacher's settings
     * @return array
     */
    public function getSalaryBreakdown(Teacher $teacher, string $periodStart, string $periodEnd, ?array $overrideFlags = null): array
    {
        $calculation = $this->calculateSalary($teacher, $periodStart, $periodEnd, $overrideFlags);
        
        return [
            'teacher' => $teacher,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'pay_type' => $teacher->pay_type,
            'calculation' => $calculation,
            'net_pay' => $this->calculateNetPay($calculation),
            // Include statutory settings for transparency
            'statutory_settings' => [
                'epf_enabled' => $calculation['epf_enabled'] ?? false,
                'socso_enabled' => $calculation['socso_enabled'] ?? false,
                'socso_type' => $calculation['socso_type'] ?? null,
            ],
        ];
    }

    /**
     * Preview salary calculation with custom statutory flags
     * Useful for AJAX preview in payslip creation form
     * 
     * @param Teacher $teacher
     * @param string $periodStart
     * @param string $periodEnd
     * @param bool $epfEnabled
     * @param bool $socsoEnabled
     * @param string $socsoType
     * @return array
     */
    public function previewSalary(
        Teacher $teacher,
        string $periodStart,
        string $periodEnd,
        bool $epfEnabled = true,
        bool $socsoEnabled = true,
        string $socsoType = 'regular'
    ): array {
        $overrideFlags = [
            'epf_enabled' => $epfEnabled,
            'socso_enabled' => $socsoEnabled,
            'socso_type' => $socsoType,
        ];

        return $this->getSalaryBreakdown($teacher, $periodStart, $periodEnd, $overrideFlags);
    }

    /**
     * Get statutory contribution summary for a teacher
     * 
     * @param Teacher $teacher
     * @return array
     */
    public function getStatutoryContributionStatus(Teacher $teacher): array
    {
        return [
            'epf' => [
                'enabled' => $teacher->epf_enabled ?? true,
                'number' => $teacher->epf_number,
                'has_number' => !empty($teacher->epf_number),
            ],
            'socso' => [
                'enabled' => $teacher->socso_enabled ?? true,
                'type' => $teacher->socso_type ?? 'regular',
                'number' => $teacher->socso_number,
                'has_number' => !empty($teacher->socso_number),
            ],
        ];
    }
}
