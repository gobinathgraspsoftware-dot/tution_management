<?php

namespace App\Exports;

use App\Models\Seminar;
use App\Services\SeminarAccountingService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SeminarExpenseExport implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    protected $seminar;

    public function __construct(Seminar $seminar)
    {
        $this->seminar = $seminar;
    }

    public function collection()
    {
        $expenses = $this->seminar->expenses()->get();
        $data = collect();

        // Header info
        $data->push(['Expense Report - ' . $this->seminar->name]);
        $data->push(['Seminar Code: ' . $this->seminar->code]);
        $data->push(['Date: ' . $this->seminar->date->format('d M Y')]);
        $data->push([]);

        // Expense items
        foreach ($expenses as $expense) {
            $data->push([
                $expense->expense_date->format('d/m/Y'),
                SeminarAccountingService::getCategoryLabel($expense->category),
                $expense->description,
                'RM ' . number_format($expense->amount, 2),
                $expense->payment_method ? ucfirst($expense->payment_method) : '-',
                $expense->reference_number ?? '-',
                ucfirst($expense->approval_status),
                $expense->approved_at ? $expense->approved_at->format('d/m/Y') : '-',
                $expense->rejection_reason ?? '-',
            ]);
        }

        // Summary
        $data->push([]);
        $data->push(['', '', 'SUMMARY']);
        $data->push(['', '', 'Total Expenses', 'RM ' . number_format($expenses->sum('amount'), 2)]);
        $data->push(['', '', 'Approved', 'RM ' . number_format($expenses->where('approval_status', 'approved')->sum('amount'), 2)]);
        $data->push(['', '', 'Pending', 'RM ' . number_format($expenses->where('approval_status', 'pending')->sum('amount'), 2)]);
        $data->push(['', '', 'Rejected', 'RM ' . number_format($expenses->where('approval_status', 'rejected')->sum('amount'), 2)]);

        return $data;
    }

    public function headings(): array
    {
        return [
            'Date',
            'Category',
            'Description',
            'Amount',
            'Payment Method',
            'Reference No.',
            'Status',
            'Approved Date',
            'Rejection Reason'
        ];
    }

    public function title(): string
    {
        return 'Expenses';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            5 => ['font' => ['bold' => true]],
        ];
    }
}
