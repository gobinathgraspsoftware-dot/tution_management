@extends('layouts.app')

@section('title', 'Invoice ' . $invoice->invoice_number)
@section('page-title', 'Invoice Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Invoice Details -->
        <div class="col-lg-8">
            <!-- Invoice Header Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start flex-wrap">
                        <div>
                            <h4 class="mb-1">{{ $invoice->invoice_number }}</h4>
                            <p class="text-muted mb-0">
                                Created {{ $invoice->created_at->format('d M Y, h:i A') }}
                            </p>
                        </div>
                        <div class="text-end">
                            @php
                                $statusColors = [
                                    'draft' => 'secondary',
                                    'pending' => 'warning',
                                    'partial' => 'info',
                                    'paid' => 'success',
                                    'overdue' => 'danger',
                                    'cancelled' => 'dark',
                                    'refunded' => 'secondary',
                                ];
                            @endphp
                            <span class="badge bg-{{ $statusColors[$invoice->status] ?? 'secondary' }} fs-6 mb-2">
                                {{ ucfirst($invoice->status) }}
                            </span>
                            @if($invoice->isOverdue())
                                <br>
                                <small class="text-danger">
                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                    {{ $invoice->due_date->diffInDays(now()) }} days overdue
                                </small>
                            @endif
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Bill To</h6>
                            <h5 class="mb-1">{{ $invoice->student->user->name ?? 'N/A' }}</h5>
                            <p class="mb-1">Student ID: {{ $invoice->student->student_id ?? 'N/A' }}</p>
                            @if($invoice->student->parent)
                                <p class="mb-1 text-muted">
                                    <i class="fas fa-user me-1"></i> Parent: {{ $invoice->student->parent->user->name ?? 'N/A' }}
                                </p>
                                @if($invoice->student->parent->whatsapp_number)
                                    <p class="mb-0 text-muted">
                                        <i class="fab fa-whatsapp me-1"></i> {{ $invoice->student->parent->whatsapp_number }}
                                    </p>
                                @endif
                            @endif
                        </div>
                        <div class="col-md-6 text-md-end">
                            <h6 class="text-muted mb-2">Invoice Details</h6>
                            <p class="mb-1">Type: <strong>{{ ucfirst($invoice->type) }}</strong></p>
                            <p class="mb-1">
                                Period: {{ $invoice->billing_period_start->format('d M Y') }} - {{ $invoice->billing_period_end->format('d M Y') }}
                            </p>
                            <p class="mb-0">
                                Due Date: <strong class="{{ $invoice->isOverdue() ? 'text-danger' : '' }}">{{ $invoice->due_date->format('d M Y') }}</strong>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Package/Enrollment Info -->
            @if($invoice->enrollment)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0"><i class="fas fa-box me-2"></i> Package Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Package:</strong> {{ $invoice->enrollment->package->name ?? 'N/A' }}</p>
                                <p class="mb-1"><strong>Type:</strong> {{ ucfirst($invoice->enrollment->package->type ?? 'N/A') }}</p>
                            </div>
                            <div class="col-md-6">
                                @if($invoice->enrollment->class)
                                    <p class="mb-1"><strong>Class:</strong> {{ $invoice->enrollment->class->name ?? 'N/A' }}</p>
                                @endif
                                <p class="mb-1"><strong>Monthly Fee:</strong> RM {{ number_format($invoice->enrollment->monthly_fee ?? 0, 2) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Invoice Items / Breakdown -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-list me-2"></i> Invoice Breakdown</h5>
                </div>
                <div class="card-body p-0">
                    <table class="table table-borderless mb-0">
                        <tbody>
                            <tr>
                                <td class="ps-4 py-3">
                                    <strong>Subtotal</strong>
                                    <br><small class="text-muted">Monthly tuition fee</small>
                                </td>
                                <td class="text-end pe-4 py-3">RM {{ number_format($invoice->subtotal, 2) }}</td>
                            </tr>
                            @if($invoice->online_fee > 0)
                                <tr class="bg-light">
                                    <td class="ps-4 py-3">
                                        <strong>Online Payment Fee</strong>
                                        <br><small class="text-muted">For online/hybrid packages</small>
                                    </td>
                                    <td class="text-end pe-4 py-3">RM {{ number_format($invoice->online_fee, 2) }}</td>
                                </tr>
                            @endif
                            @if($invoice->discount > 0)
                                <tr class="text-success">
                                    <td class="ps-4 py-3">
                                        <strong>Discount</strong>
                                        @if($invoice->discount_reason)
                                            <br><small class="text-muted">{{ $invoice->discount_reason }}</small>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4 py-3">- RM {{ number_format($invoice->discount, 2) }}</td>
                                </tr>
                            @endif
                            @if($invoice->tax > 0)
                                <tr>
                                    <td class="ps-4 py-3">
                                        <strong>Tax</strong>
                                    </td>
                                    <td class="text-end pe-4 py-3">RM {{ number_format($invoice->tax, 2) }}</td>
                                </tr>
                            @endif
                        </tbody>
                        <tfoot class="border-top">
                            <tr class="bg-light">
                                <td class="ps-4 py-3">
                                    <strong class="fs-5">Total Amount</strong>
                                </td>
                                <td class="text-end pe-4 py-3">
                                    <strong class="fs-5 text-primary">RM {{ number_format($invoice->total_amount, 2) }}</strong>
                                </td>
                            </tr>
                            <tr>
                                <td class="ps-4 py-3">
                                    <strong>Amount Paid</strong>
                                </td>
                                <td class="text-end pe-4 py-3 text-success">
                                    <strong>RM {{ number_format($invoice->paid_amount, 2) }}</strong>
                                </td>
                            </tr>
                            <tr class="border-top">
                                <td class="ps-4 py-3">
                                    <strong class="fs-5">Balance Due</strong>
                                </td>
                                <td class="text-end pe-4 py-3">
                                    <strong class="fs-5 {{ $invoice->balance > 0 ? 'text-danger' : 'text-success' }}">
                                        RM {{ number_format($invoice->balance, 2) }}
                                    </strong>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Payment History -->
            @if($invoice->payments->count() > 0)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0"><i class="fas fa-history me-2"></i> Payment History</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4">Payment #</th>
                                        <th>Date</th>
                                        <th>Method</th>
                                        <th class="text-end">Amount</th>
                                        <th>Status</th>
                                        <th class="pe-4">Processed By</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($invoice->payments as $payment)
                                        <tr>
                                            <td class="ps-4">{{ $payment->payment_number }}</td>
                                            <td>{{ $payment->payment_date->format('d M Y') }}</td>
                                            <td>
                                                <span class="badge bg-secondary">{{ ucfirst($payment->payment_method) }}</span>
                                            </td>
                                            <td class="text-end">RM {{ number_format($payment->amount, 2) }}</td>
                                            <td>
                                                @php
                                                    $paymentColors = [
                                                        'completed' => 'success',
                                                        'pending' => 'warning',
                                                        'failed' => 'danger',
                                                        'refunded' => 'secondary',
                                                    ];
                                                @endphp
                                                <span class="badge bg-{{ $paymentColors[$payment->status] ?? 'secondary' }}">
                                                    {{ ucfirst($payment->status) }}
                                                </span>
                                            </td>
                                            <td class="pe-4">{{ $payment->processedBy->name ?? 'System' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Notes -->
            @if($invoice->notes)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0"><i class="fas fa-sticky-note me-2"></i> Notes</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">{{ $invoice->notes }}</p>
                    </div>
                </div>
            @endif
        </div>

        <!-- Actions Sidebar -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4 sticky-top" style="top: 80px;">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-cog me-2"></i> Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if(!in_array($invoice->status, ['paid', 'cancelled']))
                            <a href="{{ route('admin.payments.create', ['invoice_id' => $invoice->id]) }}" class="btn btn-success">
                                <i class="fas fa-plus me-1"></i> Record Payment
                            </a>
                            <a href="{{ route('admin.invoices.edit', $invoice) }}" class="btn btn-outline-primary">
                                <i class="fas fa-edit me-1"></i> Edit Invoice
                            </a>
                        @endif

                        <form action="{{ route('admin.invoices.send', $invoice) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-outline-info w-100">
                                <i class="fas fa-paper-plane me-1"></i> Send to Parent
                            </button>
                        </form>

                        <button class="btn btn-outline-secondary" onclick="window.print()">
                            <i class="fas fa-print me-1"></i> Print Invoice
                        </button>

                        @if(!in_array($invoice->status, ['paid', 'cancelled']))
                            <hr>
                            <button class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#discountModal">
                                <i class="fas fa-percent me-1"></i> Apply Discount
                            </button>
                            <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelModal">
                                <i class="fas fa-times me-1"></i> Cancel Invoice
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Reminder History -->
            @if($invoice->reminders->count() > 0)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0"><i class="fas fa-bell me-2"></i> Reminder History</h5>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            @foreach($invoice->reminders->sortByDesc('created_at') as $reminder)
                                <li class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>{{ ucfirst($reminder->reminder_type) }}</strong>
                                            <br><small class="text-muted">{{ ucfirst($reminder->channel) }}</small>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-{{ $reminder->status === 'sent' ? 'success' : 'warning' }}">
                                                {{ ucfirst($reminder->status) }}
                                            </span>
                                            @if($reminder->sent_at)
                                                <br><small class="text-muted">{{ $reminder->sent_at->format('d M Y H:i') }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <!-- Quick Stats -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i> Invoice Stats</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Payment Progress</span>
                            <span>{{ $invoice->total_amount > 0 ? round(($invoice->paid_amount / $invoice->total_amount) * 100) : 0 }}%</span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-success" style="width: {{ $invoice->total_amount > 0 ? ($invoice->paid_amount / $invoice->total_amount) * 100 : 0 }}%"></div>
                        </div>
                    </div>
                    <p class="mb-1">
                        <i class="fas fa-calendar-alt me-2 text-muted"></i>
                        Created: {{ $invoice->created_at->diffForHumans() }}
                    </p>
                    <p class="mb-1">
                        <i class="fas fa-bell me-2 text-muted"></i>
                        Reminders Sent: {{ $invoice->reminder_count }}
                    </p>
                    @if($invoice->last_reminder_at)
                        <p class="mb-0">
                            <i class="fas fa-clock me-2 text-muted"></i>
                            Last Reminder: {{ $invoice->last_reminder_at->diffForHumans() }}
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Apply Discount Modal -->
<div class="modal fade" id="discountModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.invoices.apply-discount', $invoice) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Apply Discount</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Discount Amount (RM)</label>
                        <input type="number" name="discount_amount" class="form-control" step="0.01" min="0" max="{{ $invoice->balance }}" required>
                        <small class="text-muted">Maximum: RM {{ number_format($invoice->balance, 2) }}</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason</label>
                        <input type="text" name="discount_reason" class="form-control" required placeholder="e.g., Loyalty discount, Referral voucher">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Apply Discount</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Cancel Invoice Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.invoices.cancel', $invoice) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Cancel Invoice</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        This action cannot be undone. Are you sure?
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Cancellation Reason</label>
                        <textarea name="reason" class="form-control" rows="3" placeholder="Enter reason for cancellation..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger">Cancel Invoice</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
@media print {
    .sidebar, .top-header, .btn, .card-header h5 i, .sticky-top {
        display: none !important;
    }
    .col-lg-8 {
        width: 100% !important;
        max-width: 100% !important;
    }
    .col-lg-4 {
        display: none !important;
    }
    .main-content {
        margin-left: 0 !important;
        padding-top: 0 !important;
    }
}
</style>
@endpush
@endsection
