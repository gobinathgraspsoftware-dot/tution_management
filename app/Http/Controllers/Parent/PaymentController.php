<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Invoice;
use App\Models\Student;
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
     * Display a listing of payments for parent's children.
     */
    public function index(Request $request)
    {
        $parent = auth()->user()->parent;
        $childrenIds = $parent->students->pluck('id')->toArray();

        $query = Payment::with(['invoice', 'student.user'])
            ->whereIn('student_id', $childrenIds);

        // Filter by child
        if ($request->filled('child_id')) {
            $query->where('student_id', $request->child_id);
        }

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

        // Get children for filter
        $children = $parent->students()->with('user')->get();

        // Get payment summary
        $summary = [
            'total_paid' => Payment::whereIn('student_id', $childrenIds)
                ->where('status', 'completed')
                ->sum('amount'),
            'this_month' => Payment::whereIn('student_id', $childrenIds)
                ->where('status', 'completed')
                ->whereMonth('payment_date', Carbon::now()->month)
                ->whereYear('payment_date', Carbon::now()->year)
                ->sum('amount'),
            'pending_invoices' => Invoice::whereIn('student_id', $childrenIds)
                ->unpaid()
                ->sum('balance'),
            'overdue_invoices' => Invoice::whereIn('student_id', $childrenIds)
                ->overdue()
                ->sum('balance'),
        ];

        $paymentStatuses = PaymentService::getPaymentStatuses();

        return view('parent.payments.index', compact(
            'payments',
            'children',
            'summary',
            'paymentStatuses'
        ));
    }

    /**
     * Display the specified payment.
     */
    public function show(Payment $payment)
    {
        // Verify payment belongs to parent's child
        $parent = auth()->user()->parent;
        $childrenIds = $parent->students->pluck('id')->toArray();

        if (!in_array($payment->student_id, $childrenIds)) {
            abort(403, 'Unauthorized access to this payment.');
        }

        $payment->load(['invoice.student.user', 'invoice.enrollment.package']);
        $receiptData = $this->receiptService->getReceiptForPreview($payment);

        return view('parent.payments.show', compact('payment', 'receiptData'));
    }

    /**
     * Display payment history.
     */
    public function history(Request $request)
    {
        $parent = auth()->user()->parent;
        $childrenIds = $parent->students->pluck('id')->toArray();

        // Get date range
        $dateFrom = $request->filled('date_from')
            ? Carbon::parse($request->date_from)
            : Carbon::now()->subMonths(6);
        $dateTo = $request->filled('date_to')
            ? Carbon::parse($request->date_to)
            : Carbon::now();

        $payments = Payment::with(['invoice', 'student.user'])
            ->whereIn('student_id', $childrenIds)
            ->whereBetween('payment_date', [$dateFrom, $dateTo]);

        // Filter by child
        if ($request->filled('child_id')) {
            $payments->where('student_id', $request->child_id);
        }

        $payments = $payments->orderBy('payment_date', 'desc')->paginate(20);

        // Summary by month
        $monthlySummary = Payment::whereIn('student_id', $childrenIds)
            ->where('status', 'completed')
            ->whereBetween('payment_date', [$dateFrom, $dateTo])
            ->selectRaw('YEAR(payment_date) as year, MONTH(payment_date) as month, SUM(amount) as total')
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        $children = $parent->students()->with('user')->get();

        return view('parent.payments.history', compact(
            'payments',
            'monthlySummary',
            'children',
            'dateFrom',
            'dateTo'
        ));
    }

    /**
     * View receipt.
     */
    public function receipt(Payment $payment)
    {
        // Verify payment belongs to parent's child
        $parent = auth()->user()->parent;
        $childrenIds = $parent->students->pluck('id')->toArray();

        if (!in_array($payment->student_id, $childrenIds)) {
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
        // Verify payment belongs to parent's child
        $parent = auth()->user()->parent;
        $childrenIds = $parent->students->pluck('id')->toArray();

        if (!in_array($payment->student_id, $childrenIds)) {
            abort(403, 'Unauthorized access to this payment.');
        }

        return $this->receiptService->downloadReceiptPdf($payment);
    }

    /**
     * View outstanding payments.
     */
    public function outstanding()
    {
        $parent = auth()->user()->parent;
        $childrenIds = $parent->students->pluck('id')->toArray();

        $unpaidInvoices = Invoice::with(['student.user', 'enrollment.package'])
            ->whereIn('student_id', $childrenIds)
            ->unpaid()
            ->orderBy('due_date')
            ->get();

        $children = $parent->students()->with('user')->get();

        $summary = [
            'total_outstanding' => $unpaidInvoices->sum('balance'),
            'total_overdue' => $unpaidInvoices->where('status', 'overdue')->sum('balance'),
            'due_this_week' => $unpaidInvoices->filter(function($inv) {
                return $inv->due_date && $inv->due_date->isBetween(Carbon::today(), Carbon::today()->addDays(7));
            })->sum('balance'),
        ];

        return view('parent.payments.outstanding', compact(
            'unpaidInvoices',
            'children',
            'summary'
        ));
    }
}
