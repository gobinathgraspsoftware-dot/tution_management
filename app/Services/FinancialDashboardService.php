<?php

namespace App\Services;

use Carbon\Carbon;

class FinancialDashboardService
{
    protected $revenueService;
    protected $expenseService;

    public function __construct(RevenueService $revenueService, ExpenseService $expenseService)
    {
        $this->revenueService = $revenueService;
        $this->expenseService = $expenseService;
    }

    /**
     * Get complete financial dashboard data
     */
    public function getDashboardData($period = 'this_month')
    {
        $dates = $this->getPeriodDates($period);
        $startDate = $dates['start'];
        $endDate = $dates['end'];

        $revenue = $this->revenueService->getTotalRevenue($startDate, $endDate);
        $expenses = $this->expenseService->getExpenseSummary($startDate, $endDate);

        $totalRevenue = $revenue['total'];
        $totalExpenses = $expenses['total_expenses'];
        $netProfit = $totalRevenue - $totalExpenses;
        $profitMargin = $totalRevenue > 0 ? ($netProfit / $totalRevenue) * 100 : 0;

        return [
            'period' => $period,
            'date_range' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
            ],
            'revenue' => [
                'total' => $totalRevenue,
                'breakdown' => $revenue,
                'by_category' => $this->revenueService->getRevenueByCategory($startDate, $endDate),
            ],
            'expenses' => [
                'total' => $totalExpenses,
                'count' => $expenses['expense_count'],
                'average' => $expenses['average_expense'],
                'by_category' => $expenses['by_category'],
                'pending' => [
                    'count' => $expenses['pending_count'],
                    'amount' => $expenses['pending_amount'],
                ],
            ],
            'profit' => [
                'net_profit' => $netProfit,
                'profit_margin' => $profitMargin,
                'status' => $netProfit > 0 ? 'profit' : ($netProfit < 0 ? 'loss' : 'break_even'),
            ],
            'comparison' => $this->getComparison($startDate, $endDate),
            'trends' => $this->getTrends($startDate, $endDate),
        ];
    }

    /**
     * Get revenue vs expense comparison with previous period
     */
    public function getComparison($startDate, $endDate)
    {
        // Current period
        $currentRevenue = $this->revenueService->calculateTotalRevenue($startDate, $endDate);
        $currentExpenses = $this->expenseService->getExpenseSummary($startDate, $endDate)['total_expenses'];
        $currentProfit = $currentRevenue - $currentExpenses;

        // Previous period
        $diff = $startDate->diffInDays($endDate);
        $previousStart = $startDate->copy()->subDays($diff + 1);
        $previousEnd = $startDate->copy()->subDay();

        $previousRevenue = $this->revenueService->calculateTotalRevenue($previousStart, $previousEnd);
        $previousExpenses = $this->expenseService->getExpenseSummary($previousStart, $previousEnd)['total_expenses'];
        $previousProfit = $previousRevenue - $previousExpenses;

        return [
            'revenue' => [
                'current' => $currentRevenue,
                'previous' => $previousRevenue,
                'change' => $currentRevenue - $previousRevenue,
                'change_percentage' => $previousRevenue > 0
                    ? (($currentRevenue - $previousRevenue) / $previousRevenue) * 100
                    : 0,
            ],
            'expenses' => [
                'current' => $currentExpenses,
                'previous' => $previousExpenses,
                'change' => $currentExpenses - $previousExpenses,
                'change_percentage' => $previousExpenses > 0
                    ? (($currentExpenses - $previousExpenses) / $previousExpenses) * 100
                    : 0,
            ],
            'profit' => [
                'current' => $currentProfit,
                'previous' => $previousProfit,
                'change' => $currentProfit - $previousProfit,
                'change_percentage' => $previousProfit != 0
                    ? (($currentProfit - $previousProfit) / abs($previousProfit)) * 100
                    : 0,
            ],
        ];
    }

    /**
     * Get daily revenue and expense trends
     */
    public function getTrends($startDate, $endDate)
    {
        $revenueTrends = $this->revenueService->getRevenueTrends($startDate, $endDate);
        $expenseTrends = $this->getExpenseTrends($startDate, $endDate);

        // Merge trends by date
        $allDates = collect();
        $period = Carbon::parse($startDate);

        while ($period->lte($endDate)) {
            $dateStr = $period->format('Y-m-d');

            $revenue = $revenueTrends->firstWhere('date', $dateStr)['total'] ?? 0;
            $expense = $expenseTrends->firstWhere('date', $dateStr)['total'] ?? 0;

            $allDates->push([
                'date' => $dateStr,
                'revenue' => $revenue,
                'expense' => $expense,
                'profit' => $revenue - $expense,
            ]);

            $period->addDay();
        }

        return $allDates;
    }

    /**
     * Get expense trends (daily breakdown)
     */
    private function getExpenseTrends($startDate, $endDate)
    {
        return \DB::table('expenses')
            ->where('status', 'approved')
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->select(\DB::raw('DATE(expense_date) as date'), \DB::raw('SUM(amount) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    /**
     * Get profit/loss statement
     */
    public function getProfitLossStatement($startDate, $endDate)
    {
        $revenue = $this->revenueService->getTotalRevenue($startDate, $endDate);
        $expenses = $this->expenseService->getExpensesByCategory($startDate, $endDate);

        $totalRevenue = $revenue['total'];
        $totalExpenses = $expenses->sum('total');
        $grossProfit = $totalRevenue - $totalExpenses;
        $profitMargin = $totalRevenue > 0 ? ($grossProfit / $totalRevenue) * 100 : 0;

        return [
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
            ],
            'revenue' => [
                'student_fees_online' => $revenue['student_fees']['online'],
                'student_fees_physical' => $revenue['student_fees']['physical'],
                'seminar_revenue' => $revenue['seminar_revenue'],
                'cafeteria_sales' => $revenue['pos_revenue'],
                'material_sales' => $revenue['material_sales'],
                'other_revenue' => $revenue['other_revenue'],
                'total_revenue' => $totalRevenue,
            ],
            'expenses' => [
                'by_category' => $expenses,
                'total_expenses' => $totalExpenses,
            ],
            'summary' => [
                'gross_profit' => $grossProfit,
                'profit_margin' => $profitMargin,
                'status' => $grossProfit > 0 ? 'Profit' : ($grossProfit < 0 ? 'Loss' : 'Break Even'),
            ],
        ];
    }

    /**
     * Get financial summary cards for dashboard
     */
    public function getSummaryCards($period = 'this_month')
    {
        $dates = $this->getPeriodDates($period);
        $data = $this->getDashboardData($period);

        return [
            [
                'title' => 'Total Revenue',
                'value' => $data['revenue']['total'],
                'icon' => 'fa-dollar-sign',
                'color' => 'success',
                'trend' => $data['comparison']['revenue']['change_percentage'],
            ],
            [
                'title' => 'Total Expenses',
                'value' => $data['expenses']['total'],
                'icon' => 'fa-receipt',
                'color' => 'danger',
                'trend' => $data['comparison']['expenses']['change_percentage'],
            ],
            [
                'title' => 'Net Profit',
                'value' => $data['profit']['net_profit'],
                'icon' => 'fa-chart-line',
                'color' => $data['profit']['net_profit'] > 0 ? 'success' : 'danger',
                'trend' => $data['comparison']['profit']['change_percentage'],
            ],
            [
                'title' => 'Profit Margin',
                'value' => number_format($data['profit']['profit_margin'], 2) . '%',
                'icon' => 'fa-percentage',
                'color' => 'info',
                'trend' => null,
            ],
        ];
    }

    /**
     * Get category-based revenue and expense breakdown
     */
    public function getCategoryBreakdown($startDate, $endDate)
    {
        return [
            'revenue' => $this->revenueService->getRevenueByCategory($startDate, $endDate),
            'expenses' => $this->expenseService->getExpensesByCategory($startDate, $endDate),
        ];
    }

    /**
     * Get cash flow analysis
     */
    public function getCashFlowAnalysis($startDate, $endDate)
    {
        $revenueTrends = $this->revenueService->getRevenueTrends($startDate, $endDate);
        $expenseTrends = $this->getExpenseTrends($startDate, $endDate);

        $totalInflow = $revenueTrends->sum('total');
        $totalOutflow = $expenseTrends->sum('total');
        $netCashFlow = $totalInflow - $totalOutflow;

        return [
            'total_inflow' => $totalInflow,
            'total_outflow' => $totalOutflow,
            'net_cash_flow' => $netCashFlow,
            'cash_flow_status' => $netCashFlow > 0 ? 'positive' : ($netCashFlow < 0 ? 'negative' : 'neutral'),
            'daily_breakdown' => $this->getTrends($startDate, $endDate),
        ];
    }

    /**
     * Get period dates helper
     */
    private function getPeriodDates($period)
    {
        return match($period) {
            'today' => ['start' => now()->startOfDay(), 'end' => now()->endOfDay()],
            'this_week' => ['start' => now()->startOfWeek(), 'end' => now()->endOfWeek()],
            'this_month' => ['start' => now()->startOfMonth(), 'end' => now()->endOfMonth()],
            'this_year' => ['start' => now()->startOfYear(), 'end' => now()->endOfYear()],
            'last_month' => [
                'start' => now()->subMonth()->startOfMonth(),
                'end' => now()->subMonth()->endOfMonth()
            ],
            'last_year' => [
                'start' => now()->subYear()->startOfYear(),
                'end' => now()->subYear()->endOfYear()
            ],
            default => ['start' => now()->startOfMonth(), 'end' => now()->endOfMonth()],
        };
    }

    /**
     * Get financial health score (0-100)
     */
    public function getFinancialHealthScore($period = 'this_month')
    {
        $data = $this->getDashboardData($period);

        $score = 0;

        // Profit margin (40 points)
        $profitMargin = $data['profit']['profit_margin'];
        if ($profitMargin >= 30) $score += 40;
        elseif ($profitMargin >= 20) $score += 30;
        elseif ($profitMargin >= 10) $score += 20;
        elseif ($profitMargin >= 0) $score += 10;

        // Revenue growth (30 points)
        $revenueGrowth = $data['comparison']['revenue']['change_percentage'];
        if ($revenueGrowth >= 20) $score += 30;
        elseif ($revenueGrowth >= 10) $score += 20;
        elseif ($revenueGrowth >= 0) $score += 10;

        // Expense control (30 points)
        $expenseGrowth = $data['comparison']['expenses']['change_percentage'];
        if ($expenseGrowth <= 0) $score += 30;
        elseif ($expenseGrowth <= 10) $score += 20;
        elseif ($expenseGrowth <= 20) $score += 10;

        return [
            'score' => $score,
            'grade' => $this->getGrade($score),
            'factors' => [
                'profit_margin' => $profitMargin,
                'revenue_growth' => $revenueGrowth,
                'expense_control' => $expenseGrowth,
            ],
        ];
    }

    /**
     * Get grade based on score
     */
    private function getGrade($score)
    {
        if ($score >= 90) return 'A+';
        if ($score >= 80) return 'A';
        if ($score >= 70) return 'B';
        if ($score >= 60) return 'C';
        if ($score >= 50) return 'D';
        return 'F';
    }
}
