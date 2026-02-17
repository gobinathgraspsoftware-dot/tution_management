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
                    <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('student.payments.index') }}">Payments</a></li>
                    <li class="breadcrumb-item active">Outstanding</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('student.payments.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Payments
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
                        <i class="fas fa-exclamation-triangle fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-warning text-dark">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 opacity-75">Overdue Amount</h6>
                            <h3 class="mb-0">RM {{ number_format($summary['total_overdue'], 2) }}</h3>
                        </div>
                        <i class="fas fa-clock fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 text-white-50">Total Invoices</h6>
                            <h3 class="mb-0">{{ $unpaidInvoices->count() }}</h3>
                        </div>
                        <i class="fas fa-file-invoice fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Next Due Alert -->
    @if($summary['next_due'])
        <div class="alert {{ $summary['next_due']->status === 'overdue' ? 'alert-danger' : 'alert-warning' }} d-flex align-items-center mb-4">
            <i class="fas {{ $summary['next_due']->status === 'overdue' ? 'fa-exclamation-circle' : 'fa-info-circle' }} fa-2x me-3"></i>
            <div class="flex-grow-1">
                <h5 class="mb-1">
                    @if($summary['next_due']->status === 'overdue')
                        Overdue Payment!
                    @else
                        Next Payment Due
                    @endif
                </h5>
                <p class="mb-0">
                    Invoice <strong>{{ $summary['next_due']->invoice_number }}</strong> 
                    {{ $summary['next_due']->status === 'overdue' ? 'was' : 'is' }} due on 
                    <strong>{{ $summary['next_due']->due_date->format('d M Y') }}</strong>
                    - Amount: <strong>RM {{ number_format($summary['next_due']->balance, 2) }}</strong>
                </p>
            </div>
            @if(Route::has('student.invoices.show'))
                <a href="{{ route('student.invoices.show', $summary['next_due']) }}" class="btn btn-light btn-sm">
                    <i class="fas fa-eye me-1"></i> View Invoice
                </a>
            @endif
        </div>
    @endif

    <!-- Outstanding Invoices Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-file-invoice-dollar me-2"></i>Outstanding Invoices</h5>
            <span class="badge bg-danger">{{ $unpaidInvoices->count() }} unpaid</span>
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
                                <th class="text-end">Total Amount</th>
                                <th class="text-end">Paid Amount</th>
                                <th class="text-end">Balance</th>
                                <th>Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($unpaidInvoices as $invoice)
                                <tr class="{{ $invoice->status === 'overdue' ? 'table-danger' : '' }}">
                                    <td>
                                        <strong>{{ $invoice->invoice_number }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $invoice->created_at->format('d M Y') }}</small>
                                    </td>
                                    <td>
                                        @if($invoice->enrollment && $invoice->enrollment->package)
                                            {{ $invoice->enrollment->package->name }}
                                            <br>
                                            <small class="text-muted">{{ $invoice->type_label ?? ucfirst($invoice->type) }}</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $invoice->due_date->format('d M Y') }}
                                        <br>
                                        @if($invoice->due_date->isPast())
                                            <small class="text-danger">
                                                <i class="fas fa-exclamation-circle"></i> 
                                                {{ $invoice->due_date->diffForHumans() }}
                                            </small>
                                        @else
                                            <small class="text-muted">
                                                {{ $invoice->due_date->diffForHumans() }}
                                            </small>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <strong>RM {{ number_format($invoice->total_amount, 2) }}</strong>
                                    </td>
                                    <td class="text-end text-success">
                                        RM {{ number_format($invoice->paid_amount, 2) }}
                                    </td>
                                    <td class="text-end">
                                        <strong class="text-danger">RM {{ number_format($invoice->balance, 2) }}</strong>
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
                                        <div class="btn-group btn-group-sm">
                                            @if(Route::has('student.invoices.show'))
                                                <a href="{{ route('student.invoices.show', $invoice) }}" 
                                                   class="btn btn-outline-primary" 
                                                   title="View Invoice">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            @endif
                                            @if(Route::has('student.payments.pay-online'))
                                                <a href="{{ route('student.payments.pay-online', $invoice) }}" 
                                                   class="btn btn-outline-success" 
                                                   title="Pay Online">
                                                    <i class="fas fa-credit-card"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="5" class="text-end"><strong>Total Outstanding:</strong></td>
                                <td class="text-end">
                                    <strong class="text-danger fs-5">
                                        RM {{ number_format($unpaidInvoices->sum('balance'), 2) }}
                                    </strong>
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
                    <p class="text-muted mb-0">You have no outstanding payments at this time.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Payment Information Box -->
    @if($unpaidInvoices->count() > 0)
        <div class="card shadow-sm mt-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Payment Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="mb-3">Payment Methods Available:</h6>
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <i class="fas fa-globe text-primary me-2"></i> 
                                <strong>Online Payment</strong> - Pay securely online
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-university text-success me-2"></i> 
                                <strong>Bank Transfer</strong> - Transfer to our account
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-money-bill-wave text-info me-2"></i> 
                                <strong>Cash</strong> - Pay at the center
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-qrcode text-warning me-2"></i> 
                                <strong>QR Payment</strong> - Scan and pay
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6 class="mb-3">Important Notes:</h6>
                        <div class="alert alert-info mb-0">
                            <ul class="mb-0 ps-3">
                                <li>Please make payment before the due date to avoid late fees.</li>
                                <li>For bank transfers, please use your invoice number as reference.</li>
                                <li>Receipts will be issued upon payment confirmation.</li>
                                <li>Contact the office if you need to discuss payment arrangements.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            @if($summary['total_overdue'] > 0)
                <div class="card-footer bg-danger text-white">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Attention:</strong> You have overdue payments totaling 
                    <strong>RM {{ number_format($summary['total_overdue'], 2) }}</strong>. 
                    Please settle them as soon as possible to avoid service interruption.
                </div>
            @endif
        </div>
    @endif

    <!-- Quick Actions -->
    @if($unpaidInvoices->count() > 0)
        <div class="row mt-4">
            <div class="col-12">
                <div class="card shadow-sm bg-light">
                    <div class="card-body text-center">
                        <h5 class="mb-3">Need Help?</h5>
                        <p class="text-muted mb-3">
                            If you have questions about your invoices or need to arrange a payment plan, 
                            please contact our office.
                        </p>
                        <div class="btn-group">
                            <a href="tel:+60379723663" class="btn btn-primary">
                                <i class="fas fa-phone me-1"></i> Call Office
                            </a>
                            <a href="mailto:govind@graspsoftwaresolutions.com" class="btn btn-outline-primary">
                                <i class="fas fa-envelope me-1"></i> Email Support
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Highlight overdue rows on hover
    $('tr.table-danger').hover(
        function() {
            $(this).addClass('shadow-sm');
        },
        function() {
            $(this).removeClass('shadow-sm');
        }
    );
});
</script>
@endpush
@endsection
