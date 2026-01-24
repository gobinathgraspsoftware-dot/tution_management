<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\InventoryCategory;
use App\Models\InventoryLog;
use App\Services\InventoryService;
use App\Services\StockAlertService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class InventoryReportController extends Controller
{
    protected $inventoryService;
    protected $stockAlertService;

    public function __construct(InventoryService $inventoryService, StockAlertService $stockAlertService)
    {
        $this->inventoryService = $inventoryService;
        $this->stockAlertService = $stockAlertService;
    }

    /**
     * Reports dashboard
     */
    public function index()
    {
        $statistics = $this->inventoryService->getStatistics();
        $categoryStats = $this->inventoryService->getCategoryStatistics();
        $stockSummary = $this->stockAlertService->getStockSummary();
        $reorderSuggestions = $this->stockAlertService->getReorderSuggestions();

        // Recent activity
        $recentActivity = InventoryLog::with(['inventory', 'createdBy'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.inventory.reports.index', compact(
            'statistics',
            'categoryStats',
            'stockSummary',
            'reorderSuggestions',
            'recentActivity'
        ));
    }

    /**
     * Movement report
     */
    public function movement(Request $request)
    {
        $startDate = $request->get('start_date')
            ? Carbon::parse($request->start_date)
            : Carbon::now()->startOfMonth();

        $endDate = $request->get('end_date')
            ? Carbon::parse($request->end_date)
            : Carbon::now()->endOfMonth();

        $categoryId = $request->get('category_id');

        $report = $this->inventoryService->getMovementReport($startDate, $endDate, $categoryId);
        $categories = InventoryCategory::active()->orderBy('name')->get();

        // Chart data
        $chartData = $this->getMovementChartData($startDate, $endDate, $categoryId);

        return view('admin.inventory.reports.movement', compact(
            'report',
            'categories',
            'startDate',
            'endDate',
            'categoryId',
            'chartData'
        ));
    }

    /**
     * Valuation report
     */
    public function valuation(Request $request)
    {
        $categoryId = $request->get('category_id');

        $report = $this->inventoryService->getValuationReport($categoryId);
        $categories = InventoryCategory::active()->orderBy('name')->get();

        // Chart data
        $chartData = $this->getValuationChartData($categoryId);

        return view('admin.inventory.reports.valuation', compact(
            'report',
            'categories',
            'categoryId',
            'chartData'
        ));
    }

    /**
     * Stock level report
     */
    public function stockLevels(Request $request)
    {
        $itemsByStatus = $this->stockAlertService->getItemsByStockStatus();
        $categories = InventoryCategory::active()->orderBy('name')->get();

        $categoryId = $request->get('category_id');
        if ($categoryId) {
            foreach ($itemsByStatus as $key => $items) {
                $itemsByStatus[$key] = $items->filter(function ($item) use ($categoryId) {
                    return $item->category_id == $categoryId;
                });
            }
        }

        return view('admin.inventory.reports.stock-levels', compact('itemsByStatus', 'categories', 'categoryId'));
    }

    /**
     * Category performance report
     */
    public function categoryPerformance(Request $request)
    {
        $startDate = $request->get('start_date')
            ? Carbon::parse($request->start_date)
            : Carbon::now()->startOfMonth();

        $endDate = $request->get('end_date')
            ? Carbon::parse($request->end_date)
            : Carbon::now()->endOfMonth();

        $categories = InventoryCategory::with(['inventoryItems' => function ($q) {
            $q->withCount(['logs as sales_count' => function ($query) {
                $query->where('type', 'sale');
            }]);
        }])
            ->withCount('inventoryItems')
            ->withSum('inventoryItems', \DB::raw('current_stock * selling_price'))
            ->get();

        // Calculate sales per category
        $categoryPerformance = [];
        foreach ($categories as $category) {
            $salesLogs = InventoryLog::whereIn('inventory_id', $category->inventoryItems->pluck('id'))
                ->where('type', 'sale')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get();

            $totalSalesQuantity = $salesLogs->sum('quantity');
            $totalSalesValue = 0;

            foreach ($salesLogs as $log) {
                $item = $category->inventoryItems->firstWhere('id', $log->inventory_id);
                if ($item) {
                    $totalSalesValue += $log->quantity * $item->selling_price;
                }
            }

            $categoryPerformance[] = [
                'category' => $category,
                'total_items' => $category->inventory_items_count,
                'total_stock_value' => $category->inventory_items_sum_dbrawcurrent_stock__selling_price ?? 0,
                'total_sales_quantity' => $totalSalesQuantity,
                'total_sales_value' => $totalSalesValue,
            ];
        }

        return view('admin.inventory.reports.category-performance', compact(
            'categoryPerformance',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Export movement report
     */
    public function exportMovement(Request $request)
    {
        $startDate = $request->get('start_date')
            ? Carbon::parse($request->start_date)
            : Carbon::now()->startOfMonth();

        $endDate = $request->get('end_date')
            ? Carbon::parse($request->end_date)
            : Carbon::now()->endOfMonth();

        $categoryId = $request->get('category_id');

        return (new \App\Exports\InventoryReportExport('movement', [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'category_id' => $categoryId,
        ]))->download('inventory_movement_report_' . date('Y-m-d') . '.xlsx');
    }

    /**
     * Export valuation report
     */
    public function exportValuation(Request $request)
    {
        $categoryId = $request->get('category_id');

        return (new \App\Exports\InventoryReportExport('valuation', [
            'category_id' => $categoryId,
        ]))->download('inventory_valuation_report_' . date('Y-m-d') . '.xlsx');
    }

    /**
     * Get movement chart data
     */
    protected function getMovementChartData(Carbon $startDate, Carbon $endDate, ?int $categoryId = null): array
    {
        $labels = [];
        $addedData = [];
        $removedData = [];
        $salesData = [];

        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            $labels[] = $currentDate->format('M d');

            $query = InventoryLog::whereDate('created_at', $currentDate);

            if ($categoryId) {
                $query->whereHas('inventory', function ($q) use ($categoryId) {
                    $q->where('category_id', $categoryId);
                });
            }

            $dayLogs = $query->get();

            $addedData[] = $dayLogs->where('type', 'add')->sum('quantity');
            $removedData[] = $dayLogs->where('type', 'remove')->sum('quantity');
            $salesData[] = $dayLogs->where('type', 'sale')->sum('quantity');

            $currentDate->addDay();
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Added',
                    'data' => $addedData,
                    'backgroundColor' => 'rgba(40, 167, 69, 0.5)',
                    'borderColor' => 'rgb(40, 167, 69)',
                ],
                [
                    'label' => 'Removed',
                    'data' => $removedData,
                    'backgroundColor' => 'rgba(255, 193, 7, 0.5)',
                    'borderColor' => 'rgb(255, 193, 7)',
                ],
                [
                    'label' => 'Sales',
                    'data' => $salesData,
                    'backgroundColor' => 'rgba(0, 123, 255, 0.5)',
                    'borderColor' => 'rgb(0, 123, 255)',
                ],
            ],
        ];
    }

    /**
     * Get valuation chart data
     */
    protected function getValuationChartData(?int $categoryId = null): array
    {
        $query = InventoryCategory::withSum(['inventoryItems' => function ($q) {
            $q->where('status', 'active');
        }], \DB::raw('current_stock * cost_price'));

        $categories = $query->get();

        $labels = $categories->pluck('name')->toArray();
        $data = $categories->map(function ($cat) {
            return $cat->inventory_items_sum_dbrawcurrent_stock__cost_price ?? 0;
        })->toArray();

        $colors = [
            'rgba(255, 99, 132, 0.8)',
            'rgba(54, 162, 235, 0.8)',
            'rgba(255, 206, 86, 0.8)',
            'rgba(75, 192, 192, 0.8)',
            'rgba(153, 102, 255, 0.8)',
            'rgba(255, 159, 64, 0.8)',
            'rgba(199, 199, 199, 0.8)',
            'rgba(83, 102, 255, 0.8)',
        ];

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => array_slice($colors, 0, count($labels)),
                ],
            ],
        ];
    }
}
