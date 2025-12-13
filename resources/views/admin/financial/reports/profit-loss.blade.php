@extends('layouts.app')

@section('title', 'Profit & Loss Statement')
@section('page-title', 'Profit & Loss Statement')

@push('styles')
<style>
    .statement-row {
        padding: 10px 0;
        border-bottom: 1px solid #eee;
    }
    .statement-row:last-child {
        border-bottom: none;
    }
    .statement-total {
        font-weight: 700;
        font-size: 1.1em;
        background-color: #f5f5f5;
        padding: 15px;
        margin-top: 10px;
    }
    .print-section {
        page-break-inside: avoid;
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-balance-scale me-2"></i> Profit & Loss Statement</h1>
        <div class="btn-group">
            <a href="{{ route('admin.financial.reports', ['date_from' => $startDate->format('Y-m-d'), 'date_to' => $endDate->format('Y-m-d')]) }}" 
               class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i> Back to Reports
            </a>
            <button type="button" class="btn btn-primary" onclick="window.print()">
                <i class="fas fa-print me-2"></i> Print
            </button>
        </div>
    </div>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.financial.reports') }}">Financial Reports</a></li>
            <li class="breadcrumb-item active">Profit & Loss</li>
        </ol>
    </nav>
</div>

<!-- Period Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.financial.reports.profit-loss') }}" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Start Date</label>
                <input type="date" name="date_from" class="form-control" value="{{ $startDate->format('Y-m-d') }}" max="{{ now()->format('Y-m-d') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">End Date</label>
                <input type="date" name="date_to" class="form-control" value="{{ $endDate->format('Y-m-d') }}" max="{{ now()->format('Y-m-d') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">&nbsp;</label>
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-2"></i> Generate Report</button>
            </div>
        </form>
    </div>
</div>

<!-- Statement -->
<div class="card mb-4 print-section">
    <div class="card-header bg-primary text-white">
        <h4 class="mb-0">PROFIT & LOSS STATEMENT</h4>
        <small>Period: {{ $statement['period']['start'] }} to {{ $statement['period']['end'] }}</small>
    </div>
    <div class="card-body">
        <!-- REVENUE SECTION -->
        <h5 class="border-bottom pb-2 mb-3"><strong>REVENUE</strong></h5>
        
        <div class="statement-row">
            <div class="row">
                <div class="col-8">Student Fees (Online)</div>
                <div class="col-4 text-end">RM {{ number_format($statement['revenue']['student_fees_online'], 2) }}</div>
            </div>
        </div>
        
        <div class="statement-row">
            <div class="row">
                <div class="col-8">Student Fees (Physical)</div>
                <div class="col-4 text-end">RM {{ number_format($statement['revenue']['student_fees_physical'], 2) }}</div>
            </div>
        </div>
        
        <div class="statement-row">
            <div class="row">
                <div class="col-8">Seminar Revenue</div>
                <div class="col-4 text-end">RM {{ number_format($statement['revenue']['seminar_revenue'], 2) }}</div>
            </div>
        </div>
        
        <div class="statement-row">
            <div class="row">
                <div class="col-8">Cafeteria Sales</div>
                <div class="col-4 text-end">RM {{ number_format($statement['revenue']['cafeteria_sales'], 2) }}</div>
            </div>
        </div>
        
        <div class="statement-row">
            <div class="row">
                <div class="col-8">Material Sales</div>
                <div class="col-4 text-end">RM {{ number_format($statement['revenue']['material_sales'], 2) }}</div>
            </div>
        </div>
        
        <div class="statement-row">
            <div class="row">
                <div class="col-8">Other Revenue</div>
                <div class="col-4 text-end">RM {{ number_format($statement['revenue']['other_revenue'], 2) }}</div>
            </div>
        </div>
        
        <div class="statement-total">
            <div class="row">
                <div class="col-8"><strong>TOTAL REVENUE</strong></div>
                <div class="col-4 text-end"><strong>RM {{ number_format($statement['revenue']['total_revenue'], 2) }}</strong></div>
            </div>
        </div>

        <!-- EXPENSES SECTION -->
        <h5 class="border-bottom pb-2 mb-3 mt-4"><strong>EXPENSES</strong></h5>
        
        @foreach($statement['expenses']['by_category'] as $expense)
        <div class="statement-row">
            <div class="row">
                <div class="col-8">{{ $expense->name }}</div>
                <div class="col-4 text-end">RM {{ number_format($expense->total, 2) }}</div>
            </div>
        </div>
        @endforeach
        
        <div class="statement-total">
            <div class="row">
                <div class="col-8"><strong>TOTAL EXPENSES</strong></div>
                <div class="col-4 text-end"><strong>RM {{ number_format($statement['expenses']['total_expenses'], 2) }}</strong></div>
            </div>
        </div>

        <!-- SUMMARY SECTION -->
        <h5 class="border-bottom pb-2 mb-3 mt-4"><strong>SUMMARY</strong></h5>
        
        <div class="statement-row">
            <div class="row">
                <div class="col-8">Total Revenue</div>
                <div class="col-4 text-end text-success">RM {{ number_format($statement['revenue']['total_revenue'], 2) }}</div>
            </div>
        </div>
        
        <div class="statement-row">
            <div class="row">
                <div class="col-8">Total Expenses</div>
                <div class="col-4 text-end text-danger">RM {{ number_format($statement['expenses']['total_expenses'], 2) }}</div>
            </div>
        </div>
        
        <div class="statement-total bg-{{ $statement['summary']['gross_profit'] >= 0 ? 'success' : 'danger' }} text-white">
            <div class="row">
                <div class="col-8"><strong>GROSS PROFIT/LOSS</strong></div>
                <div class="col-4 text-end"><strong>RM {{ number_format($statement['summary']['gross_profit'], 2) }}</strong></div>
            </div>
        </div>
        
        <div class="row mt-3">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body text-center">
                        <h6 class="text-muted">Profit Margin</h6>
                        <h3 class="text-{{ $statement['summary']['profit_margin'] >= 20 ? 'success' : ($statement['summary']['profit_margin'] >= 10 ? 'warning' : 'danger') }}">
                            {{ number_format($statement['summary']['profit_margin'], 2) }}%
                        </h3>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body text-center">
                        <h6 class="text-muted">Status</h6>
                        <h3 class="text-{{ $statement['summary']['gross_profit'] >= 0 ? 'success' : 'danger' }}">
                            {{ $statement['summary']['status'] }}
                        </h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recommendations -->
@if(!empty($analysis['recommendations']))
<div class="card print-section">
    <div class="card-header bg-info text-white">
        <h5 class="mb-0"><i class="fas fa-lightbulb me-2"></i> Financial Recommendations</h5>
    </div>
    <div class="card-body">
        <ul class="mb-0">
            @foreach($analysis['recommendations'] as $recommendation)
            <li>{{ $recommendation }}</li>
            @endforeach
        </ul>
    </div>
</div>
@endif

<!-- Export Buttons -->
<div class="card mt-4">
    <div class="card-body text-center">
        <div class="btn-group">
            <a href="{{ route('admin.financial.export.profit-loss', ['date_from' => $startDate->format('Y-m-d'), 'date_to' => $endDate->format('Y-m-d')]) }}" 
               class="btn btn-success">
                <i class="fas fa-file-excel me-2"></i> Download Excel
            </a>
            <a href="{{ route('admin.financial.download.profit-loss-pdf', ['date_from' => $startDate->format('Y-m-d'), 'date_to' => $endDate->format('Y-m-d')]) }}" 
               class="btn btn-danger">
                <i class="fas fa-file-pdf me-2"></i> Download PDF
            </a>
        </div>
    </div>
</div>
@endsection
