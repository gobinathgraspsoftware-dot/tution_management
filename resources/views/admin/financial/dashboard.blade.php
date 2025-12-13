@extends('layouts.app')

@section('title', 'Financial Dashboard')
@section('page-title', 'Financial Dashboard')

@push('styles')
<style>
    .chart-container {
        position: relative;
        height: 300px;
        margin-bottom: 20px;
    }
    .stat-card {
        transition: transform 0.3s;
    }
    .stat-card:hover {
        transform: translateY(-5px);
    }
    .health-badge {
        font-size: 2rem;
        font-weight: 700;
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-chart-line me-2"></i> Financial Dashboard</h1>
        <div class="btn-group">
            <a href="{{ route('admin.financial.reports') }}" class="btn btn-primary">
                <i class="fas fa-file-alt me-2"></i> View Reports
            </a>
        </div>
    </div>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Financial Dashboard</li>
        </ol>
    </nav>
</div>

<!-- Period Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.financial.dashboard') }}" class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Period</label>
                <select name="period" class="form-select" onchange="this.form.submit()">
                    <option value="today" {{ $period === 'today' ? 'selected' : '' }}>Today</option>
                    <option value="this_week" {{ $period === 'this_week' ? 'selected' : '' }}>This Week</option>
                    <option value="this_month" {{ $period === 'this_month' ? 'selected' : '' }}>This Month</option>
                    <option value="this_year" {{ $period === 'this_year' ? 'selected' : '' }}>This Year</option>
                    <option value="last_month" {{ $period === 'last_month' ? 'selected' : '' }}>Last Month</option>
                    <option value="last_year" {{ $period === 'last_year' ? 'selected' : '' }}>Last Year</option>
                </select>
            </div>
        </form>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    @foreach($summaryCards as $card)
    <div class="col-md-3 mb-3">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: {{ $card['color'] === 'success' ? '#e8f5e9' : ($card['color'] === 'danger' ? '#ffebee' : '#e3f2fd') }}; color: {{ $card['color'] === 'success' ? '#4caf50' : ($card['color'] === 'danger' ? '#f44336' : '#2196f3') }};">
                <i class="fas {{ $card['icon'] }}"></i>
            </div>
            <div class="stat-details">
                <h6 class="text-muted mb-1">{{ $card['title'] }}</h6>
                <h3 class="mb-0">
                    @if(is_numeric($card['value']))
                        RM {{ number_format($card['value'], 2) }}
                    @else
                        {{ $card['value'] }}
                    @endif
                </h3>
                @if($card['trend'] !== null)
                <small class="text-{{ $card['trend'] >= 0 ? 'success' : 'danger' }}">
                    <i class="fas fa-{{ $card['trend'] >= 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                    {{ number_format(abs($card['trend']), 2) }}% from previous period
                </small>
                @endif
            </div>
        </div>
    </div>
    @endforeach
</div>

<!-- Financial Health Score -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-heartbeat me-2"></i> Financial Health</h5>
            </div>
            <div class="card-body text-center">
                <div class="health-badge text-{{ $healthScore['score'] >= 70 ? 'success' : ($healthScore['score'] >= 50 ? 'warning' : 'danger') }}">
                    {{ $healthScore['grade'] }}
                </div>
                <h4 class="mt-2">Score: {{ $healthScore['score'] }}/100</h4>
                <div class="progress" style="height: 25px;">
                    <div class="progress-bar bg-{{ $healthScore['score'] >= 70 ? 'success' : ($healthScore['score'] >= 50 ? 'warning' : 'danger') }}"
                         role="progressbar"
                         style="width: {{ $healthScore['score'] }}%">
                        {{ $healthScore['score'] }}%
                    </div>
                </div>
                <div class="mt-3">
                    <small class="text-muted">Profit Margin: {{ number_format($healthScore['factors']['profit_margin'], 2) }}%</small><br>
                    <small class="text-muted">Revenue Growth: {{ number_format($healthScore['factors']['revenue_growth'], 2) }}%</small><br>
                    <small class="text-muted">Expense Control: {{ number_format($healthScore['factors']['expense_control'], 2) }}%</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-area me-2"></i> Revenue vs Expenses Trend</h5>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Revenue and Expense Breakdown -->
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i> Revenue Breakdown</h5>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="revenueChart"></canvas>
                </div>
                <div class="mt-3">
                    <table class="table table-sm">
                        <tbody>
                            @foreach($data['revenue']['by_category'] as $category => $amount)
                            <tr>
                                <td>{{ ucwords(str_replace('_', ' ', $category)) }}</td>
                                <td class="text-end"><strong>RM {{ number_format($amount, 2) }}</strong></td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="fw-bold">
                                <td>Total Revenue</td>
                                <td class="text-end">RM {{ number_format($data['revenue']['total'], 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-receipt me-2"></i> Expense Breakdown</h5>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="expenseChart"></canvas>
                </div>
                <div class="mt-3">
                    <table class="table table-sm">
                        <tbody>
                            @foreach($data['expenses']['by_category'] as $expense)
                            <tr>
                                <td>{{ $expense->name }}</td>
                                <td class="text-end"><strong>RM {{ number_format($expense->total, 2) }}</strong></td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="fw-bold">
                                <td>Total Expenses</td>
                                <td class="text-end">RM {{ number_format($data['expenses']['total'], 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Profit Summary -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-{{ $data['profit']['status'] === 'profit' ? 'success' : ($data['profit']['status'] === 'loss' ? 'danger' : 'warning') }} text-white">
                <h5 class="mb-0">
                    <i class="fas fa-chart-line me-2"></i>
                    Profit Summary - {{ ucfirst($data['profit']['status']) }}
                </h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-4">
                        <h6 class="text-muted">Net Profit/Loss</h6>
                        <h3 class="text-{{ $data['profit']['net_profit'] >= 0 ? 'success' : 'danger' }}">
                            RM {{ number_format($data['profit']['net_profit'], 2) }}
                        </h3>
                    </div>
                    <div class="col-md-4">
                        <h6 class="text-muted">Profit Margin</h6>
                        <h3 class="text-{{ $data['profit']['profit_margin'] >= 20 ? 'success' : ($data['profit']['profit_margin'] >= 10 ? 'warning' : 'danger') }}">
                            {{ number_format($data['profit']['profit_margin'], 2) }}%
                        </h3>
                    </div>
                    <div class="col-md-4">
                        <h6 class="text-muted">Period</h6>
                        <h3>{{ $data['date_range']['start'] }} - {{ $data['date_range']['end'] }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<script>
$(document).ready(function() {
    // Trend Chart
    const trendCtx = document.getElementById('trendChart').getContext('2d');
    const trendChart = new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($data['trends']->pluck('date')->toArray()) !!},
            datasets: [
                {
                    label: 'Revenue',
                    data: {!! json_encode($data['trends']->pluck('revenue')->toArray()) !!},
                    borderColor: '#4caf50',
                    backgroundColor: 'rgba(76, 175, 80, 0.1)',
                    tension: 0.4
                },
                {
                    label: 'Expenses',
                    data: {!! json_encode($data['trends']->pluck('expense')->toArray()) !!},
                    borderColor: '#f44336',
                    backgroundColor: 'rgba(244, 67, 54, 0.1)',
                    tension: 0.4
                },
                {
                    label: 'Profit',
                    data: {!! json_encode($data['trends']->pluck('profit')->toArray()) !!},
                    borderColor: '#2196f3',
                    backgroundColor: 'rgba(33, 150, 243, 0.1)',
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'RM ' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // Revenue Pie Chart
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    const revenueChart = new Chart(revenueCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode(array_map(function($key) { return ucwords(str_replace('_', ' ', $key)); }, array_keys($data['revenue']['by_category']))) !!},
            datasets: [{
                data: {!! json_encode(array_values($data['revenue']['by_category'])) !!},
                backgroundColor: [
                    '#4caf50',
                    '#2196f3',
                    '#ff9800',
                    '#9c27b0',
                    '#f44336',
                    '#00bcd4'
                ]
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

    // Expense Pie Chart
    const expenseCtx = document.getElementById('expenseChart').getContext('2d');
    const expenseChart = new Chart(expenseCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($data['expenses']['by_category']->pluck('name')->toArray()) !!},
            datasets: [{
                data: {!! json_encode($data['expenses']['by_category']->pluck('total')->toArray()) !!},
                backgroundColor: [
                    '#f44336',
                    '#e91e63',
                    '#9c27b0',
                    '#673ab7',
                    '#3f51b5',
                    '#2196f3',
                    '#03a9f4',
                    '#00bcd4'
                ]
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
});
</script>
@endpush
