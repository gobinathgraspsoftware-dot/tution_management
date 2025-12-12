<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\FinancialDashboardService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class FinancialDashboardController extends Controller
{
    protected $dashboardService;

    public function __construct(FinancialDashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Display financial dashboard
     */
    public function index(Request $request)
    {
        $period = $request->input('period', 'this_month');

        $data = $this->dashboardService->getDashboardData($period);
        $summaryCards = $this->dashboardService->getSummaryCards($period);
        $healthScore = $this->dashboardService->getFinancialHealthScore($period);

        return view('admin.financial.dashboard', compact(
            'data',
            'summaryCards',
            'healthScore',
            'period'
        ));
    }

    /**
     * Get profit/loss statement
     */
    public function profitLoss(Request $request)
    {
        $startDate = $request->filled('date_from')
            ? Carbon::parse($request->date_from)
            : now()->startOfMonth();

        $endDate = $request->filled('date_to')
            ? Carbon::parse($request->date_to)
            : now()->endOfMonth();

        $statement = $this->dashboardService->getProfitLossStatement($startDate, $endDate);

        return response()->json($statement);
    }

    /**
     * Get category breakdown (AJAX)
     */
    public function getCategoryBreakdown(Request $request)
    {
        $startDate = $request->filled('date_from')
            ? Carbon::parse($request->date_from)
            : now()->startOfMonth();

        $endDate = $request->filled('date_to')
            ? Carbon::parse($request->date_to)
            : now()->endOfMonth();

        $breakdown = $this->dashboardService->getCategoryBreakdown($startDate, $endDate);

        return response()->json($breakdown);
    }

    /**
     * Get cash flow analysis
     */
    public function getCashFlow(Request $request)
    {
        $startDate = $request->filled('date_from')
            ? Carbon::parse($request->date_from)
            : now()->startOfMonth();

        $endDate = $request->filled('date_to')
            ? Carbon::parse($request->date_to)
            : now()->endOfMonth();

        $cashFlow = $this->dashboardService->getCashFlowAnalysis($startDate, $endDate);

        return response()->json($cashFlow);
    }

    /**
     * Get chart data for dashboard (AJAX)
     */
    public function getChartData(Request $request)
    {
        $period = $request->input('period', 'this_month');
        $type = $request->input('type', 'trends');

        $data = $this->dashboardService->getDashboardData($period);

        if ($type === 'trends') {
            $trends = $data['trends'];

            return response()->json([
                'labels' => $trends->pluck('date')->toArray(),
                'revenue' => $trends->pluck('revenue')->toArray(),
                'expenses' => $trends->pluck('expense')->toArray(),
                'profit' => $trends->pluck('profit')->toArray(),
            ]);
        }

        if ($type === 'revenue_category') {
            $categories = $data['revenue']['by_category'];

            return response()->json([
                'labels' => array_keys($categories),
                'values' => array_values($categories),
            ]);
        }

        if ($type === 'expense_category') {
            $categories = $data['expenses']['by_category'];

            return response()->json([
                'labels' => $categories->pluck('name')->toArray(),
                'values' => $categories->pluck('total')->toArray(),
            ]);
        }

        return response()->json(['error' => 'Invalid chart type'], 400);
    }

    /**
     * Export financial summary
     */
    public function exportSummary(Request $request)
    {
        $startDate = $request->filled('date_from')
            ? Carbon::parse($request->date_from)
            : now()->startOfMonth();

        $endDate = $request->filled('date_to')
            ? Carbon::parse($request->date_to)
            : now()->endOfMonth();

        $statement = $this->dashboardService->getProfitLossStatement($startDate, $endDate);

        $filename = 'financial_summary_' . $startDate->format('Ymd') . '_' . $endDate->format('Ymd') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($statement) {
            $file = fopen('php://output', 'w');

            // Period info
            fputcsv($file, ['Financial Summary']);
            fputcsv($file, ['Period', $statement['period']['start'] . ' to ' . $statement['period']['end']]);
            fputcsv($file, []);

            // Revenue section
            fputcsv($file, ['REVENUE']);
            fputcsv($file, ['Student Fees (Online)', number_format($statement['revenue']['student_fees_online'], 2)]);
            fputcsv($file, ['Student Fees (Physical)', number_format($statement['revenue']['student_fees_physical'], 2)]);
            fputcsv($file, ['Seminar Revenue', number_format($statement['revenue']['seminar_revenue'], 2)]);
            fputcsv($file, ['Cafeteria Sales', number_format($statement['revenue']['cafeteria_sales'], 2)]);
            fputcsv($file, ['Material Sales', number_format($statement['revenue']['material_sales'], 2)]);
            fputcsv($file, ['Other Revenue', number_format($statement['revenue']['other_revenue'], 2)]);
            fputcsv($file, ['Total Revenue', number_format($statement['revenue']['total_revenue'], 2)]);
            fputcsv($file, []);

            // Expenses section
            fputcsv($file, ['EXPENSES']);
            foreach ($statement['expenses']['by_category'] as $expense) {
                fputcsv($file, [$expense->name, number_format($expense->total, 2)]);
            }
            fputcsv($file, ['Total Expenses', number_format($statement['expenses']['total_expenses'], 2)]);
            fputcsv($file, []);

            // Summary
            fputcsv($file, ['SUMMARY']);
            fputcsv($file, ['Gross Profit', number_format($statement['summary']['gross_profit'], 2)]);
            fputcsv($file, ['Profit Margin', number_format($statement['summary']['profit_margin'], 2) . '%']);
            fputcsv($file, ['Status', $statement['summary']['status']]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
