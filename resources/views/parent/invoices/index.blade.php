@extends('layouts.app')

@section('title', 'My Invoices')
@section('page-title', 'Invoices')

@push('styles')
<style>
    .summary-card {
        border-radius: 15px;
        overflow: hidden;
    }
    .summary-card .card-body {
        padding: 20px;
    }
    .summary-pending {
        background: linear-gradient(135deg, #ff9800 0%, #f57c00 100%);
        color: white;
    }
    .summary-paid {
        background: linear-gradient(135deg, #4caf50 0%, #388e3c 100%);
        color: white;
    }
    .summary-overdue {
        background: linear-gradient(135deg, #e53935 0%, #c62828 100%);
        color: white;
    }
    .invoice-card {
        border-radius: 12px;
        transition: all 0.3s ease;
        margin-bottom: 15px;
    }
    .invoice-card:hover {
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    }
    .invoice-card.overdue {
        border-left: 4px solid #e53935;
    }
    .invoice-card.pending {
        border-left: 4px solid #ff9800;
    }
    .invoice-card.paid {
        border-left: 4px solid #4caf50;
    }
    .invoice-card.partial {
        border-left: 4px solid #2196f3;
    }
    .child-selector {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 10px;
        margin-bottom: 20px;
    }
    .amount-display {
        font-size: 1.25rem;
        font-weight: bold;
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1>
        <i class="fas fa-file-invoice-dollar me-2"></i> My Invoices
    </h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('parent.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Invoices</li>
        </ol>
    </nav>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card summary-card summary-pending">
            <div class="card-body text-center">
                <h3 class="mb-0">RM {{ number_format($summary['total_pending'], 2) }}</h3>
                <small>Total Pending</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card summary-card summary-paid">
            <div class="card-body text-center">
                <h3 class="mb-0">RM {{ number_format($summary['total_paid_this_month'], 2) }}</h3>
                <small>Paid This Month</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card summary-card summary-overdue">
            <div class="card-body text-center">
                <h3 class="mb-0">{{ $summary['overdue_count'] }}</h3>
                <small>Overdue Invoices</small>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('parent.invoices.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label for="child_id" class="form-label">Child</label>
                <select name="child_id" id="child_id" class="form-select">
                    <option value="">All Children</option>
                    @foreach($children as $child)
                        <option value="{{ $child->id }}" {{ request('child_id') == $child->id ? 'selected' : '' }}>
                            {{ $child->user->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="status" class="form-label">Status</label>
                <select name="status" id="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="unpaid" {{ request('status') == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>Partial</option>
                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="date_from" class="form-label">From</label>
                <input type="date" name="date_from" id="date_from" class="form-control"
                       value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <label for="date_to" class="form-label">To</label>
                <input type="date" name="date_to" id="date_to" class="form-control"
                       value="{{ request('date_to') }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter me-2"></i> Filter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Invoices List -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-list me-2"></i> Invoice History</span>
        <a href="{{ route('parent.invoices.payment-history') }}" class="btn btn-outline-success btn-sm">
            <i class="fas fa-history me-1"></i> Payment History
        </a>
    </div>
    <div class="card-body">
        @if($invoices->isEmpty())
            <div class="text-center py-5">
                <i class="fas fa-file-invoice text-muted fa-4x mb-3"></i>
                <h4>No Invoices Found</h4>
                <p class="text-muted">There are no invoices matching your criteria.</p>
            </div>
        @else
            @foreach($invoices as $invoice)
                <div class="card invoice-card {{ $invoice->isOverdue() ? 'overdue' : $invoice->status }}">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-3">
                                <h5 class="mb-1">{{ $invoice->invoice_number }}</h5>
                                <small class="text-muted">
                                    <i class="fas fa-user-graduate me-1"></i>
                                    {{ $invoice->student->user->name ?? 'N/A' }}
                                </small>
                                <br>
                                <small class="text-muted">
                                    <i class="fas fa-calendar me-1"></i>
                                    {{ $invoice->created_at->format('d M Y') }}
                                </small>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted">Package</small>
                                <div class="fw-bold">{{ $invoice->enrollment->package->name ?? 'N/A' }}</div>
                                <small class="text-muted">
                                    {{ ucfirst($invoice->type) }} Fee
                                </small>
                            </div>
                            <div class="col-md-2 text-center">
                                <small class="text-muted">Due Date</small>
                                <div class="fw-bold {{ $invoice->isOverdue() ? 'text-danger' : '' }}">
                                    {{ $invoice->due_date->format('d M Y') }}
                                </div>
                                @if($invoice->isOverdue())
                                    <small class="text-danger">
                                        {{ $invoice->due_date->diffInDays(now()) }} days overdue
                                    </small>
                                @endif
                            </div>
                            <div class="col-md-2 text-end">
                                <small class="text-muted">Amount</small>
                                <div class="amount-display">RM {{ number_format($invoice->total_amount, 2) }}</div>
                                @if($invoice->paid_amount > 0)
                                    <small class="text-success">Paid: RM {{ number_format($invoice->paid_amount, 2) }}</small>
                                @endif
                            </div>
                            <div class="col-md-2 text-end">
                                @php
                                    $statusClass = match($invoice->status) {
                                        'paid' => 'success',
                                        'partial' => 'info',
                                        'overdue' => 'danger',
                                        'pending' => 'warning',
                                        default => 'secondary'
                                    };
                                @endphp
                                <span class="badge bg-{{ $statusClass }} mb-2">
                                    {{ ucfirst($invoice->isOverdue() ? 'overdue' : $invoice->status) }}
                                </span>
                                <br>
                                <a href="{{ route('parent.invoices.show', $invoice) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-eye me-1"></i> View
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                {{ $invoices->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Quick Actions for Outstanding Invoices -->
@if($summary['total_pending'] > 0)
<div class="card mt-4 border-warning">
    <div class="card-header bg-warning text-dark">
        <i class="fas fa-exclamation-triangle me-2"></i> Outstanding Payments
    </div>
    <div class="card-body">
        <p class="mb-3">
            You have <strong>RM {{ number_format($summary['total_pending'], 2) }}</strong> in outstanding payments.
            Please make payment before the due date to avoid any late fees.
        </p>
        <div class="d-flex gap-2">
            <a href="{{ route('parent.invoices.index', ['status' => 'unpaid']) }}" class="btn btn-warning">
                <i class="fas fa-file-invoice me-2"></i> View Unpaid Invoices
            </a>
            <a href="{{ route('parent.payment-methods') }}" class="btn btn-outline-primary">
                <i class="fas fa-credit-card me-2"></i> Payment Methods
            </a>
        </div>
    </div>
</div>
@endif
@endsection
