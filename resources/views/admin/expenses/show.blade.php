@extends('layouts.app')

@section('title', 'Expense Voucher Details')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="h3 mb-0">Expense Voucher Details</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.expenses.index') }}">Expenses</a></li>
                    <li class="breadcrumb-item active">{{ $expense->voucher_number ?? 'EXP-' . str_pad($expense->id, 4, '0', STR_PAD_LEFT) }}</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 text-end">
            @if($expense->canBeEdited())
                @can('edit-expenses')
                <a href="{{ route('admin.expenses.edit', $expense) }}" class="btn btn-warning">
                    <i class="fas fa-edit me-1"></i> Edit
                </a>
                @endcan
            @endif
            <a href="{{ route('admin.expenses.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to List
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Main Details -->
        <div class="col-md-8">
            <!-- Voucher Card -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-file-invoice me-2"></i>
                        {{ $expense->voucher_number ?? 'EXP-' . str_pad($expense->id, 4, '0', STR_PAD_LEFT) }}
                    </h5>
                    <span class="badge bg-{{ $expense->getStatusBadgeClass() }} fs-6">{{ ucfirst($expense->status) }}</span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="text-muted" width="40%">Date:</td>
                                    <td class="fw-bold">{{ $expense->expense_date->format('d F Y') }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Category:</td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $expense->category->name ?? 'N/A' }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Payee/Vendor:</td>
                                    <td>{{ $expense->vendor_name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Payment Method:</td>
                                    <td>
                                        @switch($expense->payment_method)
                                            @case('cash')
                                                <span class="badge bg-success">Cash</span>
                                                @break
                                            @case('bank_transfer')
                                                <span class="badge bg-info">Bank Transfer</span>
                                                @break
                                            @case('cheque')
                                                <span class="badge bg-warning text-dark">Cheque</span>
                                                @break
                                            @default
                                                <span class="badge bg-secondary">{{ ucfirst($expense->payment_method) }}</span>
                                        @endswitch
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="text-muted" width="40%">Reference No:</td>
                                    <td>{{ $expense->reference_number ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Invoice No:</td>
                                    <td>{{ $expense->invoice_number ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Recurring:</td>
                                    <td>
                                        @if($expense->is_recurring)
                                            <span class="badge bg-info">{{ ucfirst($expense->recurring_frequency) }}</span>
                                        @else
                                            <span class="text-muted">No</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Created By:</td>
                                    <td>{{ $expense->createdBy->name ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <hr>

                    <!-- Description -->
                    <div class="mb-3">
                        <h6 class="text-muted mb-2">Description</h6>
                        <p class="mb-0">{{ $expense->description }}</p>
                    </div>

                    @if($expense->notes)
                    <div class="mb-3">
                        <h6 class="text-muted mb-2">Notes</h6>
                        <p class="mb-0">{{ $expense->notes }}</p>
                    </div>
                    @endif

                    <hr>

                    <!-- Amount Section -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="bg-light p-3 rounded">
                                <h6 class="text-muted mb-1">Amount</h6>
                                <h2 class="mb-0 text-primary">RM {{ number_format($expense->amount, 2) }}</h2>
                            </div>
                        </div>
                        @if($expense->budget_amount)
                        <div class="col-md-6">
                            <div class="bg-light p-3 rounded">
                                <h6 class="text-muted mb-1">Budget</h6>
                                <h4 class="mb-1">RM {{ number_format($expense->budget_amount, 2) }}</h4>
                                @php
                                    $variance = $expense->getVarianceAmount();
                                    $varianceClass = $variance > 0 ? 'text-danger' : 'text-success';
                                @endphp
                                <small class="{{ $varianceClass }}">
                                    Variance: RM {{ number_format(abs($variance), 2) }}
                                    ({{ $variance > 0 ? 'Over' : 'Under' }} budget)
                                </small>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Rejection Info (if rejected) -->
            @if($expense->isRejected() && $expense->rejection_reason)
            <div class="alert alert-danger">
                <h6 class="alert-heading"><i class="fas fa-times-circle me-2"></i>Rejection Details</h6>
                <p class="mb-1"><strong>Rejected by:</strong> {{ $expense->approvedBy->name ?? 'N/A' }}</p>
                <p class="mb-1"><strong>Rejected on:</strong> {{ $expense->rejected_at ? $expense->rejected_at->format('d/m/Y H:i') : '-' }}</p>
                <p class="mb-0"><strong>Reason:</strong> {{ $expense->rejection_reason }}</p>
            </div>
            @endif

            <!-- Approval Info (if approved) -->
            @if($expense->isApproved())
            <div class="alert alert-success">
                <h6 class="alert-heading"><i class="fas fa-check-circle me-2"></i>Approval Details</h6>
                <p class="mb-1"><strong>Approved by:</strong> {{ $expense->approvedBy->name ?? 'N/A' }}</p>
                <p class="mb-0"><strong>Approved on:</strong> {{ $expense->approved_at ? $expense->approved_at->format('d/m/Y') : '-' }}</p>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Actions Card -->
            @if($expense->isPending())
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-tasks me-2"></i>Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @can('approve-expenses')
                        <button type="button" class="btn btn-success" onclick="approveExpense()">
                            <i class="fas fa-check me-1"></i> Approve
                        </button>
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                            <i class="fas fa-times me-1"></i> Reject
                        </button>
                        @endcan
                    </div>
                </div>
            </div>
            @endif

            <!-- Attachment Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-paperclip me-2"></i>Attachment</h5>
                </div>
                <div class="card-body">
                    @if($expense->receipt_path)
                        <div class="d-flex align-items-center">
                            <i class="fas fa-file-pdf fa-3x text-danger me-3"></i>
                            <div>
                                <p class="mb-1 fw-bold">Receipt/Invoice</p>
                                <a href="{{ route('admin.expenses.download-receipt', $expense) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-download me-1"></i> Download
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="text-center text-muted py-3">
                            <i class="fas fa-file-alt fa-3x mb-2"></i>
                            <p class="mb-0">No attachment</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Audit Trail Card -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-history me-2"></i>Audit Trail</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <small class="text-muted">Created</small><br>
                            <span>{{ $expense->created_at->format('d/m/Y H:i') }}</span><br>
                            <small class="text-muted">by {{ $expense->createdBy->name ?? 'System' }}</small>
                        </li>
                        @if($expense->updated_at != $expense->created_at)
                        <li class="mb-2">
                            <small class="text-muted">Last Updated</small><br>
                            <span>{{ $expense->updated_at->format('d/m/Y H:i') }}</span>
                        </li>
                        @endif
                        @if($expense->approved_at)
                        <li class="mb-2">
                            <small class="text-muted">Approved</small><br>
                            <span>{{ $expense->approved_at->format('d/m/Y') }}</span><br>
                            <small class="text-muted">by {{ $expense->approvedBy->name ?? 'N/A' }}</small>
                        </li>
                        @endif
                        @if($expense->rejected_at)
                        <li class="mb-2">
                            <small class="text-muted">Rejected</small><br>
                            <span>{{ $expense->rejected_at->format('d/m/Y') }}</span><br>
                            <small class="text-muted">by {{ $expense->approvedBy->name ?? 'N/A' }}</small>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.expenses.reject', $expense) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Reject Expense</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea name="rejection_reason" class="form-control" rows="3" required placeholder="Please provide a reason for rejection..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject Expense</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Approve Form -->
<form id="approveForm" action="{{ route('admin.expenses.approve', $expense) }}" method="POST" style="display: none;">
    @csrf
</form>
@endsection

@push('scripts')
<script>
function approveExpense() {
    if (confirm('Are you sure you want to approve this expense?')) {
        document.getElementById('approveForm').submit();
    }
}
</script>
@endpush
