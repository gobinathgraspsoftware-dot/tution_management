@extends('layouts.app')

@section('title', 'Transaction History - ' . ucfirst($paymentGateway->gateway_name))
@section('page-title', 'Transaction History')

@push('styles')
<style>
    .stat-card {
        background: #fff;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        margin-bottom: 20px;
    }
    .stat-card h3 {
        margin: 0;
        font-size: 1.8rem;
        font-weight: 700;
    }
    .stat-card p {
        margin: 0;
        color: #6c757d;
        font-size: 0.9rem;
    }
    .transaction-row {
        border-left: 3px solid transparent;
        transition: all 0.2s ease;
    }
    .transaction-row:hover {
        background-color: #f8f9fa;
    }
    .transaction-row.status-completed {
        border-left-color: #28a745;
    }
    .transaction-row.status-pending {
        border-left-color: #ffc107;
    }
    .transaction-row.status-failed {
        border-left-color: #dc3545;
    }
    .transaction-row.status-cancelled {
        border-left-color: #6c757d;
    }
    .gateway-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 0.85rem;
    }
    .filter-card {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
    }
    .json-viewer {
        background: #2d2d2d;
        color: #f8f8f2;
        padding: 15px;
        border-radius: 8px;
        font-family: 'Monaco', 'Consolas', monospace;
        font-size: 12px;
        max-height: 300px;
        overflow-y: auto;
    }
</style>
@endpush

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1 class="h3 mb-0">
            <i class="fas fa-history me-2"></i> Transaction History
        </h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.payment-gateways.index') }}">Payment Gateways</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.payment-gateways.show', $paymentGateway) }}">{{ ucfirst($paymentGateway->gateway_name) }}</a></li>
                <li class="breadcrumb-item active">Transactions</li>
            </ol>
        </nav>
    </div>
    <div>
        <a href="{{ route('admin.payment-gateways.show', $paymentGateway) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Gateway
        </a>
    </div>
</div>

<!-- Gateway Info -->
<div class="card mb-4">
    <div class="card-body d-flex align-items-center">
        <div class="gateway-icon me-3 p-3 rounded-circle" style="background: {{ $paymentGateway->is_active ? '#e8f5e9' : '#ffebee' }};">
            @switch($paymentGateway->gateway_name)
                @case('toyyibpay')
                    <i class="fas fa-credit-card fa-lg" style="color: #1a237e;"></i>
                    @break
                @case('senangpay')
                    <i class="fas fa-credit-card fa-lg" style="color: #00bcd4;"></i>
                    @break
                @case('billplz')
                    <i class="fas fa-credit-card fa-lg" style="color: #ff5722;"></i>
                    @break
                @default
                    <i class="fas fa-credit-card fa-lg"></i>
            @endswitch
        </div>
        <div>
            <h5 class="mb-1">{{ ucfirst($paymentGateway->gateway_name) }}</h5>
            <p class="mb-0 text-muted">
                <span class="badge {{ $paymentGateway->is_active ? 'bg-success' : 'bg-secondary' }}">
                    {{ $paymentGateway->is_active ? 'Active' : 'Inactive' }}
                </span>
                <span class="badge {{ $paymentGateway->is_sandbox ? 'bg-warning text-dark' : 'bg-primary' }} ms-1">
                    {{ $paymentGateway->is_sandbox ? 'Sandbox' : 'Production' }}
                </span>
            </p>
        </div>
    </div>
</div>

<!-- Statistics Summary -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="stat-card">
            <div class="d-flex align-items-center">
                <div class="stat-icon me-3 p-3 rounded-circle" style="background: #e3f2fd;">
                    <i class="fas fa-list text-primary"></i>
                </div>
                <div>
                    <h3>{{ number_format($statistics['total']) }}</h3>
                    <p>Total Transactions</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="d-flex align-items-center">
                <div class="stat-icon me-3 p-3 rounded-circle" style="background: #e8f5e9;">
                    <i class="fas fa-check-circle text-success"></i>
                </div>
                <div>
                    <h3>{{ number_format($statistics['completed']) }}</h3>
                    <p>Completed</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="d-flex align-items-center">
                <div class="stat-icon me-3 p-3 rounded-circle" style="background: #fff3e0;">
                    <i class="fas fa-money-bill-wave text-warning"></i>
                </div>
                <div>
                    <h3>RM {{ number_format($statistics['total_amount'], 2) }}</h3>
                    <p>Total Collected</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter Card -->
<div class="filter-card">
    <form method="GET" action="{{ route('admin.payment-gateways.transactions', $paymentGateway) }}">
        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Search</label>
                <input type="text" class="form-control" name="search" value="{{ request('search') }}"
                       placeholder="Transaction ID or Invoice #">
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select class="form-select" name="status">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">From Date</label>
                <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">To Date</label>
                <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search me-1"></i> Filter
                </button>
                <a href="{{ route('admin.payment-gateways.transactions', $paymentGateway) }}" class="btn btn-secondary">
                    <i class="fas fa-times me-1"></i> Clear
                </a>
            </div>
        </div>
    </form>
</div>

