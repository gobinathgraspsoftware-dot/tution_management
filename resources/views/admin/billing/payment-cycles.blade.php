@extends('layouts.app')

@section('title', 'Payment Cycles')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Payment Cycles</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.invoices.index') }}">Invoices</a></li>
                    <li class="breadcrumb-item active">Payment Cycles</li>
                </ol>
            </nav>
        </div>
        <div>
            <form method="GET" action="{{ route('admin.billing.payment-cycles') }}" class="d-flex gap-2">
                <input type="month" name="month" class="form-control"
                       value="{{ $month->format('Y-m') }}" onchange="this.form.submit()">
            </form>
        </div>
    </div>

    <!-- Month Overview -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>{{ $cycleOverview['month'] }} Overview</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3 col-6">
                    <div class="bg-light rounded p-3 text-center">
                        <div class="h3 mb-0 text-primary">{{ $cycleOverview['summary']['total_enrollments'] }}</div>
                        <small class="text-muted">Total Enrollments</small>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="bg-success bg-opacity-10 rounded p-3 text-center">
                        <div class="h3 mb-0 text-success">{{ $cycleOverview['summary']['fully_paid'] }}</div>
                        <small class="text-muted">Fully Paid</small>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="bg-warning bg-opacity-10 rounded p-3 text-center">
                        <div class="h3 mb-0 text-warning">{{ $cycleOverview['summary']['pending'] }}</div>
                        <small class="text-muted">Pending</small>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="bg-danger bg-opacity-10 rounded p-3 text-center">
                        <div class="h3 mb-0 text-danger">{{ $cycleOverview['summary']['overdue'] }}</div>
                        <small class="text-muted">Overdue</small>
                    </div>
                </div>
            </div>

            <hr class="my-4">

            <div class="row">
                <div class="col-md-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Expected Revenue:</span>
                        <strong class="text-primary">RM {{ number_format($cycleOverview['summary']['total_expected'], 2) }}</strong>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Collected:</span>
                        <strong class="text-success">RM {{ number_format($cycleOverview['summary']['total_collected'], 2) }}</strong>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Collection Rate:</span>
                        <strong class="{{ $cycleOverview['summary']['collection_rate'] >= 80 ? 'text-success' : ($cycleOverview['summary']['collection_rate'] >= 50 ? 'text-warning' : 'text-danger') }}">
                            {{ $cycleOverview['summary']['collection_rate'] }}%
                        </strong>
                    </div>
                </div>
            </div>

            <!-- Progress Bar -->
            <div class="progress mt-3" style="height: 25px;">
                @php
                    $total = $cycleOverview['summary']['total_enrollments'] ?: 1;
                    $paidPercent = ($cycleOverview['summary']['fully_paid'] / $total) * 100;
                    $partialPercent = ($cycleOverview['summary']['partially_paid'] / $total) * 100;
                    $pendingPercent = ($cycleOverview['summary']['pending'] / $total) * 100;
                    $overduePercent = ($cycleOverview['summary']['overdue'] / $total) * 100;
                    $noInvoicePercent = ($cycleOverview['summary']['no_invoice'] / $total) * 100;
                @endphp
                <div class="progress-bar bg-success" role="progressbar" style="width: {{ $paidPercent }}%" title="Paid">
                    @if($paidPercent > 5) {{ $cycleOverview['summary']['fully_paid'] }} @endif
                </div>
                <div class="progress-bar bg-info" role="progressbar" style="width: {{ $partialPercent }}%" title="Partial">
                    @if($partialPercent > 5) {{ $cycleOverview['summary']['partially_paid'] }} @endif
                </div>
                <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $pendingPercent }}%" title="Pending">
                    @if($pendingPercent > 5) {{ $cycleOverview['summary']['pending'] }} @endif
                </div>
                <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $overduePercent }}%" title="Overdue">
                    @if($overduePercent > 5) {{ $cycleOverview['summary']['overdue'] }} @endif
                </div>
                <div class="progress-bar bg-secondary" role="progressbar" style="width: {{ $noInvoicePercent }}%" title="No Invoice">
                    @if($noInvoicePercent > 5) {{ $cycleOverview['summary']['no_invoice'] }} @endif
                </div>
            </div>
            <div class="d-flex flex-wrap gap-3 mt-2 small">
                <span><span class="badge bg-success">&nbsp;</span> Paid</span>
                <span><span class="badge bg-info">&nbsp;</span> Partial</span>
                <span><span class="badge bg-warning">&nbsp;</span> Pending</span>
                <span><span class="badge bg-danger">&nbsp;</span> Overdue</span>
                <span><span class="badge bg-secondary">&nbsp;</span> No Invoice</span>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Upcoming Payments -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Upcoming Payments (Next 7 Days)</h5>
                </div>
                <div class="card-body p-0">
                    @if($upcomingCycles->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Student</th>
                                        <th>Invoice</th>
                                        <th>Balance</th>
                                        <th>Due</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($upcomingCycles as $cycle)
                                        <tr>
                                            <td>
                                                <strong>{{ $cycle['student_name'] }}</strong>
                                                <br><small class="text-muted">{{ $cycle['student_code'] }}</small>
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.invoices.show', $cycle['invoice_id']) }}">
                                                    {{ $cycle['invoice_number'] }}
                                                </a>
                                            </td>
                                            <td>
                                                <strong class="text-danger">RM {{ number_format($cycle['balance'], 2) }}</strong>
                                            </td>
                                            <td>
                                                @if($cycle['days_until_due'] == 0)
                                                    <span class="badge bg-warning">Today</span>
                                                @elseif($cycle['days_until_due'] == 1)
                                                    <span class="badge bg-info">Tomorrow</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ $cycle['days_until_due'] }} days</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-check-circle fa-3x mb-3"></i>
                            <p>No upcoming payments in the next 7 days</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Monthly Trends -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Monthly Collection Summary</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Month</th>
                                    <th>Invoiced</th>
                                    <th>Collected</th>
                                    <th>Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($monthlySummary as $summary)
                                    <tr>
                                        <td><strong>{{ $summary['month'] }}</strong></td>
                                        <td>RM {{ number_format($summary['total_invoiced'], 2) }}</td>
                                        <td class="text-success">RM {{ number_format($summary['total_collected'], 2) }}</td>
                                        <td>
                                            @php
                                                $rate = $summary['collection_rate'];
                                                $badgeClass = $rate >= 80 ? 'success' : ($rate >= 50 ? 'warning' : 'danger');
                                            @endphp
                                            <span class="badge bg-{{ $badgeClass }}">{{ $rate }}%</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enrollment Details -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Enrollment Payment Status</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="enrollmentTable">
                    <thead class="table-light">
                        <tr>
                            <th>Student</th>
                            <th>Package</th>
                            <th>Invoice</th>
                            <th>Amount Due</th>
                            <th>Paid</th>
                            <th>Balance</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cycleOverview['details'] as $detail)
                            <tr>
                                <td>
                                    <strong>{{ $detail['student_name'] }}</strong>
                                    <br><small class="text-muted">{{ $detail['student_code'] }}</small>
                                </td>
                                <td>{{ $detail['package'] }}</td>
                                <td>
                                    @if($detail['invoice_id'])
                                        <a href="{{ route('admin.invoices.show', $detail['invoice_id']) }}">
                                            {{ $detail['invoice_number'] }}
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>RM {{ number_format($detail['amount_due'], 2) }}</td>
                                <td class="text-success">RM {{ number_format($detail['amount_paid'], 2) }}</td>
                                <td class="{{ $detail['balance'] > 0 ? 'text-danger' : 'text-success' }}">
                                    RM {{ number_format($detail['balance'], 2) }}
                                </td>
                                <td>{{ $detail['due_date'] ?? '-' }}</td>
                                <td>
                                    @switch($detail['status'])
                                        @case('paid')
                                            <span class="badge bg-success">Paid</span>
                                            @break
                                        @case('partial')
                                            <span class="badge bg-info">Partial</span>
                                            @break
                                        @case('pending')
                                            <span class="badge bg-warning">Pending</span>
                                            @break
                                        @case('overdue')
                                            <span class="badge bg-danger">Overdue</span>
                                            @break
                                        @case('no_invoice')
                                            <span class="badge bg-secondary">No Invoice</span>
                                            @break
                                        @default
                                            <span class="badge bg-secondary">{{ ucfirst($detail['status']) }}</span>
                                    @endswitch
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        @if($detail['invoice_id'])
                                            <a href="{{ route('admin.invoices.show', $detail['invoice_id']) }}"
                                               class="btn btn-outline-primary" title="View Invoice">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($detail['balance'] > 0)
                                                <a href="{{ route('admin.payments.create', ['invoice_id' => $detail['invoice_id']]) }}"
                                                   class="btn btn-outline-success" title="Record Payment">
                                                    <i class="fas fa-money-bill"></i>
                                                </a>
                                            @endif
                                        @else
                                            <a href="{{ route('admin.invoices.create', ['student_id' => $detail['student_id'], 'enrollment_id' => $detail['enrollment_id']]) }}"
                                               class="btn btn-outline-primary" title="Create Invoice">
                                                <i class="fas fa-file-invoice"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3"></i>
                                    <p>No enrollments found for this period</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .progress-bar {
        transition: width 0.6s ease;
    }
    .table th {
        white-space: nowrap;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize DataTable if available
    if ($.fn.DataTable) {
        $('#enrollmentTable').DataTable({
            pageLength: 25,
            order: [[7, 'asc']],
            columnDefs: [
                { orderable: false, targets: [8] }
            ]
        });
    }
});
</script>
@endpush
