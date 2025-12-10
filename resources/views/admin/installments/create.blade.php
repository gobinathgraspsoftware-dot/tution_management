@extends('layouts.app')

@section('title', 'Create Installment Plan')
@section('page-title', 'Create Installment Plan')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="fas fa-plus-circle me-2"></i> Create Installment Plan</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.installments.index') }}">Installments</a></li>
                <li class="breadcrumb-item active">Create Plan</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('admin.installments.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-cog me-2"></i> Plan Configuration</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.installments.store') }}" method="POST" id="installmentForm">
                    @csrf

                    <!-- Invoice Selection -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">Select Invoice <span class="text-danger">*</span></label>
                        @if($invoice)
                            <input type="hidden" name="invoice_id" value="{{ $invoice->id }}">
                            <div class="alert alert-info">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ $invoice->invoice_number }}</strong> -
                                        {{ $invoice->student->user->name ?? 'N/A' }}
                                    </div>
                                    <div class="text-end">
                                        <strong>Balance: RM {{ number_format($invoice->balance, 2) }}</strong>
                                    </div>
                                </div>
                            </div>
                        @else
                            <select name="invoice_id" id="invoiceSelect" class="form-select @error('invoice_id') is-invalid @enderror" required>
                                <option value="">-- Select an Invoice --</option>
                                @foreach($eligibleInvoices as $inv)
                                    <option value="{{ $inv->id }}"
                                            data-balance="{{ $inv->balance }}"
                                            data-student="{{ $inv->student->user->name ?? 'N/A' }}"
                                            data-package="{{ $inv->enrollment->package->name ?? 'N/A' }}"
                                            {{ old('invoice_id') == $inv->id ? 'selected' : '' }}>
                                        {{ $inv->invoice_number }} - {{ $inv->student->user->name ?? 'N/A' }}
                                        (Balance: RM {{ number_format($inv->balance, 2) }})
                                    </option>
                                @endforeach
                            </select>
                            @error('invoice_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        @endif
                    </div>

                    <!-- Selected Invoice Details -->
                    <div id="invoiceDetails" class="alert alert-light mb-4" style="{{ $invoice ? '' : 'display: none;' }}">
                        <div class="row">
                            <div class="col-md-4">
                                <small class="text-muted">Student</small>
                                <p class="mb-0 fw-bold" id="detailStudent">{{ $invoice->student->user->name ?? '-' }}</p>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted">Package</small>
                                <p class="mb-0 fw-bold" id="detailPackage">{{ $invoice->enrollment->package->name ?? '-' }}</p>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted">Outstanding Balance</small>
                                <p class="mb-0 fw-bold text-danger" id="detailBalance">RM {{ number_format($invoice->balance ?? 0, 2) }}</p>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Plan Settings -->
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Number of Installments <span class="text-danger">*</span></label>
                            <select name="number_of_installments" id="numInstallments" class="form-select @error('number_of_installments') is-invalid @enderror" required>
                                @for($i = 2; $i <= 12; $i++)
                                    <option value="{{ $i }}" {{ old('number_of_installments', 3) == $i ? 'selected' : '' }}>{{ $i }} Installments</option>
                                @endfor
                            </select>
                            @error('number_of_installments')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Start Date <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" id="startDate"
                                   class="form-control @error('start_date') is-invalid @enderror"
                                   value="{{ old('start_date', date('Y-m-d')) }}"
                                   min="{{ date('Y-m-d') }}" required>
                            @error('start_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Interval (Days)</label>
                            <select name="interval_days" id="intervalDays" class="form-select">
                                <option value="7" {{ old('interval_days') == 7 ? 'selected' : '' }}>Weekly (7 days)</option>
                                <option value="14" {{ old('interval_days') == 14 ? 'selected' : '' }}>Bi-weekly (14 days)</option>
                                <option value="30" {{ old('interval_days', 30) == 30 ? 'selected' : '' }}>Monthly (30 days)</option>
                                <option value="custom" {{ old('interval_days') == 'custom' ? 'selected' : '' }}>Custom</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3" id="customIntervalRow" style="display: none;">
                        <div class="col-md-4">
                            <label class="form-label">Custom Interval (Days)</label>
                            <input type="number" name="custom_interval" id="customInterval" class="form-control" min="1" max="90" value="{{ old('custom_interval', 30) }}">
                        </div>
                    </div>

                    <!-- Custom Amounts Toggle -->
                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="useCustomAmounts" name="use_custom_amounts">
                            <label class="form-check-label" for="useCustomAmounts">
                                <strong>Use Custom Amounts</strong> (unequal installment amounts)
                            </label>
                        </div>
                    </div>

                    <!-- Installment Preview -->
                    <div class="card bg-light mb-4">
                        <div class="card-header bg-secondary text-white">
                            <h6 class="mb-0"><i class="fas fa-calculator me-2"></i> Installment Schedule Preview</h6>
                        </div>
                        <div class="card-body">
                            <div id="installmentPreview">
                                <p class="text-muted text-center py-3">Select an invoice to preview installment schedule</p>
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="mb-4">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Optional notes about this installment plan...">{{ old('notes') }}</textarea>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.installments.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Create Installment Plan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Sidebar Info -->
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i> Guidelines</h6>
            </div>
            <div class="card-body">
                <ul class="mb-0">
                    <li class="mb-2">Minimum 2, maximum 12 installments allowed</li>
                    <li class="mb-2">First installment due on start date</li>
                    <li class="mb-2">Reminders sent on 10th, 18th, 24th of each month</li>
                    <li class="mb-2">Overdue status applied after due date + grace period</li>
                    <li>Custom amounts must equal total balance</li>
                </ul>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-warning text-dark">
                <h6 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i> Important</h6>
            </div>
            <div class="card-body">
                <p class="mb-2">Once created, installment plans:</p>
                <ul class="mb-0">
                    <li>Cannot be deleted if payments exist</li>
                    <li>Can have individual dates modified</li>
                    <li>Will trigger automatic reminders</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const invoiceSelect = document.getElementById('invoiceSelect');
    const numInstallments = document.getElementById('numInstallments');
    const startDate = document.getElementById('startDate');
    const intervalDays = document.getElementById('intervalDays');
    const customIntervalRow = document.getElementById('customIntervalRow');
    const customInterval = document.getElementById('customInterval');
    const useCustomAmounts = document.getElementById('useCustomAmounts');
    const previewDiv = document.getElementById('installmentPreview');
    const detailsDiv = document.getElementById('invoiceDetails');

    let balance = {{ $invoice->balance ?? 0 }};

    // Invoice selection change
    if (invoiceSelect) {
        invoiceSelect.addEventListener('change', function() {
            const option = this.options[this.selectedIndex];
            if (option.value) {
                balance = parseFloat(option.dataset.balance);
                document.getElementById('detailStudent').textContent = option.dataset.student;
                document.getElementById('detailPackage').textContent = option.dataset.package;
                document.getElementById('detailBalance').textContent = 'RM ' + balance.toFixed(2);
                detailsDiv.style.display = 'block';
                updatePreview();
            } else {
                detailsDiv.style.display = 'none';
                previewDiv.innerHTML = '<p class="text-muted text-center py-3">Select an invoice to preview installment schedule</p>';
            }
        });
    }

    // Interval days change
    intervalDays.addEventListener('change', function() {
        customIntervalRow.style.display = this.value === 'custom' ? 'block' : 'none';
        updatePreview();
    });

    // Update preview on any change
    [numInstallments, startDate, customInterval, useCustomAmounts].forEach(el => {
        el.addEventListener('change', updatePreview);
    });

    function updatePreview() {
        const num = parseInt(numInstallments.value);
        const start = new Date(startDate.value);
        let interval = intervalDays.value === 'custom' ? parseInt(customInterval.value) : parseInt(intervalDays.value);
        const isCustom = useCustomAmounts.checked;

        if (!startDate.value || balance <= 0) {
            previewDiv.innerHTML = '<p class="text-muted text-center py-3">Select an invoice to preview installment schedule</p>';
            return;
        }

        const installmentAmount = (balance / num).toFixed(2);
        let html = '<div class="table-responsive"><table class="table table-sm mb-0">';
        html += '<thead><tr><th>#</th><th>Due Date</th><th>Amount</th></tr></thead><tbody>';

        let totalAmount = 0;
        for (let i = 0; i < num; i++) {
            const dueDate = new Date(start);
            dueDate.setDate(dueDate.getDate() + (interval * i));

            let amount = parseFloat(installmentAmount);
            // Add remainder to last installment
            if (i === num - 1) {
                amount = balance - totalAmount;
            }
            totalAmount += amount;

            html += '<tr>';
            html += '<td><span class="badge bg-secondary">' + (i + 1) + '</span></td>';
            html += '<td>' + dueDate.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) + '</td>';

            if (isCustom) {
                html += '<td><input type="number" name="custom_amounts[]" class="form-control form-control-sm custom-amount" style="width:120px" step="0.01" min="0" value="' + amount.toFixed(2) + '" required></td>';
            } else {
                html += '<td>RM ' + amount.toFixed(2) + '</td>';
            }

            html += '</tr>';
        }

        html += '</tbody>';
        html += '<tfoot><tr class="table-secondary"><td colspan="2" class="text-end"><strong>Total:</strong></td>';
        html += '<td><strong id="totalPreview">RM ' + balance.toFixed(2) + '</strong></td></tr></tfoot>';
        html += '</table></div>';

        previewDiv.innerHTML = html;

        // Add listeners for custom amounts
        if (isCustom) {
            document.querySelectorAll('.custom-amount').forEach(input => {
                input.addEventListener('change', validateCustomAmounts);
            });
        }
    }

    function validateCustomAmounts() {
        const inputs = document.querySelectorAll('.custom-amount');
        let total = 0;
        inputs.forEach(input => {
            total += parseFloat(input.value) || 0;
        });

        const totalEl = document.getElementById('totalPreview');
        if (Math.abs(total - balance) > 0.01) {
            totalEl.innerHTML = '<span class="text-danger">RM ' + total.toFixed(2) + ' (Must equal RM ' + balance.toFixed(2) + ')</span>';
        } else {
            totalEl.innerHTML = '<span class="text-success">RM ' + total.toFixed(2) + ' ✓</span>';
        }
    }

    // Initial preview if invoice is pre-selected
    @if($invoice)
    updatePreview();
    @endif
});
</script>
@endpush
