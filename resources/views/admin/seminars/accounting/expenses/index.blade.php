@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-3">
        <div class="col-md-8">
            <h1 class="h3 mb-0">Seminar Expenses</h1>
            <p class="text-muted mb-0">{{ $seminar->name }} ({{ $seminar->code }})</p>
        </div>
        <div class="col-md-4 text-end">
            @can('create-seminar-expenses')
            <a href="{{ route('admin.seminars.accounting.expenses.create', $seminar) }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Expense
            </a>
            @endcan
            <a href="{{ route('admin.seminars.show', $seminar) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Seminar
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h6 class="mb-1">Total Expenses</h6>
                    <h4 class="mb-0">RM {{ number_format($summary['total'], 2) }}</h4>
                    <small>{{ $summary['count'] }} items</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6 class="mb-1">Approved</h6>
                    <h4 class="mb-0">RM {{ number_format($summary['approved'], 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h6 class="mb-1">Pending Approval</h6>
                    <h4 class="mb-0">RM {{ number_format($summary['pending'], 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h6 class="mb-1">Rejected</h6>
                    <h4 class="mb-0">RM {{ number_format($summary['rejected'], 2) }}</h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Expense List -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Expense List</h5>
            <div>
                @can('export-expenses')
                <a href="{{ route('admin.seminars.accounting.expenses.export', $seminar) }}" class="btn btn-sm btn-success">
                    <i class="fas fa-file-excel"></i> Export
                </a>
                @endcan
            </div>
        </div>
        <div class="card-body p-0">
            @if($expenses->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Category</th>
                            <th>Description</th>
                            <th>Amount</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($expenses as $expense)
                        <tr>
                            <td>{{ $expense->expense_date->format('d M Y') }}</td>
                            <td>
                                <span class="badge bg-secondary">{{ $expense->category_label }}</span>
                            </td>
                            <td>
                                {{ Str::limit($expense->description, 60) }}
                                @if($expense->receipt_path)
                                <a href="{{ Storage::url($expense->receipt_path) }}" target="_blank" class="text-primary" title="View Receipt">
                                    <i class="fas fa-paperclip"></i>
                                </a>
                                @endif
                            </td>
                            <td><strong>RM {{ number_format($expense->amount, 2) }}</strong></td>
                            <td>
                                @if($expense->payment_method)
                                    <small class="text-muted">{{ ucfirst($expense->payment_method) }}</small>
                                    @if($expense->reference_number)
                                        <br><small class="text-muted">{{ $expense->reference_number }}</small>
                                    @endif
                                @else
                                    <small class="text-muted">-</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $expense->status_badge_class }}">
                                    {{ ucfirst($expense->approval_status) }}
                                </span>
                                @if($expense->approval_status === 'rejected' && $expense->rejection_reason)
                                    <i class="fas fa-info-circle text-danger" 
                                       data-bs-toggle="tooltip" 
                                       title="{{ $expense->rejection_reason }}"></i>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    @if($expense->approval_status === 'pending')
                                        @can('approve-expenses')
                                        <button type="button" 
                                                class="btn btn-success approve-expense" 
                                                data-expense-id="{{ $expense->id }}">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button type="button" 
                                                class="btn btn-danger reject-expense" 
                                                data-expense-id="{{ $expense->id }}">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        @endcan
                                        @can('edit-seminar-expenses')
                                        <a href="{{ route('admin.seminars.accounting.expenses.edit', [$seminar, $expense]) }}" 
                                           class="btn btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @endcan
                                        @can('delete-seminar-expenses')
                                        <button type="button" 
                                                class="btn btn-danger delete-expense" 
                                                data-expense-id="{{ $expense->id }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        @endcan
                                    @else
                                        <button type="button" class="btn btn-info view-expense" data-expense="{{ json_encode($expense) }}">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-3">
                {{ $expenses->links() }}
            </div>
            @else
            <div class="text-center py-5">
                <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                <p class="text-muted">No expenses recorded yet.</p>
                @can('create-seminar-expenses')
                <a href="{{ route('admin.seminars.accounting.expenses.create', $seminar) }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add First Expense
                </a>
                @endcan
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Rejection Modal -->
<div class="modal fade" id="rejectionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reject Expense</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="rejectionReason" rows="3" required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmReject">Reject Expense</button>
            </div>
        </div>
    </div>
</div>

<!-- View Expense Modal -->
<div class="modal fade" id="viewExpenseModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Expense Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="expenseDetails">
                <!-- Will be populated by JavaScript -->
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();

    let currentExpenseId = null;

    // Approve expense
    $('.approve-expense').click(function() {
        const expenseId = $(this).data('expense-id');
        
        if (confirm('Are you sure you want to approve this expense?')) {
            $.ajax({
                url: `/admin/seminars/{{ $seminar->id }}/accounting/expenses/${expenseId}/approve`,
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        showAlert('success', response.message);
                        setTimeout(() => location.reload(), 1500);
                    }
                },
                error: function(xhr) {
                    showAlert('danger', xhr.responseJSON?.message || 'Failed to approve expense');
                }
            });
        }
    });

    // Reject expense
    $('.reject-expense').click(function() {
        currentExpenseId = $(this).data('expense-id');
        $('#rejectionReason').val('');
        $('#rejectionModal').modal('show');
    });

    // Confirm reject
    $('#confirmReject').click(function() {
        const reason = $('#rejectionReason').val().trim();
        
        if (!reason) {
            alert('Please provide a rejection reason');
            return;
        }

        $.ajax({
            url: `/admin/seminars/{{ $seminar->id }}/accounting/expenses/${currentExpenseId}/reject`,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: { rejection_reason: reason },
            success: function(response) {
                if (response.success) {
                    $('#rejectionModal').modal('hide');
                    showAlert('success', response.message);
                    setTimeout(() => location.reload(), 1500);
                }
            },
            error: function(xhr) {
                showAlert('danger', xhr.responseJSON?.message || 'Failed to reject expense');
            }
        });
    });

    // Delete expense
    $('.delete-expense').click(function() {
        const expenseId = $(this).data('expense-id');
        
        if (confirm('Are you sure you want to delete this expense? This action cannot be undone.')) {
            $.ajax({
                url: `/admin/seminars/{{ $seminar->id }}/accounting/expenses/${expenseId}`,
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    showAlert('success', 'Expense deleted successfully');
                    setTimeout(() => location.reload(), 1500);
                },
                error: function(xhr) {
                    showAlert('danger', xhr.responseJSON?.message || 'Failed to delete expense');
                }
            });
        }
    });

    // View expense details
    $('.view-expense').click(function() {
        const expense = $(this).data('expense');
        const html = `
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Date:</strong> ${new Date(expense.expense_date).toLocaleDateString()}</p>
                    <p><strong>Category:</strong> <span class="badge bg-secondary">${expense.category_label}</span></p>
                    <p><strong>Amount:</strong> <strong>RM ${parseFloat(expense.amount).toFixed(2)}</strong></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Payment Method:</strong> ${expense.payment_method ? expense.payment_method : '-'}</p>
                    <p><strong>Reference:</strong> ${expense.reference_number || '-'}</p>
                    <p><strong>Status:</strong> <span class="badge bg-${expense.status_badge_class}">${expense.approval_status}</span></p>
                </div>
                <div class="col-md-12">
                    <p><strong>Description:</strong></p>
                    <p>${expense.description}</p>
                    ${expense.notes ? '<p><strong>Notes:</strong></p><p>' + expense.notes + '</p>' : ''}
                    ${expense.rejection_reason ? '<div class="alert alert-danger"><strong>Rejection Reason:</strong> ' + expense.rejection_reason + '</div>' : ''}
                    ${expense.approved_at ? '<p><small class="text-muted">Processed on ' + new Date(expense.approved_at).toLocaleString() + '</small></p>' : ''}
                </div>
            </div>
        `;
        $('#expenseDetails').html(html);
        $('#viewExpenseModal').modal('show');
    });

    function showAlert(type, message) {
        const alert = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        $('body').prepend(alert);
        setTimeout(() => $('.alert').alert('close'), 3000);
    }
});
</script>
@endpush
