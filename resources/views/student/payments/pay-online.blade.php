@extends('layouts.app')

@section('title', 'Pay Online')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Pay Online</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('student.payments.index') }}">Payments</a></li>
                    <li class="breadcrumb-item active">Pay Online</li>
                </ol>
            </nav>
        </div>
    </div>

    @if(!$gatewaysAvailable)
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Online Payment Unavailable:</strong> No payment gateway is currently configured. 
            Please contact the office for alternative payment methods.
        </div>
    @endif

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 text-white-50">Total Outstanding</h6>
                            <h3 class="mb-0">RM {{ number_format($summary['total_outstanding'], 2) }}</h3>
                        </div>
                        <i class="fas fa-exclamation-triangle fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-warning text-dark">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 opacity-75">Overdue</h6>
                            <h3 class="mb-0">RM {{ number_format($summary['total_overdue'], 2) }}</h3>
                        </div>
                        <i class="fas fa-clock fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 text-white-50">Pending Invoices</h6>
                            <h3 class="mb-0">{{ $summary['invoices_count'] }}</h3>
                        </div>
                        <i class="fas fa-file-invoice fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 text-white-50">Overdue Count</h6>
                            <h3 class="mb-0">{{ $summary['overdue_count'] }}</h3>
                        </div>
                        <i class="fas fa-exclamation-circle fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Unpaid Invoices -->
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-file-invoice-dollar me-2"></i>Select Invoice to Pay</h5>
                    <span class="badge bg-warning text-dark">{{ $unpaidInvoices->count() }} unpaid</span>
                </div>
                <div class="card-body p-0">
                    @if($unpaidInvoices->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Invoice #</th>
                                        <th>Package</th>
                                        <th>Due Date</th>
                                        <th class="text-end">Amount Due</th>
                                        <th>Status</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($unpaidInvoices as $invoice)
                                        <tr class="{{ $invoice->status === 'overdue' ? 'table-danger' : '' }}">
                                            <td>
                                                <strong>{{ $invoice->invoice_number }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $invoice->type_label ?? ucfirst($invoice->type) }}</small>
                                            </td>
                                            <td>
                                                @if($invoice->enrollment && $invoice->enrollment->package)
                                                    {{ $invoice->enrollment->package->name }}
                                                    @if($invoice->billing_period)
                                                        <br><small class="text-muted">{{ $invoice->billing_period }}</small>
                                                    @endif
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $invoice->due_date ? $invoice->due_date->format('d M Y') : '-' }}
                                                @if($invoice->due_date && $invoice->due_date->isPast())
                                                    <br><small class="text-danger">
                                                        <i class="fas fa-exclamation-triangle"></i> Overdue
                                                    </small>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                <strong class="text-danger fs-5">RM {{ number_format($invoice->balance, 2) }}</strong>
                                                <br>
                                                <small class="text-muted">Total: RM {{ number_format($invoice->total_amount, 2) }}</small>
                                            </td>
                                            <td>
                                                @switch($invoice->status)
                                                    @case('pending')
                                                        <span class="badge bg-warning text-dark">Pending</span>
                                                        @break
                                                    @case('partial')
                                                        <span class="badge bg-info">Partially Paid</span>
                                                        @break
                                                    @case('overdue')
                                                        <span class="badge bg-danger">Overdue</span>
                                                        @break
                                                    @default
                                                        <span class="badge bg-secondary">{{ ucfirst($invoice->status) }}</span>
                                                @endswitch
                                            </td>
                                            <td class="text-center">
                                                @if($gatewaysAvailable)
                                                    <a href="{{ route('student.payments.pay-online', $invoice) }}" 
                                                       class="btn btn-success btn-sm">
                                                        <i class="fas fa-credit-card me-1"></i> Pay Now
                                                    </a>
                                                @else
                                                    <button class="btn btn-secondary btn-sm" disabled>
                                                        <i class="fas fa-ban me-1"></i> Unavailable
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="3" class="text-end"><strong>Total Outstanding:</strong></td>
                                        <td class="text-end">
                                            <strong class="text-danger fs-5">RM {{ number_format($unpaidInvoices->sum('balance'), 2) }}</strong>
                                        </td>
                                        <td colspan="2"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                            <h4>All Clear!</h4>
                            <p class="text-muted mb-0">You don't have any outstanding invoices. All payments are up to date!</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Recent Transactions -->
            @if($recentTransactions && $recentTransactions->count() > 0)
                <div class="card shadow-sm mt-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Online Transactions</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Invoice</th>
                                        <th>Gateway</th>
                                        <th class="text-end">Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentTransactions as $transaction)
                                        <tr>
                                            <td>
                                                {{ $transaction->created_at->format('d M Y') }}
                                                <br>
                                                <small class="text-muted">{{ $transaction->created_at->format('h:i A') }}</small>
                                            </td>
                                            <td>
                                                @if($transaction->invoice)
                                                    {{ $transaction->invoice->invoice_number }}
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <small>{{ $transaction->gatewayConfig->gateway_name ?? ucfirst($transaction->gateway) }}</small>
                                            </td>
                                            <td class="text-end">RM {{ number_format($transaction->amount, 2) }}</td>
                                            <td>
                                                @switch($transaction->status)
                                                    @case('success')
                                                        <span class="badge bg-success">Success</span>
                                                        @break
                                                    @case('pending')
                                                        <span class="badge bg-warning text-dark">Pending</span>
                                                        @break
                                                    @case('failed')
                                                        <span class="badge bg-danger">Failed</span>
                                                        @break
                                                    @default
                                                        <span class="badge bg-secondary">{{ ucfirst($transaction->status) }}</span>
                                                @endswitch
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Payment Information -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Online Payment Information</h6>
                </div>
                <div class="card-body">
                    <h6 class="mb-3">How to Pay Online:</h6>
                    <ol class="mb-3">
                        <li class="mb-2">Select an invoice from the list</li>
                        <li class="mb-2">Click "Pay Now" button</li>
                        <li class="mb-2">Choose your payment gateway</li>
                        <li class="mb-2">Complete payment on the gateway page</li>
                        <li class="mb-2">You'll be redirected back after payment</li>
                        <li class="mb-2">Receipt will be sent to your email</li>
                    </ol>

                    @if($gatewaysAvailable)
                        <div class="alert alert-info mb-0">
                            <small>
                                <i class="fas fa-lock me-1"></i>
                                <strong>Secure Payment:</strong> All transactions are encrypted and processed securely.
                            </small>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Available Payment Gateways -->
            @if($gatewaysAvailable)
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="fas fa-credit-card me-2"></i>Available Payment Methods</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-column gap-2">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <span>Online Banking (FPX)</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <span>Credit/Debit Card</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <span>E-Wallet</span>
                            </div>
                        </div>
                        <hr>
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Online payment fee: <strong>RM 1.30</strong> will be added to your payment.
                        </small>
                    </div>
                </div>
            @endif

            <!-- Alternative Payment Methods -->
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i>Other Payment Methods</h6>
                </div>
                <div class="list-group list-group-flush">
                    <div class="list-group-item">
                        <i class="fas fa-university text-primary me-2"></i>
                        <strong>Bank Transfer</strong>
                        <br>
                        <small class="text-muted">Visit the office for bank details</small>
                    </div>
                    <div class="list-group-item">
                        <i class="fas fa-money-bill-wave text-success me-2"></i>
                        <strong>Cash Payment</strong>
                        <br>
                        <small class="text-muted">Pay directly at our office</small>
                    </div>
                    <div class="list-group-item">
                        <i class="fas fa-qrcode text-info me-2"></i>
                        <strong>QR Payment</strong>
                        <br>
                        <small class="text-muted">Scan and pay via your banking app</small>
                    </div>
                </div>
            </div>

            <!-- Contact Support -->
            <div class="card shadow-sm mt-4 bg-light">
                <div class="card-body text-center">
                    <h6 class="mb-3">Need Help?</h6>
                    <p class="small text-muted mb-3">
                        If you experience any issues with online payment, please contact our office.
                    </p>
                    <div class="d-grid gap-2">
                        <a href="tel:+60379723663" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-phone me-1"></i> Call Office
                        </a>
                        <a href="mailto:info@arenamatriks.com" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-envelope me-1"></i> Email Support
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
