<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\PosTransaction;
use App\Models\SeminarParticipant;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FinancialReportService
{
    protected $revenueService;
    protected $expenseService;
    protected $dashboardService;

    public function __construct(
        RevenueService $revenueService,
        ExpenseService $expenseService,
        FinancialDashboardService $dashboardService
    ) {
        $this->revenueService = $revenueService;
        $this->expenseService = $expenseService;
        $this->dashboardService = $dashboardService;
    }

    /**
     * Generate comprehensive financial report
     */
    public function generateComprehensiveReport($startDate, $endDate)
    {
        return [
            'period' => [
                'start' => Carbon::parse($startDate)->format('d M Y'),
                'end' => Carbon::parse($endDate)->format('d M Y'),
                'days' => Carbon::parse($startDate)->diffInDays($endDate) + 1,
            ],
            'summary' => $this->getFinancialSummary($startDate, $endDate),
            'revenue' => $this->getRevenueBreakdown($startDate, $endDate),
            'expenses' => $this->getExpenseBreakdown($startDate, $endDate),
            'profit_loss' => $this->getProfitLossAnalysis($startDate, $endDate),
            'cash_flow' => $this->getCashFlowData($startDate, $endDate),
            'trends' => $this->getTrendAnalysis($startDate, $endDate),
            'category_breakdown' => $this->getCategoryBreakdown($startDate, $endDate),
        ];
    }

    /**
     * Get financial summary
     */
    public function getFinancialSummary($startDate, $endDate)
    {
        $revenue = $this->revenueService->getTotalRevenue($startDate, $endDate);
        $expenses = $this->expenseService->getExpenseSummary($startDate, $endDate);

        $totalRevenue = $revenue['total'];
        $totalExpenses = $expenses['total_expenses'];
        $netProfit = $totalRevenue - $totalExpenses;
        $profitMargin = $totalRevenue > 0 ? ($netProfit / $totalRevenue) * 100 : 0;

        return [
            'total_revenue' => $totalRevenue,
            'total_expenses' => $totalExpenses,
            'net_profit' => $netProfit,
            'profit_margin' => $profitMargin,
            'gross_profit_margin' => $profitMargin,
            'status' => $netProfit > 0 ? 'Profit' : ($netProfit < 0 ? 'Loss' : 'Break Even'),
            'revenue_count' => $revenue['transaction_count'] ?? 0,
            'expense_count' => $expenses['expense_count'] ?? 0,
        ];
    }

    /**
     * Get detailed revenue breakdown
     */
    public function getRevenueBreakdown($startDate, $endDate)
    {
        $revenue = $this->revenueService->getTotalRevenue($startDate, $endDate);

        $studentFeesTotal = $revenue['student_fees']['online'] + $revenue['student_fees']['physical'];

        return [
            'student_fees' => [
                'online' => $revenue['student_fees']['online'],
                'physical' => $revenue['student_fees']['physical'],
                'total' => $studentFeesTotal,
                'amount' => $studentFeesTotal, // Added for consistency with other categories
                'percentage' => $revenue['total'] > 0
                    ? ($studentFeesTotal / $revenue['total']) * 100
                    : 0,
            ],
            'seminar_revenue' => [
                'amount' => $revenue['seminar_revenue'] ?? 0,
                'percentage' => $revenue['total'] > 0 ? (($revenue['seminar_revenue'] ?? 0) / $revenue['total']) * 100 : 0,
            ],
            'cafeteria_sales' => [
                'amount' => $revenue['pos_revenue'] ?? 0,
                'percentage' => $revenue['total'] > 0 ? (($revenue['pos_revenue'] ?? 0) / $revenue['total']) * 100 : 0,
            ],
            'material_sales' => [
                'amount' => $revenue['material_sales'] ?? 0,
                'percentage' => $revenue['total'] > 0 ? (($revenue['material_sales'] ?? 0) / $revenue['total']) * 100 : 0,
            ],
            'other_revenue' => [
                'amount' => $revenue['other_revenue'] ?? 0,
                'percentage' => $revenue['total'] > 0 ? (($revenue['other_revenue'] ?? 0) / $revenue['total']) * 100 : 0,
            ],
            'total' => $revenue['total'] ?? 0,
        ];
    }

    /**
     * Get detailed expense breakdown
     */
    public function getExpenseBreakdown($startDate, $endDate)
    {
        $expenses = $this->expenseService->getExpensesByCategory($startDate, $endDate);
        $summary = $this->expenseService->getExpenseSummary($startDate, $endDate);

        $breakdown = [];
        foreach ($expenses as $expense) {
            $breakdown[] = [
                'category' => $expense->name,
                'amount' => $expense->total,
                'count' => $expense->count,
                'average' => $expense->count > 0 ? $expense->total / $expense->count : 0,
                'percentage' => $summary['total_expenses'] > 0
                    ? ($expense->total / $summary['total_expenses']) * 100
                    : 0,
            ];
        }

        return [
            'by_category' => $breakdown,
            'total' => $summary['total_expenses'],
            'average_per_expense' => $summary['average_expense'],
            'count' => $summary['expense_count'],
            'pending' => [
                'count' => $summary['pending_count'],
                'amount' => $summary['pending_amount'],
            ],
        ];
    }

    /**
     * Get profit & loss analysis
     */
    public function getProfitLossAnalysis($startDate, $endDate)
    {
        $statement = $this->dashboardService->getProfitLossStatement($startDate, $endDate);

        // Calculate additional metrics
        $totalRevenue = $statement['revenue']['total_revenue'];
        $totalExpenses = $statement['expenses']['total_expenses'];
        $grossProfit = $statement['summary']['gross_profit'];

        return [
            'revenue' => $statement['revenue'],
            'expenses' => $statement['expenses'],
            'gross_profit' => $grossProfit,
            'profit_margin' => $statement['summary']['profit_margin'],
            'operating_ratio' => $totalRevenue > 0 ? ($totalExpenses / $totalRevenue) * 100 : 0,
            'break_even_point' => $totalExpenses,
            'status' => $statement['summary']['status'],
            'recommendations' => $this->getFinancialRecommendations($grossProfit, $statement['summary']['profit_margin']),
        ];
    }

    /**
     * Get cash flow data
     */
    public function getCashFlowData($startDate, $endDate)
    {
        return $this->dashboardService->getCashFlowAnalysis($startDate, $endDate);
    }

    /**
     * Get trend analysis
     */
    public function getTrendAnalysis($startDate, $endDate)
    {
        $trends = $this->dashboardService->getTrends($startDate, $endDate);

        // Calculate growth rates
        $revenueData = $trends->pluck('revenue')->toArray();
        $expenseData = $trends->pluck('expense')->toArray();

        return [
            'daily_trends' => $trends,
            'revenue_trend' => $this->calculateTrend($revenueData),
            'expense_trend' => $this->calculateTrend($expenseData),
            'profit_trend' => $this->calculateTrend($trends->pluck('profit')->toArray()),
        ];
    }

    /**
     * Calculate trend direction
     */
    private function calculateTrend($data)
    {
        if (count($data) < 2) {
            return 'stable';
        }

        $firstHalf = array_slice($data, 0, (int)(count($data) / 2));
        $secondHalf = array_slice($data, (int)(count($data) / 2));

        $firstAvg = count($firstHalf) > 0 ? array_sum($firstHalf) / count($firstHalf) : 0;
        $secondAvg = count($secondHalf) > 0 ? array_sum($secondHalf) / count($secondHalf) : 0;

        $change = $firstAvg > 0 ? (($secondAvg - $firstAvg) / $firstAvg) * 100 : 0;

        if ($change > 5) return 'increasing';
        if ($change < -5) return 'decreasing';
        return 'stable';
    }

    /**
     * Get category-based breakdown
     */
    public function getCategoryBreakdown($startDate, $endDate)
    {
        return [
            'revenue' => $this->revenueService->getRevenueByCategory($startDate, $endDate),
            'expenses' => $this->expenseService->getExpensesByCategory($startDate, $endDate),
        ];
    }

    /**
     * Get financial recommendations
     */
    private function getFinancialRecommendations($grossProfit, $profitMargin)
    {
        $recommendations = [];

        if ($profitMargin < 10) {
            $recommendations[] = 'Consider reviewing expenses to improve profit margin';
            $recommendations[] = 'Explore revenue diversification opportunities';
        } elseif ($profitMargin < 20) {
            $recommendations[] = 'Profit margin is moderate - focus on cost optimization';
        } else {
            $recommendations[] = 'Excellent profit margin - maintain current operations';
        }

        if ($grossProfit < 0) {
            $recommendations[] = 'URGENT: Operating at a loss - immediate action required';
            $recommendations[] = 'Review all major expense categories';
        }

        return $recommendations;
    }

    /**
     * Generate report for specific period type
     */
    public function generatePeriodReport($periodType = 'monthly')
    {
        $dates = $this->getPeriodDates($periodType);
        return $this->generateComprehensiveReport($dates['start'], $dates['end']);
    }

    /**
     * Get period dates
     */
    private function getPeriodDates($periodType)
    {
        return match($periodType) {
            'today' => [
                'start' => now()->startOfDay(),
                'end' => now()->endOfDay()
            ],
            'weekly' => [
                'start' => now()->startOfWeek(),
                'end' => now()->endOfWeek()
            ],
            'monthly' => [
                'start' => now()->startOfMonth(),
                'end' => now()->endOfMonth()
            ],
            'quarterly' => [
                'start' => now()->startOfQuarter(),
                'end' => now()->endOfQuarter()
            ],
            'yearly' => [
                'start' => now()->startOfYear(),
                'end' => now()->endOfYear()
            ],
            default => [
                'start' => now()->startOfMonth(),
                'end' => now()->endOfMonth()
            ],
        };
    }

    /**
     * Get comparison data with previous period
     */
    public function getComparisonData($startDate, $endDate)
    {
        return $this->dashboardService->getComparison($startDate, $endDate);
    }

    /**
     * Export data for Excel/CSV
     */
    public function prepareExportData($startDate, $endDate)
    {
        $report = $this->generateComprehensiveReport($startDate, $endDate);

        return [
            'summary' => $report['summary'],
            'revenue_breakdown' => $report['revenue'],
            'expense_breakdown' => $report['expenses'],
            'profit_loss' => $report['profit_loss'],
            'daily_trends' => $report['trends']['daily_trends'],
        ];
    }

    /**
     * Get financial health metrics
     */
    public function getFinancialHealthMetrics($startDate, $endDate)
    {
        $summary = $this->getFinancialSummary($startDate, $endDate);

        return [
            'profitability_score' => $this->calculateProfitabilityScore($summary['profit_margin']),
            'revenue_health' => $this->calculateRevenueHealth($startDate, $endDate),
            'expense_efficiency' => $this->calculateExpenseEfficiency($summary),
            'overall_health' => $this->calculateOverallHealth($summary, $startDate, $endDate),
        ];
    }

    /**
     * Calculate profitability score
     */
    private function calculateProfitabilityScore($profitMargin)
    {
        if ($profitMargin >= 30) return 'Excellent';
        if ($profitMargin >= 20) return 'Good';
        if ($profitMargin >= 10) return 'Fair';
        if ($profitMargin >= 0) return 'Poor';
        return 'Critical';
    }

    /**
     * Calculate revenue health
     */
    private function calculateRevenueHealth($startDate, $endDate)
    {
        $comparison = $this->dashboardService->getComparison($startDate, $endDate);
        $growth = $comparison['revenue']['change_percentage'];

        if ($growth >= 20) return 'Excellent';
        if ($growth >= 10) return 'Good';
        if ($growth >= 0) return 'Fair';
        return 'Declining';
    }

    /**
     * Calculate expense efficiency
     */
    private function calculateExpenseEfficiency($summary)
    {
        $ratio = $summary['total_revenue'] > 0
            ? ($summary['total_expenses'] / $summary['total_revenue']) * 100
            : 100;

        if ($ratio <= 50) return 'Excellent';
        if ($ratio <= 70) return 'Good';
        if ($ratio <= 85) return 'Fair';
        return 'Poor';
    }

    /**
     * Calculate overall financial health
     */
    private function calculateOverallHealth($summary, $startDate, $endDate)
    {
        $score = 0;

        // Profitability (40%)
        if ($summary['profit_margin'] >= 20) $score += 40;
        elseif ($summary['profit_margin'] >= 10) $score += 30;
        elseif ($summary['profit_margin'] >= 0) $score += 20;

        // Revenue growth (30%)
        $comparison = $this->dashboardService->getComparison($startDate, $endDate);
        if ($comparison['revenue']['change_percentage'] >= 10) $score += 30;
        elseif ($comparison['revenue']['change_percentage'] >= 0) $score += 20;

        // Expense control (30%)
        $expenseRatio = $summary['total_revenue'] > 0
            ? ($summary['total_expenses'] / $summary['total_revenue']) * 100
            : 100;
        if ($expenseRatio <= 70) $score += 30;
        elseif ($expenseRatio <= 85) $score += 20;

        return [
            'score' => $score,
            'rating' => $this->getHealthRating($score),
            'status' => $score >= 70 ? 'Healthy' : ($score >= 50 ? 'Moderate' : 'Needs Attention'),
        ];
    }

    /**
     * Get health rating
     */
    private function getHealthRating($score)
    {
        if ($score >= 90) return 'A+';
        if ($score >= 80) return 'A';
        if ($score >= 70) return 'B';
        if ($score >= 60) return 'C';
        if ($score >= 50) return 'D';
        return 'F';
    }
}
