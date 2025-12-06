@extends('layouts.app')

@section('title', 'My Invoices')
@section('page-title', 'Invoices')

@push('styles')
<style>
    .summary-card {
        border-radius: 15px;
        overflow: hidden;
        transition: all 0.3s ease;
    }
    .summary-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    }
    .summary-outstanding {
        background: linear-gradient(135deg, #ff6b6b 0%, #ee5253 100%);
        color: white;
    }
    .summary-paid {
        background: linear-gradient(135deg, #26de81 0%, #20bf6b 100%);
        color: white;
    }
    .summary-count {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    .invoice-item {
        border-radius: 12px;
        border: 1px solid #e0e0e0;
        padding: 20px;
        margin-bottom: 15px;
        transition: all 0.3s ease;
    }
    .invoice-item:hover {
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    .invoice-item.overdue {
        border-left: 5px solid #dc3545;
        background: #fff5f5;
    }
    .invoice-item.pending {
        border-left: 5px solid #ffc107;
    }
    .invoice-item.paid {
        border-left: 5px solid #28a745;
        background: #f8fff8;
    }
    .invoice-item.partial {
        border-left: 5px solid #17a2b8;
    }
    .amount-large {
        font-size: 1.5rem;
        font-weight: bold;
    }
    .filter-pills .nav-link {
        border-radius: 20px;
        padding: 8px 20px;
        margin-right: 10px;
        margin-bottom: 10px;
    }
    .filter-pills .nav-link.active {
        background: #667eea;
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1>
        <i class="fas fa-file-invoice me-2"></i> My Invoices
    </h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Invoices</li>
        </ol>
    </nav>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card summary-card summary-outstanding h-100">
            <div class="card-body text-center py-4">
                <i class="fas fa-exclamation-circle fa-2x mb-2"></i>
                <h3 class="mb-0">RM {{ number_format($summary['total_outstanding'], 2) }}</h3>
                <small>Outstanding Balance</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card summary-card summary-paid h-100">
            <div class="card-body text-center py-4">
                <i class="fas fa-check-circle fa-2x mb-2"></i>
                <h3 class="mb-0">RM {{ number_format($summary['total_paid'], 2) }}</h3>
                <small>Total Paid</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card summary-card summary-count h-100">
            <div class="card-body text-center py-4">
                <i class="fas fa-clock fa-2x mb-2"></i>
                <h3 class="mb-0">{{ $summary['pending_count'] }}</h3>
                <small>Pending Invoices</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card summary-card" style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%); color: white;">
            <div class="card-body text-center py-4">
                <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                <h3 class="mb-0">{{ $summary['overdue_count'] }}</h3>
                <small>Overdue</small>
            </div>
        </div>
    </div>
</div>

<!-- Filter Pills -->
<div class="card mb-4">
    <div class="card-body">
        <nav class="filter-pills nav nav-pills">
            <a class="nav-link {{ !request('status') ? 'active' : '' }}" href="{{ route('student.invoices.index') }}">
                All Invoices
            </a>
            <a class="nav-link {{ request('status') == 'unpaid' ? 'active' : '' }}" href="{{ route('student.invoices.index', ['status' => 'unpaid']) }}">
                <i class="fas fa-clock me-1"></i> Unpaid
            </a>
            <a class="nav-link {{ request('status') == 'overdue' ? 'active' : '' }}" href="{{ route('student.invoices.index', ['status' => 'overdue']) }}">
                <i class="fas fa-exclamation-triangle me-1"></i> Overdue
                @if($summary['overdue_count'] > 0)
                    <span class="badge bg-danger ms-1">{{ $summary['overdue_count'] }}</span>
                @endif
            </a>
            <a class="nav-link {{ request('status') == 'paid' ? 'active' : '' }}" href="{{ route('student.invoices.index', ['status' => 'paid']) }}">
                <i class="fas fa-check me-1"></i> Paid
            </a>
        </nav>
    </div>
</div>

<!-- Invoices List -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-list me-2"></i> Invoice History</span>
        <span class="badge bg-secondary">{{ $invoices->total() }} invoices</span>
    </div>
    <div class="card-body">
        @if($invoices->isEmpty())
            <div class="text-center py-5">
                <i class="fas fa-file-invoice text-muted fa-4x mb-3"></i>
                <h4>No Invoices Found</h4>
                <p class="text-muted">
                    @if(request('status'))
                        No {{ request('status') }} invoices found.
                    @else
                        You don't have any invoices yet.
                    @endif
                </p>
            </div>
        @else
            @foreach($invoices as $invoice)
                @php
                    $isOverdue = $invoice->isOverdue();
                    $statusClass = $isOverdue ? 'overdue' : $invoice->status;
                @endphp
                <div class="invoice-item {{ $statusClass }}">
                    <div class="row align-items-center">
                        <div class="col-md-4">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    @if($invoice->isPaid())
                                        <i class="fas fa-check-circle text-success fa-2x"></i>
                                    @elseif($isOverdue)
                                        <i class="fas fa-exclamation-circle text-danger fa-2x"></i>
                                    @else
                                        <i class="fas fa-file-invoice text-warning fa-2x"></i>
                                    @endif
                                </div>
                                <div>
                                    <h5 class="mb-0">{{ $invoice->invoice_number }}</h5>
                                    <small class="text-muted">
                                        {{ $invoice->created_at->format('d M Y') }}
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">Package</small>
                            <div class="fw-bold">{{ $invoice->enrollment->package->name ?? 'N/A' }}</div>
                            <small class="text-muted">{{ ucfirst($invoice->type) }} Fee</small>
                        </div>
                        <div class="col-md-2 text-center">
                            <small class="text-muted">Due Date</small>
                            <div class="{{ $isOverdue ? 'text-danger fw-bold' : '' }}">
                                {{ $invoice->due_date->format('d M Y') }}
                            </div>
                            @if($isOverdue)
                                <small class="text-danger">
                                    {{ $invoice->due_date->diffInDays(now()) }} days ago
                                </small>
                            @endif
                        </div>
                        <div class="col-md-2 text-end">
                            <div class="amount-large">RM {{ number_format($invoice->total_amount, 2) }}</div>
                            @if($invoice->paid_amount > 0 && $invoice->paid_amount < $invoice->total_amount)
                                <small class="text-success">
                                    Paid: RM {{ number_format($invoice->paid_amount, 2) }}
                                </small>
                            @endif
                        </div>
                        <div class="col-md-1 text-end">
                            <a href="{{ route('student.invoices.show', $invoice) }}" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                    </div>

                    @if($invoice->balance > 0)
                    <div class="mt-3 pt-3 border-top d-flex justify-content-between align-items-center">
                        <div>
                            <span class="badge bg-{{ $isOverdue ? 'danger' : 'warning' }}">
                                Balance: RM {{ number_format($invoice->balance, 2) }}
                            </span>
                        </div>
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Please contact your parent/guardian for payment.
                        </small>
                    </div>
                    @endif
                </div>
            @endforeach

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                {{ $invoices->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Outstanding Balance Alert -->
@if($summary['total_outstanding'] > 0)
<div class="card mt-4 border-warning">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-1 text-center">
                <i class="fas fa-exclamation-triangle text-warning fa-3x"></i>
            </div>
            <div class="col-md-8">
                <h5 class="mb-1">Outstanding Balance</h5>
                <p class="mb-0 text-muted">
                    You have an outstanding balance of <strong class="text-danger">RM {{ number_format($summary['total_outstanding'], 2) }}</strong>.
                    Please inform your parent/guardian to complete the payment.
                </p>
            </div>
            <div class="col-md-3 text-end">
                <a href="{{ route('student.invoices.index', ['status' => 'unpaid']) }}" class="btn btn-warning">
                    <i class="fas fa-eye me-2"></i> View Unpaid
                </a>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Information Card -->
<div class="card mt-4 bg-light">
    <div class="card-body">
        <h6><i class="fas fa-info-circle me-2"></i> About Invoices</h6>
        <p class="mb-0 small text-muted">
            This page shows all invoices related to your enrollment. For payment inquiries or to make a payment,
            please contact your parent/guardian or visit the administration office. You can also view detailed
            information about each invoice by clicking the view button.
        </p>
    </div>
</div>
@endsection
