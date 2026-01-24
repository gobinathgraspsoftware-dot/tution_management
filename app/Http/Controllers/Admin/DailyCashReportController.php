<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PosService;
use App\Models\DailyCashReport;
use App\Models\PosTransaction;
use App\Exports\DailyCashReportExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class DailyCashReportController extends Controller
{
    protected $posService;

    public function __construct(PosService $posService)
    {
        $this->posService = $posService;
    }

    /**
     * Display all daily reports
     */
    public function index(Request $request)
    {
        $query = DailyCashReport::with('closedBy');
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by date range
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('report_date', [$request->start_date, $request->end_date]);
        } elseif ($request->filled('start_date')) {
            $query->where('report_date', '>=', $request->start_date);
        } elseif ($request->filled('end_date')) {
            $query->where('report_date', '<=', $request->end_date);
        }
        
        // Filter by month/year
        if ($request->filled('month') && $request->filled('year')) {
            $query->whereMonth('report_date', $request->month)
                  ->whereYear('report_date', $request->year);
        }
        
        $reports = $query->orderByDesc('report_date')->paginate(20)->withQueryString();
        
        // Calculate summary stats
        $totalCashSales = $reports->sum('total_cash_sales');
        $totalQrSales = $reports->sum('total_qr_sales');
        $totalVariance = $reports->sum('variance');
        
        return view('admin.pos.daily-reports.index', compact(
            'reports',
            'totalCashSales',
            'totalQrSales',
            'totalVariance'
        ));
    }

    /**
     * Display today's report
     */
    public function today()
    {
        $report = $this->posService->getTodayCashReport();
        $todayStats = $this->posService->getTodayStatistics();
        $transactions = $this->posService->getTransactionsByDate(today());
        $salesByCategory = $this->posService->getSalesByCategory(today());
        $topItems = $this->posService->getTopSellingItems(5, today(), today());
        
        return view('admin.pos.daily-reports.show', compact(
            'report',
            'todayStats',
            'transactions',
            'salesByCategory',
            'topItems'
        ))->with('isToday', true);
    }

    /**
     * Display specific report
     */
    public function show(DailyCashReport $report)
    {
        $report->load('closedBy');
        $transactions = $this->posService->getTransactionsByDate($report->report_date);
        $salesByCategory = $this->posService->getSalesByCategory($report->report_date);
        $topItems = $this->posService->getTopSellingItems(5, $report->report_date, $report->report_date);
        
        // Calculate stats
        $todayStats = [
            'total_sales' => $report->total_cash_sales + $report->total_qr_sales,
            'total_transactions' => $report->total_transactions,
            'cash_sales' => $report->total_cash_sales,
            'qr_sales' => $report->total_qr_sales,
            'voided_count' => $transactions->where('status', 'voided')->count(),
            'refunded_count' => $transactions->where('status', 'refunded')->count(),
        ];
        
        $isToday = $report->report_date->isToday();
        
        return view('admin.pos.daily-reports.show', compact(
            'report',
            'todayStats',
            'transactions',
            'salesByCategory',
            'topItems',
            'isToday'
        ));
    }

    /**
     * Open drawer form
     */
    public function openDrawer()
    {
        $report = $this->posService->getTodayCashReport();
        
        // Already open with opening cash
        if ($report && $report->opening_cash > 0) {
            return redirect()->route('admin.pos.daily-reports.today')
                ->with('info', 'Cash drawer is already open for today.');
        }
        
        return view('admin.pos.daily-reports.open-drawer', compact('report'));
    }

    /**
     * Set opening cash
     */
    public function setOpeningCash(Request $request)
    {
        $request->validate([
            'opening_cash' => 'required|numeric|min:0',
        ]);

        try {
            $this->posService->setOpeningCash($request->opening_cash);
            
            return redirect()->route('admin.pos.index')
                ->with('success', 'Cash drawer opened successfully with RM' . number_format($request->opening_cash, 2));
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Close day form
     */
    public function closeForm()
    {
        $report = $this->posService->getTodayCashReport();
        
        if (!$report) {
            return redirect()->route('admin.pos.daily-reports.open-drawer')
                ->with('error', 'No report found for today. Please open the drawer first.');
        }
        
        if ($report->status === 'closed') {
            return redirect()->route('admin.pos.daily-reports.today')
                ->with('info', 'Today\'s report is already closed.');
        }
        
        $transactions = $this->posService->getTransactionsByDate(today());
        $todayStats = $this->posService->getTodayStatistics();
        
        return view('admin.pos.daily-reports.close', compact('report', 'transactions', 'todayStats'));
    }

    /**
     * Close the day
     */
    public function closeDay(Request $request)
    {
        $request->validate([
            'actual_cash' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $report = $this->posService->closeDay($request->actual_cash, $request->notes);
            
            $message = 'Day closed successfully.';
            if ($report->variance != 0) {
                $varianceType = $report->variance > 0 ? 'surplus' : 'shortage';
                $message .= ' Variance: RM' . number_format(abs($report->variance), 2) . " ({$varianceType})";
            }
            
            return redirect()->route('admin.pos.daily-reports.show', $report)
                ->with('success', $message);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Export report to Excel
     */
    public function export(DailyCashReport $report)
    {
        return Excel::download(
            new DailyCashReportExport($report),
            'daily-cash-report-' . $report->report_date->format('Y-m-d') . '.xlsx'
        );
    }

    /**
     * Download report as PDF
     */
    public function downloadPdf(DailyCashReport $report)
    {
        $report->load('closedBy');
        $transactions = $this->posService->getTransactionsByDate($report->report_date);
        $salesByCategory = $this->posService->getSalesByCategory($report->report_date);
        
        $pdf = Pdf::loadView('admin.pos.daily-reports.pdf', compact(
            'report',
            'transactions',
            'salesByCategory'
        ));
        
        return $pdf->download('daily-cash-report-' . $report->report_date->format('Y-m-d') . '.pdf');
    }

    /**
     * Monthly summary
     */
    public function summary(Request $request)
    {
        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);
        
        $monthlySummary = $this->posService->getMonthlySummary($year, $month);
        
        // Get all reports for the month
        $reports = DailyCashReport::whereYear('report_date', $year)
            ->whereMonth('report_date', $month)
            ->with('closedBy')
            ->orderBy('report_date')
            ->get();
        
        // Get yearly data for comparison
        $yearlyData = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthData = $this->posService->getMonthlySummary($year, $m);
            $yearlyData[$m] = $monthData;
        }
        
        return view('admin.pos.daily-reports.summary', compact(
            'year',
            'month',
            'monthlySummary',
            'reports',
            'yearlyData'
        ));
    }
}
