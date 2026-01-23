@extends('layouts.app')

@section('title', 'Student Arrears Details')
@section('page-title', 'Student Arrears Details')

@section('content')
<div class="container-fluid">
    <!-- Back Button -->
    <div class="mb-3">
        <a href="{{ route('staff.arrears.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Arrears
        </a>
    </div>

    @php
        $student = $arrearsData['student'] ?? null;
        $summary = $arrearsData['summary'] ?? [];
        $unpaidInvoices = $arrearsData['unpaid_invoices'] ?? collect();
        $installmentArrears = $arrearsData['installment_arrears'] ?? collect();
    @endphp

    <!-- Student Info Card -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex align-items-center">
                        <div class="avatar-large bg-primary text-white me-3">
                            {{ $student && $student->user ? strtoupper(substr($student->user->name, 0, 1)) : '?' }}
                        </div>
                        <div>
                            <h4 class="mb-1">{{ $student && $student->user ? $student->user->name : 'Unknown Student' }}</h4>
                            <p class="text-muted mb-0">
                                @if($student)
                                <span class="me-3"><i class="fas fa-id-card me-1"></i>{{ $student->student_id ?? 'N/A' }}</span>
                                @if($student->user && $student->user->phone)
                                <span class="me-3"><i class="fas fa-phone me-1"></i>{{ $student->user->phone }}</span>
                                @endif
                                @if($student->user && $student->user->email)
                                <span><i class="fas fa-envelope me-1"></i>{{ $student->user->email }}</span>
                                @endif
                                @endif
                            </p>
                            @if($student && $student->parent && $student->parent->user)
                            <p class="text-muted mb-0 mt-1">
                                <i class="fas fa-user-friends me-1"></i>
                                <strong>Parent:</strong> {{ $student->parent->user->name }}
                                @if($student->parent->user->phone)
                                <span class="ms-2"><i class="fas fa-phone me-1"></i>{{ $student->parent->user->phone }}</span>
                                @endif
                            </p>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <div class="bg-danger text-white rounded p-3 d-inline-block">
                        <small class="d-block">Total Arrears</small>
                        <h3 class="mb-0">RM {{ number_format($summary['total_arrears'] ?? 0, 2) }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Summary Statistics -->
        <div class="col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-chart-bar me-2 text-primary"></i>Arrears Summary</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span>Total Arrears:</span>
                            <strong class="text-danger">RM {{ number_format($summary['total_arrears'] ?? 0, 2) }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span>Unpaid Invoices:</span>
                            <strong>{{ $summary['invoice_count'] ?? 0 }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span>Oldest Due Date:</span>
                            <strong>{{ isset($summary['oldest_due']) && $summary['oldest_due'] ? $summary['oldest_due']->format('d M Y') : '-' }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span>Max Days Overdue:</span>
                            <strong class="text-danger">{{ $summary['max_days_overdue'] ?? 0 }} days</strong>
                        </li>
                    </ul>

                    @if(isset($summary['by_status']))
                    <hr>
                    <h6 class="mb-3"><i class="fas fa-info-circle me-1"></i>By Status</h6>
                    <div class="row g-2">
                        <div class="col-4 text-center">
                            <div class="border rounded p-2">
                                <small class="text-muted d-block">Pending</small>
                                <strong class="text-warning">RM {{ number_format($summary['by_status']['pending'] ?? 0, 2) }}</strong>
                            </div>
                        </div>
                        <div class="col-4 text-center">
                            <div class="border rounded p-2">
                                <small class="text-muted d-block">Partial</small>
                                <strong class="text-info">RM {{ number_format($summary['by_status']['partial'] ?? 0, 2) }}</strong>
                            </div>
                        </div>
                        <div class="col-4 text-center">
                            <div class="border rounded p-2">
                                <small class="text-muted d-block">Overdue</small>
                                <strong class="text-danger">RM {{ number_format($summary['by_status']['overdue'] ?? 0, 2) }}</strong>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Unpaid Invoices -->
        <div class="col-lg-8 mb-4">
            <div class="card h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-file-invoice-dollar me-2 text-danger"></i>Unpaid Invoices</h5>
                    <span class="badge bg-danger">{{ $unpaidInvoices->count() }} invoices</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Invoice #</th>
                                    <th>Description</th>
                                    <th class="text-end">Amount</th>
                                    <th class="text-end">Paid</th>
                                    <th class="text-end">Balance</th>
                                    <th class="text-center">Due Date</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($unpaidInvoices as $invoice)
                                <tr>
                                    <td>
                                        <strong>{{ $invoice->invoice_number ?? 'N/A' }}</strong>
                                    </td>
                                    <td>
                                        @if($invoice->enrollment && $invoice->enrollment->package)
                                        {{ Str::limit($invoice->enrollment->package->name, 25) }}
                                        @else
                                        {{ $invoice->description ?? 'N/A' }}
                                        @endif
                                    </td>
                                    <td class="text-end">RM {{ number_format($invoice->total_amount, 2) }}</td>
                                    <td class="text-end text-success">RM {{ number_format($invoice->paid_amount, 2) }}</td>
                                    <td class="text-end text-danger">
                                        <strong>RM {{ number_format($invoice->balance ?? ($invoice->total_amount - $invoice->paid_amount), 2) }}</strong>
                                    </td>
                                    <td class="text-center">
                                        @if($invoice->due_date)
                                        <span class="{{ $invoice->due_date->isPast() ? 'text-danger' : '' }}">
                                            {{ $invoice->due_date->format('d M Y') }}
                                        </span>
                                        @if($invoice->due_date->isPast())
                                        <br><small class="text-danger">({{ $invoice->due_date->diffInDays(now()) }} days ago)</small>
                                        @endif
                                        @else
                                        -
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $statusColors = [
                                                'pending' => 'warning',
                                                'partial' => 'info',
                                                'overdue' => 'danger',
                                            ];
                                        @endphp
                                        <span class="badge bg-{{ $statusColors[$invoice->status] ?? 'secondary' }}">
                                            {{ ucfirst($invoice->status) }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="fas fa-check-circle fa-2x mb-2 text-success"></i>
                                            <p class="mb-0">No unpaid invoices.</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                            @if($unpaidInvoices->count() > 0)
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="4" class="text-end">Total Outstanding:</th>
                                    <th class="text-end text-danger">RM {{ number_format($summary['total_arrears'] ?? 0, 2) }}</th>
                                    <th colspan="2"></th>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Payment History -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-history me-2 text-success"></i>Recent Payment History</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Invoice</th>
                                    <th class="text-end">Amount</th>
                                    <th>Method</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($paymentHistory as $payment)
                                <tr>
                                    <td>{{ $payment->payment_date ? $payment->payment_date->format('d M Y') : '-' }}</td>
                                    <td>{{ $payment->invoice->invoice_number ?? 'N/A' }}</td>
                                    <td class="text-end text-success">
                                        <strong>RM {{ number_format($payment->amount, 2) }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ ucfirst($payment->payment_method ?? 'Unknown') }}</span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">
                                        <i class="fas fa-inbox fa-2x mb-2"></i>
                                        <p class="mb-0">No payment history found.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reminder History -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-bell me-2 text-warning"></i>Reminder History</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Invoice</th>
                                    <th>Type</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($reminderHistory as $reminder)
                                <tr>
                                    <td>{{ $reminder->scheduled_date ? $reminder->scheduled_date->format('d M Y') : '-' }}</td>
                                    <td>{{ $reminder->invoice->invoice_number ?? 'N/A' }}</td>
                                    <td>{{ ucfirst($reminder->reminder_type ?? 'Standard') }}</td>
                                    <td class="text-center">
                                        @php
                                            $reminderStatusColors = [
                                                'pending' => 'warning',
                                                'sent' => 'success',
                                                'failed' => 'danger',
                                                'cancelled' => 'secondary',
                                            ];
                                        @endphp
                                        <span class="badge bg-{{ $reminderStatusColors[$reminder->status] ?? 'secondary' }}">
                                            {{ ucfirst($reminder->status ?? 'Unknown') }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">
                                        <i class="fas fa-inbox fa-2x mb-2"></i>
                                        <p class="mb-0">No reminder history found.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Installment Arrears (if any) -->
    @if($installmentArrears->count() > 0)
    <div class="card">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-calendar-check me-2 text-info"></i>Installment Arrears</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Invoice</th>
                            <th>Installment #</th>
                            <th class="text-end">Amount Due</th>
                            <th class="text-center">Due Date</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($installmentArrears as $installment)
                        <tr>
                            <td>{{ $installment->invoice->invoice_number ?? 'N/A' }}</td>
                            <td>Installment #{{ $installment->installment_number ?? '-' }}</td>
                            <td class="text-end text-danger">
                                <strong>RM {{ number_format($installment->amount - ($installment->paid_amount ?? 0), 2) }}</strong>
                            </td>
                            <td class="text-center">
                                @if($installment->due_date)
                                <span class="{{ $installment->due_date->isPast() ? 'text-danger' : '' }}">
                                    {{ $installment->due_date->format('d M Y') }}
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
                                        'overdue' => 'danger',
                                        'paid' => 'success',
                                    ];
                                @endphp
                                <span class="badge bg-{{ $instStatusColors[$installment->status] ?? 'secondary' }}">
                                    {{ ucfirst($installment->status ?? 'Unknown') }}
                                </span>
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

<style>
.avatar-large {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 24px;
}
</style>
@endsection
