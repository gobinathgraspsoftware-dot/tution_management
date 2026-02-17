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
                    <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('student.invoices.index') }}">Invoices</a></li>
                    <li class="breadcrumb-item active">History</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('student.invoices.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Invoices
            </a>
        </div>
    </div>

    <!-- Paid Invoices -->
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-check-circle me-2 text-success"></i>Paid Invoices</h5>
            <span class="badge bg-success">{{ $invoices->total() }} invoices</span>
        </div>
        <div class="card-body p-0">
            @if($invoices->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Invoice #</th>
                                <th>Package</th>
                                <th>Paid Date</th>
                                <th class="text-end">Amount</th>
                                <th>Payments</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoices as $invoice)
                                <tr>
                                    <td>
                                        <a href="{{ route('student.invoices.show', $invoice) }}" class="fw-bold text-decoration-none">
                                            {{ $invoice->invoice_number }}
                                        </a>
                                        <br>
                                        <small class="text-muted">{{ $invoice->type_label ?? ucfirst($invoice->type) }}</small>
                                    </td>
                                    <td>
                                        @if($invoice->enrollment && $invoice->enrollment->package)
                                            {{ $invoice->enrollment->package->name }}
                                            @if($invoice->billing_period)
                                                <br><small class="text-muted">{{ $invoice->billing_period }}</small>
                                            @endif
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $invoice->updated_at->format('d M Y') }}
                                        <br>
                                        <small class="text-muted">{{ $invoice->updated_at->format('h:i A') }}</small>
                                    </td>
                                    <td class="text-end">
                                        <strong class="text-success">RM {{ number_format($invoice->total_amount, 2) }}</strong>
                                        <br>
                                        <small class="text-muted">Paid: RM {{ number_format($invoice->paid_amount, 2) }}</small>
                                    </td>
                                    <td>
                                        @if($invoice->payments && $invoice->payments->count() > 0)
                                            <span class="badge bg-info">{{ $invoice->payments->count() }} payment(s)</span>
                                            <br>
                                            @foreach($invoice->payments->take(2) as $payment)
                                                <small class="text-muted d-block">
                                                    {{ $payment->payment_date->format('d M Y') }} - RM {{ number_format($payment->amount, 2) }}
                                                </small>
                                            @endforeach
                                            @if($invoice->payments->count() > 2)
                                                <small class="text-muted">+{{ $invoice->payments->count() - 2 }} more</small>
                                            @endif
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('student.invoices.show', $invoice) }}" 
                                               class="btn btn-outline-primary" 
                                               title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($invoice->payments && $invoice->payments->count() > 0)
                                                @php $firstPayment = $invoice->payments->first(); @endphp
                                                @if(Route::has('student.payments.receipt'))
                                                    <a href="{{ route('student.payments.receipt', $firstPayment) }}" 
                                                       class="btn btn-outline-success" 
                                                       title="View Receipt"
                                                       target="_blank">
                                                        <i class="fas fa-receipt"></i>
                                                    </a>
                                                @endif
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="3" class="text-end"><strong>Total Paid:</strong></td>
                                <td class="text-end">
                                    <strong class="text-success fs-5">
                                        RM {{ number_format($invoices->sum('total_amount'), 2) }}
                                    </strong>
                                </td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                    <h5>No Payment History</h5>
                    <p class="text-muted mb-0">You don't have any paid invoices yet.</p>
                </div>
            @endif
        </div>
        @if($invoices->hasPages())
            <div class="card-footer bg-white">
                {{ $invoices->links() }}
            </div>
        @endif
    </div>

    <!-- Summary Information -->
    @if($invoices->count() > 0)
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card shadow-sm bg-light">
                    <div class="card-body">
                        <h6 class="mb-3"><i class="fas fa-info-circle me-2"></i>Payment Summary</h6>
                        <div class="row">
                            <div class="col-6">
                                <p class="mb-2"><small class="text-muted">Total Invoices Paid:</small></p>
                                <h4 class="text-success">{{ $invoices->total() }}</h4>
                            </div>
                            <div class="col-6">
                                <p class="mb-2"><small class="text-muted">Total Amount Paid:</small></p>
                                <h4 class="text-success">RM {{ number_format($invoices->sum('total_amount'), 2) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card shadow-sm bg-light">
                    <div class="card-body">
                        <h6 class="mb-3"><i class="fas fa-link me-2"></i>Quick Links</h6>
                        <div class="d-grid gap-2">
                            <a href="{{ route('student.invoices.index') }}" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-list me-2"></i> View All Invoices
                            </a>
                            @if(Route::has('student.payments.index'))
                                <a href="{{ route('student.payments.index') }}" class="btn btn-outline-success btn-sm">
                                    <i class="fas fa-money-bill-wave me-2"></i> View Payments
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
