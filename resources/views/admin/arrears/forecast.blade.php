@extends('layouts.app')

@section('title', 'Collection Forecast')
@section('page-title', 'Collection Forecast')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.arrears.index') }}">Arrears</a></li>
            <li class="breadcrumb-item active">Collection Forecast</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="fas fa-chart-line me-2"></i> Collection Forecast</h4>
            <p class="text-muted mb-0">Expected collections for the next {{ $months }} months</p>
        </div>
        <div>
            <form method="GET" action="{{ route('admin.arrears.forecast') }}" class="d-inline">
                <div class="input-group">
                    <select name="months" class="form-select" onchange="this.form.submit()">
                        <option value="3" {{ $months == 3 ? 'selected' : '' }}>3 Months</option>
                        <option value="6" {{ $months == 6 ? 'selected' : '' }}>6 Months</option>
                        <option value="12" {{ $months == 12 ? 'selected' : '' }}>12 Months</option>
                    </select>
                </div>
            </form>
            <a href="{{ route('admin.arrears.index') }}" class="btn btn-outline-secondary ms-2">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <!-- Total Expected Collections -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-money-bill-wave fa-2x opacity-75"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0">RM {{ number_format(collect($forecast)->sum('expected_collections'), 2) }}</h3>
                            <small>Total Expected Collections</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-info text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-file-invoice fa-2x opacity-75"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0">{{ collect($forecast)->sum('invoice_count') }}</h3>
                            <small>Total Invoices</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-warning text-dark">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-calendar-check fa-2x opacity-75"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0">{{ collect($forecast)->sum('installment_count') }}</h3>
                            <small>Total Installments</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Forecast Chart -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-chart-area me-2"></i> Monthly Collection Forecast</h5>
        </div>
        <div class="card-body">
            <canvas id="forecastChart" height="100"></canvas>
        </div>
    </div>

    <!-- Monthly Breakdown Cards -->
    <div class="row mb-4">
        @foreach($forecast as $index => $monthData)
            @php
                $bgClass = $index === 0 ? 'bg-primary' : ($index === 1 ? 'bg-info' : 'bg-secondary');
            @endphp
            <div class="col-md-4 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header {{ $bgClass }} text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-calendar-alt me-2"></i>
                            {{ $monthData['month'] }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <h3 class="text-success mb-3">RM {{ number_format($monthData['expected_collections'], 2) }}</h3>

                        <div class="row text-center">
                            <div class="col-6">
                                <div class="border-end">
                                    <h4 class="mb-0">{{ $monthData['invoice_count'] }}</h4>
                                    <small class="text-muted">Invoices</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <h4 class="mb-0">{{ $monthData['installment_count'] }}</h4>
                                <small class="text-muted">Installments</small>
                            </div>
                        </div>

                        <hr>

                        <div class="progress" style="height: 10px;">
                            @php
                                $maxCollection = collect($forecast)->max('expected_collections');
                                $percentage = $maxCollection > 0 ? ($monthData['expected_collections'] / $maxCollection) * 100 : 0;
                            @endphp
                            <div class="progress-bar bg-success" role="progressbar"
                                 style="width: {{ $percentage }}%"
                                 aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <small class="text-muted">{{ number_format($percentage, 1) }}% of max month</small>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Detailed Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-table me-2"></i> Forecast Details</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Month</th>
                            <th>Expected Collections</th>
                            <th>Invoices Due</th>
                            <th>Installments Due</th>
                            <th>Avg. per Invoice</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($forecast as $monthData)
                            <tr>
                                <td><strong>{{ $monthData['month'] }}</strong></td>
                                <td><strong class="text-success">RM {{ number_format($monthData['expected_collections'], 2) }}</strong></td>
                                <td>
                                    <span class="badge bg-info">{{ $monthData['invoice_count'] }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-warning text-dark">{{ $monthData['installment_count'] }}</span>
                                </td>
                                <td>
                                    @php
                                        $totalItems = $monthData['invoice_count'] + $monthData['installment_count'];
                                        $avgPerItem = $totalItems > 0 ? $monthData['expected_collections'] / $totalItems : 0;
                                    @endphp
                                    RM {{ number_format($avgPerItem, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th>Total</th>
                            <th class="text-success">RM {{ number_format(collect($forecast)->sum('expected_collections'), 2) }}</th>
                            <th>{{ collect($forecast)->sum('invoice_count') }}</th>
                            <th>{{ collect($forecast)->sum('installment_count') }}</th>
                            <th>-</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Disclaimer -->
    <div class="alert alert-info mt-4">
        <i class="fas fa-info-circle me-2"></i>
        <strong>Note:</strong> This forecast is based on scheduled invoice and installment due dates.
        Actual collections may vary based on payment behavior, early payments, or late payments.
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var ctx = document.getElementById('forecastChart').getContext('2d');

    var months = {!! json_encode(collect($forecast)->pluck('month')) !!};
    var collections = {!! json_encode(collect($forecast)->pluck('expected_collections')) !!};
    var invoices = {!! json_encode(collect($forecast)->pluck('invoice_count')) !!};
    var installments = {!! json_encode(collect($forecast)->pluck('installment_count')) !!};

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: months,
            datasets: [
                {
                    type: 'line',
                    label: 'Expected Collections (RM)',
                    data: collections,
                    borderColor: 'rgba(40, 167, 69, 1)',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    fill: true,
                    tension: 0.4,
                    yAxisID: 'y'
                },
                {
                    type: 'bar',
                    label: 'Invoices',
                    data: invoices,
                    backgroundColor: 'rgba(23, 162, 184, 0.7)',
                    yAxisID: 'y1'
                },
                {
                    type: 'bar',
                    label: 'Installments',
                    data: installments,
                    backgroundColor: 'rgba(255, 193, 7, 0.7)',
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            interaction: {
                mode: 'index',
                intersect: false
            },
            plugins: {
                legend: {
                    position: 'top'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            if (context.dataset.label.includes('RM')) {
                                return context.dataset.label + ': RM ' + context.raw.toLocaleString();
                            }
                            return context.dataset.label + ': ' + context.raw;
                        }
                    }
                }
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Amount (RM)'
                    },
                    ticks: {
                        callback: function(value) {
                            return 'RM ' + value.toLocaleString();
                        }
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Count'
                    },
                    grid: {
                        drawOnChartArea: false
                    }
                }
            }
        }
    });
});
</script>
@endpush
@endsection
