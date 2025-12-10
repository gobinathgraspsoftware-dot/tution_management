<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Installment;
use App\Models\Student;
use App\Models\Enrollment;
use App\Models\ClassModel;
use App\Models\Subject;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ArrearsService
{
    /**
     * Get comprehensive arrears report
     */
    public function getArrearsReport(array $filters = []): array
    {
        $query = Invoice::with([
            'student.user',
            'student.parent.user',
            'enrollment.package',
            'enrollment.class',
            'installments'
        ])
        ->whereIn('status', ['pending', 'partial', 'overdue'])
        ->where(DB::raw('total_amount - paid_amount'), '>', 0);

        // Apply filters
        if (!empty($filters['date_from'])) {
            $query->whereDate('due_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('due_date', '<=', $filters['date_to']);
        }

        if (!empty($filters['class_id'])) {
            $query->whereHas('enrollment', function($q) use ($filters) {
                $q->where('class_id', $filters['class_id']);
            });
        }

        if (!empty($filters['subject_id'])) {
            $query->whereHas('enrollment.package.subjects', function($q) use ($filters) {
                $q->where('subjects.id', $filters['subject_id']);
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['student_id'])) {
            $query->where('student_id', $filters['student_id']);
        }

        if (!empty($filters['days_overdue_min'])) {
            $query->where('due_date', '<=', now()->subDays($filters['days_overdue_min']));
        }

        if (!empty($filters['amount_min'])) {
            $query->where(DB::raw('total_amount - paid_amount'), '>=', $filters['amount_min']);
        }

        // Sort by due date (oldest first for arrears)
        $query->orderBy('due_date', 'asc');

        $invoices = isset($filters['paginate']) && $filters['paginate']
            ? $query->paginate($filters['per_page'] ?? 20)
            : $query->get();

        // Calculate totals
        $totalQuery = Invoice::whereIn('status', ['pending', 'partial', 'overdue']);

        $summary = [
            'total_arrears' => $totalQuery->sum(DB::raw('total_amount - paid_amount')),
            'total_invoices' => $totalQuery->count(),
            'total_students' => $totalQuery->distinct('student_id')->count('student_id'),
            'overdue_count' => Invoice::overdue()->count(),
            'by_age' => $this->getArrearsByAge(),
        ];

        return [
            'invoices' => $invoices,
            'summary' => $summary,
            'filters' => $filters,
        ];
    }

    /**
     * Get arrears breakdown by age (days overdue)
     */
    public function getArrearsByAge(): array
    {
        $today = Carbon::today();

        return [
            '0-30' => [
                'count' => Invoice::unpaid()
                    ->where('due_date', '>', $today->copy()->subDays(30))
                    ->where('due_date', '<=', $today)
                    ->count(),
                'amount' => Invoice::unpaid()
                    ->where('due_date', '>', $today->copy()->subDays(30))
                    ->where('due_date', '<=', $today)
                    ->sum(DB::raw('total_amount - paid_amount')),
            ],
            '31-60' => [
                'count' => Invoice::unpaid()
                    ->where('due_date', '>', $today->copy()->subDays(60))
                    ->where('due_date', '<=', $today->copy()->subDays(30))
                    ->count(),
                'amount' => Invoice::unpaid()
                    ->where('due_date', '>', $today->copy()->subDays(60))
                    ->where('due_date', '<=', $today->copy()->subDays(30))
                    ->sum(DB::raw('total_amount - paid_amount')),
            ],
            '61-90' => [
                'count' => Invoice::unpaid()
                    ->where('due_date', '>', $today->copy()->subDays(90))
                    ->where('due_date', '<=', $today->copy()->subDays(60))
                    ->count(),
                'amount' => Invoice::unpaid()
                    ->where('due_date', '>', $today->copy()->subDays(90))
                    ->where('due_date', '<=', $today->copy()->subDays(60))
                    ->sum(DB::raw('total_amount - paid_amount')),
            ],
            '90+' => [
                'count' => Invoice::unpaid()
                    ->where('due_date', '<=', $today->copy()->subDays(90))
                    ->count(),
                'amount' => Invoice::unpaid()
                    ->where('due_date', '<=', $today->copy()->subDays(90))
                    ->sum(DB::raw('total_amount - paid_amount')),
            ],
        ];
    }

    /**
     * Get students with arrears
     */
    public function getStudentsWithArrears(array $filters = []): Collection
    {
        $query = Student::select('students.*')
            ->with(['user', 'parent.user', 'enrollments.class', 'enrollments.package'])
            ->join('invoices', 'students.id', '=', 'invoices.student_id')
            ->whereIn('invoices.status', ['pending', 'partial', 'overdue'])
            ->groupBy('students.id')
            ->selectRaw('SUM(invoices.total_amount - invoices.paid_amount) as total_arrears')
            ->selectRaw('COUNT(invoices.id) as unpaid_invoice_count')
            ->selectRaw('MIN(invoices.due_date) as oldest_due_date')
            ->having('total_arrears', '>', 0)
            ->orderBy('total_arrears', 'desc');

        if (!empty($filters['min_arrears'])) {
            $query->having('total_arrears', '>=', $filters['min_arrears']);
        }

        if (!empty($filters['class_id'])) {
            $query->whereHas('enrollments', function($q) use ($filters) {
                $q->where('class_id', $filters['class_id']);
            });
        }

        return $query->get();
    }

    /**
     * Get student's arrears details
     */
    public function getStudentArrears(Student $student): array
    {
        $unpaidInvoices = Invoice::where('student_id', $student->id)
            ->unpaid()
            ->with(['enrollment.package', 'installments'])
            ->orderBy('due_date', 'asc')
            ->get();

        $summary = [
            'total_arrears' => $unpaidInvoices->sum('balance'),
            'invoice_count' => $unpaidInvoices->count(),
            'oldest_due' => $unpaidInvoices->first()?->due_date,
            'max_days_overdue' => $unpaidInvoices->max('days_overdue'),
            'by_status' => [
                'pending' => $unpaidInvoices->where('status', 'pending')->sum('balance'),
                'partial' => $unpaidInvoices->where('status', 'partial')->sum('balance'),
                'overdue' => $unpaidInvoices->where('status', 'overdue')->sum('balance'),
            ],
        ];

        // Get installment arrears if any
        $installmentArrears = Installment::whereIn('invoice_id', $unpaidInvoices->pluck('id'))
            ->unpaid()
            ->orderBy('due_date', 'asc')
            ->get();

        return [
            'student' => $student->load(['user', 'parent.user']),
            'unpaid_invoices' => $unpaidInvoices,
            'installment_arrears' => $installmentArrears,
            'summary' => $summary,
        ];
    }

    /**
     * Get arrears by class
     */
    public function getArrearsByClass(): Collection
    {
        return ClassModel::select('classes.*')
            ->join('enrollments', 'classes.id', '=', 'enrollments.class_id')
            ->join('invoices', 'enrollments.id', '=', 'invoices.enrollment_id')
            ->whereIn('invoices.status', ['pending', 'partial', 'overdue'])
            ->groupBy('classes.id')
            ->selectRaw('SUM(invoices.total_amount - invoices.paid_amount) as total_arrears')
            ->selectRaw('COUNT(DISTINCT invoices.student_id) as students_with_arrears')
            ->selectRaw('COUNT(invoices.id) as unpaid_invoices')
            ->having('total_arrears', '>', 0)
            ->orderBy('total_arrears', 'desc')
            ->get();
    }

    /**
     * Get arrears by subject
     */
    public function getArrearsBySubject(): Collection
    {
        return DB::table('subjects')
            ->select('subjects.*')
            ->join('package_subject', 'subjects.id', '=', 'package_subject.subject_id')
            ->join('packages', 'package_subject.package_id', '=', 'packages.id')
            ->join('enrollments', 'packages.id', '=', 'enrollments.package_id')
            ->join('invoices', 'enrollments.id', '=', 'invoices.enrollment_id')
            ->whereIn('invoices.status', ['pending', 'partial', 'overdue'])
            ->groupBy('subjects.id')
            ->selectRaw('SUM(invoices.total_amount - invoices.paid_amount) as total_arrears')
            ->selectRaw('COUNT(DISTINCT invoices.student_id) as students_with_arrears')
            ->having('total_arrears', '>', 0)
            ->orderBy('total_arrears', 'desc')
            ->get();
    }

    /**
     * Get due report (upcoming dues)
     */
    public function getDueReport(int $daysAhead = 30): array
    {
        $today = Carbon::today();
        $endDate = $today->copy()->addDays($daysAhead);

        // Upcoming invoice dues
        $upcomingInvoices = Invoice::with(['student.user', 'enrollment.package'])
            ->whereIn('status', ['pending', 'partial'])
            ->whereBetween('due_date', [$today, $endDate])
            ->orderBy('due_date', 'asc')
            ->get();

        // Upcoming installment dues
        $upcomingInstallments = Installment::with(['invoice.student.user'])
            ->whereIn('status', ['pending', 'partial'])
            ->whereBetween('due_date', [$today, $endDate])
            ->orderBy('due_date', 'asc')
            ->get();

        // Group by week
        $byWeek = [
            'this_week' => [
                'invoices' => $upcomingInvoices->filter(fn($i) => $i->due_date->isBetween($today, $today->copy()->endOfWeek())),
                'installments' => $upcomingInstallments->filter(fn($i) => $i->due_date->isBetween($today, $today->copy()->endOfWeek())),
            ],
            'next_week' => [
                'invoices' => $upcomingInvoices->filter(fn($i) => $i->due_date->isBetween($today->copy()->addWeek()->startOfWeek(), $today->copy()->addWeek()->endOfWeek())),
                'installments' => $upcomingInstallments->filter(fn($i) => $i->due_date->isBetween($today->copy()->addWeek()->startOfWeek(), $today->copy()->addWeek()->endOfWeek())),
            ],
            'later' => [
                'invoices' => $upcomingInvoices->filter(fn($i) => $i->due_date->isAfter($today->copy()->addWeeks(2))),
                'installments' => $upcomingInstallments->filter(fn($i) => $i->due_date->isAfter($today->copy()->addWeeks(2))),
            ],
        ];

        $summary = [
            'total_due' => $upcomingInvoices->sum('balance') + $upcomingInstallments->sum('balance'),
            'invoice_count' => $upcomingInvoices->count(),
            'installment_count' => $upcomingInstallments->count(),
            'student_count' => $upcomingInvoices->pluck('student_id')->unique()->count(),
        ];

        return [
            'invoices' => $upcomingInvoices,
            'installments' => $upcomingInstallments,
            'by_week' => $byWeek,
            'summary' => $summary,
        ];
    }

    /**
     * Get collection forecast
     */
    public function getCollectionForecast(int $months = 3): array
    {
        $forecast = [];
        $today = Carbon::today();

        for ($i = 0; $i < $months; $i++) {
            $monthStart = $today->copy()->addMonths($i)->startOfMonth();
            $monthEnd = $today->copy()->addMonths($i)->endOfMonth();

            $invoicesDue = Invoice::unpaid()
                ->whereBetween('due_date', [$monthStart, $monthEnd])
                ->get();

            $installmentsDue = Installment::unpaid()
                ->whereBetween('due_date', [$monthStart, $monthEnd])
                ->get();

            $forecast[] = [
                'month' => $monthStart->format('F Y'),
                'month_key' => $monthStart->format('Y-m'),
                'expected_collections' => $invoicesDue->sum('balance') + $installmentsDue->sum('balance'),
                'invoice_count' => $invoicesDue->count(),
                'installment_count' => $installmentsDue->count(),
            ];
        }

        return $forecast;
    }

    /**
     * Export arrears report
     */
    public function exportArrearsReport(array $filters = [], string $format = 'csv'): array
    {
        $report = $this->getArrearsReport($filters);
        $data = [];

        foreach ($report['invoices'] as $invoice) {
            $data[] = [
                'Invoice #' => $invoice->invoice_number,
                'Student' => $invoice->student->user->name ?? 'N/A',
                'Parent' => $invoice->student->parent?->user?->name ?? 'N/A',
                'Phone' => $invoice->student->parent?->user?->phone ?? $invoice->student->user?->phone ?? 'N/A',
                'Package' => $invoice->enrollment?->package?->name ?? 'N/A',
                'Class' => $invoice->enrollment?->class?->name ?? 'N/A',
                'Total Amount' => number_format($invoice->total_amount, 2),
                'Paid Amount' => number_format($invoice->paid_amount, 2),
                'Balance' => number_format($invoice->balance, 2),
                'Due Date' => $invoice->due_date?->format('d/m/Y') ?? 'N/A',
                'Days Overdue' => $invoice->days_overdue,
                'Status' => ucfirst($invoice->status),
                'Reminder Count' => $invoice->reminder_count,
            ];
        }

        return [
            'data' => $data,
            'summary' => $report['summary'],
            'generated_at' => now()->format('d M Y H:i:s'),
        ];
    }

    /**
     * Get dashboard statistics
     */
    public function getDashboardStats(): array
    {
        $today = Carbon::today();

        return [
            'total_arrears' => Invoice::unpaid()->sum(DB::raw('total_amount - paid_amount')),
            'overdue_invoices' => Invoice::overdue()->count(),
            'students_with_arrears' => Invoice::unpaid()->distinct('student_id')->count('student_id'),
            'due_this_week' => Invoice::unpaid()
                ->whereBetween('due_date', [$today, $today->copy()->endOfWeek()])
                ->sum(DB::raw('total_amount - paid_amount')),
            'due_next_week' => Invoice::unpaid()
                ->whereBetween('due_date', [
                    $today->copy()->addWeek()->startOfWeek(),
                    $today->copy()->addWeek()->endOfWeek()
                ])
                ->sum(DB::raw('total_amount - paid_amount')),
            'collection_rate' => $this->calculateCollectionRate(),
            'arrears_by_age' => $this->getArrearsByAge(),
        ];
    }

    /**
     * Calculate overall collection rate
     */
    protected function calculateCollectionRate(): float
    {
        $thisMonth = Carbon::now();

        $totalInvoiced = Invoice::whereMonth('created_at', $thisMonth->month)
            ->whereYear('created_at', $thisMonth->year)
            ->whereNotIn('status', ['draft', 'cancelled'])
            ->sum('total_amount');

        $totalCollected = Invoice::whereMonth('created_at', $thisMonth->month)
            ->whereYear('created_at', $thisMonth->year)
            ->whereNotIn('status', ['draft', 'cancelled'])
            ->sum('paid_amount');

        if ($totalInvoiced <= 0) {
            return 0;
        }

        return round(($totalCollected / $totalInvoiced) * 100, 1);
    }

    /**
     * Get critical arrears (high priority follow-up needed)
     */
    public function getCriticalArrears(): Collection
    {
        return Invoice::with(['student.user', 'student.parent.user'])
            ->overdue()
            ->where('due_date', '<', now()->subDays(30))
            ->where(DB::raw('total_amount - paid_amount'), '>', 500)
            ->orderBy('due_date', 'asc')
            ->limit(20)
            ->get();
    }

    /**
     * Mark student for follow-up action
     */
    public function flagStudentForFollowUp(Student $student, string $reason): void
    {
        // Log the follow-up flag
        Log::info('Student flagged for arrears follow-up', [
            'student_id' => $student->id,
            'reason' => $reason,
            'total_arrears' => Invoice::where('student_id', $student->id)
                ->unpaid()
                ->sum(DB::raw('total_amount - paid_amount'))
        ]);

        // Could trigger additional actions like notifications to management
    }
}
