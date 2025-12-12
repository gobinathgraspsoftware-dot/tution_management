<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExpenseCategory;
use App\Http\Requests\ExpenseCategoryRequest;
use Illuminate\Http\Request;

class ExpenseCategoryController extends Controller
{
    /**
     * Display expense categories
     */
    public function index(Request $request)
    {
        $query = ExpenseCategory::withCount('expenses');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $categories = $query->orderBy('name')->paginate(20);

        return view('admin.expense-categories.index', compact('categories'));
    }

    /**
     * Show create category form
     */
    public function create()
    {
        return view('admin.expense-categories.create');
    }

    /**
     * Store new category
     */
    public function store(ExpenseCategoryRequest $request)
    {
        try {
            ExpenseCategory::create($request->validated());

            return redirect()
                ->route('admin.expense-categories.index')
                ->with('success', 'Expense category created successfully.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to create category: ' . $e->getMessage());
        }
    }

    /**
     * Show edit category form
     */
    public function edit(ExpenseCategory $expenseCategory)
    {
        return view('admin.expense-categories.edit', compact('expenseCategory'));
    }

    /**
     * Update category
     */
    public function update(ExpenseCategoryRequest $request, ExpenseCategory $expenseCategory)
    {
        try {
            $expenseCategory->update($request->validated());

            return redirect()
                ->route('admin.expense-categories.index')
                ->with('success', 'Expense category updated successfully.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to update category: ' . $e->getMessage());
        }
    }

    /**
     * Delete category
     */
    public function destroy(ExpenseCategory $expenseCategory)
    {
        // Check if category has expenses
        if ($expenseCategory->expenses()->count() > 0) {
            return back()->with('error', 'Cannot delete category with existing expenses.');
        }

        try {
            $expenseCategory->delete();

            return redirect()
                ->route('admin.expense-categories.index')
                ->with('success', 'Expense category deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete category: ' . $e->getMessage());
        }
    }
}
