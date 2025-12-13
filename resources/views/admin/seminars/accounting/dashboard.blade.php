@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-12">
            <h1 class="h3 mb-0">Seminar Accounting Dashboard</h1>
            <p class="text-muted">Overview of seminar financial performance and expenses</p>
        </div>
    </div>

    <!-- Date Filter -->
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.seminars.accounting.dashboard') }}" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Date From</label>
                            <input type="date" name="date_from" class="form-control" value="{{ $filters['date_from'] }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Date To</label>
                            <input type="date" name="date_to" class="form-control" value="{{ $filters['date_to'] }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary d-block">
                                <i class="fas fa-filter"></i> Apply Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Total Revenue</h6>
                            <h3 class="mb-0">RM {{ number_format($profitability['summary']['total_revenue'], 2) }}</h3>
                        </div>
                        <div class="fs-1">
                            <i class="fas fa-coins"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Total Expenses</h6>
                            <h3 class="mb-0">RM {{ number_format($profitability['summary']['total_expenses'], 2) }}</h3>
                        </div>
                        <div class="fs-1">
                            <i class="fas fa-receipt"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card bg-{{ $profitability['summary']['total_profit'] >= 0 ? 'success' : 'warning' }} text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Net Profit/Loss</h6>
                            <h3 class="mb-0">RM {{ number_format($profitability['summary']['total_profit'], 2) }}</h3>
                        </div>
                        <div class="fs-1">
                            <i class="fas fa-chart-line"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Profit Margin</h6>
                            <h3 class="mb-0">{{ number_format($profitability['summary']['overall_margin'], 2) }}%</h3>
                        </div>
                        <div class="fs-1">
                            <i class="fas fa-percentage"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Seminar Performance</h6>
                    <div class="mt-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Total Seminars</span>
                            <strong>{{ $profitability['summary']['total_seminars'] }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2 text-success">
                            <span>Profitable</span>
                            <strong>{{ $profitability['summary']['profitable_count'] }}</strong>
                        </div>
                        <div class="d-flex justify-content-between text-danger">
                            <span>Loss-Making</span>
                            <strong>{{ $profitability['summary']['loss_count'] }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Payment Collection</h6>
                    <div class="mt-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Total Participants</span>
                            <strong>{{ $paymentTracking['summary']['total_participants'] }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2 text-success">
                            <span>Paid</span>
                            <strong>{{ $paymentTracking['summary']['total_paid'] }}</strong>
                        </div>
                        <div class="d-flex justify-content-between text-warning">
                            <span>Pending</span>
                            <strong>{{ $paymentTracking['summary']['total_pending'] }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Expense Status</h6>
                    <div class="mt-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Total Expenses</span>
                            <strong>RM {{ number_format($expenseStats['total'], 2) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2 text-success">
                            <span>Approved</span>
                            <strong>RM {{ number_format($expenseStats['approved'], 2) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between text-warning">
                            <span>Pending</span>
                            <strong>RM {{ number_format($expenseStats['pending'], 2) }} ({{ $expenseStats['pending_count'] }})</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Approvals -->
    @if($pendingExpenses->count() > 0)
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Expenses Pending Approval</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Seminar</th>
                                    <th>Category</th>
                                    <th>Description</th>
                                    <th>Amount</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pendingExpenses as $expense)
                                <tr>
                                    <td>{{ $expense->expense_date->format('d M Y') }}</td>
                                    <td>
                                        <a href="{{ route('admin.seminars.show', $expense->seminar) }}">
                                            {{ $expense->seminar->name }}
                                        </a>
                                    </td>
                                    <td>{{ $expense->category_label }}</td>
                                    <td>{{ Str::limit($expense->description, 50) }}</td>
                                    <td><strong>RM {{ number_format($expense->amount, 2) }}</strong></td>
                                    <td>
                                        <a href="{{ route('admin.seminars.accounting.expenses', $expense->seminar) }}" 
                                           class="btn btn-sm btn-primary">
                                            Review
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Quick Actions -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <a href="{{ route('admin.seminars.accounting.reports.profitability') }}" class="btn btn-primary me-2">
                        <i class="fas fa-chart-bar"></i> Profitability Report
                    </a>
                    <a href="{{ route('admin.seminars.accounting.reports.payment-status') }}" class="btn btn-info me-2">
                        <i class="fas fa-money-check-alt"></i> Payment Status Report
                    </a>
                    <a href="{{ route('admin.seminars.index') }}" class="btn btn-secondary">
                        <i class="fas fa-list"></i> View All Seminars
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
    transition: transform 0.2s;
}
.card:hover {
    transform: translateY(-2px);
}
</style>
@endpush
