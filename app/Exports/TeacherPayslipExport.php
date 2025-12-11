<?php

namespace App\Exports;

use App\Models\TeacherPayslip;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TeacherPayslipExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    /**
     * Get collection for export.
     */
    public function collection()
    {
        $query = TeacherPayslip::with('teacher.user');

        // Apply filters
        if (!empty($this->filters['teacher_id'])) {
            $query->where('teacher_id', $this->filters['teacher_id']);
        }

        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        if (!empty($this->filters['month']) && !empty($this->filters['year'])) {
            $query->whereMonth('period_start', $this->filters['month'])
                  ->whereYear('period_start', $this->filters['year']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Define headings for Excel.
     */
    public function headings(): array
    {
        return [
            'Payslip Number',
            'Teacher Name',
            'Period Start',
            'Period End',
            'Pay Type',
            'Total Hours',
            'Total Classes',
            'Basic Pay (RM)',
            'Allowances (RM)',
            'Deductions (RM)',
            'EPF Employee (RM)',
            'EPF Employer (RM)',
            'SOCSO Employee (RM)',
            'SOCSO Employer (RM)',
            'Net Pay (RM)',
            'Status',
            'Payment Date',
            'Payment Method',
            'Reference Number',
            'Generated Date',
        ];
    }

    /**
     * Map data for each row.
     */
    public function map($payslip): array
    {
        return [
            $payslip->payslip_number,
            $payslip->teacher->user->name,
            $payslip->period_start->format('d/m/Y'),
            $payslip->period_end->format('d/m/Y'),
            ucfirst(str_replace('_', ' ', $payslip->teacher->pay_type)),
            number_format($payslip->total_hours, 2),
            $payslip->total_classes,
            number_format($payslip->basic_pay, 2),
            number_format($payslip->allowances, 2),
            number_format($payslip->deductions, 2),
            number_format($payslip->epf_employee, 2),
            number_format($payslip->epf_employer, 2),
            number_format($payslip->socso_employee, 2),
            number_format($payslip->socso_employer, 2),
            number_format($payslip->net_pay, 2),
            ucfirst($payslip->status),
            $payslip->payment_date ? $payslip->payment_date->format('d/m/Y') : '-',
            $payslip->payment_method ?? '-',
            $payslip->reference_number ?? '-',
            $payslip->created_at->format('d/m/Y'),
        ];
    }

    /**
     * Apply styles to the worksheet.
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E8EAF6']
                ],
            ],
        ];
    }
}
