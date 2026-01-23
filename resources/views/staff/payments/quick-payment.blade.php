@extends('layouts.app')

@section('title', 'Quick Payment')
@section('page-title', 'Quick Payment')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <i class="fas fa-bolt text-warning me-2"></i> Quick Payment
            </h4>
            <p class="text-muted mb-0">Fast payment processing for walk-in payments</p>
        </div>
        <a href="{{ route('staff.payments.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i> Back to Payments
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row">
        <!-- Search Section -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-search me-2"></i> Find Student
                </div>
                <div class="card-body">
                    <form action="{{ route('staff.payments.quick-payment') }}" method="GET">
                        <div class="mb-3">
                            <label class="form-label">Search Student</label>
                            <div class="input-group">
                                <input type="text" name="search" class="form-control form-control-lg" 
                                       placeholder="Student ID, Name or Email" 
                                       value="{{ request('search') }}" autofocus>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                            <small class="text-muted">Enter student ID, name, or email to search</small>
                        </div>
                    </form>

                    @if(request('search') && !$student)
                    <div class="alert alert-warning mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        No student found for "<strong>{{ request('search') }}</strong>"
                    </div>
                    @endif
                </div>
            </div>

            @if($student)
            <!-- Student Info Card -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <i class="fas fa-user-check me-2"></i> Student Found
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="avatar-lg bg-light rounded-circle me-3 d-flex align-items-center justify-content-center">
                            @if($student->user && $student->user->photo)
                                <img src="{{ asset('storage/' . $student->user->photo) }}" 
                                     class="rounded-circle" width="60" height="60" alt="Photo">
                            @else
                                <i class="fas fa-user-graduate fa-2x text-muted"></i>
                            @endif
                        </div>
                        <div>
                            <h5 class="mb-1">{{ $student->user->name ?? 'N/A' }}</h5>
                            <span class="badge bg-primary">{{ $student->student_id }}</span>
                        </div>
                    </div>
                    <hr>
                    <div class="row g-2">
                        <div class="col-6">
                            <small class="text-muted d-block">Email</small>
                            <span>{{ $student->user->email ?? 'N/A' }}</span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Phone</small>
                            <span>{{ $student->user->phone ?? 'N/A' }}</span>
                        </div>
                        <div class="col-12 mt-2">
                            <small class="text-muted d-block">School</small>
                            <span>{{ $student->school_name ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Invoices & Payment Section -->
        <div class="col-lg-8">
            @if($student)
                @if($unpaidInvoices->isEmpty())
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-check-circle text-success fa-4x mb-3"></i>
                        <h4>No Outstanding Invoices</h4>
                        <p class="text-muted">This student has no unpaid invoices.</p>
                        <a href="{{ route('staff.payments.create', ['student_id' => $student->id]) }}" 
                           class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i> Create Manual Payment
                        </a>
                    </div>
                </div>
                @else
                <!-- Unpaid Invoices List -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-file-invoice-dollar me-2"></i> 
                        Unpaid Invoices ({{ $unpaidInvoices->count() }})
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Select</th>
                                        <th>Invoice #</th>
                                        <th>Description</th>
                                        <th>Due Date</th>
                                        <th class="text-end">Amount</th>
                                        <th class="text-end">Balance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($unpaidInvoices as $invoice)
                                    <tr class="invoice-row" data-invoice-id="{{ $invoice->id }}" 
                                        data-balance="{{ $invoice->balance }}">
                                        <td>
                                            <div class="form-check">
                                                <input type="radio" name="selected_invoice" 
                                                       class="form-check-input invoice-radio"
                                                       value="{{ $invoice->id }}"
                                                       data-balance="{{ $invoice->balance }}"
                                                       data-invoice-number="{{ $invoice->invoice_number }}">
                                            </div>
                                        </td>
                                        <td>
                                            <strong>{{ $invoice->invoice_number }}</strong>
                                            @if($invoice->isOverdue())
                                            <span class="badge bg-danger ms-1">Overdue</span>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $invoice->description ?? $invoice->type_label ?? 'Invoice' }}
                                            @if($invoice->billing_period)
                                            <br><small class="text-muted">{{ $invoice->billing_period }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $invoice->due_date ? $invoice->due_date->format('d M Y') : 'N/A' }}
                                        </td>
                                        <td class="text-end">RM {{ number_format($invoice->total_amount, 2) }}</td>
                                        <td class="text-end fw-bold text-danger">
                                            RM {{ number_format($invoice->balance, 2) }}
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="5" class="text-end fw-bold">Total Outstanding:</td>
                                        <td class="text-end fw-bold text-danger">
                                            RM {{ number_format($unpaidInvoices->sum('balance'), 2) }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Quick Payment Form -->
                <div class="card" id="paymentForm" style="display: none;">
                    <div class="card-header bg-success text-white">
                        <i class="fas fa-money-bill-wave me-2"></i> Process Payment
                    </div>
                    <div class="card-body">
                        <form action="{{ route('staff.payments.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="invoice_id" id="invoice_id">
                            
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="bg-light p-3 rounded">
                                        <small class="text-muted">Selected Invoice</small>
                                        <h5 class="mb-0" id="selectedInvoiceNumber">-</h5>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="bg-light p-3 rounded">
                                        <small class="text-muted">Balance Due</small>
                                        <h5 class="mb-0 text-danger" id="selectedBalance">RM 0.00</h5>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label"><strong>Payment Method</strong> <span class="text-danger">*</span></label>
                                    <select name="payment_method" id="payment_method" class="form-select form-select-lg" required>
                                        @foreach($paymentMethods as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label"><strong>Amount (RM)</strong> <span class="text-danger">*</span></label>
                                    <input type="number" name="amount" id="amount" 
                                           class="form-control form-control-lg" 
                                           step="0.01" min="0.01" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label"><strong>Payment Date</strong> <span class="text-danger">*</span></label>
                                    <input type="date" name="payment_date" class="form-control" 
                                           value="{{ date('Y-m-d') }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label"><strong>Reference Number</strong></label>
                                    <input type="text" name="reference_number" class="form-control" 
                                           placeholder="Transaction/Receipt reference">
                                </div>

                                <!-- QR Payment Screenshot (shown only for QR method) -->
                                <div class="col-12" id="qrScreenshotSection" style="display: none;">
                                    <label class="form-label"><strong>Payment Screenshot</strong></label>
                                    <input type="file" name="screenshot" class="form-control" accept="image/*">
                                    <small class="text-muted">Upload screenshot of successful QR payment</small>
                                </div>

                                <div class="col-12">
                                    <label class="form-label"><strong>Notes</strong></label>
                                    <textarea name="notes" class="form-control" rows="2" 
                                              placeholder="Any additional notes..."></textarea>
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="d-flex justify-content-between">
                                <button type="button" class="btn btn-outline-secondary" onclick="clearSelection()">
                                    <i class="fas fa-times me-2"></i> Cancel
                                </button>
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="fas fa-check me-2"></i> Record Payment
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                @endif
            @else
            <!-- No Student Selected -->
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-search text-muted fa-4x mb-3"></i>
                    <h4>Search for a Student</h4>
                    <p class="text-muted">Enter a student ID, name, or email to find their unpaid invoices and process payment quickly.</p>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Invoice selection
    const invoiceRadios = document.querySelectorAll('.invoice-radio');
    const paymentForm = document.getElementById('paymentForm');
    const invoiceIdInput = document.getElementById('invoice_id');
    const selectedInvoiceNumber = document.getElementById('selectedInvoiceNumber');
    const selectedBalance = document.getElementById('selectedBalance');
    const amountInput = document.getElementById('amount');
    const paymentMethodSelect = document.getElementById('payment_method');
    const qrScreenshotSection = document.getElementById('qrScreenshotSection');

    invoiceRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.checked) {
                const balance = parseFloat(this.dataset.balance);
                const invoiceNumber = this.dataset.invoiceNumber;

                // Show payment form
                paymentForm.style.display = 'block';

                // Set values
                invoiceIdInput.value = this.value;
                selectedInvoiceNumber.textContent = invoiceNumber;
                selectedBalance.textContent = 'RM ' + balance.toFixed(2);
                amountInput.value = balance.toFixed(2);
                amountInput.max = balance;

                // Highlight selected row
                document.querySelectorAll('.invoice-row').forEach(row => {
                    row.classList.remove('table-primary');
                });
                this.closest('.invoice-row').classList.add('table-primary');

                // Scroll to payment form
                paymentForm.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    // Payment method change - show/hide QR screenshot
    if (paymentMethodSelect) {
        paymentMethodSelect.addEventListener('change', function() {
            if (this.value === 'qr') {
                qrScreenshotSection.style.display = 'block';
            } else {
                qrScreenshotSection.style.display = 'none';
            }
        });
    }
});

function clearSelection() {
    // Hide payment form
    document.getElementById('paymentForm').style.display = 'none';

    // Uncheck all radios
    document.querySelectorAll('.invoice-radio').forEach(radio => {
        radio.checked = false;
    });

    // Remove highlight
    document.querySelectorAll('.invoice-row').forEach(row => {
        row.classList.remove('table-primary');
    });

    // Clear form values
    document.getElementById('invoice_id').value = '';
    document.getElementById('amount').value = '';
}
</script>
@endpush

@push('styles')
<style>
.avatar-lg {
    width: 70px;
    height: 70px;
}

.invoice-row {
    cursor: pointer;
    transition: background-color 0.2s;
}

.invoice-row:hover {
    background-color: #f8f9fa;
}

.invoice-row.table-primary {
    background-color: #cfe2ff !important;
}

#paymentForm {
    border: 2px solid #198754;
}
</style>
@endpush
