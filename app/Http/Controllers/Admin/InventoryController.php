<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\InventoryRequest;
use App\Http\Requests\StockAdjustmentRequest;
use App\Models\Inventory;
use App\Models\InventoryCategory;
use App\Services\InventoryService;
use App\Services\StockAlertService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    protected $inventoryService;
    protected $stockAlertService;

    public function __construct(InventoryService $inventoryService, StockAlertService $stockAlertService)
    {
        $this->inventoryService = $inventoryService;
        $this->stockAlertService = $stockAlertService;
    }

    /**
     * Display a listing of inventory items.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'category_id', 'status', 'low_stock', 'sort_by', 'sort_direction']);
        $perPage = $request->get('per_page', 15);

        $items = $this->inventoryService->getInventoryItems($filters, $perPage);
        $categories = InventoryCategory::active()->orderBy('name')->get();
        $statistics = $this->inventoryService->getStatistics();

        return view('admin.inventory.index', compact('items', 'categories', 'statistics', 'filters'));
    }

    /**
     * Show the form for creating a new inventory item.
     */
    public function create()
    {
        $categories = InventoryCategory::active()->orderBy('name')->get();

        return view('admin.inventory.create', compact('categories'));
    }

    /**
     * Store a newly created inventory item.
     */
    public function store(InventoryRequest $request)
    {
        try {
            $inventory = $this->inventoryService->createInventory($request->validated());

            return redirect()
                ->route('admin.inventory.show', $inventory)
                ->with('success', 'Inventory item created successfully.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to create inventory item: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified inventory item.
     */
    public function show(Inventory $inventory)
    {
        $inventory->load('category', 'logs.createdBy');

        $recentLogs = $inventory->logs()
            ->with('createdBy')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $stockTrend = $this->stockAlertService->getStockTrend($inventory, 30);
        $avgConsumption = $this->stockAlertService->calculateAverageConsumption($inventory);
        $daysUntilOut = $this->stockAlertService->estimateDaysUntilOutOfStock($inventory);

        return view('admin.inventory.show', compact('inventory', 'recentLogs', 'stockTrend', 'avgConsumption', 'daysUntilOut'));
    }

    /**
     * Show the form for editing the specified inventory item.
     */
    public function edit(Inventory $inventory)
    {
        $categories = InventoryCategory::active()->orderBy('name')->get();

        return view('admin.inventory.edit', compact('inventory', 'categories'));
    }

    /**
     * Update the specified inventory item.
     */
    public function update(InventoryRequest $request, Inventory $inventory)
    {
        try {
            $this->inventoryService->updateInventory($inventory, $request->validated());

            return redirect()
                ->route('admin.inventory.show', $inventory)
                ->with('success', 'Inventory item updated successfully.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to update inventory item: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified inventory item.
     */
    public function destroy(Inventory $inventory)
    {
        try {
            // Check if item has transactions
            if ($inventory->transactionItems()->exists()) {
                return back()->with('error', 'Cannot delete item with existing transactions.');
            }

            $this->inventoryService->deleteInventory($inventory);

            return redirect()
                ->route('admin.inventory.index')
                ->with('success', 'Inventory item deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete inventory item: ' . $e->getMessage());
        }
    }

    /**
     * Show stock adjustment form
     */
    public function adjustStock(Inventory $inventory)
    {
        $recentLogs = $inventory->logs()
            ->with('createdBy')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.inventory.stock.adjust', compact('inventory', 'recentLogs'));
    }

    /**
     * Process stock adjustment
     */
    public function processAdjustment(StockAdjustmentRequest $request, Inventory $inventory)
    {
        try {
            $this->inventoryService->adjustStock(
                $inventory,
                $request->type,
                $request->quantity,
                $request->reference,
                $request->notes
            );

            return redirect()
                ->route('admin.inventory.show', $inventory)
                ->with('success', 'Stock adjusted successfully.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to adjust stock: ' . $e->getMessage());
        }
    }

    /**
     * Show stock history
     */
    public function stockHistory(Request $request, Inventory $inventory)
    {
        $filters = $request->only(['type', 'date_from', 'date_to']);
        $logs = $this->inventoryService->getStockHistory($inventory, $filters);

        return view('admin.inventory.stock.history', compact('inventory', 'logs', 'filters'));
    }

    /**
     * Show low stock items
     */
    public function lowStock(Request $request)
    {
        $lowStockItems = $this->stockAlertService->getLowStockItems();
        $outOfStockItems = $this->stockAlertService->getOutOfStockItems();
        $criticalItems = $this->stockAlertService->getCriticalStockItems();
        $reorderSuggestions = $this->stockAlertService->getReorderSuggestions();
        $stockSummary = $this->stockAlertService->getStockSummary();

        return view('admin.inventory.stock.low-stock', compact(
            'lowStockItems',
            'outOfStockItems',
            'criticalItems',
            'reorderSuggestions',
            'stockSummary'
        ));
    }

    /**
     * Bulk stock adjustment
     */
    public function bulkAdjust(Request $request)
    {
        $items = Inventory::active()->orderBy('name')->get();
        $categories = InventoryCategory::active()->orderBy('name')->get();

        if ($request->isMethod('POST')) {
            $validated = $request->validate([
                'adjustments' => ['required', 'array', 'min:1'],
                'adjustments.*.inventory_id' => ['required', 'exists:inventory,id'],
                'adjustments.*.type' => ['required', 'in:add,remove,adjust'],
                'adjustments.*.quantity' => ['required', 'integer', 'min:1'],
                'adjustments.*.notes' => ['nullable', 'string', 'max:500'],
            ]);

            try {
                $this->inventoryService->bulkAdjustStock($validated['adjustments']);

                return redirect()
                    ->route('admin.inventory.index')
                    ->with('success', 'Bulk stock adjustment completed successfully.');
            } catch (\Exception $e) {
                return back()
                    ->withInput()
                    ->with('error', 'Failed to process bulk adjustment: ' . $e->getMessage());
            }
        }

        return view('admin.inventory.stock.bulk-adjust', compact('items', 'categories'));
    }

    /**
     * Export inventory
     */
    public function export(Request $request)
    {
        $filters = $request->only(['category_id', 'status', 'low_stock']);

        return (new \App\Exports\InventoryExport($filters))
            ->download('inventory_' . date('Y-m-d_His') . '.xlsx');
    }

    /**
     * Toggle item status
     */
    public function toggleStatus(Inventory $inventory)
    {
        $inventory->status = $inventory->status === 'active' ? 'inactive' : 'active';
        $inventory->save();

        return back()->with('success', 'Item status updated successfully.');
    }

    /**
     * Get inventory data for AJAX
     */
    public function getItems(Request $request)
    {
        $search = $request->get('search', '');
        $categoryId = $request->get('category_id');

        $items = Inventory::with('category')
            ->active()
            ->when($search, function ($q) use ($search) {
                $q->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%");
                });
            })
            ->when($categoryId, function ($q) use ($categoryId) {
                $q->where('category_id', $categoryId);
            })
            ->orderBy('name')
            ->limit(50)
            ->get();

        return response()->json($items);
    }
}
