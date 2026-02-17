@extends('layouts.app')

@section('title', 'Invoice Details')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Invoice Details</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('student.invoices.index') }}">Invoices</a></li>
                    <li class="breadcrumb-item active">{{ $invoice->invoice_number }}</li>
                </ol>
            </nav>
        </div>
        <div>
            @if($invoice->balance > 0 && Route::has('student.payments.pay-online'))
                <a href="{{ route('student.payments.pay-online', $invoice) }}" class="btn btn-success">
                    <i class="fas fa-credit-card me-1"></i> Pay Now
                </a>
            @endif
            <a href="{{ route('student.invoices.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Invoice Information -->
        <div class="col-lg-8">
            <!-- Invoice Header Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-file-invoice me-2"></i>Invoice Information</h5>
                    @switch($invoice->status)
                        @case('paid')
                            <span class="badge bg-success fs-6">Paid</span>
                            @break
                        @case('pending')
                            <span class="badge bg-warning text-dark fs-6">Pending</span>
                            @break
                        @case('partial')
                            <span class="badge bg-info fs-6">Partially Paid</span>
                            @break
                        @case('overdue')
                            <span class="badge bg-danger fs-6">Overdue</span>
                            @break
                    @endswitch
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="text-muted" style="width: 40%;">Invoice Number</td>
                                    <td class="fw-bold">{{ $invoice->invoice_number }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Invoice Date</td>
                                    <td>{{ $invoice->created_at->format('d M Y') }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Due Date</td>
                                    <td>
                                        {{ $invoice->due_date ? $invoice->due_date->format('d M Y') : '-' }}
                                        @if($invoice->due_date && $invoice->due_date->isPast() && $invoice->status !== 'paid')
                                            <br><small class="text-danger">
                                                <i class="fas fa-exclamation-triangle"></i> Overdue by {{ $invoice->due_date->diffForHumans() }}
                                            </small>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="text-muted" style="width: 40%;">Type</td>
                                    <td>{{ $invoice->type_label ?? ucfirst($invoice->type) }}</td>
                                </tr>
                                @if($invoice->billing_period)
                                <tr>
                                    <td class="text-muted">Billing Period</td>
                                    <td>{{ $invoice->billing_period }}</td>
                                </tr>
                                @endif
                                @if($invoice->enrollment && $invoice->enrollment->package)
                                <tr>
                                    <td class="text-muted">Package</td>
                                    <td>{{ $invoice->enrollment->package->name }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Amount Breakdown -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-calculator me-2"></i>Amount Breakdown</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td class="text-muted">Subtotal</td>
                            <td class="text-end">RM {{ number_format($invoice->subtotal ?? $invoice->total_amount, 2) }}</td>
                        </tr>
                        @if($invoice->online_fee > 0)
                        <tr>
                            <td class="text-muted">Online Fee</td>
                            <td class="text-end">RM {{ number_format($invoice->online_fee, 2) }}</td>
                        </tr>
                        @endif
                        @if($invoice->discount > 0)
                        <tr>
                            <td class="text-muted">
                                Discount
                                @if($invoice->discount_reason)
                                    <br><small class="text-success">{{ $invoice->discount_reason }}</small>
                                @endif
                            </td>
                            <td class="text-end text-success">- RM {{ number_format($invoice->discount, 2) }}</td>
                        </tr>
                        @endif
                        <tr class="border-top">
                            <td class="fw-bold">Total Amount</td>
                            <td class="text-end fw-bold fs-5">RM {{ number_format($invoice->total_amount, 2) }}</td>
                        </tr>
                        <tr class="text-success">
                            <td>Paid Amount</td>
                            <td class="text-end">RM {{ number_format($invoice->paid_amount, 2) }}</td>
                        </tr>
                        <tr class="{{ $invoice->balance > 0 ? 'text-danger' : 'text-success' }} border-top">
                            <td class="fw-bold">Balance Due</td>
                            <td class="text-end fw-bold fs-4">RM {{ number_format($invoice->balance, 2) }}</td>
                        </tr>
                    </table>

                    @if($invoice->balance > 0)
                        <div class="alert alert-warning mt-3 mb-0">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Outstanding Balance:</strong> RM {{ number_format($invoice->balance, 2) }}
                            @if($invoice->due_date && $invoice->due_date->isPast())
                                <br><small>This invoice is overdue. Please make payment as soon as possible.</small>
                            @endif
                        </div>
                    @else
                        <div class="alert alert-success mt-3 mb-0">
                            <i class="fas fa-check-circle me-2"></i>
                            This invoice has been fully paid. Thank you!
                        </div>
                    @endif
                </div>
            </div>

            <!-- Payment History -->
            @if($invoice->payments && $invoice->payments->count() > 0)
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-history me-2"></i>Payment History</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Payment #</th>
                                        <th>Date</th>
                                        <th>Method</th>
                                        <th class="text-end">Amount</th>
                                        <th>Status</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($invoice->payments as $payment)
                                        <tr>
                                            <td>{{ $payment->payment_number }}</td>
                                            <td>{{ $payment->payment_date->format('d M Y') }}</td>
                                            <td>
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
                                                    @case('online_gateway')
                                                        <i class="fas fa-globe text-purple"></i> Online
                                                        @break
                                                    @default
                                                        {{ ucfirst($payment->payment_method) }}
                                                @endswitch
                                            </td>
                                            <td class="text-end fw-bold text-success">RM {{ number_format($payment->amount, 2) }}</td>
                                            <td>
                                                @if($payment->status === 'completed')
                                                    <span class="badge bg-success">Completed</span>
                                                @else
                                                    <span class="badge bg-warning">{{ ucfirst($payment->status) }}</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if(Route::has('student.payments.show'))
                                                    <a href="{{ route('student.payments.show', $payment) }}" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Installments (if applicable) -->
            @if($invoice->is_installment && $invoice->installments && $invoice->installments->count() > 0)
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Installment Plan</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Due Date</th>
                                        <th class="text-end">Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($invoice->installments as $installment)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $installment->due_date->format('d M Y') }}</td>
                                            <td class="text-end">RM {{ number_format($installment->amount, 2) }}</td>
                                            <td>
                                                @switch($installment->status)
                                                    @case('paid')
                                                        <span class="badge bg-success">Paid</span>
                                                        @break
                                                    @case('pending')
                                                        <span class="badge bg-warning text-dark">Pending</span>
                                                        @break
                                                    @case('overdue')
                                                        <span class="badge bg-danger">Overdue</span>
                                                        @break
                                                    @default
                                                        <span class="badge bg-secondary">{{ ucfirst($installment->status) }}</span>
                                                @endswitch
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Right Sidebar -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h6>
                </div>
                <div class="list-group list-group-flush">
                    @if($invoice->balance > 0 && Route::has('student.payments.pay-online'))
                        <a href="{{ route('student.payments.pay-online', $invoice) }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-credit-card text-success me-2"></i> Pay Online
                        </a>
                    @endif
                    <a href="{{ route('student.invoices.index') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-list me-2"></i> All Invoices
                    </a>
                    @if(Route::has('student.payments.index'))
                        <a href="{{ route('student.payments.index') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-money-bill-wave me-2"></i> Payment Records
                        </a>
                    @endif
                </div>
            </div>

            <!-- Package Information -->
            @if($invoice->enrollment && $invoice->enrollment->package)
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="fas fa-box me-2"></i>Package Details</h6>
                    </div>
                    <div class="card-body">
                        <h6 class="mb-2">{{ $invoice->enrollment->package->name }}</h6>
                        @if($invoice->enrollment->class)
                            <p class="mb-1">
                                <small class="text-muted">Class:</small> {{ $invoice->enrollment->class->name }}
                            </p>
                        @endif
                        <p class="mb-1">
                            <small class="text-muted">Enrolled:</small> {{ $invoice->enrollment->created_at->format('d M Y') }}
                        </p>
                        @if($invoice->enrollment->package->description)
                            <hr>
                            <p class="small text-muted mb-0">{{ $invoice->enrollment->package->description }}</p>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
