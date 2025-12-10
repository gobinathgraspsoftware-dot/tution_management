<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\InstallmentPlanRequest;
use App\Models\Invoice;
use App\Models\Installment;
use App\Models\Payment;
use App\Models\Student;
use App\Services\InstallmentService;
use App\Services\PaymentReminderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InstallmentController extends Controller
{
    protected $installmentService;
    protected $reminderService;

    public function __construct(
        InstallmentService $installmentService,
        PaymentReminderService $reminderService
    ) {
        $this->installmentService = $installmentService;
        $this->reminderService = $reminderService;
    }

    /**
     * Display installment plans listing
     * Route: GET /admin/installments
     * Name: admin.installments.index
     */
    public function index(Request $request)
    {
        $query = Invoice::with(['student.user', 'enrollment.package', 'installments'])
            ->where('is_installment', true);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by student
        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        // Search by invoice number or student name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('student.user', function($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $invoicesWithInstallments = $query->orderBy('created_at', 'desc')->paginate(15);

        // Get statistics
        $statistics = $this->installmentService->getStatistics();

        // Get students for filter
        $students = Student::approved()->with('user')->get();

        return view('admin.installments.index', compact(
            'invoicesWithInstallments',
            'statistics',
            'students'
        ));
    }

    /**
     * Show form to create installment plan
     * Route: GET /admin/installments/create?invoice_id=X
     * Name: admin.installments.create
     */
    public function create(Request $request)
    {
        $invoice = null;

        if ($request->filled('invoice_id')) {
            $invoice = Invoice::with(['student.user', 'enrollment.package', 'installments'])
                ->findOrFail($request->invoice_id);

            // Validate invoice is eligible
            if ($invoice->isPaid()) {
                return back()->with('error', 'Cannot create installment plan for a paid invoice.');
            }

            if ($invoice->is_installment && $invoice->installments()->count() > 0) {
                return back()->with('error', 'Invoice already has an installment plan.');
            }
        }

        // Get unpaid invoices for selection
        $eligibleInvoices = Invoice::with(['student.user', 'enrollment.package'])
            ->where('is_installment', false)
            ->whereIn('status', ['pending', 'partial', 'overdue'])
            ->where(DB::raw('total_amount - paid_amount'), '>', 0)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.installments.create', compact('invoice', 'eligibleInvoices'));
    }

    /**
     * Store installment plan
     * Route: POST /admin/installments
     * Name: admin.installments.store
     */
    public function store(InstallmentPlanRequest $request)
    {
        $validated = $request->validated();

        $invoice = Invoice::findOrFail($validated['invoice_id']);

        try {
            $result = $this->installmentService->createInstallmentPlan($invoice, [
                'number_of_installments' => $validated['number_of_installments'],
                'start_date' => $validated['start_date'],
                'interval_days' => $validated['interval_days'] ?? 30,
                'custom_amounts' => $validated['custom_amounts'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            return redirect()
                ->route('admin.installments.show', $invoice)
                ->with('success', 'Installment plan created successfully with ' . $validated['number_of_installments'] . ' installments.');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to create installment plan: ' . $e->getMessage());
        }
    }

    /**
     * Display installment plan details
     * Route: GET /admin/installments/{invoice}
     * Name: admin.installments.show
     */
    public function show(Invoice $invoice)
    {
        if (!$invoice->is_installment) {
            return redirect()->route('admin.invoices.show', $invoice)
                ->with('info', 'This invoice does not have an installment plan.');
        }

        $invoice->load([
            'student.user',
            'student.parent.user',
            'enrollment.package',
            'installments' => fn($q) => $q->orderBy('installment_number'),
            'payments',
            'reminders' => fn($q) => $q->orderBy('scheduled_date', 'desc'),
        ]);

        $summary = $this->installmentService->getInstallmentSummary($invoice);

        return view('admin.installments.show', compact('invoice', 'summary'));
    }

    /**
     * Update individual installment
     * Route: PATCH /admin/installments/installment/{installment}
     * Name: admin.installments.update-installment
     */
    public function updateInstallment(Request $request, Installment $installment)
    {
        $request->validate([
            'amount' => 'sometimes|numeric|min:0',
            'due_date' => 'sometimes|date',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($installment->isPaid()) {
            return back()->with('error', 'Cannot modify a paid installment.');
        }

        try {
            $this->installmentService->modifyInstallment($installment, $request->only([
                'amount', 'due_date', 'notes'
            ]));

            return back()->with('success', 'Installment updated successfully.');

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Cancel installment plan
     * Route: DELETE /admin/installments/{invoice}/cancel
     * Name: admin.installments.cancel
     */
    public function cancel(Invoice $invoice)
    {
        try {
            $this->installmentService->cancelInstallmentPlan($invoice);

            return redirect()->route('admin.invoices.show', $invoice)
                ->with('success', 'Installment plan cancelled successfully.');

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Record payment for specific installment
     * Route: POST /admin/installments/installment/{installment}/payment
     * Name: admin.installments.record-payment
     */
    public function recordPayment(Request $request, Installment $installment)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $installment->balance,
            'payment_method' => 'required|in:cash,qr,online,bank_transfer',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            // Create payment record
            $payment = Payment::create([
                'invoice_id' => $installment->invoice_id,
                'student_id' => $installment->invoice->student_id,
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'reference_number' => $request->reference_number,
                'status' => 'completed',
                'payment_date' => now(),
                'processed_by' => auth()->id(),
                'notes' => $request->notes,
            ]);

            // Record payment on installment
            $this->installmentService->payInstallment($installment, $request->amount, $payment->id);

            DB::commit();

            return back()->with('success', 'Payment of RM' . number_format($request->amount, 2) . ' recorded successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Send reminder for specific installment
     * Route: POST /admin/installments/installment/{installment}/reminder
     * Name: admin.installments.send-reminder
     */
    public function sendReminder(Installment $installment)
    {
        try {
            $reminder = $this->reminderService->createReminder(
                $installment->invoice,
                'installment',
                Carbon::now()
            );

            $reminder->update(['installment_id' => $installment->id]);
            $this->reminderService->sendReminder($reminder);

            $installment->sendReminder();

            return back()->with('success', 'Reminder sent successfully.');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to send reminder: ' . $e->getMessage());
        }
    }

    /**
     * Get student's installment history
     * Route: GET /admin/installments/student/{student}/history
     * Name: admin.installments.student-history
     */
    public function studentHistory(Student $student)
    {
        $history = $this->installmentService->getStudentInstallmentHistory($student);
        $student->load(['user', 'parent.user']);

        return view('admin.installments.student-history', compact('student', 'history'));
    }

    /**
     * Overdue installments list
     * Route: GET /admin/installments/overdue
     * Name: admin.installments.overdue
     */
    public function overdue()
    {
        $overdueInstallments = Installment::with(['invoice.student.user', 'invoice.enrollment.package'])
            ->overdue()
            ->orderBy('due_date', 'asc')
            ->paginate(20);

        $totalOverdue = Installment::overdue()->sum(DB::raw('amount - paid_amount'));

        return view('admin.installments.overdue', compact('overdueInstallments', 'totalOverdue'));
    }

    /**
     * Bulk send reminders for overdue installments
     * Route: POST /admin/installments/bulk-reminder
     * Name: admin.installments.bulk-reminder
     */
    public function bulkReminder(Request $request)
    {
        $request->validate([
            'installment_ids' => 'required|array',
            'installment_ids.*' => 'exists:installments,id',
        ]);

        $sent = 0;
        $failed = 0;

        foreach ($request->installment_ids as $id) {
            $installment = Installment::find($id);
            if (!$installment || $installment->isPaid()) continue;

            try {
                $reminder = $this->reminderService->createReminder(
                    $installment->invoice,
                    'installment',
                    Carbon::now()
                );
                $reminder->update(['installment_id' => $installment->id]);
                $this->reminderService->sendReminder($reminder);
                $installment->sendReminder();
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
     * Update overdue status (can be called via scheduler)
     * Route: POST /admin/installments/update-overdue-status
     * Name: admin.installments.update-overdue-status
     */
    public function updateOverdueStatus()
    {
        $count = $this->installmentService->updateOverdueInstallments();

        return response()->json([
            'success' => true,
            'message' => "Updated {$count} installments to overdue status.",
        ]);
    }

    /**
     * Export installments
     * Route: GET /admin/installments/export
     * Name: admin.installments.export
     */
    public function export(Request $request)
    {
        $query = Installment::with(['invoice.student.user', 'invoice.enrollment.package']);

        if ($request->filled('status')) {
            if ($request->status === 'overdue') {
                $query->overdue();
            } else {
                $query->where('status', $request->status);
            }
        }

        $installments = $query->orderBy('due_date', 'asc')->get();

        $filename = 'installments_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($installments) {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'Invoice #', 'Student', 'Package', 'Installment #',
                'Amount', 'Paid', 'Balance', 'Due Date', 'Days Overdue', 'Status'
            ]);

            foreach ($installments as $inst) {
                fputcsv($file, [
                    $inst->invoice->invoice_number ?? 'N/A',
                    $inst->invoice->student->user->name ?? 'N/A',
                    $inst->invoice->enrollment->package->name ?? 'N/A',
                    $inst->installment_number,
                    number_format($inst->amount, 2),
                    number_format($inst->paid_amount, 2),
                    number_format($inst->balance, 2),
                    $inst->due_date->format('Y-m-d'),
                    $inst->days_overdue,
                    ucfirst($inst->status),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
