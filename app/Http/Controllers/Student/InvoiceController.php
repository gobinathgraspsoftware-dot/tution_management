<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Carbon\Carbon;

class InvoiceController extends Controller
{
    /**
     * Display listing of student's invoices.
     */
    public function index(Request $request)
    {
        $student = auth()->user()->student;

        if (!$student) {
            return redirect()->route('student.dashboard')
                ->with('error', 'Student profile not found.');
        }

        $query = Invoice::where('student_id', $student->id)
            ->with(['enrollment.package']);

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'overdue') {
                $query->overdue();
            } elseif ($request->status === 'unpaid') {
                $query->whereIn('status', ['pending', 'partial', 'overdue']);
            } else {
                $query->where('status', $request->status);
            }
        }

        $invoices = $query->orderBy('created_at', 'desc')->paginate(10);

        // Get summary
        $summary = [
            'total_outstanding' => Invoice::where('student_id', $student->id)
                ->whereIn('status', ['pending', 'partial', 'overdue'])
                ->selectRaw('SUM(total_amount - paid_amount) as balance')
                ->value('balance') ?? 0,
            'total_paid' => Invoice::where('student_id', $student->id)
                ->sum('paid_amount'),
            'pending_count' => Invoice::where('student_id', $student->id)
                ->whereIn('status', ['pending', 'partial'])
                ->count(),
            'overdue_count' => Invoice::where('student_id', $student->id)
                ->overdue()
                ->count(),
        ];

        return view('student.invoices.index', compact('invoices', 'summary'));
    }

    /**
     * Display the specified invoice.
     */
    public function show(Invoice $invoice)
    {
        $student = auth()->user()->student;

        // Verify student owns this invoice
        if (!$student || $invoice->student_id !== $student->id) {
            abort(403, 'Unauthorized access to this invoice.');
        }

        $invoice->load([
            'enrollment.package',
            'enrollment.class',
            'payments',
            'installments',
        ]);

        return view('student.invoices.show', compact('invoice'));
    }

    /**
     * Get current outstanding balance for dashboard.
     */
    public function getOutstandingBalance()
    {
        $student = auth()->user()->student;

        if (!$student) {
            return [
                'balance' => 0,
                'invoices' => collect(),
            ];
        }

        $pendingInvoices = Invoice::where('student_id', $student->id)
            ->whereIn('status', ['pending', 'partial', 'overdue'])
            ->with('enrollment.package')
            ->orderBy('due_date')
            ->get();

        return [
            'balance' => $pendingInvoices->sum(fn($inv) => $inv->total_amount - $inv->paid_amount),
            'invoices' => $pendingInvoices,
        ];
    }

    /**
     * Display payment history.
     */
    public function paymentHistory(Request $request)
    {
        $student = auth()->user()->student;

        if (!$student) {
            return redirect()->route('student.dashboard')
                ->with('error', 'Student profile not found.');
        }

        $invoices = Invoice::where('student_id', $student->id)
            ->where('status', 'paid')
            ->with(['enrollment.package', 'payments'])
            ->orderBy('updated_at', 'desc')
            ->paginate(15);

        return view('student.invoices.history', compact('invoices'));
    }
}
