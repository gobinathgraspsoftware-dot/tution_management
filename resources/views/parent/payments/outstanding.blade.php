@extends('layouts.app')

@section('title', 'Outstanding Payments')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Outstanding Payments</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('parent.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('parent.payments.index') }}">Payments</a></li>
                    <li class="breadcrumb-item active">Outstanding</li>
                </ol>
            </nav>
        </div>
        <div>
            @if(Route::has('parent.payments.pay-online'))
            <a href="{{ route('parent.payments.pay-online') }}" class="btn btn-success">
                <i class="fas fa-credit-card me-1"></i> Pay Online
            </a>
            @endif
            <a href="{{ route('parent.payments.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 text-white-50">Total Outstanding</h6>
                            <h3 class="mb-0">RM {{ number_format($summary['total_outstanding'], 2) }}</h3>
                        </div>
                        <i class="fas fa-file-invoice-dollar fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-warning text-dark">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 opacity-75">Total Overdue</h6>
                            <h3 class="mb-0">RM {{ number_format($summary['total_overdue'], 2) }}</h3>
                        </div>
                        <i class="fas fa-exclamation-triangle fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 text-white-50">Due This Week</h6>
                            <h3 class="mb-0">RM {{ number_format($summary['due_this_week'], 2) }}</h3>
                        </div>
                        <i class="fas fa-calendar-week fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert if overdue -->
    @if($summary['total_overdue'] > 0)
    <div class="alert alert-danger d-flex align-items-center" role="alert">
        <i class="fas fa-exclamation-circle me-3 fa-2x"></i>
        <div>
            <strong>Attention Required!</strong><br>
            You have overdue invoices totaling <strong>RM {{ number_format($summary['total_overdue'], 2) }}</strong>.
            Please make payment as soon as possible to avoid service interruption.
        </div>
    </div>
    @endif

    <div class="row">
        <!-- Outstanding Invoices Table -->
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-exclamation-circle me-2 text-warning"></i>Unpaid Invoices</h5>
                    <span class="badge bg-danger">{{ $unpaidInvoices->count() }} invoices</span>
                </div>
                <div class="card-body p-0">
                    @if($unpaidInvoices->isEmpty())
                    <div class="text-center py-5">
                        <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                        <h5>All Paid Up!</h5>
                        <p class="text-muted">You have no outstanding invoices. Great job!</p>
                    </div>
                    @else
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Invoice #</th>
                                    <th>Child</th>
                                    <th>Package</th>
                                    <th>Due Date</th>
                                    <th class="text-end">Balance</th>
                                    <th>Status</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($unpaidInvoices as $invoice)
                                <tr class="{{ $invoice->status === 'overdue' ? 'table-danger' : '' }}">
                                    <td>
                                        @if(Route::has('parent.invoices.show'))
                                        <a href="{{ route('parent.invoices.show', $invoice) }}" class="fw-bold text-decoration-none">
                                            {{ $invoice->invoice_number }}
                                        </a>
                                        @else
                                        <span class="fw-bold">{{ $invoice->invoice_number }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($invoice->student && $invoice->student->user)
                                            {{ $invoice->student->user->name }}
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($invoice->enrollment && $invoice->enrollment->package)
                                            {{ $invoice->enrollment->package->name }}
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($invoice->due_date)
                                            <span class="{{ $invoice->due_date->isPast() ? 'text-danger fw-bold' : '' }}">
                                                {{ $invoice->due_date->format('d M Y') }}
                                            </span>
                                            @if($invoice->due_date->isPast())
                                            <br><small class="text-danger">{{ $invoice->due_date->diffForHumans() }}</small>
                                            @elseif($invoice->due_date->diffInDays(now()) <= 7)
                                            <br><small class="text-warning">Due soon</small>
                                            @endif
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <span class="fw-bold text-danger">RM {{ number_format($invoice->balance, 2) }}</span>
                                        @if($invoice->paid_amount > 0)
                                        <br><small class="text-muted">Paid: RM {{ number_format($invoice->paid_amount, 2) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @switch($invoice->status)
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
                                        @if(Route::has('parent.payments.pay-online'))
                                        <a href="{{ route('parent.payments.pay-online', $invoice) }}"
                                           class="btn btn-sm btn-success" title="Pay Now">
                                            <i class="fas fa-credit-card me-1"></i> Pay
                                        </a>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="4" class="text-end fw-bold">Total Outstanding:</td>
                                    <td class="text-end fw-bold text-danger">RM {{ number_format($summary['total_outstanding'], 2) }}</td>
                                    <td colspan="2"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- By Child Breakdown -->
            @if($children->count() > 1)
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="fas fa-child me-2"></i>By Child</h6>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @foreach($children as $child)
                        @php
                            $childInvoices = $unpaidInvoices->where('student_id', $child->id);
                            $childTotal = $childInvoices->sum('balance');
                        @endphp
                        @if($childTotal > 0)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-user-graduate text-primary me-2"></i>
                                {{ $child->user->name ?? 'Unknown' }}
                                <br><small class="text-muted">{{ $childInvoices->count() }} invoice(s)</small>
                            </div>
                            <span class="badge bg-danger rounded-pill">RM {{ number_format($childTotal, 2) }}</span>
                        </li>
                        @endif
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif

            <!-- Payment Info -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Payment Information</h6>
                </div>
                <div class="card-body">
                    <p class="small text-muted mb-3">
                        You can pay your outstanding invoices online using various payment methods.
                    </p>
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <span class="badge bg-light text-dark border">
                            <i class="fas fa-university me-1"></i>FPX
                        </span>
                        <span class="badge bg-light text-dark border">
                            <i class="fas fa-credit-card me-1"></i>Card
                        </span>
                        <span class="badge bg-light text-dark border">
                            <i class="fas fa-wallet me-1"></i>E-Wallet
                        </span>
                    </div>
                    <hr>
                    <h6 class="fw-bold">Need Help?</h6>
                    <p class="small mb-0">
                        <i class="fas fa-phone me-1 text-primary"></i> 03-7972 3663<br>
                        <i class="fas fa-envelope me-1 text-primary"></i> info@arenamatriks.com
                    </p>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="fas fa-link me-2"></i>Quick Links</h6>
                </div>
                <div class="list-group list-group-flush">
                    @if(Route::has('parent.payments.pay-online'))
                    <a href="{{ route('parent.payments.pay-online') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-credit-card me-2 text-success"></i> Make Payment
                    </a>
                    @endif
                    <a href="{{ route('parent.payments.index') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-list me-2 text-primary"></i> All Payments
                    </a>
                    <a href="{{ route('parent.payments.history') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-history me-2 text-info"></i> Payment History
                    </a>
                    @if(Route::has('parent.invoices.index'))
                    <a href="{{ route('parent.invoices.index') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-file-invoice me-2 text-warning"></i> View Invoices
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
