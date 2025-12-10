@extends('layouts.app')

@section('title', 'Overdue Installments')
@section('page-title', 'Overdue Installments')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="fas fa-exclamation-triangle text-danger me-2"></i> Overdue Installments</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.installments.index') }}">Installments</a></li>
                <li class="breadcrumb-item active">Overdue</li>
            </ol>
        </nav>
    </div>
    <div>
        <a href="{{ route('admin.installments.index') }}" class="btn btn-outline-secondary me-2">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
        <a href="{{ route('admin.installments.export', ['status' => 'overdue']) }}" class="btn btn-outline-success">
            <i class="fas fa-download me-1"></i> Export
        </a>
    </div>
</div>

<!-- Summary Card -->
<div class="card mb-4 border-danger">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h2 class="text-danger mb-0">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    RM {{ number_format($totalOverdue, 2) }}
                </h2>
                <p class="text-muted mb-0">Total Overdue Amount</p>
            </div>
            <div class="col-md-6 text-md-end">
                <form action="{{ route('admin.installments.bulk-reminder') }}" method="POST" id="bulkForm">
                    @csrf
                    <button type="button" class="btn btn-warning" onclick="selectAll()" id="selectAllBtn">
                        <i class="fas fa-check-square me-1"></i> Select All
                    </button>
                    <button type="submit" class="btn btn-danger" id="sendRemindersBtn" disabled>
                        <i class="fas fa-bell me-1"></i> Send Reminders (<span id="selectedCount">0</span>)
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Overdue Installments Table -->
<div class="card">
    <div class="card-header bg-danger text-white">
        <h5 class="mb-0"><i class="fas fa-list me-2"></i> Overdue List ({{ $overdueInstallments->total() }} installments)</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="40">
                            <input type="checkbox" class="form-check-input" id="checkAll" onchange="toggleAll()">
                        </th>
                        <th>Invoice</th>
                        <th>Student</th>
                        <th>Package</th>
                        <th>Installment</th>
                        <th>Amount Due</th>
                        <th>Due Date</th>
                        <th>Days Overdue</th>
                        <th>Reminders</th>
                        <th width="130">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($overdueInstallments as $installment)
                        @php
                            $daysOverdue = $installment->due_date->diffInDays(now());
                            $severityClass = $daysOverdue > 60 ? 'table-danger' : ($daysOverdue > 30 ? 'table-warning' : '');
                        @endphp
                        <tr class="{{ $severityClass }}">
                            <td>
                                <input type="checkbox" class="form-check-input installment-check"
                                       name="installment_ids[]"
                                       value="{{ $installment->id }}"
                                       form="bulkForm"
                                       onchange="updateCount()">
                            </td>
                            <td>
                                <a href="{{ route('admin.installments.show', $installment->invoice) }}" class="fw-bold text-primary">
                                    {{ $installment->invoice->invoice_number ?? 'N/A' }}
                                </a>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="user-avatar me-2" style="width:30px;height:30px;font-size:0.8rem;">
                                        {{ substr($installment->invoice->student->user->name ?? 'N', 0, 1) }}
                                    </div>
                                    <div>
                                        <strong>{{ $installment->invoice->student->user->name ?? 'N/A' }}</strong>
                                        <br><small class="text-muted">{{ $installment->invoice->student->user->phone ?? '' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $installment->invoice->enrollment->package->name ?? 'N/A' }}</td>
                            <td>
                                <span class="badge bg-secondary">#{{ $installment->installment_number }}</span>
                                <br>
                                <small class="text-muted">of {{ $installment->invoice->installment_count ?? '?' }}</small>
                            </td>
                            <td>
                                <strong class="text-danger">RM {{ number_format($installment->balance, 2) }}</strong>
                                @if($installment->paid_amount > 0)
                                    <br><small class="text-success">Paid: RM {{ number_format($installment->paid_amount, 2) }}</small>
                                @endif
                            </td>
                            <td>
                                {{ $installment->due_date->format('d M Y') }}
                            </td>
                            <td>
                                <span class="badge {{ $daysOverdue > 60 ? 'bg-danger' : ($daysOverdue > 30 ? 'bg-warning text-dark' : 'bg-secondary') }} fs-6">
                                    {{ $daysOverdue }} days
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $installment->reminder_count ?? 0 }}</span>
                                @if($installment->last_reminder_at)
                                    <br><small class="text-muted">{{ $installment->last_reminder_at->format('d M') }}</small>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.installments.show', $installment->invoice) }}"
                                       class="btn btn-outline-info" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-success"
                                            title="Record Payment"
                                            data-bs-toggle="modal"
                                            data-bs-target="#paymentModal"
                                            data-installment-id="{{ $installment->id }}"
                                            data-balance="{{ $installment->balance }}">
                                        <i class="fas fa-money-bill"></i>
                                    </button>
                                    <form action="{{ route('admin.installments.send-reminder', $installment) }}"
                                          method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-warning" title="Send Reminder">
                                            <i class="fas fa-bell"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-5">
                                <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                                <h5 class="text-success">No Overdue Installments!</h5>
                                <p class="text-muted">All installments are up to date.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($overdueInstallments->hasPages())
        <div class="p-3 border-top">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted">
                    Showing {{ $overdueInstallments->firstItem() ?? 0 }} to {{ $overdueInstallments->lastItem() ?? 0 }} of {{ $overdueInstallments->total() }} overdue installments
                </div>
                {{ $overdueInstallments->links() }}
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Severity Legend -->
<div class="card mt-4">
    <div class="card-body">
        <h6 class="mb-3">Severity Legend</h6>
        <div class="d-flex gap-4">
            <div>
                <span class="badge bg-secondary me-1">1-30 days</span>
                <small class="text-muted">Normal Overdue</small>
            </div>
            <div>
                <span class="badge bg-warning text-dark me-1">31-60 days</span>
                <small class="text-muted">Warning</small>
            </div>
            <div>
                <span class="badge bg-danger me-1">60+ days</span>
                <small class="text-muted">Critical</small>
            </div>
        </div>
    </div>
