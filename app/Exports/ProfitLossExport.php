<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProfitLossExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    protected $statement;
    protected $period;

    public function __construct(array $statement, array $period)
    {
        $this->statement = $statement;
        $this->period = $period;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $rows = collect();

        // Header
        $rows->push(['PROFIT & LOSS STATEMENT']);
        $rows->push(['Period: ' . $this->period['start'] . ' to ' . $this->period['end']]);
        $rows->push(['Generated: ' . now()->format('d M Y, h:i A')]);
        $rows->push(['']);

        // Revenue Section
        $rows->push(['REVENUE', '', '']);
        $rows->push(['Student Fees (Online)', number_format($this->statement['revenue']['student_fees_online'], 2), '']);
        $rows->push(['Student Fees (Physical)', number_format($this->statement['revenue']['student_fees_physical'], 2), '']);
        $rows->push(['Seminar Revenue', number_format($this->statement['revenue']['seminar_revenue'], 2), '']);
        $rows->push(['Cafeteria Sales', number_format($this->statement['revenue']['cafeteria_sales'], 2), '']);
        $rows->push(['Material Sales', number_format($this->statement['revenue']['material_sales'], 2), '']);
        $rows->push(['Other Revenue', number_format($this->statement['revenue']['other_revenue'], 2), '']);
        $rows->push(['']);
        $rows->push(['TOTAL REVENUE', number_format($this->statement['revenue']['total_revenue'], 2), '100.00%']);
        $rows->push(['']);

        // Expenses Section
        $rows->push(['EXPENSES', '', '']);
        foreach ($this->statement['expenses']['by_category'] as $expense) {
            $percentage = $this->statement['expenses']['total_expenses'] > 0
                ? ($expense->total / $this->statement['expenses']['total_expenses']) * 100
                : 0;
            $rows->push([$expense->name, number_format($expense->total, 2), number_format($percentage, 2) . '%']);
        }
        $rows->push(['']);
        $rows->push(['TOTAL EXPENSES', number_format($this->statement['expenses']['total_expenses'], 2), '100.00%']);
        $rows->push(['']);

        // Summary
        $rows->push(['SUMMARY', '', '']);
        $rows->push(['Gross Profit', number_format($this->statement['summary']['gross_profit'], 2), '']);
        $rows->push(['Profit Margin', number_format($this->statement['summary']['profit_margin'], 2) . '%', '']);
        $rows->push(['Status', $this->statement['summary']['status'], '']);

        return $rows;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 14],
                'alignment' => ['horizontal' => 'center']
            ],
            5 => ['font' => ['bold' => true, 'size' => 12]],
            13 => ['font' => ['bold' => true, 'size' => 11]],
            // Expenses header
            'A15' => ['font' => ['bold' => true, 'size' => 12]],
            // Summary section
            'A' . ($this->getRowCount() - 3) => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }

    /**
     * Get total row count
     */
    private function getRowCount()
    {
        return 4 + 10 + count($this->statement['expenses']['by_category']) + 8;
    }
}
