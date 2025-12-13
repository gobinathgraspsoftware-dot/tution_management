<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SeminarFinancialExport implements WithMultipleSheets
{
    protected $overview;

    public function __construct(array $overview)
    {
        $this->overview = $overview;
    }

    public function sheets(): array
    {
        return [
            new SummarySheet($this->overview),
            new RevenueBreakdownSheet($this->overview),
            new ExpenseBreakdownSheet($this->overview),
        ];
    }
}

/**
 * Summary Sheet
 */
class SummarySheet implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    protected $overview;

    public function __construct(array $overview)
    {
        $this->overview = $overview;
    }

    public function collection()
    {
        $seminar = $this->overview['seminar'];
        $revenue = $this->overview['revenue'];
        $expenses = $this->overview['expenses'];
        $profitability = $this->overview['profitability'];

        $data = collect();

        // Seminar Information
        $data->push(['SEMINAR INFORMATION', '']);
        $data->push(['Seminar Name', $seminar->name]);
        $data->push(['Seminar Code', $seminar->code]);
        $data->push(['Date', $seminar->date->format('d M Y')]);
        $data->push(['Type', ucfirst($seminar->type)]);
        $data->push(['Status', ucfirst($seminar->status)]);
        $data->push(['', '']);

        // Revenue Summary
        $data->push(['REVENUE SUMMARY', '']);
        $data->push(['Total Revenue (Paid)', 'RM ' . number_format($revenue['total'], 2)]);
        $data->push(['Pending Revenue', 'RM ' . number_format($revenue['pending'], 2)]);
        $data->push(['Refunded', 'RM ' . number_format($revenue['refunded'], 2)]);
        $data->push(['Total Participants', $revenue['participant_count']]);
        $data->push(['Paid Participants', $revenue['paid_count']]);
        $data->push(['', '']);

        // Expense Summary
        $data->push(['EXPENSE SUMMARY', '']);
        $data->push(['Total Expenses (Approved)', 'RM ' . number_format($expenses['total'], 2)]);
        $data->push(['Pending Approval', 'RM ' . number_format($expenses['pending'], 2)]);
        $data->push(['Total Expense Items', $expenses['expense_count']]);
        $data->push(['Approved Items', $expenses['approved_count']]);
        $data->push(['', '']);

        // Profitability
        $data->push(['PROFITABILITY ANALYSIS', '']);
        $data->push(['Net Profit/Loss', 'RM ' . number_format($profitability['net_profit'], 2)]);
        $data->push(['Profit Margin', $profitability['profit_margin'] . '%']);
        $data->push(['ROI', $profitability['roi'] . '%']);
        $data->push(['Status', $profitability['status']]);

        return $data;
    }

    public function headings(): array
    {
        return ['Item', 'Value'];
    }

    public function title(): string
    {
        return 'Summary';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
            'A:B' => ['alignment' => ['horizontal' => 'left']],
        ];
    }
}

/**
 * Revenue Breakdown Sheet
 */
class RevenueBreakdownSheet implements FromCollection, WithHeadings, WithTitle
{
    protected $overview;

    public function __construct(array $overview)
    {
        $this->overview = $overview;
    }

    public function collection()
    {
        $revenue = $this->overview['revenue'];
        $data = collect();

        $data->push(['BY PAYMENT METHOD', '', '']);
        
        foreach ($revenue['by_method'] as $method => $amount) {
            $data->push([
                ucfirst($method),
                'RM ' . number_format($amount, 2),
                ''
            ]);
        }

        $data->push(['', '', '']);
        $data->push(['TOTAL REVENUE', 'RM ' . number_format($revenue['total'], 2), '']);

        return $data;
    }

    public function headings(): array
    {
        return ['Payment Method', 'Amount', 'Notes'];
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
    protected $overview;

    public function __construct(array $overview)
    {
        $this->overview = $overview;
    }

    public function collection()
    {
        $expenses = $this->overview['expenses'];
        $data = collect();

        $data->push(['BY CATEGORY', '', '']);
        
        foreach ($expenses['by_category'] as $category => $amount) {
            $data->push([
                \App\Services\SeminarAccountingService::getCategoryLabel($category),
                'RM ' . number_format($amount, 2),
                ''
            ]);
        }

        $data->push(['', '', '']);
        $data->push(['TOTAL EXPENSES', 'RM ' . number_format($expenses['total'], 2), '']);

        return $data;
    }

    public function headings(): array
    {
        return ['Category', 'Amount', 'Notes'];
    }

    public function title(): string
    {
        return 'Expense Breakdown';
    }
}
