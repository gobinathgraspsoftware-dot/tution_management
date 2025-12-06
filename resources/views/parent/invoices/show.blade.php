@extends('layouts.app')

@section('title', 'Invoice Details')
@section('page-title', 'Invoice Details')

@push('styles')
<style>
    .invoice-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 15px 15px 0 0;
        padding: 30px;
    }
    .invoice-body {
        background: white;
        border-radius: 0 0 15px 15px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    }
    .invoice-table th {
        background-color: #f8f9fa;
        font-weight: 600;
    }
    .status-paid {
        background: #28a745;
        color: white;
    }
    .status-pending {
        background: #ffc107;
        color: #333;
    }
    .status-overdue {
        background: #dc3545;
        color: white;
    }
    .status-partial {
        background: #17a2b8;
        color: white;
    }
    .total-section {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 20px;
    }
    .payment-item {
        border-left: 4px solid #28a745;
        padding-left: 15px;
        margin-bottom: 15px;
    }
    .installment-item {
        border-left: 4px solid #17a2b8;
        padding-left: 15px;
        margin-bottom: 10px;
    }
    .qr-code {
        max-width: 150px;
        border: 3px solid #667eea;
        border-radius: 10px;
        padding: 10px;
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
                <li class="breadcrumb-item"><a href="{{ route('parent.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('parent.invoices.index') }}">Invoices</a></li>
                <li class="breadcrumb-item active">{{ $invoice->invoice_number }}</li>
            </ol>
        </nav>
    </div>
    <div>
        <a href="{{ route('parent.invoices.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i> Back to Invoices
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Invoice Card -->
        <div class="mb-4">
            <div class="invoice-header">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h2 class="mb-1">INVOICE</h2>
                        <h4 class="mb-0">{{ $invoice->invoice_number }}</h4>
                    </div>
                    <div class="col-md-6 text-md-end">
                        @php
                            $isOverdue = $invoice->isOverdue();
                            $statusText = $isOverdue ? 'Overdue' : ucfirst($invoice->status);
                            $statusClass = $isOverdue ? 'overdue' : $invoice->status;
                        @endphp
                        <span class="badge status-{{ $statusClass }} fs-5 px-4 py-2">
                            {{ $statusText }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="invoice-body p-4">
                <!-- Student & Billing Info -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-2">BILLED TO</h6>
                        <h5 class="mb-1">{{ $invoice->student->user->name ?? 'N/A' }}</h5>
                        <p class="mb-0 text-muted">
                            Student ID: {{ $invoice->student->student_id ?? 'N/A' }}
                        </p>
                        @if($invoice->student->parent)
                            <p class="mb-0 text-muted">
                                Parent: {{ $invoice->student->parent->user->name ?? 'N/A' }}
                            </p>
                        @endif
                    </div>
                    <div class="col-md-6 text-md-end">
                        <h6 class="text-muted mb-2">INVOICE DETAILS</h6>
                        <p class="mb-0"><strong>Issue Date:</strong> {{ $invoice->created_at->format('d M Y') }}</p>
                        <p class="mb-0"><strong>Due Date:</strong>
                            <span class="{{ $invoice->isOverdue() ? 'text-danger fw-bold' : '' }}">
                                {{ $invoice->due_date->format('d M Y') }}
                            </span>
                        </p>
                        @if($invoice->billing_period_start && $invoice->billing_period_end)
                            <p class="mb-0"><strong>Billing Period:</strong>
                                {{ $invoice->billing_period_start->format('d M Y') }} -
                                {{ $invoice->billing_period_end->format('d M Y') }}
                            </p>
                        @endif
                    </div>
                </div>

                <hr>

                <!-- Package/Course Info -->
                @if($invoice->enrollment)
                <div class="mb-4">
                    <h6 class="text-muted mb-2">PACKAGE / COURSE</h6>
                    <div class="d-flex justify-content-between align-items-center bg-light p-3 rounded">
                        <div>
                            <h5 class="mb-0">{{ $invoice->enrollment->package->name ?? 'N/A' }}</h5>
                            <small class="text-muted">{{ ucfirst($invoice->type) }} Fee</small>
                        </div>
                        @if($invoice->enrollment->class)
                            <span class="badge bg-info">
                                {{ $invoice->enrollment->class->name ?? 'N/A' }}
                            </span>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Amount Breakdown -->
                <div class="table-responsive">
                    <table class="table invoice-table">
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th class="text-end">Amount (RM)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>{{ ucfirst($invoice->type) }} Fee</td>
                                <td class="text-end">{{ number_format($invoice->subtotal, 2) }}</td>
                            </tr>
                            @if($invoice->online_fee > 0)
                            <tr>
                                <td>Online Processing Fee</td>
                                <td class="text-end">{{ number_format($invoice->online_fee, 2) }}</td>
                            </tr>
                            @endif
                            @if($invoice->discount > 0)
                            <tr class="text-success">
                                <td>
                                    Discount
                                    @if($invoice->discount_reason)
                                        <br><small class="text-muted">{{ $invoice->discount_reason }}</small>
                                    @endif
                                </td>
                                <td class="text-end">-{{ number_format($invoice->discount, 2) }}</td>
                            </tr>
                            @endif
                            @if($invoice->tax > 0)
                            <tr>
                                <td>Tax</td>
                                <td class="text-end">{{ number_format($invoice->tax, 2) }}</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                <!-- Total Section -->
                <div class="total-section">
                    <div class="row">
                        <div class="col-md-6">
                            @if($invoice->paid_amount > 0)
                                <p class="mb-1"><strong>Amount Paid:</strong>
                                    <span class="text-success">RM {{ number_format($invoice->paid_amount, 2) }}</span>
                                </p>
                            @endif
                        </div>
                        <div class="col-md-6 text-end">
                            <h5 class="mb-1">Total Amount: <strong>RM {{ number_format($invoice->total_amount, 2) }}</strong></h5>
                            @if($invoice->balance > 0)
                                <h4 class="text-danger mb-0">Balance Due: RM {{ number_format($invoice->balance, 2) }}</h4>
                            @else
                                <h4 class="text-success mb-0"><i class="fas fa-check-circle me-2"></i> PAID IN FULL</h4>
                            @endif
                        </div>
                    </div>
                </div>

                @if($invoice->notes)
                <div class="mt-4">
                    <h6 class="text-muted">Notes</h6>
                    <p class="mb-0">{{ $invoice->notes }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Payment Status -->
        <div class="card mb-4">
            <div class="card-header bg-{{ $invoice->isPaid() ? 'success' : ($invoice->isOverdue() ? 'danger' : 'warning') }} text-white">
                <i class="fas fa-info-circle me-2"></i> Payment Status
            </div>
            <div class="card-body text-center">
                @if($invoice->isPaid())
                    <i class="fas fa-check-circle text-success fa-4x mb-3"></i>
                    <h4>Paid in Full</h4>
                    <p class="text-muted mb-0">Thank you for your payment!</p>
                @elseif($invoice->isOverdue())
                    <i class="fas fa-exclamation-triangle text-danger fa-4x mb-3"></i>
                    <h4 class="text-danger">Payment Overdue</h4>
                    <p class="mb-2">{{ $invoice->due_date->diffInDays(now()) }} days past due</p>
                    <p class="text-muted mb-0">Please make payment as soon as possible.</p>
                @else
                    <i class="fas fa-clock text-warning fa-4x mb-3"></i>
                    <h4>Payment Pending</h4>
                    <p class="mb-0">Due: {{ $invoice->due_date->format('d M Y') }}</p>
                    <p class="text-muted">{{ $invoice->due_date->diffForHumans() }}</p>
                @endif
            </div>
        </div>

        <!-- Payment Methods -->
        @if(!$invoice->isPaid())
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-credit-card me-2"></i> Payment Options
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('parent.payment.online', $invoice) }}" class="btn btn-primary">
                        <i class="fas fa-globe me-2"></i> Pay Online
                    </a>
                    <button class="btn btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#bankDetails">
                        <i class="fas fa-university me-2"></i> Bank Transfer Details
                    </button>
                </div>

                <div class="collapse mt-3" id="bankDetails">
                    <div class="bg-light p-3 rounded">
                        <h6>Bank Transfer Details</h6>
                        <p class="mb-1"><strong>Bank:</strong> Maybank</p>
                        <p class="mb-1"><strong>Account Name:</strong> Arena Matriks Edu Group</p>
                        <p class="mb-1"><strong>Account No:</strong> 5123 4567 8901</p>
                        <p class="mb-0"><strong>Reference:</strong> {{ $invoice->invoice_number }}</p>
                        <hr>
                        <small class="text-muted">
                            Please include the invoice number as payment reference.
                            Upload your payment receipt after transfer.
                        </small>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Payment History -->
        @if($invoice->payments->isNotEmpty())
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-history me-2"></i> Payment History
            </div>
            <div class="card-body">
                @foreach($invoice->payments as $payment)
                    <div class="payment-item">
                        <div class="d-flex justify-content-between">
                            <strong>RM {{ number_format($payment->amount, 2) }}</strong>
                            <span class="badge bg-success">{{ ucfirst($payment->status) }}</span>
                        </div>
                        <small class="text-muted d-block">
                            {{ $payment->payment_date->format('d M Y, h:i A') }}
                        </small>
                        <small class="text-muted">
                            {{ ucfirst($payment->payment_method) }}
                            @if($payment->reference_number)
                                - Ref: {{ $payment->reference_number }}
                            @endif
                        </small>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Installment Plan -->
        @if($invoice->installments->isNotEmpty())
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-calendar-alt me-2"></i> Installment Plan
            </div>
            <div class="card-body">
                @foreach($invoice->installments as $installment)
                    <div class="installment-item">
                        <div class="d-flex justify-content-between">
                            <span>Installment #{{ $installment->installment_number }}</span>
                            <strong>RM {{ number_format($installment->amount, 2) }}</strong>
                        </div>
                        <small class="text-muted d-block">
                            Due: {{ $installment->due_date->format('d M Y') }}
                        </small>
                        <span class="badge bg-{{ $installment->status === 'paid' ? 'success' : ($installment->due_date->isPast() ? 'danger' : 'secondary') }}">
                            {{ ucfirst($installment->status) }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Actions -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-tools me-2"></i> Actions
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('parent.invoices.download', $invoice) }}" class="btn btn-outline-primary">
                        <i class="fas fa-download me-2"></i> Download PDF
                    </a>
                    <a href="{{ route('parent.invoices.print', $invoice) }}" class="btn btn-outline-secondary" target="_blank">
                        <i class="fas fa-print me-2"></i> Print Invoice
                    </a>
                    @if(!$invoice->isPaid())
                    <a href="#" class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#uploadReceiptModal">
                        <i class="fas fa-upload me-2"></i> Upload Receipt
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Upload Receipt Modal -->
<div class="modal fade" id="uploadReceiptModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-upload me-2"></i> Upload Payment Receipt</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('parent.invoices.upload-receipt', $invoice) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="receipt" class="form-label">Receipt Image/PDF</label>
                        <input type="file" class="form-control" name="receipt" id="receipt" required
                               accept="image/*,.pdf">
                        <small class="text-muted">Upload your bank transfer receipt or payment confirmation.</small>
                    </div>
                    <div class="mb-3">
                        <label for="reference_number" class="form-label">Reference Number</label>
                        <input type="text" class="form-control" name="reference_number" id="reference_number"
                               placeholder="Transaction/Reference number">
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes (Optional)</label>
                        <textarea class="form-control" name="notes" id="notes" rows="2"
                                  placeholder="Any additional information..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload me-2"></i> Upload Receipt
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
