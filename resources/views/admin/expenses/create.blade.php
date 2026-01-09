@extends('layouts.app')

@section('title', 'Create Expense Voucher')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="h3 mb-0">Create Expense Voucher</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.expenses.index') }}">Expenses</a></li>
                    <li class="breadcrumb-item active">Create</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('admin.expenses.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to List
            </a>
        </div>
    </div>

    <form action="{{ route('admin.expenses.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="row">
            <!-- Main Form -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-file-invoice me-2"></i>Voucher Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <!-- Category -->
                            <div class="col-md-6">
                                <label class="form-label">Category <span class="text-danger">*</span></label>
                                <select name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                                    <option value="">-- Select Category --</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Date -->
                            <div class="col-md-6">
                                <label class="form-label">Expense Date <span class="text-danger">*</span></label>
                                <input type="date" name="expense_date" class="form-control @error('expense_date') is-invalid @enderror"
                                       value="{{ old('expense_date', date('Y-m-d')) }}" max="{{ date('Y-m-d') }}" required>
                                @error('expense_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Payee/Vendor -->
                            <div class="col-md-6">
                                <label class="form-label">Payee/Vendor Name</label>
                                <input type="text" name="vendor_name" class="form-control @error('vendor_name') is-invalid @enderror"
                                       value="{{ old('vendor_name') }}" placeholder="Enter payee or vendor name">
                                @error('vendor_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Amount -->
                            <div class="col-md-6">
                                <label class="form-label">Amount (RM) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">RM</span>
                                    <input type="number" name="amount" class="form-control @error('amount') is-invalid @enderror"
                                           value="{{ old('amount') }}" step="0.01" min="0.01" placeholder="0.00" required>
                                    @error('amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="col-12">
                                <label class="form-label">Description <span class="text-danger">*</span></label>
                                <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                                          rows="3" placeholder="Enter expense description..." required>{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Payment Method -->
                            <div class="col-md-6">
                                <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                                <select name="payment_method" class="form-select @error('payment_method') is-invalid @enderror" required>
                                    <option value="">-- Select Payment Method --</option>
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

                            <!-- Reference Number -->
                            <div class="col-md-6">
                                <label class="form-label">Reference Number</label>
                                <input type="text" name="reference_number" class="form-control @error('reference_number') is-invalid @enderror"
                                       value="{{ old('reference_number') }}" placeholder="Bank ref, cheque no, etc.">
                                @error('reference_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Invoice Number -->
                            <div class="col-md-6">
                                <label class="form-label">Invoice Number</label>
                                <input type="text" name="invoice_number" class="form-control @error('invoice_number') is-invalid @enderror"
                                       value="{{ old('invoice_number') }}" placeholder="Supplier invoice number">
                                @error('invoice_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Budget Amount -->
                            <div class="col-md-6">
                                <label class="form-label">Budget Amount (Optional)</label>
                                <div class="input-group">
                                    <span class="input-group-text">RM</span>
                                    <input type="number" name="budget_amount" class="form-control @error('budget_amount') is-invalid @enderror"
                                           value="{{ old('budget_amount') }}" step="0.01" min="0" placeholder="0.00">
                                    @error('budget_amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <small class="text-muted">Compare expense against budget</small>
                            </div>

                            <!-- Notes -->
                            <div class="col-12">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" class="form-control @error('notes') is-invalid @enderror"
                                          rows="2" placeholder="Additional notes...">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-md-4">
                <!-- Receipt Upload -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-paperclip me-2"></i>Attachment</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Receipt/Invoice</label>
                            <input type="file" name="receipt" class="form-control @error('receipt') is-invalid @enderror"
                                   accept=".pdf,.jpg,.jpeg,.png">
                            @error('receipt')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Max 5MB. Allowed: PDF, JPG, PNG</small>
                        </div>
                    </div>
                </div>

                <!-- Recurring Options -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-sync me-2"></i>Recurring Options</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check mb-3">
                            <input type="checkbox" name="is_recurring" id="is_recurring" class="form-check-input"
                                   value="1" {{ old('is_recurring') ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_recurring">This is a recurring expense</label>
                        </div>

                        <div id="recurring_options" style="{{ old('is_recurring') ? '' : 'display: none;' }}">
                            <label class="form-label">Recurring Frequency</label>
                            <select name="recurring_frequency" class="form-select @error('recurring_frequency') is-invalid @enderror">
                                <option value="">-- Select Frequency --</option>
                                @foreach($recurringFrequencies as $key => $label)
                                    <option value="{{ $key }}" {{ old('recurring_frequency') == $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('recurring_frequency')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="card">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save me-1"></i> Save Expense Voucher
                            </button>
                            <a href="{{ route('admin.expenses.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i> Cancel
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('is_recurring').addEventListener('change', function() {
    document.getElementById('recurring_options').style.display = this.checked ? 'block' : 'none';
});
</script>
@endpush
