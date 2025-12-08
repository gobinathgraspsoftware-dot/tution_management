@extends('layouts.app')

@section('title', 'Payment History')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Payment History</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.payments.index') }}">Payments</a></li>
                    <li class="breadcrumb-item active">History</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.payments.export', request()->query()) }}" class="btn btn-success">
                <i class="fas fa-file-export me-1"></i> Export
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 text-white-50">Total Collected</h6>
                            <h3 class="mb-0">RM {{ number_format($statistics['total_collected'] ?? 0, 2) }}</h3>
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
                            <h6 class="mb-1 text-white-50">Cash Payments</h6>
                            <h3 class="mb-0">RM {{ number_format($statistics['cash_collected'] ?? 0, 2) }}</h3>
                        </div>
                        <i class="fas fa-money-bill-wave fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 text-white-50">QR Payments</h6>
                            <h3 class="mb-0">RM {{ number_format($statistics['qr_collected'] ?? 0, 2) }}</h3>
                        </div>
                        <i class="fas fa-qrcode fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-secondary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 text-white-50">Total Transactions</h6>
                            <h3 class="mb-0">{{ $statistics['total_transactions'] ?? 0 }}</h3>
                        </div>
                        <i class="fas fa-receipt fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filter Payment History</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.payments.history') }}" method="GET">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Date From</label>
                        <input type="date" name="date_from" class="form-control"
                               value="{{ $dateFrom->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date To</label>
                        <input type="date" name="date_to" class="form-control"
                               value="{{ $dateTo->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Payment Method</label>
                        <select name="payment_method" class="form-select">
                            <option value="">All Methods</option>
                            @foreach($paymentMethods as $key => $label)
                                <option value="{{ $key }}" {{ request('payment_method') == $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
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
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-1"></i> Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Payment History Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-history me-2"></i>Payment Records
                <small class="text-muted">({{ $dateFrom->format('d M Y') }} - {{ $dateTo->format('d M Y') }})</small>
            </h5>
            <span class="badge bg-primary">{{ $payments->total() }} records</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Payment #</th>
                            <th>Date</th>
                            <th>Student</th>
                            <th>Invoice</th>
                            <th>Method</th>
                            <th class="text-end">Amount</th>
                            <th>Status</th>
                            <th>Processed By</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.payments.show', $payment) }}" class="fw-bold text-decoration-none">
                                        {{ $payment->payment_number }}
                                    </a>
                                </td>
                                <td>{{ $payment->payment_date->format('d M Y') }}</td>
                                <td>
                                    @if($payment->student && $payment->student->user)
                                        <a href="{{ route('admin.students.show', $payment->student) }}" class="text-decoration-none">
                                            {{ $payment->student->user->name }}
                                        </a>
                                        <br><small class="text-muted">{{ $payment->student->student_id }}</small>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @if($payment->invoice)
                                        <a href="{{ route('admin.invoices.show', $payment->invoice) }}" class="text-decoration-none">
                                            {{ $payment->invoice->invoice_number }}
                                        </a>
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
                                            <span class="badge bg-primary"><i class="fas fa-university me-1"></i>Bank Transfer</span>
                                            @break
                                        @case('cheque')
                                            <span class="badge bg-secondary"><i class="fas fa-money-check me-1"></i>Cheque</span>
                                            @break
                                        @default
                                            <span class="badge bg-dark">{{ ucfirst($payment->payment_method) }}</span>
                                    @endswitch
                                </td>
                                <td class="text-end fw-bold">RM {{ number_format($payment->amount, 2) }}</td>
                                <td>
                                    @switch($payment->status)
                                        @case('completed')
                                            <span class="badge bg-success">Completed</span>
                                            @break
                                        @case('pending')
                                            <span class="badge bg-warning text-dark">Pending</span>
                                            @break
                                        @case('failed')
                                            <span class="badge bg-danger">Failed</span>
                                            @break
                                        @case('refunded')
                                            <span class="badge bg-secondary">Refunded</span>
                                            @break
                                        @default
                                            <span class="badge bg-dark">{{ ucfirst($payment->status) }}</span>
                                    @endswitch
                                </td>
                                <td>{{ $payment->processedBy?->name ?? 'System' }}</td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.payments.show', $payment) }}"
                                           class="btn btn-outline-primary" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($payment->status === 'completed')
                                            <a href="{{ route('admin.payments.receipt', $payment) }}"
                                               class="btn btn-outline-success" title="View Receipt" target="_blank">
                                                <i class="fas fa-receipt"></i>
                                            </a>
                                            <a href="{{ route('admin.payments.download-receipt', $payment) }}"
                                               class="btn btn-outline-info" title="Download Receipt">
                                                <i class="fas fa-download"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <p class="text-muted mb-0">No payments found for the selected period.</p>
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
