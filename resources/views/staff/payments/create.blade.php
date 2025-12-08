@extends('layouts.staff')

@section('title', 'Record Payment')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Record Payment</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('staff.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('staff.payments.index') }}">Payments</a></li>
                    <li class="breadcrumb-item active">Record Payment</li>
                </ol>
            </nav>
        </div>
    </div>

    <form action="{{ route('staff.payments.store') }}" method="POST" enctype="multipart/form-data" id="paymentForm">
        @csrf

        <div class="row">
            <!-- Left Column - Payment Form -->
            <div class="col-lg-8">
                <!-- Student Selection -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-user-graduate me-2"></i>Student Selection</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Select Student <span class="text-danger">*</span></label>
                            <select name="student_id" id="studentSelect" class="form-select @error('student_id') is-invalid @enderror" required>
                                <option value="">-- Select Student --</option>
                                @foreach($students as $student)
                                    <option value="{{ $student->id }}"
                                            {{ ($selectedStudent && $selectedStudent->id == $student->id) ? 'selected' : '' }}>
                                        {{ $student->user->name }} ({{ $student->student_id }})
                                    </option>
                                @endforeach
                            </select>
                            @error('student_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Student Info Display -->
                        <div id="studentInfo" class="alert alert-info d-none">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Name:</strong> <span id="studentName">-</span><br>
                                    <strong>Student ID:</strong> <span id="studentId">-</span>
                                </div>
                                <div class="col-md-6">
                                    <strong>Outstanding:</strong> <span id="studentOutstanding" class="text-danger fw-bold">RM 0.00</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Invoice Selection -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-file-invoice me-2"></i>Invoice Selection</h5>
                    </div>
                    <div class="card-body">
                        <div id="invoiceLoading" class="text-center py-3 d-none">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Loading invoices...</p>
                        </div>

                        <div id="noInvoices" class="alert alert-warning d-none">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            No unpaid invoices found for this student.
                        </div>

                        <div id="invoiceList" class="d-none">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 30px;"></th>
                                            <th>Invoice</th>
                                            <th>Type</th>
                                            <th>Period</th>
                                            <th class="text-end">Total</th>
                                            <th class="text-end">Balance</th>
                                            <th>Due Date</th>
                                        </tr>
                                    </thead>
                                    <tbody id="invoiceTableBody">
                                    </tbody>
                                </table>
                            </div>
                            <input type="hidden" name="invoice_id" id="selectedInvoiceId" required>
                        </div>

                        @if($selectedInvoice)
                            <input type="hidden" name="invoice_id" value="{{ $selectedInvoice->id }}">
                            <div class="alert alert-success">
                                <strong>Selected Invoice:</strong> {{ $selectedInvoice->invoice_number }}
                                - Balance: RM {{ number_format($selectedInvoice->balance, 2) }}
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Payment Details -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-credit-card me-2"></i>Payment Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                                <select name="payment_method" id="paymentMethod" class="form-select @error('payment_method') is-invalid @enderror" required>
                                    <option value="">-- Select Method --</option>
                                    @foreach($paymentMethods as $key => $label)
                                        <option value="{{ $key }}" {{ old('payment_method') == $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('payment_method')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Amount (RM) <span class="text-danger">*</span></label>
                                <input type="number" name="amount" id="paymentAmount"
                                       class="form-control @error('amount') is-invalid @enderror"
                                       step="0.01" min="0.01" required
                                       value="{{ old('amount') }}" placeholder="0.00">
                                @error('amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div id="balanceHint" class="form-text"></div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Payment Date <span class="text-danger">*</span></label>
                                <input type="date" name="payment_date"
                                       class="form-control @error('payment_date') is-invalid @enderror"
                                       value="{{ old('payment_date', date('Y-m-d')) }}" required>
                                @error('payment_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Reference Number</label>
                                <input type="text" name="reference_number"
                                       class="form-control @error('reference_number') is-invalid @enderror"
                                       value="{{ old('reference_number') }}"
                                       placeholder="Transaction/Receipt reference">
                                @error('reference_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- QR Payment Screenshot -->
                        <div id="qrScreenshotSection" class="d-none">
                            <div class="mb-3">
                                <label class="form-label">Payment Screenshot <span class="text-danger">*</span></label>
                                <input type="file" name="screenshot" id="screenshot"
                                       class="form-control @error('screenshot') is-invalid @enderror"
                                       accept="image/*">
                                @error('screenshot')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Upload screenshot of QR payment confirmation</div>
                            </div>
                            <div id="screenshotPreview" class="d-none">
                                <img src="" alt="Preview" class="img-thumbnail" style="max-height: 200px;">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="2"
                                      placeholder="Any additional notes...">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - QR Code & Summary -->
            <div class="col-lg-4">
                <!-- QR Payment Info -->
                <div id="qrPaymentInfo" class="card shadow-sm mb-4 d-none">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-qrcode me-2"></i>QR Payment</h5>
                    </div>
                    <div class="card-body text-center">
                        @if($qrSettings['qr_image'])
                            <img src="{{ asset('storage/' . $qrSettings['qr_image']) }}"
                                 alt="QR Code" class="img-fluid mb-3" style="max-width: 250px;">
                        @else
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                QR code not configured
                            </div>
                        @endif
                        <div class="text-start">
                            <p class="mb-1"><strong>Bank:</strong> {{ $qrSettings['bank_name'] }}</p>
                            <p class="mb-1"><strong>Account:</strong> {{ $qrSettings['account_name'] }}</p>
                            <p class="mb-0"><strong>Number:</strong> {{ $qrSettings['account_number'] }}</p>
                        </div>
                    </div>
                </div>

                <!-- Payment Summary -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-calculator me-2"></i>Payment Summary</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <td>Invoice Total:</td>
                                <td class="text-end fw-bold" id="summaryTotal">RM 0.00</td>
                            </tr>
                            <tr>
                                <td>Already Paid:</td>
                                <td class="text-end text-success" id="summaryPaid">RM 0.00</td>
                            </tr>
                            <tr>
                                <td>Balance Due:</td>
                                <td class="text-end text-danger fw-bold" id="summaryBalance">RM 0.00</td>
                            </tr>
                            <tr class="border-top">
                                <td>This Payment:</td>
                                <td class="text-end text-primary fw-bold" id="summaryPayment">RM 0.00</td>
                            </tr>
                            <tr>
                                <td>Remaining:</td>
                                <td class="text-end" id="summaryRemaining">RM 0.00</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-success btn-lg" id="submitBtn" disabled>
                        <i class="fas fa-check-circle me-2"></i>Record Payment
                    </button>
                    <a href="{{ route('staff.payments.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-2"></i>Cancel
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let selectedInvoice = null;

    // Student selection change
    $('#studentSelect').change(function() {
        const studentId = $(this).val();
        if (!studentId) {
            resetForm();
            return;
        }

        loadStudentInvoices(studentId);
    });

    // Load student invoices
    function loadStudentInvoices(studentId) {
        $('#invoiceLoading').removeClass('d-none');
        $('#invoiceList, #noInvoices').addClass('d-none');

        $.ajax({
            url: "{{ url('staff/payments/student') }}/" + studentId + "/invoices",
            method: 'GET',
            success: function(response) {
                $('#invoiceLoading').addClass('d-none');

                if (response.length === 0) {
                    $('#noInvoices').removeClass('d-none');
                    return;
                }

                let html = '';
                let totalOutstanding = 0;

                response.forEach(function(invoice) {
                    totalOutstanding += parseFloat(invoice.balance);
                    const overdueClass = invoice.is_overdue ? 'table-danger' : '';

                    html += `
                        <tr class="${overdueClass}" style="cursor: pointer;" onclick="selectInvoice(${invoice.id}, this)">
                            <td><input type="radio" name="invoice_radio" value="${invoice.id}" class="form-check-input"></td>
                            <td>${invoice.invoice_number}</td>
                            <td>${invoice.type}</td>
                            <td>${invoice.billing_period || '-'}</td>
                            <td class="text-end">RM ${parseFloat(invoice.total_amount).toFixed(2)}</td>
                            <td class="text-end fw-bold text-danger">RM ${parseFloat(invoice.balance).toFixed(2)}</td>
                            <td>
                                ${invoice.due_date}
                                ${invoice.is_overdue ? '<span class="badge bg-danger ms-1">Overdue</span>' : ''}
                            </td>
                        </tr>
                    `;
                });

                $('#invoiceTableBody').html(html);
                $('#invoiceList').removeClass('d-none');
                $('#studentOutstanding').text('RM ' + totalOutstanding.toFixed(2));
                $('#studentInfo').removeClass('d-none');

                // Store invoices data
                window.invoicesData = response;
            },
            error: function() {
                $('#invoiceLoading').addClass('d-none');
                alert('Failed to load invoices. Please try again.');
            }
        });
    }

    // Select invoice function (global)
    window.selectInvoice = function(invoiceId, row) {
        $('input[name="invoice_radio"]').prop('checked', false);
        $(row).find('input[type="radio"]').prop('checked', true);
        $('#selectedInvoiceId').val(invoiceId);

        // Find invoice data
        const invoice = window.invoicesData.find(inv => inv.id === invoiceId);
        if (invoice) {
            selectedInvoice = invoice;
            updateSummary();
            $('#paymentAmount').attr('max', invoice.balance);
            $('#balanceHint').text('Maximum: RM ' + parseFloat(invoice.balance).toFixed(2));
            validateForm();
        }
    };

    // Payment method change
    $('#paymentMethod').change(function() {
        const method = $(this).val();

        if (method === 'qr') {
            $('#qrPaymentInfo, #qrScreenshotSection').removeClass('d-none');
            $('#screenshot').prop('required', true);
        } else {
            $('#qrPaymentInfo, #qrScreenshotSection').addClass('d-none');
            $('#screenshot').prop('required', false);
        }

        validateForm();
    });

    // Screenshot preview
    $('#screenshot').change(function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#screenshotPreview img').attr('src', e.target.result);
                $('#screenshotPreview').removeClass('d-none');
            };
            reader.readAsDataURL(file);
        }
        validateForm();
    });

    // Amount change
    $('#paymentAmount').on('input', function() {
        updateSummary();
        validateForm();
    });

    // Update summary
    function updateSummary() {
        if (!selectedInvoice) return;

        const amount = parseFloat($('#paymentAmount').val()) || 0;
        const balance = parseFloat(selectedInvoice.balance);
        const remaining = balance - amount;

        $('#summaryTotal').text('RM ' + parseFloat(selectedInvoice.total_amount).toFixed(2));
        $('#summaryPaid').text('RM ' + parseFloat(selectedInvoice.paid_amount).toFixed(2));
        $('#summaryBalance').text('RM ' + balance.toFixed(2));
        $('#summaryPayment').text('RM ' + amount.toFixed(2));
        $('#summaryRemaining').text('RM ' + Math.max(0, remaining).toFixed(2));

        if (remaining < 0) {
            $('#summaryRemaining').addClass('text-danger').text('Exceeds balance!');
        } else {
            $('#summaryRemaining').removeClass('text-danger');
        }
    }

    // Form validation
    function validateForm() {
        const studentId = $('#studentSelect').val();
        const invoiceId = $('#selectedInvoiceId').val();
        const method = $('#paymentMethod').val();
        const amount = parseFloat($('#paymentAmount').val()) || 0;

        let valid = studentId && invoiceId && method && amount > 0;

        if (selectedInvoice && amount > parseFloat(selectedInvoice.balance)) {
            valid = false;
        }

        if (method === 'qr' && !$('#screenshot')[0].files.length) {
            valid = false;
        }

        $('#submitBtn').prop('disabled', !valid);
    }

    // Reset form
    function resetForm() {
        $('#studentInfo, #invoiceList, #noInvoices, #qrPaymentInfo, #qrScreenshotSection').addClass('d-none');
        $('#invoiceTableBody').html('');
        $('#selectedInvoiceId').val('');
        selectedInvoice = null;
        updateSummary();
        validateForm();
    }

    // Initial load if student is pre-selected
    @if($selectedStudent)
        loadStudentInvoices({{ $selectedStudent->id }});
    @endif
});
</script>
@endpush
