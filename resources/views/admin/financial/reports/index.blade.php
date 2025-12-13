@extends('layouts.app')

@section('title', 'Financial Reports')
@section('page-title', 'Financial Reports')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-file-alt me-2"></i> Financial Reports & Analytics</h1>
        <div class="btn-group">
            <a href="{{ route('admin.financial.dashboard') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i> Back to Dashboard
            </a>
        </div>
    </div>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.financial.dashboard') }}">Financial Dashboard</a></li>
            <li class="breadcrumb-item active">Reports</li>
        </ol>
    </nav>
</div>

<!-- Date Filter -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-calendar me-2"></i> Select Report Period</h5>
    </div>
    <div class="card-body">
        <form method="GET" id="reportFilterForm">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="date_from" class="form-control"
                           value="{{ $startDate->format('Y-m-d') }}"
                           max="{{ now()->format('Y-m-d') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">End Date</label>
                    <input type="date" name="date_to" class="form-control"
                           value="{{ $endDate->format('Y-m-d') }}"
                           max="{{ now()->format('Y-m-d') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-2"></i> Apply Filter
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #e8f5e9; color: #4caf50;">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="stat-details">
                <h6 class="text-muted mb-1">Total Revenue</h6>
                <h3 class="mb-0 text-success">RM {{ number_format($summary['total_revenue'], 2) }}</h3>
                <small class="text-muted">{{ $summary['revenue_count'] }} transactions</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #ffebee; color: #f44336;">
                <i class="fas fa-receipt"></i>
            </div>
            <div class="stat-details">
                <h6 class="text-muted mb-1">Total Expenses</h6>
                <h3 class="mb-0 text-danger">RM {{ number_format($summary['total_expenses'], 2) }}</h3>
                <small class="text-muted">{{ $summary['expense_count'] }} expenses</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: {{ $summary['net_profit'] >= 0 ? '#e8f5e9' : '#ffebee' }}; color: {{ $summary['net_profit'] >= 0 ? '#4caf50' : '#f44336' }};">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stat-details">
                <h6 class="text-muted mb-1">Net Profit/Loss</h6>
                <h3 class="mb-0 text-{{ $summary['net_profit'] >= 0 ? 'success' : 'danger' }}">
                    RM {{ number_format($summary['net_profit'], 2) }}
                </h3>
                <small class="text-muted">{{ $summary['status'] }}</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #e3f2fd; color: #2196f3;">
                <i class="fas fa-percentage"></i>
            </div>
            <div class="stat-details">
                <h6 class="text-muted mb-1">Profit Margin</h6>
                <h3 class="mb-0 text-info">{{ number_format($summary['profit_margin'], 2) }}%</h3>
                <small class="text-muted">
                    {{ $summary['profit_margin'] >= 20 ? 'Excellent' : ($summary['profit_margin'] >= 10 ? 'Good' : 'Fair') }}
                </small>
            </div>
        </div>
    </div>
</div>

<!-- Financial Health Metrics -->
<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="fas fa-heartbeat me-2"></i> Financial Health Metrics</h5>
    </div>
    <div class="card-body">
        <div class="row text-center">
            <div class="col-md-3">
                <h6 class="text-muted">Overall Health</h6>
                <h2 class="text-{{ $healthMetrics['overall_health']['score'] >= 70 ? 'success' : ($healthMetrics['overall_health']['score'] >= 50 ? 'warning' : 'danger') }}">
                    {{ $healthMetrics['overall_health']['rating'] }}
                </h2>
                <small class="text-muted">{{ $healthMetrics['overall_health']['status'] }}</small>
            </div>
            <div class="col-md-3">
                <h6 class="text-muted">Profitability</h6>
                <h4 class="badge bg-{{ $healthMetrics['profitability_score'] === 'Excellent' ? 'success' : ($healthMetrics['profitability_score'] === 'Good' ? 'primary' : 'warning') }}">
                    {{ $healthMetrics['profitability_score'] }}
                </h4>
            </div>
            <div class="col-md-3">
                <h6 class="text-muted">Revenue Health</h6>
                <h4 class="badge bg-{{ $healthMetrics['revenue_health'] === 'Excellent' ? 'success' : ($healthMetrics['revenue_health'] === 'Good' ? 'primary' : 'warning') }}">
                    {{ $healthMetrics['revenue_health'] }}
                </h4>
            </div>
            <div class="col-md-3">
                <h6 class="text-muted">Expense Efficiency</h6>
                <h4 class="badge bg-{{ $healthMetrics['expense_efficiency'] === 'Excellent' ? 'success' : ($healthMetrics['expense_efficiency'] === 'Good' ? 'primary' : 'warning') }}">
                    {{ $healthMetrics['expense_efficiency'] }}
                </h4>
            </div>
        </div>
    </div>
</div>

