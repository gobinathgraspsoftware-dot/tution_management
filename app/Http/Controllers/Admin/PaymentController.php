<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentRequest;
use App\Models\Payment;
use App\Models\Invoice;
use App\Models\Student;
use App\Models\DailyCashReport;
use App\Services\PaymentService;
use App\Services\ReceiptService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PaymentController extends Controller
{
    protected PaymentService $paymentService;
    protected ReceiptService $receiptService;

    public function __construct(PaymentService $paymentService, ReceiptService $receiptService)
    {
        $this->paymentService = $paymentService;
        $this->receiptService = $receiptService;
    }

    /**
     * Display a listing of payments.
     */
    public function index(Request $request)
    {
        $query = Payment::with(['invoice', 'student.user', 'processedBy']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by payment method
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        // Filter by student
        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('payment_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('payment_date', '<=', $request->date_to);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('payment_number', 'like', "%{$search}%")
                    ->orWhere('reference_number', 'like', "%{$search}%")
                    ->orWhereHas('student.user', function($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('invoice', function($q2) use ($search) {
                        $q2->where('invoice_number', 'like', "%{$search}%");
                    });
            });
        }

        // Get statistics
        $statistics = $this->paymentService->getPaymentStatistics();

        // Paginate results
        $payments = $query->orderBy('payment_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Filter options
        $students = Student::approved()->with('user')->get();
        $paymentMethods = PaymentService::getPaymentMethods();
        $paymentStatuses = PaymentService::getPaymentStatuses();

        return view('admin.payments.index', compact(
            'payments',
            'statistics',
            'students',
            'paymentMethods',
            'paymentStatuses'
        ));
    }

    /**
     * Show the form for creating a new payment.
     */
    public function create(Request $request)
    {
        $students = Student::approved()->with(['user', 'enrollments.package'])->get();
        $paymentMethods = PaymentService::getManualPaymentMethods();
        $qrSettings = $this->paymentService->getQRSettings();

        $selectedStudent = null;
        $unpaidInvoices = collect();

        // Pre-select student if provided
        if ($request->filled('student_id')) {
            $selectedStudent = Student::with(['user', 'enrollments.package'])->find($request->student_id);
            if ($selectedStudent) {
                $unpaidInvoices = Invoice::forStudent($selectedStudent->id)
                    ->unpaid()
                    ->orderBy('due_date')
                    ->get();
            }
        }

        // Pre-select invoice if provided
        $selectedInvoice = null;
        if ($request->filled('invoice_id')) {
            $selectedInvoice = Invoice::with(['student.user'])->find($request->invoice_id);
            if ($selectedInvoice) {
                $selectedStudent = $selectedInvoice->student;
                $unpaidInvoices = Invoice::forStudent($selectedStudent->id)
                    ->unpaid()
                    ->orderBy('due_date')
                    ->get();
            }
        }

        return view('admin.payments.create', compact(
            'students',
            'paymentMethods',
            'qrSettings',
            'selectedStudent',
            'selectedInvoice',
            'unpaidInvoices'
        ));
    }

    /**
     * Store a newly created payment.
     */
    public function store(PaymentRequest $request)
    {
        $validated = $request->validated();

        try {
            $invoice = Invoice::findOrFail($validated['invoice_id']);

            // Handle screenshot for QR payments
            $screenshot = $request->hasFile('screenshot') ? $request->file('screenshot') : null;

            // Process payment based on method
            if ($validated['payment_method'] === 'qr') {
                $payment = $this->paymentService->processQRPayment($invoice, $validated, $screenshot);
            } else {
                $payment = $this->paymentService->processCashPayment($invoice, $validated);
            }

            return redirect()->route('admin.payments.show', $payment)
                ->with('success', "Payment {$payment->payment_number} recorded successfully!");

        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Failed to process payment: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified payment.
     */
    public function show(Payment $payment)
    {
        $payment->load(['invoice.student.user', 'invoice.student.parent.user', 'invoice.enrollment.package', 'processedBy']);

        $receiptData = $this->receiptService->getReceiptForPreview($payment);

        return view('admin.payments.show', compact('payment', 'receiptData'));
    }

    /**
     * Show receipt for a payment.
     */
    public function receipt(Payment $payment)
    {
        $data = $this->receiptService->generateReceiptData($payment);
        return view('admin.payments.receipt', $data);
    }

    /**
     * Download receipt PDF.
     */
    public function downloadReceipt(Payment $payment)
    {
        return $this->receiptService->downloadReceiptPdf($payment);
    }

    /**
     * Print receipt (stream PDF).
     */
    public function printReceipt(Payment $payment)
    {
        return $this->receiptService->streamReceiptPdf($payment);
    }

    /**
     * Verify a pending payment (QR payments).
     */
    public function verify(Request $request, Payment $payment)
    {
        $request->validate([
            'approved' => 'required|boolean',
            'verification_notes' => 'nullable|string|max:500',
        ]);

        try {
            $this->paymentService->verifyPayment(
                $payment,
                $request->boolean('approved'),
                $request->verification_notes
            );

            $status = $request->boolean('approved') ? 'verified' : 'rejected';
            return back()->with('success', "Payment has been {$status}.");

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to verify payment: ' . $e->getMessage());
        }
    }

    /**
     * Refund a payment.
     */
    public function refund(Request $request, Payment $payment)
    {
        $request->validate([
            'refund_amount' => 'required|numeric|min:0.01|max:' . $payment->amount,
            'refund_reason' => 'required|string|max:500',
        ]);

        try {
            $this->paymentService->refundPayment(
                $payment,
                $request->refund_amount,
                $request->refund_reason
            );

            return back()->with('success', 'Payment refunded successfully.');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to refund payment: ' . $e->getMessage());
        }
    }

    /**
     * Display payment history.
     */
    public function history(Request $request)
    {
        $query = Payment::with(['invoice', 'student.user', 'processedBy']);

        // Date range filter
        $dateFrom = $request->filled('date_from')
            ? Carbon::parse($request->date_from)
            : Carbon::now()->startOfMonth();
        $dateTo = $request->filled('date_to')
            ? Carbon::parse($request->date_to)
            : Carbon::now()->endOfMonth();

        $query->whereBetween('payment_date', [$dateFrom, $dateTo]);

        // Additional filters
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $payments = $query->orderBy('payment_date', 'desc')->paginate(20);

        // Statistics for the period
        $statistics = $this->paymentService->getPaymentStatistics($dateFrom, $dateTo);

        $paymentMethods = PaymentService::getPaymentMethods();
        $paymentStatuses = PaymentService::getPaymentStatuses();

        return view('admin.payments.history', compact(
            'payments',
            'statistics',
            'dateFrom',
            'dateTo',
            'paymentMethods',
            'paymentStatuses'
        ));
    }

    /**
     * Display daily cash report.
     */
    public function dailyReport(Request $request)
    {
        $date = $request->filled('date')
            ? Carbon::parse($request->date)
            : Carbon::today();

        // Get or create daily report
        $report = DailyCashReport::firstOrCreate(
            ['report_date' => $date->format('Y-m-d')],
            [
                'opening_cash' => 0,
                'total_cash_sales' => 0,
                'total_qr_sales' => 0,
                'total_transactions' => 0,
                'expected_closing' => 0,
                'status' => 'open'
            ]
        );

        // Get payments for the day
        $payments = Payment::with(['invoice', 'student.user', 'processedBy'])
            ->whereDate('payment_date', $date)
            ->where('status', 'completed')
            ->orderBy('created_at')
            ->get();

        // Summary by method
        $summary = $this->paymentService->getDailyCollectionSummary($date);

        // Previous report for opening balance reference
        $previousReport = DailyCashReport::where('report_date', '<', $date->format('Y-m-d'))
            ->orderBy('report_date', 'desc')
            ->first();

        return view('admin.payments.daily-report', compact(
            'report',
            'payments',
            'summary',
            'date',
            'previousReport'
        ));
    }

    /**
     * Update daily report (set opening/closing cash).
     */
    public function updateDailyReport(Request $request)
    {
        $request->validate([
            'report_date' => 'required|date',
            'opening_cash' => 'nullable|numeric|min:0',
            'actual_closing' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $report = DailyCashReport::where('report_date', $request->report_date)->first();

        if (!$report) {
            return back()->with('error', 'Report not found.');
        }

        if ($request->filled('opening_cash') && $report->status === 'open') {
            $report->opening_cash = $request->opening_cash;
            $report->expected_closing = $request->opening_cash + $report->total_cash_sales;
        }

        if ($request->filled('actual_closing')) {
            $report->actual_closing = $request->actual_closing;
            $report->variance = $report->actual_closing - $report->expected_closing;
        }

        if ($request->filled('notes')) {
            $report->notes = $request->notes;
        }

        $report->save();

        return back()->with('success', 'Report updated successfully.');
    }

    /**
     * Close daily report.
     */
    public function closeDailyReport(Request $request)
    {
        $request->validate([
            'report_date' => 'required|date',
            'actual_closing' => 'required|numeric|min:0',
        ]);

        $report = DailyCashReport::where('report_date', $request->report_date)->first();

        if (!$report) {
            return back()->with('error', 'Report not found.');
        }

        if ($report->status === 'closed') {
            return back()->with('error', 'Report is already closed.');
        }

        $report->actual_closing = $request->actual_closing;
        $report->variance = $request->actual_closing - $report->expected_closing;
        $report->closed_by = auth()->id();
        $report->status = 'closed';
        $report->save();

        return back()->with('success', 'Daily report closed successfully.');
    }

    /**
     * Get unpaid invoices for a student (AJAX).
     */
    public function getStudentInvoices(Student $student)
    {
        $invoices = Invoice::forStudent($student->id)
            ->unpaid()
            ->with('enrollment.package')
            ->orderBy('due_date')
            ->get()
            ->map(function($invoice) {
                return [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'type' => $invoice->type_label,
                    'billing_period' => $invoice->billing_period,
                    'total_amount' => $invoice->total_amount,
                    'paid_amount' => $invoice->paid_amount,
                    'balance' => $invoice->balance,
                    'due_date' => $invoice->due_date->format('d M Y'),
                    'status' => $invoice->status,
                    'is_overdue' => $invoice->isOverdue(),
                ];
            });

        return response()->json($invoices);
    }

    /**
     * Pending verifications list.
     */
    public function pendingVerifications()
    {
        $payments = $this->paymentService->getPendingVerifications();

        return view('admin.payments.pending-verifications', compact('payments'));
    }

    /**
     * Export payments.
     */
    public function export(Request $request)
    {
        $dateFrom = $request->filled('date_from')
            ? Carbon::parse($request->date_from)
            : Carbon::now()->startOfMonth();
        $dateTo = $request->filled('date_to')
            ? Carbon::parse($request->date_to)
            : Carbon::now()->endOfMonth();

        $filters = $request->only(['payment_method', 'status', 'student_id']);

        $payments = $this->paymentService->getPaymentsByDateRange($dateFrom, $dateTo, $filters);

        // Generate CSV
        $filename = 'payments_' . $dateFrom->format('Ymd') . '_' . $dateTo->format('Ymd') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($payments) {
            $file = fopen('php://output', 'w');

            // Header row
            fputcsv($file, [
                'Payment Number',
                'Date',
                'Student Name',
                'Student ID',
                'Invoice Number',
                'Amount',
                'Method',
                'Reference',
                'Status',
                'Processed By',
            ]);

            // Data rows
            foreach ($payments as $payment) {
                fputcsv($file, [
                    $payment->payment_number,
                    $payment->payment_date->format('Y-m-d'),
                    $payment->student->user->name ?? 'N/A',
                    $payment->student->student_id ?? 'N/A',
                    $payment->invoice->invoice_number ?? 'N/A',
                    $payment->amount,
                    ucfirst($payment->payment_method),
                    $payment->reference_number ?? 'N/A',
                    ucfirst($payment->status),
                    $payment->processedBy->name ?? 'System',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
