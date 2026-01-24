<?php

namespace App\Exports;

use App\Models\PosTransaction;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PosTransactionExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
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
        $query = PosTransaction::with(['items.inventory', 'cashier']);
        
        // Apply filters
        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('transaction_number', 'like', "%{$search}%")
                  ->orWhereHas('cashier', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }
        
        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }
        
        if (!empty($this->filters['payment_method'])) {
            $query->where('payment_method', $this->filters['payment_method']);
        }
        
        if (!empty($this->filters['start_date']) && !empty($this->filters['end_date'])) {
            $query->dateRange($this->filters['start_date'], $this->filters['end_date']);
        } elseif (!empty($this->filters['start_date'])) {
            $query->whereDate('transaction_date', '>=', $this->filters['start_date']);
        } elseif (!empty($this->filters['end_date'])) {
            $query->whereDate('transaction_date', '<=', $this->filters['end_date']);
        }
        
        if (!empty($this->filters['cashier_id'])) {
            $query->where('cashier_id', $this->filters['cashier_id']);
        }
        
        return $query->orderByDesc('transaction_date')->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Transaction #',
            'Date & Time',
            'Items',
            'Subtotal (RM)',
            'Discount (RM)',
            'Tax (RM)',
            'Total (RM)',
            'Payment Method',
            'Amount Received (RM)',
            'Change (RM)',
            'Reference #',
            'Status',
            'Cashier',
            'Notes',
        ];
    }

    /**
     * @param PosTransaction $transaction
     * @return array
     */
    public function map($transaction): array
    {
        $items = $transaction->items->map(function ($item) {
            return $item->inventory->name . ' x' . $item->quantity;
        })->join(', ');
        
        return [
            $transaction->transaction_number,
            $transaction->transaction_date->format('Y-m-d H:i:s'),
            $items,
            number_format($transaction->subtotal, 2),
            number_format($transaction->discount, 2),
            number_format($transaction->tax, 2),
            number_format($transaction->total_amount, 2),
            ucfirst($transaction->payment_method),
            number_format($transaction->amount_received, 2),
            number_format($transaction->change_amount, 2),
            $transaction->reference_number ?? '-',
            ucfirst($transaction->status),
            $transaction->cashier->name ?? 'N/A',
            $transaction->notes ?? '-',
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet): array
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
