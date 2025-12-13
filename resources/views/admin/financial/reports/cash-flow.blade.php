@extends('layouts.app')

@section('title', 'Cash Flow Analysis')
@section('page-title', 'Cash Flow Analysis')

@push('styles')
<style>
    .chart-container { position: relative; height: 350px; margin-bottom: 20px; }
    .cash-flow-summary { font-size: 1.2em; font-weight: 600; padding: 20px; border-radius: 8px; }
</style>
@endpush

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-exchange-alt me-2"></i> Cash Flow Analysis</h1>
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

<!-- Summary -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card border-success">
            <div class="card-body text-center">
                <h6 class="text-muted">Total Cash Inflow</h6>
                <h2 class="text-success">RM {{ number_format($cashFlowData['total_inflow'], 2) }}</h2>
                <small>Revenue Received</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-danger">
            <div class="card-body text-center">
                <h6 class="text-muted">Total Cash Outflow</h6>
                <h2 class="text-danger">RM {{ number_format($cashFlowData['total_outflow'], 2) }}</h2>
                <small>Expenses Paid</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-{{ $cashFlowData['net_cash_flow'] >= 0 ? 'success' : 'danger' }}">
            <div class="card-body text-center">
                <h6 class="text-muted">Net Cash Flow</h6>
                <h2 class="text-{{ $cashFlowData['net_cash_flow'] >= 0 ? 'success' : 'danger' }}">
                    RM {{ number_format($cashFlowData['net_cash_flow'], 2) }}
                </h2>
                <small class="text-uppercase">{{ $cashFlowData['cash_flow_status'] }}</small>
            </div>
        </div>
    </div>
</div>

<!-- Chart -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Daily Cash Flow Trend</h5>
    </div>
    <div class="card-body">
        <div class="chart-container">
            <canvas id="cashFlowChart"></canvas>
        </div>
    </div>
</div>

<!-- Daily Breakdown Table -->
<div class="card">
    <div class="card-header bg-info text-white">
        <h5 class="mb-0">Daily Cash Flow Breakdown</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th class="text-end">Cash Inflow</th>
                        <th class="text-end">Cash Outflow</th>
                        <th class="text-end">Net Cash Flow</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cashFlowData['daily_breakdown'] as $day)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($day['date'])->format('d M Y') }}</td>
                        <td class="text-end text-success">RM {{ number_format($day['revenue'], 2) }}</td>
                        <td class="text-end text-danger">RM {{ number_format($day['expense'], 2) }}</td>
                        <td class="text-end fw-bold text-{{ $day['profit'] >= 0 ? 'success' : 'danger' }}">
                            RM {{ number_format($day['profit'], 2) }}
                        </td>
                        <td class="text-center">
                            <span class="badge bg-{{ $day['profit'] >= 0 ? 'success' : 'danger' }}">
                                {{ $day['profit'] >= 0 ? 'Positive' : 'Negative' }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="fw-bold">
                        <td>TOTAL</td>
                        <td class="text-end text-success">RM {{ number_format($cashFlowData['total_inflow'], 2) }}</td>
                        <td class="text-end text-danger">RM {{ number_format($cashFlowData['total_outflow'], 2) }}</td>
                        <td class="text-end text-{{ $cashFlowData['net_cash_flow'] >= 0 ? 'success' : 'danger' }}">
                            RM {{ number_format($cashFlowData['net_cash_flow'], 2) }}
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<!-- Export Button -->
<div class="card mt-4">
    <div class="card-body text-center">
        <a href="{{ route('admin.financial.export.cash-flow', ['date_from' => $startDate->format('Y-m-d'), 'date_to' => $endDate->format('Y-m-d')]) }}" 
           class="btn btn-success">
            <i class="fas fa-file-excel me-2"></i> Download Excel Report
        </a>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
$(document).ready(function() {
    const ctx = document.getElementById('cashFlowChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: {!! json_encode($cashFlowData['daily_breakdown']->pluck('date')->toArray()) !!},
            datasets: [
                {
                    label: 'Cash Inflow',
                    data: {!! json_encode($cashFlowData['daily_breakdown']->pluck('revenue')->toArray()) !!},
                    borderColor: '#4caf50',
                    backgroundColor: 'rgba(76, 175, 80, 0.1)',
                    fill: true
                },
                {
                    label: 'Cash Outflow',
                    data: {!! json_encode($cashFlowData['daily_breakdown']->pluck('expense')->toArray()) !!},
                    borderColor: '#f44336',
                    backgroundColor: 'rgba(244, 67, 54, 0.1)',
                    fill: true
                },
                {
                    label: 'Net Cash Flow',
                    data: {!! json_encode($cashFlowData['daily_breakdown']->pluck('profit')->toArray()) !!},
                    borderColor: '#2196f3',
                    backgroundColor: 'rgba(33, 150, 243, 0.1)',
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'top' } },
            scales: { y: { beginAtZero: true } }
        }
    });
});
</script>
@endpush
