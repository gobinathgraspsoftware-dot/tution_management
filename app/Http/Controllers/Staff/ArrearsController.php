<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Student;
use App\Models\ClassModel;
use App\Models\Subject;
use App\Services\ArrearsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ArrearsController extends Controller
{
    protected $arrearsService;

    public function __construct(ArrearsService $arrearsService)
    {
        $this->arrearsService = $arrearsService;

        // Apply permission middleware for view-only access
        // $this->middleware('permission:view-arrears')->only(['index', 'studentsWithArrears']);
        // $this->middleware('permission:view-student-arrears')->only(['student']);
        // $this->middleware('permission:view-due-reports')->only(['dueReport']);
    }

    /**
     * Display arrears dashboard (view-only)
     */
    public function index(Request $request)
    {
        // Build filters
        $filters = $request->only([
            'date_from', 'date_to', 'class_id', 'subject_id',
            'status', 'student_id', 'days_overdue_min', 'amount_min'
        ]);
        $filters['paginate'] = true;
        $filters['per_page'] = 20;

        // Get arrears report
        $report = $this->arrearsService->getArrearsReport($filters);

        // Get dashboard stats
        $dashboardStats = $this->arrearsService->getDashboardStats();

        // Get critical arrears
        $criticalArrears = $this->arrearsService->getCriticalArrears();

        // Get filter options
        $classes = ClassModel::where('status', 'active')->orderBy('name')->get();
        $subjects = Subject::where('status', 'active')->orderBy('name')->get();
        $students = Student::approved()
            ->whereHas('invoices', function($q) {
                $q->unpaid();
            })
            ->with('user')
            ->get();

        return view('staff.arrears.index', compact(
            'report',
            'dashboardStats',
            'criticalArrears',
            'classes',
            'subjects',
            'students'
        ));
    }

    /**
     * View student's arrears details (view-only)
     */
    public function student(Student $student)
    {
        $arrearsData = $this->arrearsService->getStudentArrears($student);

        // Get payment history
        $paymentHistory = $student->payments()
            ->with('invoice')
            ->orderBy('payment_date', 'desc')
            ->limit(20)
            ->get();

        // Get reminder history
        $reminderHistory = \App\Models\PaymentReminder::where('student_id', $student->id)
            ->with('invoice')
            ->orderBy('scheduled_date', 'desc')
            ->limit(20)
            ->get();

        return view('staff.arrears.student', compact(
            'arrearsData',
            'paymentHistory',
            'reminderHistory'
        ));
    }

    /**
     * Display students with arrears list (view-only)
     */
    public function studentsWithArrears(Request $request)
    {
        $filters = $request->only(['min_arrears', 'class_id']);
        $studentsWithArrears = $this->arrearsService->getStudentsWithArrears($filters);

        // Get filter options
        $classes = ClassModel::where('status', 'active')->orderBy('name')->get();

        return view('staff.arrears.students-list', compact('studentsWithArrears', 'classes'));
    }

    /**
     * Display due report - upcoming dues (view-only)
     */
    public function dueReport(Request $request)
    {
        $daysAhead = $request->get('days', 30);
        $dueReport = $this->arrearsService->getDueReport($daysAhead);

        return view('staff.arrears.due-report', compact('dueReport', 'daysAhead'));
    }

    /**
     * Get arrears summary API endpoint (for AJAX requests)
     */
    public function getSummary()
    {
        $stats = $this->arrearsService->getDashboardStats();

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
