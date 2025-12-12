@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-receipt"></i> Expense Details</h2>
        <div class="btn-group">
            <a href="{{ route('admin.expenses.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
            @if($expense->isPending())
                @can('edit-expenses')
                <a href="{{ route('admin.expenses.edit', $expense) }}" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Edit
                </a>
                @endcan
            @endif
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> Expense Information</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Category:</strong>
                            <p class="mb-0">{{ $expense->category->name }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Expense Date:</strong>
                            <p class="mb-0">{{ $expense->expense_date->format('d M Y') }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <strong>Description:</strong>
                            <p class="mb-0">{{ $expense->description }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Amount:</strong>
                            <h4 class="text-primary mb-0">RM {{ number_format($expense->amount, 2) }}</h4>
                        </div>
                        @if($expense->budget_amount)
                        <div class="col-md-6">
                            <strong>Budget Amount:</strong>
                            <h5 class="mb-0">RM {{ number_format($expense->budget_amount, 2) }}</h5>
                            @if($expense->isOverBudget())
                            <span class="badge bg-danger">
                                <i class="fas fa-exclamation-triangle"></i> Over Budget by RM {{ number_format($expense->getVarianceAmount(), 2) }}
                            </span>
                            @else
                            <span class="badge bg-success">
                                <i class="fas fa-check"></i> Within Budget
                            </span>
                            @endif
                        </div>
                        @endif
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Payment Method:</strong>
                            <p class="mb-0">{{ ucfirst(str_replace('_', ' ', $expense->payment_method)) }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Reference Number:</strong>
                            <p class="mb-0">{{ $expense->reference_number ?? '-' }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Vendor Name:</strong>
                            <p class="mb-0">{{ $expense->vendor_name ?? '-' }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Invoice Number:</strong>
                            <p class="mb-0">{{ $expense->invoice_number ?? '-' }}</p>
                        </div>
                    </div>

                    @if($expense->is_recurring)
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <span class="badge bg-info">
                                <i class="fas fa-sync"></i> Recurring Expense - {{ ucfirst($expense->recurring_frequency) }}
                            </span>
                        </div>
                    </div>
                    @endif

                    @if($expense->notes)
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <strong>Notes:</strong>
                            <p class="mb-0">{{ $expense->notes }}</p>
                        </div>
                    </div>
                    @endif

                    @if($expense->receipt_path)
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <strong>Receipt/Invoice:</strong>
                            <div class="mt-2">
                                <a href="{{ route('admin.expenses.download-receipt', $expense) }}" class="btn btn-info" target="_blank">
                                    <i class="fas fa-download"></i> Download Receipt
                                </a>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            {{-- Status Card --}}
            <div class="card mb-4">
                <div class="card-header bg-{{ $expense->getStatusBadgeClass() }} text-white">
                    <h5 class="mb-0"><i class="fas fa-flag"></i> Status</h5>
                </div>
                <div class="card-body text-center">
                    <h3>
                        <span class="badge bg-{{ $expense->getStatusBadgeClass() }} fs-4">
                            {{ ucfirst($expense->status) }}
                        </span>
                    </h3>

                    @if($expense->isPending())
                        <div class="mt-3">
                            @can('approve-expenses')
                            <form action="{{ route('admin.expenses.approve', $expense) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success btn-lg w-100 mb-2" onclick="return confirm('Approve this expense?')">
                                    <i class="fas fa-check"></i> Approve
                                </button>
                            </form>
                            @endcan

                            @can('reject-expenses')
                            <button type="button" class="btn btn-danger btn-lg w-100" data-bs-toggle="modal" data-bs-target="#rejectModal">
                                <i class="fas fa-times"></i> Reject
                            </button>
                            @endcan
                        </div>
                    @endif

                    @if($expense->isApproved())
                        <p class="mt-3 mb-0">
                            <small>Approved by: {{ $expense->approvedBy->name ?? 'N/A' }}</small><br>
                            <small>Date: {{ $expense->approved_at ? $expense->approved_at->format('d M Y') : '-' }}</small>
                        </p>
                    @endif

                    @if($expense->isRejected())
                        <p class="mt-3 mb-0">
                            <small>Rejected by: {{ $expense->approvedBy->name ?? 'N/A' }}</small><br>
                            <small>Date: {{ $expense->rejected_at ? $expense->rejected_at->format('d M Y') : '-' }}</small>
                        </p>
                        @if($expense->rejection_reason)
                        <div class="alert alert-danger mt-3">
                            <strong>Reason:</strong><br>
                            {{ $expense->rejection_reason }}
                        </div>
                        @endif
                    @endif
                </div>
            </div>

            {{-- Audit Trail Card --}}
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-history"></i> Audit Trail</h6>
                </div>
                <div class="card-body">
                    <small>
                        <strong>Created By:</strong><br>
                        {{ $expense->createdBy->name ?? 'System' }}<br>
                        {{ $expense->created_at->format('d M Y, h:i A') }}
                    </small>
                    <hr>
                    <small>
                        <strong>Last Updated:</strong><br>
                        {{ $expense->updated_at->format('d M Y, h:i A') }}
                    </small>
                </div>
            </div>

            {{-- Actions --}}
            @if($expense->isPending() || $expense->isRejected())
            <div class="card mt-3">
                <div class="card-header bg-danger text-white">
                    <h6 class="mb-0"><i class="fas fa-trash"></i> Danger Zone</h6>
                </div>
                <div class="card-body">
                    @can('delete-expenses')
                    <form action="{{ route('admin.expenses.destroy', $expense) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this expense? This action cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="fas fa-trash"></i> Delete Expense
                        </button>
                    </form>
                    @endcan
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Reject Modal --}}
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.expenses.reject', $expense) }}" method="POST">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="fas fa-times-circle"></i> Reject Expense</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Rejection Reason</label>
                        <textarea name="rejection_reason" class="form-control" rows="4" required placeholder="Please provide a reason for rejecting this expense..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times"></i> Reject Expense
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
