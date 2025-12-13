@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-12">
            <h1 class="h3 mb-0">Edit Seminar Expense</h1>
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
                    <form action="{{ route('admin.seminars.accounting.expenses.update', [$seminar, $expense]) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Expense Date <span class="text-danger">*</span></label>
                                <input type="date" name="expense_date" class="form-control @error('expense_date') is-invalid @enderror" 
                                       value="{{ old('expense_date', $expense->expense_date->format('Y-m-d')) }}" required>
                                @error('expense_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Category <span class="text-danger">*</span></label>
                                <select name="category" class="form-select @error('category') is-invalid @enderror" required>
                                    <option value="">Select Category</option>
                                    @foreach($categories as $key => $label)
                                        <option value="{{ $key }}" {{ old('category', $expense->category) == $key ? 'selected' : '' }}>
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
                                      rows="3" required>{{ old('description', $expense->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Amount (RM) <span class="text-danger">*</span></label>
                                <input type="number" name="amount" class="form-control @error('amount') is-invalid @enderror" 
                                       step="0.01" min="0" value="{{ old('amount', $expense->amount) }}" required>
                                @error('amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Payment Method</label>
                                <select name="payment_method" class="form-select @error('payment_method') is-invalid @enderror">
                                    <option value="">Select Method</option>
                                    <option value="cash" {{ old('payment_method', $expense->payment_method) == 'cash' ? 'selected' : '' }}>Cash</option>
                                    <option value="bank_transfer" {{ old('payment_method', $expense->payment_method) == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                    <option value="online" {{ old('payment_method', $expense->payment_method) == 'online' ? 'selected' : '' }}>Online Payment</option>
                                    <option value="cheque" {{ old('payment_method', $expense->payment_method) == 'cheque' ? 'selected' : '' }}>Cheque</option>
                                    <option value="card" {{ old('payment_method', $expense->payment_method) == 'card' ? 'selected' : '' }}>Card</option>
                                </select>
                                @error('payment_method')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Reference/Receipt Number</label>
                            <input type="text" name="reference_number" class="form-control @error('reference_number') is-invalid @enderror" 
                                   value="{{ old('reference_number', $expense->reference_number) }}">
                            @error('reference_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Upload Receipt/Invoice</label>
                            @if($expense->receipt_path)
                            <div class="mb-2">
                                <a href="{{ Storage::url($expense->receipt_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-file"></i> View Current Receipt
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-danger" id="deleteReceipt">
                                    <i class="fas fa-trash"></i> Delete Receipt
                                </button>
                            </div>
                            @endif
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
                                      rows="3">{{ old('notes', $expense->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.seminars.accounting.expenses', $seminar) }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Expense
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Expense Status</h5>
                </div>
                <div class="card-body">
                    <p><strong>Status:</strong> 
                        <span class="badge bg-{{ $expense->status_badge_class }}">
                            {{ ucfirst($expense->approval_status) }}
                        </span>
                    </p>
                    <p class="text-muted small">
                        <i class="fas fa-info-circle"></i> Only pending expenses can be edited.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#deleteReceipt').click(function() {
        if (confirm('Are you sure you want to delete the receipt?')) {
            $.ajax({
                url: '/admin/seminars/{{ $seminar->id }}/accounting/expenses/{{ $expense->id }}/delete-receipt',
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        alert('Receipt deleted successfully');
                        location.reload();
                    }
                },
                error: function() {
                    alert('Failed to delete receipt');
                }
            });
        }
    });
});
</script>
@endpush
