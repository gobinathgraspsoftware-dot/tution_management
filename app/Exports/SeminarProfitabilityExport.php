<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SeminarProfitabilityExport implements WithMultipleSheets
{
    protected $report;

    public function __construct(array $report)
    {
        $this->report = $report;
    }

    public function sheets(): array
    {
        return [
            new ProfitabilitySummarySheet($this->report),
            new DetailedProfitabilitySheet($this->report),
        ];
    }
}

/**
 * Profitability Summary Sheet
 */
class ProfitabilitySummarySheet implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    protected $report;

    public function __construct(array $report)
    {
        $this->report = $report;
    }

    public function collection()
    {
        $summary = $this->report['summary'];
        $data = collect();

        $data->push(['OVERALL PROFITABILITY SUMMARY', '']);
        $data->push([]);
        $data->push(['Total Seminars', $summary['total_seminars']]);
        $data->push(['Profitable Seminars', $summary['profitable_count']]);
        $data->push(['Loss-Making Seminars', $summary['loss_count']]);
        $data->push([]);
        $data->push(['Total Revenue', 'RM ' . number_format($summary['total_revenue'], 2)]);
        $data->push(['Total Expenses', 'RM ' . number_format($summary['total_expenses'], 2)]);
        $data->push(['Total Profit/Loss', 'RM ' . number_format($summary['total_profit'], 2)]);
        $data->push([]);
        $data->push(['Average Profit per Seminar', 'RM ' . number_format($summary['average_profit'], 2)]);
        $data->push(['Overall Profit Margin', number_format($summary['overall_margin'], 2) . '%']);

        return $data;
    }

    public function headings(): array
    {
        return ['Metric', 'Value'];
    }

    public function title(): string
    {
        return 'Summary';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            'A:B' => ['alignment' => ['horizontal' => 'left']],
        ];
    }
}

/**
 * Detailed Profitability Sheet
 */
class DetailedProfitabilitySheet implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    protected $report;

    public function __construct(array $report)
    {
        $this->report = $report;
    }

    public function collection()
    {
        $seminars = $this->report['seminars'];
        $data = collect();

        foreach ($seminars as $seminar) {
            $data->push([
                $seminar['seminar_code'],
                $seminar['seminar_name'],
                $seminar['date']->format('d/m/Y'),
                ucfirst($seminar['type']),
                ucfirst($seminar['status']),
                $seminar['participants'],
                'RM ' . number_format($seminar['revenue'], 2),
                'RM ' . number_format($seminar['expenses'], 2),
                'RM ' . number_format($seminar['profit'], 2),
                $seminar['profit_margin'] . '%',
                $seminar['status_label'],
            ]);
        }

        // Add totals
        $summary = $this->report['summary'];
        $data->push([]);
        $data->push([
            '', 'TOTAL', '', '', '',
            collect($seminars)->sum('participants'),
            'RM ' . number_format($summary['total_revenue'], 2),
            'RM ' . number_format($summary['total_expenses'], 2),
            'RM ' . number_format($summary['total_profit'], 2),
            number_format($summary['overall_margin'], 2) . '%',
            ''
        ]);

        return $data;
    }

    public function headings(): array
    {
        return [
            'Code',
            'Seminar Name',
            'Date',
            'Type',
            'Status',
            'Participants',
            'Revenue',
            'Expenses',
            'Profit/Loss',
            'Margin %',
            'Result'
        ];
    }

    public function title(): string
    {
        return 'Detailed Report';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
