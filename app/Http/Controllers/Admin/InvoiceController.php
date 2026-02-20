<?php
/**
 * FILE: app/Http/Controllers/Admin/InvoiceController.php
 *
 * FIX APPLIED: Restored original index() method that uses
 *   $this->invoiceService->getInvoiceStatistics() → $statistics
 * instead of Invoice::getSummary() → $summary.
 *
 * NEW METHODS ADDED AT BOTTOM:
 *   - studentDashboard()
 *   - exportStudentBillingCsv()
 *
 * NEW IMPORT ADDED:
 *   - use App\Models\Payment;
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\InvoiceRequest;
use App\Models\Invoice;
use App\Models\Student;
use App\Models\Enrollment;
use App\Models\Package;
use App\Models\Payment;
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
     *
     * IMPORTANT: The blade view (admin.invoices.index) expects $statistics
     * from $this->invoiceService->getInvoiceStatistics().
     * Keys used: total_invoices, pending_invoices, overdue_invoices,
     *            paid_invoices, total_invoiced, total_collected, total_outstanding
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

        // Search by invoice number or student name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('student.user', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Get statistics — blade expects $statistics with specific keys
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
        $students = Student::approved()->with(['user', 'enrollments' => function ($q) {
            $q->active()->with('package');
        }])->get();

        $selectedStudent = null;
        if ($request->filled('student_id')) {
            $selectedStudent = Student::with(['user', 'enrollments' => function ($q) {
                $q->active()->with('package');
            }])->find($request->student_id);
        }

        return view('admin.invoices.create', compact('students', 'selectedStudent'));
    }

    /**
     * Store a newly created invoice.
     */
    public function store(InvoiceRequest $request)
    {
        try {
            $invoice = $this->invoiceService->createInvoice($request->validated());

            return redirect()->route('admin.invoices.show', $invoice->id)
                ->with('success', 'Invoice created successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Failed to create invoice. ' . $e->getMessage());
        }
    }

    /**
     * Display the specified invoice.
     */
    public function show(Invoice $invoice)
    {
        $invoice->load([
            'student.user',
            'enrollment.package',
            'payments' => function ($q) {
                $q->orderBy('payment_date', 'desc');
            },
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
        if (!$invoice->isEditable()) {
            return back()->with('error', 'This invoice cannot be edited.');
        }

        $invoice->load(['student.user', 'enrollment.package']);

        return view('admin.invoices.edit', compact('invoice'));
    }

    /**
     * Update the specified invoice.
     */
    public function update(InvoiceRequest $request, Invoice $invoice)
    {
        if (!$invoice->isEditable()) {
            return back()->with('error', 'This invoice cannot be edited.');
        }

        try {
            $invoice->update($request->validated());
            $invoice->recalculateTotal();

            return redirect()->route('admin.invoices.show', $invoice->id)
                ->with('success', 'Invoice updated successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Failed to update invoice. ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified invoice.
     */
    public function destroy(Invoice $invoice)
    {
        if ($invoice->isPaid()) {
            return back()->with('error', 'Cannot delete a paid invoice.');
        }

        if ($invoice->payments()->exists()) {
            return back()->with('error', 'Cannot delete invoice with associated payments.');
        }

        $invoice->cancel();

        return redirect()->route('admin.invoices.index')
            ->with('success', 'Invoice cancelled successfully.');
    }

    /**
     * Generate monthly invoices in bulk.
     */
    public function generateMonthly(Request $request)
    {
        $request->validate([
            'month' => 'required|date_format:Y-m',
        ]);

        try {
            $month = Carbon::parse($request->month . '-01');
            $results = $this->invoiceService->generateMonthlyInvoices($month);

            $message = "Monthly invoices generated: {$results['created']} created.";
            if ($results['skipped'] > 0) {
                $message .= " Skipped: {$results['skipped']}.";
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
        $query = Invoice::with(['student.user', 'enrollment.package']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $invoices = $query->latest()->get();

        $filename = 'invoices_' . now()->format('Ymd_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($invoices) {
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

    // =========================================================================
    // STUDENT BILLING DASHBOARD (Online / Offline)
    // Added: Student billing dashboard with separated Online/Offline views
    // =========================================================================

    /**
     * Display student billing dashboard with Online/Offline separation.
     * Route: admin.billing.student-dashboard
     */
    public function studentDashboard(Request $request)
    {
        $month = $request->filled('month')
            ? Carbon::parse($request->month . '-01')
            : Carbon::now();

        $monthStart = $month->copy()->startOfMonth();
        $monthEnd   = $month->copy()->endOfMonth();

        // ── Base Query Builder (shared filters) ────────────────────
        $baseQuery = function ($regType) use ($request, $monthStart, $monthEnd) {
            $query = Student::approved()
                ->where('registration_type', $regType)
                ->with([
                    'user',
                    'enrollments' => function ($q) {
                        $q->active()->with('package');
                    },
                ]);

            // Search filter
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('student_id', 'like', "%{$search}%")
                      ->orWhereHas('user', function ($q2) use ($search) {
                          $q2->where('name', 'like', "%{$search}%")
                              ->orWhere('email', 'like', "%{$search}%");
                      });
                });
            }

            // Package filter
            if ($request->filled('package_id')) {
                $query->whereHas('enrollments', function ($q) use ($request) {
                    $q->active()->where('package_id', $request->package_id);
                });
            }

            // Payment status filter
            if ($request->filled('payment_status')) {
                $status = $request->payment_status;
                $query->where(function ($q) use ($status, $monthStart, $monthEnd) {
                    if ($status === 'overdue') {
                        $q->whereHas('invoices', function ($iq) {
                            $iq->whereIn('status', ['pending', 'partial', 'overdue'])
                               ->where('due_date', '<', now());
                        });
                    } elseif ($status === 'paid') {
                        $q->whereDoesntHave('invoices', function ($iq) use ($monthStart, $monthEnd) {
                            $iq->whereBetween('billing_period_start', [$monthStart, $monthEnd])
                               ->whereIn('status', ['pending', 'partial', 'overdue']);
                        });
                    } elseif ($status === 'partial') {
                        $q->whereHas('invoices', function ($iq) {
                            $iq->where('status', 'partial');
                        });
                    } elseif ($status === 'pending') {
                        $q->whereHas('invoices', function ($iq) {
                            $iq->where('status', 'pending');
                        });
                    }
                });
            }

            return $query;
        };

        // ── Fetch Online & Offline Students (Paginated) ──────────
        $onlineStudents = $baseQuery('online')
            ->withSum(['invoices as billing_total_invoiced' => function ($q) {
                $q->whereNotIn('status', ['cancelled', 'draft']);
            }], 'total_amount')
            ->withSum(['invoices as billing_total_paid' => function ($q) {
                $q->whereNotIn('status', ['cancelled', 'draft']);
            }], 'paid_amount')
            ->withMax('payments as last_payment_date', 'payment_date')
            ->withExists(['invoices as has_overdue' => function ($q) {
                $q->whereIn('status', ['pending', 'partial', 'overdue'])
                  ->where('due_date', '<', now());
            }])
            ->orderBy('created_at', 'desc')
            ->paginate(15, ['*'], 'online_page');

        $offlineStudents = $baseQuery('offline')
            ->withSum(['invoices as billing_total_invoiced' => function ($q) {
                $q->whereNotIn('status', ['cancelled', 'draft']);
            }], 'total_amount')
            ->withSum(['invoices as billing_total_paid' => function ($q) {
                $q->whereNotIn('status', ['cancelled', 'draft']);
            }], 'paid_amount')
            ->withMax('payments as last_payment_date', 'payment_date')
            ->withExists(['invoices as has_overdue' => function ($q) {
                $q->whereIn('status', ['pending', 'partial', 'overdue'])
                  ->where('due_date', '<', now());
            }])
            ->orderBy('created_at', 'desc')
            ->paginate(15, ['*'], 'offline_page');

        // ── Aggregate Statistics ──────────────────────────────────
        $onlineCount  = Student::approved()->onlineRegistration()->count();
        $offlineCount = Student::approved()->offlineRegistration()->count();

        // Revenue this month by revenue_source
        $onlineRevenueMonth = Payment::completed()
            ->studentFeesOnline()
            ->whereBetween('payment_date', [$monthStart, $monthEnd])
            ->sum('amount');

        $offlineRevenueMonth = Payment::completed()
            ->studentFeesPhysical()
            ->whereBetween('payment_date', [$monthStart, $monthEnd])
            ->sum('amount');

        // Outstanding by registration type
        $onlineOutstanding = Invoice::whereHas('student', function ($q) {
                $q->where('registration_type', 'online');
            })
            ->unpaid()
            ->sum(DB::raw('total_amount - paid_amount'));

        $offlineOutstanding = Invoice::whereHas('student', function ($q) {
                $q->where('registration_type', 'offline');
            })
            ->unpaid()
            ->sum(DB::raw('total_amount - paid_amount'));

        // Collection rate
        $totalInvoiced = Invoice::whereNotIn('status', ['cancelled', 'draft'])->sum('total_amount');
        $totalPaid     = Invoice::whereNotIn('status', ['cancelled', 'draft'])->sum('paid_amount');
        $collectionRate = $totalInvoiced > 0 ? round(($totalPaid / $totalInvoiced) * 100, 1) : 0;

        // Average monthly fees
        $onlineAvgFee = Enrollment::active()
            ->whereHas('student', function ($q) { $q->where('registration_type', 'online'); })
            ->avg('monthly_fee') ?? 0;

        $offlineAvgFee = Enrollment::active()
            ->whereHas('student', function ($q) { $q->where('registration_type', 'offline'); })
            ->avg('monthly_fee') ?? 0;

        // Overdue counts per type
        $onlineOverdueCount = Invoice::whereHas('student', function ($q) {
                $q->where('registration_type', 'online');
            })
            ->where(function ($q) {
                $q->where('status', 'overdue')
                  ->orWhere(function ($q2) {
                      $q2->whereIn('status', ['pending', 'partial'])
                         ->where('due_date', '<', now());
                  });
            })
            ->distinct('student_id')
            ->count('student_id');

        $offlineOverdueCount = Invoice::whereHas('student', function ($q) {
                $q->where('registration_type', 'offline');
            })
            ->where(function ($q) {
                $q->where('status', 'overdue')
                  ->orWhere(function ($q2) {
                      $q2->whereIn('status', ['pending', 'partial'])
                         ->where('due_date', '<', now());
                  });
            })
            ->distinct('student_id')
            ->count('student_id');

        $stats = [
            'total_students'       => $onlineCount + $offlineCount,
            'online_count'         => $onlineCount,
            'offline_count'        => $offlineCount,
            'total_revenue_month'  => $onlineRevenueMonth + $offlineRevenueMonth,
            'online_revenue_month' => $onlineRevenueMonth,
            'offline_revenue_month'=> $offlineRevenueMonth,
            'total_outstanding'    => $onlineOutstanding + $offlineOutstanding,
            'online_outstanding'   => $onlineOutstanding,
            'offline_outstanding'  => $offlineOutstanding,
            'collection_rate'      => $collectionRate,
            'online_avg_fee'       => $onlineAvgFee,
            'offline_avg_fee'      => $offlineAvgFee,
            'online_overdue_count' => $onlineOverdueCount,
            'offline_overdue_count'=> $offlineOverdueCount,
        ];

        // ── Chart Data (Last 6 Months) ───────────────────────────
        $chartData = ['months' => [], 'online_revenue' => [], 'offline_revenue' => []];
        for ($i = 5; $i >= 0; $i--) {
            $m = Carbon::now()->subMonths($i);
            $chartData['months'][] = $m->format('M Y');

            $chartData['online_revenue'][] = (float) Payment::completed()
                ->studentFeesOnline()
                ->whereMonth('payment_date', $m->month)
                ->whereYear('payment_date', $m->year)
                ->sum('amount');

            $chartData['offline_revenue'][] = (float) Payment::completed()
                ->studentFeesPhysical()
                ->whereMonth('payment_date', $m->month)
                ->whereYear('payment_date', $m->year)
                ->sum('amount');
        }

        // ── Packages for Filter Dropdown ─────────────────────────
        $packages = Package::active()->orderBy('name')->get(['id', 'name', 'type']);

        // ── CSV Export ───────────────────────────────────────────
        if ($request->get('export') === 'csv') {
            return $this->exportStudentBillingCsv($onlineStudents, $offlineStudents);
        }

        return view('admin.billing.student-dashboard', compact(
            'onlineStudents',
            'offlineStudents',
            'stats',
            'chartData',
            'packages'
        ));
    }

    /**
     * Export student billing data as CSV.
     */
    private function exportStudentBillingCsv($onlineStudents, $offlineStudents)
    {
        $filename = 'student_billing_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($onlineStudents, $offlineStudents) {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'Type', 'Student ID', 'Name', 'Email', 'Package',
                'Monthly Fee', 'Total Invoiced', 'Total Paid', 'Outstanding',
                'Last Payment', 'Status'
            ]);

            foreach (['Online' => $onlineStudents, 'Offline' => $offlineStudents] as $type => $students) {
                foreach ($students as $student) {
                    $enrollment  = $student->enrollments->first();
                    $outstanding = max(0, ($student->billing_total_invoiced ?? 0) - ($student->billing_total_paid ?? 0));

                    if ($outstanding <= 0 && ($student->billing_total_invoiced ?? 0) > 0) {
                        $status = 'Paid';
                    } elseif ($student->has_overdue) {
                        $status = 'Overdue';
                    } elseif (($student->billing_total_paid ?? 0) > 0) {
                        $status = 'Partial';
                    } else {
                        $status = 'Pending';
                    }

                    fputcsv($file, [
                        $type,
                        $student->student_id,
                        $student->user->name ?? 'N/A',
                        $student->user->email ?? 'N/A',
                        $enrollment->package->name ?? 'N/A',
                        number_format($enrollment->monthly_fee ?? 0, 2),
                        number_format($student->billing_total_invoiced ?? 0, 2),
                        number_format($student->billing_total_paid ?? 0, 2),
                        number_format($outstanding, 2),
                        $student->last_payment_date
                            ? Carbon::parse($student->last_payment_date)->format('d M Y')
                            : 'N/A',
                        $status,
                    ]);
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
