@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-edit"></i> Edit Expense</h2>
        <a href="{{ route('admin.expenses.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.expenses.update', $expense) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label required">Expense Category</label>
                        <select name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id', $expense->category_id) == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('category_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label required">Expense Date</label>
                        <input type="date" name="expense_date" class="form-control @error('expense_date') is-invalid @enderror"
                               value="{{ old('expense_date', $expense->expense_date->format('Y-m-d')) }}" max="{{ now()->format('Y-m-d') }}" required>
                        @error('expense_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="form-label required">Description</label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                                  rows="3" required>{{ old('description', $expense->description) }}</textarea>
                        @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label required">Amount (RM)</label>
                        <input type="number" name="amount" step="0.01" min="0"
                               class="form-control @error('amount') is-invalid @enderror"
                               value="{{ old('amount', $expense->amount) }}" required>
                        @error('amount')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Budget Amount (RM)</label>
                        <input type="number" name="budget_amount" step="0.01" min="0"
                               class="form-control @error('budget_amount') is-invalid @enderror"
                               value="{{ old('budget_amount', $expense->budget_amount) }}">
                        <small class="text-muted">Optional: Set budget for comparison</small>
                        @error('budget_amount')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label required">Payment Method</label>
                        <select name="payment_method" class="form-select @error('payment_method') is-invalid @enderror" required>
                            <option value="">Select Payment Method</option>
                            @foreach($paymentMethods as $key => $method)
                            <option value="{{ $key }}" {{ old('payment_method', $expense->payment_method) == $key ? 'selected' : '' }}>
                                {{ $method }}
                            </option>
                            @endforeach
                        </select>
                        @error('payment_method')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Reference Number</label>
                        <input type="text" name="reference_number" class="form-control @error('reference_number') is-invalid @enderror"
                               value="{{ old('reference_number', $expense->reference_number) }}">
                        @error('reference_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Vendor Name</label>
                        <input type="text" name="vendor_name" class="form-control @error('vendor_name') is-invalid @enderror"
                               value="{{ old('vendor_name', $expense->vendor_name) }}">
                        @error('vendor_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Invoice Number</label>
                        <input type="text" name="invoice_number" class="form-control @error('invoice_number') is-invalid @enderror"
                               value="{{ old('invoice_number', $expense->invoice_number) }}">
                        @error('invoice_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="form-label">Receipt/Invoice</label>
                        @if($expense->receipt_path)
                        <div class="mb-2">
                            <small class="text-muted">Current file: {{ basename($expense->receipt_path) }}</small>
                            <a href="{{ route('admin.expenses.download-receipt', $expense) }}" class="btn btn-sm btn-info" target="_blank">
                                <i class="fas fa-download"></i> Download
                            </a>
                        </div>
                        @endif
                        <input type="file" name="receipt" class="form-control @error('receipt') is-invalid @enderror"
                               accept="image/*,.pdf">
                        <small class="text-muted">Upload new file to replace existing. Supported: JPG, PNG, PDF (Max: 5MB)</small>
                        @error('receipt')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-12 mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="is_recurring" value="1" class="form-check-input"
                                   id="isRecurring" {{ old('is_recurring', $expense->is_recurring) ? 'checked' : '' }}
                                   onchange="toggleRecurringFields()">
                            <label class="form-check-label" for="isRecurring">
                                This is a recurring expense
                            </label>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3" id="recurringFrequency" style="display: {{ $expense->is_recurring ? 'block' : 'none' }};">
                        <label class="form-label">Recurring Frequency</label>
                        <select name="recurring_frequency" class="form-select">
                            <option value="">Select Frequency</option>
                            @foreach($recurringFrequencies as $key => $freq)
                            <option value="{{ $key }}" {{ old('recurring_frequency', $expense->recurring_frequency) == $key ? 'selected' : '' }}>
                                {{ $freq }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control @error('notes') is-invalid @enderror"
                                  rows="2">{{ old('notes', $expense->notes) }}</textarea>
                        @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.expenses.show', $expense) }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Expense
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleRecurringFields() {
    const isRecurring = document.getElementById('isRecurring').checked;
    document.getElementById('recurringFrequency').style.display = isRecurring ? 'block' : 'none';
}

// Check on page load
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('isRecurring').checked) {
        toggleRecurringFields();
    }
});
</script>
@endsection
