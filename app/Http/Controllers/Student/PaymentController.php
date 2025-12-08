<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Invoice;
use App\Services\PaymentService;
use App\Services\ReceiptService;
use Illuminate\Http\Request;
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
     * Display a listing of student's payments.
     */
    public function index(Request $request)
    {
        $student = auth()->user()->student;

        if (!$student) {
            return redirect()->back()->with('error', 'Student profile not found.');
        }

        $query = Payment::with(['invoice'])
            ->where('student_id', $student->id);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('payment_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('payment_date', '<=', $request->date_to);
        }

        $payments = $query->orderBy('payment_date', 'desc')->paginate(15);

        // Payment summary
        $summary = [
            'total_paid' => Payment::where('student_id', $student->id)
                ->where('status', 'completed')
                ->sum('amount'),
            'this_month' => Payment::where('student_id', $student->id)
                ->where('status', 'completed')
                ->whereMonth('payment_date', Carbon::now()->month)
                ->whereYear('payment_date', Carbon::now()->year)
                ->sum('amount'),
            'pending_amount' => Invoice::where('student_id', $student->id)
                ->unpaid()
                ->sum('balance'),
        ];

        $paymentStatuses = PaymentService::getPaymentStatuses();

        return view('student.payments.index', compact(
            'payments',
            'summary',
            'paymentStatuses'
        ));
    }

    /**
     * Display the specified payment.
     */
    public function show(Payment $payment)
    {
        $student = auth()->user()->student;

        // Verify payment belongs to this student
        if ($payment->student_id !== $student->id) {
            abort(403, 'Unauthorized access to this payment.');
        }

        $payment->load(['invoice.enrollment.package']);
        $receiptData = $this->receiptService->getReceiptForPreview($payment);

        return view('student.payments.show', compact('payment', 'receiptData'));
    }

    /**
     * Display payment history.
     */
    public function history(Request $request)
    {
        $student = auth()->user()->student;

        if (!$student) {
            return redirect()->back()->with('error', 'Student profile not found.');
        }

        // Get date range
        $dateFrom = $request->filled('date_from')
            ? Carbon::parse($request->date_from)
            : Carbon::now()->subMonths(6);
        $dateTo = $request->filled('date_to')
            ? Carbon::parse($request->date_to)
            : Carbon::now();

        $payments = Payment::with(['invoice'])
            ->where('student_id', $student->id)
            ->whereBetween('payment_date', [$dateFrom, $dateTo])
            ->orderBy('payment_date', 'desc')
            ->paginate(20);

        // Summary by month
        $monthlySummary = Payment::where('student_id', $student->id)
            ->where('status', 'completed')
            ->whereBetween('payment_date', [$dateFrom, $dateTo])
            ->selectRaw('YEAR(payment_date) as year, MONTH(payment_date) as month, SUM(amount) as total')
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        return view('student.payments.history', compact(
            'payments',
            'monthlySummary',
            'dateFrom',
            'dateTo'
        ));
    }

    /**
     * View receipt.
     */
    public function receipt(Payment $payment)
    {
        $student = auth()->user()->student;

        // Verify payment belongs to this student
        if ($payment->student_id !== $student->id) {
            abort(403, 'Unauthorized access to this payment.');
        }

        $data = $this->receiptService->generateReceiptData($payment);
        return view('admin.payments.receipt', $data);
    }

    /**
     * Download receipt.
     */
    public function downloadReceipt(Payment $payment)
    {
        $student = auth()->user()->student;

        // Verify payment belongs to this student
        if ($payment->student_id !== $student->id) {
            abort(403, 'Unauthorized access to this payment.');
        }

        return $this->receiptService->downloadReceiptPdf($payment);
    }

    /**
     * View outstanding invoices.
     */
    public function outstanding()
    {
        $student = auth()->user()->student;

        if (!$student) {
            return redirect()->back()->with('error', 'Student profile not found.');
        }

        $unpaidInvoices = Invoice::with(['enrollment.package'])
            ->where('student_id', $student->id)
            ->unpaid()
            ->orderBy('due_date')
            ->get();

        $summary = [
            'total_outstanding' => $unpaidInvoices->sum('balance'),
            'total_overdue' => $unpaidInvoices->where('status', 'overdue')->sum('balance'),
            'next_due' => $unpaidInvoices->sortBy('due_date')->first(),
        ];

        return view('student.payments.outstanding', compact(
            'unpaidInvoices',
            'summary'
        ));
    }
}
