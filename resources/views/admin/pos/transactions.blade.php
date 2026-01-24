@extends('layouts.app')

@section('title', 'POS Transactions')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">POS Transactions</h4>
            <p class="text-muted mb-0">View and manage all point of sale transactions</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.pos.index') }}" class="btn btn-primary">
                <i class="fas fa-cash-register me-1"></i> Back to POS
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-success bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-dollar-sign text-success fa-lg"></i>
                            </div>
                        </div>
                        <div>
                            <p class="text-muted mb-1 small">Today's Sales</p>
                            <h4 class="mb-0">RM {{ number_format($todayStats['total_sales'], 2) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-receipt text-primary fa-lg"></i>
                            </div>
                        </div>
                        <div>
                            <p class="text-muted mb-1 small">Transactions</p>
                            <h4 class="mb-0">{{ $todayStats['total_transactions'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-times-circle text-warning fa-lg"></i>
                            </div>
                        </div>
                        <div>
                            <p class="text-muted mb-1 small">Voided</p>
                            <h4 class="mb-0">{{ $todayStats['voided_count'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-info bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-undo text-info fa-lg"></i>
                            </div>
                        </div>
                        <div>
                            <p class="text-muted mb-1 small">Refunded</p>
                            <h4 class="mb-0">{{ $todayStats['refunded_count'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.pos.transactions') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label small">Search</label>
                        <input type="text" name="search" class="form-control" placeholder="Transaction # or Cashier" value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="voided" {{ request('status') == 'voided' ? 'selected' : '' }}>Voided</option>
                            <option value="refunded" {{ request('status') == 'refunded' ? 'selected' : '' }}>Refunded</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Payment</label>
                        <select name="payment_method" class="form-select">
                            <option value="">All Methods</option>
                            <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="qr" {{ request('payment_method') == 'qr' ? 'selected' : '' }}>QR Pay</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">From</label>
                        <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">To</label>
                        <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                    </div>
                    <div class="col-md-1 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i>
                        </button>
                        <a href="{{ route('admin.pos.transactions') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Export Button -->
    <div class="d-flex justify-content-end mb-3">
        @can('export-pos-transactions')
        <a href="{{ route('admin.pos.export', request()->query()) }}" class="btn btn-success">
            <i class="fas fa-file-excel me-1"></i> Export Excel
        </a>
        @endcan
    </div>

    <!-- Transactions Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Transaction #</th>
                            <th>Date & Time</th>
                            <th>Items</th>
                            <th class="text-end">Total</th>
                            <th class="text-center">Payment</th>
                            <th class="text-center">Status</th>
                            <th>Cashier</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $transaction)
                        <tr>
                            <td>
                                <a href="{{ route('admin.pos.show', $transaction) }}" class="fw-semibold text-decoration-none">
                                    {{ $transaction->transaction_number }}
                                </a>
                            </td>
                            <td>
                                <div>{{ $transaction->transaction_date->format('d M Y') }}</div>
                                <small class="text-muted">{{ $transaction->transaction_date->format('h:i A') }}</small>
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ $transaction->items->count() }} items</span>
                            </td>
                            <td class="text-end fw-semibold">RM {{ number_format($transaction->total_amount, 2) }}</td>
                            <td class="text-center">
                                @if($transaction->payment_method == 'cash')
                                    <span class="badge bg-success"><i class="fas fa-money-bill me-1"></i>Cash</span>
                                @else
                                    <span class="badge bg-primary"><i class="fas fa-qrcode me-1"></i>QR</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($transaction->status == 'completed')
                                    <span class="badge bg-success">Completed</span>
                                @elseif($transaction->status == 'voided')
                                    <span class="badge bg-danger">Voided</span>
                                @elseif($transaction->status == 'refunded')
                                    <span class="badge bg-warning text-dark">Refunded</span>
                                @endif
                            </td>
                            <td>{{ $transaction->cashier->name ?? 'N/A' }}</td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.pos.show', $transaction) }}" class="btn btn-outline-primary" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.pos.receipt', $transaction) }}" class="btn btn-outline-secondary" title="Receipt" target="_blank">
                                        <i class="fas fa-receipt"></i>
                                    </a>
                                    @if($transaction->isCompleted())
                                        @can('void-pos-transaction')
                                        <button type="button" class="btn btn-outline-danger" title="Void" onclick="showVoidModal({{ $transaction->id }})">
                                            <i class="fas fa-ban"></i>
                                        </button>
                                        @endcan
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fas fa-receipt fa-3x mb-3 d-block"></i>
                                No transactions found
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($transactions->hasPages())
        <div class="card-footer border-0 bg-white">
            {{ $transactions->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Void Modal -->
<div class="modal fade" id="voidModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-ban text-danger me-2"></i>Void Transaction</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    This action will void the transaction and restore inventory stock.
                </div>
                <div class="mb-3">
                    <label class="form-label">Reason for Void <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="voidReason" rows="3" placeholder="Enter reason for voiding this transaction..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="voidTransaction()">
                    <i class="fas fa-ban me-1"></i> Void Transaction
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let voidTransactionId = null;

function showVoidModal(id) {
    voidTransactionId = id;
    document.getElementById('voidReason').value = '';
    new bootstrap.Modal(document.getElementById('voidModal')).show();
}

function voidTransaction() {
    const reason = document.getElementById('voidReason').value.trim();

    if (!reason) {
        toastr.error('Please enter a reason for voiding');
        return;
    }

    $.ajax({
        url: '/admin/pos/' + voidTransactionId + '/void',
        method: 'POST',
        data: {
            reason: reason,
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            if (response.success) {
                toastr.success('Transaction voided successfully');
                setTimeout(() => location.reload(), 1000);
            }
        },
        error: function(xhr) {
            toastr.error(xhr.responseJSON?.message || 'An error occurred');
        }
    });
}
</script>
@endsection
