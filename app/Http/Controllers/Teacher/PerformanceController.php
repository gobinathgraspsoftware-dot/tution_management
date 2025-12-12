<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Services\TeacherPerformanceService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PerformanceController extends Controller
{
    protected $performanceService;

    public function __construct(TeacherPerformanceService $performanceService)
    {
        $this->performanceService = $performanceService;
    }

    /**
     * Display teacher's own performance dashboard
     */
    public function index(Request $request)
    {
        $teacher = auth()->user()->teacher;

        if (!$teacher) {
            abort(403, 'Teacher profile not found.');
        }

        // Default to current month
        $startDate = $request->filled('start_date') 
            ? Carbon::parse($request->start_date) 
            : now()->startOfMonth();
        
        $endDate = $request->filled('end_date') 
            ? Carbon::parse($request->end_date) 
            : now()->endOfMonth();

        // Get performance metrics
        $metrics = $this->performanceService->getPerformanceMetrics($teacher, $startDate, $endDate);

        // Get monthly trend (last 6 months)
        $trend = $this->performanceService->getMonthlyTrend($teacher, 6);

        // Get recent reviews
        $reviews = $this->performanceService->getRecentReviews($teacher, 10);

        // Get dashboard summary with comparisons
        $summary = $this->performanceService->getDashboardSummary($teacher);

        return view('teacher.performance.index', compact(
            'teacher',
            'metrics',
            'trend',
            'reviews',
            'summary',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Display detailed analytics
     */
    public function analytics(Request $request)
    {
        $teacher = auth()->user()->teacher;

        if (!$teacher) {
            abort(403, 'Teacher profile not found.');
        }

        // Get period selection (default: last 12 months)
        $months = $request->input('months', 12);
        $months = min($months, 24); // Max 24 months

        // Get monthly trend
        $trend = $this->performanceService->getMonthlyTrend($teacher, $months);

        // Get current month metrics
        $currentMetrics = $this->performanceService->getPerformanceMetrics($teacher);

        // Get previous month metrics for comparison
        $previousMetrics = $this->performanceService->getPerformanceMetrics(
            $teacher,
            now()->subMonth()->startOfMonth(),
            now()->subMonth()->endOfMonth()
        );

        // Calculate year-to-date metrics
        $ytdMetrics = $this->performanceService->getPerformanceMetrics(
            $teacher,
            now()->startOfYear(),
            now()
        );

        // Prepare chart data
        $chartData = [
            'labels' => array_column($trend, 'month'),
            'classes' => array_column($trend, 'classes'),
            'materials' => array_column($trend, 'materials'),
            'ratings' => array_column($trend, 'rating'),
            'attendance' => array_column($trend, 'attendance'),
            'scores' => array_column($trend, 'score'),
        ];

        return view('teacher.performance.analytics', compact(
            'teacher',
            'trend',
            'currentMetrics',
            'previousMetrics',
            'ytdMetrics',
            'chartData',
            'months'
        ));
    }

    /**
     * Get performance data via AJAX
     */
    public function getData(Request $request)
    {
        $teacher = auth()->user()->teacher;

        if (!$teacher) {
            return response()->json(['success' => false, 'message' => 'Teacher profile not found.'], 403);
        }

        $startDate = $request->filled('start_date') 
            ? Carbon::parse($request->start_date) 
            : now()->startOfMonth();
        
        $endDate = $request->filled('end_date') 
            ? Carbon::parse($request->end_date) 
            : now()->endOfMonth();

        $metrics = $this->performanceService->getPerformanceMetrics($teacher, $startDate, $endDate);

        return response()->json([
            'success' => true,
            'data' => $metrics,
        ]);
    }
}
