<?php

namespace App\Exports;

use App\Models\Inventory;
use App\Models\InventoryLog;
use App\Services\InventoryService;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InventoryReportExport implements WithMultipleSheets
{
    use Exportable;

    protected $type;
    protected $params;

    public function __construct(string $type, array $params = [])
    {
        $this->type = $type;
        $this->params = $params;
    }

    public function sheets(): array
    {
        if ($this->type === 'movement') {
            return [
                new MovementReportSheet($this->params),
                new MovementSummarySheet($this->params),
            ];
        }

        if ($this->type === 'valuation') {
            return [
                new ValuationReportSheet($this->params),
                new ValuationSummarySheet($this->params),
            ];
        }

        return [];
    }
}

class MovementReportSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
{
    protected $params;

    public function __construct(array $params)
    {
        $this->params = $params;
    }

    public function title(): string
    {
        return 'Movement Details';
    }

    public function collection()
    {
        $query = InventoryLog::with(['inventory.category', 'createdBy'])
            ->whereBetween('created_at', [
                $this->params['start_date'],
                $this->params['end_date'],
            ]);

        if (!empty($this->params['category_id'])) {
            $query->whereHas('inventory', function ($q) {
                $q->where('category_id', $this->params['category_id']);
            });
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'Date',
            'Item Name',
            'SKU',
            'Category',
            'Type',
            'Quantity',
            'Previous Stock',
            'New Stock',
            'Reference',
            'Notes',
            'Created By',
        ];
    }

    public function map($log): array
    {
        return [
            $log->created_at->format('Y-m-d H:i:s'),
            $log->inventory->name ?? 'N/A',
            $log->inventory->sku ?? 'N/A',
            $log->inventory->category->name ?? 'N/A',
            ucfirst($log->type),
            $log->quantity,
            $log->previous_stock,
            $log->new_stock,
            $log->reference,
            $log->notes,
            $log->createdBy->name ?? 'System',
        ];
    }

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

class MovementSummarySheet implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    protected $params;

    public function __construct(array $params)
    {
        $this->params = $params;
    }

    public function title(): string
    {
        return 'Summary';
    }

    public function collection()
    {
        $query = InventoryLog::whereBetween('created_at', [
            $this->params['start_date'],
            $this->params['end_date'],
        ]);

        if (!empty($this->params['category_id'])) {
            $query->whereHas('inventory', function ($q) {
                $q->where('category_id', $this->params['category_id']);
            });
        }

        $logs = $query->get();

        return collect([
            ['Total Additions', $logs->where('type', 'add')->sum('quantity')],
            ['Total Removals', $logs->where('type', 'remove')->sum('quantity')],
            ['Total Adjustments', $logs->where('type', 'adjust')->count()],
            ['Total Sales', $logs->where('type', 'sale')->sum('quantity')],
            ['Total Transactions', $logs->count()],
        ]);
    }

    public function headings(): array
    {
        return ['Metric', 'Value'];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '70AD47'],
                ],
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            ],
        ];
    }
}

class ValuationReportSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
{
    protected $params;

    public function __construct(array $params)
    {
        $this->params = $params;
    }

    public function title(): string
    {
        return 'Valuation Details';
    }

    public function collection()
    {
        $query = Inventory::with('category')->where('status', 'active');

        if (!empty($this->params['category_id'])) {
            $query->where('category_id', $this->params['category_id']);
        }

        return $query->orderBy('name')->get();
    }

    public function headings(): array
    {
        return [
            'SKU',
            'Name',
            'Category',
            'Unit',
            'Current Stock',
            'Reorder Level',
            'Cost Price (RM)',
            'Selling Price (RM)',
            'Total Cost Value (RM)',
            'Total Retail Value (RM)',
            'Potential Profit (RM)',
            'Stock Status',
        ];
    }

    public function map($item): array
    {
        $costValue = $item->current_stock * $item->cost_price;
        $retailValue = $item->current_stock * $item->selling_price;

        $status = 'Healthy';
        if ($item->current_stock == 0) {
            $status = 'Out of Stock';
        } elseif ($item->current_stock <= $item->reorder_level) {
            $status = 'Low Stock';
        }

        return [
            $item->sku,
            $item->name,
            $item->category->name ?? 'N/A',
            $item->unit,
            $item->current_stock,
            $item->reorder_level,
            number_format($item->cost_price, 2),
            number_format($item->selling_price, 2),
            number_format($costValue, 2),
            number_format($retailValue, 2),
            number_format($retailValue - $costValue, 2),
            $status,
        ];
    }

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

class ValuationSummarySheet implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    protected $params;

    public function __construct(array $params)
    {
        $this->params = $params;
    }

    public function title(): string
    {
        return 'Summary';
    }

    public function collection()
    {
        $query = Inventory::where('status', 'active');

        if (!empty($this->params['category_id'])) {
            $query->where('category_id', $this->params['category_id']);
        }

        $items = $query->get();

        $totalCost = $items->sum(function ($item) {
            return $item->current_stock * $item->cost_price;
        });

        $totalRetail = $items->sum(function ($item) {
            return $item->current_stock * $item->selling_price;
        });

        return collect([
            ['Total Items', $items->count()],
            ['Total Stock Units', $items->sum('current_stock')],
            ['Total Cost Value (RM)', number_format($totalCost, 2)],
            ['Total Retail Value (RM)', number_format($totalRetail, 2)],
            ['Potential Profit (RM)', number_format($totalRetail - $totalCost, 2)],
            ['Profit Margin (%)', $totalCost > 0 ? number_format((($totalRetail - $totalCost) / $totalCost) * 100, 2) : '0.00'],
        ]);
    }

    public function headings(): array
    {
        return ['Metric', 'Value'];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '70AD47'],
                ],
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            ],
        ];
    }
}
