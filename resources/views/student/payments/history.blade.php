@extends('layouts.student')

@section('title', 'Payment History')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Payment History</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('student.payments.index') }}">Payments</a></li>
                    <li class="breadcrumb-item active">History</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filter History</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('student.payments.history') }}" method="GET">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">From Date</label>
                        <input type="date" name="date_from" class="form-control"
                               value="{{ $dateFrom->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">To Date</label>
                        <input type="date" name="date_to" class="form-control"
                               value="{{ $dateTo->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-1"></i> Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <!-- Monthly Summary -->
        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Monthly Summary</h5>
                </div>
                <div class="card-body p-0">
                    @if($monthlySummary->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($monthlySummary as $summary)
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fas fa-calendar-alt text-muted me-2"></i>
                                        {{ \Carbon\Carbon::createFromDate($summary->year, $summary->month, 1)->format('F Y') }}
                                    </div>
                                    <span class="badge bg-success rounded-pill fs-6">
                                        RM {{ number_format($summary->total, 2) }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">No payment data for the selected period.</p>
                        </div>
                    @endif
                </div>
                @if($monthlySummary->count() > 0)
                    <div class="card-footer bg-light">
                        <div class="d-flex justify-content-between">
                            <strong>Total:</strong>
                            <strong class="text-success">RM {{ number_format($monthlySummary->sum('total'), 2) }}</strong>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Quick Stats -->
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Period Overview</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted d-block">Selected Period</small>
                        <strong>{{ $dateFrom->format('d M Y') }} - {{ $dateTo->format('d M Y') }}</strong>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Total Payments</small>
                        <strong>{{ $payments->total() }} transactions</strong>
                    </div>
                    <div>
                        <small class="text-muted d-block">Total Amount</small>
                        <strong class="text-success fs-5">RM {{ number_format($monthlySummary->sum('total'), 2) }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment History Table -->
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-history me-2"></i>Payment Records</h5>
                    <span class="badge bg-primary">{{ $payments->total() }} records</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Payment #</th>
                                    <th>Date</th>
                                    <th>Invoice</th>
                                    <th class="text-end">Amount</th>
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
                                            <br>
                                            <small class="text-muted">
                                                @switch($payment->payment_method)
                                                    @case('cash')
                                                        <i class="fas fa-money-bill-wave text-success"></i> Cash
                                                        @break
                                                    @case('qr')
                                                        <i class="fas fa-qrcode text-info"></i> QR
                                                        @break
                                                    @case('bank_transfer')
                                                        <i class="fas fa-university text-primary"></i> Bank
                                                        @break
                                                    @default
                                                        {{ ucfirst($payment->payment_method) }}
                                                @endswitch
                                            </small>
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
                                        <td class="text-end">
                                            <span class="fw-bold text-success">RM {{ number_format($payment->amount, 2) }}</span>
                                            <br>
                                            @if($payment->status === 'completed')
                                                <span class="badge bg-success">Completed</span>
                                            @elseif($payment->status === 'pending')
                                                <span class="badge bg-warning text-dark">Pending</span>
                                            @else
                                                <span class="badge bg-secondary">{{ ucfirst($payment->status) }}</span>
                                            @endif
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
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4">
                                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                            <p class="text-muted mb-0">No payment records found for the selected period.</p>
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
    </div>
</div>
@endsection
