@extends('layouts.app')

@section('title', 'Installment Plan Details')
@section('page-title', 'Installment Plan Details')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="fas fa-file-invoice-dollar me-2"></i> Installment Plan</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.installments.index') }}">Installments</a></li>
                <li class="breadcrumb-item active">{{ $invoice->invoice_number }}</li>
            </ol>
        </nav>
    </div>
    <div>
        <a href="{{ route('admin.invoices.show', $invoice) }}" class="btn btn-outline-info me-2">
            <i class="fas fa-file-invoice me-1"></i> View Invoice
        </a>
        @can('manage-installments')
        @if(!$invoice->isPaid())
        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelPlanModal">
            <i class="fas fa-times me-1"></i> Cancel Plan
        </button>
        @endif
        @endcan
    </div>
</div>

<div class="row">
    <!-- Main Content -->
    <div class="col-lg-8">
        <!-- Progress Overview -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h5 class="mb-3">Payment Progress</h5>
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar bg-success" role="progressbar"
                                 style="width: {{ $summary['completion_percentage'] }}%"
                                 aria-valuenow="{{ $summary['completion_percentage'] }}"
                                 aria-valuemin="0" aria-valuemax="100">
                                {{ $summary['completion_percentage'] }}%
                            </div>
                        </div>
                        <div class="d-flex justify-content-between mt-2">
                            <small class="text-muted">{{ $summary['paid_installments'] }} of {{ $summary['total_installments'] }} installments paid</small>
                            <small class="text-success">RM {{ number_format($summary['total_paid'], 2) }} paid</small>
                        </div>
                    </div>
                    <div class="col-md-6 text-md-end mt-3 mt-md-0">
                        <div class="row text-center">
                            <div class="col-4">
                                <h4 class="mb-0 text-success">{{ $summary['paid_installments'] }}</h4>
                                <small class="text-muted">Paid</small>
                            </div>
                            <div class="col-4">
                                <h4 class="mb-0 text-warning">{{ $summary['pending_installments'] }}</h4>
                                <small class="text-muted">Pending</small>
                            </div>
                            <div class="col-4">
                                <h4 class="mb-0 text-danger">{{ $summary['overdue_installments'] }}</h4>
                                <small class="text-muted">Overdue</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Installments List -->
        <div class="card mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-list-ol me-2"></i> Installment Schedule</h5>
                @if($summary['overdue_installments'] > 0)
                <form action="{{ route('admin.installments.bulk-reminder') }}" method="POST" class="d-inline">
                    @csrf
                    @foreach($invoice->installments->where('status', 'overdue') as $inst)
                        <input type="hidden" name="installment_ids[]" value="{{ $inst->id }}">
                    @endforeach
                    <button type="submit" class="btn btn-warning btn-sm">
                        <i class="fas fa-bell me-1"></i> Send Overdue Reminders
                    </button>
                </form>
                @endif
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="60">#</th>
                                <th>Due Date</th>
                                <th>Amount</th>
                                <th>Paid</th>
                                <th>Balance</th>
                                <th>Status</th>
                                <th>Reminders</th>
                                <th width="150">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoice->installments as $installment)
                                <tr class="{{ $installment->isOverdue() ? 'table-danger' : ($installment->isPaid() ? 'table-success' : '') }}">
                                    <td>
                                        <span class="badge {{ $installment->isPaid() ? 'bg-success' : 'bg-secondary' }} fs-6">
                                            {{ $installment->installment_number }}
                                        </span>
                                    </td>
                                    <td>
                                        {{ $installment->due_date->format('d M Y') }}
                                        @if($installment->isOverdue())
                                            <br><small class="text-danger">{{ $installment->days_overdue }} days overdue</small>
                                        @elseif(!$installment->isPaid() && $installment->due_date->isToday())
                                            <br><small class="text-warning">Due today!</small>
                                        @elseif(!$installment->isPaid() && $installment->due_date->isFuture())
                                            <br><small class="text-muted">In {{ $installment->due_date->diffForHumans() }}</small>
                                        @endif
                                    </td>
                                    <td>RM {{ number_format($installment->amount, 2) }}</td>
                                    <td class="text-success">RM {{ number_format($installment->paid_amount, 2) }}</td>
                                    <td class="{{ $installment->balance > 0 ? 'text-danger' : 'text-success' }}">
                                        RM {{ number_format($installment->balance, 2) }}
                                    </td>
                                    <td>
                                        @switch($installment->status)
                                            @case('paid')
                                                <span class="badge bg-success">Paid</span>
                                                @if($installment->paid_at)
                                                    <br><small class="text-muted">{{ $installment->paid_at->format('d M Y') }}</small>
                                                @endif
                                                @break
                                            @case('partial')
                                                <span class="badge bg-info">Partial</span>
                                                @break
                                            @case('overdue')
                                                <span class="badge bg-danger">Overdue</span>
                                                @break
                                            @default
                                                <span class="badge bg-warning text-dark">Pending</span>
                                        @endswitch
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $installment->reminder_count ?? 0 }}</span>
                                        @if($installment->last_reminder_at)
                                            <br><small class="text-muted">{{ $installment->last_reminder_at->format('d M') }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if(!$installment->isPaid())
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-outline-success"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#paymentModal"
                                                        data-installment-id="{{ $installment->id }}"
                                                        data-installment-number="{{ $installment->installment_number }}"
                                                        data-balance="{{ $installment->balance }}"
                                                        title="Record Payment">
                                                    <i class="fas fa-money-bill"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-warning"
                                                        onclick="sendReminder({{ $installment->id }})"
                                                        title="Send Reminder">
                                                    <i class="fas fa-bell"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-primary"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editModal"
                                                        data-installment-id="{{ $installment->id }}"
                                                        data-amount="{{ $installment->amount }}"
                                                        data-due-date="{{ $installment->due_date->format('Y-m-d') }}"
                                                        title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            </div>
                                        @else
                                            <span class="text-success"><i class="fas fa-check-circle"></i> Complete</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-secondary">
                            <tr>
                                <td colspan="2" class="text-end"><strong>Totals:</strong></td>
                                <td><strong>RM {{ number_format($summary['total_amount'], 2) }}</strong></td>
                                <td class="text-success"><strong>RM {{ number_format($summary['total_paid'], 2) }}</strong></td>
                                <td class="text-danger"><strong>RM {{ number_format($summary['total_balance'], 2) }}</strong></td>
                                <td colspan="3"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Payment History -->
        @if($invoice->payments->count() > 0)
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-history me-2"></i> Payment History</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Reference</th>
                                <th>Processed By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoice->payments as $payment)
                                <tr>
                                    <td>{{ $payment->payment_date->format('d M Y H:i') }}</td>
                                    <td class="text-success">RM {{ number_format($payment->amount, 2) }}</td>
                                    <td>
                                        <span class="badge bg-info">{{ ucfirst($payment->payment_method) }}</span>
                                    </td>
                                    <td>{{ $payment->reference_number ?? '-' }}</td>
                                    <td>{{ $payment->processedBy->name ?? 'System' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        <!-- Reminder History -->
        @if($invoice->reminders->count() > 0)
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-bell me-2"></i> Reminder History</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Scheduled</th>
                                <th>Type</th>
                                <th>Channel</th>
                                <th>Status</th>
                                <th>Sent At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoice->reminders->take(10) as $reminder)
                                <tr>
                                    <td>{{ $reminder->scheduled_date->format('d M Y') }}</td>
                                    <td><span class="badge bg-secondary">{{ ucfirst($reminder->reminder_type) }}</span></td>
                                    <td><i class="fas fa-{{ $reminder->channel == 'whatsapp' ? 'whatsapp text-success' : ($reminder->channel == 'email' ? 'envelope text-primary' : 'sms text-info') }}"></i> {{ ucfirst($reminder->channel) }}</td>
                                    <td>
                                        @switch($reminder->status)
                                            @case('sent')
                                            @case('delivered')
                                                <span class="badge bg-success">{{ ucfirst($reminder->status) }}</span>
                                                @break
                                            @case('failed')
                                                <span class="badge bg-danger">Failed</span>
                                                @break
                                            @case('scheduled')
                                                <span class="badge bg-warning text-dark">Scheduled</span>
                                                @break
                                            @default
                                                <span class="badge bg-secondary">{{ ucfirst($reminder->status) }}</span>
                                        @endswitch
                                    </td>
                                    <td>{{ $reminder->sent_at ? $reminder->sent_at->format('d M Y H:i') : '-' }}</td>
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
        <!-- Invoice Info -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0"><i class="fas fa-file-invoice me-2"></i> Invoice Information</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted">Invoice #:</td>
                        <td class="text-end"><strong>{{ $invoice->invoice_number }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Total Amount:</td>
                        <td class="text-end">RM {{ number_format($invoice->total_amount, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Due Date:</td>
                        <td class="text-end">{{ $invoice->due_date?->format('d M Y') ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Status:</td>
                        <td class="text-end">
                            <x-status-badge :status="$invoice->status" />
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Student Info -->
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0"><i class="fas fa-user-graduate me-2"></i> Student Information</h6>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="user-avatar me-3" style="width:50px;height:50px;">
                        {{ substr($invoice->student->user->name ?? 'S', 0, 1) }}
                    </div>
                    <div>
                        <h6 class="mb-0">{{ $invoice->student->user->name ?? 'N/A' }}</h6>
                        <small class="text-muted">{{ $invoice->student->student_id ?? '' }}</small>
                    </div>
                </div>
                <hr>
                <p class="mb-1"><i class="fas fa-phone text-muted me-2"></i> {{ $invoice->student->user->phone ?? 'N/A' }}</p>
                <p class="mb-1"><i class="fas fa-envelope text-muted me-2"></i> {{ $invoice->student->user->email ?? 'N/A' }}</p>
                @if($invoice->student->parent)
                    <hr>
                    <small class="text-muted">Parent/Guardian:</small>
                    <p class="mb-0"><strong>{{ $invoice->student->parent->user->name ?? 'N/A' }}</strong></p>
                    <p class="mb-0"><i class="fas fa-phone text-muted me-2"></i> {{ $invoice->student->parent->user->phone ?? 'N/A' }}</p>
                @endif
            </div>
            <div class="card-footer bg-light">
                <a href="{{ route('admin.installments.student-history', $invoice->student) }}" class="btn btn-sm btn-outline-secondary w-100">
                    <i class="fas fa-history me-1"></i> View Full History
                </a>
            </div>
        </div>

        <!-- Next Due -->
        @if($summary['next_due'])
        <div class="card mb-4">
            <div class="card-header {{ $summary['next_due']->isOverdue() ? 'bg-danger' : 'bg-warning' }} text-{{ $summary['next_due']->isOverdue() ? 'white' : 'dark' }}">
                <h6 class="mb-0"><i class="fas fa-clock me-2"></i> Next Payment Due</h6>
            </div>
            <div class="card-body text-center">
                <h2 class="{{ $summary['next_due']->isOverdue() ? 'text-danger' : '' }}">
                    RM {{ number_format($summary['next_due']->balance, 2) }}
                </h2>
                <p class="mb-2">
                    Installment #{{ $summary['next_due']->installment_number }}
                </p>
                <p class="mb-0 {{ $summary['next_due']->isOverdue() ? 'text-danger' : 'text-muted' }}">
                    <i class="fas fa-calendar-day me-1"></i>
                    {{ $summary['next_due']->due_date->format('d M Y') }}
                    @if($summary['next_due']->isOverdue())
                        <br><strong>({{ $summary['next_due']->days_overdue }} days overdue)</strong>
                    @endif
                </p>
            </div>
        </div>
        @endif

        <!-- Notes -->
        @if($invoice->installment_notes)
        <div class="card">
            <div class="card-header bg-secondary text-white">
                <h6 class="mb-0"><i class="fas fa-sticky-note me-2"></i> Notes</h6>
            </div>
            <div class="card-body">
                <p class="mb-0">{{ $invoice->installment_notes }}</p>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-money-bill-wave me-2"></i> Record Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="paymentForm" method="POST">
                @csrf
                <div class="modal-body">
                    <p>Recording payment for <strong>Installment #<span id="modalInstallmentNum"></span></strong></p>
                    <p class="text-muted">Outstanding Balance: <strong class="text-danger">RM <span id="modalBalance"></span></strong></p>

                    <div class="mb-3">
                        <label class="form-label">Payment Amount <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">RM</span>
                            <input type="number" name="amount" id="paymentAmount" class="form-control" step="0.01" min="0.01" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                        <select name="payment_method" class="form-select" required>
                            <option value="cash">Cash</option>
                            <option value="qr">QR Payment</option>
                            <option value="online">Online Banking</option>
                            <option value="bank_transfer">Bank Transfer</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Reference Number</label>
                        <input type="text" name="reference_number" class="form-control" placeholder="Transaction reference...">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Optional payment notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check me-1"></i> Record Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Installment Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit me-2"></i> Edit Installment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editForm" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Amount</label>
                        <div class="input-group">
                            <span class="input-group-text">RM</span>
                            <input type="number" name="amount" id="editAmount" class="form-control" step="0.01" min="0">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Due Date</label>
                        <input type="date" name="due_date" id="editDueDate" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Optional notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Cancel Plan Modal -->
<div class="modal fade" id="cancelPlanModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i> Cancel Installment Plan</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to cancel this installment plan?</p>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    This will cancel all pending installments. Any payments already made will remain recorded.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Keep Plan</button>
                <form action="{{ route('admin.installments.cancel', $invoice) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times me-1"></i> Cancel Plan
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Reminder Form (Hidden) -->
<form id="reminderForm" method="POST" style="display: none;">
    @csrf
</form>
@endsection

@push('styles')
<style>
.user-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 1.2rem;
}
</style>
@endpush

@push('scripts')
<script>
// Payment Modal
document.getElementById('paymentModal').addEventListener('show.bs.modal', function(event) {
    const button = event.relatedTarget;
    const installmentId = button.getAttribute('data-installment-id');
    const installmentNum = button.getAttribute('data-installment-number');
    const balance = button.getAttribute('data-balance');

    document.getElementById('modalInstallmentNum').textContent = installmentNum;
    document.getElementById('modalBalance').textContent = parseFloat(balance).toFixed(2);
    document.getElementById('paymentAmount').max = balance;
    document.getElementById('paymentAmount').value = balance;
    document.getElementById('paymentForm').action = '{{ url("admin/installments") }}/' + installmentId + '/payment';
});

// Edit Modal
document.getElementById('editModal').addEventListener('show.bs.modal', function(event) {
    const button = event.relatedTarget;
    const installmentId = button.getAttribute('data-installment-id');
    const amount = button.getAttribute('data-amount');
    const dueDate = button.getAttribute('data-due-date');

    document.getElementById('editAmount').value = amount;
    document.getElementById('editDueDate').value = dueDate;
    document.getElementById('editForm').action = '{{ url("admin/installments") }}/' + installmentId;
});

// Send Reminder
function sendReminder(installmentId) {
    if (confirm('Send payment reminder for this installment?')) {
        const form = document.getElementById('reminderForm');
        form.action = '{{ url("admin/installments") }}/' + installmentId + '/reminder';
        form.submit();
    }
}
</script>
@endpush
