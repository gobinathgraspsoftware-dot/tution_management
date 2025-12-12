@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-dollar-sign"></i> Revenue Tracking</h2>
        <div>
            <a href="{{ route('admin.revenue.by-category') }}" class="btn btn-info">
                <i class="fas fa-chart-pie"></i> View by Category
            </a>
            @can('export-financial-reports')
            <a href="{{ route('admin.revenue.export', request()->all()) }}" class="btn btn-success">
                <i class="fas fa-file-export"></i> Export CSV
            </a>
            @endcan
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6 class="card-title">Total Revenue</h6>
                    <h3>RM {{ number_format($summary['total'], 2) }}</h3>
                    @if($comparison['revenue']['change_percentage'] != 0)
                    <small>
                        @if($comparison['revenue']['change_percentage'] > 0)
                        <i class="fas fa-arrow-up"></i> +{{ number_format($comparison['revenue']['change_percentage'], 1) }}%
                        @else
                        <i class="fas fa-arrow-down"></i> {{ number_format($comparison['revenue']['change_percentage'], 1) }}%
                        @endif
                        vs previous period
                    </small>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6 class="card-title">Student Fees</h6>
                    <h3>RM {{ number_format($summary['student_fees']['total'], 2) }}</h3>
                    <small>Online: RM {{ number_format($summary['student_fees']['online'], 2) }}</small><br>
                    <small>Physical: RM {{ number_format($summary['student_fees']['physical'], 2) }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h6 class="card-title">Seminar Revenue</h6>
                    <h3>RM {{ number_format($summary['seminar_revenue'], 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h6 class="card-title">POS Sales</h6>
                    <h3>RM {{ number_format($summary['pos_revenue'], 2) }}</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-filter"></i> Filters
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.revenue.index') }}">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label>From Date</label>
                        <input type="date" name="date_from" class="form-control" value="{{ request('date_from', $startDate->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label>To Date</label>
                        <input type="date" name="date_to" class="form-control" value="{{ request('date_to', $endDate->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label>Revenue Source</label>
                        <select name="revenue_source" class="form-select">
                            <option value="">All Sources</option>
                            <option value="student_fees_online" {{ request('revenue_source') == 'student_fees_online' ? 'selected' : '' }}>Student Fees (Online)</option>
                            <option value="student_fees_physical" {{ request('revenue_source') == 'student_fees_physical' ? 'selected' : '' }}>Student Fees (Physical)</option>
                            <option value="seminar_revenue" {{ request('revenue_source') == 'seminar_revenue' ? 'selected' : '' }}>Seminar Revenue</option>
                            <option value="pos_sales" {{ request('revenue_source') == 'pos_sales' ? 'selected' : '' }}>POS Sales</option>
                            <option value="material_sales" {{ request('revenue_source') == 'material_sales' ? 'selected' : '' }}>Material Sales</option>
                            <option value="other" {{ request('revenue_source') == 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label>Payment Method</label>
                        <select name="payment_method" class="form-select">
                            <option value="">All Methods</option>
                            <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="qr" {{ request('payment_method') == 'qr' ? 'selected' : '' }}>QR</option>
                            <option value="online_gateway" {{ request('payment_method') == 'online_gateway' ? 'selected' : '' }}>Online Gateway</option>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Filter
                        </button>
                        <a href="{{ route('admin.revenue.index') }}" class="btn btn-secondary">
                            <i class="fas fa-redo"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Revenue Trends Chart --}}
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-chart-line"></i> Revenue Trends
                </div>
                <div class="card-body">
                    <canvas id="revenueTrendsChart" height="80"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Revenue Breakdown --}}
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-chart-pie"></i> Revenue by Category
                </div>
                <div class="card-body">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-chart-bar"></i> Revenue by Payment Method
                </div>
                <div class="card-body">
                    <canvas id="paymentMethodChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Revenue Transactions Table --}}
    <div class="card">
        <div class="card-header">
            <i class="fas fa-list"></i> Revenue Transactions ({{ $revenues->count() }} records)
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Payment #</th>
                            <th>Date</th>
                            <th>Student</th>
                            <th>Amount</th>
                            <th>Revenue Source</th>
                            <th>Payment Method</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($revenues as $revenue)
                        <tr>
                            <td>{{ $revenue->payment_number }}</td>
                            <td>{{ $revenue->payment_date->format('d M Y') }}</td>
                            <td>{{ $revenue->student->user->name ?? 'N/A' }}</td>
                            <td><strong>RM {{ number_format($revenue->amount, 2) }}</strong></td>
                            <td>
                                <span class="badge bg-info">
                                    {{ $revenue->getRevenueSourceLabel() }}
                                </span>
                            </td>
                            <td>{{ ucfirst($revenue->payment_method) }}</td>
                            <td>
                                <span class="badge bg-success">{{ ucfirst($revenue->status) }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">No revenue transactions found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($revenues->count() > 0)
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="3" class="text-end">TOTAL:</th>
                            <th colspan="4"><strong>RM {{ number_format($revenues->sum('amount'), 2) }}</strong></th>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Revenue Trends Chart
const trendsData = @json($trends);
const trendsCtx = document.getElementById('revenueTrendsChart').getContext('2d');
new Chart(trendsCtx, {
    type: 'line',
    data: {
        labels: trendsData.map(d => d.date),
        datasets: [{
            label: 'Revenue',
            data: trendsData.map(d => d.total),
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.1,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: false
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

// Revenue by Category Pie Chart
const categoryData = @json($byCategory);
const categoryCtx = document.getElementById('categoryChart').getContext('2d');
new Chart(categoryCtx, {
    type: 'pie',
    data: {
        labels: Object.keys(categoryData),
        datasets: [{
            data: Object.values(categoryData),
            backgroundColor: [
                'rgba(255, 99, 132, 0.8)',
                'rgba(54, 162, 235, 0.8)',
                'rgba(255, 206, 86, 0.8)',
                'rgba(75, 192, 192, 0.8)',
                'rgba(153, 102, 255, 0.8)',
                'rgba(255, 159, 64, 0.8)'
            ]
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Revenue by Payment Method Chart
const paymentMethodData = @json($byPaymentMethod);
const paymentCtx = document.getElementById('paymentMethodChart').getContext('2d');
new Chart(paymentCtx, {
    type: 'bar',
    data: {
        labels: paymentMethodData.map(p => p.method.toUpperCase()),
        datasets: [{
            label: 'Revenue',
            data: paymentMethodData.map(p => p.total),
            backgroundColor: 'rgba(54, 162, 235, 0.8)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false
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
</script>
@endsection