<!-- Report Types -->
<div class="row">
    <!-- Profit & Loss Report -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-balance-scale me-2"></i> Profit & Loss Statement</h5>
            </div>
            <div class="card-body">
                <p>Comprehensive profit and loss statement showing detailed revenue and expense breakdown with analysis.</p>
                <ul class="list-unstyled">
                    <li><i class="fas fa-check text-success me-2"></i> Detailed revenue sources</li>
                    <li><i class="fas fa-check text-success me-2"></i> Expense categorization</li>
                    <li><i class="fas fa-check text-success me-2"></i> Profit margin analysis</li>
                    <li><i class="fas fa-check text-success me-2"></i> Financial recommendations</li>
                </ul>
            </div>
            <div class="card-footer">
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.financial.reports.profit-loss', ['date_from' => $startDate->format('Y-m-d'), 'date_to' => $endDate->format('Y-m-d')]) }}"
                       class="btn btn-success">
                        <i class="fas fa-eye me-2"></i> View Report
                    </a>
                    <div class="btn-group">
                        <a href="{{ route('admin.financial.export.profit-loss', ['date_from' => $startDate->format('Y-m-d'), 'date_to' => $endDate->format('Y-m-d')]) }}"
                           class="btn btn-sm btn-outline-success">
                            <i class="fas fa-file-excel me-1"></i> Excel
                        </a>
                        <a href="{{ route('admin.financial.download.profit-loss-pdf', ['date_from' => $startDate->format('Y-m-d'), 'date_to' => $endDate->format('Y-m-d')]) }}"
                           class="btn btn-sm btn-outline-success">
                            <i class="fas fa-file-pdf me-1"></i> PDF
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Category Revenue Report -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i> Category Revenue Analysis</h5>
            </div>
            <div class="card-body">
                <p>Detailed revenue segmentation by category with percentage breakdown and trend analysis.</p>
                <ul class="list-unstyled">
                    <li><i class="fas fa-check text-primary me-2"></i> Revenue by category</li>
                    <li><i class="fas fa-check text-primary me-2"></i> Percentage contribution</li>
                    <li><i class="fas fa-check text-primary me-2"></i> Category comparison</li>
                    <li><i class="fas fa-check text-primary me-2"></i> Growth opportunities</li>
                </ul>
            </div>
            <div class="card-footer">
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.financial.reports.category-revenue', ['date_from' => $startDate->format('Y-m-d'), 'date_to' => $endDate->format('Y-m-d')]) }}"
                       class="btn btn-primary">
                        <i class="fas fa-eye me-2"></i> View Report
                    </a>
                    <div class="btn-group">
                        <a href="{{ route('admin.financial.export.category-revenue', ['date_from' => $startDate->format('Y-m-d'), 'date_to' => $endDate->format('Y-m-d')]) }}"
                           class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-file-excel me-1"></i> Excel
                        </a>
                        <a href="{{ route('admin.financial.download.category-revenue-pdf', ['date_from' => $startDate->format('Y-m-d'), 'date_to' => $endDate->format('Y-m-d')]) }}"
                           class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-file-pdf me-1"></i> PDF
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cash Flow Report -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-exchange-alt me-2"></i> Cash Flow Analysis</h5>
            </div>
            <div class="card-body">
                <p>Complete cash flow analysis showing inflow, outflow, and net cash flow with daily breakdown.</p>
                <ul class="list-unstyled">
                    <li><i class="fas fa-check text-info me-2"></i> Cash inflow summary</li>
                    <li><i class="fas fa-check text-info me-2"></i> Cash outflow tracking</li>
                    <li><i class="fas fa-check text-info me-2"></i> Net cash flow status</li>
                    <li><i class="fas fa-check text-info me-2"></i> Daily breakdown</li>
                </ul>
            </div>
            <div class="card-footer">
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.financial.reports.cash-flow', ['date_from' => $startDate->format('Y-m-d'), 'date_to' => $endDate->format('Y-m-d')]) }}"
                       class="btn btn-info">
                        <i class="fas fa-eye me-2"></i> View Report
                    </a>
                    <div class="btn-group">
                        <a href="{{ route('admin.financial.export.cash-flow', ['date_from' => $startDate->format('Y-m-d'), 'date_to' => $endDate->format('Y-m-d')]) }}"
                           class="btn btn-sm btn-outline-info">
                            <i class="fas fa-file-excel me-1"></i> Excel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Comprehensive Report -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="fas fa-file-invoice me-2"></i> Comprehensive Financial Report</h5>
            </div>
            <div class="card-body">
                <p>All-inclusive financial report combining all metrics, analysis, and recommendations.</p>
                <ul class="list-unstyled">
                    <li><i class="fas fa-check text-warning me-2"></i> Complete financial summary</li>
                    <li><i class="fas fa-check text-warning me-2"></i> All revenue & expense data</li>
                    <li><i class="fas fa-check text-warning me-2"></i> Profit/loss analysis</li>
                    <li><i class="fas fa-check text-warning me-2"></i> Trend analysis & forecasts</li>
                </ul>
            </div>
            <div class="card-footer">
                <div class="d-grid gap-2">
                    <div class="btn-group">
                        <a href="{{ route('admin.financial.export.comprehensive', ['date_from' => $startDate->format('Y-m-d'), 'date_to' => $endDate->format('Y-m-d')]) }}"
                           class="btn btn-warning">
                            <i class="fas fa-file-excel me-1"></i> Export Excel
                        </a>
                        <a href="{{ route('admin.financial.download.comprehensive-pdf', ['date_from' => $startDate->format('Y-m-d'), 'date_to' => $endDate->format('Y-m-d')]) }}"
                           class="btn btn-warning">
                            <i class="fas fa-file-pdf me-1"></i> Export PDF
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Stats -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i> Report Period Information</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <strong>Period:</strong> {{ $startDate->format('d M Y') }} - {{ $endDate->format('d M Y') }}
            </div>
            <div class="col-md-4">
                <strong>Days:</strong> {{ $startDate->diffInDays($endDate) + 1 }} days
            </div>
            <div class="col-md-4">
                <strong>Generated:</strong> {{ now()->format('d M Y, h:i A') }}
            </div>
        </div>
    </div>
</div>
@endsection
