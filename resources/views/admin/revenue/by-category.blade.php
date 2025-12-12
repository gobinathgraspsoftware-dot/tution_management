@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-chart-pie"></i> Revenue by Category</h2>
        <div>
            <a href="{{ route('admin.revenue.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Revenue
            </a>
            @can('export-financial-reports')
            <a href="{{ route('admin.revenue.export', request()->all()) }}" class="btn btn-success">
                <i class="fas fa-file-export"></i> Export CSV
            </a>
            @endcan
        </div>
    </div>

    {{-- Date Filter --}}
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-calendar"></i> Select Period
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.revenue.by-category') }}">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label>From Date</label>
                        <input type="date" name="date_from" class="form-control" value="{{ request('date_from', $startDate->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>To Date</label>
                        <input type="date" name="date_to" class="form-control" value="{{ request('date_to', $endDate->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>&nbsp;</label>
                        <div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Filter
                            </button>
                            <a href="{{ route('admin.revenue.by-category') }}" class="btn btn-secondary">
                                <i class="fas fa-redo"></i> Reset
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Total Revenue Card --}}
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="mb-0">Total Revenue</h4>
                            <p class="mb-0">
                                <small>Period: {{ $startDate->format('d M Y') }} - {{ $endDate->format('d M Y') }}</small>
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <h1 class="mb-0">RM {{ number_format($totalRevenue['total'], 2) }}</h1>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Category Breakdown Chart --}}
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-chart-pie"></i> Revenue Distribution by Category
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <canvas id="categoryPieChart"></canvas>
                        </div>
                        <div class="col-md-6">
                            <canvas id="categoryBarChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Category Breakdown Table --}}
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-table"></i> Detailed Category Breakdown
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="35%">Revenue Category</th>
                                    <th width="20%" class="text-end">Amount (RM)</th>
                                    <th width="15%" class="text-center">Percentage</th>
                                    <th width="25%">
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-success" style="width: 100%;">Contribution</div>
                                        </div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $total = array_sum($byCategory);
                                    $rank = 1;
                                @endphp
                                @foreach($byCategory as $category => $amount)
                                @php
                                    $percentage = $total > 0 ? ($amount / $total) * 100 : 0;
                                @endphp
                                <tr>
                                    <td>{{ $rank++ }}</td>
                                    <td>
                                        <strong>{{ $category }}</strong>
                                        @if($rank == 2)
                                        <span class="badge bg-warning text-dark">
                                            <i class="fas fa-crown"></i> Top Revenue Source
                                        </span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <strong>{{ number_format($amount, 2) }}</strong>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-info">{{ number_format($percentage, 1) }}%</span>
                                    </td>
                                    <td>
                                        <div class="progress" style="height: 25px;">
                                            <div class="progress-bar
                                                @if($percentage >= 40) bg-success
                                                @elseif($percentage >= 20) bg-info
                                                @elseif($percentage >= 10) bg-warning
                                                @else bg-secondary
                                                @endif"
                                                style="width: {{ $percentage }}%;">
                                                {{ number_format($percentage, 1) }}%
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-success">
                                <tr>
                                    <th colspan="2" class="text-end">TOTAL REVENUE:</th>
                                    <th class="text-end">{{ number_format($total, 2) }}</th>
                                    <th class="text-center">100%</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Top Revenue Sources --}}
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-warning">
                    <h5 class="mb-0"><i class="fas fa-trophy"></i> Top 5 Revenue Sources</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($topSources->take(5) as $index => $amount)
                        @php
                            $categoryName = $index;
                            $percentage = $total > 0 ? ($amount / $total) * 100 : 0;
                        @endphp
                        <div class="col-md-{{ $loop->iteration <= 3 ? '4' : '6' }} mb-3">
                            <div class="card {{ $loop->iteration == 1 ? 'border-warning' : '' }}">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-0">
                                                @if($loop->iteration == 1)
                                                <i class="fas fa-crown text-warning"></i>
                                                @elseif($loop->iteration == 2)
                                                <i class="fas fa-medal text-secondary"></i>
                                                @elseif($loop->iteration == 3)
                                                <i class="fas fa-medal text-bronze"></i>
                                                @else
                                                <i class="fas fa-star text-muted"></i>
                                                @endif
                                                {{ $categoryName }}
                                            </h6>
                                            <h4 class="mb-0 text-success">RM {{ number_format($amount, 2) }}</h4>
                                            <small class="text-muted">{{ number_format($percentage, 1) }}% of total revenue</small>
                                        </div>
                                        <div class="fs-1 text-muted">
                                            #{{ $loop->iteration }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const categoryData = @json($byCategory);
const labels = Object.keys(categoryData);
const values = Object.values(categoryData);

// Pie Chart
const pieCtx = document.getElementById('categoryPieChart').getContext('2d');
new Chart(pieCtx, {
    type: 'pie',
    data: {
        labels: labels,
        datasets: [{
            data: values,
            backgroundColor: [
                'rgba(255, 99, 132, 0.8)',
                'rgba(54, 162, 235, 0.8)',
                'rgba(255, 206, 86, 0.8)',
                'rgba(75, 192, 192, 0.8)',
                'rgba(153, 102, 255, 0.8)',
                'rgba(255, 159, 64, 0.8)'
            ],
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'bottom'
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        const label = context.label || '';
                        const value = context.parsed || 0;
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = ((value / total) * 100).toFixed(1);
                        return label + ': RM ' + value.toLocaleString() + ' (' + percentage + '%)';
                    }
                }
            }
        }
    }
});

// Bar Chart
const barCtx = document.getElementById('categoryBarChart').getContext('2d');
new Chart(barCtx, {
    type: 'bar',
    data: {
        labels: labels,
        datasets: [{
            label: 'Revenue (RM)',
            data: values,
            backgroundColor: 'rgba(75, 192, 192, 0.8)',
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 1
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
</script>
@endsection
