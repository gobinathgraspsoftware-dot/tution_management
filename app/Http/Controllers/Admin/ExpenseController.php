<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Http\Requests\ExpenseRequest;
use App\Services\ExpenseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ExpenseController extends Controller
{
    protected $expenseService;

    public function __construct(ExpenseService $expenseService)
    {
        $this->expenseService = $expenseService;
    }

    /**
     * Display expense listing
     */
    public function index(Request $request)
    {
        $filters = $request->only([
            'category_id', 'status', 'payment_method',
            'date_from', 'date_to', 'is_recurring', 'vendor_name',
            'over_budget', 'search'
        ]);
        $filters['paginate'] = true;
        $filters['per_page'] = 20;

        $expenses = $this->expenseService->getExpenses($filters);
        $categories = ExpenseCategory::active()->orderBy('name')->get();

        $summary = $this->expenseService->getExpenseSummary(
            $request->date_from ?? now()->startOfMonth(),
            $request->date_to ?? now()->endOfMonth()
        );

        return view('admin.expenses.index', compact('expenses', 'categories', 'summary'));
    }

    /**
     * Show create expense form
     */
    public function create()
    {
        $categories = ExpenseCategory::active()->orderBy('name')->get();
        $statuses = Expense::getStatuses();
        $paymentMethods = Expense::getPaymentMethods();
        $recurringFrequencies = Expense::getRecurringFrequencies();

        return view('admin.expenses.create', compact(
            'categories',
            'statuses',
            'paymentMethods',
            'recurringFrequencies'
        ));
    }

    /**
     * Store new expense
     */
    public function store(ExpenseRequest $request)
    {
        try {
            $expense = $this->expenseService->createExpense(
                $request->validated(),
                auth()->id()
            );

            return redirect()
                ->route('admin.expenses.show', $expense)
                ->with('success', 'Expense created successfully.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to create expense: ' . $e->getMessage());
        }
    }

    /**
     * Show expense details
     */
    public function show(Expense $expense)
    {
        $expense->load(['category', 'createdBy', 'approvedBy']);

        return view('admin.expenses.show', compact('expense'));
    }

    /**
     * Show edit expense form
     */
    public function edit(Expense $expense)
    {
        // Check if expense is approved/paid - cannot edit
        if (in_array($expense->status, [Expense::STATUS_APPROVED, Expense::STATUS_PAID])) {
            return redirect()
                ->route('admin.expenses.show', $expense)
                ->with('error', 'Cannot edit approved or paid expenses.');
        }

        $categories = ExpenseCategory::active()->orderBy('name')->get();
        $statuses = Expense::getStatuses();
        $paymentMethods = Expense::getPaymentMethods();
        $recurringFrequencies = Expense::getRecurringFrequencies();

        return view('admin.expenses.edit', compact(
            'expense',
            'categories',
            'statuses',
            'paymentMethods',
            'recurringFrequencies'
        ));
    }

    /**
     * Update expense
     */
    public function update(ExpenseRequest $request, Expense $expense)
    {
        // Check if expense is approved/paid - cannot edit
        if (in_array($expense->status, [Expense::STATUS_APPROVED, Expense::STATUS_PAID])) {
            return redirect()
                ->route('admin.expenses.show', $expense)
                ->with('error', 'Cannot edit approved or paid expenses.');
        }

        try {
            $this->expenseService->updateExpense($expense, $request->validated());

            return redirect()
                ->route('admin.expenses.show', $expense)
                ->with('success', 'Expense updated successfully.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to update expense: ' . $e->getMessage());
        }
    }

    /**
     * Approve expense
     */
    public function approve(Expense $expense)
    {
        if (!$expense->isPending()) {
            return back()->with('error', 'Only pending expenses can be approved.');
        }

        try {
            $this->expenseService->approveExpense($expense, auth()->id());

            return back()->with('success', 'Expense approved successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to approve expense: ' . $e->getMessage());
        }
    }

    /**
     * Reject expense
     */
    public function reject(Request $request, Expense $expense)
    {
        if (!$expense->isPending()) {
            return back()->with('error', 'Only pending expenses can be rejected.');
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:500'
        ]);

        try {
            $this->expenseService->rejectExpense(
                $expense,
                auth()->id(),
                $request->rejection_reason
            );

            return back()->with('success', 'Expense rejected successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to reject expense: ' . $e->getMessage());
        }
    }

    /**
     * Delete expense
     */
    public function destroy(Expense $expense)
    {
        // Only pending or rejected expenses can be deleted
        if (!in_array($expense->status, [Expense::STATUS_PENDING, Expense::STATUS_REJECTED])) {
            return back()->with('error', 'Cannot delete approved or paid expenses.');
        }

        try {
            $this->expenseService->deleteExpense($expense);

            return redirect()
                ->route('admin.expenses.index')
                ->with('success', 'Expense deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete expense: ' . $e->getMessage());
        }
    }

    /**
     * Download receipt
     */
    public function downloadReceipt(Expense $expense)
    {
        if (!$expense->receipt_path) {
            return back()->with('error', 'No receipt available for this expense.');
        }

        if (!Storage::disk('public')->exists($expense->receipt_path)) {
            return back()->with('error', 'Receipt file not found.');
        }

        return Storage::disk('public')->download($expense->receipt_path);
    }

    /**
     * Export expenses
     */
    public function export(Request $request)
    {
        $filters = $request->only([
            'category_id', 'status', 'payment_method',
            'date_from', 'date_to', 'is_recurring', 'vendor_name'
        ]);

        $expenses = $this->expenseService->getExpenses($filters);

        $filename = 'expenses_' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($expenses) {
            $file = fopen('php://output', 'w');

            // Header row
            fputcsv($file, [
                'Date', 'Category', 'Description', 'Amount',
                'Payment Method', 'Reference', 'Vendor', 'Status',
                'Approved By', 'Budget', 'Variance'
            ]);

            // Data rows
            foreach ($expenses as $expense) {
                fputcsv($file, [
                    $expense->expense_date->format('Y-m-d'),
                    $expense->category->name,
                    $expense->description,
                    number_format($expense->amount, 2),
                    $expense->payment_method,
                    $expense->reference_number ?? '',
                    $expense->vendor_name ?? '',
                    $expense->status,
                    $expense->approvedBy->name ?? '',
                    $expense->budget_amount ? number_format($expense->budget_amount, 2) : '',
                    $expense->budget_amount ? number_format($expense->getVarianceAmount(), 2) : '',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Generate recurring expenses (manual trigger)
     */
    public function generateRecurring()
    {
        try {
            $generated = $this->expenseService->generateRecurringExpenses();

            return back()->with('success', count($generated) . ' recurring expense(s) generated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to generate recurring expenses: ' . $e->getMessage());
        }
    }

    /**
     * Sync teacher salaries (manual trigger)
     */
    public function syncTeacherSalaries(Request $request)
    {
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        try {
            $synced = $this->expenseService->syncTeacherSalaries($month, $year);

            return back()->with('success', count($synced) . ' teacher salary expense(s) synced successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to sync teacher salaries: ' . $e->getMessage());
        }
    }
}
