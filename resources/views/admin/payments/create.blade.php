@extends('layouts.app')

@section('title', 'Record Payment')
@section('page-title', 'Record Payment')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item"><a href="{{ route('admin.payments.index') }}">Payments</a></li>
<li class="breadcrumb-item active">Record Payment</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Payment Form -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i>Payment Details</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.payments.store') }}" method="POST" enctype="multipart/form-data" id="paymentForm">
                        @csrf

                        <!-- Student Selection -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Select Student <span class="text-danger">*</span></label>
                            <select name="student_id" id="studentSelect" class="form-select @error('student_id') is-invalid @enderror" required>
                                <option value="">-- Search Student --</option>
                                @foreach($students as $student)
                                    <option value="{{ $student->id }}"
                                        {{ (old('student_id') == $student->id || ($selectedStudent && $selectedStudent->id == $student->id)) ? 'selected' : '' }}>
                                        {{ $student->student_id }} - {{ $student->user->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('student_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Invoice Selection -->
                        <div class="mb-4" id="invoiceSection" style="{{ $selectedStudent ? '' : 'display: none;' }}">
                            <label class="form-label fw-semibold">Select Invoice <span class="text-danger">*</span></label>
                            <div id="invoiceList">
                                @if($selectedStudent && $unpaidInvoices->count() > 0)
                                    @foreach($unpaidInvoices as $invoice)
                                    <div class="form-check border rounded p-3 mb-2 invoice-option {{ $invoice->isOverdue() ? 'border-danger' : '' }}">
                                        <input class="form-check-input" type="radio" name="invoice_id"
                                            id="invoice_{{ $invoice->id }}" value="{{ $invoice->id }}"
                                            data-balance="{{ $invoice->balance }}"
                                            {{ ($selectedInvoice && $selectedInvoice->id == $invoice->id) ? 'checked' : '' }}>
                                        <label class="form-check-label w-100" for="invoice_{{ $invoice->id }}">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <strong>{{ $invoice->invoice_number }}</strong>
                                                    <span class="badge bg-{{ $invoice->status_badge }} ms-2">{{ $invoice->status_label }}</span>
                                                    @if($invoice->isOverdue())
                                                    <span class="badge bg-danger ms-1">{{ $invoice->days_overdue }} days overdue</span>
                                                    @endif
                                                    <br>
                                                    <small class="text-muted">{{ $invoice->type_label }} | {{ $invoice->billing_period }}</small>
                                                    <br>
                                                    <small class="text-muted">Due: {{ $invoice->due_date->format('d M Y') }}</small>
                                                </div>
                                                <div class="text-end">
                                                    <span class="text-muted">Total: RM {{ number_format($invoice->total_amount, 2) }}</span>
                                                    <br>
                                                    <span class="text-muted">Paid: RM {{ number_format($invoice->paid_amount, 2) }}</span>
                                                    <br>
                                                    <strong class="text-primary">Balance: RM {{ number_format($invoice->balance, 2) }}</strong>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                    @endforeach
                                @elseif($selectedStudent)
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>No unpaid invoices found for this student.
                                    </div>
                                @endif
                            </div>
                            @error('invoice_id')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr class="my-4">

                        <!-- Payment Method -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Payment Method <span class="text-danger">*</span></label>
                            <div class="row g-3">
                                @foreach($paymentMethods as $key => $label)
                                <div class="col-md-6">
                                    <div class="form-check border rounded p-3 h-100 payment-method-option">
                                        <input class="form-check-input" type="radio" name="payment_method"
                                            id="method_{{ $key }}" value="{{ $key }}"
                                            {{ old('payment_method', 'cash') == $key ? 'checked' : '' }} required>
                                        <label class="form-check-label w-100" for="method_{{ $key }}">
                                            <i class="fas fa-{{ $key === 'cash' ? 'money-bill' : ($key === 'qr' ? 'qrcode' : 'credit-card') }} me-2"></i>
                                            {{ $label }}
                                        </label>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @error('payment_method')
                                <div class="text-danger small mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Amount -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Payment Amount (RM) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">RM</span>
                                    <input type="number" name="amount" id="paymentAmount" step="0.01" min="0.01"
                                        class="form-control @error('amount') is-invalid @enderror"
                                        value="{{ old('amount', $selectedInvoice ? $selectedInvoice->balance : '') }}"
                                        placeholder="0.00" required>
                                </div>
                                <small class="text-muted" id="balanceHint"></small>
                                @error('amount')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Payment Date <span class="text-danger">*</span></label>
                                <input type="date" name="payment_date" class="form-control @error('payment_date') is-invalid @enderror"
                                    value="{{ old('payment_date', date('Y-m-d')) }}" max="{{ date('Y-m-d') }}" required>
                                @error('payment_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Reference Number (for QR/Bank Transfer) -->
                        <div class="mb-4" id="referenceSection" style="{{ in_array(old('payment_method'), ['qr', 'bank_transfer', 'cheque']) ? '' : 'display: none;' }}">
                            <label class="form-label fw-semibold">Reference Number <span class="text-danger">*</span></label>
                            <input type="text" name="reference_number" id="referenceNumber"
                                class="form-control @error('reference_number') is-invalid @enderror"
                                value="{{ old('reference_number') }}" placeholder="Enter transaction reference">
                            <small class="text-muted">Enter the transaction reference number from the payment receipt.</small>
                            @error('reference_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Screenshot Upload (for QR) -->
                        <div class="mb-4" id="screenshotSection" style="{{ old('payment_method') == 'qr' ? '' : 'display: none;' }}">
                            <label class="form-label fw-semibold">Payment Screenshot</label>
                            <input type="file" name="screenshot" class="form-control @error('screenshot') is-invalid @enderror"
                                accept="image/jpeg,image/png,image/jpg">
                            <small class="text-muted">Upload a screenshot of the payment confirmation (optional but recommended).</small>
                            @error('screenshot')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Notes -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Notes</label>
                            <textarea name="notes" class="form-control @error('notes') is-invalid @enderror"
                                rows="2" placeholder="Add any additional notes...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-check me-1"></i> Record Payment
                            </button>
                            <a href="{{ route('admin.payments.index') }}" class="btn btn-outline-secondary">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- QR Code Sidebar -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4" id="qrCodeCard" style="{{ old('payment_method') == 'qr' ? '' : 'display: none;' }}">
                <div class="card-header bg-info text-white py-3">
                    <h5 class="mb-0"><i class="fas fa-qrcode me-2"></i>QR Payment</h5>
                </div>
                <div class="card-body text-center">
                    @if($qrSettings['qr_image'])
                    <img src="{{ asset('storage/' . $qrSettings['qr_image']) }}" alt="QR Code" class="img-fluid mb-3" style="max-width: 250px;">
                    @else
                    <div class="bg-light p-5 rounded mb-3">
                        <i class="fas fa-qrcode fa-5x text-muted"></i>
                        <p class="text-muted mt-2 mb-0">QR Code not configured</p>
                    </div>
                    @endif
                    <div class="text-start">
                        <p class="mb-1"><strong>Bank:</strong> {{ $qrSettings['bank_name'] ?: 'Not configured' }}</p>
                        <p class="mb-1"><strong>Account Name:</strong> {{ $qrSettings['account_name'] ?: 'Not configured' }}</p>
                        <p class="mb-0"><strong>Account Number:</strong> {{ $qrSettings['account_number'] ?: 'Not configured' }}</p>
                    </div>
                </div>
            </div>

            <!-- Payment Guidelines -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Payment Guidelines</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="text-primary"><i class="fas fa-money-bill me-2"></i>Cash Payment</h6>
                        <ul class="small text-muted mb-0">
                            <li>Count cash received carefully</li>
                            <li>Issue receipt immediately after recording</li>
                            <li>Store cash securely in drawer</li>
                        </ul>
                    </div>
                    <div class="mb-3">
                        <h6 class="text-info"><i class="fas fa-qrcode me-2"></i>QR Payment</h6>
                        <ul class="small text-muted mb-0">
                            <li>Verify transaction reference number</li>
                            <li>Upload payment screenshot if available</li>
                            <li>Confirm amount received in bank app</li>
                        </ul>
                    </div>
                    <div>
                        <h6 class="text-secondary"><i class="fas fa-exchange-alt me-2"></i>Bank Transfer</h6>
                        <ul class="small text-muted mb-0">
                            <li>Record bank reference number</li>
                            <li>Verify against bank statement</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Student selection change
    $('#studentSelect').on('change', function() {
        const studentId = $(this).val();

        if (studentId) {
            // Fetch invoices via AJAX
            $.ajax({
                url: `/admin/payments/student/${studentId}/invoices`,
                type: 'GET',
                success: function(invoices) {
                    let html = '';

                    if (invoices.length > 0) {
                        invoices.forEach(function(invoice) {
                            const overdueClass = invoice.is_overdue ? 'border-danger' : '';
                            const overdueBadge = invoice.is_overdue ? `<span class="badge bg-danger ms-1">${invoice.days_overdue || ''} days overdue</span>` : '';

                            html += `
                                <div class="form-check border rounded p-3 mb-2 invoice-option ${overdueClass}">
                                    <input class="form-check-input" type="radio" name="invoice_id"
                                        id="invoice_${invoice.id}" value="${invoice.id}"
                                        data-balance="${invoice.balance}">
                                    <label class="form-check-label w-100" for="invoice_${invoice.id}">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <strong>${invoice.invoice_number}</strong>
                                                <span class="badge bg-${getStatusBadge(invoice.status)} ms-2">${invoice.status}</span>
                                                ${overdueBadge}
                                                <br>
                                                <small class="text-muted">${invoice.type} | ${invoice.billing_period}</small>
                                                <br>
                                                <small class="text-muted">Due: ${invoice.due_date}</small>
                                            </div>
                                            <div class="text-end">
                                                <span class="text-muted">Total: RM ${parseFloat(invoice.total_amount).toFixed(2)}</span>
                                                <br>
                                                <span class="text-muted">Paid: RM ${parseFloat(invoice.paid_amount).toFixed(2)}</span>
                                                <br>
                                                <strong class="text-primary">Balance: RM ${parseFloat(invoice.balance).toFixed(2)}</strong>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            `;
                        });
                    } else {
                        html = '<div class="alert alert-info"><i class="fas fa-info-circle me-2"></i>No unpaid invoices found for this student.</div>';
                    }

                    $('#invoiceList').html(html);
                    $('#invoiceSection').show();

                    // Re-bind invoice selection
                    bindInvoiceSelection();
                },
                error: function() {
                    $('#invoiceList').html('<div class="alert alert-danger">Error loading invoices.</div>');
                }
            });
        } else {
            $('#invoiceSection').hide();
            $('#invoiceList').html('');
        }
    });

    function getStatusBadge(status) {
        const badges = {
            'pending': 'warning',
            'partial': 'info',
            'overdue': 'danger',
            'paid': 'success'
        };
        return badges[status] || 'secondary';
    }

    // Invoice selection - auto-fill amount
    function bindInvoiceSelection() {
        $('input[name="invoice_id"]').on('change', function() {
            const balance = $(this).data('balance');
            $('#paymentAmount').val(parseFloat(balance).toFixed(2));
            $('#balanceHint').text(`Outstanding balance: RM ${parseFloat(balance).toFixed(2)}`);
        });
    }
    bindInvoiceSelection();

    // Payment method change
    $('input[name="payment_method"]').on('change', function() {
        const method = $(this).val();

        // Toggle reference section
        if (['qr', 'bank_transfer', 'cheque'].includes(method)) {
            $('#referenceSection').show();
            $('#referenceNumber').prop('required', true);
        } else {
            $('#referenceSection').hide();
            $('#referenceNumber').prop('required', false);
        }

        // Toggle screenshot section
        if (method === 'qr') {
            $('#screenshotSection').show();
            $('#qrCodeCard').show();
        } else {
            $('#screenshotSection').hide();
            $('#qrCodeCard').hide();
        }
    });

    // Trigger initial state
    $('input[name="payment_method"]:checked').trigger('change');

    // If invoice is pre-selected, trigger change
    $('input[name="invoice_id"]:checked').trigger('change');
});
</script>
@endpush
@endsection