<!-- Transactions Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-exchange-alt me-2"></i> Transactions</span>
        <span class="badge bg-secondary">{{ $transactions->total() }} records</span>
    </div>
    <div class="card-body p-0">
        @if($transactions->isEmpty())
            <div class="text-center py-5">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <p class="text-muted">No transactions found.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Transaction ID</th>
                            <th>Invoice</th>
                            <th>Student</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Gateway Status</th>
                            <th>Date</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transactions as $transaction)
                            <tr class="transaction-row status-{{ $transaction->status }}">
                                <td>
                                    <code class="text-primary">{{ Str::limit($transaction->transaction_id, 15) }}</code>
                                    <br>
                                    <small class="text-muted">{{ $transaction->currency }}</small>
                                </td>
                                <td>
                                    @if($transaction->invoice)
                                        <a href="{{ route('admin.invoices.show', $transaction->invoice) }}" class="text-decoration-none">
                                            {{ $transaction->invoice->invoice_number }}
                                        </a>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @if($transaction->invoice && $transaction->invoice->student)
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm me-2">
                                                {{ strtoupper(substr($transaction->invoice->student->user->name ?? '', 0, 1)) }}
                                            </div>
                                            <div>
                                                <span class="fw-medium">{{ $transaction->invoice->student->user->name ?? 'N/A' }}</span>
                                                <br>
                                                <small class="text-muted">{{ $transaction->customer_email ?? '' }}</small>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-muted">{{ $transaction->customer_email ?? 'N/A' }}</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="fw-bold">RM {{ number_format($transaction->amount, 2) }}</span>
                                </td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'pending' => 'warning',
                                            'completed' => 'success',
                                            'failed' => 'danger',
                                            'cancelled' => 'secondary',
                                        ];
                                    @endphp
                                    <span class="badge bg-{{ $statusColors[$transaction->status] ?? 'secondary' }}">
                                        {{ ucfirst($transaction->status) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="text-muted">{{ $transaction->gateway_status ?? '-' }}</span>
                                </td>
                                <td>
                                    {{ $transaction->created_at->format('d M Y') }}
                                    <br>
                                    <small class="text-muted">{{ $transaction->created_at->format('h:i A') }}</small>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-info view-details"
                                                data-bs-toggle="modal"
                                                data-bs-target="#transactionModal"
                                                data-transaction="{{ json_encode($transaction) }}">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        @if($transaction->payment)
                                            <a href="{{ route('admin.payments.show', $transaction->payment) }}"
                                               class="btn btn-outline-success" title="View Payment">
                                                <i class="fas fa-receipt"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
    @if($transactions->hasPages())
        <div class="card-footer">
            {{ $transactions->withQueryString()->links() }}
        </div>
    @endif
</div>

<!-- Transaction Detail Modal -->
<div class="modal fade" id="transactionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-info-circle me-2"></i> Transaction Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-3">Transaction Information</h6>
                        <table class="table table-sm">
                            <tr>
                                <th width="40%">Transaction ID:</th>
                                <td><code id="modal-txn-id"></code></td>
                            </tr>
                            <tr>
                                <th>Amount:</th>
                                <td id="modal-amount"></td>
                            </tr>
                            <tr>
                                <th>Currency:</th>
                                <td id="modal-currency"></td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td id="modal-status"></td>
                            </tr>
                            <tr>
                                <th>Gateway Status:</th>
                                <td id="modal-gateway-status"></td>
                            </tr>
                            <tr>
                                <th>Created At:</th>
                                <td id="modal-created-at"></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted mb-3">Customer Information</h6>
                        <table class="table table-sm">
                            <tr>
                                <th width="40%">Email:</th>
                                <td id="modal-email"></td>
                            </tr>
                            <tr>
                                <th>Phone:</th>
                                <td id="modal-phone"></td>
                            </tr>
                            <tr>
                                <th>IP Address:</th>
                                <td id="modal-ip"></td>
                            </tr>
                            <tr>
                                <th>Callback URL:</th>
                                <td><small id="modal-callback"></small></td>
                            </tr>
                            <tr>
                                <th>Webhook Received:</th>
                                <td id="modal-webhook"></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <h6 class="text-muted mb-3 mt-4">Gateway Response</h6>
                <div class="json-viewer" id="modal-response">
                    <pre></pre>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // View transaction details
    $('.view-details').on('click', function() {
        var transaction = $(this).data('transaction');

        $('#modal-txn-id').text(transaction.transaction_id);
        $('#modal-amount').html('<strong>RM ' + parseFloat(transaction.amount).toFixed(2) + '</strong>');
        $('#modal-currency').text(transaction.currency || 'MYR');
        $('#modal-status').html('<span class="badge bg-' + getStatusColor(transaction.status) + '">' + capitalizeFirst(transaction.status) + '</span>');
        $('#modal-gateway-status').text(transaction.gateway_status || '-');
        $('#modal-created-at').text(formatDate(transaction.created_at));
        $('#modal-email').text(transaction.customer_email || '-');
        $('#modal-phone').text(transaction.customer_phone || '-');
        $('#modal-ip').text(transaction.ip_address || '-');
        $('#modal-callback').text(transaction.callback_url || '-');
        $('#modal-webhook').text(transaction.webhook_received_at ? formatDate(transaction.webhook_received_at) : 'Not received');

        // Format JSON response
        if (transaction.gateway_response) {
            var formattedJson = JSON.stringify(transaction.gateway_response, null, 2);
            $('#modal-response pre').text(formattedJson);
        } else {
            $('#modal-response pre').text('No response data');
        }
    });

    function getStatusColor(status) {
        var colors = {
            'pending': 'warning',
            'completed': 'success',
            'failed': 'danger',
            'cancelled': 'secondary'
        };
        return colors[status] || 'secondary';
    }

    function capitalizeFirst(string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
    }

    function formatDate(dateString) {
        if (!dateString) return '-';
        var date = new Date(dateString);
        return date.toLocaleDateString('en-GB', {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }
});
</script>

<style>
.avatar-sm {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #e3f2fd;
    color: #1976d2;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    font-weight: 600;
}
</style>
@endpush
