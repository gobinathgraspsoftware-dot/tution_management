@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-8">
            <h1 class="h3 mb-0">Financial Report</h1>
            <p class="text-muted">{{ $overview['seminar']->name }} ({{ $overview['seminar']->code }})</p>
        </div>
        <div class="col-md-4 text-end">
            <div class="btn-group">
                <a href="{{ route('admin.seminars.accounting.reports.financial.export-excel', $overview['seminar']) }}" class="btn btn-success">
                    <i class="fas fa-file-excel"></i> Excel
                </a>
                <a href="{{ route('admin.seminars.accounting.reports.financial.export-pdf', $overview['seminar']) }}" class="btn btn-danger">
                    <i class="fas fa-file-pdf"></i> PDF
                </a>
            </div>
            <a href="{{ route('admin.seminars.show', $overview['seminar']) }}" class="btn btn-secondary ms-2">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-primary">
                <div class="card-body">
                    <h6 class="text-primary mb-2">Total Revenue</h6>
                    <h3 class="mb-0">RM {{ number_format($overview['revenue']['total'], 2) }}</h3>
                    <small class="text-muted">From {{ $overview['revenue']['paid_count'] }} paid participants</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-danger">
                <div class="card-body">
                    <h6 class="text-danger mb-2">Total Expenses</h6>
                    <h3 class="mb-0">RM {{ number_format($overview['expenses']['total'], 2) }}</h3>
                    <small class="text-muted">{{ $overview['expenses']['approved_count'] }} approved items</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-{{ $overview['profitability']['net_profit'] >= 0 ? 'success' : 'warning' }}">
                <div class="card-body">
                    <h6 class="text-{{ $overview['profitability']['net_profit'] >= 0 ? 'success' : 'warning' }} mb-2">Net Profit/Loss</h6>
                    <h3 class="mb-0">RM {{ number_format($overview['profitability']['net_profit'], 2) }}</h3>
                    <small class="text-muted">{{ $overview['profitability']['status'] }} ({{ $overview['profitability']['profit_margin'] }}% margin)</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Revenue Breakdown -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Revenue Breakdown</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <td><strong>Paid Revenue</strong></td>
                            <td class="text-end text-success"><strong>RM {{ number_format($overview['revenue']['total'], 2) }}</strong></td>
                        </tr>
                        <tr>
                            <td>Pending Revenue</td>
                            <td class="text-end text-warning">RM {{ number_format($overview['revenue']['pending'], 2) }}</td>
                        </tr>
                        <tr>
                            <td>Refunded</td>
                            <td class="text-end text-danger">RM {{ number_format($overview['revenue']['refunded'], 2) }}</td>
                        </tr>
                    </table>

                    <h6 class="mt-4">By Payment Method</h6>
                    <table class="table table-sm">
                        @forelse($overview['revenue']['by_method'] as $method => $amount)
                        <tr>
                            <td>{{ ucfirst($method) }}</td>
                            <td class="text-end">RM {{ number_format($amount, 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="2" class="text-center text-muted">No data available</td></tr>
                        @endforelse
                    </table>
                </div>
            </div>
        </div>

        <!-- Expense Breakdown -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Expense Breakdown</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <td><strong>Approved Expenses</strong></td>
                            <td class="text-end text-danger"><strong>RM {{ number_format($overview['expenses']['total'], 2) }}</strong></td>
                        </tr>
                        <tr>
                            <td>Pending Approval</td>
                            <td class="text-end text-warning">RM {{ number_format($overview['expenses']['pending'], 2) }}</td>
                        </tr>
                    </table>

                    <h6 class="mt-4">By Category</h6>
                    <table class="table table-sm">
                        @forelse($overview['expenses']['by_category'] as $category => $amount)
                        <tr>
                            <td>{{ \App\Services\SeminarAccountingService::getCategoryLabel($category) }}</td>
                            <td class="text-end">RM {{ number_format($amount, 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="2" class="text-center text-muted">No expenses recorded</td></tr>
                        @endforelse
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Profitability Analysis -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Profitability Analysis</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <p class="text-muted mb-1">Net Profit/Loss</p>
                            <h4 class="text-{{ $overview['profitability']['net_profit'] >= 0 ? 'success' : 'danger' }}">
                                RM {{ number_format($overview['profitability']['net_profit'], 2) }}
                            </h4>
                        </div>
                        <div class="col-md-3">
                            <p class="text-muted mb-1">Profit Margin</p>
                            <h4>{{ $overview['profitability']['profit_margin'] }}%</h4>
                        </div>
                        <div class="col-md-3">
                            <p class="text-muted mb-1">ROI</p>
                            <h4>{{ $overview['profitability']['roi'] }}%</h4>
                        </div>
                        <div class="col-md-3">
                            <p class="text-muted mb-1">Status</p>
                            <h4>
                                <span class="badge bg-{{ $overview['profitability']['net_profit'] >= 0 ? 'success' : 'danger' }}">
                                    {{ $overview['profitability']['status'] }}
                                </span>
                            </h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
