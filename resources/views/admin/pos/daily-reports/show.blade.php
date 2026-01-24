@extends('layouts.app')

@section('title', 'Daily Report - ' . $report->report_date->format('d M Y'))

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Daily Cash Report</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.daily-cash-reports.index') }}">Daily Reports</a></li>
                    <li class="breadcrumb-item active">{{ $report->report_date->format('d M Y') }}</li>
                </ol>
            </nav>
        </div>
        <div>
            @if($report->status == 'open' && $report->report_date->isToday())
                <a href="{{ route('admin.daily-cash-reports.close', $report) }}" class="btn btn-success">
                    <i class="fas fa-lock me-1"></i> Close Day
                </a>
            @endif
            <a href="{{ route('admin.daily-cash-reports.download', $report) }}" class="btn btn-outline-primary ms-2">
                <i class="fas fa-file-pdf me-1"></i> Download PDF
            </a>
            <a href="{{ route('admin.daily-cash-reports.index') }}" class="btn btn-outline-secondary ms-2">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Report Details -->
        <div class="col-lg-4">
            <!-- Report Info Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Report Information</h5>
                        @if($report->status == 'open')
                            <span class="badge bg-warning text-dark">
                                <i class="fas fa-unlock me-1"></i> Open
                            </span>
                        @else
                            <span class="badge bg-success">
                                <i class="fas fa-lock me-1"></i> Closed
                            </span>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <td class="text-muted">Date</td>
                            <td class="text-end">
                                <strong>{{ $report->report_date->format('l, d M Y') }}</strong>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Opened By</td>
                            <td class="text-end">{{ $report->openedBy->name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Opened At</td>
                            <td class="text-end">{{ $report->opened_at ? $report->opened_at->format('h:i A') : 'N/A' }}</td>
                        </tr>
                        @if($report->status == 'closed')
                            <tr>
                                <td class="text-muted">Closed By</td>
                                <td class="text-end">{{ $report->closedBy->name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Closed At</td>
                                <td class="text-end">{{ $report->closed_at ? $report->closed_at->format('h:i A') : 'N/A' }}</td>
                            </tr>
                        @endif
                    </table>
                </div>
            </div>

            <!-- Cash Summary Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-coins text-warning me-2"></i>Cash Summary</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <td class="text-muted">Opening Cash</td>
                            <td class="text-end">RM {{ number_format($report->opening_cash, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Cash Sales</td>
                            <td class="text-end text-success">+ RM {{ number_format($report->cash_sales, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Refunds (Cash)</td>
                            <td class="text-end text-danger">- RM {{ number_format($report->cash_refunds ?? 0, 2) }}</td>
                        </tr>
                        <tr class="border-top">
                            <td><strong>Expected Cash</strong></td>
                            <td class="text-end"><strong>RM {{ number_format($report->expected_cash, 2) }}</strong></td>
                        </tr>
                        @if($report->status == 'closed')
                            <tr>
                                <td class="text-muted">Actual Cash</td>
                                <td class="text-end">RM {{ number_format($report->actual_cash, 2) }}</td>
                            </tr>
                            <tr class="border-top">
                                <td><strong>Variance</strong></td>
                                <td class="text-end">
                                    @php
                                        $variance = $report->actual_cash - $report->expected_cash;
                                    @endphp
                                    @if($variance == 0)
                                        <span class="badge bg-success">Balanced</span>
                                    @elseif($variance > 0)
                                        <span class="badge bg-info">+RM {{ number_format($variance, 2) }} Over</span>
                                    @else
                                        <span class="badge bg-danger">-RM {{ number_format(abs($variance), 2) }} Short</span>
                                    @endif
                                </td>
                            </tr>
                        @endif
                    </table>
                </div>
            </div>

            <!-- Sales Summary Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-chart-pie text-primary me-2"></i>Sales Summary</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <td class="text-muted">Total Transactions</td>
                            <td class="text-end">{{ number_format($report->total_transactions) }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Completed Sales</td>
                            <td class="text-end text-success">{{ number_format($report->completed_transactions ?? $report->total_transactions) }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Voided</td>
                            <td class="text-end text-danger">{{ number_format($report->voided_transactions ?? 0) }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Refunded</td>
                            <td class="text-end text-warning">{{ number_format($report->refunded_transactions ?? 0) }}</td>
                        </tr>
                        <tr class="border-top">
                            <td class="text-muted">Cash Sales</td>
                            <td class="text-end">RM {{ number_format($report->cash_sales, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">QR Sales</td>
                            <td class="text-end">RM {{ number_format($report->qr_sales, 2) }}</td>
                        </tr>
                        <tr class="border-top bg-light">
                            <td><strong>Total Sales</strong></td>
                            <td class="text-end"><strong class="text-primary">RM {{ number_format($report->total_sales, 2) }}</strong></td>
                        </tr>
                    </table>
                </div>
            </div>

            @if($report->notes)
                <!-- Notes Card -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0"><i class="fas fa-sticky-note text-info me-2"></i>Notes</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">{{ $report->notes }}</p>
                    </div>
                </div>
            @endif
        </div>

        <!-- Transactions -->
        <div class="col-lg-8">
            <!-- Sales by Payment Method Chart -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-chart-bar text-success me-2"></i>Sales Breakdown</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <div class="p-3 bg-success bg-opacity-10 rounded">
                                <i class="fas fa-money-bill-wave fa-2x text-success mb-2"></i>
                                <h4 class="mb-1">RM {{ number_format($report->cash_sales, 2) }}</h4>
                                <small class="text-muted">Cash Payments</small>
                                <div class="progress mt-2" style="height: 5px;">
                                    @php
                                        $cashPercent = $report->total_sales > 0 ? ($report->cash_sales / $report->total_sales) * 100 : 0;
                                    @endphp
                                    <div class="progress-bar bg-success" style="width: {{ $cashPercent }}%"></div>
                                </div>
                                <small class="text-muted">{{ number_format($cashPercent, 1) }}%</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-primary bg-opacity-10 rounded">
                                <i class="fas fa-qrcode fa-2x text-primary mb-2"></i>
                                <h4 class="mb-1">RM {{ number_format($report->qr_sales, 2) }}</h4>
                                <small class="text-muted">QR Payments</small>
                                <div class="progress mt-2" style="height: 5px;">
                                    @php
                                        $qrPercent = $report->total_sales > 0 ? ($report->qr_sales / $report->total_sales) * 100 : 0;
                                    @endphp
                                    <div class="progress-bar bg-primary" style="width: {{ $qrPercent }}%"></div>
                                </div>
                                <small class="text-muted">{{ number_format($qrPercent, 1) }}%</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-warning bg-opacity-10 rounded">
                                <i class="fas fa-shopping-bag fa-2x text-warning mb-2"></i>
                                <h4 class="mb-1">RM {{ number_format($report->total_sales, 2) }}</h4>
                                <small class="text-muted">Total Sales</small>
                                <div class="progress mt-2" style="height: 5px;">
                                    <div class="progress-bar bg-warning" style="width: 100%"></div>
                                </div>
                                <small class="text-muted">{{ number_format($report->total_transactions) }} transactions</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Transactions Table -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-receipt text-info me-2"></i>Transactions</h5>
                        <span class="badge bg-primary">{{ $transactions->total() }} Total</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>Transaction #</th>
                                    <th>Time</th>
                                    <th>Items</th>
                                    <th class="text-end">Amount</th>
                                    <th class="text-center">Payment</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($transactions as $transaction)
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.pos.transactions.show', $transaction) }}" class="fw-bold text-decoration-none">
                                                {{ $transaction->transaction_number }}
                                            </a>
                                        </td>
                                        <td>{{ $transaction->created_at->format('h:i A') }}</td>
                                        <td>{{ $transaction->items_count ?? $transaction->items->count() }} items</td>
                                        <td class="text-end">
                                            <strong>RM {{ number_format($transaction->total_amount, 2) }}</strong>
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
                                                    <span class="badge bg-success">Completed</span>
                                                    @break
                                                @case('voided')
                                                    <span class="badge bg-danger">Voided</span>
                                                    @break
                                                @case('refunded')
                                                    <span class="badge bg-warning text-dark">Refunded</span>
                                                    @break
                                                @default
                                                    <span class="badge bg-secondary">{{ ucfirst($transaction->status) }}</span>
                                            @endswitch
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('admin.pos.transactions.show', $transaction) }}"
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fas fa-receipt fa-2x mb-2"></i>
                                                <p class="mb-0">No transactions for this day.</p>
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
                        {{ $transactions->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
