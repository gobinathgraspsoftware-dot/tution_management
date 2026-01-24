<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\InventoryCategory;
use App\Models\InventoryLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;

class InventoryService
{
    /**
     * Get paginated inventory items with filters
     */
    public function getInventoryItems(array $filters = [], int $perPage = 15)
    {
        $query = Inventory::with('category')
            ->when(isset($filters['search']), function ($q) use ($filters) {
                $q->where(function ($query) use ($filters) {
                    $query->where('name', 'like', '%' . $filters['search'] . '%')
                        ->orWhere('sku', 'like', '%' . $filters['search'] . '%')
                        ->orWhere('description', 'like', '%' . $filters['search'] . '%');
                });
            })
            ->when(isset($filters['category_id']) && $filters['category_id'], function ($q) use ($filters) {
                $q->where('category_id', $filters['category_id']);
            })
            ->when(isset($filters['status']) && $filters['status'], function ($q) use ($filters) {
                $q->where('status', $filters['status']);
            })
            ->when(isset($filters['low_stock']) && $filters['low_stock'], function ($q) {
                $q->lowStock();
            })
            ->when(isset($filters['sort_by']), function ($q) use ($filters) {
                $direction = $filters['sort_direction'] ?? 'asc';
                $q->orderBy($filters['sort_by'], $direction);
            }, function ($q) {
                $q->orderBy('name', 'asc');
            });

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Create new inventory item
     */
    public function createInventory(array $data): Inventory
    {
        return DB::transaction(function () use ($data) {
            // Generate SKU if not provided
            if (empty($data['sku'])) {
                $data['sku'] = $this->generateSku($data['name'], $data['category_id']);
            }

            // Handle image upload
            if (isset($data['image']) && $data['image']) {
                $data['image'] = $this->uploadImage($data['image']);
            }

            $inventory = Inventory::create($data);

            // Log initial stock if provided
            if (isset($data['current_stock']) && $data['current_stock'] > 0) {
                $this->logStockChange($inventory, 'add', $data['current_stock'], 0, $data['current_stock'], 'Initial stock');
            }

            return $inventory;
        });
    }

    /**
     * Update inventory item
     */
    public function updateInventory(Inventory $inventory, array $data): Inventory
    {
        return DB::transaction(function () use ($inventory, $data) {
            // Handle image upload
            if (isset($data['image']) && $data['image']) {
                // Delete old image
                $this->deleteImage($inventory->image);
                $data['image'] = $this->uploadImage($data['image']);
            }

            $inventory->update($data);

            return $inventory->fresh();
        });
    }

    /**
     * Delete inventory item
     */
    public function deleteInventory(Inventory $inventory): bool
    {
        return DB::transaction(function () use ($inventory) {
            // Delete image
            $this->deleteImage($inventory->image);

            // Delete logs
            $inventory->logs()->delete();

            return $inventory->delete();
        });
    }

    /**
     * Adjust stock level
     */
    public function adjustStock(Inventory $inventory, string $type, int $quantity, ?string $reference = null, ?string $notes = null): InventoryLog
    {
        return DB::transaction(function () use ($inventory, $type, $quantity, $reference, $notes) {
            $previousStock = $inventory->current_stock;

            switch ($type) {
                case 'add':
                    $newStock = $previousStock + $quantity;
                    break;
                case 'remove':
                    $newStock = max(0, $previousStock - $quantity);
                    break;
                case 'adjust':
                    $newStock = $quantity;
                    $quantity = abs($newStock - $previousStock);
                    break;
                case 'sale':
                    $newStock = max(0, $previousStock - $quantity);
                    break;
                default:
                    throw new \InvalidArgumentException("Invalid stock adjustment type: {$type}");
            }

            // Update inventory stock
            $inventory->update([
                'current_stock' => $newStock,
                'status' => $newStock <= 0 ? 'out_of_stock' : ($newStock <= $inventory->reorder_level ? 'active' : 'active'),
            ]);

            // Log the change
            return $this->logStockChange($inventory, $type, $quantity, $previousStock, $newStock, $notes, $reference);
        });
    }

    /**
     * Bulk stock adjustment
     */
    public function bulkAdjustStock(array $adjustments): array
    {
        $results = [];

        DB::transaction(function () use ($adjustments, &$results) {
            foreach ($adjustments as $adjustment) {
                $inventory = Inventory::find($adjustment['inventory_id']);
                if ($inventory) {
                    $results[] = $this->adjustStock(
                        $inventory,
                        $adjustment['type'],
                        $adjustment['quantity'],
                        $adjustment['reference'] ?? null,
                        $adjustment['notes'] ?? null
                    );
                }
            }
        });

        return $results;
    }

    /**
     * Log stock change
     */
    protected function logStockChange(
        Inventory $inventory,
        string $type,
        int $quantity,
        int $previousStock,
        int $newStock,
        ?string $notes = null,
        ?string $reference = null
    ): InventoryLog {
        return InventoryLog::create([
            'inventory_id' => $inventory->id,
            'type' => $type,
            'quantity' => $quantity,
            'previous_stock' => $previousStock,
            'new_stock' => $newStock,
            'reference' => $reference,
            'notes' => $notes,
            'created_by' => Auth::id(),
        ]);
    }

    /**
     * Get low stock items
     */
    public function getLowStockItems(int $limit = null)
    {
        $query = Inventory::with('category')
            ->lowStock()
            ->active()
            ->orderBy('current_stock', 'asc');

        if ($limit) {
            return $query->limit($limit)->get();
        }

        return $query->get();
    }

    /**
     * Get out of stock items
     */
    public function getOutOfStockItems()
    {
        return Inventory::with('category')
            ->where('current_stock', 0)
            ->active()
            ->orderBy('name')
            ->get();
    }

    /**
     * Get stock history for an item
     */
    public function getStockHistory(Inventory $inventory, array $filters = [], int $perPage = 20)
    {
        $query = $inventory->logs()
            ->with('createdBy')
            ->when(isset($filters['type']) && $filters['type'], function ($q) use ($filters) {
                $q->where('type', $filters['type']);
            })
            ->when(isset($filters['date_from']) && $filters['date_from'], function ($q) use ($filters) {
                $q->whereDate('created_at', '>=', $filters['date_from']);
            })
            ->when(isset($filters['date_to']) && $filters['date_to'], function ($q) use ($filters) {
                $q->whereDate('created_at', '<=', $filters['date_to']);
            })
            ->orderBy('created_at', 'desc');

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Get inventory statistics
     */
    public function getStatistics(): array
    {
        $totalItems = Inventory::count();
        $activeItems = Inventory::active()->count();
        $lowStockItems = Inventory::lowStock()->active()->count();
        $outOfStockItems = Inventory::where('current_stock', 0)->count();

        $totalValue = Inventory::sum(DB::raw('current_stock * cost_price'));
        $totalRetailValue = Inventory::sum(DB::raw('current_stock * selling_price'));

        return [
            'total_items' => $totalItems,
            'active_items' => $activeItems,
            'low_stock_items' => $lowStockItems,
            'out_of_stock_items' => $outOfStockItems,
            'total_cost_value' => $totalValue,
            'total_retail_value' => $totalRetailValue,
            'potential_profit' => $totalRetailValue - $totalValue,
        ];
    }

    /**
     * Get category statistics
     */
    public function getCategoryStatistics(): array
    {
        return InventoryCategory::withCount(['inventoryItems', 'inventoryItems as low_stock_count' => function ($q) {
            $q->whereColumn('current_stock', '<=', 'reorder_level');
        }])
            ->withSum('inventoryItems', DB::raw('current_stock * cost_price'))
            ->active()
            ->get()
            ->toArray();
    }

    /**
     * Generate unique SKU
     */
    protected function generateSku(string $name, int $categoryId): string
    {
        $prefix = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $name), 0, 3));
        $categoryPrefix = str_pad($categoryId, 2, '0', STR_PAD_LEFT);
        $uniqueId = strtoupper(Str::random(4));

