@extends('layouts.app')

@section('title', 'My Transactions')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">My Transactions</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('staff.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('staff.pos.index') }}">POS</a></li>
                    <li class="breadcrumb-item active">My Transactions</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('staff.pos.index') }}" class="btn btn-primary">
            <i class="fas fa-cash-register me-1"></i> Back to POS
        </a>
    </div>

    <!-- Today's Summary -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-shopping-cart text-primary fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Today's Transactions</h6>
                            <h3 class="mb-0">{{ number_format($todayStats['count'] ?? 0) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-dollar-sign text-success fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Today's Sales</h6>
                            <h3 class="mb-0">RM {{ number_format($todayStats['total'] ?? 0, 2) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-info bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-money-bill-wave text-info fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Cash Sales</h6>
                            <h3 class="mb-0">RM {{ number_format($todayStats['cash'] ?? 0, 2) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-qrcode text-warning fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">QR Sales</h6>
                            <h3 class="mb-0">RM {{ number_format($todayStats['qr'] ?? 0, 2) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('staff.pos.my-transactions') }}" method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control"
                           value="{{ request('search') }}" placeholder="Transaction #...">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="voided" {{ request('status') == 'voided' ? 'selected' : '' }}>Voided</option>
                        <option value="refunded" {{ request('status') == 'refunded' ? 'selected' : '' }}>Refunded</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Payment</label>
                    <select name="payment_method" class="form-select">
                        <option value="">All Methods</option>
                        <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="qr" {{ request('payment_method') == 'qr' ? 'selected' : '' }}>QR Payment</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">From Date</label>
                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">To Date</label>
                    <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Transaction History</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Transaction #</th>
                            <th>Date & Time</th>
                            <th class="text-center">Items</th>
                            <th class="text-end">Amount</th>
                            <th class="text-center">Payment</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $transaction)
                            <tr>
                                <td>
                                    <a href="{{ route('staff.pos.show', $transaction) }}"
                                       class="fw-bold text-decoration-none">
                                        {{ $transaction->transaction_number }}
                                    </a>
                                </td>
                                <td>
                                    <div>{{ $transaction->created_at->format('d M Y') }}</div>
                                    <small class="text-muted">{{ $transaction->created_at->format('h:i A') }}</small>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-secondary">
                                        {{ $transaction->items_count ?? $transaction->items->count() }} items
                                    </span>
                                </td>
                                <td class="text-end">
                                    <strong>RM {{ number_format($transaction->total_amount, 2) }}</strong>
                                    @if($transaction->discount_amount > 0)
                                        <br>
                                        <small class="text-danger">
                                            -RM {{ number_format($transaction->discount_amount, 2) }}
                                        </small>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($transaction->payment_method == 'cash')
                                        <span class="badge bg-success">
                                            <i class="fas fa-money-bill me-1"></i> Cash
                                        </span>
                                    @else
                                        <span class="badge bg-primary">
                                            <i class="fas fa-qrcode me-1"></i> QR
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @switch($transaction->status)
                                        @case('completed')
                                            <span class="badge bg-success">
                                                <i class="fas fa-check-circle me-1"></i> Completed
                                            </span>
                                            @break
                                        @case('voided')
                                            <span class="badge bg-danger">
                                                <i class="fas fa-times-circle me-1"></i> Voided
                                            </span>
                                            @break
                                        @case('refunded')
                                            <span class="badge bg-warning text-dark">
                                                <i class="fas fa-undo me-1"></i> Refunded
                                            </span>
                                            @break
                                        @default
                                            <span class="badge bg-secondary">{{ ucfirst($transaction->status) }}</span>
                                    @endswitch
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('staff.pos.show', $transaction) }}"
                                           class="btn btn-outline-primary" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('staff.pos.receipt', $transaction) }}"
                                           class="btn btn-outline-secondary" title="View Receipt" target="_blank">
                                            <i class="fas fa-receipt"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="fas fa-receipt fa-3x mb-3"></i>
                                        <p class="mb-0">No transactions found.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($transactions->hasPages())
            <div class="card-footer bg-white">
                {{ $transactions->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
