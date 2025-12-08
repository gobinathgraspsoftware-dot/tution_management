@extends('layouts.staff')

@section('title', 'Payment Details')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Payment Details</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('staff.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('staff.payments.index') }}">Payments</a></li>
                    <li class="breadcrumb-item active">{{ $payment->payment_number }}</li>
                </ol>
            </nav>
        </div>
        <div>
            @if($payment->status === 'completed')
                <a href="{{ route('staff.payments.receipt', $payment) }}" class="btn btn-success" target="_blank">
                    <i class="fas fa-receipt me-1"></i> View Receipt
                </a>
                <a href="{{ route('staff.payments.print-receipt', $payment) }}" class="btn btn-info" target="_blank">
                    <i class="fas fa-print me-1"></i> Print
                </a>
            @endif
            <a href="{{ route('staff.payments.index') }}" class="btn btn-outline-secondary">
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
                            <span class="badge bg-warning text-dark fs-6">Pending Verification</span>
                            @break
                        @case('failed')
                            <span class="badge bg-danger fs-6">Failed</span>
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
                                    <td class="text-muted">Payment Time</td>
                                    <td>{{ $payment->created_at->format('h:i A') }}</td>
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
                                            @case('cheque')
                                                <span class="badge bg-secondary"><i class="fas fa-money-check me-1"></i>Cheque</span>
                                                @break
                                            @default
                                                <span class="badge bg-dark">{{ ucfirst($payment->payment_method) }}</span>
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
                                    <td class="text-muted">Reference Number</td>
                                    <td>{{ $payment->reference_number ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Processed By</td>
                                    <td>{{ $payment->processedBy?->name ?? 'System' }}</td>
                                </tr>
                                @if($payment->notes)
                                <tr>
                                    <td class="text-muted">Notes</td>
                                    <td>{{ $payment->notes }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>

                    <!-- QR Payment Screenshot -->
                    @if($payment->payment_method === 'qr' && $payment->screenshot_path)
                        <div class="border-top pt-3 mt-3">
                            <h6><i class="fas fa-image me-2"></i>Payment Screenshot</h6>
                            <a href="{{ asset('storage/' . $payment->screenshot_path) }}" target="_blank">
                                <img src="{{ asset('storage/' . $payment->screenshot_path) }}"
                                     alt="Payment Screenshot" class="img-thumbnail" style="max-height: 300px;">
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Student Information -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-user-graduate me-2"></i>Student Information</h5>
                </div>
                <div class="card-body">
                    @if($payment->invoice && $payment->invoice->student)
                        @php $student = $payment->invoice->student; @endphp
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td class="text-muted" style="width: 40%;">Name</td>
                                        <td class="fw-bold">{{ $student->user->name }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Student ID</td>
                                        <td>{{ $student->student_id }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">IC Number</td>
                                        <td>{{ $student->ic_number ?? '-' }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                @if($student->parent)
                                    <table class="table table-borderless">
                                        <tr>
                                            <td class="text-muted" style="width: 40%;">Parent/Guardian</td>
                                            <td>{{ $student->parent->user->name ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Contact</td>
                                            <td>{{ $student->parent->whatsapp_number ?? '-' }}</td>
                                        </tr>
                                    </table>
                                @endif
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
                                        <td class="text-muted">Invoice Type</td>
                                        <td>{{ $invoice->type_label ?? ucfirst($invoice->type) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Billing Period</td>
                                        <td>{{ $invoice->billing_period ?? '-' }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td class="text-muted" style="width: 40%;">Total Amount</td>
                                        <td>RM {{ number_format($invoice->total_amount, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Paid Amount</td>
                                        <td class="text-success">RM {{ number_format($invoice->paid_amount, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Outstanding</td>
                                        <td class="fw-bold {{ $invoice->balance > 0 ? 'text-danger' : 'text-success' }}">
                                            RM {{ number_format($invoice->balance, 2) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Invoice Status</td>
                                        <td>
                                            @switch($invoice->status)
                                                @case('paid')
                                                    <span class="badge bg-success">Paid</span>
                                                    @break
                                                @case('partial')
                                                    <span class="badge bg-info">Partial</span>
                                                    @break
                                                @case('overdue')
                                                    <span class="badge bg-danger">Overdue</span>
                                                    @break
                                                @default
                                                    <span class="badge bg-warning text-dark">{{ ucfirst($invoice->status) }}</span>
                                            @endswitch
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
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
                    <h5 class="mb-0"><i class="fas fa-receipt me-2"></i>Receipt Preview</h5>
                </div>
                <div class="card-body">
                    @if(isset($receiptData))
                        <div class="text-center border-bottom pb-3 mb-3">
                            <h6 class="mb-1">{{ $receiptData['company']['name'] ?? 'Arena Matriks Edu Group' }}</h6>
                            <small class="text-muted d-block">{{ $receiptData['company']['address'] ?? '' }}</small>
                        </div>

                        <table class="table table-sm table-borderless">
                            <tr>
                                <td class="text-muted">Receipt No:</td>
                                <td class="text-end fw-bold">{{ $receiptData['receipt_number'] ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Date:</td>
                                <td class="text-end">{{ $receiptData['payment_details']['date'] ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Student:</td>
                                <td class="text-end">{{ $receiptData['student']['name'] ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Invoice:</td>
                                <td class="text-end">{{ $receiptData['invoice_details']['number'] ?? '-' }}</td>
                            </tr>
                        </table>

                        <div class="bg-light p-3 rounded text-center mb-3">
                            <small class="text-muted d-block">Amount Paid</small>
                            <h3 class="text-success mb-0">RM {{ number_format($receiptData['payment_details']['amount'] ?? 0, 2) }}</h3>
                        </div>

                        <table class="table table-sm table-borderless">
                            <tr>
                                <td class="text-muted">Method:</td>
                                <td class="text-end">{{ $receiptData['payment_details']['method'] ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Balance:</td>
                                <td class="text-end {{ ($receiptData['invoice_details']['balance'] ?? 0) > 0 ? 'text-danger' : 'text-success' }}">
                                    RM {{ number_format($receiptData['invoice_details']['balance'] ?? 0, 2) }}
                                </td>
                            </tr>
                        </table>

                        <div class="text-center text-muted">
                            <small>{{ $receiptData['generated_at'] ?? '' }}</small>
                        </div>
                    @else
                        <p class="text-muted text-center mb-0">Receipt preview not available.</p>
                    @endif
                </div>
                <div class="card-footer bg-white">
                    @if($payment->status === 'completed')
                        <div class="d-grid gap-2">
                            <a href="{{ route('staff.payments.receipt', $payment) }}" class="btn btn-success" target="_blank">
                                <i class="fas fa-receipt me-1"></i> View Full Receipt
                            </a>
                            <a href="{{ route('staff.payments.print-receipt', $payment) }}" class="btn btn-outline-primary" target="_blank">
                                <i class="fas fa-print me-1"></i> Print Receipt
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
