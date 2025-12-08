@extends('layouts.parent')

@section('title', 'Payment Details')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Payment Details</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('parent.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('parent.payments.index') }}">Payments</a></li>
                    <li class="breadcrumb-item active">{{ $payment->payment_number }}</li>
                </ol>
            </nav>
        </div>
        <div>
            @if($payment->status === 'completed')
                <a href="{{ route('parent.payments.receipt', $payment) }}" class="btn btn-success" target="_blank">
                    <i class="fas fa-receipt me-1"></i> View Receipt
                </a>
                <a href="{{ route('parent.payments.download-receipt', $payment) }}" class="btn btn-info">
                    <i class="fas fa-download me-1"></i> Download
                </a>
            @endif
            <a href="{{ route('parent.payments.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Payment Information -->
        <div class="col-lg-8">
            <!-- Payment Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i>Payment Information</h5>
                    @switch($payment->status)
                        @case('completed')
                            <span class="badge bg-success fs-6">Completed</span>
                            @break
                        @case('pending')
                            <span class="badge bg-warning text-dark fs-6">Processing</span>
                            @break
                        @case('refunded')
                            <span class="badge bg-secondary fs-6">Refunded</span>
                            @break
                    @endswitch
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="text-muted" style="width: 40%;">Payment Number</td>
                                    <td class="fw-bold">{{ $payment->payment_number }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Payment Date</td>
                                    <td>{{ $payment->payment_date->format('d M Y') }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Payment Method</td>
                                    <td>
                                        @switch($payment->payment_method)
                                            @case('cash')
                                                <span class="badge bg-success"><i class="fas fa-money-bill-wave me-1"></i>Cash</span>
                                                @break
                                            @case('qr')
                                                <span class="badge bg-info"><i class="fas fa-qrcode me-1"></i>QR Payment</span>
                                                @break
                                            @case('bank_transfer')
                                                <span class="badge bg-primary"><i class="fas fa-university me-1"></i>Bank Transfer</span>
                                                @break
                                            @case('online_gateway')
                                                <span class="badge bg-purple"><i class="fas fa-globe me-1"></i>Online</span>
                                                @break
                                            @default
                                                <span class="badge bg-secondary">{{ ucfirst($payment->payment_method) }}</span>
                                        @endswitch
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="text-muted" style="width: 40%;">Amount Paid</td>
                                    <td class="fw-bold text-success fs-4">RM {{ number_format($payment->amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Reference</td>
                                    <td>{{ $payment->reference_number ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Child Information -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-child me-2"></i>Child Information</h5>
                </div>
                <div class="card-body">
                    @if($payment->invoice && $payment->invoice->student)
                        @php $student = $payment->invoice->student; @endphp
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center"
                                     style="width: 60px; height: 60px; font-size: 24px;">
                                    {{ strtoupper(substr($student->user->name, 0, 1)) }}
                                </div>
                            </div>
                            <div class="col">
                                <h5 class="mb-1">{{ $student->user->name }}</h5>
                                <p class="text-muted mb-0">
                                    Student ID: {{ $student->student_id }}<br>
                                    @if($payment->invoice->enrollment && $payment->invoice->enrollment->package)
                                        Package: {{ $payment->invoice->enrollment->package->name }}
                                    @endif
                                </p>
                            </div>
                        </div>
                    @else
                        <p class="text-muted mb-0">Student information not available.</p>
                    @endif
                </div>
            </div>

            <!-- Invoice Information -->
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-file-invoice me-2"></i>Invoice Information</h5>
                </div>
                <div class="card-body">
                    @if($payment->invoice)
                        @php $invoice = $payment->invoice; @endphp
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td class="text-muted" style="width: 40%;">Invoice Number</td>
                                        <td class="fw-bold">{{ $invoice->invoice_number }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Type</td>
                                        <td>{{ $invoice->type_label ?? ucfirst($invoice->type) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Period</td>
                                        <td>{{ $invoice->billing_period ?? '-' }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td class="text-muted" style="width: 40%;">Total Amount</td>
                                        <td class="fw-bold">RM {{ number_format($invoice->total_amount, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Paid Amount</td>
                                        <td class="text-success">RM {{ number_format($invoice->paid_amount, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Balance</td>
                                        <td class="{{ $invoice->balance > 0 ? 'text-danger fw-bold' : 'text-success' }}">
                                            RM {{ number_format($invoice->balance, 2) }}
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        @if($invoice->balance > 0)
                            <div class="alert alert-warning mb-0 mt-3">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                There is still an outstanding balance of <strong>RM {{ number_format($invoice->balance, 2) }}</strong>
                                for this invoice. Please ensure timely payment to avoid any late fees.
                            </div>
                        @else
                            <div class="alert alert-success mb-0 mt-3">
                                <i class="fas fa-check-circle me-2"></i>
                                This invoice has been fully paid. Thank you!
                            </div>
                        @endif
                    @else
                        <p class="text-muted mb-0">Invoice information not available.</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Sidebar - Receipt Preview -->
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-receipt me-2"></i>Receipt</h5>
                </div>
                <div class="card-body">
                    @if($payment->status === 'completed' && isset($receiptData))
                        <div class="text-center border-bottom pb-3 mb-3">
                            <h6 class="mb-1">{{ $receiptData['company']['name'] ?? 'Arena Matriks Edu Group' }}</h6>
                            <small class="text-muted">Official Payment Receipt</small>
                        </div>

                        <div class="bg-light p-3 rounded text-center mb-3">
                            <small class="text-muted d-block">Amount Paid</small>
                            <h3 class="text-success mb-0">RM {{ number_format($payment->amount, 2) }}</h3>
                        </div>

                        <table class="table table-sm table-borderless">
                            <tr>
                                <td class="text-muted">Receipt No:</td>
                                <td class="text-end">{{ $receiptData['receipt_number'] ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Date:</td>
                                <td class="text-end">{{ $payment->payment_date->format('d M Y') }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Method:</td>
                                <td class="text-end">{{ ucfirst($payment->payment_method) }}</td>
                            </tr>
                        </table>

                        <div class="d-grid gap-2 mt-4">
                            <a href="{{ route('parent.payments.receipt', $payment) }}" class="btn btn-success" target="_blank">
                                <i class="fas fa-eye me-1"></i> View Full Receipt
                            </a>
                            <a href="{{ route('parent.payments.download-receipt', $payment) }}" class="btn btn-outline-primary">
                                <i class="fas fa-download me-1"></i> Download PDF
                            </a>
                        </div>
                    @elseif($payment->status === 'pending')
                        <div class="text-center py-4">
                            <i class="fas fa-clock fa-3x text-warning mb-3"></i>
                            <h6>Payment Processing</h6>
                            <p class="text-muted mb-0">Your payment is being verified. Receipt will be available once completed.</p>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-times-circle fa-3x text-secondary mb-3"></i>
                            <p class="text-muted mb-0">Receipt not available for this payment.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Links -->
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="fas fa-link me-2"></i>Quick Links</h6>
                </div>
                <div class="list-group list-group-flush">
                    <a href="{{ route('parent.payments.index') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-list me-2"></i> All Payments
                    </a>
                    <a href="{{ route('parent.payments.history') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-history me-2"></i> Payment History
                    </a>
                    @if(Route::has('parent.invoices.index'))
                        <a href="{{ route('parent.invoices.index') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-file-invoice me-2"></i> View Invoices
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