        $sku = "{$prefix}-{$categoryPrefix}-{$uniqueId}";

        // Ensure uniqueness
        while (Inventory::where('sku', $sku)->exists()) {
            $uniqueId = strtoupper(Str::random(4));
            $sku = "{$prefix}-{$categoryPrefix}-{$uniqueId}";
        }

        return $sku;
    }

    /**
     * Upload image
     */
    protected function uploadImage($image): string
    {
        $filename = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
        $path = $image->storeAs('inventory', $filename, 'public');
        return $path;
    }

    /**
     * Delete image
     */
    protected function deleteImage(?string $path): void
    {
        if ($path && \Storage::disk('public')->exists($path)) {
            \Storage::disk('public')->delete($path);
        }
    }

    /**
     * Get movement report
     */
    public function getMovementReport(Carbon $startDate, Carbon $endDate, ?int $categoryId = null): array
    {
        $query = InventoryLog::with(['inventory.category', 'createdBy'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->when($categoryId, function ($q) use ($categoryId) {
                $q->whereHas('inventory', function ($query) use ($categoryId) {
                    $query->where('category_id', $categoryId);
                });
            });

        $logs = $query->get();

        $summary = [
            'total_added' => $logs->where('type', 'add')->sum('quantity'),
            'total_removed' => $logs->where('type', 'remove')->sum('quantity'),
            'total_adjusted' => $logs->where('type', 'adjust')->count(),
            'total_sales' => $logs->where('type', 'sale')->sum('quantity'),
        ];

        $byType = $logs->groupBy('type')->map(function ($group) {
            return [
                'count' => $group->count(),
                'total_quantity' => $group->sum('quantity'),
            ];
        });

        $byCategory = $logs->groupBy('inventory.category.name')->map(function ($group) {
            return [
                'count' => $group->count(),
                'total_quantity' => $group->sum('quantity'),
            ];
        });

        return [
            'summary' => $summary,
            'by_type' => $byType,
            'by_category' => $byCategory,
            'details' => $logs,
        ];
    }

    /**
     * Get valuation report
     */
    public function getValuationReport(?int $categoryId = null): array
    {
        $query = Inventory::with('category')
            ->when($categoryId, function ($q) use ($categoryId) {
                $q->where('category_id', $categoryId);
            })
            ->active();

        $items = $query->get();

        $totalCostValue = $items->sum(function ($item) {
            return $item->current_stock * $item->cost_price;
        });

        $totalRetailValue = $items->sum(function ($item) {
            return $item->current_stock * $item->selling_price;
        });

        $byCategory = $items->groupBy('category.name')->map(function ($group) {
            return [
                'items_count' => $group->count(),
                'total_stock' => $group->sum('current_stock'),
                'cost_value' => $group->sum(function ($item) {
                    return $item->current_stock * $item->cost_price;
                }),
                'retail_value' => $group->sum(function ($item) {
                    return $item->current_stock * $item->selling_price;
                }),
            ];
        });

        return [
            'total_items' => $items->count(),
            'total_stock_units' => $items->sum('current_stock'),
            'total_cost_value' => $totalCostValue,
            'total_retail_value' => $totalRetailValue,
            'potential_profit' => $totalRetailValue - $totalCostValue,
            'profit_margin' => $totalCostValue > 0 ? (($totalRetailValue - $totalCostValue) / $totalCostValue) * 100 : 0,
            'by_category' => $byCategory,
            'items' => $items,
        ];
    }
}
