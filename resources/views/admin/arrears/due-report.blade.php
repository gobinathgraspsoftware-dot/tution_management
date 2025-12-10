@extends('layouts.app')

@section('title', 'Due Report')
@section('page-title', 'Upcoming Dues Report')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.arrears.index') }}">Arrears</a></li>
            <li class="breadcrumb-item active">Due Report</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="fas fa-calendar-check me-2"></i> Upcoming Dues Report</h4>
            <p class="text-muted mb-0">Payments due in the next {{ $daysAhead }} days</p>
        </div>
        <div>
            <form method="GET" action="{{ route('admin.arrears.due-report') }}" class="d-inline">
                <div class="input-group">
                    <select name="days" class="form-select" onchange="this.form.submit()">
                        <option value="7" {{ $daysAhead == 7 ? 'selected' : '' }}>Next 7 days</option>
                        <option value="14" {{ $daysAhead == 14 ? 'selected' : '' }}>Next 14 days</option>
                        <option value="30" {{ $daysAhead == 30 ? 'selected' : '' }}>Next 30 days</option>
                        <option value="60" {{ $daysAhead == 60 ? 'selected' : '' }}>Next 60 days</option>
                    </select>
                </div>
            </form>
            <a href="{{ route('admin.arrears.index') }}" class="btn btn-outline-secondary ms-2">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-money-bill-wave fa-2x opacity-75"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0">RM {{ number_format($dueReport['summary']['total_due'], 2) }}</h3>
                            <small>Total Due</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-info text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-file-invoice fa-2x opacity-75"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0">{{ $dueReport['summary']['invoice_count'] }}</h3>
                            <small>Invoices Due</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-warning text-dark">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-calendar-alt fa-2x opacity-75"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0">{{ $dueReport['summary']['installment_count'] }}</h3>
                            <small>Installments Due</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-users fa-2x opacity-75"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0">{{ $dueReport['summary']['student_count'] }}</h3>
                            <small>Students</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Weekly Breakdown -->
    <div class="row mb-4">
        <!-- This Week -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="fas fa-exclamation-circle me-2"></i> This Week</h5>
                </div>
                <div class="card-body">
                    @php
                        $thisWeekInvoices = $dueReport['by_week']['this_week']['invoices'] ?? collect();
                        $thisWeekInstallments = $dueReport['by_week']['this_week']['installments'] ?? collect();
                        $thisWeekTotal = $thisWeekInvoices->sum('balance') + $thisWeekInstallments->sum('balance');
                    @endphp
                    <h3 class="text-danger">RM {{ number_format($thisWeekTotal, 2) }}</h3>
                    <p class="text-muted mb-0">
                        {{ $thisWeekInvoices->count() }} invoices, {{ $thisWeekInstallments->count() }} installments
                    </p>
                </div>
            </div>
        </div>

        <!-- Next Week -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-clock me-2"></i> Next Week</h5>
                </div>
                <div class="card-body">
                    @php
                        $nextWeekInvoices = $dueReport['by_week']['next_week']['invoices'] ?? collect();
                        $nextWeekInstallments = $dueReport['by_week']['next_week']['installments'] ?? collect();
                        $nextWeekTotal = $nextWeekInvoices->sum('balance') + $nextWeekInstallments->sum('balance');
                    @endphp
                    <h3 class="text-warning">RM {{ number_format($nextWeekTotal, 2) }}</h3>
                    <p class="text-muted mb-0">
                        {{ $nextWeekInvoices->count() }} invoices, {{ $nextWeekInstallments->count() }} installments
                    </p>
                </div>
            </div>
        </div>

        <!-- Later -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-calendar me-2"></i> Later</h5>
                </div>
                <div class="card-body">
                    @php
                        $laterInvoices = $dueReport['by_week']['later']['invoices'] ?? collect();
                        $laterInstallments = $dueReport['by_week']['later']['installments'] ?? collect();
                        $laterTotal = $laterInvoices->sum('balance') + $laterInstallments->sum('balance');
                    @endphp
                    <h3 class="text-info">RM {{ number_format($laterTotal, 2) }}</h3>
                    <p class="text-muted mb-0">
                        {{ $laterInvoices->count() }} invoices, {{ $laterInstallments->count() }} installments
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Upcoming Invoices -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-file-invoice-dollar me-2"></i> Upcoming Invoice Dues</h5>
            <span class="badge bg-primary">{{ $dueReport['invoices']->count() }} invoices</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Invoice #</th>
                            <th>Student</th>
                            <th>Package</th>
                            <th>Due Date</th>
                            <th>Balance</th>
                            <th>Days Until Due</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dueReport['invoices'] as $invoice)
                            @php
                                $daysUntilDue = now()->startOfDay()->diffInDays($invoice->due_date, false);
                            @endphp
                            <tr>
                                <td>
                                    <a href="{{ route('admin.invoices.show', $invoice) }}">
                                        {{ $invoice->invoice_number }}
                                    </a>
                                </td>
                                <td>
                                    <strong>{{ $invoice->student?->user?->name ?? 'N/A' }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $invoice->student?->parent?->user?->phone ?? '' }}</small>
                                </td>
                                <td>{{ $invoice->enrollment?->package?->name ?? 'N/A' }}</td>
                                <td>{{ $invoice->due_date?->format('d M Y') ?? 'N/A' }}</td>
                                <td><strong class="text-danger">RM {{ number_format($invoice->balance, 2) }}</strong></td>
                                <td>
                                    @if($daysUntilDue <= 0)
                                        <span class="badge bg-danger">Due Today</span>
                                    @elseif($daysUntilDue <= 3)
                                        <span class="badge bg-warning text-dark">{{ $daysUntilDue }} days</span>
                                    @elseif($daysUntilDue <= 7)
                                        <span class="badge bg-info">{{ $daysUntilDue }} days</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $daysUntilDue }} days</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.invoices.show', $invoice) }}" class="btn btn-outline-primary" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.payments.create', ['invoice_id' => $invoice->id]) }}" class="btn btn-outline-success" title="Pay">
                                            <i class="fas fa-money-bill"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    No upcoming invoices due in this period
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Upcoming Installments -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i> Upcoming Installment Dues</h5>
            <span class="badge bg-warning text-dark">{{ $dueReport['installments']->count() }} installments</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Invoice #</th>
                            <th>Student</th>
                            <th>Installment</th>
                            <th>Due Date</th>
                            <th>Amount</th>
                            <th>Balance</th>
                            <th>Days Until Due</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dueReport['installments'] as $installment)
                            @php
                                $daysUntilDue = now()->startOfDay()->diffInDays($installment->due_date, false);
                            @endphp
                            <tr>
                                <td>{{ $installment->invoice?->invoice_number ?? 'N/A' }}</td>
                                <td>
                                    <strong>{{ $installment->invoice?->student?->user?->name ?? 'N/A' }}</strong>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">#{{ $installment->installment_number }}</span>
                                </td>
                                <td>{{ $installment->due_date?->format('d M Y') ?? 'N/A' }}</td>
                                <td>RM {{ number_format($installment->amount, 2) }}</td>
                                <td><strong class="text-danger">RM {{ number_format($installment->amount - $installment->paid_amount, 2) }}</strong></td>
                                <td>
                                    @if($daysUntilDue <= 0)
                                        <span class="badge bg-danger">Due Today</span>
                                    @elseif($daysUntilDue <= 3)
                                        <span class="badge bg-warning text-dark">{{ $daysUntilDue }} days</span>
                                    @elseif($daysUntilDue <= 7)
                                        <span class="badge bg-info">{{ $daysUntilDue }} days</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $daysUntilDue }} days</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    No upcoming installments due in this period
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
