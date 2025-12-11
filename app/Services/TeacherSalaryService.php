<?php

namespace App\Services;

use App\Models\Teacher;
use App\Models\TeacherAttendance;
use App\Models\ClassSession;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TeacherSalaryService
{
    /**
     * Calculate salary for a teacher for a given period
     */
    public function calculateSalary(Teacher $teacher, string $periodStart, string $periodEnd): array
    {
        $startDate = Carbon::parse($periodStart);
        $endDate = Carbon::parse($periodEnd);

        switch ($teacher->pay_type) {
            case 'hourly':
                return $this->calculateHourlySalary($teacher, $startDate, $endDate);
            
            case 'monthly':
                return $this->calculateMonthlySalary($teacher, $startDate, $endDate);
            
            case 'per_class':
                return $this->calculatePerClassSalary($teacher, $startDate, $endDate);
            
            default:
                return $this->getEmptyCalculation();
        }
    }

    /**
     * Calculate hourly based salary
     */
    protected function calculateHourlySalary(Teacher $teacher, Carbon $startDate, Carbon $endDate): array
    {
        // Get total hours from TeacherAttendance
        $attendanceHours = TeacherAttendance::where('teacher_id', $teacher->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->whereIn('status', ['present', 'half_day'])
            ->sum('hours_worked');

        // Get total hours from ClassSessions
        $classHours = $this->calculateClassHours($teacher, $startDate, $endDate);

        // Use whichever is higher (attendance or class sessions)
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
        ];
    }

    /**
     * Calculate monthly salary
     */
    protected function calculateMonthlySalary(Teacher $teacher, Carbon $startDate, Carbon $endDate): array
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

        // Calculate EPF (Employee 11%, Employer 13%)
        $epfEmployee = $basicPay * 0.11;
        $epfEmployer = $basicPay * 0.13;

        // Calculate SOCSO (based on salary brackets - simplified)
        $socsoRates = $this->calculateSOCSO($basicPay);

        $totalClasses = $this->getCompletedClassesCount($teacher, $startDate, $endDate);
        $totalHours = $this->calculateClassHours($teacher, $startDate, $endDate);

        return [
            'basic_pay' => round($basicPay, 2),
            'total_hours' => round($totalHours, 2),
            'total_classes' => $totalClasses,
            'allowances' => 0,
            'deductions' => round($absenceDeduction, 2),
            'epf_employee' => round($epfEmployee, 2),
            'epf_employer' => round($epfEmployer, 2),
            'socso_employee' => round($socsoRates['employee'], 2),
            'socso_employer' => round($socsoRates['employer'], 2),
            'working_days' => $workingDaysInMonth,
            'present_days' => $presentDays,
            'absent_days' => $absentDays,
        ];
    }

    /**
     * Calculate per class based salary
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
        ];
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

        return $totalMinutes / 60; // Convert to hours
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
     * Calculate SOCSO based on salary brackets (Malaysian rates)
     */
    protected function calculateSOCSO(float $salary): array
    {
        // Simplified SOCSO calculation
        // Actual rates vary by salary bracket - this is a simplified version
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
     * Get working days in period (excluding weekends)
     */
    protected function getWorkingDaysInPeriod(Carbon $startDate, Carbon $endDate): int
    {
        $workingDays = 0;
        $current = $startDate->copy();

        while ($current->lte($endDate)) {
            // Count Monday to Friday as working days
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

        return round(max(0, $netPay), 2); // Ensure not negative
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
        ];
    }

    /**
     * Get salary breakdown for display
     */
    public function getSalaryBreakdown(Teacher $teacher, string $periodStart, string $periodEnd): array
    {
        $calculation = $this->calculateSalary($teacher, $periodStart, $periodEnd);
        
        return [
            'teacher' => $teacher,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'pay_type' => $teacher->pay_type,
            'calculation' => $calculation,
            'net_pay' => $this->calculateNetPay($calculation),
        ];
    }
}
