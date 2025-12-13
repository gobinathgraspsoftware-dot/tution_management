<?php

namespace App\Services;

use App\Models\Seminar;
use App\Models\SeminarExpense;
use App\Models\SeminarParticipant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class SeminarAccountingService
{
    /**
     * Get financial overview for a specific seminar
     */
    public function getSeminarFinancialOverview(Seminar $seminar): array
    {
        // Revenue calculation
        $totalRevenue = $seminar->participants()
            ->where('payment_status', 'paid')
            ->sum('fee_amount');
        
        $pendingRevenue = $seminar->participants()
            ->where('payment_status', 'pending')
            ->sum('fee_amount');
        
        $refundedRevenue = $seminar->participants()
            ->where('payment_status', 'refunded')
            ->sum('fee_amount');

        // Expense calculation
        $approvedExpenses = $seminar->expenses()
            ->where('approval_status', 'approved')
            ->sum('amount');
        
        $pendingExpenses = $seminar->expenses()
            ->where('approval_status', 'pending')
            ->sum('amount');
        
        $totalExpenses = $seminar->expenses()->sum('amount');

        // Profit calculation (only approved expenses)
        $netProfit = $totalRevenue - $approvedExpenses;
        $profitMargin = $totalRevenue > 0 ? ($netProfit / $totalRevenue) * 100 : 0;

        // Expense breakdown by category
        $expensesByCategory = $seminar->expenses()
            ->where('approval_status', 'approved')
            ->select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->category => $item->total];
            });

        // Revenue breakdown by payment method
        $revenueByMethod = $seminar->participants()
            ->where('payment_status', 'paid')
            ->select('payment_method', DB::raw('SUM(fee_amount) as total'))
            ->groupBy('payment_method')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->payment_method ?? 'Not Specified' => $item->total];
            });

        return [
            'seminar' => $seminar,
            'revenue' => [
                'total' => $totalRevenue,
                'pending' => $pendingRevenue,
                'refunded' => $refundedRevenue,
                'by_method' => $revenueByMethod,
                'participant_count' => $seminar->participants()->count(),
                'paid_count' => $seminar->participants()->where('payment_status', 'paid')->count(),
            ],
            'expenses' => [
                'total' => $approvedExpenses,
                'pending' => $pendingExpenses,
                'all_expenses' => $totalExpenses,
                'by_category' => $expensesByCategory,
                'expense_count' => $seminar->expenses()->count(),
                'approved_count' => $seminar->expenses()->where('approval_status', 'approved')->count(),
            ],
            'profitability' => [
                'net_profit' => $netProfit,
                'profit_margin' => round($profitMargin, 2),
                'status' => $netProfit > 0 ? 'Profitable' : ($netProfit < 0 ? 'Loss' : 'Break Even'),
                'roi' => $approvedExpenses > 0 ? round(($netProfit / $approvedExpenses) * 100, 2) : 0,
            ],
        ];
    }

    /**
     * Get profitability report for multiple seminars
     */
    public function getProfitabilityReport($filters = []): array
    {
        $query = Seminar::with(['participants', 'expenses']);

        // Apply filters
        if (isset($filters['date_from'])) {
            $query->whereDate('date', '>=', $filters['date_from']);
        }
        if (isset($filters['date_to'])) {
            $query->whereDate('date', '<=', $filters['date_to']);
        }
        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $seminars = $query->get();

        $report = [];
        $totalRevenue = 0;
        $totalExpenses = 0;
        $totalProfit = 0;

        foreach ($seminars as $seminar) {
            $revenue = $seminar->participants()
                ->where('payment_status', 'paid')
                ->sum('fee_amount');
            
            $expenses = $seminar->expenses()
                ->where('approval_status', 'approved')
                ->sum('amount');
            
            $profit = $revenue - $expenses;
            $profitMargin = $revenue > 0 ? ($profit / $revenue) * 100 : 0;

            $report[] = [
                'seminar_id' => $seminar->id,
                'seminar_name' => $seminar->name,
                'seminar_code' => $seminar->code,
                'date' => $seminar->date,
                'type' => $seminar->type,
                'status' => $seminar->status,
                'participants' => $seminar->participants()->count(),
                'revenue' => $revenue,
                'expenses' => $expenses,
                'profit' => $profit,
                'profit_margin' => round($profitMargin, 2),
                'status_label' => $profit > 0 ? 'Profit' : ($profit < 0 ? 'Loss' : 'Break Even'),
            ];

            $totalRevenue += $revenue;
            $totalExpenses += $expenses;
            $totalProfit += $profit;
        }

        // Sort by profit (descending)
        usort($report, function ($a, $b) {
            return $b['profit'] <=> $a['profit'];
        });

        return [
            'seminars' => $report,
            'summary' => [
                'total_seminars' => count($report),
                'total_revenue' => $totalRevenue,
                'total_expenses' => $totalExpenses,
                'total_profit' => $totalProfit,
                'average_profit' => count($report) > 0 ? $totalProfit / count($report) : 0,
                'overall_margin' => $totalRevenue > 0 ? ($totalProfit / $totalRevenue) * 100 : 0,
                'profitable_count' => collect($report)->where('profit', '>', 0)->count(),
                'loss_count' => collect($report)->where('profit', '<', 0)->count(),
            ],
        ];
    }

    /**
     * Get payment status tracking across seminars
     */
    public function getPaymentStatusTracking($filters = []): array
    {
        $query = Seminar::with(['participants']);

        // Apply filters
        if (isset($filters['date_from'])) {
            $query->whereDate('date', '>=', $filters['date_from']);
        }
        if (isset($filters['date_to'])) {
            $query->whereDate('date', '<=', $filters['date_to']);
        }
        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        $seminars = $query->get();

        $tracking = [];
        $totalParticipants = 0;
        $totalPaid = 0;
        $totalPending = 0;
        $totalRefunded = 0;
        $totalRevenue = 0;
        $totalPendingAmount = 0;

        foreach ($seminars as $seminar) {
            $participants = $seminar->participants();
            
            $paidCount = $participants->where('payment_status', 'paid')->count();
            $pendingCount = $participants->where('payment_status', 'pending')->count();
            $refundedCount = $participants->where('payment_status', 'refunded')->count();
            $totalCount = $participants->count();

            $paidAmount = $participants->where('payment_status', 'paid')->sum('fee_amount');
            $pendingAmount = $participants->where('payment_status', 'pending')->sum('fee_amount');
            $refundedAmount = $participants->where('payment_status', 'refunded')->sum('fee_amount');

            $paymentRate = $totalCount > 0 ? ($paidCount / $totalCount) * 100 : 0;

            $tracking[] = [
                'seminar_id' => $seminar->id,
                'seminar_name' => $seminar->name,
                'seminar_code' => $seminar->code,
                'date' => $seminar->date,
                'total_participants' => $totalCount,
                'paid_count' => $paidCount,
                'pending_count' => $pendingCount,
                'refunded_count' => $refundedCount,
                'paid_amount' => $paidAmount,
                'pending_amount' => $pendingAmount,
                'refunded_amount' => $refundedAmount,
                'payment_rate' => round($paymentRate, 2),
                'collection_status' => $paymentRate >= 90 ? 'Excellent' : 
                                      ($paymentRate >= 75 ? 'Good' : 
                                      ($paymentRate >= 50 ? 'Fair' : 'Poor')),
            ];

            $totalParticipants += $totalCount;
            $totalPaid += $paidCount;
            $totalPending += $pendingCount;
            $totalRefunded += $refundedCount;
            $totalRevenue += $paidAmount;
            $totalPendingAmount += $pendingAmount;
        }

        return [
            'tracking' => $tracking,
            'summary' => [
                'total_seminars' => count($tracking),
                'total_participants' => $totalParticipants,
                'total_paid' => $totalPaid,
                'total_pending' => $totalPending,
                'total_refunded' => $totalRefunded,
                'total_revenue' => $totalRevenue,
                'total_pending_amount' => $totalPendingAmount,
                'overall_payment_rate' => $totalParticipants > 0 ? 
                    round(($totalPaid / $totalParticipants) * 100, 2) : 0,
            ],
        ];
    }

    /**
     * Approve seminar expense
     */
    public function approveExpense(SeminarExpense $expense, $approvedBy): bool
    {
        return $expense->update([
            'approval_status' => 'approved',
            'approved_by' => $approvedBy,
            'approved_at' => now(),
        ]);
    }

    /**
     * Reject seminar expense
     */
    public function rejectExpense(SeminarExpense $expense, $rejectedBy, $reason = null): bool
    {
        return $expense->update([
            'approval_status' => 'rejected',
            'approved_by' => $rejectedBy,
            'approved_at' => now(),
            'rejection_reason' => $reason,
        ]);
    }

    /**
     * Get expense statistics
     */
    public function getExpenseStatistics($startDate = null, $endDate = null): array
    {
        $query = SeminarExpense::query();

        if ($startDate && $endDate) {
            $query->whereBetween('expense_date', [$startDate, $endDate]);
        }

        $totalExpenses = $query->sum('amount');
        $approvedExpenses = (clone $query)->where('approval_status', 'approved')->sum('amount');
        $pendingExpenses = (clone $query)->where('approval_status', 'pending')->sum('amount');
        $rejectedExpenses = (clone $query)->where('approval_status', 'rejected')->sum('amount');

        $byCategory = (clone $query)
            ->where('approval_status', 'approved')
            ->select('category', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('category')
            ->get();

        return [
            'total' => $totalExpenses,
            'approved' => $approvedExpenses,
            'pending' => $pendingExpenses,
            'rejected' => $rejectedExpenses,
            'by_category' => $byCategory,
            'count' => $query->count(),
            'pending_count' => (clone $query)->where('approval_status', 'pending')->count(),
        ];
    }

    /**
     * Delete expense receipt
     */
    public function deleteReceipt(SeminarExpense $expense): bool
    {
        if ($expense->receipt_path) {
            Storage::disk('public')->delete($expense->receipt_path);
            return $expense->update(['receipt_path' => null]);
        }
        return true;
    }

    /**
     * Get category label
     */
    public static function getCategoryLabel($category): string
    {
        $labels = [
            'venue' => 'Venue Rental',
            'materials' => 'Materials & Supplies',
            'food' => 'Food & Beverages',
            'facilitator_fees' => 'Facilitator Fees',
            'marketing' => 'Marketing & Promotion',
            'transportation' => 'Transportation',
            'equipment' => 'Equipment Rental',
            'miscellaneous' => 'Miscellaneous',
        ];

        return $labels[$category] ?? ucfirst($category);
    }

    /**
     * Get all expense categories
     */
    public static function getExpenseCategories(): array
    {
        return [
            'venue' => 'Venue Rental',
            'materials' => 'Materials & Supplies',
            'food' => 'Food & Beverages',
            'facilitator_fees' => 'Facilitator Fees',
            'marketing' => 'Marketing & Promotion',
            'transportation' => 'Transportation',
            'equipment' => 'Equipment Rental',
            'miscellaneous' => 'Miscellaneous',
        ];
    }
}
