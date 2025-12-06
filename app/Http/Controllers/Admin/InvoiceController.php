<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\InvoiceRequest;
use App\Models\Invoice;
use App\Models\Student;
use App\Models\Enrollment;
use App\Models\Package;
use App\Services\InvoiceService;
use App\Services\PaymentCycleService;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InvoiceController extends Controller
{
    protected $invoiceService;
    protected $paymentCycleService;
    protected $subscriptionService;

    public function __construct(
        InvoiceService $invoiceService,
        PaymentCycleService $paymentCycleService,
        SubscriptionService $subscriptionService
    ) {
        $this->invoiceService = $invoiceService;
        $this->paymentCycleService = $paymentCycleService;
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Display a listing of invoices.
     */
    public function index(Request $request)
    {
        $query = Invoice::with(['student.user', 'enrollment.package']);

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'overdue') {
                $query->overdue();
            } else {
                $query->where('status', $request->status);
            }
        }

        // Filter by student
        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search by invoice number
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('student.user', function($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Get statistics
        $statistics = $this->invoiceService->getInvoiceStatistics();

        // Paginate results
        $invoices = $query->orderBy('created_at', 'desc')->paginate(15);

        // Get filter options
        $students = Student::approved()->with('user')->get();

        return view('admin.invoices.index', compact('invoices', 'statistics', 'students'));
    }

    /**
     * Show the form for creating a new invoice.
     */
    public function create(Request $request)
    {
        $students = Student::approved()
            ->with(['user', 'enrollments.package'])
            ->get();

        $selectedStudent = null;
        $selectedEnrollment = null;

        if ($request->filled('student_id')) {
            $selectedStudent = Student::with(['enrollments.package'])->find($request->student_id);
        }

        if ($request->filled('enrollment_id')) {
            $selectedEnrollment = Enrollment::with('package')->find($request->enrollment_id);
        }

        return view('admin.invoices.create', compact('students', 'selectedStudent', 'selectedEnrollment'));
    }

    /**
     * Store a newly created invoice.
     */
    public function store(InvoiceRequest $request)
    {
        $validated = $request->validated();

        try {
            DB::beginTransaction();

            // Calculate total amount
            $totalAmount = $validated['subtotal']
                + ($validated['online_fee'] ?? 0)
                - ($validated['discount'] ?? 0)
                + ($validated['tax'] ?? 0);

            $invoice = Invoice::create([
                'invoice_number' => Invoice::generateInvoiceNumber(),
                'student_id' => $validated['student_id'],
                'enrollment_id' => $validated['enrollment_id'] ?? null,
                'type' => $validated['type'],
                'billing_period_start' => $validated['billing_period_start'],
                'billing_period_end' => $validated['billing_period_end'],
                'subtotal' => $validated['subtotal'],
                'online_fee' => $validated['online_fee'] ?? 0,
                'discount' => $validated['discount'] ?? 0,
                'discount_reason' => $validated['discount_reason'] ?? null,
                'tax' => $validated['tax'] ?? 0,
                'total_amount' => $totalAmount,
                'paid_amount' => 0,
                'due_date' => $validated['due_date'],
                'status' => 'pending',
                'reminder_count' => 0,
                'notes' => $validated['notes'] ?? null,
            ]);

            DB::commit();

            return redirect()->route('admin.invoices.show', $invoice)
                ->with('success', "Invoice {$invoice->invoice_number} created successfully.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to create invoice. ' . $e->getMessage());
        }
    }

    /**
     * Display the specified invoice.
     */
    public function show(Invoice $invoice)
    {
        $invoice->load([
            'student.user',
            'student.parent.user',
            'enrollment.package',
            'enrollment.class',
            'payments.processedBy',
            'installments',
            'reminders',
        ]);

        return view('admin.invoices.show', compact('invoice'));
    }

    /**
     * Show the form for editing the specified invoice.
     */
    public function edit(Invoice $invoice)
    {
        if ($invoice->status === 'paid') {
            return back()->with('error', 'Cannot edit a paid invoice.');
        }

        $invoice->load(['student.user', 'enrollment.package']);
        $students = Student::approved()->with('user')->get();

        return view('admin.invoices.edit', compact('invoice', 'students'));
    }

    /**
     * Update the specified invoice.
     */
    public function update(InvoiceRequest $request, Invoice $invoice)
    {
        if ($invoice->status === 'paid') {
            return back()->with('error', 'Cannot edit a paid invoice.');
        }

        $validated = $request->validated();

        try {
            // Recalculate total
            $totalAmount = ($validated['subtotal'] ?? $invoice->subtotal)
                + ($validated['online_fee'] ?? $invoice->online_fee)
                - ($validated['discount'] ?? $invoice->discount)
                + ($validated['tax'] ?? $invoice->tax);

            $invoice->update(array_merge($validated, [
                'total_amount' => $totalAmount,
            ]));

            // Update status if overdue
            if ($invoice->due_date->isPast() && $invoice->status === 'pending') {
                $invoice->update(['status' => 'overdue']);
            }

            return redirect()->route('admin.invoices.show', $invoice)
                ->with('success', 'Invoice updated successfully.');

        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Failed to update invoice. ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified invoice.
     */
    public function destroy(Invoice $invoice)
    {
        if ($invoice->paid_amount > 0) {
            return back()->with('error', 'Cannot delete invoice with payments. Please refund first.');
        }

        try {
            $invoiceNumber = $invoice->invoice_number;
            $invoice->delete();

            return redirect()->route('admin.invoices.index')
                ->with('success', "Invoice {$invoiceNumber} deleted successfully.");
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete invoice. ' . $e->getMessage());
        }
    }

    /**
     * Cancel an invoice.
     */
    public function cancel(Request $request, Invoice $invoice)
    {
        try {
            $this->invoiceService->cancelInvoice($invoice, $request->reason);

            return back()->with('success', "Invoice {$invoice->invoice_number} cancelled successfully.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Apply discount to invoice.
     */
    public function applyDiscount(Request $request, Invoice $invoice)
    {
        $request->validate([
            'discount_amount' => 'required|numeric|min:0|max:' . $invoice->balance,
            'discount_reason' => 'required|string|max:255',
        ]);

        try {
            $this->invoiceService->applyDiscount(
                $invoice,
                $request->discount_amount,
                $request->discount_reason
            );

            return back()->with('success', 'Discount applied successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to apply discount. ' . $e->getMessage());
        }
    }

    /**
     * Send invoice to student/parent.
     */
    public function send(Invoice $invoice)
    {
        try {
            // Here you would integrate with notification service
            // For now, we'll just mark that a reminder was sent
            $invoice->sendReminder();

            return back()->with('success', 'Invoice sent successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to send invoice. ' . $e->getMessage());
        }
    }

    /**
     * Display bulk generate form.
     */
    public function bulkGenerateForm(Request $request)
    {
        $month = $request->filled('month')
            ? Carbon::parse($request->month)
            : Carbon::now();

        // Get enrollments that would receive invoices
        $enrollments = Enrollment::active()
            ->with(['student.user', 'package'])
            ->whereHas('student', function($q) {
                $q->where('status', 'approved');
            })
            ->get()
            ->map(function($enrollment) use ($month) {
                $hasInvoice = Invoice::where('enrollment_id', $enrollment->id)
                    ->where('billing_period_start', $month->copy()->startOfMonth())
                    ->where('billing_period_end', $month->copy()->endOfMonth())
                    ->whereNotIn('status', ['cancelled', 'refunded'])
                    ->exists();

                return [
                    'enrollment' => $enrollment,
                    'has_invoice' => $hasInvoice,
                    'monthly_fee' => $enrollment->monthly_fee ?? $enrollment->package->price,
                ];
            });

        return view('admin.invoices.bulk-generate', compact('enrollments', 'month'));
    }

    /**
     * Bulk generate invoices.
     */
    public function bulkGenerate(Request $request)
    {
        $request->validate([
            'month' => 'required|date_format:Y-m',
            'enrollment_ids' => 'nullable|array',
            'enrollment_ids.*' => 'exists:enrollments,id',
        ]);

        $month = Carbon::parse($request->month);

        try {
            if ($request->filled('enrollment_ids')) {
                // Generate for selected enrollments only
                $results = [
                    'generated' => 0,
                    'skipped' => 0,
                    'failed' => 0,
                    'errors' => [],
                ];

                foreach ($request->enrollment_ids as $enrollmentId) {
                    $enrollment = Enrollment::find($enrollmentId);
                    if (!$enrollment) continue;

                    try {
                        $invoice = $this->invoiceService->generateInvoice($enrollment, [
                            'billing_start' => $month->copy()->startOfMonth(),
                            'billing_end' => $month->copy()->endOfMonth(),
                            'type' => 'monthly',
                        ]);

                        if ($invoice) {
                            $results['generated']++;
                        } else {
                            $results['skipped']++;
                        }
                    } catch (\Exception $e) {
                        $results['failed']++;
                        $results['errors'][] = "Enrollment #{$enrollmentId}: " . $e->getMessage();
                    }
                }
            } else {
                // Generate for all active enrollments
                $results = $this->invoiceService->generateMonthlyInvoices($month);
            }

            $message = "Generated {$results['generated']} invoices.";
            if ($results['skipped'] > 0) {
                $message .= " Skipped {$results['skipped']} (already exist).";
            }
            if ($results['failed'] > 0) {
                $message .= " Failed: {$results['failed']}.";
            }

            return redirect()->route('admin.invoices.index')
                ->with($results['failed'] > 0 ? 'warning' : 'success', $message);

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to generate invoices. ' . $e->getMessage());
        }
    }

    /**
     * Display overdue invoices.
     */
    public function overdue(Request $request)
    {
        $overdueInvoices = $this->paymentCycleService->getOverduePaymentCycles();
        $studentsWithIssues = $this->paymentCycleService->getStudentsWithPaymentIssues();

        return view('admin.invoices.overdue', compact('overdueInvoices', 'studentsWithIssues'));
    }

    /**
     * Display payment cycles tracking.
     */
    public function paymentCycles(Request $request)
    {
        $month = $request->filled('month')
            ? Carbon::parse($request->month)
            : Carbon::now();

        $cycleOverview = $this->paymentCycleService->getPaymentCycleOverview($month);
        $upcomingCycles = $this->paymentCycleService->getUpcomingPaymentCycles(7);
        $monthlySummary = $this->paymentCycleService->getMonthlyPaymentSummary(6);

        return view('admin.billing.payment-cycles', compact(
            'cycleOverview',
            'upcomingCycles',
            'monthlySummary',
            'month'
        ));
    }

    /**
     * Display subscription alerts.
     */
    public function subscriptionAlerts(Request $request)
    {
        $summary = $this->subscriptionService->getSubscriptionSummary();
        $expiringEnrollments = $this->subscriptionService->getExpiringEnrollments(30);
        $expiredEnrollments = $this->subscriptionService->getExpiredEnrollments();
        $needingAttention = $this->subscriptionService->getStudentsNeedingAttention();

        return view('admin.billing.subscription-alerts', compact(
            'summary',
            'expiringEnrollments',
            'expiredEnrollments',
            'needingAttention'
        ));
    }

    /**
     * Renew enrollment.
     */
    public function renewEnrollment(Request $request, Enrollment $enrollment)
    {
        $request->validate([
            'months' => 'required|integer|min:1|max:24',
            'generate_invoice' => 'boolean',
        ]);

        try {
            $this->subscriptionService->renewEnrollment(
                $enrollment,
                $request->months,
                $request->boolean('generate_invoice', true)
            );

            return back()->with('success', 'Enrollment renewed successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to renew enrollment. ' . $e->getMessage());
        }
    }

    /**
     * Export invoices.
     */
    public function export(Request $request)
    {
        // Implementation for CSV/Excel export
        $query = Invoice::with(['student.user', 'enrollment.package']);

        // Apply same filters as index
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $invoices = $query->orderBy('created_at', 'desc')->get();

        $filename = 'invoices_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($invoices) {
            $file = fopen('php://output', 'w');

            // Header row
            fputcsv($file, [
                'Invoice #', 'Date', 'Student', 'Package', 'Type',
                'Subtotal', 'Online Fee', 'Discount', 'Total', 'Paid',
                'Balance', 'Due Date', 'Status'
            ]);

            foreach ($invoices as $invoice) {
                fputcsv($file, [
                    $invoice->invoice_number,
                    $invoice->created_at->format('Y-m-d'),
                    $invoice->student->user->name ?? 'N/A',
                    $invoice->enrollment->package->name ?? 'N/A',
                    ucfirst($invoice->type),
                    number_format($invoice->subtotal, 2),
                    number_format($invoice->online_fee, 2),
                    number_format($invoice->discount, 2),
                    number_format($invoice->total_amount, 2),
                    number_format($invoice->paid_amount, 2),
                    number_format($invoice->balance, 2),
                    $invoice->due_date->format('Y-m-d'),
                    ucfirst($invoice->status),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
