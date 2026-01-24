@extends('layouts.app')

@section('title', 'Transaction ' . $transaction->transaction_number)

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Transaction Details</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('staff.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('staff.pos.my-transactions') }}">My Transactions</a></li>
                    <li class="breadcrumb-item active">{{ $transaction->transaction_number }}</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('staff.pos.receipt', $transaction) }}" class="btn btn-outline-primary" target="_blank">
                <i class="fas fa-receipt me-1"></i> View Receipt
            </a>
            <a href="{{ route('staff.pos.my-transactions') }}" class="btn btn-outline-secondary ms-2">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Transaction Details -->
        <div class="col-lg-8">
            <!-- Status Banner -->
            @if($transaction->status == 'voided')
                <div class="alert alert-danger d-flex align-items-center mb-4">
                    <i class="fas fa-times-circle fa-2x me-3"></i>
                    <div>
                        <strong>This transaction has been voided</strong>
                        @if($transaction->void_reason)
                            <p class="mb-0 mt-1">Reason: {{ $transaction->void_reason }}</p>
                        @endif
                        @if($transaction->voided_at)
                            <small>Voided on {{ $transaction->voided_at->format('d M Y, h:i A') }}</small>
                        @endif
                    </div>
                </div>
            @elseif($transaction->status == 'refunded')
                <div class="alert alert-warning d-flex align-items-center mb-4">
                    <i class="fas fa-undo fa-2x me-3"></i>
                    <div>
                        <strong>This transaction has been refunded</strong>
                        @if($transaction->refund_amount)
                            <p class="mb-0 mt-1">Refund Amount: RM {{ number_format($transaction->refund_amount, 2) }}</p>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Transaction Info Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-info-circle text-primary me-2"></i>
                            Transaction Information
                        </h5>
                        @switch($transaction->status)
                            @case('completed')
                                <span class="badge bg-success fs-6">
                                    <i class="fas fa-check-circle me-1"></i> Completed
                                </span>
                                @break
                            @case('voided')
                                <span class="badge bg-danger fs-6">
                                    <i class="fas fa-times-circle me-1"></i> Voided
                                </span>
                                @break
                            @case('refunded')
                                <span class="badge bg-warning text-dark fs-6">
                                    <i class="fas fa-undo me-1"></i> Refunded
                                </span>
                                @break
                        @endswitch
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless mb-0">
                                <tr>
                                    <td class="text-muted" width="40%">Transaction #</td>
                                    <td><strong>{{ $transaction->transaction_number }}</strong></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Date</td>
                                    <td>{{ $transaction->created_at->format('l, d M Y') }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Time</td>
                                    <td>{{ $transaction->created_at->format('h:i:s A') }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless mb-0">
                                <tr>
                                    <td class="text-muted" width="40%">Payment Method</td>
                                    <td>
                                        @if($transaction->payment_method == 'cash')
                                            <span class="badge bg-success">
                                                <i class="fas fa-money-bill me-1"></i> Cash
                                            </span>
                                        @else
                                            <span class="badge bg-primary">
                                                <i class="fas fa-qrcode me-1"></i> QR Payment
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                                @if($transaction->payment_method == 'cash')
                                    <tr>
                                        <td class="text-muted">Amount Received</td>
                                        <td>RM {{ number_format($transaction->amount_received, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Change Given</td>
                                        <td>RM {{ number_format($transaction->change_amount, 2) }}</td>
                                    </tr>
                                @else
                                    <tr>
                                        <td class="text-muted">QR Reference</td>
                                        <td>{{ $transaction->qr_reference ?? '-' }}</td>
                                    </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Items Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">
                        <i class="fas fa-shopping-basket text-info me-2"></i>
                        Items ({{ $transaction->items->count() }})
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>#</th>
                                    <th>Item</th>
                                    <th class="text-center">Quantity</th>
                                    <th class="text-end">Unit Price</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($transaction->items as $index => $item)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <strong>{{ $item->inventory->name ?? 'Unknown Item' }}</strong>
                                            @if($item->inventory && $item->inventory->sku)
                                                <br>
                                                <small class="text-muted">SKU: {{ $item->inventory->sku }}</small>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-secondary">{{ $item->quantity }}</span>
                                        </td>
                                        <td class="text-end">RM {{ number_format($item->unit_price, 2) }}</td>
                                        <td class="text-end">
                                            <strong>RM {{ number_format($item->subtotal, 2) }}</strong>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Notes Card -->
            @if($transaction->notes)
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0">
                            <i class="fas fa-sticky-note text-warning me-2"></i> Notes
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">{{ $transaction->notes }}</p>
                    </div>
                </div>
            @endif
        </div>

        <!-- Summary Sidebar -->
        <div class="col-lg-4">
            <!-- Payment Summary -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="mb-0">
                        <i class="fas fa-calculator me-2"></i> Payment Summary
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <td>Subtotal</td>
                            <td class="text-end">RM {{ number_format($transaction->subtotal, 2) }}</td>
                        </tr>
                        @if($transaction->discount_amount > 0)
                            <tr>
                                <td>Discount</td>
                                <td class="text-end text-danger">
                                    - RM {{ number_format($transaction->discount_amount, 2) }}
                                </td>
                            </tr>
                        @endif
                        <tr class="border-top">
                            <td><strong>Total</strong></td>
                            <td class="text-end">
                                <strong class="text-primary fs-4">
                                    RM {{ number_format($transaction->total_amount, 2) }}
                                </strong>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">
                        <i class="fas fa-bolt text-warning me-2"></i> Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('staff.pos.receipt', $transaction) }}"
                           class="btn btn-outline-primary" target="_blank">
                            <i class="fas fa-eye me-2"></i> View Receipt
                        </a>
                        <button class="btn btn-outline-secondary" onclick="window.print()">
                            <i class="fas fa-print me-2"></i> Print Receipt
                        </button>
                        <a href="{{ route('staff.pos.index') }}" class="btn btn-success">
                            <i class="fas fa-plus me-2"></i> New Transaction
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
