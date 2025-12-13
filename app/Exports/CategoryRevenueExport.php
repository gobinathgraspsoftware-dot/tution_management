<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CategoryRevenueExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $data;
    protected $period;

    public function __construct(array $data, array $period)
    {
        $this->data = $data;
        $this->period = $period;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return collect($this->data);
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Revenue Category',
            'Amount (RM)',
            'Percentage (%)',
            'Transaction Count',
            'Average per Transaction',
        ];
    }

    /**
     * @var mixed $row
     */
    public function map($row): array
    {
        $amount = $row['amount'] ?? 0;
        $total = collect($this->data)->sum('amount');
        $percentage = $total > 0 ? ($amount / $total) * 100 : 0;
        $count = $row['count'] ?? 0;
        $average = $count > 0 ? $amount / $count : 0;

        return [
            $row['category'],
            number_format($amount, 2),
            number_format($percentage, 2),
            $count,
            number_format($average, 2),
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}
