<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\RevenueService;
use App\Models\Payment;
use App\Models\Student;
use Illuminate\Http\Request;
use Carbon\Carbon;

class RevenueController extends Controller
{
    protected $revenueService;

    public function __construct(RevenueService $revenueService)
    {
        $this->revenueService = $revenueService;
    }

    /**
     * Display revenue listing and summary
     */
    public function index(Request $request)
    {
        $startDate = $request->filled('date_from')
            ? Carbon::parse($request->date_from)
            : now()->startOfMonth();

        $endDate = $request->filled('date_to')
            ? Carbon::parse($request->date_to)
            : now()->endOfMonth();

        // Get revenue summary
        $summary = $this->revenueService->getTotalRevenue($startDate, $endDate);
        $byCategory = $this->revenueService->getRevenueByCategory($startDate, $endDate);
        $byPaymentMethod = $this->revenueService->getRevenueByPaymentMethod($startDate, $endDate);
        $comparison = $this->revenueService->getRevenueComparison($startDate, $endDate);
        $trends = $this->revenueService->getRevenueTrends($startDate, $endDate);

        // Get filtered revenue data
        $filters = $request->only(['revenue_source', 'payment_method', 'student_id']);
        $filters['date_from'] = $startDate;
        $filters['date_to'] = $endDate;

        $revenues = $this->revenueService->getFilteredRevenue($filters);

        // Get filter options
        $students = Student::approved()
            ->with('user')
            ->orderBy('id', 'desc')
            ->take(100)
            ->get();

        return view('admin.revenue.index', compact(
            'summary',
            'byCategory',
            'byPaymentMethod',
            'comparison',
            'trends',
            'revenues',
            'students',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Display revenue by category breakdown
     *
     * ⚠️ FIX: Now passes $comparison variable to view
     */
    public function byCategory(Request $request)
    {
        $startDate = $request->filled('date_from')
            ? Carbon::parse($request->date_from)
            : now()->startOfMonth();

        $endDate = $request->filled('date_to')
            ? Carbon::parse($request->date_to)
            : now()->endOfMonth();

        $byCategory = $this->revenueService->getRevenueByCategory($startDate, $endDate);
        $totalRevenue = $this->revenueService->getTotalRevenue($startDate, $endDate);
        $topSources = $this->revenueService->getTopRevenueSources($startDate, $endDate, 10);

        // ⚠️ FIX: Add comparison data for trend indicators
        $comparison = $this->revenueService->getRevenueComparison($startDate, $endDate);

        return view('admin.revenue.by-category', compact(
            'byCategory',
            'totalRevenue',
            'topSources',
            'comparison',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Get revenue summary for specific period (AJAX)
     */
    public function getPeriodSummary(Request $request)
    {
        $period = $request->input('period', 'this_month');

        $summary = $this->revenueService->getRevenueSummary($period);

        return response()->json($summary);
    }

    /**
     * Export revenue report
     */
    public function export(Request $request)
    {
        $startDate = $request->filled('date_from')
            ? Carbon::parse($request->date_from)
            : now()->startOfMonth();

        $endDate = $request->filled('date_to')
            ? Carbon::parse($request->date_to)
            : now()->endOfMonth();

        $filters = $request->only(['revenue_source', 'payment_method', 'student_id']);
        $filters['date_from'] = $startDate;
        $filters['date_to'] = $endDate;

        $revenues = $this->revenueService->getFilteredRevenue($filters);

        $filename = 'revenue_' . $startDate->format('Ymd') . '_' . $endDate->format('Ymd') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($revenues) {
            $file = fopen('php://output', 'w');

            // Header row
            fputcsv($file, [
                'Payment Number',
                'Date',
                'Student',
                'Amount',
                'Revenue Source',
                'Payment Method',
                'Reference',
                'Status',
                'Processed By'
            ]);

            // Data rows
            foreach ($revenues as $revenue) {
                fputcsv($file, [
                    $revenue->payment_number,
                    $revenue->payment_date->format('Y-m-d'),
                    $revenue->student->user->name ?? 'N/A',
                    number_format($revenue->amount, 2),
                    $revenue->getRevenueSourceLabel(),
                    $revenue->payment_method,
                    $revenue->reference_number ?? '',
                    $revenue->status,
                    $revenue->processedBy->name ?? '',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get revenue chart data (AJAX)
     */
    public function getChartData(Request $request)
    {
        $startDate = $request->filled('date_from')
            ? Carbon::parse($request->date_from)
            : now()->startOfMonth();

        $endDate = $request->filled('date_to')
            ? Carbon::parse($request->date_to)
            : now()->endOfMonth();

        $type = $request->input('type', 'trends');

        if ($type === 'category') {
            $data = $this->revenueService->getRevenueByCategory($startDate, $endDate);

            return response()->json([
                'labels' => array_keys($data),
                'values' => array_values($data),
            ]);
        }

        if ($type === 'trends') {
            $trends = $this->revenueService->getRevenueTrends($startDate, $endDate);

            return response()->json([
                'labels' => $trends->pluck('date')->toArray(),
                'values' => $trends->pluck('total')->toArray(),
            ]);
        }

        return response()->json(['error' => 'Invalid chart type'], 400);
    }
}
