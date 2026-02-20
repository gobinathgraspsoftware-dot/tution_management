@extends('layouts.app')

@section('title', 'Payment Details - ' . $payment->payment_number)
@section('page-title', 'Payment Details')

@section('content')
<div class="container-fluid">

    <!-- Page Header -->
    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="fas fa-receipt me-2"></i> Payment Details</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.payments.index') }}">Payments</a></li>
                    <li class="breadcrumb-item active">{{ $payment->payment_number }}</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.payments.receipt', $payment) }}" class="btn btn-outline-success" target="_blank">
                <i class="fas fa-receipt me-1"></i> View Receipt
            </a>
            <a href="{{ route('admin.payments.print-receipt', $payment) }}" class="btn btn-outline-secondary" target="_blank">
                <i class="fas fa-print me-1"></i> Print
            </a>
            <a href="{{ route('admin.payments.download-receipt', $payment) }}" class="btn btn-outline-primary">
                <i class="fas fa-download me-1"></i> Download PDF
            </a>
            <a href="{{ route('admin.payments.index') }}" class="btn btn-outline-dark">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <!-- Payment Status Banner -->
    @php
        $statusConfig = [
            'completed' => ['bg' => 'success', 'icon' => 'check-circle', 'text' => 'Payment Completed'],
            'pending'   => ['bg' => 'warning', 'icon' => 'clock', 'text' => 'Payment Pending Verification'],
            'failed'    => ['bg' => 'danger', 'icon' => 'times-circle', 'text' => 'Payment Failed'],
            'refunded'  => ['bg' => 'secondary', 'icon' => 'undo', 'text' => 'Payment Refunded'],
        ];
        $sc = $statusConfig[$payment->status] ?? ['bg' => 'secondary', 'icon' => 'question-circle', 'text' => ucfirst($payment->status)];
    @endphp
    <div class="alert alert-{{ $sc['bg'] }} d-flex align-items-center mb-4" role="alert">
        <i class="fas fa-{{ $sc['icon'] }} fa-lg me-3"></i>
        <div>
            <strong>{{ $sc['text'] }}</strong>
            <span class="ms-2">|</span>
            <span class="ms-2">{{ $payment->payment_number }}</span>
            <span class="ms-2">|</span>
            <span class="ms-2">RM {{ number_format($payment->amount, 2) }}</span>
            <span class="ms-2">|</span>
            <span class="ms-2">{{ $payment->payment_date ? $payment->payment_date->format('d M Y, h:i A') : 'N/A' }}</span>
        </div>
    </div>

    <div class="row">
        <!-- Left Column: Payment & Invoice Details -->
        <div class="col-lg-8">

            <!-- Payment Information Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-money-bill-wave text-success me-2"></i>Payment Information</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <table class="table table-borderless mb-0">
                                <tr>
                                    <td class="text-muted" style="width: 160px;">Payment Number</td>
                                    <td><strong>{{ $payment->payment_number }}</strong></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Payment Date</td>
                                    <td>{{ $payment->payment_date ? $payment->payment_date->format('d M Y') : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Payment Time</td>
                                    <td>{{ $payment->created_at ? $payment->created_at->format('h:i A') : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Amount</td>
                                    <td><span class="fs-5 fw-bold text-success">RM {{ number_format($payment->amount, 2) }}</span></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless mb-0">
                                <tr>
                                    <td class="text-muted" style="width: 160px;">Payment Method</td>
                                    <td>
                                        @php
                                            $methodBadges = [
                                                'cash' => ['bg' => 'success', 'icon' => 'money-bill'],
                                                'qr' => ['bg' => 'info', 'icon' => 'qrcode'],
                                                'online_gateway' => ['bg' => 'primary', 'icon' => 'credit-card'],
                                                'bank_transfer' => ['bg' => 'secondary', 'icon' => 'university'],
                                                'cheque' => ['bg' => 'dark', 'icon' => 'money-check'],
                                            ];
                                            $mb = $methodBadges[$payment->payment_method] ?? ['bg' => 'secondary', 'icon' => 'credit-card'];
                                        @endphp
                                        <span class="badge bg-{{ $mb['bg'] }}">
                                            <i class="fas fa-{{ $mb['icon'] }} me-1"></i>
                                            {{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Reference Number</td>
                                    <td>{{ $payment->reference_number ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Status</td>
                                    <td>
                                        <span class="badge bg-{{ $sc['bg'] }}">
                                            <i class="fas fa-{{ $sc['icon'] }} me-1"></i>{{ ucfirst($payment->status) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Processed By</td>
                                    <td>{{ $payment->processedBy->name ?? 'System' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($payment->notes)
                    <div class="mt-3 p-3 bg-light rounded">
                        <strong><i class="fas fa-sticky-note me-1"></i> Notes:</strong>
                        <p class="mb-0 mt-1">{{ $payment->notes }}</p>
                    </div>
                    @endif

                    @if($payment->screenshot_path)
                    <div class="mt-3">
                        <strong><i class="fas fa-image me-1"></i> Payment Screenshot:</strong>
                        <div class="mt-2">
                            <a href="{{ asset('storage/' . $payment->screenshot_path) }}" target="_blank">
                                <img src="{{ asset('storage/' . $payment->screenshot_path) }}" alt="Payment Screenshot" class="img-thumbnail" style="max-width: 300px;">
                            </a>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Invoice Information Card -->
            @if($payment->invoice)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-file-invoice text-primary me-2"></i>Invoice Information</h5>
                    @if(Route::has('admin.invoices.show'))
                    <a href="{{ route('admin.invoices.show', $payment->invoice) }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-external-link-alt me-1"></i> View Invoice
                    </a>
                    @endif
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <table class="table table-borderless mb-0">
                                <tr>
                                    <td class="text-muted" style="width: 160px;">Invoice Number</td>
                                    <td><strong>{{ $payment->invoice->invoice_number }}</strong></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Type</td>
                                    <td>{{ $payment->invoice->type_label ?? ucfirst($payment->invoice->type ?? 'N/A') }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Billing Period</td>
                                    <td>{{ $payment->invoice->billing_period ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Due Date</td>
                                    <td>
                                        {{ $payment->invoice->due_date ? $payment->invoice->due_date->format('d M Y') : 'N/A' }}
                                        @if($payment->invoice->due_date && $payment->invoice->due_date->isPast() && $payment->invoice->status !== 'paid')
                                            <span class="badge bg-danger ms-1">Overdue</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless mb-0">
                                <tr>
                                    <td class="text-muted" style="width: 160px;">Subtotal</td>
                                    <td>RM {{ number_format($payment->invoice->subtotal ?? 0, 2) }}</td>
                                </tr>
                                @if(($payment->invoice->online_fee ?? 0) > 0)
                                <tr>
                                    <td class="text-muted">Online Fee</td>
                                    <td>RM {{ number_format($payment->invoice->online_fee, 2) }}</td>
                                </tr>
                                @endif
                                @if(($payment->invoice->discount ?? 0) > 0)
                                <tr>
                                    <td class="text-muted">Discount</td>
                                    <td class="text-danger">- RM {{ number_format($payment->invoice->discount, 2) }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td class="text-muted">Total Amount</td>
                                    <td><strong>RM {{ number_format($payment->invoice->total_amount ?? 0, 2) }}</strong></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Paid Amount</td>
                                    <td class="text-success">RM {{ number_format($payment->invoice->paid_amount ?? 0, 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Balance</td>
                                    <td>
                                        @php
                                            $balance = ($payment->invoice->total_amount ?? 0) - ($payment->invoice->paid_amount ?? 0);
                                        @endphp
                                        <strong class="{{ $balance > 0 ? 'text-danger' : 'text-success' }}">
                                            RM {{ number_format(max(0, $balance), 2) }}
                                        </strong>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Invoice Status Bar -->
                    @php
                        $paid = $payment->invoice->paid_amount ?? 0;
                        $total = $payment->invoice->total_amount ?? 1;
                        $paidPercent = $total > 0 ? min(100, round(($paid / $total) * 100)) : 0;
                    @endphp
                    <div class="mt-3">
                        <div class="d-flex justify-content-between mb-1">
                            <small class="text-muted">Payment Progress</small>
                            <small class="text-muted">{{ $paidPercent }}%</small>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-{{ $paidPercent >= 100 ? 'success' : ($paidPercent >= 50 ? 'info' : 'warning') }}"
                                 role="progressbar" style="width: {{ $paidPercent }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Enrollment/Package Info -->
            @if($payment->invoice && $payment->invoice->enrollment)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-graduation-cap text-info me-2"></i>Enrollment Details</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <table class="table table-borderless mb-0">
                                @if($payment->invoice->enrollment->package)
                                <tr>
                                    <td class="text-muted" style="width: 160px;">Package</td>
                                    <td><strong>{{ $payment->invoice->enrollment->package->name }}</strong></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Package Type</td>
                                    <td>
                                        <span class="badge bg-{{ $payment->invoice->enrollment->package->type === 'online' ? 'info' : 'secondary' }}">
                                            {{ ucfirst($payment->invoice->enrollment->package->type ?? 'N/A') }}
                                        </span>
                                    </td>
                                </tr>
                                @endif
                                @if($payment->invoice->enrollment->class)
                                <tr>
                                    <td class="text-muted">Class</td>
                                    <td>{{ $payment->invoice->enrollment->class->name ?? 'N/A' }}</td>
                                </tr>
                                @if($payment->invoice->enrollment->class->subject)
                                <tr>
                                    <td class="text-muted">Subject</td>
                                    <td>{{ $payment->invoice->enrollment->class->subject->name ?? 'N/A' }}</td>
                                </tr>
                                @endif
                                @endif
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless mb-0">
                                <tr>
                                    <td class="text-muted" style="width: 160px;">Enrollment Status</td>
                                    <td>
                                        @php
                                            $eStat = $payment->invoice->enrollment->status ?? 'unknown';
                                            $eColor = match($eStat) {
                                                'active' => 'success',
                                                'trial' => 'info',
                                                'expired' => 'danger',
                                                'cancelled' => 'secondary',
                                                'suspended' => 'warning',
                                                default => 'secondary',
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $eColor }}">{{ ucfirst($eStat) }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Monthly Fee</td>
                                    <td>RM {{ number_format($payment->invoice->enrollment->monthly_fee ?? 0, 2) }}</td>
                                </tr>
                                @if($payment->invoice->enrollment->start_date)
                                <tr>
                                    <td class="text-muted">Start Date</td>
                                    <td>{{ $payment->invoice->enrollment->start_date->format('d M Y') }}</td>
                                </tr>
                                @endif
                                @if($payment->invoice->enrollment->end_date)
                                <tr>
                                    <td class="text-muted">End Date</td>
                                    <td>{{ $payment->invoice->enrollment->end_date->format('d M Y') }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Right Column: Student & Actions -->
        <div class="col-lg-4">

            <!-- Student Information Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-user-graduate text-primary me-2"></i>Student Information</h5>
                </div>
                <div class="card-body">
                    @php
                        $student = $payment->invoice->student ?? $payment->student ?? null;
                    @endphp
                    @if($student)
                    <div class="text-center mb-3">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary text-white" style="width: 60px; height: 60px; font-size: 1.5rem; font-weight: 600;">
                            {{ substr($student->user->name ?? 'N', 0, 1) }}
                        </div>
                        <h5 class="mt-2 mb-0">{{ $student->user->name ?? 'Unknown' }}</h5>
                        <span class="badge bg-primary mt-1">{{ $student->student_id ?? 'N/A' }}</span>
                    </div>
                    <hr>
                    <table class="table table-borderless table-sm mb-0">
                        <tr>
                            <td class="text-muted"><i class="fas fa-envelope me-1"></i> Email</td>
                            <td>{{ $student->user->email ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted"><i class="fas fa-phone me-1"></i> Phone</td>
                            <td>{{ $student->user->phone ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted"><i class="fas fa-id-card me-1"></i> IC Number</td>
                            <td>
                                @if($student->ic_number && strlen($student->ic_number) === 12)
                                    {{ substr($student->ic_number, 0, 6) }}-{{ substr($student->ic_number, 6, 2) }}-{{ substr($student->ic_number, 8, 4) }}
                                @else
                                    {{ $student->ic_number ?? 'N/A' }}
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted"><i class="fas fa-school me-1"></i> School</td>
                            <td>{{ $student->school_name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted"><i class="fas fa-layer-group me-1"></i> Grade</td>
                            <td>{{ $student->grade_level ?? 'N/A' }}</td>
                        </tr>
                    </table>

                    @if($student->parent && $student->parent->user)
                    <hr>
                    <h6 class="text-muted mb-2"><i class="fas fa-user-friends me-1"></i> Parent/Guardian</h6>
                    <table class="table table-borderless table-sm mb-0">
                        <tr>
                            <td class="text-muted">Name</td>
                            <td>{{ $student->parent->user->name }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Phone</td>
                            <td>{{ $student->parent->user->phone ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Email</td>
                            <td>{{ $student->parent->user->email ?? 'N/A' }}</td>
                        </tr>
                    </table>
                    @endif

                    <div class="mt-3">
                        @if(Route::has('admin.students.show'))
                        <a href="{{ route('admin.students.show', $student) }}" class="btn btn-sm btn-outline-primary w-100">
                            <i class="fas fa-eye me-1"></i> View Student Profile
                        </a>
                        @endif
                    </div>
                    @else
                    <div class="text-center py-3">
                        <i class="fas fa-user-slash fa-2x text-muted mb-2"></i>
                        <p class="text-muted mb-0">Student information not available</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Quick Actions Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-bolt text-warning me-2"></i>Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.payments.receipt', $payment) }}" class="btn btn-outline-success" target="_blank">
                            <i class="fas fa-receipt me-1"></i> View Receipt
                        </a>
                        <a href="{{ route('admin.payments.download-receipt', $payment) }}" class="btn btn-outline-primary">
                            <i class="fas fa-file-pdf me-1"></i> Download Receipt PDF
                        </a>
                        <a href="{{ route('admin.payments.print-receipt', $payment) }}" class="btn btn-outline-secondary" target="_blank">
                            <i class="fas fa-print me-1"></i> Print Receipt
                        </a>

                        @if($payment->status === 'pending')
                        <hr>
                        <form action="{{ route('admin.payments.verify', $payment) }}" method="POST">
                            @csrf
                            <input type="hidden" name="approved" value="1">
                            <div class="mb-2">
                                <textarea name="verification_notes" class="form-control" rows="2" placeholder="Verification notes (optional)"></textarea>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-success" onclick="return confirm('Approve this payment?')">
                                    <i class="fas fa-check me-1"></i> Approve Payment
                                </button>
                            </div>
                        </form>
                        <form action="{{ route('admin.payments.verify', $payment) }}" method="POST">
                            @csrf
                            <input type="hidden" name="approved" value="0">
                            <div class="mb-2">
                                <textarea name="verification_notes" class="form-control" rows="2" placeholder="Rejection reason (optional)"></textarea>
                            </div>
                            <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Reject this payment?')">
                                <i class="fas fa-times me-1"></i> Reject Payment
                            </button>
                        </form>
                        @endif

                        @if($payment->status === 'completed')
                        <hr>
                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="collapse" data-bs-target="#refundSection">
                            <i class="fas fa-undo me-1"></i> Refund Payment
                        </button>
                        <div class="collapse mt-2" id="refundSection">
                            <form action="{{ route('admin.payments.refund', $payment) }}" method="POST">
                                @csrf
                                <div class="mb-2">
                                    <label class="form-label">Refund Amount (Max: RM {{ number_format($payment->amount, 2) }})</label>
                                    <input type="number" name="refund_amount" class="form-control" step="0.01" min="0.01" max="{{ $payment->amount }}" value="{{ $payment->amount }}" required>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Reason</label>
                                    <textarea name="refund_reason" class="form-control" rows="2" placeholder="Refund reason..." required></textarea>
                                </div>
                                <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Are you sure you want to refund this payment?')">
                                    <i class="fas fa-undo me-1"></i> Process Refund
                                </button>
                            </form>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Receipt Preview Summary -->
            @if(isset($receiptData))
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-file-alt text-success me-2"></i>Receipt Summary</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless table-sm mb-0">
                        <tr>
                            <td class="text-muted">Receipt No</td>
                            <td><strong>{{ $receiptData['receipt_number'] ?? 'N/A' }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Amount</td>
                            <td><strong class="text-success">RM {{ number_format($receiptData['payment_details']['amount'] ?? 0, 2) }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Method</td>
                            <td>{{ $receiptData['payment_details']['method'] ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Processed By</td>
                            <td>{{ $receiptData['payment_details']['processed_by'] ?? 'System' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Generated At</td>
                            <td>{{ $receiptData['generated_at'] ?? 'N/A' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
            @endif

        </div>
    </div>
</div>
@endsection
