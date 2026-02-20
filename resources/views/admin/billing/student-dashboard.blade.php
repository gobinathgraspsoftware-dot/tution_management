{{-- 
    FILE: resources/views/admin/billing/student-dashboard.blade.php
    PURPOSE: Billing & Payments module - Online/Offline Student Billing Dashboard
    ROUTE: admin.billing.student-dashboard
--}}
@extends('layouts.app')

@section('title', 'Student Billing Dashboard')
@section('page-title', 'Student Billing Dashboard')

@push('styles')
<style>
    .billing-stat-card {
        background: white;
        border-radius: 12px;
        padding: 1.25rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        transition: transform 0.2s, box-shadow 0.2s;
        height: 100%;
    }
    .billing-stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 16px rgba(0,0,0,0.1);
    }
    .billing-stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
    }
    .billing-stat-value {
        font-size: 1.5rem;
        font-weight: 700;
        line-height: 1.2;
    }
    .billing-stat-label {
        font-size: 0.8rem;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .tab-student-type .nav-link {
        font-weight: 600;
        border: none;
        border-bottom: 3px solid transparent;
        color: #6c757d;
        padding: 0.75rem 1.25rem;
    }
    .tab-student-type .nav-link.active {
        color: #fda530;
        border-bottom-color: #fda530;
        background: transparent;
    }
    .tab-student-type .nav-link:hover:not(.active) {
        color: #4c4c4c;
        border-bottom-color: #dee2e6;
    }
    .student-avatar-sm {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.85rem;
        color: white;
    }
    .chart-container {
        position: relative;
        height: 280px;
    }
    .filter-section {
        background: white;
        border-radius: 10px;
        padding: 1rem 1.25rem;
        margin-bottom: 1.25rem;
        box-shadow: 0 1px 4px rgba(0,0,0,0.04);
    }
    .table-billing th {
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #6c757d;
        font-weight: 600;
        border-bottom: 2px solid #dee2e6;
    }
    .table-billing td {
        vertical-align: middle;
        font-size: 0.9rem;
    }
    .progress-thin {
        height: 6px;
        border-radius: 3px;
    }
    .export-btn-group .btn {
        font-size: 0.82rem;
        padding: 0.35rem 0.75rem;
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h1><i class="fas fa-chart-bar text-warning me-2"></i>Student Billing Dashboard</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.invoices.index') }}">Billing</a></li>
                    <li class="breadcrumb-item active">Student Billing Dashboard</li>
                </ol>
            </nav>
        </div>
        <div class="export-btn-group d-flex gap-2">
            <a href="{{ route('admin.billing.student-dashboard', array_merge(request()->all(), ['export' => 'csv'])) }}" class="btn btn-outline-success btn-sm">
                <i class="fas fa-file-csv me-1"></i> Export CSV
            </a>
            <button class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                <i class="fas fa-print me-1"></i> Print
            </button>
        </div>
    </div>
</div>

{{-- Summary Statistics Row --}}
<div class="row g-3 mb-4">
    {{-- Total Active Students --}}
    <div class="col-xl-3 col-md-6">
        <div class="billing-stat-card">
            <div class="d-flex align-items-center gap-3">
                <div class="billing-stat-icon bg-primary bg-opacity-10 text-primary">
                    <i class="fas fa-users"></i>
                </div>
                <div>
                    <div class="billing-stat-value text-primary">{{ number_format($stats['total_students']) }}</div>
                    <div class="billing-stat-label">Total Active Students</div>
                </div>
            </div>
            <div class="mt-2 pt-2 border-top d-flex justify-content-between small text-muted">
                <span><i class="fas fa-globe me-1 text-info"></i> {{ $stats['online_count'] }} Online</span>
                <span><i class="fas fa-school me-1 text-success"></i> {{ $stats['offline_count'] }} Offline</span>
            </div>
        </div>
    </div>

    {{-- Total Revenue This Month --}}
    <div class="col-xl-3 col-md-6">
        <div class="billing-stat-card">
            <div class="d-flex align-items-center gap-3">
                <div class="billing-stat-icon bg-success bg-opacity-10 text-success">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div>
                    <div class="billing-stat-value text-success">RM {{ number_format($stats['total_revenue_month'], 2) }}</div>
                    <div class="billing-stat-label">Revenue This Month</div>
                </div>
            </div>
            <div class="mt-2 pt-2 border-top d-flex justify-content-between small text-muted">
                <span><i class="fas fa-globe me-1 text-info"></i> RM {{ number_format($stats['online_revenue_month'], 2) }}</span>
                <span><i class="fas fa-school me-1 text-success"></i> RM {{ number_format($stats['offline_revenue_month'], 2) }}</span>
            </div>
        </div>
    </div>

    {{-- Outstanding Amount --}}
    <div class="col-xl-3 col-md-6">
        <div class="billing-stat-card">
            <div class="d-flex align-items-center gap-3">
                <div class="billing-stat-icon bg-warning bg-opacity-10 text-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div>
                    <div class="billing-stat-value text-warning">RM {{ number_format($stats['total_outstanding'], 2) }}</div>
                    <div class="billing-stat-label">Total Outstanding</div>
                </div>
            </div>
            <div class="mt-2 pt-2 border-top d-flex justify-content-between small text-muted">
                <span><i class="fas fa-globe me-1 text-info"></i> RM {{ number_format($stats['online_outstanding'], 2) }}</span>
                <span><i class="fas fa-school me-1 text-success"></i> RM {{ number_format($stats['offline_outstanding'], 2) }}</span>
            </div>
        </div>
    </div>

    {{-- Collection Rate --}}
    <div class="col-xl-3 col-md-6">
        <div class="billing-stat-card">
            <div class="d-flex align-items-center gap-3">
                <div class="billing-stat-icon bg-info bg-opacity-10 text-info">
                    <i class="fas fa-percentage"></i>
                </div>
                <div>
                    <div class="billing-stat-value text-info">{{ $stats['collection_rate'] }}%</div>
                    <div class="billing-stat-label">Collection Rate</div>
                </div>
            </div>
            <div class="mt-2 pt-2 border-top">
                <div class="progress progress-thin">
                    <div class="progress-bar bg-info" style="width: {{ $stats['collection_rate'] }}%"></div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Charts Row --}}
