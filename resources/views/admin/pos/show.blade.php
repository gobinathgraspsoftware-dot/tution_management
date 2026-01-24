@extends('layouts.app')

@section('title', 'Transaction Detail')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('admin.pos.index') }}">POS</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.pos.transactions') }}">Transactions</a></li>
                    <li class="breadcrumb-item active">{{ $transaction->transaction_number }}</li>
                </ol>
            </nav>
            <h4 class="mb-0">Transaction {{ $transaction->transaction_number }}</h4>
        </div>
        <a href="{{ route('admin.pos.transactions') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Transaction Details Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Transaction Details</h5>
                    @if($transaction->status == 'completed')
                        <span class="badge bg-success fs-6"><i class="fas fa-check-circle me-1"></i>Completed</span>
                    @elseif($transaction->status == 'voided')
                        <span class="badge bg-danger fs-6"><i class="fas fa-ban me-1"></i>Voided</span>
                    @elseif($transaction->status == 'refunded')
                        <span class="badge bg-warning text-dark fs-6"><i class="fas fa-undo me-1"></i>Refunded</span>
                    @endif
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <table class="table table-borderless mb-0">
                                <tr>
                                    <td class="text-muted" width="150">Transaction #</td>
                                    <td class="fw-semibold">{{ $transaction->transaction_number }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Date & Time</td>
                                    <td>{{ $transaction->transaction_date->format('d M Y, h:i A') }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Cashier</td>
                                    <td>{{ $transaction->cashier->name ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless mb-0">
                                <tr>
                                    <td class="text-muted" width="150">Payment Method</td>
                                    <td>
                                        @if($transaction->payment_method == 'cash')
                                            <span class="badge bg-success"><i class="fas fa-money-bill me-1"></i>Cash</span>
                                        @else
                                            <span class="badge bg-primary"><i class="fas fa-qrcode me-1"></i>QR Pay</span>
                                        @endif
                                    </td>
                                </tr>
                                @if($transaction->payment_method == 'cash')
                                <tr>
                                    <td class="text-muted">Amount Received</td>
                                    <td>RM {{ number_format($transaction->amount_received, 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Change</td>
                                    <td>RM {{ number_format($transaction->change_amount, 2) }}</td>
                                </tr>
                                @else
                                <tr>
                                    <td class="text-muted">Reference #</td>
                                    <td>{{ $transaction->reference_number ?? '-' }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>

                    @if($transaction->notes)
                    <div class="alert alert-secondary mb-4">
                        <strong>Notes:</strong><br>
                        {!! nl2br(e($transaction->notes)) !!}
                    </div>
                    @endif

                    <!-- Items Table -->
                    <h6 class="mb-3">Items</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th width="50">#</th>
                                    <th>Item</th>
                                    <th class="text-center" width="100">Qty</th>
                                    <th class="text-end" width="120">Unit Price</th>
                                    <th class="text-end" width="120">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($transaction->items as $index => $item)
                                <tr>
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td>
                                        <div class="fw-semibold">{{ $item->inventory->name ?? 'Deleted Item' }}</div>
                                        @if($item->inventory && $item->inventory->sku)
                                        <small class="text-muted">SKU: {{ $item->inventory->sku }}</small>
                                        @endif
                                    </td>
                                    <td class="text-center">{{ $item->quantity }}</td>
                                    <td class="text-end">RM {{ number_format($item->unit_price, 2) }}</td>
                                    <td class="text-end fw-semibold">RM {{ number_format($item->total_price, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="4" class="text-end">Subtotal</td>
                                    <td class="text-end">RM {{ number_format($transaction->subtotal, 2) }}</td>
                                </tr>
                                @if($transaction->discount > 0)
                                <tr>
                                    <td colspan="4" class="text-end">Discount</td>
                                    <td class="text-end text-danger">- RM {{ number_format($transaction->discount, 2) }}</td>
                                </tr>
                                @endif
                                @if($transaction->tax > 0)
                                <tr>
                                    <td colspan="4" class="text-end">Tax</td>
                                    <td class="text-end">RM {{ number_format($transaction->tax, 2) }}</td>
                                </tr>
                                @endif
                                <tr class="fw-bold" style="background-color: #FFF8ED;">
                                    <td colspan="4" class="text-end">Total</td>
                                    <td class="text-end" style="color: #FDA530;">RM {{ number_format($transaction->total_amount, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Actions Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.pos.receipt', $transaction) }}" class="btn btn-outline-primary" target="_blank">
                            <i class="fas fa-receipt me-2"></i> View Receipt
                        </a>
                        <a href="{{ route('admin.pos.print', $transaction) }}" class="btn btn-outline-secondary" target="_blank">
                            <i class="fas fa-print me-2"></i> Print Receipt
                        </a>
                        <a href="{{ route('admin.pos.download', $transaction) }}" class="btn btn-outline-info">
                            <i class="fas fa-download me-2"></i> Download PDF
                        </a>

                        @if($transaction->isCompleted())
                            <hr>
                            @can('refund-pos-transaction')
                            <button type="button" class="btn btn-warning" onclick="showRefundModal()">
                                <i class="fas fa-undo me-2"></i> Process Refund
                            </button>
                            @endcan
                            @can('void-pos-transaction')
                            <button type="button" class="btn btn-danger" onclick="showVoidModal()">
                                <i class="fas fa-ban me-2"></i> Void Transaction
                            </button>
                            @endcan
                        @endif
                    </div>
                </div>
            </div>

            <!-- Timeline Card -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Timeline</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <p class="mb-0 fw-semibold">Transaction Created</p>
                                <small class="text-muted">{{ $transaction->created_at->format('d M Y, h:i A') }}</small>
                            </div>
                        </div>
                        @if($transaction->status == 'voided')
                        <div class="timeline-item">
                            <div class="timeline-marker bg-danger"></div>
                            <div class="timeline-content">
                                <p class="mb-0 fw-semibold">Transaction Voided</p>
                                <small class="text-muted">{{ $transaction->updated_at->format('d M Y, h:i A') }}</small>
                            </div>
                        </div>
                        @elseif($transaction->status == 'refunded')
                        <div class="timeline-item">
                            <div class="timeline-marker bg-warning"></div>
                            <div class="timeline-content">
                                <p class="mb-0 fw-semibold">Refund Processed</p>
                                <small class="text-muted">{{ $transaction->updated_at->format('d M Y, h:i A') }}</small>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
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
                    This action will void the transaction and restore inventory stock for all items.
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

<!-- Refund Modal -->
<div class="modal fade" id="refundModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-undo text-warning me-2"></i>Process Refund</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Select items and quantities to refund. Stock will be restored for refunded items.
                </div>

                <table class="table table-bordered" id="refundItemsTable">
                    <thead class="table-light">
                        <tr>
                            <th width="40">
                                <input type="checkbox" id="selectAllRefund" onchange="toggleAllRefundItems(this)">
                            </th>
                            <th>Item</th>
                            <th class="text-center" width="100">Purchased</th>
                            <th class="text-center" width="150">Refund Qty</th>
                            <th class="text-end" width="120">Refund Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transaction->items as $item)
                        <tr data-item-id="{{ $item->id }}" data-unit-price="{{ $item->unit_price }}" data-max-qty="{{ $item->quantity }}">
                            <td class="text-center">
                                <input type="checkbox" class="refund-checkbox" data-item-id="{{ $item->id }}" onchange="updateRefundTotal()">
                            </td>
                            <td>{{ $item->inventory->name ?? 'Deleted Item' }}</td>
                            <td class="text-center">{{ $item->quantity }}</td>
                            <td class="text-center">
                                <input type="number" class="form-control form-control-sm text-center refund-qty"
                                       data-item-id="{{ $item->id }}"
                                       value="{{ $item->quantity }}"
                                       min="1" max="{{ $item->quantity }}"
                                       disabled
                                       onchange="updateRefundTotal()">
                            </td>
                            <td class="text-end refund-amount" data-item-id="{{ $item->id }}">
                                RM {{ number_format($item->total_price, 2) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr class="fw-bold">
                            <td colspan="4" class="text-end">Total Refund:</td>
                            <td class="text-end text-danger" id="totalRefundAmount">RM 0.00</td>
                        </tr>
                    </tfoot>
                </table>

                <div class="mb-3">
                    <label class="form-label">Reason for Refund <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="refundReason" rows="2" placeholder="Enter reason for this refund..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" onclick="processRefund()">
                    <i class="fas fa-undo me-1"></i> Process Refund
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
.timeline {
    position: relative;
    padding-left: 30px;
}
.timeline-item {
    position: relative;
    padding-bottom: 1.5rem;
}
.timeline-item:before {
    content: '';
    position: absolute;
    left: -24px;
    top: 8px;
    height: calc(100% + 8px);
    width: 2px;
    background: #e5e7eb;
}
.timeline-item:last-child:before {
    display: none;
}
.timeline-marker {
    position: absolute;
    left: -30px;
    top: 4px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
}
</style>
@endsection

@section('scripts')
<script>
function showVoidModal() {
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
        url: '{{ route("admin.pos.void", $transaction) }}',
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

function showRefundModal() {
    document.getElementById('refundReason').value = '';
    document.querySelectorAll('.refund-checkbox').forEach(cb => {
        cb.checked = false;
    });
    document.querySelectorAll('.refund-qty').forEach(input => {
        input.disabled = true;
        const row = input.closest('tr');
        input.value = row.dataset.maxQty;
    });
    updateRefundTotal();
    new bootstrap.Modal(document.getElementById('refundModal')).show();
}

function toggleAllRefundItems(checkbox) {
    document.querySelectorAll('.refund-checkbox').forEach(cb => {
        cb.checked = checkbox.checked;
        const row = cb.closest('tr');
        const qtyInput = row.querySelector('.refund-qty');
        qtyInput.disabled = !checkbox.checked;
    });
    updateRefundTotal();
}

document.querySelectorAll('.refund-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        const row = this.closest('tr');
        const qtyInput = row.querySelector('.refund-qty');
        qtyInput.disabled = !this.checked;
        updateRefundTotal();
    });
});

function updateRefundTotal() {
    let total = 0;
    document.querySelectorAll('.refund-checkbox:checked').forEach(checkbox => {
        const row = checkbox.closest('tr');
        const unitPrice = parseFloat(row.dataset.unitPrice);
        const qty = parseInt(row.querySelector('.refund-qty').value) || 0;
        const amount = unitPrice * qty;

        row.querySelector('.refund-amount').textContent = 'RM ' + amount.toFixed(2);
        total += amount;
    });

    document.getElementById('totalRefundAmount').textContent = 'RM ' + total.toFixed(2);
}

function processRefund() {
    const reason = document.getElementById('refundReason').value.trim();

    if (!reason) {
        toastr.error('Please enter a reason for the refund');
        return;
    }

    const items = [];
    document.querySelectorAll('.refund-checkbox:checked').forEach(checkbox => {
        const row = checkbox.closest('tr');
        items.push({
            item_id: parseInt(row.dataset.itemId),
            quantity: parseInt(row.querySelector('.refund-qty').value)
        });
    });

    if (items.length === 0) {
        toastr.error('Please select at least one item to refund');
        return;
    }

    $.ajax({
        url: '{{ route("admin.pos.refund", $transaction) }}',
        method: 'POST',
        data: {
            items: items,
            reason: reason,
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            if (response.success) {
                toastr.success('Refund processed successfully');
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
