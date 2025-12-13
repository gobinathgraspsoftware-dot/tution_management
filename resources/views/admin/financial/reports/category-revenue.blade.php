@extends('layouts.app')

@section('title', 'Category Revenue Report')
@section('page-title', 'Category Revenue Report')

@push('styles')
<style>
    .chart-container { position: relative; height: 350px; margin-bottom: 20px; }
</style>
@endpush

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-chart-pie me-2"></i> Category Revenue Analysis</h1>
        <div class="btn-group">
            <a href="{{ route('admin.financial.reports') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i> Back to Reports
            </a>
        </div>
    </div>
</div>

<!-- Period Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Start Date</label>
                <input type="date" name="date_from" class="form-control" value="{{ $startDate->format('Y-m-d') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">End Date</label>
                <input type="date" name="date_to" class="form-control" value="{{ $endDate->format('Y-m-d') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">&nbsp;</label>
                <button type="submit" class="btn btn-primary w-100">Generate Report</button>
            </div>
        </form>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    @foreach($revenueBreakdown as $key => $data)
        @if($key !== 'total')
        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">{{ ucwords(str_replace('_', ' ', $key)) }}</h6>
                    <h3>RM {{ number_format($data['amount'], 2) }}</h3>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar" style="width: {{ $data['percentage'] }}%"></div>
                    </div>
                    <small class="text-muted">{{ number_format($data['percentage'], 2) }}% of total</small>
                </div>
            </div>
        </div>
        @endif
    @endforeach
</div>

<!-- Chart -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Revenue Distribution by Category</h5>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Detailed Table -->
<div class="card">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">Detailed Revenue Breakdown</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th class="text-end">Amount (RM)</th>
                        <th class="text-center">Percentage</th>
                        <th class="text-center">Trend</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($revenueBreakdown as $key => $data)
                        @if($key !== 'total')
                        <tr>
                            <td>{{ ucwords(str_replace('_', ' ', $key)) }}</td>
                            <td class="text-end">{{ number_format($data['amount'], 2) }}</td>
                            <td class="text-center">
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar" style="width: {{ $data['percentage'] }}%">
                                        {{ number_format($data['percentage'], 2) }}%
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-success"><i class="fas fa-arrow-up"></i></span>
                            </td>
                        </tr>
                        @endif
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="fw-bold">
                        <td>TOTAL REVENUE</td>
                        <td class="text-end">{{ number_format($revenueBreakdown['total'], 2) }}</td>
                        <td class="text-center">100.00%</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<!-- Export Buttons -->
<div class="card mt-4">
    <div class="card-body text-center">
        <div class="btn-group">
            <a href="{{ route('admin.financial.export.category-revenue', ['date_from' => $startDate->format('Y-m-d'), 'date_to' => $endDate->format('Y-m-d')]) }}"
               class="btn btn-success">
                <i class="fas fa-file-excel me-2"></i> Download Excel
            </a>
            <a href="{{ route('admin.financial.download.category-revenue-pdf', ['date_from' => $startDate->format('Y-m-d'), 'date_to' => $endDate->format('Y-m-d')]) }}"
               class="btn btn-danger">
                <i class="fas fa-file-pdf me-2"></i> Download PDF
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
$(document).ready(function() {
    const ctx = document.getElementById('categoryChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: {!! json_encode(array_map(function($key) { return ucwords(str_replace('_', ' ', $key)); }, array_keys(array_filter($revenueBreakdown, function($key) { return $key !== 'total'; }, ARRAY_FILTER_USE_KEY)))) !!},
            datasets: [{
                label: 'Revenue (RM)',
                data: {!! json_encode(array_values(array_map(function($data) { return $data['amount']; }, array_filter($revenueBreakdown, function($key) { return $key !== 'total'; }, ARRAY_FILTER_USE_KEY)))) !!},
                backgroundColor: ['#4caf50', '#2196f3', '#ff9800', '#9c27b0', '#f44336', '#00bcd4']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true } }
        }
    });
});
</script>
@endpush
