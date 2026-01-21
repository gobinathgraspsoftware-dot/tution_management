@extends('layouts.app')

@section('title', 'Make Payment')

@section('content')
<div class="container-fluid">
    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-credit-card me-2"></i>Make Online Payment
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('parent.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('parent.payments.index') }}">Payments</a></li>
                    <li class="breadcrumb-item active">Pay Online</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('parent.payments.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Payments
            </a>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- Gateway Availability Warning --}}
    @if(!$gatewaysAvailable)
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <strong>Online payment is currently unavailable.</strong>
        No payment gateway is configured. Please contact the administrator or use alternative payment methods.
    </div>
    @endif

    {{-- Summary Cards --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary text-white">
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

        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 text-white-50">Total Overdue</h6>
                            <h3 class="mb-0">RM {{ number_format($summary['total_overdue'], 2) }}</h3>
                        </div>
                        <i class="fas fa-exclamation-circle fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-warning text-dark">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 opacity-75">Pending Invoices</h6>
                            <h3 class="mb-0">{{ $summary['invoices_count'] }}</h3>
                        </div>
                        <i class="fas fa-file-alt fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 text-white-50">Overdue Count</h6>
                            <h3 class="mb-0">{{ $summary['overdue_count'] }}</h3>
                        </div>
                        <i class="fas fa-clock fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Unpaid Invoices List --}}
        <div class="col-lg-8 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-file-invoice me-2"></i>Unpaid Invoices
                    </h5>
                    @if($children->count() > 1)
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="childFilter" data-bs-toggle="dropdown">
                            <i class="fas fa-filter me-1"></i>Filter by Child
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('parent.payments.pay-online') }}">All Children</a></li>
                            <li><hr class="dropdown-divider"></li>
                            @foreach($children as $child)
                            <li>
                                <a class="dropdown-item" href="#" onclick="filterByChild({{ $child->id }})">
                                    {{ $child->user->name }}
                                </a>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                </div>
                <div class="card-body p-0">
                    @if($unpaidInvoices->isEmpty())
                    <div class="text-center py-5">
                        <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                        <h5>All Paid!</h5>
                        <p class="text-muted">There are no outstanding invoices at this time.</p>
                    </div>
                    @else
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="invoicesTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Invoice #</th>
                                    <th>Child</th>
                                    <th>Description</th>
                                    <th>Due Date</th>
                                    <th class="text-end">Amount Due</th>
                                    <th>Status</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($unpaidInvoices as $inv)
                                <tr class="invoice-row" data-child-id="{{ $inv->student_id }}">
                                    <td>
                                        @if(Route::has('parent.invoices.show'))
                                        <a href="{{ route('parent.invoices.show', $inv) }}" class="fw-bold text-decoration-none">
                                            {{ $inv->invoice_number }}
                                        </a>
                                        @else
                                        <span class="fw-bold">{{ $inv->invoice_number }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($inv->student && $inv->student->user)
                                            {{ $inv->student->user->name }}
                                            <br><small class="text-muted">{{ $inv->student->student_id ?? '' }}</small>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($inv->enrollment && $inv->enrollment->package)
                                            {{ $inv->enrollment->package->name }}
                                            @if($inv->enrollment->class)
                                            <br><small class="text-muted">{{ $inv->enrollment->class->name }}</small>
                                            @endif
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($inv->due_date)
                                            <span class="{{ $inv->due_date->isPast() ? 'text-danger fw-bold' : '' }}">
                                                {{ $inv->due_date->format('d M Y') }}
                                            </span>
                                            @if($inv->due_date->isPast())
                                            <br><small class="text-danger">{{ $inv->due_date->diffForHumans() }}</small>
                                            @endif
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <span class="fw-bold text-primary">RM {{ number_format($inv->balance, 2) }}</span>
                                        @if($inv->total_amount != $inv->balance)
                                        <br><small class="text-muted">of RM {{ number_format($inv->total_amount, 2) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $statusColors = [
                                                'pending' => 'warning',
                                                'partial' => 'info',
                                                'overdue' => 'danger',
                                            ];
                                            $color = $statusColors[$inv->status] ?? 'secondary';
                                        @endphp
                                        <span class="badge bg-{{ $color }}">
                                            {{ ucfirst($inv->status) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if($gatewaysAvailable)
                                        <a href="{{ route('parent.payments.pay-online', $inv) }}"
                                           class="btn btn-sm btn-success">
                                            <i class="fas fa-credit-card me-1"></i>Pay Now
                                        </a>
                                        @else
                                        <button class="btn btn-sm btn-secondary" disabled>
                                            <i class="fas fa-ban me-1"></i>Unavailable
                                        </button>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4">
            {{-- Payment Information --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>Payment Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="fw-bold">Accepted Payment Methods</h6>
                        <div class="d-flex flex-wrap gap-2 mt-2">
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
                    </div>
                    <hr>
                    <div class="mb-3">
                        <h6 class="fw-bold">Important Notes</h6>
                        <ul class="small text-muted mb-0">
                            <li>Processing fee: RM 1.30</li>
                            <li>Secure payment gateway</li>
                            <li>Receipt emailed on success</li>
                            <li>Allow 24 hours to reflect</li>
                        </ul>
                    </div>
                    <hr>
                    <div>
                        <h6 class="fw-bold">Need Help?</h6>
                        <p class="small mb-0">
                            <i class="fas fa-phone me-1 text-primary"></i> 03-7972 3663<br>
                            <i class="fas fa-envelope me-1 text-primary"></i> info@arenamatriks.com
                        </p>
                    </div>
                </div>
            </div>

            {{-- Recent Transactions --}}
            @if(isset($recentTransactions) && $recentTransactions->isNotEmpty())
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0">
                        <i class="fas fa-history me-2"></i>Recent Transactions
                    </h6>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @foreach($recentTransactions as $transaction)
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <small class="text-muted">{{ $transaction->created_at->format('d M Y, h:i A') }}</small>
                                    <div class="fw-bold">RM {{ number_format($transaction->amount, 2) }}</div>
                                    <small>{{ $transaction->invoice->student->user->name ?? 'N/A' }}</small>
                                </div>
                                <div>
                                    @php
                                        $txStatusColors = [
                                            'completed' => 'success',
                                            'pending' => 'warning',
                                            'failed' => 'danger',
                                            'cancelled' => 'secondary',
                                        ];
                                        $txColor = $txStatusColors[$transaction->status] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $txColor }}">
                                        {{ ucfirst($transaction->status) }}
                                    </span>
                                </div>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function filterByChild(childId) {
        const rows = document.querySelectorAll('.invoice-row');

        if (!childId) {
            rows.forEach(row => row.style.display = '');
            return;
        }

        rows.forEach(row => {
            if (row.dataset.childId == childId) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
</script>
@endpush
