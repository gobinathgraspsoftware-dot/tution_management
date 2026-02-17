@extends('layouts.app')

@section('title', 'My Invoices')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">My Invoices</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Invoices</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 text-white-50">Total Outstanding</h6>
                            <h3 class="mb-0">RM {{ number_format($summary['total_outstanding'], 2) }}</h3>
                        </div>
                        <i class="fas fa-exclamation-triangle fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 text-white-50">Total Paid</h6>
                            <h3 class="mb-0">RM {{ number_format($summary['total_paid'], 2) }}</h3>
                        </div>
                        <i class="fas fa-check-circle fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-warning text-dark">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 opacity-75">Pending</h6>
                            <h3 class="mb-0">{{ $summary['pending_count'] }}</h3>
                        </div>
                        <i class="fas fa-clock fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 text-white-50">Overdue</h6>
                            <h3 class="mb-0">{{ $summary['overdue_count'] }}</h3>
                        </div>
                        <i class="fas fa-exclamation-circle fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card shadow-sm mb-4">
        <div class="card-body py-3">
            <form action="{{ route('student.invoices.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>Partially Paid</option>
                        <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                        <option value="unpaid" {{ request('status') == 'unpaid' ? 'selected' : '' }}>All Unpaid</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-1"></i> Filter
                    </button>
                    <a href="{{ route('student.invoices.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-redo me-1"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Invoices Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-file-invoice me-2"></i>Invoice Records</h5>
            <div>
                @if(Route::has('student.invoices.history'))
                    <a href="{{ route('student.invoices.history') }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-history me-1"></i> Payment History
                    </a>
                @endif
                @if(Route::has('student.payments.outstanding'))
                    <a href="{{ route('student.payments.outstanding') }}" class="btn btn-sm btn-outline-warning">
                        <i class="fas fa-exclamation-circle me-1"></i> Outstanding
                    </a>
                @endif
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Invoice #</th>
                            <th>Package</th>
                            <th>Type</th>
                            <th>Due Date</th>
                            <th class="text-end">Total</th>
                            <th class="text-end">Paid</th>
                            <th class="text-end">Balance</th>
                            <th>Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices as $invoice)
                            <tr class="{{ $invoice->status === 'overdue' ? 'table-danger' : '' }}">
                                <td>
                                    <a href="{{ route('student.invoices.show', $invoice) }}" class="fw-bold text-decoration-none">
                                        {{ $invoice->invoice_number }}
                                    </a>
                                    <br>
                                    <small class="text-muted">{{ $invoice->created_at->format('d M Y') }}</small>
                                </td>
                                <td>
                                    @if($invoice->enrollment && $invoice->enrollment->package)
                                        {{ $invoice->enrollment->package->name }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <small>{{ $invoice->type_label ?? ucfirst($invoice->type) }}</small>
                                </td>
                                <td>
                                    {{ $invoice->due_date ? $invoice->due_date->format('d M Y') : '-' }}
                                    @if($invoice->due_date && $invoice->due_date->isPast() && $invoice->status !== 'paid')
                                        <br><small class="text-danger">
                                            <i class="fas fa-exclamation-triangle"></i> {{ $invoice->due_date->diffForHumans() }}
                                        </small>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <strong>RM {{ number_format($invoice->total_amount, 2) }}</strong>
                                </td>
                                <td class="text-end text-success">
                                    RM {{ number_format($invoice->paid_amount, 2) }}
                                </td>
                                <td class="text-end">
                                    @if($invoice->balance > 0)
                                        <strong class="text-danger">RM {{ number_format($invoice->balance, 2) }}</strong>
                                    @else
                                        <span class="text-success">RM 0.00</span>
                                    @endif
                                </td>
                                <td>
                                    @switch($invoice->status)
                                        @case('paid')
                                            <span class="badge bg-success">Paid</span>
                                            @break
                                        @case('pending')
                                            <span class="badge bg-warning text-dark">Pending</span>
                                            @break
                                        @case('partial')
                                            <span class="badge bg-info">Partial</span>
                                            @break
                                        @case('overdue')
                                            <span class="badge bg-danger">Overdue</span>
                                            @break
                                        @default
                                            <span class="badge bg-secondary">{{ ucfirst($invoice->status) }}</span>
                                    @endswitch
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('student.invoices.show', $invoice) }}" 
                                           class="btn btn-outline-primary" 
                                           title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($invoice->balance > 0 && Route::has('student.payments.pay-online'))
                                            <a href="{{ route('student.payments.pay-online', $invoice) }}" 
                                               class="btn btn-outline-success" 
                                               title="Pay Now">
                                                <i class="fas fa-credit-card"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <p class="text-muted mb-0">No invoices found.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($invoices->hasPages())
            <div class="card-footer bg-white">
                {{ $invoices->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
