@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-12">
            <h1 class="h3 mb-0">Add Seminar Expense</h1>
            <p class="text-muted">{{ $seminar->name }} ({{ $seminar->code }})</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Expense Details</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.seminars.accounting.expenses.store', $seminar) }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Expense Date <span class="text-danger">*</span></label>
                                <input type="date" name="expense_date" class="form-control @error('expense_date') is-invalid @enderror" 
                                       value="{{ old('expense_date', date('Y-m-d')) }}" required>
                                @error('expense_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Category <span class="text-danger">*</span></label>
                                <select name="category" class="form-select @error('category') is-invalid @enderror" required>
                                    <option value="">Select Category</option>
                                    @foreach($categories as $key => $label)
                                        <option value="{{ $key }}" {{ old('category') == $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description <span class="text-danger">*</span></label>
                            <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                                      rows="3" required>{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Amount (RM) <span class="text-danger">*</span></label>
                                <input type="number" name="amount" class="form-control @error('amount') is-invalid @enderror" 
                                       step="0.01" min="0" value="{{ old('amount') }}" required>
                                @error('amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Payment Method</label>
                                <select name="payment_method" class="form-select @error('payment_method') is-invalid @enderror">
                                    <option value="">Select Method</option>
                                    <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                                    <option value="bank_transfer" {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                    <option value="online" {{ old('payment_method') == 'online' ? 'selected' : '' }}>Online Payment</option>
                                    <option value="cheque" {{ old('payment_method') == 'cheque' ? 'selected' : '' }}>Cheque</option>
                                    <option value="card" {{ old('payment_method') == 'card' ? 'selected' : '' }}>Card</option>
                                </select>
                                @error('payment_method')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Reference/Receipt Number</label>
                            <input type="text" name="reference_number" class="form-control @error('reference_number') is-invalid @enderror" 
                                   value="{{ old('reference_number') }}">
                            @error('reference_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Upload Receipt/Invoice</label>
                            <input type="file" name="receipt" class="form-control @error('receipt') is-invalid @enderror" 
                                   accept=".pdf,.jpg,.jpeg,.png">
                            <small class="text-muted">Accepted formats: PDF, JPG, PNG (Max: 5MB)</small>
                            @error('receipt')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Additional Notes</label>
                            <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" 
                                      rows="3">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> This expense will be submitted for approval before being added to the seminar's financial records.
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.seminars.accounting.expenses', $seminar) }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Expense
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Expense Categories</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        @foreach($categories as $key => $label)
                        <li class="mb-2">
                            <i class="fas fa-circle text-primary" style="font-size: 8px;"></i>
                            <strong>{{ $label }}</strong>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">Tips</h5>
                </div>
                <div class="card-body">
                    <ul class="small">
                        <li>Provide detailed descriptions for better tracking</li>
                        <li>Always upload receipts when available</li>
                        <li>Use appropriate categories for accurate reporting</li>
                        <li>Expenses require approval before being finalized</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
