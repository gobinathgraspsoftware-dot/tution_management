@extends('layouts.app')

@section('title', 'Payment Cycles')
@section('page-title', 'Payment Cycles')

@push('styles')
<style>
    .cycle-stats {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 15px;
        padding: 25px;
    }
    .collection-rate {
        font-size: 3rem;
        font-weight: bold;
    }
    .status-badge {
        font-size: 0.8rem;
        padding: 0.4rem 0.8rem;
    }
    .progress-ring {
        width: 120px;
        height: 120px;
    }
    .upcoming-item {
        border-left: 4px solid #ffc107;
        padding-left: 15px;
        margin-bottom: 15px;
    }
    .upcoming-item.due-today {
        border-left-color: #dc3545;
    }
    .monthly-chart {
        height: 300px;
    }
    .cycle-detail-card {
        transition: all 0.3s ease;
    }
    .cycle-detail-card:hover {
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    .filter-bar {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 10px;
        margin-bottom: 20px;
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1>
        <i class="fas fa-sync-alt me-2"></i> Payment Cycles
    </h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.invoices.index') }}">Billing</a></li>
            <li class="breadcrumb-item active">Payment Cycles</li>
        </ol>
    </nav>
</div>

<!-- Month Selection -->
<div class="filter-bar">
    <form action="{{ route('admin.billing.payment-cycles') }}" method="GET" class="row g-3 align-items-end">
        <div class="col-md-4">
            <label for="month" class="form-label">Select Month</label>
            <input type="month" name="month" id="month" class="form-control"
                   value="{{ $month->format('Y-m') }}">
        </div>
        <div class="col-md-4">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search me-2"></i> Load Payment Cycles
            </button>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('admin.invoices.bulk-generate-form', ['month' => $month->format('Y-m')]) }}" class="btn btn-success">
                <i class="fas fa-plus me-2"></i> Generate Invoices
            </a>
        </div>
    </form>
</div>

<!-- Overview Stats -->
<div class="row mb-4">
    <div class="col-lg-8">
        <div class="cycle-stats h-100">
            <h4 class="mb-4">
                <i class="fas fa-chart-pie me-2"></i> {{ $cycleOverview['month'] }} Collection Overview
            </h4>
            <div class="row">
                <div class="col-md-3 text-center mb-3">
                    <div class="collection-rate">{{ $cycleOverview['summary']['collection_rate'] }}%</div>
                    <small>Collection Rate</small>
                </div>
                <div class="col-md-3 text-center mb-3">
                    <h3 class="mb-0">RM {{ number_format($cycleOverview['summary']['total_collected'], 2) }}</h3>
                    <small>Collected</small>
                </div>
                <div class="col-md-3 text-center mb-3">
                    <h3 class="mb-0">RM {{ number_format($cycleOverview['summary']['total_expected'], 2) }}</h3>
                    <small>Expected</small>
                </div>
                <div class="col-md-3 text-center mb-3">
                    <h3 class="mb-0">RM {{ number_format($cycleOverview['summary']['total_expected'] - $cycleOverview['summary']['total_collected'], 2) }}</h3>
                    <small>Outstanding</small>
                </div>
            </div>
            <hr class="bg-white my-3">
            <div class="row text-center">
                <div class="col">
                    <span class="badge bg-success status-badge">{{ $cycleOverview['summary']['fully_paid'] }} Paid</span>
                </div>
                <div class="col">
                    <span class="badge bg-warning status-badge">{{ $cycleOverview['summary']['partially_paid'] }} Partial</span>
                </div>
                <div class="col">
                    <span class="badge bg-secondary status-badge">{{ $cycleOverview['summary']['pending'] }} Pending</span>
                </div>
                <div class="col">
                    <span class="badge bg-danger status-badge">{{ $cycleOverview['summary']['overdue'] }} Overdue</span>
                </div>
                <div class="col">
                    <span class="badge bg-info status-badge">{{ $cycleOverview['summary']['no_invoice'] }} No Invoice</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header bg-warning text-dark">
                <i class="fas fa-clock me-2"></i> Due Within 7 Days
            </div>
            <div class="card-body" style="max-height: 250px; overflow-y: auto;">
                @forelse($upcomingCycles as $upcoming)
                    <div class="upcoming-item {{ $upcoming['days_until_due'] <= 0 ? 'due-today' : '' }}">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong>{{ $upcoming['student_name'] }}</strong>
                                <br><small class="text-muted">{{ $upcoming['package'] }}</small>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-{{ $upcoming['days_until_due'] <= 0 ? 'danger' : ($upcoming['days_until_due'] <= 3 ? 'warning' : 'info') }}">
                                    @if($upcoming['days_until_due'] <= 0)
                                        Due Today
                                    @else
                                        {{ $upcoming['days_until_due'] }} days
                                    @endif
                                </span>
                                <br><small class="fw-bold">RM {{ number_format($upcoming['balance'], 2) }}</small>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-muted text-center mb-0">No payments due within 7 days</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Monthly Summary Chart -->
