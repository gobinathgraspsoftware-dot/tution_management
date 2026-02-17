@extends('layouts.app')

@section('title', 'Invoice History')
@section('page-title', 'Invoice History')

@section('content')
<div class="page-header">
    <h1>
        <i class="fas fa-history me-2"></i> Invoice History
    </h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('student.invoices.index') }}">Invoices</a></li>
            <li class="breadcrumb-item active">History</li>
        </ol>
    </nav>
</div>

{{-- Summary Card --}}
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card border-0 bg-success text-white">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-white bg-opacity-25 me-3" style="width:50px;height:50px;border-radius:10px;display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-check-circle fa-lg text-white"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 text-white-50">Total Paid Invoices</h6>
                        <h3 class="mb-0">{{ $invoices->total() }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="card-body text-white">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-white bg-opacity-25 me-3" style="width:50px;height:50px;border-radius:10px;display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-money-bill-wave fa-lg text-white"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 text-white-50">Total Amount Paid</h6>
                        <h3 class="mb-0">RM {{ number_format($invoices->sum('paid_amount'), 2) }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 bg-info text-white">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-white bg-opacity-25 me-3" style="width:50px;height:50px;border-radius:10px;display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-file-invoice fa-lg text-white"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 text-white-50">This Page</h6>
                        <h3 class="mb-0">{{ $invoices->count() }} Invoice(s)</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Paid Invoices Table --}}
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-check-double me-2 text-success"></i>Paid Invoices</h5>
        <a href="{{ route('student.invoices.index') }}" class="btn btn-sm btn-outline-primary">
            <i class="fas fa-arrow-left me-1"></i> Back to All Invoices
        </a>
    </div>
    <div class="card-body">
        @if($invoices->isEmpty())
            <div class="text-center py-5">
                <i class="fas fa-file-invoice fa-4x text-muted mb-3"></i>
                <h5 class="text-muted">No Paid Invoices Found</h5>
                <p class="text-muted">Your paid invoice history will appear here once payments are completed.</p>
                <a href="{{ route('student.invoices.index') }}" class="btn btn-primary">
                    <i class="fas fa-file-invoice me-1"></i> View All Invoices
                </a>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Invoice #</th>
                            <th>Package / Description</th>
                            <th>Invoice Date</th>
                            <th class="text-end">Total Amount</th>
                            <th class="text-end">Paid Amount</th>
                            <th>Paid Date</th>
                            <th>Payment Method</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoices as $invoice)
                        <tr>
                            <td>
                                <span class="fw-bold text-primary">{{ $invoice->invoice_number }}</span>
                            </td>
                            <td>
                                @if($invoice->enrollment && $invoice->enrollment->package)
                                    <span>{{ $invoice->enrollment->package->name }}</span>
                                @elseif($invoice->description)
                                    <span>{{ Str::limit($invoice->description, 40) }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                {{ $invoice->created_at->format('d M Y') }}
                            </td>
                            <td class="text-end fw-bold">
                                RM {{ number_format($invoice->total_amount, 2) }}
                            </td>
                            <td class="text-end text-success fw-bold">
                                RM {{ number_format($invoice->paid_amount, 2) }}
                            </td>
                            <td>
                                {{ $invoice->updated_at->format('d M Y') }}
                            </td>
                            <td>
                                @if($invoice->payments && $invoice->payments->isNotEmpty())
                                    @php
                                        $lastPayment = $invoice->payments->sortByDesc('created_at')->first();
                                    @endphp
                                    <span class="badge bg-light text-dark">
                                        <i class="fas fa-{{ $lastPayment->payment_method === 'online' ? 'globe' : ($lastPayment->payment_method === 'cash' ? 'money-bill' : 'qrcode') }} me-1"></i>
                                        {{ ucfirst($lastPayment->payment_method ?? 'N/A') }}
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('student.invoices.show', $invoice->id) }}" class="btn btn-sm btn-outline-primary" title="View Invoice">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if(Route::has('student.payments.receipt') && $invoice->payments && $invoice->payments->isNotEmpty())
                                    @php $lastPayment = $invoice->payments->sortByDesc('created_at')->first(); @endphp
                                    <a href="{{ route('student.payments.receipt', $lastPayment->id) }}" class="btn btn-sm btn-outline-success" title="View Receipt" target="_blank">
                                        <i class="fas fa-receipt"></i>
                                    </a>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted small">
                    Showing {{ $invoices->firstItem() }} to {{ $invoices->lastItem() }} of {{ $invoices->total() }} paid invoices
                </div>
                {{ $invoices->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
