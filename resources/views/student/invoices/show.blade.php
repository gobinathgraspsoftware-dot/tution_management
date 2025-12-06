@extends('layouts.app')

@section('title', 'Invoice Details')
@section('page-title', 'Invoice Details')

@push('styles')
<style>
    .invoice-card {
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    }
    .invoice-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 30px;
    }
    .invoice-body {
        padding: 30px;
    }
    .status-badge {
        font-size: 1rem;
        padding: 10px 20px;
        border-radius: 30px;
    }
    .status-paid { background: #28a745; }
    .status-pending { background: #ffc107; color: #333; }
    .status-overdue { background: #dc3545; }
    .status-partial { background: #17a2b8; }
    .detail-row {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #eee;
    }
    .detail-row:last-child {
        border-bottom: none;
    }
    .total-row {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 10px;
        margin-top: 15px;
    }
    .payment-timeline {
        position: relative;
        padding-left: 30px;
    }
    .payment-timeline::before {
        content: '';
        position: absolute;
        left: 10px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #28a745;
    }
    .payment-timeline-item {
        position: relative;
        padding-bottom: 20px;
    }
    .payment-timeline-item::before {
        content: '';
        position: absolute;
        left: -24px;
        top: 5px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #28a745;
        border: 2px solid white;
    }
    .info-alert {
        background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
        border-radius: 10px;
        padding: 20px;
        margin-top: 20px;
    }
</style>
@endpush

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1>
            <i class="fas fa-file-invoice-dollar me-2"></i> Invoice #{{ $invoice->invoice_number }}
        </h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('student.invoices.index') }}">Invoices</a></li>
                <li class="breadcrumb-item active">{{ $invoice->invoice_number }}</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('student.invoices.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i> Back to Invoices
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Main Invoice Card -->
        <div class="invoice-card">
            <div class="invoice-header">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h2 class="mb-1">INVOICE</h2>
                        <h4 class="mb-0 opacity-75">{{ $invoice->invoice_number }}</h4>
                    </div>
                    <div class="col-md-4 text-md-end">
                        @php
                            $isOverdue = $invoice->isOverdue();
                            $statusText = $isOverdue ? 'Overdue' : ucfirst($invoice->status);
                            $statusClass = $isOverdue ? 'overdue' : $invoice->status;
                        @endphp
                        <span class="status-badge status-{{ $statusClass }}">
                            {{ $statusText }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="invoice-body">
                <!-- Invoice Info -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="text-muted text-uppercase mb-2">Invoice Details</h6>
                        <p class="mb-1"><strong>Issue Date:</strong> {{ $invoice->created_at->format('d M Y') }}</p>
                        <p class="mb-1">
                            <strong>Due Date:</strong>
                            <span class="{{ $isOverdue ? 'text-danger fw-bold' : '' }}">
                                {{ $invoice->due_date->format('d M Y') }}
                            </span>
                            @if($isOverdue)
                                <br><small class="text-danger">({{ $invoice->due_date->diffInDays(now()) }} days overdue)</small>
                            @endif
                        </p>
                        <p class="mb-0"><strong>Type:</strong> {{ ucfirst($invoice->type) }} Fee</p>
                    </div>
                    <div class="col-md-6">
                        @if($invoice->billing_period_start && $invoice->billing_period_end)
                        <h6 class="text-muted text-uppercase mb-2">Billing Period</h6>
                        <p class="mb-0">
                            {{ $invoice->billing_period_start->format('d M Y') }} -
                            {{ $invoice->billing_period_end->format('d M Y') }}
                        </p>
                        @endif
                    </div>
                </div>

                <hr>

                <!-- Package Info -->
                @if($invoice->enrollment)
                <div class="mb-4">
                    <h6 class="text-muted text-uppercase mb-3">Package / Course</h6>
                    <div class="bg-light p-3 rounded">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h5 class="mb-0">{{ $invoice->enrollment->package->name ?? 'N/A' }}</h5>
                                @if($invoice->enrollment->class)
                                    <small class="text-muted">
                                        <i class="fas fa-chalkboard me-1"></i>
                                        {{ $invoice->enrollment->class->name }}
                                    </small>
                                @endif
                            </div>
                            <div class="col-md-4 text-end">
                                <span class="badge bg-info fs-6">
                                    {{ $invoice->enrollment->package->type ?? 'Standard' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Amount Breakdown -->
                <h6 class="text-muted text-uppercase mb-3">Amount Breakdown</h6>

                <div class="detail-row">
                    <span>{{ ucfirst($invoice->type) }} Fee</span>
                    <strong>RM {{ number_format($invoice->subtotal, 2) }}</strong>
                </div>

                @if($invoice->online_fee > 0)
                <div class="detail-row">
                    <span>Online Processing Fee</span>
                    <span>RM {{ number_format($invoice->online_fee, 2) }}</span>
                </div>
                @endif

                @if($invoice->discount > 0)
                <div class="detail-row text-success">
                    <span>
                        Discount
                        @if($invoice->discount_reason)
                            <small class="text-muted">({{ $invoice->discount_reason }})</small>
                        @endif
                    </span>
                    <span>- RM {{ number_format($invoice->discount, 2) }}</span>
                </div>
                @endif

                @if($invoice->tax > 0)
                <div class="detail-row">
                    <span>Tax</span>
                    <span>RM {{ number_format($invoice->tax, 2) }}</span>
                </div>
                @endif

                <!-- Total -->
                <div class="total-row">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5 class="mb-0">Total Amount</h5>
                        </div>
                        <div class="col-md-6 text-end">
                            <h3 class="mb-0">RM {{ number_format($invoice->total_amount, 2) }}</h3>
                        </div>
                    </div>

                    @if($invoice->paid_amount > 0)
                    <hr class="my-3">
                    <div class="row">
                        <div class="col-md-6">
                            <span class="text-muted">Amount Paid</span>
                        </div>
                        <div class="col-md-6 text-end text-success">
                            <strong>RM {{ number_format($invoice->paid_amount, 2) }}</strong>
                        </div>
                    </div>
                    @endif

                    @if($invoice->balance > 0)
                    <div class="row mt-2">
                        <div class="col-md-6">
                            <span class="text-danger fw-bold">Balance Due</span>
                        </div>
                        <div class="col-md-6 text-end">
                            <h4 class="mb-0 text-danger">RM {{ number_format($invoice->balance, 2) }}</h4>
                        </div>
                    </div>
                    @endif
                </div>

                @if($invoice->notes)
                <div class="mt-4">
                    <h6 class="text-muted text-uppercase mb-2">Notes</h6>
                    <p class="mb-0 bg-light p-3 rounded">{{ $invoice->notes }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Info Alert for Students -->
        <div class="info-alert">
            <div class="d-flex">
                <div class="me-3">
                    <i class="fas fa-info-circle text-primary fa-2x"></i>
                </div>
                <div>
                    <h6 class="mb-1">Payment Information</h6>
                    <p class="mb-0 small">
                        For payment inquiries or to make a payment, please contact your parent or guardian.
                        They can view payment options and complete the payment through the parent portal.
                        You can also visit the administration office for assistance.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Payment Status Card -->
        <div class="card mb-4">
            <div class="card-header bg-{{ $invoice->isPaid() ? 'success' : ($isOverdue ? 'danger' : 'warning') }} text-white">
                <i class="fas fa-info-circle me-2"></i> Payment Status
            </div>
            <div class="card-body text-center py-4">
                @if($invoice->isPaid())
                    <i class="fas fa-check-circle text-success fa-4x mb-3"></i>
                    <h4 class="text-success">Fully Paid</h4>
                    <p class="text-muted mb-0">This invoice has been paid in full.</p>
                @elseif($invoice->status === 'partial')
                    <i class="fas fa-clock text-info fa-4x mb-3"></i>
                    <h4 class="text-info">Partially Paid</h4>
                    <p class="mb-0">
                        <span class="text-muted">Paid:</span> RM {{ number_format($invoice->paid_amount, 2) }}<br>
                        <span class="text-danger">Balance:</span> RM {{ number_format($invoice->balance, 2) }}
                    </p>
                @elseif($isOverdue)
                    <i class="fas fa-exclamation-triangle text-danger fa-4x mb-3"></i>
                    <h4 class="text-danger">Overdue</h4>
                    <p class="mb-0">
                        This invoice is {{ $invoice->due_date->diffInDays(now()) }} days past due.
                    </p>
                @else
                    <i class="fas fa-hourglass-half text-warning fa-4x mb-3"></i>
                    <h4 class="text-warning">Pending</h4>
                    <p class="mb-0">
                        Due on {{ $invoice->due_date->format('d M Y') }}<br>
                        <small class="text-muted">{{ $invoice->due_date->diffForHumans() }}</small>
                    </p>
                @endif
            </div>
        </div>

        <!-- Payment History -->
        @if($invoice->payments->isNotEmpty())
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-history me-2"></i> Payment History
            </div>
            <div class="card-body">
                <div class="payment-timeline">
                    @foreach($invoice->payments as $payment)
                        <div class="payment-timeline-item">
                            <div class="d-flex justify-content-between mb-1">
                                <strong>RM {{ number_format($payment->amount, 2) }}</strong>
                                <span class="badge bg-success">{{ ucfirst($payment->status) }}</span>
                            </div>
                            <small class="text-muted d-block">
                                {{ $payment->payment_date->format('d M Y, h:i A') }}
                            </small>
                            <small class="text-muted">
                                Via {{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}
                            </small>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Installments -->
        @if($invoice->installments->isNotEmpty())
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-calendar-alt me-2"></i> Installment Plan
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @foreach($invoice->installments as $installment)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong>Installment {{ $installment->installment_number }}</strong>
                                <br>
                                <small class="text-muted">Due: {{ $installment->due_date->format('d M Y') }}</small>
                            </div>
                            <div class="text-end">
                                <strong>RM {{ number_format($installment->amount, 2) }}</strong>
                                <br>
                                @php
                                    $instStatus = match($installment->status) {
                                        'paid' => 'success',
                                        'overdue' => 'danger',
                                        default => 'secondary'
                                    };
                                @endphp
                                <span class="badge bg-{{ $instStatus }}">{{ ucfirst($installment->status) }}</span>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif

        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-tools me-2"></i> Quick Actions
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('student.invoices.download', $invoice) }}" class="btn btn-outline-primary">
                        <i class="fas fa-download me-2"></i> Download PDF
                    </a>
                    <a href="{{ route('student.invoices.print', $invoice) }}" class="btn btn-outline-secondary" target="_blank">
                        <i class="fas fa-print me-2"></i> Print Invoice
                    </a>
                </div>
            </div>
        </div>

        <!-- Contact Card -->
        <div class="card mt-4 bg-light">
            <div class="card-body">
                <h6><i class="fas fa-question-circle me-2"></i> Need Help?</h6>
                <p class="small mb-2">For payment-related questions:</p>
                <ul class="small mb-0 ps-3">
                    <li>Ask your parent or guardian</li>
                    <li>Visit the administration office</li>
                    <li>Contact us during office hours</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