<div class="row g-3 mb-4">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-chart-pie me-2 text-warning"></i>Student Distribution</span>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="studentDistributionChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-chart-bar me-2 text-warning"></i>Revenue Comparison (Last 6 Months)</span>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="revenueComparisonChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Filter Section --}}
<div class="filter-section">
    <form method="GET" action="{{ route('admin.billing.student-dashboard') }}" id="filterForm">
        <div class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Search Student</label>
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Name, Student ID, Email..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Package</label>
                <select name="package_id" class="form-select form-select-sm">
                    <option value="">All Packages</option>
                    @foreach($packages as $package)
                        <option value="{{ $package->id }}" {{ request('package_id') == $package->id ? 'selected' : '' }}>
                            {{ $package->name }} ({{ ucfirst($package->type) }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Payment Status</label>
                <select name="payment_status" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Fully Paid</option>
                    <option value="partial" {{ request('payment_status') == 'partial' ? 'selected' : '' }}>Partial</option>
                    <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="overdue" {{ request('payment_status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Month</label>
                <input type="month" name="month" class="form-control form-control-sm" value="{{ request('month', now()->format('Y-m')) }}">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-warning btn-sm"><i class="fas fa-filter me-1"></i> Filter</button>
                <a href="{{ route('admin.billing.student-dashboard') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-redo me-1"></i> Reset</a>
            </div>
        </div>
    </form>
</div>

{{-- Tabbed Student Tables --}}
<div class="card">
    <div class="card-header p-0 bg-white">
        <ul class="nav nav-tabs tab-student-type border-0" id="studentTypeTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="online-tab" data-bs-toggle="tab" data-bs-target="#onlineStudents" type="button" role="tab">
                    <i class="fas fa-globe me-1 text-info"></i> Online Students
                    <span class="badge bg-info ms-1">{{ $stats['online_count'] }}</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="offline-tab" data-bs-toggle="tab" data-bs-target="#offlineStudents" type="button" role="tab">
                    <i class="fas fa-school me-1 text-success"></i> Offline Students
                    <span class="badge bg-success ms-1">{{ $stats['offline_count'] }}</span>
                </button>
            </li>
        </ul>
    </div>
    <div class="card-body p-0">
        <div class="tab-content" id="studentTypeTabContent">

            {{-- ========== ONLINE STUDENTS TAB ========== --}}
            <div class="tab-pane fade show active" id="onlineStudents" role="tabpanel">
                @if($onlineStudents->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover table-billing mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Student</th>
                                <th>Student ID</th>
                                <th>Package</th>
                                <th>Monthly Fee</th>
                                <th>Total Invoiced</th>
                                <th>Total Paid</th>
                                <th>Outstanding</th>
                                <th>Last Payment</th>
                                <th>Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($onlineStudents as $index => $student)
                            @php
                                $activeEnrollment = $student->enrollments->first();
                                $outstanding = max(0, ($student->billing_total_invoiced ?? 0) - ($student->billing_total_paid ?? 0));
                            @endphp
                            <tr>
                                <td>{{ $onlineStudents->firstItem() + $index }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="student-avatar-sm bg-info">
                                            {{ strtoupper(substr($student->user->name ?? 'N', 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ $student->user->name ?? 'N/A' }}</div>
                                            <small class="text-muted">{{ $student->user->email ?? '' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge bg-info bg-opacity-10 text-info">{{ $student->student_id }}</span></td>
                                <td>{{ $activeEnrollment->package->name ?? 'N/A' }}</td>
                                <td>RM {{ number_format($activeEnrollment->monthly_fee ?? 0, 2) }}</td>
                                <td>RM {{ number_format($student->billing_total_invoiced ?? 0, 2) }}</td>
                                <td class="text-success fw-semibold">RM {{ number_format($student->billing_total_paid ?? 0, 2) }}</td>
                                <td>
                                    <span class="{{ $outstanding > 0 ? 'text-danger fw-semibold' : 'text-success' }}">
                                        RM {{ number_format($outstanding, 2) }}
                                    </span>
                                </td>
                                <td>
                                    @if($student->last_payment_date)
                                        <small>{{ \Carbon\Carbon::parse($student->last_payment_date)->format('d M Y') }}</small>
                                    @else
                                        <small class="text-muted">No payment</small>
                                    @endif
                                </td>
                                <td>
                                    @if($outstanding <= 0 && ($student->billing_total_invoiced ?? 0) > 0)
                                        <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Paid</span>
                                    @elseif($student->has_overdue)
                                        <span class="badge bg-danger"><i class="fas fa-exclamation-circle me-1"></i>Overdue</span>
                                    @elseif(($student->billing_total_paid ?? 0) > 0)
                                        <span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i>Partial</span>
                                    @elseif(($student->billing_total_invoiced ?? 0) > 0)
                                        <span class="badge bg-secondary"><i class="fas fa-hourglass-half me-1"></i>Pending</span>
                                    @else
                                        <span class="badge bg-light text-muted">No Invoice</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        @if(Route::has('admin.students.show'))
                                        <a href="{{ route('admin.students.show', $student->id) }}" class="btn btn-outline-primary" title="View Profile">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @endif
                                        @if(Route::has('admin.invoices.index'))
                                        <a href="{{ route('admin.invoices.index', ['student_id' => $student->id]) }}" class="btn btn-outline-info" title="View Invoices">
                                            <i class="fas fa-file-invoice"></i>
                                        </a>
                                        @endif
                                        @if(Route::has('admin.payments.index'))
                                        <a href="{{ route('admin.payments.index', ['student_id' => $student->id]) }}" class="btn btn-outline-success" title="View Payments">
                                            <i class="fas fa-money-bill"></i>
                                        </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-3 py-2">
                    {{ $onlineStudents->appends(request()->except('online_page'))->links() }}
                </div>
                @else
                <div class="text-center py-5">
                    <i class="fas fa-globe fa-3x text-muted mb-3"></i>
                    <p class="text-muted mb-0">No online students found matching the criteria.</p>
                </div>
                @endif
            </div>

            {{-- ========== OFFLINE STUDENTS TAB ========== --}}
            <div class="tab-pane fade" id="offlineStudents" role="tabpanel">
                @if($offlineStudents->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover table-billing mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Student</th>
                                <th>Student ID</th>
                                <th>Package</th>
                                <th>Monthly Fee</th>
                                <th>Total Invoiced</th>
                                <th>Total Paid</th>
                                <th>Outstanding</th>
                                <th>Last Payment</th>
                                <th>Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($offlineStudents as $index => $student)
                            @php
                                $activeEnrollment = $student->enrollments->first();
                                $outstanding = max(0, ($student->billing_total_invoiced ?? 0) - ($student->billing_total_paid ?? 0));
                            @endphp
                            <tr>
                                <td>{{ $offlineStudents->firstItem() + $index }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="student-avatar-sm bg-success">
                                            {{ strtoupper(substr($student->user->name ?? 'N', 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ $student->user->name ?? 'N/A' }}</div>
                                            <small class="text-muted">{{ $student->user->email ?? '' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge bg-success bg-opacity-10 text-success">{{ $student->student_id }}</span></td>
                                <td>{{ $activeEnrollment->package->name ?? 'N/A' }}</td>
                                <td>RM {{ number_format($activeEnrollment->monthly_fee ?? 0, 2) }}</td>
                                <td>RM {{ number_format($student->billing_total_invoiced ?? 0, 2) }}</td>
                                <td class="text-success fw-semibold">RM {{ number_format($student->billing_total_paid ?? 0, 2) }}</td>
                                <td>
                                    <span class="{{ $outstanding > 0 ? 'text-danger fw-semibold' : 'text-success' }}">
                                        RM {{ number_format($outstanding, 2) }}
                                    </span>
                                </td>
                                <td>
                                    @if($student->last_payment_date)
                                        <small>{{ \Carbon\Carbon::parse($student->last_payment_date)->format('d M Y') }}</small>
                                    @else
                                        <small class="text-muted">No payment</small>
                                    @endif
                                </td>
                                <td>
                                    @if($outstanding <= 0 && ($student->billing_total_invoiced ?? 0) > 0)
                                        <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Paid</span>
                                    @elseif($student->has_overdue)
                                        <span class="badge bg-danger"><i class="fas fa-exclamation-circle me-1"></i>Overdue</span>
                                    @elseif(($student->billing_total_paid ?? 0) > 0)
                                        <span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i>Partial</span>
                                    @elseif(($student->billing_total_invoiced ?? 0) > 0)
                                        <span class="badge bg-secondary"><i class="fas fa-hourglass-half me-1"></i>Pending</span>
                                    @else
                                        <span class="badge bg-light text-muted">No Invoice</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        @if(Route::has('admin.students.show'))
                                        <a href="{{ route('admin.students.show', $student->id) }}" class="btn btn-outline-primary" title="View Profile">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @endif
                                        @if(Route::has('admin.invoices.index'))
                                        <a href="{{ route('admin.invoices.index', ['student_id' => $student->id]) }}" class="btn btn-outline-info" title="View Invoices">
                                            <i class="fas fa-file-invoice"></i>
                                        </a>
                                        @endif
                                        @if(Route::has('admin.payments.index'))
                                        <a href="{{ route('admin.payments.index', ['student_id' => $student->id]) }}" class="btn btn-outline-success" title="View Payments">
                                            <i class="fas fa-money-bill"></i>
                                        </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-3 py-2">
                    {{ $offlineStudents->appends(request()->except('offline_page'))->links() }}
                </div>
                @else
                <div class="text-center py-5">
                    <i class="fas fa-school fa-3x text-muted mb-3"></i>
                    <p class="text-muted mb-0">No offline students found matching the criteria.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Quick Summary Cards Row --}}
<div class="row g-3 mt-2">
    <div class="col-md-6">
        <div class="card border-info">
            <div class="card-header bg-info bg-opacity-10">
                <i class="fas fa-globe me-2 text-info"></i><strong>Online Students Summary</strong>
            </div>
            <div class="card-body">
                <div class="row g-3 text-center">
                    <div class="col-4">
                        <div class="fw-bold fs-5 text-info">{{ $stats['online_count'] }}</div>
                        <small class="text-muted">Students</small>
                    </div>
                    <div class="col-4">
                        <div class="fw-bold fs-5 text-success">RM {{ number_format($stats['online_revenue_month'], 2) }}</div>
                        <small class="text-muted">Revenue/Month</small>
                    </div>
                    <div class="col-4">
                        <div class="fw-bold fs-5 text-danger">RM {{ number_format($stats['online_outstanding'], 2) }}</div>
                        <small class="text-muted">Outstanding</small>
                    </div>
                </div>
                @if($stats['online_count'] > 0)
                <hr>
                <div class="d-flex justify-content-between small text-muted">
                    <span>Avg. Monthly Fee: <strong>RM {{ number_format($stats['online_avg_fee'], 2) }}</strong></span>
                    <span>Overdue: <strong class="text-danger">{{ $stats['online_overdue_count'] }}</strong></span>
                </div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-success">
            <div class="card-header bg-success bg-opacity-10">
                <i class="fas fa-school me-2 text-success"></i><strong>Offline Students Summary</strong>
            </div>
            <div class="card-body">
                <div class="row g-3 text-center">
                    <div class="col-4">
                        <div class="fw-bold fs-5 text-success">{{ $stats['offline_count'] }}</div>
                        <small class="text-muted">Students</small>
                    </div>
                    <div class="col-4">
                        <div class="fw-bold fs-5 text-success">RM {{ number_format($stats['offline_revenue_month'], 2) }}</div>
                        <small class="text-muted">Revenue/Month</small>
                    </div>
                    <div class="col-4">
                        <div class="fw-bold fs-5 text-danger">RM {{ number_format($stats['offline_outstanding'], 2) }}</div>
                        <small class="text-muted">Outstanding</small>
                    </div>
                </div>
                @if($stats['offline_count'] > 0)
                <hr>
                <div class="d-flex justify-content-between small text-muted">
                    <span>Avg. Monthly Fee: <strong>RM {{ number_format($stats['offline_avg_fee'], 2) }}</strong></span>
                    <span>Overdue: <strong class="text-danger">{{ $stats['offline_overdue_count'] }}</strong></span>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // ── Student Distribution Doughnut Chart ──────────────────
    const distCtx = document.getElementById('studentDistributionChart').getContext('2d');
    new Chart(distCtx, {
        type: 'doughnut',
        data: {
            labels: ['Online Students', 'Offline Students'],
            datasets: [{
                data: [{{ $stats['online_count'] }}, {{ $stats['offline_count'] }}],
                backgroundColor: ['#0dcaf0', '#198754'],
                borderWidth: 2,
                borderColor: '#fff',
                hoverOffset: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { padding: 16, usePointStyle: true, pointStyle: 'circle' }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const pct = total > 0 ? ((context.parsed / total) * 100).toFixed(1) : 0;
                            return context.label + ': ' + context.parsed + ' (' + pct + '%)';
                        }
                    }
                }
            },
            cutout: '65%'
        }
    });

    // ── Revenue Comparison Bar Chart ─────────────────────────
    const revCtx = document.getElementById('revenueComparisonChart').getContext('2d');
    new Chart(revCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($chartData['months']) !!},
            datasets: [
                {
                    label: 'Online Revenue',
                    data: {!! json_encode($chartData['online_revenue']) !!},
                    backgroundColor: 'rgba(13, 202, 240, 0.7)',
                    borderColor: '#0dcaf0',
                    borderWidth: 1,
                    borderRadius: 4
                },
                {
                    label: 'Offline Revenue',
                    data: {!! json_encode($chartData['offline_revenue']) !!},
                    backgroundColor: 'rgba(25, 135, 84, 0.7)',
                    borderColor: '#198754',
                    borderWidth: 1,
                    borderRadius: 4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { padding: 16, usePointStyle: true, pointStyle: 'rect' }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': RM ' + parseFloat(context.parsed.y).toLocaleString('en-MY', {minimumFractionDigits: 2});
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) { return 'RM ' + value.toLocaleString(); }
                    },
                    grid: { color: 'rgba(0,0,0,0.05)' }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });

    // Activate correct tab from URL hash
    const hash = window.location.hash;
    if (hash === '#offlineStudents') {
        $('#offline-tab').tab('show');
    }
});
</script>
@endpush
