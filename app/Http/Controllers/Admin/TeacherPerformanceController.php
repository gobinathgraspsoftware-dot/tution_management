<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\ActivityLog;
use App\Services\TeacherPerformanceService;
use App\Exports\TeacherPerformanceExport;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class TeacherPerformanceController extends Controller
{
    protected $performanceService;

    public function __construct(TeacherPerformanceService $performanceService)
    {
        $this->performanceService = $performanceService;
    }

    /**
     * Display performance dashboard with all teachers
     */
    public function index(Request $request)
    {
        // Increase execution time limit for large datasets
        set_time_limit(300); // 5 minutes
        
        // Default to current month
        $startDate = $request->filled('start_date') 
            ? Carbon::parse($request->start_date) 
            : now()->startOfMonth();
        
        $endDate = $request->filled('end_date') 
            ? Carbon::parse($request->end_date) 
            : now()->endOfMonth();

        // Get comparative data
        $performanceData = $this->performanceService->getComparativeData($startDate, $endDate);

        // Get top performers
        $topPerformers = $this->performanceService->getTopPerformers(5, $startDate, $endDate);

        // Filter by employment type if requested
        if ($request->filled('employment_type')) {
            $performanceData = array_filter($performanceData, function($item) use ($request) {
                return $item['employment_type'] === $request->employment_type;
            });
        }

        // Search by teacher name
        if ($request->filled('search')) {
            $search = strtolower($request->search);
            $performanceData = array_filter($performanceData, function($item) use ($search) {
                return str_contains(strtolower($item['teacher_name']), $search);
            });
        }

        // Statistics
        $stats = [
            'total_teachers' => count($performanceData),
            'avg_score' => count($performanceData) > 0 
                ? round(array_sum(array_column($performanceData, 'score')) / count($performanceData), 2) 
                : 0,
            'avg_rating' => count($performanceData) > 0 
                ? round(array_sum(array_column($performanceData, 'rating')) / count($performanceData), 2) 
                : 0,
            'total_classes' => array_sum(array_column($performanceData, 'classes')),
            'total_hours' => round(array_sum(array_column($performanceData, 'hours')), 2),
        ];

        return view('admin.teacher-performance.index', compact(
            'performanceData',
            'topPerformers',
            'stats',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Display individual teacher performance details
     */
    public function show(Request $request, Teacher $teacher)
    {
        // Default to current month
        $startDate = $request->filled('start_date') 
            ? Carbon::parse($request->start_date) 
            : now()->startOfMonth();
        
        $endDate = $request->filled('end_date') 
            ? Carbon::parse($request->end_date) 
            : now()->endOfMonth();

        $teacher->load('user');

        // Get performance metrics
        $metrics = $this->performanceService->getPerformanceMetrics($teacher, $startDate, $endDate);

        // Get monthly trend (last 6 months)
        $trend = $this->performanceService->getMonthlyTrend($teacher, 6);

        // Get recent reviews
        $reviews = $this->performanceService->getRecentReviews($teacher, 10);

        // Get dashboard summary
        $summary = $this->performanceService->getDashboardSummary($teacher);

        return view('admin.teacher-performance.show', compact(
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
     * Display performance comparison view
     */
    public function comparison(Request $request)
    {
        // Default to current month
        $startDate = $request->filled('start_date') 
            ? Carbon::parse($request->start_date) 
            : now()->startOfMonth();
        
        $endDate = $request->filled('end_date') 
            ? Carbon::parse($request->end_date) 
            : now()->endOfMonth();

        // Get teachers to compare (up to 5)
        $teacherIds = $request->input('teachers', []);
        
        if (empty($teacherIds)) {
            // Default: top 5 performers
            $topPerformers = $this->performanceService->getTopPerformers(5, $startDate, $endDate);
            $teacherIds = array_column($topPerformers, 'teacher_id');
        }

        // Limit to 5 teachers
        $teacherIds = array_slice($teacherIds, 0, 5);

        $comparisonData = [];
        $chartData = [
            'labels' => [],
            'classes' => [],
            'materials' => [],
            'ratings' => [],
            'scores' => [],
        ];

        foreach ($teacherIds as $teacherId) {
            $teacher = Teacher::with('user')->find($teacherId);
            if ($teacher) {
                $metrics = $this->performanceService->getPerformanceMetrics($teacher, $startDate, $endDate);
                
                $comparisonData[] = [
                    'teacher' => $teacher,
                    'metrics' => $metrics,
                ];

                $chartData['labels'][] = $teacher->user->name;
                $chartData['classes'][] = $metrics['classes_conducted'];
                $chartData['materials'][] = $metrics['materials_uploaded'];
                $chartData['ratings'][] = $metrics['average_rating'];
                $chartData['scores'][] = $metrics['performance_score'];
            }
        }

        // Get all teachers for selection dropdown
        $allTeachers = Teacher::with('user')->active()->get();

        return view('admin.teacher-performance.comparison', compact(
            'comparisonData',
            'chartData',
            'allTeachers',
            'teacherIds',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Display performance reports
     */
    public function reports(Request $request)
    {
        // Default to current month
        $startDate = $request->filled('start_date') 
            ? Carbon::parse($request->start_date) 
            : now()->startOfMonth();
        
        $endDate = $request->filled('end_date') 
            ? Carbon::parse($request->end_date) 
            : now()->endOfMonth();

        // Get report type
        $reportType = $request->input('report_type', 'summary');

        $reportData = null;

        switch ($reportType) {
            case 'summary':
                $reportData = $this->performanceService->getComparativeData($startDate, $endDate);
                break;

            case 'top_performers':
                $reportData = $this->performanceService->getTopPerformers(10, $startDate, $endDate);
                break;

            case 'detailed':
                $teacherId = $request->input('teacher_id');
                if ($teacherId) {
                    $teacher = Teacher::find($teacherId);
                    if ($teacher) {
                        $reportData = $this->performanceService->getReportData($teacher, $startDate, $endDate);
                    }
                }
                break;

            case 'monthly_trend':
                $reportData = [];
                $teachers = Teacher::active()->get();
                foreach ($teachers as $teacher) {
                    $reportData[] = [
                        'teacher' => $teacher->user->name,
                        'trend' => $this->performanceService->getMonthlyTrend($teacher, 6),
                    ];
                }
                break;
        }

        // Get all teachers for selection
        $teachers = Teacher::with('user')->active()->get();

        return view('admin.teacher-performance.reports', compact(
            'reportData',
            'reportType',
            'teachers',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Export performance data to Excel
     */
    public function export(Request $request)
    {
        $startDate = $request->filled('start_date') 
            ? Carbon::parse($request->start_date) 
            : now()->startOfMonth();
        
        $endDate = $request->filled('end_date') 
            ? Carbon::parse($request->end_date) 
            : now()->endOfMonth();

        $filters = [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'employment_type' => $request->employment_type,
        ];

        // Log activity
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'export',
            'model_type' => 'TeacherPerformance',
            'description' => 'Exported teacher performance report',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return Excel::download(
            new TeacherPerformanceExport($filters, $this->performanceService),
            'teacher-performance-' . date('Y-m-d') . '.xlsx'
        );
    }

    /**
     * Get performance data via AJAX
     */
    public function getData(Request $request, Teacher $teacher)
    {
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
