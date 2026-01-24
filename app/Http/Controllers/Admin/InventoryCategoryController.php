<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\InventoryCategoryRequest;
use App\Models\InventoryCategory;
use Illuminate\Http\Request;

class InventoryCategoryController extends Controller
{
    /**
     * Display a listing of categories.
     */
    public function index(Request $request)
    {
        $categories = InventoryCategory::withCount('inventoryItems')
            ->when($request->search, function ($q, $search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            })
            ->when($request->status, function ($q, $status) {
                $q->where('status', $status);
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.inventory.categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new category.
     */
    public function create()
    {
        return view('admin.inventory.categories.create');
    }

    /**
     * Store a newly created category.
     */
    public function store(InventoryCategoryRequest $request)
    {
        try {
            InventoryCategory::create($request->validated());

            return redirect()
                ->route('admin.inventory-categories.index')
                ->with('success', 'Category created successfully.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to create category: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified category.
     */
    public function edit(InventoryCategory $category)
    {
        return view('admin.inventory.categories.edit', compact('category'));
    }

    /**
     * Update the specified category.
     */
    public function update(InventoryCategoryRequest $request, InventoryCategory $category)
    {
        try {
            $category->update($request->validated());

            return redirect()
                ->route('admin.inventory-categories.index')
                ->with('success', 'Category updated successfully.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to update category: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified category.
     */
    public function destroy(InventoryCategory $category)
    {
        // Check if category has items
        if ($category->inventoryItems()->exists()) {
            return back()->with('error', 'Cannot delete category with existing items. Please move or delete items first.');
        }

        try {
            $category->delete();

            return redirect()
                ->route('admin.inventory-categories.index')
                ->with('success', 'Category deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete category: ' . $e->getMessage());
        }
    }

    /**
     * Toggle category status
     */
    public function toggleStatus(InventoryCategory $category)
    {
        $category->status = $category->status === 'active' ? 'inactive' : 'active';
        $category->save();

        return back()->with('success', 'Category status updated successfully.');
    }

    /**
     * Get categories for AJAX
     */
    public function getCategories(Request $request)
    {
        $categories = InventoryCategory::active()
            ->orderBy('name')
            ->get(['id', 'name', 'description']);

        return response()->json($categories);
    }
}
