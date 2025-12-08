@extends('layouts.staff')

@section('title', 'Payments')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Payment Management</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('staff.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Payments</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('staff.payments.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Record Payment
            </a>
        </div>
    </div>

    <!-- Today's Collection Summary -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 text-white-50">Today's Total</h6>
                            <h3 class="mb-0">RM {{ number_format($todayStats['total_collected'] ?? 0, 2) }}</h3>
                        </div>
                        <i class="fas fa-wallet fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 text-white-50">Cash Collection</h6>
                            <h3 class="mb-0">RM {{ number_format($todayStats['by_method']['cash']['amount'] ?? 0, 2) }}</h3>
                        </div>
                        <i class="fas fa-money-bill-wave fa-2x opacity-50"></i>
                    </div>
                    <small>{{ $todayStats['by_method']['cash']['count'] ?? 0 }} transactions</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 text-white-50">QR Collection</h6>
                            <h3 class="mb-0">RM {{ number_format($todayStats['by_method']['qr']['amount'] ?? 0, 2) }}</h3>
                        </div>
                        <i class="fas fa-qrcode fa-2x opacity-50"></i>
                    </div>
                    <small>{{ $todayStats['by_method']['qr']['count'] ?? 0 }} transactions</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-secondary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 text-white-50">Total Transactions</h6>
                            <h3 class="mb-0">{{ $todayStats['total_transactions'] ?? 0 }}</h3>
                        </div>
                        <i class="fas fa-receipt fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body py-3">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <form action="{{ route('staff.payments.index') }}" method="GET" class="row g-2">
                                <div class="col-md-3">
                                    <input type="text" name="search" class="form-control"
                                           placeholder="Search payment/student..." value="{{ request('search') }}">
                                </div>
                                <div class="col-md-3">
                                    <input type="date" name="date" class="form-control"
                                           value="{{ request('date', $date->format('Y-m-d')) }}">
                                </div>
                                <div class="col-md-3">
                                    <select name="payment_method" class="form-select">
                                        <option value="">All Methods</option>
                                        @foreach($paymentMethods as $key => $label)
                                            <option value="{{ $key }}" {{ request('payment_method') == $key ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-search me-1"></i> Search
                                    </button>
                                </div>
                            </form>
                        </div>
                        <div class="col-md-4 text-end">
                            <a href="{{ route('staff.payments.quick-payment') }}" class="btn btn-success">
                                <i class="fas fa-bolt me-1"></i> Quick Payment
                            </a>
                            <a href="{{ route('staff.payments.today-collection') }}" class="btn btn-info">
                                <i class="fas fa-chart-pie me-1"></i> Today's Report
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payments Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-list me-2"></i>Payments - {{ $date->format('d M Y') }}
            </h5>
            <span class="badge bg-primary">{{ $payments->total() }} records</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Payment #</th>
                            <th>Time</th>
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
                                    <a href="{{ route('staff.payments.show', $payment) }}" class="fw-bold text-decoration-none">
                                        {{ $payment->payment_number }}
                                    </a>
                                </td>
                                <td>{{ $payment->created_at->format('h:i A') }}</td>
                                <td>
                                    @if($payment->student && $payment->student->user)
                                        {{ $payment->student->user->name }}
                                        <br><small class="text-muted">{{ $payment->student->student_id }}</small>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @if($payment->invoice)
                                        {{ $payment->invoice->invoice_number }}
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
                                        @default
                                            <span class="badge bg-secondary">{{ ucfirst($payment->status) }}</span>
                                    @endswitch
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('staff.payments.show', $payment) }}"
                                           class="btn btn-outline-primary" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($payment->status === 'completed')
                                            <a href="{{ route('staff.payments.receipt', $payment) }}"
                                               class="btn btn-outline-success" title="Receipt" target="_blank">
                                                <i class="fas fa-receipt"></i>
                                            </a>
                                            <a href="{{ route('staff.payments.print-receipt', $payment) }}"
                                               class="btn btn-outline-info" title="Print" target="_blank">
                                                <i class="fas fa-print"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <p class="text-muted mb-0">No payments recorded for this date.</p>
                                    <a href="{{ route('staff.payments.create') }}" class="btn btn-primary mt-2">
                                        <i class="fas fa-plus me-1"></i> Record First Payment
                                    </a>
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
