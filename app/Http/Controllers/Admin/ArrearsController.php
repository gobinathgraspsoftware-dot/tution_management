<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Student;
use App\Models\ClassModel;
use App\Models\Subject;
use App\Services\ArrearsService;
use App\Services\PaymentReminderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ArrearsController extends Controller
{
    protected $arrearsService;
    protected $reminderService;

    public function __construct(
        ArrearsService $arrearsService,
        PaymentReminderService $reminderService
    ) {
        $this->arrearsService = $arrearsService;
        $this->reminderService = $reminderService;
    }

    /**
     * Display arrears dashboard
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

        return view('admin.arrears.index', compact(
            'report',
            'dashboardStats',
            'criticalArrears',
            'classes',
            'subjects',
            'students'
        ));
    }

    /**
     * View student's arrears details
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

        return view('admin.arrears.student', compact(
            'arrearsData',
            'paymentHistory',
            'reminderHistory'
        ));
    }

    /**
     * Display students with arrears list
     */
    public function studentsWithArrears(Request $request)
    {
        $filters = $request->only(['min_arrears', 'class_id']);
        $studentsWithArrears = $this->arrearsService->getStudentsWithArrears($filters);

        // Get filter options
        $classes = ClassModel::where('status', 'active')->orderBy('name')->get();

        return view('admin.arrears.students-list', compact('studentsWithArrears', 'classes'));
    }

    /**
     * Display arrears by class
     */
    public function byClass()
    {
        $arrearsByClass = $this->arrearsService->getArrearsByClass();
        $totalArrears = $arrearsByClass->sum('total_arrears');

        return view('admin.arrears.by-class', compact('arrearsByClass', 'totalArrears'));
    }

    /**
     * Display arrears by subject
     */
    public function bySubject()
    {
        $arrearsBySubject = $this->arrearsService->getArrearsBySubject();
        $totalArrears = collect($arrearsBySubject)->sum('total_arrears');

        return view('admin.arrears.by-subject', compact('arrearsBySubject', 'totalArrears'));
    }

    /**
     * Display due report (upcoming dues)
     */
    public function dueReport(Request $request)
    {
        $daysAhead = $request->get('days', 30);
        $dueReport = $this->arrearsService->getDueReport($daysAhead);

        return view('admin.arrears.due-report', compact('dueReport', 'daysAhead'));
    }

    /**
     * Display collection forecast
     */
    public function forecast(Request $request)
    {
        $months = $request->get('months', 3);
        $forecast = $this->arrearsService->getCollectionForecast($months);

        return view('admin.arrears.forecast', compact('forecast', 'months'));
    }

    /**
     * Send bulk reminders to students with arrears
     */
    public function sendBulkReminders(Request $request)
    {
        $request->validate([
            'invoice_ids' => 'required|array',
            'invoice_ids.*' => 'exists:invoices,id',
        ]);

        $sent = 0;
        $failed = 0;

        foreach ($request->invoice_ids as $invoiceId) {
            $invoice = Invoice::find($invoiceId);
            if (!$invoice || $invoice->isPaid()) continue;

            try {
                $this->reminderService->sendFollowUpReminder($invoice);
                $sent++;
            } catch (\Exception $e) {
                $failed++;
            }
        }

        $message = "Sent {$sent} reminders.";
        if ($failed > 0) {
            $message .= " Failed: {$failed}.";
        }

        return back()->with($failed > 0 ? 'warning' : 'success', $message);
    }

    /**
     * Export arrears report
     */
    public function export(Request $request)
    {
        $filters = $request->only([
            'date_from', 'date_to', 'class_id', 'subject_id',
            'status', 'student_id', 'days_overdue_min'
        ]);

        $exportData = $this->arrearsService->exportArrearsReport($filters);

        $format = $request->get('format', 'csv');

        if ($format === 'csv') {
            return $this->exportCsv($exportData);
        }

        // Default to CSV
        return $this->exportCsv($exportData);
    }

    /**
     * Export as CSV
     */
    protected function exportCsv(array $exportData): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $filename = 'arrears_report_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($exportData) {
            $file = fopen('php://output', 'w');

            // Summary section
            fputcsv($file, ['ARREARS REPORT']);
            fputcsv($file, ['Generated:', $exportData['generated_at']]);
            fputcsv($file, []);
            fputcsv($file, ['SUMMARY']);
            fputcsv($file, ['Total Arrears:', 'RM ' . number_format($exportData['summary']['total_arrears'], 2)]);
            fputcsv($file, ['Total Invoices:', $exportData['summary']['total_invoices']]);
            fputcsv($file, ['Total Students:', $exportData['summary']['total_students']]);
            fputcsv($file, ['Overdue Count:', $exportData['summary']['overdue_count']]);
            fputcsv($file, []);

            // Arrears by age
            fputcsv($file, ['ARREARS BY AGE (Days Overdue)']);
            foreach ($exportData['summary']['by_age'] as $age => $data) {
                fputcsv($file, [
                    $age . ' days',
                    $data['count'] . ' invoices',
                    'RM ' . number_format($data['amount'], 2)
                ]);
            }
            fputcsv($file, []);

            // Detail section
            if (!empty($exportData['data'])) {
                fputcsv($file, ['DETAIL RECORDS']);
                fputcsv($file, array_keys($exportData['data'][0]));

                foreach ($exportData['data'] as $row) {
                    fputcsv($file, $row);
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Print arrears report
     */
    public function print(Request $request)
    {
        $filters = $request->only([
            'date_from', 'date_to', 'class_id', 'subject_id',
            'status', 'student_id'
        ]);

        $report = $this->arrearsService->getArrearsReport($filters);
        $dashboardStats = $this->arrearsService->getDashboardStats();

        return view('admin.arrears.print', compact('report', 'dashboardStats'));
    }

    /**
     * Flag student for follow-up
     */
    public function flagStudent(Request $request, Student $student)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $this->arrearsService->flagStudentForFollowUp($student, $request->reason);

        // Add activity log
        activity()
            ->performedOn($student)
            ->causedBy(auth()->user())
            ->withProperties(['reason' => $request->reason])
            ->log('Student flagged for arrears follow-up');

        return back()->with('success', 'Student flagged for follow-up successfully.');
    }

    /**
     * Get aging analysis
     */
    public function agingAnalysis()
    {
        $arrearsByAge = $this->arrearsService->getArrearsByAge();

        // Get detailed breakdown
        $detailedAging = [];
        $ranges = [
            '0-30' => [0, 30],
            '31-60' => [31, 60],
            '61-90' => [61, 90],
            '90+' => [91, 9999],
        ];

        foreach ($ranges as $label => $range) {
            $invoices = Invoice::with(['student.user', 'enrollment.package'])
                ->unpaid()
                ->when($range[1] < 9999, function($q) use ($range) {
                    $q->where('due_date', '>', now()->subDays($range[1]))
                      ->where('due_date', '<=', now()->subDays($range[0]));
                })
                ->when($range[1] === 9999, function($q) use ($range) {
                    $q->where('due_date', '<=', now()->subDays($range[0]));
                })
                ->orderBy('due_date', 'asc')
                ->get();

            $detailedAging[$label] = $invoices;
        }

        return view('admin.arrears.aging-analysis', compact('arrearsByAge', 'detailedAging'));
    }

    /**
     * Automated daily arrears update (for scheduler)
     */
    public function dailyUpdate()
    {
        // Update overdue invoices
        $overdueCount = Invoice::where('status', 'pending')
            ->where('due_date', '<', Carbon::today())
            ->update(['status' => 'overdue']);

        // Update overdue installments
        $overdueInstallments = \App\Models\Installment::whereIn('status', ['pending', 'partial'])
            ->where('due_date', '<', Carbon::today())
            ->update(['status' => 'overdue']);

        return response()->json([
            'success' => true,
            'updated_invoices' => $overdueCount,
            'updated_installments' => $overdueInstallments,
        ]);
    }

    /**
     * Get arrears summary API endpoint
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
