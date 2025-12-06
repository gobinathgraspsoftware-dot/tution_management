@extends('layouts.app')

@section('title', 'Invoices')
@section('page-title', 'Invoice Management')

@section('content')
<div class="container-fluid">
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Total Invoices</h6>
                            <h3 class="mb-0">{{ number_format($statistics['total_invoices']) }}</h3>
                        </div>
                        <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                            <i class="fas fa-file-invoice text-primary fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Pending</h6>
                            <h3 class="mb-0">{{ number_format($statistics['pending_invoices']) }}</h3>
                        </div>
                        <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                            <i class="fas fa-clock text-warning fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Overdue</h6>
                            <h3 class="mb-0 text-danger">{{ number_format($statistics['overdue_invoices']) }}</h3>
                        </div>
                        <div class="bg-danger bg-opacity-10 rounded-circle p-3">
                            <i class="fas fa-exclamation-triangle text-danger fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Outstanding</h6>
                            <h3 class="mb-0">RM {{ number_format($statistics['total_outstanding'], 2) }}</h3>
                        </div>
                        <div class="bg-info bg-opacity-10 rounded-circle p-3">
                            <i class="fas fa-money-bill-wave text-info fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons & Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-6 mb-3 mb-md-0">
                    <div class="btn-group" role="group">
                        <a href="{{ route('admin.invoices.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i> New Invoice
                        </a>
                        <a href="{{ route('admin.invoices.bulk-generate') }}" class="btn btn-outline-primary">
                            <i class="fas fa-cogs me-1"></i> Bulk Generate
                        </a>
                        <a href="{{ route('admin.invoices.overdue') }}" class="btn btn-outline-danger">
                            <i class="fas fa-exclamation-circle me-1"></i> Overdue
                        </a>
                    </div>
                </div>
                <div class="col-md-6">
                    <form action="{{ route('admin.invoices.index') }}" method="GET" class="d-flex gap-2">
                        <input type="text" name="search" class="form-control" placeholder="Search invoice #, student..." value="{{ request('search') }}">
                        <select name="status" class="form-select" style="width: auto;">
                            <option value="">All Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>Partial</option>
                            <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                        <button type="submit" class="btn btn-secondary">
                            <i class="fas fa-search"></i>
                        </button>
                        @if(request()->hasAny(['search', 'status', 'student_id', 'date_from', 'date_to']))
                            <a href="{{ route('admin.invoices.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i>
                            </a>
                        @endif
                    </form>
                </div>
            </div>

            <!-- Advanced Filters (Collapsible) -->
            <div class="collapse mt-3" id="advancedFilters">
                <form action="{{ route('admin.invoices.index') }}" method="GET">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Student</label>
                            <select name="student_id" class="form-select">
                                <option value="">All Students</option>
                                @foreach($students as $student)
                                    <option value="{{ $student->id }}" {{ request('student_id') == $student->id ? 'selected' : '' }}>
                                        {{ $student->user->name ?? 'Unknown' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Type</label>
                            <select name="type" class="form-select">
                                <option value="">All Types</option>
                                <option value="registration" {{ request('type') == 'registration' ? 'selected' : '' }}>Registration</option>
                                <option value="monthly" {{ request('type') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                <option value="renewal" {{ request('type') == 'renewal' ? 'selected' : '' }}>Renewal</option>
                                <option value="custom" {{ request('type') == 'custom' ? 'selected' : '' }}>Custom</option>
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
                        <div class="col-md-3 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary">Apply Filters</button>
                            <a href="{{ route('admin.invoices.export', request()->query()) }}" class="btn btn-outline-success">
                                <i class="fas fa-download"></i> Export
                            </a>
                        </div>
                    </div>
                </form>
            </div>
            <div class="mt-2">
                <a class="text-decoration-none small" data-bs-toggle="collapse" href="#advancedFilters">
                    <i class="fas fa-filter me-1"></i> Advanced Filters
                </a>
            </div>
        </div>
    </div>

    <!-- Invoices Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Invoice #</th>
                            <th>Student</th>
                            <th>Type</th>
                            <th>Period</th>
                            <th class="text-end">Amount</th>
                            <th class="text-end">Paid</th>
                            <th class="text-end">Balance</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th class="text-center pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices as $invoice)
                            <tr>
                                <td class="ps-4">
                                    <a href="{{ route('admin.invoices.show', $invoice) }}" class="fw-semibold text-decoration-none">
                                        {{ $invoice->invoice_number }}
                                    </a>
                                </td>
                                <td>
                                    <div>{{ $invoice->student->user->name ?? 'N/A' }}</div>
                                    <small class="text-muted">{{ $invoice->student->student_id ?? '' }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ ucfirst($invoice->type) }}</span>
                                </td>
                                <td>
                                    <small>
                                        {{ $invoice->billing_period_start?->format('d M') }} -
                                        {{ $invoice->billing_period_end?->format('d M Y') }}
                                    </small>
                                </td>
                                <td class="text-end">RM {{ number_format($invoice->total_amount, 2) }}</td>
                                <td class="text-end text-success">RM {{ number_format($invoice->paid_amount, 2) }}</td>
                                <td class="text-end fw-semibold {{ $invoice->balance > 0 ? 'text-danger' : 'text-success' }}">
                                    RM {{ number_format($invoice->balance, 2) }}
                                </td>
                                <td>
                                    @if($invoice->due_date)
                                        <span class="{{ $invoice->isOverdue() ? 'text-danger fw-semibold' : '' }}">
                                            {{ $invoice->due_date->format('d M Y') }}
                                        </span>
                                        @if($invoice->isOverdue())
                                            <br><small class="text-danger">{{ $invoice->due_date->diffInDays(now()) }} days overdue</small>
                                        @endif
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'draft' => 'secondary',
                                            'pending' => 'warning',
                                            'partial' => 'info',
                                            'paid' => 'success',
                                            'overdue' => 'danger',
                                            'cancelled' => 'dark',
                                            'refunded' => 'secondary',
                                        ];
                                    @endphp
                                    <span class="badge bg-{{ $statusColors[$invoice->status] ?? 'secondary' }}">
                                        {{ ucfirst($invoice->status) }}
                                    </span>
                                </td>
                                <td class="text-center pe-4">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.invoices.show', $invoice) }}" class="btn btn-outline-primary" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if(!in_array($invoice->status, ['paid', 'cancelled']))
                                            <a href="{{ route('admin.invoices.edit', $invoice) }}" class="btn btn-outline-secondary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif
                                        <button type="button" class="btn btn-outline-info dropdown-toggle" data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <a class="dropdown-item" href="{{ route('admin.invoices.show', $invoice) }}">
                                                    <i class="fas fa-eye me-2"></i> View Details
                                                </a>
                                            </li>
                                            @if($invoice->status !== 'cancelled')
                                                <li>
                                                    <form action="{{ route('admin.invoices.send', $invoice) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item">
                                                            <i class="fas fa-paper-plane me-2"></i> Send to Parent
                                                        </button>
                                                    </form>
                                                </li>
                                            @endif
                                            @if(!in_array($invoice->status, ['paid', 'cancelled']))
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <button class="dropdown-item text-danger" onclick="cancelInvoice({{ $invoice->id }})">
                                                        <i class="fas fa-times me-2"></i> Cancel Invoice
                                                    </button>
                                                </li>
                                            @endif
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="fas fa-file-invoice fa-3x mb-3"></i>
                                        <p class="mb-0">No invoices found</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($invoices->hasPages())
            <div class="card-footer bg-white">
                {{ $invoices->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Cancel Invoice Modal -->
<div class="modal fade" id="cancelInvoiceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="cancelInvoiceForm" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Cancel Invoice</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Are you sure you want to cancel this invoice? This action cannot be undone.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason for Cancellation</label>
                        <textarea name="reason" class="form-control" rows="3" placeholder="Enter reason..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger">Cancel Invoice</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function cancelInvoice(invoiceId) {
    const form = document.getElementById('cancelInvoiceForm');
    form.action = `/admin/invoices/${invoiceId}/cancel`;
    new bootstrap.Modal(document.getElementById('cancelInvoiceModal')).show();
}
</script>
@endpush
@endsection
