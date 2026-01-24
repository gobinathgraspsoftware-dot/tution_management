@extends('layouts.app')

@section('title', 'Cash Reports Summary')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Cash Reports Summary</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.pos.daily-reports.index') }}">Daily Reports</a></li>
                    <li class="breadcrumb-item active">Summary</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('admin.pos.daily-reports.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Reports
        </a>
    </div>

    <!-- Period Filter -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('admin.pos.daily-reports.summary') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Period</label>
                    <select name="period" class="form-select" id="periodSelect">
                        <option value="this_month" {{ request('period', 'this_month') == 'this_month' ? 'selected' : '' }}>This Month</option>
                        <option value="last_month" {{ request('period') == 'last_month' ? 'selected' : '' }}>Last Month</option>
                        <option value="this_year" {{ request('period') == 'this_year' ? 'selected' : '' }}>This Year</option>
                        <option value="custom" {{ request('period') == 'custom' ? 'selected' : '' }}>Custom Range</option>
                    </select>
                </div>
                <div class="col-md-3 custom-dates" style="{{ request('period') == 'custom' ? '' : 'display:none;' }}">
                    <label class="form-label">From Date</label>
                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                </div>
                <div class="col-md-3 custom-dates" style="{{ request('period') == 'custom' ? '' : 'display:none;' }}">
                    <label class="form-label">To Date</label>
                    <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-chart-bar me-1"></i> Generate Summary
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Statistics -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-money-bill-wave fa-2x opacity-75"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-white-50 mb-1">Total Revenue</h6>
                            <h3 class="mb-0">RM {{ number_format($summary['total_revenue'] ?? 0, 2) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 bg-success text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-shopping-cart fa-2x opacity-75"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-white-50 mb-1">Total Transactions</h6>
                            <h3 class="mb-0">{{ number_format($summary['total_transactions'] ?? 0) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 bg-info text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-chart-line fa-2x opacity-75"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-white-50 mb-1">Avg Daily Sales</h6>
                            <h3 class="mb-0">RM {{ number_format($summary['avg_daily_sales'] ?? 0, 2) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 bg-warning text-dark">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-receipt fa-2x opacity-75"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-dark-50 mb-1">Avg Transaction</h6>
                            <h3 class="mb-0">RM {{ number_format($summary['avg_transaction'] ?? 0, 2) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Daily Sales Chart -->
        <div class="col-lg-8 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-chart-area text-primary me-2"></i>Daily Sales Trend</h5>
                </div>
                <div class="card-body">
                    <canvas id="dailySalesChart" height="300"></canvas>
                </div>
            </div>
        </div>

        <!-- Payment Methods Breakdown -->
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-chart-pie text-success me-2"></i>Payment Methods</h5>
                </div>
                <div class="card-body">
                    <canvas id="paymentMethodsChart" height="250"></canvas>
                    <div class="mt-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span><i class="fas fa-circle text-success me-2"></i>Cash</span>
                            <strong>RM {{ number_format($summary['cash_total'] ?? 0, 2) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span><i class="fas fa-circle text-primary me-2"></i>QR Payment</span>
                            <strong>RM {{ number_format($summary['qr_total'] ?? 0, 2) }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Cash Variance Summary -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-balance-scale text-warning me-2"></i>Cash Variance Summary</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center mb-4">
                        <div class="col-4">
                            <div class="p-3 bg-success bg-opacity-10 rounded">
                                <h3 class="text-success mb-1">{{ $summary['balanced_days'] ?? 0 }}</h3>
                                <small class="text-muted">Balanced Days</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-3 bg-info bg-opacity-10 rounded">
                                <h3 class="text-info mb-1">{{ $summary['over_days'] ?? 0 }}</h3>
                                <small class="text-muted">Over Days</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-3 bg-danger bg-opacity-10 rounded">
                                <h3 class="text-danger mb-1">{{ $summary['short_days'] ?? 0 }}</h3>
                                <small class="text-muted">Short Days</small>
                            </div>
                        </div>
                    </div>
                    <table class="table table-sm">
                        <tr>
                            <td class="text-muted">Total Over Amount</td>
                            <td class="text-end text-info">+ RM {{ number_format($summary['total_over'] ?? 0, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Total Short Amount</td>
                            <td class="text-end text-danger">- RM {{ number_format($summary['total_short'] ?? 0, 2) }}</td>
                        </tr>
                        <tr class="border-top">
                            <td><strong>Net Variance</strong></td>
                            <td class="text-end">
                                @php
                                    $netVariance = ($summary['total_over'] ?? 0) - ($summary['total_short'] ?? 0);
                                @endphp
                                @if($netVariance >= 0)
                                    <strong class="text-success">+ RM {{ number_format($netVariance, 2) }}</strong>
                                @else
                                    <strong class="text-danger">- RM {{ number_format(abs($netVariance), 2) }}</strong>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Top Performing Days -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-trophy text-warning me-2"></i>Top Performing Days</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>#</th>
                                    <th>Date</th>
                                    <th class="text-end">Sales</th>
                                    <th class="text-center">Transactions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topDays ?? [] as $index => $day)
                                    <tr>
                                        <td>
                                            @if($index == 0)
                                                <i class="fas fa-medal text-warning"></i>
                                            @elseif($index == 1)
                                                <i class="fas fa-medal text-secondary"></i>
                                            @elseif($index == 2)
                                                <i class="fas fa-medal" style="color: #cd7f32;"></i>
                                            @else
                                                {{ $index + 1 }}
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.pos.daily-reports.show', $day->id) }}">
                                                {{ $day->report_date->format('d M Y') }}
                                            </a>
                                            <br>
                                            <small class="text-muted">{{ $day->report_date->format('l') }}</small>
                                        </td>
                                        <td class="text-end">
                                            <strong>RM {{ number_format($day->total_sales, 2) }}</strong>
                                        </td>
                                        <td class="text-center">{{ $day->total_transactions }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-3 text-muted">
                                            No data available
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Day of Week Analysis -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0"><i class="fas fa-calendar-week text-info me-2"></i>Sales by Day of Week</h5>
        </div>
        <div class="card-body">
            <canvas id="dayOfWeekChart" height="100"></canvas>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // Toggle custom date fields
    $('#periodSelect').change(function() {
        if ($(this).val() === 'custom') {
            $('.custom-dates').show();
        } else {
            $('.custom-dates').hide();
        }
    });

    // Daily Sales Chart
    const dailySalesCtx = document.getElementById('dailySalesChart').getContext('2d');
    new Chart(dailySalesCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($chartData['dates'] ?? []) !!},
            datasets: [{
                label: 'Sales (RM)',
                data: {!! json_encode($chartData['sales'] ?? []) !!},
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                fill: true,
                tension: 0.3
            }, {
                label: 'Transactions',
                data: {!! json_encode($chartData['transactions'] ?? []) !!},
                borderColor: '#198754',
                backgroundColor: 'transparent',
                yAxisID: 'y1',
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index'
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Sales (RM)'
                    }
                },
                y1: {
                    beginAtZero: true,
                    position: 'right',
                    grid: {
                        drawOnChartArea: false
                    },
                    title: {
                        display: true,
                        text: 'Transactions'
                    }
                }
            }
        }
    });

    // Payment Methods Chart
    const paymentCtx = document.getElementById('paymentMethodsChart').getContext('2d');
    new Chart(paymentCtx, {
        type: 'doughnut',
        data: {
            labels: ['Cash', 'QR Payment'],
            datasets: [{
                data: [{{ $summary['cash_total'] ?? 0 }}, {{ $summary['qr_total'] ?? 0 }}],
                backgroundColor: ['#198754', '#0d6efd'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Day of Week Chart
    const dayOfWeekCtx = document.getElementById('dayOfWeekChart').getContext('2d');
    new Chart(dayOfWeekCtx, {
        type: 'bar',
        data: {
            labels: ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
            datasets: [{
                label: 'Average Sales (RM)',
                data: {!! json_encode($dayOfWeekData ?? [0,0,0,0,0,0,0]) !!},
                backgroundColor: [
                    '#dc3545', '#fd7e14', '#ffc107', '#198754', '#20c997', '#0dcaf0', '#6f42c1'
                ],
                borderRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Average Sales (RM)'
                    }
                }
            }
        }
    });
});
</script>
@endpush
