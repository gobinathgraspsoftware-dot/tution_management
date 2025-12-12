@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-chart-line"></i> Financial Dashboard</h2>
        <div>
            <select class="form-select" id="periodSelector" onchange="changePeriod()">
                <option value="today" {{ $period == 'today' ? 'selected' : '' }}>Today</option>
                <option value="this_week" {{ $period == 'this_week' ? 'selected' : '' }}>This Week</option>
                <option value="this_month" {{ $period == 'this_month' ? 'selected' : '' }}>This Month</option>
                <option value="this_year" {{ $period == 'this_year' ? 'selected' : '' }}>This Year</option>
            </select>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row mb-4">
        @foreach($summaryCards as $card)
        <div class="col-md-3 mb-3">
            <div class="card text-white bg-{{ $card['color'] }}">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-2">{{ $card['title'] }}</h6>
                            <h3 class="mb-0">
                                @if(is_numeric($card['value']))
                                RM {{ number_format($card['value'], 2) }}
                                @else
                                {{ $card['value'] }}
                                @endif
                            </h3>
                            @if($card['trend'] !== null)
                            <small>
                                @if($card['trend'] > 0)
                                <i class="fas fa-arrow-up"></i> +{{ number_format($card['trend'], 1) }}%
                                @elseif($card['trend'] < 0)
                                <i class="fas fa-arrow-down"></i> {{ number_format($card['trend'], 1) }}%
                                @else
                                <i class="fas fa-minus"></i> 0%
                                @endif
                                vs previous
                            </small>
                            @endif
                        </div>
                        <div>
                            <i class="fas {{ $card['icon'] }} fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Financial Health Score --}}
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-heartbeat"></i> Financial Health Score
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-3 text-center">
                            <h1 class="display-1
                                @if($healthScore['score'] >= 80) text-success
                                @elseif($healthScore['score'] >= 60) text-warning
                                @else text-danger
                                @endif">
                                {{ $healthScore['score'] }}
                            </h1>
                            <h4>Grade: <strong>{{ $healthScore['grade'] }}</strong></h4>
                        </div>
                        <div class="col-md-9">
                            <div class="mb-3">
                                <label>Profit Margin: {{ number_format($healthScore['factors']['profit_margin'], 1) }}%</label>
                                <div class="progress">
                                    <div class="progress-bar bg-success" style="width: {{ min($healthScore['factors']['profit_margin'] * 2, 100) }}%"></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label>Revenue Growth: {{ number_format($healthScore['factors']['revenue_growth'], 1) }}%</label>
                                <div class="progress">
                                    <div class="progress-bar bg-info" style="width: {{ abs($healthScore['factors']['revenue_growth']) }}%"></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label>Expense Control: {{ number_format($healthScore['factors']['expense_control'], 1) }}%</label>
                                <div class="progress">
                                    <div class="progress-bar {{ $healthScore['factors']['expense_control'] <= 10 ? 'bg-success' : 'bg-warning' }}"
                                         style="width: {{ min($healthScore['factors']['expense_control'], 100) }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Revenue vs Expense Trend Chart --}}
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-chart-area"></i> Revenue vs Expense Trends
                </div>
                <div class="card-body">
                    <canvas id="trendsChart" height="80"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Revenue and Expense Breakdown --}}
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-chart-pie"></i> Revenue by Category
                </div>
                <div class="card-body">
                    <canvas id="revenueCategoryChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-chart-pie"></i> Expense by Category
                </div>
                <div class="card-body">
                    <canvas id="expenseCategoryChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Detailed Breakdown --}}
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <i class="fas fa-dollar-sign"></i> Revenue Breakdown
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        @foreach($data['revenue']['by_category'] as $category => $amount)
                        <tr>
                            <td>{{ $category }}</td>
                            <td class="text-end"><strong>RM {{ number_format($amount, 2) }}</strong></td>
                        </tr>
                        @endforeach
                        <tr class="table-success">
                            <td><strong>TOTAL REVENUE</strong></td>
                            <td class="text-end"><strong>RM {{ number_format($data['revenue']['total'], 2) }}</strong></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <i class="fas fa-receipt"></i> Expense Breakdown
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        @foreach($data['expenses']['by_category'] as $expense)
                        <tr>
                            <td>{{ $expense->name }}</td>
                            <td class="text-end"><strong>RM {{ number_format($expense->total, 2) }}</strong></td>
                        </tr>
                        @endforeach
                        <tr class="table-danger">
                            <td><strong>TOTAL EXPENSES</strong></td>
                            <td class="text-end"><strong>RM {{ number_format($data['expenses']['total'], 2) }}</strong></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Change period
function changePeriod() {
    const period = document.getElementById('periodSelector').value;
    window.location.href = '{{ route("admin.financial.dashboard") }}?period=' + period;
}

// Revenue vs Expense Trends Chart
const trendsData = @json($data['trends']);
const trendsCtx = document.getElementById('trendsChart').getContext('2d');
new Chart(trendsCtx, {
    type: 'line',
    data: {
        labels: trendsData.map(d => d.date),
        datasets: [{
            label: 'Revenue',
            data: trendsData.map(d => d.revenue),
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.1
        }, {
            label: 'Expense',
            data: trendsData.map(d => d.expense),
            borderColor: 'rgb(255, 99, 132)',
            backgroundColor: 'rgba(255, 99, 132, 0.2)',
            tension: 0.1
        }, {
            label: 'Profit',
            data: trendsData.map(d => d.profit),
            borderColor: 'rgb(54, 162, 235)',
            backgroundColor: 'rgba(54, 162, 235, 0.2)',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
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

// Revenue Category Pie Chart
const revenueCategoryData = @json($data['revenue']['by_category']);
const revenueCtx = document.getElementById('revenueCategoryChart').getContext('2d');
new Chart(revenueCtx, {
    type: 'pie',
    data: {
        labels: Object.keys(revenueCategoryData),
        datasets: [{
            data: Object.values(revenueCategoryData),
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
                position: 'bottom',
            }
        }
    }
});

// Expense Category Pie Chart
const expenseCategoryData = @json($data['expenses']['by_category']);
const expenseCtx = document.getElementById('expenseCategoryChart').getContext('2d');
new Chart(expenseCtx, {
    type: 'pie',
    data: {
        labels: expenseCategoryData.map(e => e.name),
        datasets: [{
            data: expenseCategoryData.map(e => e.total),
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
                position: 'bottom',
            }
        }
    }
});
</script>
@endsection
