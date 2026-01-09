@extends('layouts.app')

@section('title', 'Expense Management')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="h3 mb-0">Expense Management</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Expenses</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 text-end">
            @can('create-expenses')
            <a href="{{ route('admin.expenses.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Create Expense Voucher
            </a>
            @endcan
            <a href="{{ route('admin.expenses.export', request()->query()) }}" class="btn btn-success">
                <i class="fas fa-file-excel me-1"></i> Export
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Total Expenses</h6>
                            <h3 class="mb-0">RM {{ number_format($summary['total_expenses'] ?? 0, 2) }}</h3>
                        </div>
                        <i class="fas fa-money-bill-wave fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Approved</h6>
                            <h3 class="mb-0">{{ $summary['expense_count'] ?? 0 }}</h3>
                        </div>
                        <i class="fas fa-check-circle fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Pending</h6>
                            <h3 class="mb-0">{{ $summary['pending_count'] ?? 0 }}</h3>
                        </div>
                        <i class="fas fa-clock fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Average</h6>
                            <h3 class="mb-0">RM {{ number_format($summary['average_expense'] ?? 0, 2) }}</h3>
                        </div>
                        <i class="fas fa-calculator fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filter Expenses</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.expenses.index') }}" method="GET">
                <div class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" class="form-control" placeholder="Voucher, vendor..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-select">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                            <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Payment Method</label>
                        <select name="payment_method" class="form-select">
                            <option value="">All Methods</option>
                            <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="bank_transfer" {{ request('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                            <option value="cheque" {{ request('payment_method') == 'cheque' ? 'selected' : '' }}>Cheque</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">From Date</label>
                        <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">To Date</label>
                        <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-1"></i> Search
                        </button>
                        <a href="{{ route('admin.expenses.index') }}" class="btn btn-secondary">
                            <i class="fas fa-redo me-1"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Expenses Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-receipt me-2"></i>Expense Vouchers</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>Voucher No.</th>
                            <th>Date</th>
                            <th>Category</th>
                            <th>Payee/Vendor</th>
                            <th>Description</th>
                            <th>Amount</th>
                            <th>Payment Method</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($expenses as $expense)
                        <tr>
                            <td>
                                <a href="{{ route('admin.expenses.show', $expense) }}" class="fw-bold text-primary">
                                    {{ $expense->voucher_number ?? 'EXP-' . str_pad($expense->id, 4, '0', STR_PAD_LEFT) }}
                                </a>
                            </td>
                            <td>{{ $expense->expense_date->format('d/m/Y') }}</td>
                            <td>
                                <span class="badge bg-secondary">{{ $expense->category->name ?? 'N/A' }}</span>
                            </td>
                            <td>{{ $expense->vendor_name ?? '-' }}</td>
                            <td>{{ Str::limit($expense->description, 30) }}</td>
                            <td class="fw-bold">RM {{ number_format($expense->amount, 2) }}</td>
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
                            <td>
                                <span class="badge bg-{{ $expense->getStatusBadgeClass() }}">
                                    {{ ucfirst($expense->status) }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.expenses.show', $expense) }}" class="btn btn-info" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($expense->canBeEdited())
                                        @can('edit-expenses')
                                        <a href="{{ route('admin.expenses.edit', $expense) }}" class="btn btn-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @endcan
                                    @endif
                                    @if($expense->isPending())
                                        @can('approve-expenses')
                                        <button type="button" class="btn btn-success" title="Approve" onclick="approveExpense({{ $expense->id }})">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button type="button" class="btn btn-danger" title="Reject" onclick="showRejectModal({{ $expense->id }})">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        @endcan
                                    @endif
                                    @if($expense->canBeDeleted())
                                        @can('delete-expenses')
                                        <button type="button" class="btn btn-danger" title="Delete" onclick="deleteExpense({{ $expense->id }})">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        @endcan
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No expenses found.</p>
                                @can('create-expenses')
                                <a href="{{ route('admin.expenses.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-1"></i> Create First Expense
                                </a>
                                @endcan
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($expenses->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $expenses->withQueryString()->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="rejectForm" method="POST">
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

<!-- Delete Form -->
<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<!-- Approve Form -->
<form id="approveForm" method="POST" style="display: none;">
    @csrf
</form>
@endsection

@push('scripts')
<script>
function approveExpense(id) {
    if (confirm('Are you sure you want to approve this expense?')) {
        const form = document.getElementById('approveForm');
        form.action = '{{ url("admin/expenses") }}/' + id + '/approve';
        form.submit();
    }
}

function showRejectModal(id) {
    const form = document.getElementById('rejectForm');
    form.action = '{{ url("admin/expenses") }}/' + id + '/reject';
    new bootstrap.Modal(document.getElementById('rejectModal')).show();
}

function deleteExpense(id) {
    if (confirm('Are you sure you want to delete this expense? This action cannot be undone.')) {
        const form = document.getElementById('deleteForm');
        form.action = '{{ url("admin/expenses") }}/' + id;
        form.submit();
    }
}
</script>
@endpush
