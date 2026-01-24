<?php

namespace App\Exports;

use App\Models\DailyCashReport;
use App\Models\PosTransaction;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class DailyCashReportExport implements WithMultipleSheets
{
    protected $report;

    public function __construct(DailyCashReport $report)
    {
        $this->report = $report;
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        return [
            'Summary' => new DailyCashReportSummarySheet($this->report),
            'Transactions' => new DailyCashReportTransactionsSheet($this->report),
        ];
    }
}

// Summary Sheet
class DailyCashReportSummarySheet implements 
    \Maatwebsite\Excel\Concerns\FromArray,
    \Maatwebsite\Excel\Concerns\WithTitle,
    \Maatwebsite\Excel\Concerns\WithStyles,
    \Maatwebsite\Excel\Concerns\ShouldAutoSize
{
    protected $report;

    public function __construct(DailyCashReport $report)
    {
        $this->report = $report;
    }

    public function array(): array
    {
        $report = $this->report;
        $totalSales = $report->total_cash_sales + $report->total_qr_sales;
        
        return [
            ['DAILY CASH REPORT'],
            [''],
            ['Report Date:', $report->report_date->format('l, d F Y')],
            ['Status:', ucfirst($report->status)],
            [''],
            ['CASH SUMMARY'],
            ['Opening Cash:', 'RM ' . number_format($report->opening_cash, 2)],
            ['Cash Sales:', 'RM ' . number_format($report->total_cash_sales, 2)],
            ['Expected Closing:', 'RM ' . number_format($report->expected_closing, 2)],
            ['Actual Closing:', $report->actual_closing !== null ? 'RM ' . number_format($report->actual_closing, 2) : '-'],
            ['Variance:', $report->variance !== null ? 'RM ' . number_format($report->variance, 2) : '-'],
            [''],
            ['SALES SUMMARY'],
            ['Total Transactions:', $report->total_transactions],
            ['Cash Sales:', 'RM ' . number_format($report->total_cash_sales, 2)],
            ['QR Sales:', 'RM ' . number_format($report->total_qr_sales, 2)],
            ['Total Sales:', 'RM ' . number_format($totalSales, 2)],
            [''],
            ['Closed By:', $report->closedBy->name ?? '-'],
            ['Notes:', $report->notes ?? '-'],
        ];
    }

    public function title(): string
    {
        return 'Summary';
    }

    public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 16]],
            6 => ['font' => ['bold' => true]],
            13 => ['font' => ['bold' => true]],
        ];
    }
}

// Transactions Sheet
class DailyCashReportTransactionsSheet implements 
    \Maatwebsite\Excel\Concerns\FromCollection,
    \Maatwebsite\Excel\Concerns\WithTitle,
    \Maatwebsite\Excel\Concerns\WithHeadings,
    \Maatwebsite\Excel\Concerns\WithMapping,
    \Maatwebsite\Excel\Concerns\WithStyles,
    \Maatwebsite\Excel\Concerns\ShouldAutoSize
{
    protected $report;

    public function __construct(DailyCashReport $report)
    {
        $this->report = $report;
    }

    public function collection()
    {
        return PosTransaction::with(['items.inventory', 'cashier'])
            ->whereDate('transaction_date', $this->report->report_date)
            ->orderBy('transaction_date')
            ->get();
    }

    public function title(): string
    {
        return 'Transactions';
    }

    public function headings(): array
    {
        return [
            'Time',
            'Transaction #',
            'Items',
            'Subtotal (RM)',
            'Discount (RM)',
            'Total (RM)',
            'Payment',
            'Status',
            'Cashier',
        ];
    }

    public function map($transaction): array
    {
        $items = $transaction->items->map(function ($item) {
            return $item->inventory->name . ' x' . $item->quantity;
        })->join(', ');
        
        return [
            $transaction->transaction_date->format('H:i:s'),
            $transaction->transaction_number,
            $items,
            number_format($transaction->subtotal, 2),
            number_format($transaction->discount, 2),
            number_format($transaction->total_amount, 2),
            ucfirst($transaction->payment_method),
            ucfirst($transaction->status),
            $transaction->cashier->name ?? 'N/A',
        ];
    }

    public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FDA530'],
                ],
            ],
        ];
    }
}
