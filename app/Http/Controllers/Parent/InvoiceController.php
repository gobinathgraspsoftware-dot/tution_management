<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Student;
use Illuminate\Http\Request;
use Carbon\Carbon;

class InvoiceController extends Controller
{
    /**
     * Display listing of invoices for parent's children.
     */
    public function index(Request $request)
    {
        $parent = auth()->user()->parent;

        if (!$parent) {
            return redirect()->route('parent.dashboard')
                ->with('error', 'Parent profile not found.');
        }

        $childrenIds = $parent->students->pluck('id');

        $query = Invoice::whereIn('student_id', $childrenIds)
            ->with(['student.user', 'enrollment.package']);

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

        // Filter by child
        if ($request->filled('child_id')) {
            $query->where('student_id', $request->child_id);
        }

        // Filter by date
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $invoices = $query->orderBy('created_at', 'desc')->paginate(10);

        // Get summary
        $summary = [
            'total_pending' => Invoice::whereIn('student_id', $childrenIds)
                ->whereIn('status', ['pending', 'overdue'])
                ->sum('total_amount'),
            'total_paid_this_month' => Invoice::whereIn('student_id', $childrenIds)
                ->where('status', 'paid')
                ->whereMonth('updated_at', Carbon::now()->month)
                ->sum('paid_amount'),
            'overdue_count' => Invoice::whereIn('student_id', $childrenIds)
                ->overdue()
                ->count(),
        ];

        $children = $parent->students()->with('user')->get();

        return view('parent.invoices.index', compact('invoices', 'summary', 'children'));
    }

    /**
     * Display the specified invoice.
     */
    public function show(Invoice $invoice)
    {
        $parent = auth()->user()->parent;

        // Verify parent owns this invoice's student
        if (!$parent || !$parent->students->contains('id', $invoice->student_id)) {
            abort(403, 'Unauthorized access to this invoice.');
        }

        $invoice->load([
            'student.user',
            'enrollment.package',
            'enrollment.class',
            'payments',
            'installments',
        ]);

        return view('parent.invoices.show', compact('invoice'));
    }

    /**
     * Get pending invoices summary for dashboard.
     */
    public function getPendingInvoices()
    {
        $parent = auth()->user()->parent;

        if (!$parent) {
            return collect();
        }

        $childrenIds = $parent->students->pluck('id');

        return Invoice::whereIn('student_id', $childrenIds)
            ->whereIn('status', ['pending', 'partial', 'overdue'])
            ->with(['student.user', 'enrollment.package'])
            ->orderBy('due_date')
            ->get();
    }

    /**
     * Display payment history.
     */
    public function paymentHistory(Request $request)
    {
        $parent = auth()->user()->parent;

        if (!$parent) {
            return redirect()->route('parent.dashboard')
                ->with('error', 'Parent profile not found.');
        }

        $childrenIds = $parent->students->pluck('id');

        $invoices = Invoice::whereIn('student_id', $childrenIds)
            ->where('status', 'paid')
            ->with(['student.user', 'enrollment.package', 'payments'])
            ->orderBy('updated_at', 'desc')
            ->paginate(15);

        // Calculate totals by month
        $monthlyTotals = Invoice::whereIn('student_id', $childrenIds)
            ->where('status', 'paid')
            ->selectRaw('YEAR(updated_at) as year, MONTH(updated_at) as month, SUM(paid_amount) as total')
            ->groupBy('year', 'month')
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->limit(6)
            ->get();

        return view('parent.invoices.history', compact('invoices', 'monthlyTotals'));
    }
}
