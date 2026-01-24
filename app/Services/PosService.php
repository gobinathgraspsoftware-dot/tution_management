<?php

namespace App\Services;

use App\Models\PosTransaction;
use App\Models\PosTransactionItem;
use App\Models\DailyCashReport;
use App\Models\Inventory;
use App\Models\InventoryLog;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PosService
{
    /**
     * Generate unique transaction number
     * Format: POS{YYYYMMDD}{0001}
     */
    public function generateTransactionNumber(): string
    {
        $prefix = 'POS';
        $date = now()->format('Ymd');

        $lastTransaction = PosTransaction::whereDate('transaction_date', today())
            ->orderBy('id', 'desc')
            ->first();

        if ($lastTransaction) {
            $lastNumber = (int) substr($lastTransaction->transaction_number, -4);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix . $date . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get available inventory items for POS
     */
    public function getAvailableItems($categoryId = null, $search = null)
    {
        $query = Inventory::with('category')
            ->active()
            ->where('current_stock', '>', 0);

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('name')->get();
    }

    /**
     * Process a POS transaction
     */
    public function processTransaction(array $data): PosTransaction
    {
        return DB::transaction(function () use ($data) {
            // Validate stock availability first
            foreach ($data['items'] as $item) {
                $inventory = Inventory::findOrFail($item['inventory_id']);
                if ($inventory->current_stock < $item['quantity']) {
                    throw new \Exception("Insufficient stock for {$inventory->name}. Available: {$inventory->current_stock}");
                }
            }

            // Calculate totals
            $subtotal = 0;
            foreach ($data['items'] as $item) {
                $subtotal += $item['unit_price'] * $item['quantity'];
            }

            $discount = $data['discount'] ?? 0;
            $tax = $data['tax'] ?? 0;
            $totalAmount = $subtotal - $discount + $tax;

            // Create transaction
            $transaction = PosTransaction::create([
                'transaction_number' => $this->generateTransactionNumber(),
                'transaction_date' => now(),
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => $tax,
                'total_amount' => $totalAmount,
                'payment_method' => $data['payment_method'],
                'amount_received' => $data['amount_received'] ?? $totalAmount,
                'change_amount' => $data['change_amount'] ?? 0,
                'reference_number' => $data['reference_number'] ?? null,
                'status' => 'completed',
                'cashier_id' => Auth::id(),
                'notes' => $data['notes'] ?? null,
            ]);

            // Create transaction items and update inventory
            foreach ($data['items'] as $item) {
                PosTransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'inventory_id' => $item['inventory_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['unit_price'] * $item['quantity'],
                ]);

                // Update inventory stock
                $inventory = Inventory::find($item['inventory_id']);
                $previousStock = $inventory->current_stock;
                $inventory->decrement('current_stock', $item['quantity']);

                // Log inventory change
                InventoryLog::create([
                    'inventory_id' => $item['inventory_id'],
                    'type' => 'sale',
                    'quantity' => -$item['quantity'],
                    'previous_stock' => $previousStock,
                    'new_stock' => $inventory->fresh()->current_stock,
                    'reference_type' => 'pos_transaction',
                    'reference_id' => $transaction->id,
                    'notes' => "POS Sale: {$transaction->transaction_number}",
                    'user_id' => Auth::id(),
                ]);
            }

            // Update daily cash report
            $this->updateDailyCashReport($transaction);

            return $transaction->load('items.inventory', 'cashier');
        });
    }

    /**
     * Update or create daily cash report
     */
    protected function updateDailyCashReport(PosTransaction $transaction): void
    {
        $report = DailyCashReport::firstOrCreate(
            ['report_date' => today()],
            [
                'opening_cash' => 0,
                'total_cash_sales' => 0,
                'total_qr_sales' => 0,
                'total_transactions' => 0,
                'expected_closing' => 0,
                'status' => 'open',
            ]
        );

        if ($transaction->payment_method === 'cash') {
            $report->increment('total_cash_sales', $transaction->total_amount);
        } else {
            $report->increment('total_qr_sales', $transaction->total_amount);
        }

        $report->increment('total_transactions');
        $report->expected_closing = $report->opening_cash + $report->total_cash_sales;
        $report->save();
    }

    /**
     * Void a transaction
     */
    public function voidTransaction(PosTransaction $transaction, string $reason): PosTransaction
    {
        if (!$transaction->isCompleted()) {
            throw new \Exception('Only completed transactions can be voided.');
        }

        return DB::transaction(function () use ($transaction, $reason) {
            // Restore inventory
            foreach ($transaction->items as $item) {
                $inventory = $item->inventory;
                $previousStock = $inventory->current_stock;
                $inventory->increment('current_stock', $item->quantity);

                InventoryLog::create([
                    'inventory_id' => $item->inventory_id,
                    'type' => 'void',
                    'quantity' => $item->quantity,
                    'previous_stock' => $previousStock,
                    'new_stock' => $inventory->fresh()->current_stock,
                    'reference_type' => 'pos_transaction',
                    'reference_id' => $transaction->id,
                    'notes' => "Void: {$transaction->transaction_number} - {$reason}",
                    'user_id' => Auth::id(),
                ]);
            }

            // Update daily cash report
            $report = DailyCashReport::where('report_date', $transaction->transaction_date->toDateString())->first();
            if ($report && $report->status === 'open') {
                if ($transaction->payment_method === 'cash') {
                    $report->decrement('total_cash_sales', $transaction->total_amount);
                } else {
                    $report->decrement('total_qr_sales', $transaction->total_amount);
                }
                $report->decrement('total_transactions');
                $report->expected_closing = $report->opening_cash + $report->total_cash_sales;
                $report->save();
            }

            $transaction->update([
                'status' => 'voided',
                'notes' => ($transaction->notes ? $transaction->notes . "\n" : '') . "VOIDED: {$reason}",
            ]);

            return $transaction->fresh(['items.inventory', 'cashier']);
        });
    }

    /**
     * Refund a transaction (partial or full)
     */
    public function refundTransaction(PosTransaction $transaction, array $refundItems, string $reason): PosTransaction
    {
        if (!$transaction->isCompleted()) {
            throw new \Exception('Only completed transactions can be refunded.');
        }

        return DB::transaction(function () use ($transaction, $refundItems, $reason) {
            $refundTotal = 0;

            foreach ($refundItems as $refundItem) {
                $transactionItem = PosTransactionItem::findOrFail($refundItem['item_id']);

                if ($transactionItem->transaction_id !== $transaction->id) {
                    throw new \Exception('Invalid item for this transaction.');
                }

                $refundQty = min($refundItem['quantity'], $transactionItem->quantity);
                $refundAmount = $refundQty * $transactionItem->unit_price;
                $refundTotal += $refundAmount;

                // Restore inventory
                $inventory = $transactionItem->inventory;
                $previousStock = $inventory->current_stock;
                $inventory->increment('current_stock', $refundQty);

                InventoryLog::create([
                    'inventory_id' => $transactionItem->inventory_id,
                    'type' => 'refund',
                    'quantity' => $refundQty,
                    'previous_stock' => $previousStock,
                    'new_stock' => $inventory->fresh()->current_stock,
                    'reference_type' => 'pos_transaction',
                    'reference_id' => $transaction->id,
                    'notes' => "Refund: {$transaction->transaction_number} - {$reason}",
                    'user_id' => Auth::id(),
                ]);
            }

            // Update daily cash report if same day
            $report = DailyCashReport::where('report_date', $transaction->transaction_date->toDateString())->first();
            if ($report && $report->status === 'open') {
                if ($transaction->payment_method === 'cash') {
                    $report->decrement('total_cash_sales', $refundTotal);
                } else {
                    $report->decrement('total_qr_sales', $refundTotal);
                }
                $report->expected_closing = $report->opening_cash + $report->total_cash_sales;
                $report->save();
            }

            $transaction->update([
                'status' => 'refunded',
                'notes' => ($transaction->notes ? $transaction->notes . "\n" : '') . "REFUNDED (RM{$refundTotal}): {$reason}",
            ]);

            return $transaction->fresh(['items.inventory', 'cashier']);
        });
    }

    /**
     * Get today's statistics
     */
    public function getTodayStatistics(): array
    {
        $transactions = PosTransaction::today()->completed()->get();

        return [
            'total_sales' => $transactions->sum('total_amount'),
            'total_transactions' => $transactions->count(),
            'cash_sales' => $transactions->where('payment_method', 'cash')->sum('total_amount'),
            'qr_sales' => $transactions->where('payment_method', 'qr')->sum('total_amount'),
            'average_transaction' => $transactions->count() > 0 ? $transactions->avg('total_amount') : 0,
            'voided_count' => PosTransaction::today()->voided()->count(),
            'refunded_count' => PosTransaction::today()->refunded()->count(),
        ];
    }

    /**
     * Get today's cash report
     */
    public function getTodayCashReport(): ?DailyCashReport
    {
        return DailyCashReport::where('report_date', today())->first();
    }

    /**
     * Set opening cash for today
     */
    public function setOpeningCash(float $amount): DailyCashReport
    {
        $report = DailyCashReport::firstOrCreate(
            ['report_date' => today()],
            [
                'opening_cash' => 0,
                'total_cash_sales' => 0,
                'total_qr_sales' => 0,
                'total_transactions' => 0,
                'expected_closing' => 0,
                'status' => 'open',
            ]
        );

        $report->update([
            'opening_cash' => $amount,
            'expected_closing' => $amount + $report->total_cash_sales,
        ]);

        return $report->fresh();
    }

    /**
     * Close the day
     */
    public function closeDay(float $actualCash, ?string $notes = null): DailyCashReport
    {
        $report = $this->getTodayCashReport();

        if (!$report) {
            throw new \Exception('No report found for today.');
        }

        if ($report->status === 'closed') {
            throw new \Exception('Today\'s report is already closed.');
        }

        $expectedClosing = $report->opening_cash + $report->total_cash_sales;
        $variance = $actualCash - $expectedClosing;

        $report->update([
            'actual_closing' => $actualCash,
            'expected_closing' => $expectedClosing,
            'variance' => $variance,
            'notes' => $notes,
            'closed_by' => Auth::id(),
            'status' => 'closed',
        ]);

        return $report->fresh(['closedBy']);
    }

    /**
     * Get sales by category for today
     */
    public function getSalesByCategory($date = null): array
    {
        $date = $date ?? today();

        $sales = PosTransactionItem::whereHas('transaction', function ($query) use ($date) {
            $query->whereDate('transaction_date', $date)->completed();
        })
        ->with('inventory.category')
        ->get()
        ->groupBy(function ($item) {
            return $item->inventory->category->name ?? 'Uncategorized';
        })
        ->map(function ($items) {
            return [
                'quantity' => $items->sum('quantity'),
                'total' => $items->sum('total_price'),
            ];
        });

        return $sales->toArray();
    }

    /**
     * Get top selling items
     */
    public function getTopSellingItems($limit = 10, $startDate = null, $endDate = null): array
    {
        $query = PosTransactionItem::select('inventory_id')
            ->selectRaw('SUM(quantity) as total_qty')
            ->selectRaw('SUM(total_price) as total_revenue')
            ->whereHas('transaction', function ($q) use ($startDate, $endDate) {
                $q->completed();
                if ($startDate && $endDate) {
                    $q->dateRange($startDate, $endDate);
                }
            })
            ->groupBy('inventory_id')
            ->orderByDesc('total_qty')
            ->limit($limit)
            ->get();

        return $query->map(function ($item) {
            $inventory = Inventory::find($item->inventory_id);
            return [
                'inventory' => $inventory,
                'total_qty' => $item->total_qty,
                'total_revenue' => $item->total_revenue,
            ];
        })->toArray();
    }

    /**
     * Generate receipt data
     */
    public function generateReceiptData(PosTransaction $transaction): array
    {
        $companyName = Setting::where('key', 'company_name')->value('value') ?? 'Arena Matriks Edu Group';
        $companyAddress = Setting::where('key', 'company_address')->value('value') ?? '';
        $companyPhone = Setting::where('key', 'company_phone')->value('value') ?? '';
        $companyEmail = Setting::where('key', 'company_email')->value('value') ?? '';

        return [
            'company' => [
                'name' => $companyName,
                'address' => $companyAddress,
                'phone' => $companyPhone,
                'email' => $companyEmail,
            ],
            'transaction' => $transaction->load('items.inventory', 'cashier'),
            'generated_at' => now(),
        ];
    }

    /**
     * Get daily reports with filters
     */
    public function getDailyReports($startDate = null, $endDate = null, $status = null)
    {
        $query = DailyCashReport::with('closedBy');

        if ($startDate && $endDate) {
            $query->whereBetween('report_date', [$startDate, $endDate]);
        }

        if ($status) {
            $query->where('status', $status);
        }

        return $query->orderByDesc('report_date')->get();
    }

    /**
     * Get monthly summary
     */
    public function getMonthlySummary($year = null, $month = null): array
    {
        $year = $year ?? now()->year;
        $month = $month ?? now()->month;

        $reports = DailyCashReport::whereYear('report_date', $year)
            ->whereMonth('report_date', $month)
            ->get();

        return [
            'total_cash_sales' => $reports->sum('total_cash_sales'),
            'total_qr_sales' => $reports->sum('total_qr_sales'),
            'total_sales' => $reports->sum('total_cash_sales') + $reports->sum('total_qr_sales'),
            'total_transactions' => $reports->sum('total_transactions'),
            'total_variance' => $reports->sum('variance'),
            'working_days' => $reports->count(),
            'average_daily_sales' => $reports->count() > 0 ? ($reports->sum('total_cash_sales') + $reports->sum('total_qr_sales')) / $reports->count() : 0,
        ];
    }

    /**
     * Check if drawer is open for today
     */
    public function isDrawerOpen(): bool
    {
        $report = $this->getTodayCashReport();
        return $report && $report->status === 'open' && $report->opening_cash > 0;
    }

    /**
     * Get transactions for a specific date
     */
    public function getTransactionsByDate($date)
    {
        return PosTransaction::with(['items.inventory', 'cashier'])
            ->whereDate('transaction_date', $date)
            ->orderByDesc('transaction_date')
            ->get();
    }
}
