<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\TeacherPayslip;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ExpenseService
{
    /**
     * Get filtered expenses
     */
    public function getExpenses(array $filters = [])
    {
        $query = Expense::with(['category', 'createdBy', 'approvedBy']);

        // Apply filters
        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['payment_method'])) {
            $query->where('payment_method', $filters['payment_method']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('expense_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('expense_date', '<=', $filters['date_to']);
        }

        if (!empty($filters['is_recurring'])) {
            $query->where('is_recurring', $filters['is_recurring']);
        }

        if (!empty($filters['vendor_name'])) {
            $query->where('vendor_name', 'like', '%' . $filters['vendor_name'] . '%');
        }

        if (isset($filters['over_budget']) && $filters['over_budget']) {
            $query->overBudget();
        }

        if (!empty($filters['search'])) {
            $query->where(function($q) use ($filters) {
                $q->where('description', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('reference_number', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('vendor_name', 'like', '%' . $filters['search'] . '%');
            });
        }

        // Sort
        $sortBy = $filters['sort_by'] ?? 'expense_date';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        // Paginate or get all
        if (isset($filters['paginate']) && $filters['paginate']) {
            return $query->paginate($filters['per_page'] ?? 15);
        }

        return $query->get();
    }

    /**
     * Create a new expense
     */
    public function createExpense(array $data, $userId)
    {
        DB::beginTransaction();
        try {
            $data['created_by'] = $userId;

            // Handle receipt upload
            if (isset($data['receipt']) && $data['receipt']) {
                $data['receipt_path'] = $data['receipt']->store('expenses/receipts', 'public');
            }

            $expense = Expense::create($data);

            DB::commit();
            return $expense;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update expense
     */
    public function updateExpense(Expense $expense, array $data)
    {
        DB::beginTransaction();
        try {
            // Handle receipt upload
            if (isset($data['receipt']) && $data['receipt']) {
                // Delete old receipt
                if ($expense->receipt_path) {
                    Storage::disk('public')->delete($expense->receipt_path);
                }
                $data['receipt_path'] = $data['receipt']->store('expenses/receipts', 'public');
            }

            $expense->update($data);

            DB::commit();
            return $expense;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Approve expense
     */
    public function approveExpense(Expense $expense, $userId)
    {
        $expense->approve($userId);
        return $expense;
    }

    /**
     * Reject expense
     */
    public function rejectExpense(Expense $expense, $userId, $reason)
    {
        $expense->reject($userId, $reason);
        return $expense;
    }

    /**
     * Delete expense
     */
    public function deleteExpense(Expense $expense)
    {
        // Delete receipt file
        if ($expense->receipt_path) {
            Storage::disk('public')->delete($expense->receipt_path);
        }

        return $expense->delete();
    }

    /**
     * Get expense summary by date range
     */
    public function getExpenseSummary($startDate = null, $endDate = null)
    {
        $startDate = $startDate ?? now()->startOfMonth();
        $endDate = $endDate ?? now()->endOfMonth();

        $query = Expense::approved()->dateRange($startDate, $endDate);

        return [
            'total_expenses' => $query->sum('amount'),
            'expense_count' => $query->count(),
            'average_expense' => $query->avg('amount'),
            'by_category' => $this->getExpensesByCategory($startDate, $endDate),
            'by_payment_method' => $this->getExpensesByPaymentMethod($startDate, $endDate),
            'pending_count' => Expense::pending()->count(),
            'pending_amount' => Expense::pending()->sum('amount'),
        ];
    }

    /**
     * Get expenses by category
     */
    public function getExpensesByCategory($startDate = null, $endDate = null)
    {
        $query = Expense::approved()
            ->join('expense_categories', 'expenses.category_id', '=', 'expense_categories.id')
            ->select('expense_categories.name', DB::raw('SUM(expenses.amount) as total'))
            ->groupBy('expense_categories.id', 'expense_categories.name');

        if ($startDate && $endDate) {
            $query->whereBetween('expenses.expense_date', [$startDate, $endDate]);
        }

        return $query->get();
    }

    /**
     * Get expenses by payment method
     */
    public function getExpensesByPaymentMethod($startDate = null, $endDate = null)
    {
        $query = Expense::approved()
            ->select('payment_method', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('payment_method');

        if ($startDate && $endDate) {
            $query->whereBetween('expense_date', [$startDate, $endDate]);
        }

        return $query->get();
    }

    /**
     * Generate recurring expenses
     */
    public function generateRecurringExpenses()
    {
        $recurringExpenses = Expense::recurring()
            ->approved()
            ->get();

        $generated = [];

        foreach ($recurringExpenses as $expense) {
            $lastExpenseDate = $expense->expense_date;
            $nextDate = $this->calculateNextRecurringDate($lastExpenseDate, $expense->recurring_frequency);

            // Check if it's time to generate
            if ($nextDate->lte(now())) {
                $newExpense = $expense->replicate();
                $newExpense->expense_date = $nextDate;
                $newExpense->status = Expense::STATUS_PENDING;
                $newExpense->approved_by = null;
                $newExpense->approved_at = null;
                $newExpense->save();

                $generated[] = $newExpense;
            }
        }

        return $generated;
    }

    /**
     * Calculate next recurring date
     */
    private function calculateNextRecurringDate(Carbon $lastDate, $frequency)
    {
        return match($frequency) {
            Expense::RECURRING_MONTHLY => $lastDate->copy()->addMonth(),
            Expense::RECURRING_QUARTERLY => $lastDate->copy()->addMonths(3),
            Expense::RECURRING_YEARLY => $lastDate->copy()->addYear(),
            default => $lastDate->copy()->addMonth(),
        };
    }

    /**
     * Sync teacher salaries as expenses
     */
    public function syncTeacherSalaries($month = null, $year = null)
    {
        $month = $month ?? now()->month;
        $year = $year ?? now()->year;

        $payslips = TeacherPayslip::whereMonth('payment_month', $month)
            ->whereYear('payment_month', $year)
            ->where('status', 'paid')
            ->with('teacher.user')
            ->get();

        $salaryCategoryId = ExpenseCategory::firstOrCreate(
            ['name' => 'Teacher Salaries'],
            ['description' => 'Monthly teacher salary payments', 'status' => 'active']
        )->id;

        $synced = [];

        foreach ($payslips as $payslip) {
            // Check if expense already exists for this payslip
            $exists = Expense::where('reference_number', 'PAYSLIP-' . $payslip->id)->exists();

            if (!$exists) {
                $expense = Expense::create([
                    'category_id' => $salaryCategoryId,
                    'description' => 'Salary payment for ' . $payslip->teacher->user->name . ' - ' . $payslip->payment_month->format('F Y'),
                    'amount' => $payslip->net_salary,
                    'expense_date' => $payslip->payment_date ?? $payslip->payment_month,
                    'payment_method' => 'bank_transfer',
                    'reference_number' => 'PAYSLIP-' . $payslip->id,
                    'status' => Expense::STATUS_APPROVED,
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                    'created_by' => auth()->id(),
                    'notes' => 'Auto-synced from teacher payslip',
                ]);

                $synced[] = $expense;
            }
        }

        return $synced;
    }

    /**
     * Get budget vs actual comparison
     */
    public function getBudgetComparison($startDate = null, $endDate = null)
    {
        $startDate = $startDate ?? now()->startOfMonth();
        $endDate = $endDate ?? now()->endOfMonth();

        $expenses = Expense::approved()
            ->dateRange($startDate, $endDate)
            ->whereNotNull('budget_amount')
            ->with('category')
            ->get();

        return [
            'total_budget' => $expenses->sum('budget_amount'),
            'total_actual' => $expenses->sum('amount'),
            'total_variance' => $expenses->sum('amount') - $expenses->sum('budget_amount'),
            'variance_percentage' => $expenses->sum('budget_amount') > 0
                ? (($expenses->sum('amount') - $expenses->sum('budget_amount')) / $expenses->sum('budget_amount')) * 100
                : 0,
            'by_category' => $expenses->groupBy('category.name')->map(function($items, $category) {
                return [
                    'category' => $category,
                    'budget' => $items->sum('budget_amount'),
                    'actual' => $items->sum('amount'),
                    'variance' => $items->sum('amount') - $items->sum('budget_amount'),
                ];
            }),
            'over_budget_items' => $expenses->filter(fn($e) => $e->isOverBudget())->count(),
        ];
    }
}
