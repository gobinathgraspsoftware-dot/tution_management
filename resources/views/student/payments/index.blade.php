@extends('layouts.student')

@section('title', 'My Payments')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">My Payments</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Payments</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 text-white-50">Total Paid</h6>
                            <h3 class="mb-0">RM {{ number_format($summary['total_paid'], 2) }}</h3>
                        </div>
                        <i class="fas fa-check-circle fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 text-white-50">This Month</h6>
                            <h3 class="mb-0">RM {{ number_format($summary['this_month'], 2) }}</h3>
                        </div>
                        <i class="fas fa-calendar-alt fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-warning text-dark">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 opacity-75">Pending</h6>
                            <h3 class="mb-0">RM {{ number_format($summary['pending_amount'], 2) }}</h3>
                        </div>
                        <i class="fas fa-clock fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card shadow-sm mb-4">
        <div class="card-body py-3">
            <form action="{{ route('student.payments.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        @foreach($paymentStatuses as $key => $label)
                            <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">From Date</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">To Date</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-1"></i> Filter
                    </button>
                    <a href="{{ route('student.payments.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-redo me-1"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Payments Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-history me-2"></i>Payment Records</h5>
            <div>
                <a href="{{ route('student.payments.history') }}" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-chart-line me-1"></i> Full History
                </a>
                @if(Route::has('student.payments.outstanding'))
                    <a href="{{ route('student.payments.outstanding') }}" class="btn btn-sm btn-outline-warning">
                        <i class="fas fa-exclamation-circle me-1"></i> Outstanding
                    </a>
                @endif
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Payment #</th>
                            <th>Date</th>
                            <th>Invoice</th>
                            <th>Method</th>
                            <th class="text-end">Amount</th>
                            <th>Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                            <tr>
                                <td>
                                    <a href="{{ route('student.payments.show', $payment) }}" class="fw-bold text-decoration-none">
                                        {{ $payment->payment_number }}
                                    </a>
                                </td>
                                <td>
                                    {{ $payment->payment_date->format('d M Y') }}
                                    <br>
                                    <small class="text-muted">{{ $payment->created_at->format('h:i A') }}</small>
                                </td>
                                <td>
                                    @if($payment->invoice)
                                        {{ $payment->invoice->invoice_number }}
                                        @if($payment->invoice->type)
                                            <br><small class="text-muted">{{ ucfirst($payment->invoice->type) }}</small>
                                        @endif
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @switch($payment->payment_method)
                                        @case('cash')
                                            <span class="badge bg-success"><i class="fas fa-money-bill-wave me-1"></i>Cash</span>
                                            @break
                                        @case('qr')
                                            <span class="badge bg-info"><i class="fas fa-qrcode me-1"></i>QR</span>
                                            @break
                                        @case('bank_transfer')
                                            <span class="badge bg-primary"><i class="fas fa-university me-1"></i>Bank</span>
                                            @break
                                        @case('online_gateway')
                                            <span class="badge bg-purple"><i class="fas fa-globe me-1"></i>Online</span>
                                            @break
                                        @default
                                            <span class="badge bg-secondary">{{ ucfirst($payment->payment_method) }}</span>
                                    @endswitch
                                </td>
                                <td class="text-end fw-bold text-success">RM {{ number_format($payment->amount, 2) }}</td>
                                <td>
                                    @switch($payment->status)
                                        @case('completed')
                                            <span class="badge bg-success">Completed</span>
                                            @break
                                        @case('pending')
                                            <span class="badge bg-warning text-dark">Pending</span>
                                            @break
                                        @case('refunded')
                                            <span class="badge bg-secondary">Refunded</span>
                                            @break
                                        @default
                                            <span class="badge bg-dark">{{ ucfirst($payment->status) }}</span>
                                    @endswitch
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('student.payments.show', $payment) }}"
                                           class="btn btn-outline-primary" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($payment->status === 'completed')
                                            <a href="{{ route('student.payments.receipt', $payment) }}"
                                               class="btn btn-outline-success" title="Receipt" target="_blank">
                                                <i class="fas fa-receipt"></i>
                                            </a>
                                            <a href="{{ route('student.payments.download-receipt', $payment) }}"
                                               class="btn btn-outline-info" title="Download">
                                                <i class="fas fa-download"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <p class="text-muted mb-0">No payment records found.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($payments->hasPages())
            <div class="card-footer bg-white">
                {{ $payments->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