<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-chart-bar me-2"></i> Monthly Payment Summary (Last 6 Months)
    </div>
    <div class="card-body">
        <div class="row">
            @foreach($monthlySummary as $monthData)
                <div class="col-md-2 text-center mb-3">
                    <h6 class="text-muted">{{ $monthData['month'] }}</h6>
                    <div class="progress mb-2" style="height: 100px; transform: rotate(180deg); border-radius: 10px;">
                        <div class="progress-bar bg-success" role="progressbar"
                             style="width: 100%; height: {{ $monthData['collection_rate'] }}%;"
                             title="{{ $monthData['collection_rate'] }}% collected">
                        </div>
                    </div>
                    <small class="d-block fw-bold">{{ $monthData['collection_rate'] }}%</small>
                    <small class="text-muted">
                        RM {{ number_format($monthData['total_collected'] / 1000, 1) }}K collected
                    </small>
                </div>
            @endforeach
        </div>
        <div class="row mt-3">
            <div class="col-12">
                <table class="table table-sm table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Month</th>
                            <th class="text-end">Invoiced</th>
                            <th class="text-end">Collected</th>
                            <th class="text-center">Invoices</th>
                            <th class="text-center">Paid</th>
                            <th class="text-center">Pending</th>
                            <th class="text-center">Overdue</th>
                            <th class="text-center">Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($monthlySummary as $monthData)
                            <tr>
                                <td>{{ $monthData['month'] }}</td>
                                <td class="text-end">RM {{ number_format($monthData['total_invoiced'], 2) }}</td>
                                <td class="text-end">RM {{ number_format($monthData['total_collected'], 2) }}</td>
                                <td class="text-center">{{ $monthData['invoices_count'] }}</td>
                                <td class="text-center"><span class="badge bg-success">{{ $monthData['paid_invoices'] }}</span></td>
                                <td class="text-center"><span class="badge bg-secondary">{{ $monthData['pending_invoices'] }}</span></td>
                                <td class="text-center"><span class="badge bg-danger">{{ $monthData['overdue_invoices'] }}</span></td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $monthData['collection_rate'] >= 80 ? 'success' : ($monthData['collection_rate'] >= 60 ? 'warning' : 'danger') }}">
                                        {{ $monthData['collection_rate'] }}%
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Detailed Enrollment Cycles -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-list me-2"></i> Enrollment Payment Cycles for {{ $cycleOverview['month'] }}</span>
        <div>
            <select id="statusFilter" class="form-select form-select-sm" style="width: auto; display: inline-block;">
                <option value="">All Statuses</option>
                <option value="paid">Paid</option>
                <option value="partial">Partial</option>
                <option value="pending">Pending</option>
                <option value="overdue">Overdue</option>
                <option value="no_invoice">No Invoice</option>
            </select>
            <input type="text" id="searchCycle" class="form-control form-control-sm d-inline-block ms-2"
                   placeholder="Search student..." style="width: 200px;">
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="cyclesTable">
                <thead class="table-light">
                    <tr>
                        <th>Student</th>
                        <th>Package</th>
                        <th>Invoice #</th>
                        <th>Cycle Day</th>
                        <th class="text-end">Amount Due</th>
                        <th class="text-end">Paid</th>
                        <th class="text-end">Balance</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cycleOverview['details'] as $detail)
                        <tr class="cycle-row" data-status="{{ $detail['status'] }}"
                            data-student="{{ strtolower($detail['student_name']) }}">
                            <td>
                                <a href="{{ route('admin.students.show', $detail['student_id']) }}" class="text-decoration-none">
                                    {{ $detail['student_name'] }}
                                </a>
                                <br><small class="text-muted">{{ $detail['student_code'] }}</small>
                            </td>
                            <td>{{ $detail['package'] }}</td>
                            <td>
                                @if($detail['invoice_number'])
                                    <a href="{{ route('admin.invoices.show', $detail['invoice_id']) }}">
                                        {{ $detail['invoice_number'] }}
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-secondary">Day {{ $detail['payment_cycle_day'] }}</span>
                            </td>
                            <td class="text-end fw-bold">RM {{ number_format($detail['amount_due'], 2) }}</td>
                            <td class="text-end text-success">RM {{ number_format($detail['amount_paid'], 2) }}</td>
                            <td class="text-end {{ $detail['balance'] > 0 ? 'text-danger fw-bold' : '' }}">
                                RM {{ number_format($detail['balance'], 2) }}
                            </td>
                            <td>
                                @if($detail['due_date'])
                                    {{ \Carbon\Carbon::parse($detail['due_date'])->format('d M Y') }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $statusClass = match($detail['status']) {
                                        'paid' => 'success',
                                        'partial' => 'warning',
                                        'overdue' => 'danger',
                                        'pending' => 'secondary',
                                        'no_invoice' => 'info',
                                        default => 'secondary'
                                    };
                                @endphp
                                <span class="badge bg-{{ $statusClass }}">{{ ucfirst(str_replace('_', ' ', $detail['status'])) }}</span>
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
                                        <a href="{{ route('admin.invoices.create', ['enrollment_id' => $detail['enrollment_id']]) }}"
                                           class="btn btn-outline-info" title="Create Invoice">
                                            <i class="fas fa-plus"></i>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-4">
                                <i class="fas fa-inbox text-muted fa-3x mb-3 d-block"></i>
                                No enrollment cycles found for this month.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Status filter
    $('#statusFilter').on('change', function() {
        filterTable();
    });

    // Search filter
    $('#searchCycle').on('input', function() {
        filterTable();
    });

    function filterTable() {
        var status = $('#statusFilter').val().toLowerCase();
        var search = $('#searchCycle').val().toLowerCase();

        $('.cycle-row').each(function() {
            var rowStatus = $(this).data('status');
            var rowStudent = $(this).data('student');

            var matchStatus = !status || rowStatus === status;
            var matchSearch = !search || rowStudent.indexOf(search) > -1;

            if (matchStatus && matchSearch) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    }
});
</script>
@endpush
