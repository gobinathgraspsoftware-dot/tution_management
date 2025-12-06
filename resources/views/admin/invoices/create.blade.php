@extends('layouts.app')

@section('title', 'Create Invoice')
@section('page-title', 'Create New Invoice')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-file-invoice me-2"></i> Invoice Details</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.invoices.store') }}" method="POST" id="invoiceForm">
                        @csrf

                        <!-- Student Selection -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Student <span class="text-danger">*</span></label>
                                <select name="student_id" id="studentSelect" class="form-select @error('student_id') is-invalid @enderror" required>
                                    <option value="">Select Student</option>
                                    @foreach($students as $student)
                                        <option value="{{ $student->id }}"
                                            data-enrollments="{{ json_encode($student->enrollments) }}"
                                            {{ (old('student_id') ?? ($selectedStudent?->id)) == $student->id ? 'selected' : '' }}>
                                            {{ $student->user->name ?? 'Unknown' }} ({{ $student->student_id ?? 'N/A' }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('student_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Enrollment</label>
                                <select name="enrollment_id" id="enrollmentSelect" class="form-select @error('enrollment_id') is-invalid @enderror">
                                    <option value="">Select Enrollment (Optional)</option>
                                    @if($selectedStudent)
                                        @foreach($selectedStudent->enrollments as $enrollment)
                                            <option value="{{ $enrollment->id }}"
                                                data-fee="{{ $enrollment->monthly_fee ?? $enrollment->package->price }}"
                                                data-online-fee="{{ in_array($enrollment->package->type, ['online', 'hybrid']) ? ($enrollment->package->online_fee ?? 130) : 0 }}"
                                                data-package="{{ $enrollment->package->name }}"
                                                {{ (old('enrollment_id') ?? ($selectedEnrollment?->id)) == $enrollment->id ? 'selected' : '' }}>
                                                {{ $enrollment->package->name ?? 'Unknown Package' }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                                @error('enrollment_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Invoice Type & Period -->
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label class="form-label">Invoice Type <span class="text-danger">*</span></label>
                                <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                                    <option value="monthly" {{ old('type') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                    <option value="registration" {{ old('type') == 'registration' ? 'selected' : '' }}>Registration</option>
                                    <option value="renewal" {{ old('type') == 'renewal' ? 'selected' : '' }}>Renewal</option>
                                    <option value="additional" {{ old('type') == 'additional' ? 'selected' : '' }}>Additional</option>
                                    <option value="custom" {{ old('type') == 'custom' ? 'selected' : '' }}>Custom</option>
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Billing Period Start <span class="text-danger">*</span></label>
                                <input type="date" name="billing_period_start" class="form-control @error('billing_period_start') is-invalid @enderror"
                                    value="{{ old('billing_period_start', now()->startOfMonth()->format('Y-m-d')) }}" required>
                                @error('billing_period_start')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Billing Period End <span class="text-danger">*</span></label>
                                <input type="date" name="billing_period_end" class="form-control @error('billing_period_end') is-invalid @enderror"
                                    value="{{ old('billing_period_end', now()->endOfMonth()->format('Y-m-d')) }}" required>
                                @error('billing_period_end')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Amounts -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Subtotal (RM) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">RM</span>
                                    <input type="number" name="subtotal" id="subtotalInput" step="0.01" min="0"
                                        class="form-control @error('subtotal') is-invalid @enderror"
                                        value="{{ old('subtotal', 0) }}" required onchange="calculateTotal()">
                                </div>
                                @error('subtotal')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Online Fee (RM)</label>
                                <div class="input-group">
                                    <span class="input-group-text">RM</span>
                                    <input type="number" name="online_fee" id="onlineFeeInput" step="0.01" min="0"
                                        class="form-control @error('online_fee') is-invalid @enderror"
                                        value="{{ old('online_fee', 0) }}" onchange="calculateTotal()">
                                </div>
                                <small class="text-muted">Auto-filled for online/hybrid packages</small>
                                @error('online_fee')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Discount (RM)</label>
                                <div class="input-group">
                                    <span class="input-group-text">RM</span>
                                    <input type="number" name="discount" id="discountInput" step="0.01" min="0"
                                        class="form-control @error('discount') is-invalid @enderror"
                                        value="{{ old('discount', 0) }}" onchange="calculateTotal()">
                                </div>
                                @error('discount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Discount Reason</label>
                                <input type="text" name="discount_reason" class="form-control @error('discount_reason') is-invalid @enderror"
                                    value="{{ old('discount_reason') }}" placeholder="e.g., Referral Voucher">
                                @error('discount_reason')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Tax (RM)</label>
                                <div class="input-group">
                                    <span class="input-group-text">RM</span>
                                    <input type="number" name="tax" id="taxInput" step="0.01" min="0"
                                        class="form-control @error('tax') is-invalid @enderror"
                                        value="{{ old('tax', 0) }}" onchange="calculateTotal()">
                                </div>
                                <small class="text-muted">Usually 0 for educational services</small>
                                @error('tax')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Due Date <span class="text-danger">*</span></label>
                                <input type="date" name="due_date" class="form-control @error('due_date') is-invalid @enderror"
                                    value="{{ old('due_date', now()->addDays(14)->format('Y-m-d')) }}" required>
                                @error('due_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="mb-4">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3"
                                placeholder="Additional notes for this invoice...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('admin.invoices.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Create Invoice
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Invoice Preview -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm sticky-top" style="top: 80px;">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-receipt me-2"></i> Invoice Preview</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Student:</span>
                            <span id="previewStudent" class="fw-semibold">-</span>
                        </div>
                    </div>
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Package:</span>
                            <span id="previewPackage" class="fw-semibold">-</span>
                        </div>
                    </div>

                    <div class="mb-2">
                        <div class="d-flex justify-content-between">
                            <span>Subtotal:</span>
                            <span>RM <span id="previewSubtotal">0.00</span></span>
                        </div>
                    </div>
                    <div class="mb-2">
                        <div class="d-flex justify-content-between">
                            <span>Online Fee:</span>
                            <span>RM <span id="previewOnlineFee">0.00</span></span>
                        </div>
                    </div>
                    <div class="mb-2">
                        <div class="d-flex justify-content-between text-danger">
                            <span>Discount:</span>
                            <span>- RM <span id="previewDiscount">0.00</span></span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Tax:</span>
                            <span>RM <span id="previewTax">0.00</span></span>
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between">
                        <span class="h5 mb-0">Total:</span>
                        <span class="h5 mb-0 text-primary">RM <span id="previewTotal">0.00</span></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const studentSelect = document.getElementById('studentSelect');
    const enrollmentSelect = document.getElementById('enrollmentSelect');

    studentSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const enrollments = selectedOption.dataset.enrollments ? JSON.parse(selectedOption.dataset.enrollments) : [];

        // Clear and populate enrollment select
        enrollmentSelect.innerHTML = '<option value="">Select Enrollment (Optional)</option>';
        enrollments.forEach(function(enrollment) {
            const option = document.createElement('option');
            option.value = enrollment.id;
            option.dataset.fee = enrollment.monthly_fee || (enrollment.package?.price || 0);
            option.dataset.onlineFee = ['online', 'hybrid'].includes(enrollment.package?.type) ? (enrollment.package?.online_fee || 130) : 0;
            option.dataset.package = enrollment.package?.name || 'Unknown Package';
            option.textContent = enrollment.package?.name || 'Unknown Package';
            enrollmentSelect.appendChild(option);
        });

        // Update preview
        document.getElementById('previewStudent').textContent = selectedOption.text || '-';
        calculateTotal();
    });

    enrollmentSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value) {
            document.getElementById('subtotalInput').value = selectedOption.dataset.fee || 0;
            document.getElementById('onlineFeeInput').value = selectedOption.dataset.onlineFee || 0;
            document.getElementById('previewPackage').textContent = selectedOption.dataset.package || '-';
        }
        calculateTotal();
    });

    // Initialize
    if (studentSelect.value) {
        studentSelect.dispatchEvent(new Event('change'));
    }
    calculateTotal();
});

function calculateTotal() {
    const subtotal = parseFloat(document.getElementById('subtotalInput').value) || 0;
    const onlineFee = parseFloat(document.getElementById('onlineFeeInput').value) || 0;
    const discount = parseFloat(document.getElementById('discountInput').value) || 0;
    const tax = parseFloat(document.getElementById('taxInput').value) || 0;

    const total = subtotal + onlineFee - discount + tax;

    // Update preview
    document.getElementById('previewSubtotal').textContent = subtotal.toFixed(2);
    document.getElementById('previewOnlineFee').textContent = onlineFee.toFixed(2);
    document.getElementById('previewDiscount').textContent = discount.toFixed(2);
    document.getElementById('previewTax').textContent = tax.toFixed(2);
    document.getElementById('previewTotal').textContent = total.toFixed(2);
}
</script>
@endpush
@endsection
