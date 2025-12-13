@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-8"><h1 class="h3">Profitability Report</h1></div>
        <div class="col-md-4 text-end">
            <div class="btn-group">
                <a href="{{ route('admin.seminars.accounting.reports.profitability.export-excel', request()->all()) }}" class="btn btn-success"><i class="fas fa-file-excel"></i> Excel</a>
                <a href="{{ route('admin.seminars.accounting.reports.profitability.export-pdf', request()->all()) }}" class="btn btn-danger"><i class="fas fa-file-pdf"></i> PDF</a>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3"><input type="date" name="date_from" class="form-control" value="{{ $filters['date_from'] }}" placeholder="Date From"></div>
                <div class="col-md-3"><input type="date" name="date_to" class="form-control" value="{{ $filters['date_to'] }}" placeholder="Date To"></div>
                <div class="col-md-2"><select name="type" class="form-select"><option value="">All Types</option><option value="spm">SPM</option><option value="workshop">Workshop</option><option value="webinar">Webinar</option></select></div>
                <div class="col-md-2"><button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter"></i> Apply</button></div>
            </form>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3"><div class="card bg-primary text-white"><div class="card-body"><h6>Total Seminars</h6><h3>{{ $report['summary']['total_seminars'] }}</h3></div></div></div>
        <div class="col-md-3"><div class="card bg-success text-white"><div class="card-body"><h6>Total Revenue</h6><h3>RM {{ number_format($report['summary']['total_revenue'], 2) }}</h3></div></div></div>
        <div class="col-md-3"><div class="card bg-danger text-white"><div class="card-body"><h6>Total Expenses</h6><h3>RM {{ number_format($report['summary']['total_expenses'], 2) }}</h3></div></div></div>
        <div class="col-md-3"><div class="card bg-info text-white"><div class="card-body"><h6>Net Profit</h6><h3>RM {{ number_format($report['summary']['total_profit'], 2) }}</h3></div></div></div>
    </div>

    <div class="card">
        <div class="card-header"><h5>Seminar Performance</h5></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Code</th><th>Seminar Name</th><th>Date</th><th>Participants</th><th>Revenue</th><th>Expenses</th><th>Profit/Loss</th><th>Margin %</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse($report['seminars'] as $sem)
                        <tr>
                            <td><a href="{{ route('admin.seminars.show', $sem['seminar_id']) }}">{{ $sem['seminar_code'] }}</a></td>
                            <td>{{ $sem['seminar_name'] }}</td>
                            <td>{{ $sem['date']->format('d M Y') }}</td>
                            <td>{{ $sem['participants'] }}</td>
                            <td>RM {{ number_format($sem['revenue'], 2) }}</td>
                            <td>RM {{ number_format($sem['expenses'], 2) }}</td>
                            <td class="text-{{ $sem['profit'] >= 0 ? 'success' : 'danger' }}"><strong>RM {{ number_format($sem['profit'], 2) }}</strong></td>
                            <td>{{ $sem['profit_margin'] }}%</td>
                            <td><span class="badge bg-{{ $sem['profit'] >= 0 ? 'success' : 'danger' }}">{{ $sem['status_label'] }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="9" class="text-center py-4">No data found</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
