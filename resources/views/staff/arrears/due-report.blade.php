@extends('layouts.app')

@section('title', 'Due Report')
@section('page-title', 'Due Report - Upcoming Payments')

@section('content')
<div class="container-fluid">
    <!-- Back Button -->
    <div class="mb-3">
        <a href="{{ route('staff.arrears.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Arrears Dashboard
        </a>
    </div>

    @php
        $upcomingInvoices = $dueReport['invoices'] ?? collect();
        $upcomingInstallments = $dueReport['installments'] ?? collect();
        $summary = $dueReport['summary'] ?? [];
    @endphp

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 opacity-75">Total Due ({{ $daysAhead }} Days)</p>
                            <h3 class="mb-0">RM {{ number_format($summary['total_due'] ?? 0, 2) }}</h3>
                        </div>
                        <i class="fas fa-calendar-alt fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 opacity-75">Invoices Due</p>
                            <h3 class="mb-0">{{ $upcomingInvoices->count() }}</h3>
                        </div>
                        <i class="fas fa-file-invoice fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 opacity-75">Installments Due</p>
                            <h3 class="mb-0">{{ $upcomingInstallments->count() }}</h3>
                        </div>
                        <i class="fas fa-calendar-check fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 opacity-75">Students Affected</p>
                            <h3 class="mb-0">{{ $summary['students_count'] ?? 0 }}</h3>
                        </div>
                        <i class="fas fa-users fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter -->
    <div class="card mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-filter me-2 text-primary"></i>Date Range</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('staff.arrears.due-report') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Days Ahead</label>
                    <select class="form-select" name="days">
                        <option value="7" {{ $daysAhead == 7 ? 'selected' : '' }}>Next 7 Days</option>
                        <option value="14" {{ $daysAhead == 14 ? 'selected' : '' }}>Next 14 Days</option>
                        <option value="30" {{ $daysAhead == 30 ? 'selected' : '' }}>Next 30 Days</option>
                        <option value="60" {{ $daysAhead == 60 ? 'selected' : '' }}>Next 60 Days</option>
                        <option value="90" {{ $daysAhead == 90 ? 'selected' : '' }}>Next 90 Days</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-sync me-1"></i> Update
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Upcoming Invoices -->
    <div class="card mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-file-invoice-dollar me-2 text-warning"></i>Upcoming Invoice Payments</h5>
            <span class="badge bg-warning text-dark">{{ $upcomingInvoices->count() }} invoices</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Invoice #</th>
                            <th>Student</th>
                            <th>Package/Description</th>
                            <th class="text-end">Amount Due</th>
                            <th class="text-center">Due Date</th>
                            <th class="text-center">Days Until Due</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($upcomingInvoices as $invoice)
                        <tr>
                            <td><strong>{{ $invoice->invoice_number ?? 'N/A' }}</strong></td>
                            <td>
                                @if($invoice->student && $invoice->student->user)
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle bg-primary text-white me-2">
                                        {{ strtoupper(substr($invoice->student->user->name, 0, 1)) }}
                                    </div>
                                    <span>{{ $invoice->student->user->name }}</span>
                                </div>
                                @else
                                <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if($invoice->enrollment && $invoice->enrollment->package)
                                {{ Str::limit($invoice->enrollment->package->name, 30) }}
                                @else
                                {{ $invoice->description ?? 'N/A' }}
                                @endif
                            </td>
                            <td class="text-end">
                                <strong class="text-danger">RM {{ number_format($invoice->balance ?? ($invoice->total_amount - $invoice->paid_amount), 2) }}</strong>
                            </td>
                            <td class="text-center">
                                {{ $invoice->due_date ? $invoice->due_date->format('d M Y') : '-' }}
                            </td>
                            <td class="text-center">
                                @if($invoice->due_date)
                                @php
                                    $daysUntil = now()->startOfDay()->diffInDays($invoice->due_date->startOfDay(), false);
                                    $urgencyClass = $daysUntil <= 3 ? 'danger' : ($daysUntil <= 7 ? 'warning' : 'info');
                                @endphp
                                <span class="badge bg-{{ $urgencyClass }}">
                                    {{ $daysUntil }} days
                                </span>
                                @else
                                -
                                @endif
                            </td>
                            <td class="text-center">
                                @php
                                    $statusColors = [
                                        'pending' => 'warning',
                                        'partial' => 'info',
                                    ];
                                @endphp
                                <span class="badge bg-{{ $statusColors[$invoice->status] ?? 'secondary' }}">
                                    {{ ucfirst($invoice->status ?? 'Unknown') }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-check-circle fa-2x mb-2 text-success"></i>
                                    <p class="mb-0">No upcoming invoice payments in the next {{ $daysAhead }} days.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Upcoming Installments -->
    <div class="card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-calendar-check me-2 text-info"></i>Upcoming Installment Payments</h5>
            <span class="badge bg-info">{{ $upcomingInstallments->count() }} installments</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Invoice #</th>
                            <th>Student</th>
                            <th>Installment</th>
                            <th class="text-end">Amount Due</th>
                            <th class="text-center">Due Date</th>
                            <th class="text-center">Days Until Due</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($upcomingInstallments as $installment)
                        <tr>
                            <td><strong>{{ $installment->invoice->invoice_number ?? 'N/A' }}</strong></td>
                            <td>
                                @if($installment->invoice && $installment->invoice->student && $installment->invoice->student->user)
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle bg-info text-white me-2">
                                        {{ strtoupper(substr($installment->invoice->student->user->name, 0, 1)) }}
                                    </div>
                                    <span>{{ $installment->invoice->student->user->name }}</span>
                                </div>
                                @else
                                <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                Installment #{{ $installment->installment_number ?? '-' }}
                            </td>
                            <td class="text-end">
                                <strong class="text-danger">RM {{ number_format($installment->amount - ($installment->paid_amount ?? 0), 2) }}</strong>
                            </td>
                            <td class="text-center">
                                {{ $installment->due_date ? $installment->due_date->format('d M Y') : '-' }}
                            </td>
                            <td class="text-center">
                                @if($installment->due_date)
                                @php
                                    $daysUntil = now()->startOfDay()->diffInDays($installment->due_date->startOfDay(), false);
                                    $urgencyClass = $daysUntil <= 3 ? 'danger' : ($daysUntil <= 7 ? 'warning' : 'info');
                                @endphp
                                <span class="badge bg-{{ $urgencyClass }}">
                                    {{ $daysUntil }} days
                                </span>
                                @else
                                -
                                @endif
                            </td>
                            <td class="text-center">
                                @php
                                    $instStatusColors = [
                                        'pending' => 'warning',
                                        'partial' => 'info',
                                    ];
                                @endphp
                                <span class="badge bg-{{ $instStatusColors[$installment->status] ?? 'secondary' }}">
                                    {{ ucfirst($installment->status ?? 'Unknown') }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-check-circle fa-2x mb-2 text-success"></i>
                                    <p class="mb-0">No upcoming installment payments in the next {{ $daysAhead }} days.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Legend -->
    <div class="card mt-4">
        <div class="card-body">
            <h6 class="mb-3"><i class="fas fa-info-circle me-1"></i>Color Legend</h6>
            <div class="d-flex flex-wrap gap-3">
                <span><span class="badge bg-danger me-1">●</span> Due within 3 days</span>
                <span><span class="badge bg-warning text-dark me-1">●</span> Due within 7 days</span>
                <span><span class="badge bg-info me-1">●</span> Due later</span>
            </div>
        </div>
    </div>
</div>

<style>
.avatar-circle {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 14px;
    flex-shrink: 0;
}
</style>
@endsection
