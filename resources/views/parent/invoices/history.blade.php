@extends('layouts.app')

@section('title', 'Invoice History')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Invoice History</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('parent.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('parent.invoices.index') }}">Invoices</a></li>
                    <li class="breadcrumb-item active">History</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('parent.invoices.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Invoices
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Monthly Summary -->
        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Monthly Summary</h5>
                </div>
                <div class="card-body p-0">
                    @if(isset($monthlyTotals) && $monthlyTotals->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($monthlyTotals as $monthly)
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fas fa-calendar-alt text-muted me-2"></i>
                                        @if(isset($monthly->year) && isset($monthly->month))
                                            {{ \Carbon\Carbon::createFromDate($monthly->year, $monthly->month, 1)->format('F Y') }}
                                        @elseif(isset($monthly['month']))
                                            {{ $monthly['month'] }}
                                        @else
                                            N/A
                                        @endif
                                    </div>
                                    <span class="badge bg-success rounded-pill fs-6">
                                        RM {{ number_format($monthly->total ?? $monthly['total'] ?? 0, 2) }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">No payment history available.</p>
                        </div>
                    @endif
                </div>
                @if(isset($monthlyTotals) && $monthlyTotals->count() > 0)
                    <div class="card-footer bg-light">
                        <div class="d-flex justify-content-between">
                            <strong>Total Paid:</strong>
                            <strong class="text-success">
                                RM {{ number_format($monthlyTotals->sum('total') ?? $monthlyTotals->sum(fn($m) => $m['total'] ?? 0), 2) }}
                            </strong>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Quick Stats -->
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Overview</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted d-block">Total Invoices Paid</small>
                        <strong>{{ $invoices->total() ?? $invoices->count() }} invoices</strong>
                    </div>
                    <div>
                        <small class="text-muted d-block">Total Amount Paid</small>
                        <strong class="text-success fs-5">
                            RM {{ number_format($invoices->sum('paid_amount'), 2) }}
                        </strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Invoice History Table -->
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-history me-2"></i>Paid Invoices</h5>
                    <span class="badge bg-primary">{{ $invoices->total() ?? $invoices->count() }} records</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Invoice #</th>
                                    <th>Child</th>
                                    <th>Package</th>
                                    <th>Period</th>
                                    <th class="text-end">Amount</th>
                                    <th>Paid Date</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($invoices as $invoice)
                                    <tr>
                                        <td>
                                            <a href="{{ route('parent.invoices.show', $invoice) }}" class="fw-bold text-decoration-none">
                                                {{ $invoice->invoice_number }}
                                            </a>
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
                                            @if($invoice->billing_period_start && $invoice->billing_period_end)
                                                {{ $invoice->billing_period_start->format('M Y') }}
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <span class="fw-bold text-success">
                                                RM {{ number_format($invoice->total_amount, 2) }}
                                            </span>
                                        </td>
                                        <td>
                                            {{ $invoice->updated_at->format('d M Y') }}
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('parent.invoices.show', $invoice) }}"
                                                   class="btn btn-outline-primary" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if($invoice->payments && $invoice->payments->count() > 0)
                                                    @php $lastPayment = $invoice->payments->last(); @endphp
                                                    @if($lastPayment && Route::has('parent.payments.receipt'))
                                                        <a href="{{ route('parent.payments.receipt', $lastPayment) }}"
                                                           class="btn btn-outline-success" title="Receipt" target="_blank">
                                                            <i class="fas fa-receipt"></i>
                                                        </a>
                                                    @endif
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                            <p class="text-muted mb-0">No paid invoices found.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($invoices->hasPages())
                    <div class="card-footer bg-white">
                        {{ $invoices->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