</div>

<!-- Quick Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-money-bill-wave me-2"></i> Quick Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="paymentForm" method="POST">
                @csrf
                <div class="modal-body">
                    <p>Outstanding Balance: <strong class="text-danger">RM <span id="modalBalance"></span></strong></p>

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
                        <input type="text" name="reference_number" class="form-control">
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
@endsection

@push('styles')
<style>
.user-avatar {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
}
</style>
@endpush

@push('scripts')
<script>
let allSelected = false;

function toggleAll() {
    const checkboxes = document.querySelectorAll('.installment-check');
    const checkAll = document.getElementById('checkAll');
    checkboxes.forEach(cb => cb.checked = checkAll.checked);
    updateCount();
}

function selectAll() {
    allSelected = !allSelected;
    const checkboxes = document.querySelectorAll('.installment-check');
    const checkAll = document.getElementById('checkAll');

    checkboxes.forEach(cb => cb.checked = allSelected);
    checkAll.checked = allSelected;

    document.getElementById('selectAllBtn').innerHTML = allSelected
        ? '<i class="fas fa-square me-1"></i> Deselect All'
        : '<i class="fas fa-check-square me-1"></i> Select All';

    updateCount();
}

function updateCount() {
    const checked = document.querySelectorAll('.installment-check:checked').length;
    document.getElementById('selectedCount').textContent = checked;
    document.getElementById('sendRemindersBtn').disabled = checked === 0;

    // Update checkAll state
    const total = document.querySelectorAll('.installment-check').length;
    document.getElementById('checkAll').checked = checked === total && total > 0;
}

// Payment Modal
document.getElementById('paymentModal').addEventListener('show.bs.modal', function(event) {
    const button = event.relatedTarget;
    const installmentId = button.getAttribute('data-installment-id');
    const balance = button.getAttribute('data-balance');

    document.getElementById('modalBalance').textContent = parseFloat(balance).toFixed(2);
    document.getElementById('paymentAmount').max = balance;
    document.getElementById('paymentAmount').value = balance;
    document.getElementById('paymentForm').action = '{{ url("admin/installments") }}/' + installmentId + '/payment';
});
</script>
@endpush
