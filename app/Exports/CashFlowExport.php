<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CashFlowExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    protected $cashFlowData;
    protected $period;

    public function __construct(array $cashFlowData, array $period)
    {
        $this->cashFlowData = $cashFlowData;
        $this->period = $period;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $rows = collect();

        // Header
        $rows->push(['CASH FLOW ANALYSIS']);
        $rows->push(['Period: ' . $this->period['start'] . ' to ' . $this->period['end']]);
        $rows->push(['Generated: ' . now()->format('d M Y, h:i A')]);
        $rows->push(['']);

        // Summary
        $rows->push(['SUMMARY', '', '']);
        $rows->push([
            'Total Cash Inflow (Revenue)',
            number_format($this->cashFlowData['total_inflow'], 2),
            ''
        ]);
        $rows->push([
            'Total Cash Outflow (Expenses)',
            number_format($this->cashFlowData['total_outflow'], 2),
            ''
        ]);
        $rows->push([
            'Net Cash Flow',
            number_format($this->cashFlowData['net_cash_flow'], 2),
            ucfirst($this->cashFlowData['cash_flow_status'])
        ]);
        $rows->push(['']);

        // Daily Breakdown Header
        $rows->push(['DAILY CASH FLOW BREAKDOWN', '', '', '']);

        // Daily data
        foreach ($this->cashFlowData['daily_breakdown'] as $day) {
            $rows->push([
                $day['date'],
                number_format($day['revenue'], 2),
                number_format($day['expense'], 2),
                number_format($day['profit'], 2),
            ]);
        }

        return $rows;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            ['', '', '', ''],
            ['', '', '', ''],
            ['', '', '', ''],
            ['', '', '', ''],
            ['', '', '', ''],
            ['', '', '', ''],
            ['', '', '', ''],
            ['', '', '', ''],
            ['', '', '', ''],
            ['Date', 'Cash Inflow', 'Cash Outflow', 'Net Cash Flow'],
        ];
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
            10 => ['font' => ['bold' => true, 'size' => 12]],
            11 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E0E0E0']
                ]
            ],
        ];
    }
}
