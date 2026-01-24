<?php

namespace App\Exports;

use App\Models\Inventory;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\Exportable;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InventoryExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    use Exportable;

    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Inventory::with('category')
            ->when(isset($this->filters['category_id']) && $this->filters['category_id'], function ($q) {
                $q->where('category_id', $this->filters['category_id']);
            })
            ->when(isset($this->filters['status']) && $this->filters['status'], function ($q) {
                $q->where('status', $this->filters['status']);
            })
            ->when(isset($this->filters['low_stock']) && $this->filters['low_stock'], function ($q) {
                $q->lowStock();
            })
            ->orderBy('name')
            ->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'ID',
            'SKU',
            'Name',
            'Category',
            'Description',
            'Cost Price (RM)',
            'Selling Price (RM)',
            'Current Stock',
            'Reorder Level',
            'Unit',
            'Stock Value (Cost)',
            'Stock Value (Retail)',
            'Status',
            'Created At',
        ];
    }

    /**
     * @param Inventory $item
     * @return array
     */
    public function map($item): array
    {
        return [
            $item->id,
            $item->sku,
            $item->name,
            $item->category->name ?? 'N/A',
            $item->description,
            number_format($item->cost_price, 2),
            number_format($item->selling_price, 2),
            $item->current_stock,
            $item->reorder_level,
            $item->unit,
            number_format($item->current_stock * $item->cost_price, 2),
            number_format($item->current_stock * $item->selling_price, 2),
            ucfirst(str_replace('_', ' ', $item->status)),
            $item->created_at->format('Y-m-d H:i:s'),
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
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4'],
                ],
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            ],
        ];
    }
}
