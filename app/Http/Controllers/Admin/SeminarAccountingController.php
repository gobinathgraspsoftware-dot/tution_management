<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SeminarExpenseRequest;
use App\Models\Seminar;
use App\Models\SeminarExpense;
use App\Models\ActivityLog;
use App\Services\SeminarAccountingService;
use App\Exports\SeminarFinancialExport;
use App\Exports\SeminarExpenseExport;
use App\Exports\SeminarProfitabilityExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class SeminarAccountingController extends Controller
{
    protected $accountingService;

    public function __construct(SeminarAccountingService $accountingService)
    {
        $this->accountingService = $accountingService;
    }

    /**
     * Seminar accounting dashboard
     */
    public function dashboard(Request $request)
    {
        $filters = [
            'date_from' => $request->date_from ?? now()->startOfMonth()->toDateString(),
            'date_to' => $request->date_to ?? now()->endOfMonth()->toDateString(),
        ];

        // Get profitability overview
        $profitability = $this->accountingService->getProfitabilityReport($filters);
        
        // Get payment tracking
        $paymentTracking = $this->accountingService->getPaymentStatusTracking($filters);
        
        // Get expense statistics
        $expenseStats = $this->accountingService->getExpenseStatistics(
            $filters['date_from'],
            $filters['date_to']
        );

        // Recent expenses needing approval
        $pendingExpenses = SeminarExpense::with(['seminar'])
            ->where('approval_status', 'pending')
            ->latest('expense_date')
            ->take(10)
            ->get();

        return view('admin.seminars.accounting.dashboard', compact(
            'profitability',
            'paymentTracking',
            'expenseStats',
            'pendingExpenses',
            'filters'
        ));
    }

    // ==================== EXPENSE MANAGEMENT ====================

    /**
     * Display expense list for a seminar
     */
    public function expenses(Seminar $seminar)
    {
        $expenses = $seminar->expenses()
            ->latest('expense_date')
            ->paginate(20);

        $summary = [
            'total' => $seminar->expenses()->sum('amount'),
            'approved' => $seminar->expenses()->where('approval_status', 'approved')->sum('amount'),
            'pending' => $seminar->expenses()->where('approval_status', 'pending')->sum('amount'),
            'rejected' => $seminar->expenses()->where('approval_status', 'rejected')->sum('amount'),
            'count' => $seminar->expenses()->count(),
        ];

        return view('admin.seminars.accounting.expenses.index', compact('seminar', 'expenses', 'summary'));
    }

    /**
     * Show create expense form
     */
    public function createExpense(Seminar $seminar)
    {
        $categories = SeminarAccountingService::getExpenseCategories();
        return view('admin.seminars.accounting.expenses.create', compact('seminar', 'categories'));
    }

    /**
     * Store new expense
     */
    public function storeExpense(SeminarExpenseRequest $request, Seminar $seminar)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();
            $data['seminar_id'] = $seminar->id;
            $data['approval_status'] = 'pending';
            $data['created_by'] = auth()->id();

            // Handle receipt upload
            if ($request->hasFile('receipt')) {
                $data['receipt_path'] = $request->file('receipt')->store('seminar-receipts', 'public');
            }

            $expense = SeminarExpense::create($data);

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'create',
                'model_type' => 'SeminarExpense',
                'model_id' => $expense->id,
                'description' => "Added expense for seminar: {$seminar->name} - " . 
                                SeminarAccountingService::getCategoryLabel($expense->category) . 
                                " (RM " . number_format($expense->amount, 2) . ")",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            DB::commit();
            return redirect()->route('admin.seminars.accounting.expenses', $seminar)
                ->with('success', 'Expense added successfully and pending approval.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to add expense: ' . $e->getMessage());
        }
    }

    /**
     * Show edit expense form
     */
    public function editExpense(Seminar $seminar, SeminarExpense $expense)
    {
        // Only allow editing pending expenses
        if ($expense->approval_status !== 'pending') {
            return back()->with('error', 'Cannot edit approved or rejected expenses.');
        }

        $categories = SeminarAccountingService::getExpenseCategories();
        return view('admin.seminars.accounting.expenses.edit', compact('seminar', 'expense', 'categories'));
    }

    /**
     * Update expense
     */
    public function updateExpense(SeminarExpenseRequest $request, Seminar $seminar, SeminarExpense $expense)
    {
        // Only allow editing pending expenses
        if ($expense->approval_status !== 'pending') {
            return back()->with('error', 'Cannot edit approved or rejected expenses.');
        }

        try {
            DB::beginTransaction();

            $data = $request->validated();

            // Handle receipt upload
            if ($request->hasFile('receipt')) {
                // Delete old receipt
                if ($expense->receipt_path) {
                    Storage::disk('public')->delete($expense->receipt_path);
                }
                $data['receipt_path'] = $request->file('receipt')->store('seminar-receipts', 'public');
            }

            $expense->update($data);

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'update',
                'model_type' => 'SeminarExpense',
                'model_id' => $expense->id,
                'description' => "Updated expense for seminar: {$seminar->name} - " . 
                                SeminarAccountingService::getCategoryLabel($expense->category),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            DB::commit();
            return redirect()->route('admin.seminars.accounting.expenses', $seminar)
                ->with('success', 'Expense updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to update expense: ' . $e->getMessage());
        }
    }

    /**
     * Delete expense
     */
    public function destroyExpense(Seminar $seminar, SeminarExpense $expense)
    {
        // Only allow deleting pending expenses
        if ($expense->approval_status !== 'pending') {
            return back()->with('error', 'Cannot delete approved or rejected expenses.');
        }

        try {
            DB::beginTransaction();

            // Delete receipt file
            if ($expense->receipt_path) {
                Storage::disk('public')->delete($expense->receipt_path);
            }

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'delete',
                'model_type' => 'SeminarExpense',
                'model_id' => $expense->id,
                'description' => "Deleted expense for seminar: {$seminar->name} - " . 
                                SeminarAccountingService::getCategoryLabel($expense->category) . 
                                " (RM " . number_format($expense->amount, 2) . ")",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            $expense->delete();

            DB::commit();
            return redirect()->route('admin.seminars.accounting.expenses', $seminar)
                ->with('success', 'Expense deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete expense: ' . $e->getMessage());
        }
    }

    /**
     * Approve expense
     */
    public function approveExpense(Request $request, Seminar $seminar, SeminarExpense $expense)
    {
        if ($expense->approval_status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'This expense has already been processed.'
            ], 400);
        }

        try {
            $this->accountingService->approveExpense($expense, auth()->id());

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'approve',
                'model_type' => 'SeminarExpense',
                'model_id' => $expense->id,
                'description' => "Approved expense for seminar: {$seminar->name} - " . 
                                SeminarAccountingService::getCategoryLabel($expense->category) . 
                                " (RM " . number_format($expense->amount, 2) . ")",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Expense approved successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve expense: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject expense
     */
    public function rejectExpense(Request $request, Seminar $seminar, SeminarExpense $expense)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500'
        ]);

        if ($expense->approval_status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'This expense has already been processed.'
            ], 400);
        }

        try {
            $this->accountingService->rejectExpense(
                $expense,
                auth()->id(),
                $request->rejection_reason
            );

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'reject',
                'model_type' => 'SeminarExpense',
                'model_id' => $expense->id,
                'description' => "Rejected expense for seminar: {$seminar->name} - " . 
                                SeminarAccountingService::getCategoryLabel($expense->category) . 
                                " - Reason: {$request->rejection_reason}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Expense rejected successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject expense: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete receipt
     */
    public function deleteReceipt(Seminar $seminar, SeminarExpense $expense)
    {
        try {
            $this->accountingService->deleteReceipt($expense);

            return response()->json([
                'success' => true,
                'message' => 'Receipt deleted successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete receipt: ' . $e->getMessage()
            ], 500);
        }
    }

    // ==================== REPORTS ====================

    /**
     * Financial report for a specific seminar
     */
    public function financialReport(Seminar $seminar)
    {
        $overview = $this->accountingService->getSeminarFinancialOverview($seminar);
        
        return view('admin.seminars.accounting.reports.financial', compact('overview'));
    }

    /**
     * Profitability report across all seminars
     */
    public function profitabilityReport(Request $request)
    {
        $filters = [
            'date_from' => $request->date_from,
            'date_to' => $request->date_to,
            'type' => $request->type,
            'status' => $request->status,
        ];

        $report = $this->accountingService->getProfitabilityReport($filters);
        
        return view('admin.seminars.accounting.reports.profitability', compact('report', 'filters'));
    }

    /**
     * Payment status tracking report
     */
    public function paymentStatusReport(Request $request)
    {
        $filters = [
            'date_from' => $request->date_from,
            'date_to' => $request->date_to,
            'type' => $request->type,
        ];

        $report = $this->accountingService->getPaymentStatusTracking($filters);
        
        return view('admin.seminars.accounting.reports.payment-status', compact('report', 'filters'));
    }

    // ==================== EXPORTS ====================

    /**
     * Export financial report to Excel
     */
    public function exportFinancialExcel(Seminar $seminar)
    {
        $overview = $this->accountingService->getSeminarFinancialOverview($seminar);
        
        $fileName = 'financial_report_' . $seminar->code . '_' . date('Ymd') . '.xlsx';
        
        return Excel::download(new SeminarFinancialExport($overview), $fileName);
    }

    /**
     * Export financial report to PDF
     */
    public function exportFinancialPdf(Seminar $seminar)
    {
        $overview = $this->accountingService->getSeminarFinancialOverview($seminar);
        
        $pdf = Pdf::loadView('admin.seminars.accounting.reports.financial-pdf', compact('overview'));
        
        $fileName = 'financial_report_' . $seminar->code . '_' . date('Ymd') . '.pdf';
        
        return $pdf->download($fileName);
    }

    /**
     * Export expense list to Excel
     */
    public function exportExpensesExcel(Seminar $seminar)
    {
        $fileName = 'expenses_' . $seminar->code . '_' . date('Ymd') . '.xlsx';
        
        return Excel::download(new SeminarExpenseExport($seminar), $fileName);
    }

    /**
     * Export profitability report to Excel
     */
    public function exportProfitabilityExcel(Request $request)
    {
        $filters = [
            'date_from' => $request->date_from,
            'date_to' => $request->date_to,
            'type' => $request->type,
            'status' => $request->status,
        ];

        $report = $this->accountingService->getProfitabilityReport($filters);
        
        $fileName = 'profitability_report_' . date('Ymd') . '.xlsx';
        
        return Excel::download(new SeminarProfitabilityExport($report), $fileName);
    }

    /**
     * Export profitability report to PDF
     */
    public function exportProfitabilityPdf(Request $request)
    {
        $filters = [
            'date_from' => $request->date_from,
            'date_to' => $request->date_to,
            'type' => $request->type,
            'status' => $request->status,
        ];

        $report = $this->accountingService->getProfitabilityReport($filters);
        
        $pdf = Pdf::loadView('admin.seminars.accounting.reports.profitability-pdf', compact('report', 'filters'));
        
        $fileName = 'profitability_report_' . date('Ymd') . '.pdf';
        
        return $pdf->download($fileName);
    }
}
