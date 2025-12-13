@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-8"><h1 class="h3">Payment Status Tracking</h1></div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4"><input type="date" name="date_from" class="form-control" value="{{ $filters['date_from'] }}"></div>
                <div class="col-md-4"><input type="date" name="date_to" class="form-control" value="{{ $filters['date_to'] }}"></div>
                <div class="col-md-2"><button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter"></i> Apply</button></div>
            </form>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3"><div class="card"><div class="card-body"><h6>Total Participants</h6><h3>{{ $report['summary']['total_participants'] }}</h3></div></div></div>
        <div class="col-md-3"><div class="card bg-success text-white"><div class="card-body"><h6>Paid</h6><h3>{{ $report['summary']['total_paid'] }}</h3><small>RM {{ number_format($report['summary']['total_revenue'], 2) }}</small></div></div></div>
        <div class="col-md-3"><div class="card bg-warning text-white"><div class="card-body"><h6>Pending</h6><h3>{{ $report['summary']['total_pending'] }}</h3><small>RM {{ number_format($report['summary']['total_pending_amount'], 2) }}</small></div></div></div>
        <div class="col-md-3"><div class="card bg-info text-white"><div class="card-body"><h6>Payment Rate</h6><h3>{{ $report['summary']['overall_payment_rate'] }}%</h3></div></div></div>
    </div>

    <div class="card">
        <div class="card-header"><h5>Payment Collection Status</h5></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Seminar</th><th>Date</th><th>Participants</th><th>Paid</th><th>Pending</th><th>Paid Amount</th><th>Pending Amount</th><th>Rate %</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse($report['tracking'] as $item)
                        <tr>
                            <td><a href="{{ route('admin.seminars.show', $item['seminar_id']) }}">{{ $item['seminar_name'] }}</a></td>
                            <td>{{ $item['date']->format('d M Y') }}</td>
                            <td>{{ $item['total_participants'] }}</td>
                            <td class="text-success">{{ $item['paid_count'] }}</td>
                            <td class="text-warning">{{ $item['pending_count'] }}</td>
                            <td>RM {{ number_format($item['paid_amount'], 2) }}</td>
                            <td>RM {{ number_format($item['pending_amount'], 2) }}</td>
                            <td><strong>{{ $item['payment_rate'] }}%</strong></td>
                            <td><span class="badge bg-{{ $item['payment_rate'] >= 90 ? 'success' : ($item['payment_rate'] >= 75 ? 'primary' : 'warning') }}">{{ $item['collection_status'] }}</span></td>
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
