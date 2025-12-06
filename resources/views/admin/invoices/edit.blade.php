@extends('layouts.app')

@section('title', 'Edit Invoice')
@section('page-title', 'Edit Invoice')

@push('styles')
<style>
    .invoice-summary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 10px;
        padding: 20px;
    }
    .summary-row {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px solid rgba(255,255,255,0.2);
    }
    .summary-row:last-child {
        border-bottom: none;
        font-size: 1.2em;
        font-weight: bold;
    }
    .form-section {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 20px;
    }
    .form-section-title {
        font-weight: 600;
        color: #333;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid #667eea;
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1>
        <i class="fas fa-edit me-2"></i> Edit Invoice #{{ $invoice->invoice_number }}
    </h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.invoices.index') }}">Invoices</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.invoices.show', $invoice) }}">{{ $invoice->invoice_number }}</a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol>
    </nav>
</div>

@if($invoice->status === 'paid')
<div class="alert alert-danger">
    <i class="fas fa-exclamation-triangle me-2"></i>
    <strong>Cannot Edit:</strong> This invoice has already been paid.
</div>
@else

<form action="{{ route('admin.invoices.update', $invoice) }}" method="POST" id="editInvoiceForm">
    @csrf
    @method('PUT')

    <div class="row">
        <!-- Left Column - Form -->
        <div class="col-lg-8">
            <!-- Student & Enrollment Info -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-user-graduate me-2"></i> Student Information
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Student</label>
                                <p class="form-control-plaintext">
                                    {{ $invoice->student->user->name ?? 'N/A' }}
                                    <br>
                                    <small class="text-muted">{{ $invoice->student->student_id ?? 'No ID' }}</small>
                                </p>
                                <input type="hidden" name="student_id" value="{{ $invoice->student_id }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Enrollment / Package</label>
                                <p class="form-control-plaintext">
                                    {{ $invoice->enrollment->package->name ?? 'N/A' }}
                                </p>
                                <input type="hidden" name="enrollment_id" value="{{ $invoice->enrollment_id }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Invoice Details -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-file-invoice me-2"></i> Invoice Details
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="type" class="form-label">Invoice Type <span class="text-danger">*</span></label>
                                <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required>
                                    <option value="monthly" {{ old('type', $invoice->type) == 'monthly' ? 'selected' : '' }}>Monthly Fee</option>
                                    <option value="registration" {{ old('type', $invoice->type) == 'registration' ? 'selected' : '' }}>Registration Fee</option>
                                    <option value="material" {{ old('type', $invoice->type) == 'material' ? 'selected' : '' }}>Material Fee</option>
                                    <option value="exam" {{ old('type', $invoice->type) == 'exam' ? 'selected' : '' }}>Exam Fee</option>
                                    <option value="renewal" {{ old('type', $invoice->type) == 'renewal' ? 'selected' : '' }}>Renewal</option>
                                    <option value="other" {{ old('type', $invoice->type) == 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="due_date" class="form-label">Due Date <span class="text-danger">*</span></label>
                                <input type="date" name="due_date" id="due_date"
                                       class="form-control @error('due_date') is-invalid @enderror"
                                       value="{{ old('due_date', $invoice->due_date->format('Y-m-d')) }}" required>
                                @error('due_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="billing_period_start" class="form-label">Billing Period Start</label>
                                <input type="date" name="billing_period_start" id="billing_period_start"
                                       class="form-control @error('billing_period_start') is-invalid @enderror"
                                       value="{{ old('billing_period_start', $invoice->billing_period_start?->format('Y-m-d')) }}">
                                @error('billing_period_start')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="billing_period_end" class="form-label">Billing Period End</label>
                                <input type="date" name="billing_period_end" id="billing_period_end"
                                       class="form-control @error('billing_period_end') is-invalid @enderror"
                                       value="{{ old('billing_period_end', $invoice->billing_period_end?->format('Y-m-d')) }}">
                                @error('billing_period_end')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Amount Details -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-calculator me-2"></i> Amount Details
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="subtotal" class="form-label">Subtotal (RM) <span class="text-danger">*</span></label>
                                <input type="number" name="subtotal" id="subtotal" step="0.01" min="0"
                                       class="form-control @error('subtotal') is-invalid @enderror amount-input"
                                       value="{{ old('subtotal', $invoice->subtotal) }}" required>
                                @error('subtotal')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="online_fee" class="form-label">Online Fee (RM)</label>
                                <input type="number" name="online_fee" id="online_fee" step="0.01" min="0"
                                       class="form-control @error('online_fee') is-invalid @enderror amount-input"
                                       value="{{ old('online_fee', $invoice->online_fee) }}">
                                @error('online_fee')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="discount" class="form-label">Discount (RM)</label>
                                <input type="number" name="discount" id="discount" step="0.01" min="0"
                                       class="form-control @error('discount') is-invalid @enderror amount-input"
                                       value="{{ old('discount', $invoice->discount) }}">
                                @error('discount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="discount_reason" class="form-label">Discount Reason</label>
                                <input type="text" name="discount_reason" id="discount_reason"
                                       class="form-control @error('discount_reason') is-invalid @enderror"
                                       value="{{ old('discount_reason', $invoice->discount_reason) }}"
                                       placeholder="e.g., Early bird, Sibling discount">
                                @error('discount_reason')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="tax" class="form-label">Tax (RM)</label>
                                <input type="number" name="tax" id="tax" step="0.01" min="0"
                                       class="form-control @error('tax') is-invalid @enderror amount-input"
                                       value="{{ old('tax', $invoice->tax) }}">
                                @error('tax')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea name="notes" id="notes" rows="3"
                                  class="form-control @error('notes') is-invalid @enderror"
                                  placeholder="Optional notes for this invoice">{{ old('notes', $invoice->notes) }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column - Summary -->
        <div class="col-lg-4">
            <!-- Invoice Summary -->
            <div class="card mb-4">
                <div class="card-header bg-dark text-white">
                    <i class="fas fa-receipt me-2"></i> Invoice Summary
                </div>
                <div class="card-body invoice-summary">
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span id="displaySubtotal">RM {{ number_format($invoice->subtotal, 2) }}</span>
                    </div>
                    <div class="summary-row">
                        <span>Online Fee</span>
                        <span id="displayOnlineFee">RM {{ number_format($invoice->online_fee, 2) }}</span>
                    </div>
                    <div class="summary-row">
                        <span>Discount</span>
                        <span id="displayDiscount">- RM {{ number_format($invoice->discount, 2) }}</span>
                    </div>
                    <div class="summary-row">
                        <span>Tax</span>
                        <span id="displayTax">RM {{ number_format($invoice->tax, 2) }}</span>
                    </div>
                    <hr class="my-2 bg-white">
                    <div class="summary-row">
                        <span>Total Amount</span>
                        <span id="displayTotal">RM {{ number_format($invoice->total_amount, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Payment Status -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-money-bill-wave me-2"></i> Payment Status
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Amount:</span>
                        <strong>RM {{ number_format($invoice->total_amount, 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Paid Amount:</span>
                        <strong class="text-success">RM {{ number_format($invoice->paid_amount, 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Balance:</span>
                        <strong class="text-{{ $invoice->balance > 0 ? 'danger' : 'success' }}">
                            RM {{ number_format($invoice->balance, 2) }}
                        </strong>
                    </div>
                    <hr>
                    <div class="text-center">
                        <span class="badge bg-{{ $invoice->status === 'paid' ? 'success' : ($invoice->status === 'overdue' ? 'danger' : ($invoice->status === 'partial' ? 'warning' : 'secondary')) }} fs-6">
                            {{ ucfirst($invoice->status) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-save me-2"></i> Update Invoice
                </button>
                <a href="{{ route('admin.invoices.show', $invoice) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-2"></i> Cancel
                </a>
            </div>
        </div>
    </div>
</form>
@endif
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Calculate totals when amount fields change
    $('.amount-input').on('input', function() {
        calculateTotal();
    });

    function calculateTotal() {
        var subtotal = parseFloat($('#subtotal').val()) || 0;
        var onlineFee = parseFloat($('#online_fee').val()) || 0;
        var discount = parseFloat($('#discount').val()) || 0;
        var tax = parseFloat($('#tax').val()) || 0;

        var total = subtotal + onlineFee - discount + tax;

        $('#displaySubtotal').text('RM ' + subtotal.toFixed(2));
        $('#displayOnlineFee').text('RM ' + onlineFee.toFixed(2));
        $('#displayDiscount').text('- RM ' + discount.toFixed(2));
        $('#displayTax').text('RM ' + tax.toFixed(2));
        $('#displayTotal').text('RM ' + total.toFixed(2));
    }
});
</script>
@endpush
