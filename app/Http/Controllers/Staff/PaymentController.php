<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentRequest;
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
     * Display a listing of payments.
     */
    public function index(Request $request)
    {
        $query = Payment::with(['invoice', 'student.user', 'processedBy']);

        // Filter by date - default to today
        $date = $request->filled('date') ? Carbon::parse($request->date) : Carbon::today();

        if ($request->filled('date_filter') && $request->date_filter === 'range') {
            $dateFrom = $request->filled('date_from') ? Carbon::parse($request->date_from) : Carbon::today();
            $dateTo = $request->filled('date_to') ? Carbon::parse($request->date_to) : Carbon::today();
            $query->whereBetween('payment_date', [$dateFrom, $dateTo]);
        } else {
            $query->whereDate('payment_date', $date);
        }

        // Filter by payment method
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('payment_number', 'like', "%{$search}%")
                    ->orWhere('reference_number', 'like', "%{$search}%")
                    ->orWhereHas('student.user', function($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Get today's statistics
        $todayStats = $this->paymentService->getDailyCollectionSummary(Carbon::today());

        // Paginate results
        $payments = $query->orderBy('created_at', 'desc')->paginate(15);

        $paymentMethods = PaymentService::getManualPaymentMethods();

        return view('staff.payments.index', compact(
            'payments',
            'todayStats',
            'paymentMethods',
            'date'
        ));
    }

    /**
     * Show the form for creating a new payment.
     */
    public function create(Request $request)
    {
        $students = Student::approved()->with(['user'])->get();
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

        return view('staff.payments.create', compact(
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

            return redirect()->route('staff.payments.show', $payment)
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

        return view('staff.payments.show', compact('payment', 'receiptData'));
    }

    /**
     * Show receipt for printing.
     */
    public function receipt(Payment $payment)
    {
        $data = $this->receiptService->generateReceiptData($payment);
        return view('admin.payments.receipt', $data);
    }

    /**
     * Print receipt.
     */
    public function printReceipt(Payment $payment)
    {
        return $this->receiptService->streamReceiptPdf($payment);
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
     * Quick payment form (simplified).
     */
    public function quickPayment(Request $request)
    {
        // Search for student
        $student = null;
        $unpaidInvoices = collect();

        if ($request->filled('search')) {
            $search = $request->search;
            $student = Student::where('student_id', $search)
                ->orWhereHas('user', function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                })
                ->with('user')
                ->first();

            if ($student) {
                $unpaidInvoices = Invoice::forStudent($student->id)
                    ->unpaid()
                    ->orderBy('due_date')
                    ->get();
            }
        }

        $paymentMethods = PaymentService::getManualPaymentMethods();
        $qrSettings = $this->paymentService->getQRSettings();

        return view('staff.payments.quick-payment', compact(
            'student',
            'unpaidInvoices',
            'paymentMethods',
            'qrSettings'
        ));
    }

    /**
     * Today's collection summary.
     */
    public function todayCollection()
    {
        $summary = $this->paymentService->getDailyCollectionSummary(Carbon::today());

        $payments = Payment::with(['student.user', 'processedBy'])
            ->whereDate('payment_date', Carbon::today())
            ->where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('staff.payments.today-collection', compact('summary', 'payments'));
    }
}
