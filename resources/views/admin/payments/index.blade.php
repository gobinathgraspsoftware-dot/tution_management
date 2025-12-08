@extends('layouts.app')

@section('title', 'Payments')
@section('page-title', 'Payment Management')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item active">Payments</li>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-money-bill-wave text-success fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Total Collected</h6>
                            <h3 class="mb-0">RM {{ number_format($statistics['total_collected'], 2) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-cash-register text-primary fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Cash Payments</h6>
                            <h3 class="mb-0">RM {{ number_format($statistics['cash_collected'], 2) }}</h3>
                            <small class="text-muted">{{ $statistics['cash_transactions'] }} transactions</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-info bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-qrcode text-info fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">QR Payments</h6>
                            <h3 class="mb-0">RM {{ number_format($statistics['qr_collected'], 2) }}</h3>
                            <small class="text-muted">{{ $statistics['qr_transactions'] }} transactions</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-clock text-warning fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Pending Verification</h6>
                            <h3 class="mb-0">{{ $statistics['pending_verification'] }}</h3>
                            <small class="text-muted">Requires review</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-3">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ route('admin.payments.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i> Record Payment
                            </a>
                            <a href="{{ route('admin.payments.daily-report') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-chart-bar me-1"></i> Daily Report
                            </a>
                            <a href="{{ route('admin.payments.history') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-history me-1"></i> Payment History
                            </a>
                            @if($statistics['pending_verification'] > 0)
                            <a href="{{ route('admin.payments.pending-verifications') }}" class="btn btn-warning">
                                <i class="fas fa-exclamation-triangle me-1"></i> Pending Verifications ({{ $statistics['pending_verification'] }})
                            </a>
                            @endif
                        </div>
                        <a href="{{ route('admin.payments.export', request()->query()) }}" class="btn btn-outline-success">
                            <i class="fas fa-file-export me-1"></i> Export
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filters</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.payments.index') }}" method="GET">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" class="form-control" placeholder="Payment #, Reference, Student..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Payment Method</label>
                        <select name="payment_method" class="form-select">
                            <option value="">All Methods</option>
                            @foreach($paymentMethods as $key => $label)
                                <option value="{{ $key }}" {{ request('payment_method') == $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Statuses</option>
                            @foreach($paymentStatuses as $key => $label)
                                <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Date From</label>
                        <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Date To</label>
                        <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Payments Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Payments ({{ $payments->total() }})</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Payment #</th>
                            <th>Date</th>
                            <th>Student</th>
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
                                <strong>{{ $payment->payment_number }}</strong>
                                @if($payment->reference_number)
                                <br><small class="text-muted">Ref: {{ $payment->reference_number }}</small>
                                @endif
                            </td>
                            <td>
                                {{ $payment->payment_date->format('d M Y') }}
                                <br><small class="text-muted">{{ $payment->created_at->format('h:i A') }}</small>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm me-2">
                                        <span class="avatar-initial rounded-circle bg-primary">
                                            {{ substr($payment->student->user->name ?? 'N', 0, 1) }}
                                        </span>
                                    </div>
                                    <div>
                                        <strong>{{ $payment->student->user->name ?? 'N/A' }}</strong>
                                        <br><small class="text-muted">{{ $payment->student->student_id ?? '' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($payment->invoice)
                                <a href="{{ route('admin.invoices.show', $payment->invoice) }}">
                                    {{ $payment->invoice->invoice_number }}
                                </a>
                                @else
                                <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $methodBadges = [
                                        'cash' => 'success',
                                        'qr' => 'info',
                                        'online_gateway' => 'primary',
                                        'bank_transfer' => 'secondary',
                                        'cheque' => 'dark',
                                    ];
                                @endphp
                                <span class="badge bg-{{ $methodBadges[$payment->payment_method] ?? 'secondary' }}">
                                    <i class="fas fa-{{ $payment->payment_method === 'cash' ? 'money-bill' : ($payment->payment_method === 'qr' ? 'qrcode' : 'credit-card') }} me-1"></i>
                                    {{ $paymentMethods[$payment->payment_method] ?? ucfirst($payment->payment_method) }}
                                </span>
                            </td>
                            <td class="text-end">
                                <strong class="text-success">RM {{ number_format($payment->amount, 2) }}</strong>
                            </td>
                            <td>
                                @php
                                    $statusBadges = [
                                        'completed' => 'success',
                                        'pending' => 'warning',
                                        'failed' => 'danger',
                                        'refunded' => 'secondary',
                                    ];
                                @endphp
                                <span class="badge bg-{{ $statusBadges[$payment->status] ?? 'secondary' }}">
                                    {{ ucfirst($payment->status) }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="btn-group">
                                    <a href="{{ route('admin.payments.show', $payment) }}" class="btn btn-sm btn-outline-primary" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.payments.receipt', $payment) }}" class="btn btn-sm btn-outline-success" title="Receipt" target="_blank">
                                        <i class="fas fa-receipt"></i>
                                    </a>
                                    <a href="{{ route('admin.payments.print-receipt', $payment) }}" class="btn btn-sm btn-outline-secondary" title="Print" target="_blank">
                                        <i class="fas fa-print"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <img src="{{ asset('images/empty-state.svg') }}" alt="No payments" style="max-width: 200px; opacity: 0.5;">
                                <p class="text-muted mt-3 mb-0">No payments found</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($payments->hasPages())
        <div class="card-footer bg-white">
            {{ $payments->links() }}
        </div>
        @endif
    </div>
</div>

<style>
.avatar {
    width: 40px;
    height: 40px;
}
.avatar-sm {
    width: 32px;
    height: 32px;
}
.avatar-initial {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
    font-weight: 600;
    color: #fff;
}
</style>
@endsection
