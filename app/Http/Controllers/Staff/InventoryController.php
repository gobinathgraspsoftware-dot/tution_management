<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\StockAdjustmentRequest;
use App\Models\Inventory;
use App\Models\InventoryCategory;
use App\Services\InventoryService;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    protected $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Display inventory listing for staff
     */
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'category_id', 'low_stock']);
        $filters['status'] = 'active'; // Staff can only see active items

        $items = $this->inventoryService->getInventoryItems($filters, 20);
        $categories = InventoryCategory::active()->orderBy('name')->get();

        // Get low stock count for alert
        $lowStockCount = Inventory::lowStock()->active()->count();

        return view('staff.inventory.index', compact('items', 'categories', 'filters', 'lowStockCount'));
    }

    /**
     * Show stock adjustment form for a specific item
     */
    public function adjustStock(Inventory $inventory)
    {
        $recentLogs = $inventory->logs()
            ->with('createdBy')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('staff.inventory.stock-adjust', compact('inventory', 'recentLogs'));
    }

    /**
     * Process stock adjustment from staff
     */
    public function processAdjustment(StockAdjustmentRequest $request, Inventory $inventory)
    {
        try {
            // Staff can only add or remove stock, not set arbitrary values
            if (!in_array($request->type, ['add', 'remove'])) {
                return back()->with('error', 'Invalid adjustment type for staff.');
            }

            $this->inventoryService->adjustStock(
                $inventory,
                $request->type,
                $request->quantity,
                $request->reference,
                $request->notes
            );

            return redirect()
                ->route('staff.inventory.index')
                ->with('success', 'Stock adjusted successfully for ' . $inventory->name);
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to adjust stock: ' . $e->getMessage());
        }
    }

    /**
     * Quick stock update (AJAX)
     */
    public function quickUpdate(Request $request, Inventory $inventory)
    {
        $validated = $request->validate([
            'type' => ['required', 'in:add,remove'],
            'quantity' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:200'],
        ]);

        try {
            $log = $this->inventoryService->adjustStock(
                $inventory,
                $validated['type'],
                $validated['quantity'],
                null,
                $validated['notes'] ?? 'Quick update via staff panel'
            );

            return response()->json([
                'success' => true,
                'message' => 'Stock updated successfully',
                'new_stock' => $inventory->fresh()->current_stock,
                'log' => $log,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get item details (AJAX)
     */
    public function getItem(Inventory $inventory)
    {
        $inventory->load('category');

        return response()->json([
            'id' => $inventory->id,
            'name' => $inventory->name,
            'sku' => $inventory->sku,
            'category' => $inventory->category->name ?? 'N/A',
            'current_stock' => $inventory->current_stock,
            'reorder_level' => $inventory->reorder_level,
            'unit' => $inventory->unit,
            'selling_price' => $inventory->selling_price,
            'image_url' => $inventory->image ? \Storage::url($inventory->image) : null,
            'is_low_stock' => $inventory->current_stock <= $inventory->reorder_level,
        ]);
    }

    /**
     * Search items (AJAX)
     */
    public function search(Request $request)
    {
        $search = $request->get('q', '');

        $items = Inventory::with('category')
            ->active()
            ->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->limit(20)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'sku' => $item->sku,
                    'category' => $item->category->name ?? 'N/A',
                    'current_stock' => $item->current_stock,
                    'unit' => $item->unit,
                    'is_low_stock' => $item->current_stock <= $item->reorder_level,
                ];
            });

        return response()->json($items);
    }
}
