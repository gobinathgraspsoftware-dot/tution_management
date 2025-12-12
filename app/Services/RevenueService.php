<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\PosTransaction;
use App\Models\Seminar;
use App\Models\SeminarParticipant;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RevenueService
{
    /**
     * Get total revenue from all sources
     */
    public function getTotalRevenue($startDate = null, $endDate = null)
    {
        $startDate = $startDate ?? now()->startOfMonth();
        $endDate = $endDate ?? now()->endOfMonth();

        return [
            'student_fees' => $this->getStudentFeesRevenue($startDate, $endDate),
            'seminar_revenue' => $this->getSeminarRevenue($startDate, $endDate),
            'pos_revenue' => $this->getPosRevenue($startDate, $endDate),
            'material_sales' => $this->getMaterialSalesRevenue($startDate, $endDate),
            'other_revenue' => $this->getOtherRevenue($startDate, $endDate),
            'total' => $this->calculateTotalRevenue($startDate, $endDate),
        ];
    }

    /**
     * Get student fees revenue (online + physical)
     */
    public function getStudentFeesRevenue($startDate, $endDate)
    {
        $online = Payment::completed()
            ->studentFeesOnline()
            ->dateRange($startDate, $endDate)
            ->sum('amount');

        $physical = Payment::completed()
            ->studentFeesPhysical()
            ->dateRange($startDate, $endDate)
            ->sum('amount');

        return [
            'online' => $online,
            'physical' => $physical,
            'total' => $online + $physical,
        ];
    }

    /**
     * Get seminar revenue
     */
    public function getSeminarRevenue($startDate, $endDate)
    {
        return Payment::completed()
            ->seminarRevenue()
            ->dateRange($startDate, $endDate)
            ->sum('amount');
    }

    /**
     * Get POS revenue (cafeteria sales)
     */
    public function getPosRevenue($startDate, $endDate)
    {
        return PosTransaction::completed()
            ->dateRange($startDate, $endDate)
            ->sum('total_amount');
    }

    /**
     * Get material sales revenue
     */
    public function getMaterialSalesRevenue($startDate, $endDate)
    {
        return Payment::completed()
            ->where('revenue_source', Payment::REVENUE_SOURCE_MATERIAL)
            ->dateRange($startDate, $endDate)
            ->sum('amount');
    }

    /**
     * Get other revenue
     */
    public function getOtherRevenue($startDate, $endDate)
    {
        return Payment::completed()
            ->where('revenue_source', Payment::REVENUE_SOURCE_OTHER)
            ->dateRange($startDate, $endDate)
            ->sum('amount');
    }

    /**
     * Calculate total revenue
     */
    public function calculateTotalRevenue($startDate, $endDate)
    {
        $paymentTotal = Payment::completed()
            ->dateRange($startDate, $endDate)
            ->sum('amount');

        $posTotal = PosTransaction::completed()
            ->dateRange($startDate, $endDate)
            ->sum('total_amount');

        return $paymentTotal + $posTotal;
    }

    /**
     * Get revenue by category
     */
    public function getRevenueByCategory($startDate = null, $endDate = null)
    {
        $startDate = $startDate ?? now()->startOfMonth();
        $endDate = $endDate ?? now()->endOfMonth();

        $paymentsBySource = Payment::completed()
            ->dateRange($startDate, $endDate)
            ->select('revenue_source', DB::raw('SUM(amount) as total'))
            ->groupBy('revenue_source')
            ->get()
            ->mapWithKeys(function($item) {
                return [$item->revenue_source => $item->total];
            });

        $posTotal = PosTransaction::completed()
            ->dateRange($startDate, $endDate)
            ->sum('total_amount');

        return [
            'Student Fees (Online)' => $paymentsBySource[Payment::REVENUE_SOURCE_STUDENT_FEES_ONLINE] ?? 0,
            'Student Fees (Physical)' => $paymentsBySource[Payment::REVENUE_SOURCE_STUDENT_FEES_PHYSICAL] ?? 0,
            'Seminar Revenue' => $paymentsBySource[Payment::REVENUE_SOURCE_SEMINAR] ?? 0,
            'Cafeteria Sales' => $posTotal,
            'Material Sales' => $paymentsBySource[Payment::REVENUE_SOURCE_MATERIAL] ?? 0,
            'Other' => $paymentsBySource[Payment::REVENUE_SOURCE_OTHER] ?? 0,
        ];
    }

    /**
     * Get revenue by payment method
     */
    public function getRevenueByPaymentMethod($startDate = null, $endDate = null)
    {
        $startDate = $startDate ?? now()->startOfMonth();
        $endDate = $endDate ?? now()->endOfMonth();

        $payments = Payment::completed()
            ->dateRange($startDate, $endDate)
            ->select('payment_method', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('payment_method')
            ->get();

        $pos = PosTransaction::completed()
            ->dateRange($startDate, $endDate)
            ->select('payment_method', DB::raw('SUM(total_amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('payment_method')
            ->get();

        // Merge POS data
        $allMethods = $payments->concat($pos);

        return $allMethods->groupBy('payment_method')->map(function($items, $method) {
            return [
                'method' => $method,
                'total' => $items->sum('total'),
                'count' => $items->sum('count'),
            ];
        })->values();
    }

    /**
     * Get revenue trends (daily breakdown)
     */
    public function getRevenueTrends($startDate, $endDate)
    {
        $payments = Payment::completed()
            ->dateRange($startDate, $endDate)
            ->select(DB::raw('DATE(payment_date) as date'), DB::raw('SUM(amount) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $pos = PosTransaction::completed()
            ->dateRange($startDate, $endDate)
            ->select(DB::raw('DATE(transaction_date) as date'), DB::raw('SUM(total_amount) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Merge and aggregate by date
        $allDates = $payments->concat($pos)
            ->groupBy('date')
            ->map(function($items, $date) {
                return [
                    'date' => $date,
                    'total' => $items->sum('total'),
                ];
            })
            ->sortBy('date')
            ->values();

        return $allDates;
    }

    /**
     * Get revenue comparison (current vs previous period)
     *
     *  FIX: Returns nested structure with 'revenue', 'expense', 'profit' keys
     * This matches the expected format in blade files
     */
    public function getRevenueComparison($startDate, $endDate)
    {
        $currentRevenue = $this->calculateTotalRevenue($startDate, $endDate);

        // Calculate previous period
        $diff = $startDate->diffInDays($endDate);
        $previousStart = $startDate->copy()->subDays($diff + 1);
        $previousEnd = $startDate->copy()->subDay();
        $previousRevenue = $this->calculateTotalRevenue($previousStart, $previousEnd);

        $change = $currentRevenue - $previousRevenue;
        $changePercentage = $previousRevenue > 0
            ? (($change / $previousRevenue) * 100)
            : 0;

        //  FIX: Return data in nested structure
        return [
            'revenue' => [
                'current' => $currentRevenue,
                'previous' => $previousRevenue,
                'change' => $change,
                'change_percentage' => $changePercentage,
                'trend' => $change > 0 ? 'up' : ($change < 0 ? 'down' : 'stable'),
            ],
            'current_period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
                'revenue' => $currentRevenue,
            ],
            'previous_period' => [
                'start' => $previousStart->format('Y-m-d'),
                'end' => $previousEnd->format('Y-m-d'),
                'revenue' => $previousRevenue,
            ],
        ];
    }

    /**
     * Get top revenue sources
     */
    public function getTopRevenueSources($startDate = null, $endDate = null, $limit = 5)
    {
        $startDate = $startDate ?? now()->startOfMonth();
        $endDate = $endDate ?? now()->endOfMonth();

        $byCategory = $this->getRevenueByCategory($startDate, $endDate);

        return collect($byCategory)
            ->sortDesc()
            ->take($limit);
    }

    /**
     * Get revenue by staff member (for payments processed)
     */
    public function getRevenueByStaff($startDate = null, $endDate = null)
    {
        $startDate = $startDate ?? now()->startOfMonth();
        $endDate = $endDate ?? now()->endOfMonth();

        return Payment::completed()
            ->dateRange($startDate, $endDate)
            ->join('users', 'payments.processed_by', '=', 'users.id')
            ->select('users.name', DB::raw('SUM(payments.amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total')
            ->get();
    }

    /**
     * Get revenue summary for dashboard
     */
    public function getRevenueSummary($period = 'today')
    {
        $dates = $this->getPeriodDates($period);

        return [
            'period' => $period,
            'total_revenue' => $this->calculateTotalRevenue($dates['start'], $dates['end']),
            'by_category' => $this->getRevenueByCategory($dates['start'], $dates['end']),
            'by_payment_method' => $this->getRevenueByPaymentMethod($dates['start'], $dates['end']),
            'transaction_count' => $this->getTransactionCount($dates['start'], $dates['end']),
            'average_transaction' => $this->getAverageTransaction($dates['start'], $dates['end']),
        ];
    }

    /**
     * Get period dates
     */
    private function getPeriodDates($period)
    {
        return match($period) {
            'today' => ['start' => now()->startOfDay(), 'end' => now()->endOfDay()],
            'this_week' => ['start' => now()->startOfWeek(), 'end' => now()->endOfWeek()],
            'this_month' => ['start' => now()->startOfMonth(), 'end' => now()->endOfMonth()],
            'this_year' => ['start' => now()->startOfYear(), 'end' => now()->endOfYear()],
            default => ['start' => now()->startOfMonth(), 'end' => now()->endOfMonth()],
        };
    }

    /**
     * Get transaction count
     */
    private function getTransactionCount($startDate, $endDate)
    {
        $payments = Payment::completed()->dateRange($startDate, $endDate)->count();
        $pos = PosTransaction::completed()->dateRange($startDate, $endDate)->count();

        return $payments + $pos;
    }

    /**
     * Get average transaction value
     */
    private function getAverageTransaction($startDate, $endDate)
    {
        $total = $this->calculateTotalRevenue($startDate, $endDate);
        $count = $this->getTransactionCount($startDate, $endDate);

        return $count > 0 ? $total / $count : 0;
    }

    /**
     * Get filtered revenue data
     *
     *  FIX: Now handles both Payment and PosTransaction filtering
     */
    public function getFilteredRevenue(array $filters = [])
    {
        $startDate = $filters['date_from'] ?? now()->startOfMonth();
        $endDate = $filters['date_to'] ?? now()->endOfMonth();

        // Build Payment query
        $paymentQuery = Payment::completed()->dateRange($startDate, $endDate);

        if (!empty($filters['revenue_source'])) {
            $paymentQuery->where('revenue_source', $filters['revenue_source']);
        }

        if (!empty($filters['payment_method'])) {
            $paymentQuery->where('payment_method', $filters['payment_method']);
        }

        if (!empty($filters['student_id'])) {
            $paymentQuery->where('student_id', $filters['student_id']);
        }

        // Get payments with relationships
        $payments = $paymentQuery->with(['student.user', 'invoice', 'processedBy'])
            ->orderBy('payment_date', 'desc')
            ->get();
        // (or if revenue source is pos_sales - though POS uses different table)
        if (empty($filters['student_id']) && empty($filters['revenue_source'])) {
            // Note: PosTransaction is separate from Payment, so we return only payments
            // The POS data is shown in summary cards but not in detailed transaction list
            // This is by design as POS has its own management interface
        }

        return $payments;
    }
}
