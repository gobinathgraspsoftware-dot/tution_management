<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;
use Illuminate\Support\Collection;

class FinancialReportExport implements WithMultipleSheets
{
    protected $reportData;

    public function __construct(array $reportData)
    {
        $this->reportData = $reportData;
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];

        $sheets[] = new FinancialSummarySheet($this->reportData);
        $sheets[] = new RevenueBreakdownSheet($this->reportData);
        $sheets[] = new ExpenseBreakdownSheet($this->reportData);
        $sheets[] = new DailyTrendsSheet($this->reportData);

        return $sheets;
    }
}

/**
 * Summary Sheet
 */
class FinancialSummarySheet implements FromCollection, WithHeadings, WithTitle
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        $summary = $this->data['summary'];

        return collect([
            ['Total Revenue', 'RM ' . number_format($summary['total_revenue'], 2)],
            ['Total Expenses', 'RM ' . number_format($summary['total_expenses'], 2)],
            ['Net Profit/Loss', 'RM ' . number_format($summary['net_profit'], 2)],
            ['Profit Margin', number_format($summary['profit_margin'], 2) . '%'],
            ['Status', $summary['status']],
            [''],
            ['Period', $this->data['period']['start'] . ' to ' . $this->data['period']['end']],
            ['Days', $this->data['period']['days']],
            ['Revenue Transactions', $summary['revenue_count']],
            ['Expense Transactions', $summary['expense_count']],
        ]);
    }

    public function headings(): array
    {
        return ['Metric', 'Value'];
    }

    public function title(): string
    {
        return 'Summary';
    }
}

/**
 * Revenue Breakdown Sheet
 */
class RevenueBreakdownSheet implements FromCollection, WithHeadings, WithTitle
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        $revenue = $this->data['revenue'];

        return collect([
            [
                'Student Fees (Online)',
                'RM ' . number_format($revenue['student_fees']['online'], 2),
                number_format($revenue['student_fees']['percentage'], 2) . '%'
            ],
            [
                'Student Fees (Physical)',
                'RM ' . number_format($revenue['student_fees']['physical'], 2),
                number_format($revenue['student_fees']['percentage'], 2) . '%'
            ],
            [
                'Seminar Revenue',
                'RM ' . number_format($revenue['seminar_revenue']['amount'], 2),
                number_format($revenue['seminar_revenue']['percentage'], 2) . '%'
            ],
            [
                'Cafeteria Sales',
                'RM ' . number_format($revenue['cafeteria_sales']['amount'], 2),
                number_format($revenue['cafeteria_sales']['percentage'], 2) . '%'
            ],
            [
                'Material Sales',
                'RM ' . number_format($revenue['material_sales']['amount'], 2),
                number_format($revenue['material_sales']['percentage'], 2) . '%'
            ],
            [
                'Other Revenue',
                'RM ' . number_format($revenue['other_revenue']['amount'], 2),
                number_format($revenue['other_revenue']['percentage'], 2) . '%'
            ],
            ['', '', ''],
            [
                'TOTAL REVENUE',
                'RM ' . number_format($revenue['total'], 2),
                '100.00%'
            ],
        ]);
    }

    public function headings(): array
    {
        return ['Category', 'Amount', 'Percentage'];
    }

    public function title(): string
    {
        return 'Revenue Breakdown';
    }
}

/**
 * Expense Breakdown Sheet
 */
class ExpenseBreakdownSheet implements FromCollection, WithHeadings, WithTitle
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        $expenses = $this->data['expenses'];
        $rows = collect();

        foreach ($expenses['by_category'] as $category) {
            $rows->push([
                $category['category'],
                'RM ' . number_format($category['amount'], 2),
                $category['count'],
                'RM ' . number_format($category['average'], 2),
                number_format($category['percentage'], 2) . '%'
            ]);
        }

        $rows->push(['', '', '', '', '']);
        $rows->push([
            'TOTAL EXPENSES',
            'RM ' . number_format($expenses['total'], 2),
            $expenses['count'],
            'RM ' . number_format($expenses['average_per_expense'], 2),
            '100.00%'
        ]);

        if ($expenses['pending']['count'] > 0) {
            $rows->push(['', '', '', '', '']);
            $rows->push([
                'Pending Expenses',
                'RM ' . number_format($expenses['pending']['amount'], 2),
                $expenses['pending']['count'],
                '',
                ''
            ]);
        }

        return $rows;
    }

    public function headings(): array
    {
        return ['Category', 'Amount', 'Count', 'Average', 'Percentage'];
    }

    public function title(): string
    {
        return 'Expense Breakdown';
    }
}

/**
 * Daily Trends Sheet
 */
class DailyTrendsSheet implements FromCollection, WithHeadings, WithTitle
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        $trends = $this->data['trends']['daily_trends'];

        return $trends->map(function ($trend) {
            return [
                $trend['date'],
                'RM ' . number_format($trend['revenue'], 2),
                'RM ' . number_format($trend['expense'], 2),
                'RM ' . number_format($trend['profit'], 2),
            ];
        });
    }

    public function headings(): array
    {
        return ['Date', 'Revenue', 'Expenses', 'Profit/Loss'];
    }

    public function title(): string
    {
        return 'Daily Trends';
    }
}
