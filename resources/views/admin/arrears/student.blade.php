@extends('layouts.app')

@section('title', 'Student Arrears Details')
@section('page-title', 'Student Arrears Details')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.arrears.index') }}">Arrears</a></li>
            <li class="breadcrumb-item active">{{ $arrearsData['student']->user->name ?? 'Student' }}</li>
        </ol>
    </nav>

    <div class="row">
        <!-- Student Info Card -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-user-graduate me-2"></i> Student Information</h5>
                </div>
                <div class="card-body text-center">
                    <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px; font-size: 32px;">
                        {{ strtoupper(substr($arrearsData['student']->user->name ?? 'S', 0, 1)) }}
                    </div>
                    <h5>{{ $arrearsData['student']->user->name ?? 'N/A' }}</h5>
                    <p class="text-muted mb-2">{{ $arrearsData['student']->student_id ?? '' }}</p>

                    <hr>

                    <div class="text-start">
                        <p class="mb-2">
                            <i class="fas fa-envelope me-2 text-muted"></i>
                            {{ $arrearsData['student']->user->email ?? 'N/A' }}
                        </p>
                        <p class="mb-2">
                            <i class="fas fa-phone me-2 text-muted"></i>
                            {{ $arrearsData['student']->user->phone ?? 'N/A' }}
                        </p>
                    </div>

                    @if($arrearsData['student']->parent)
                        <hr>
                        <h6 class="text-muted">Parent/Guardian</h6>
                        <p class="mb-1"><strong>{{ $arrearsData['student']->parent->user->name ?? 'N/A' }}</strong></p>
                        <p class="mb-1">
                            <i class="fab fa-whatsapp text-success me-1"></i>
                            {{ $arrearsData['student']->parent->user->phone ?? 'N/A' }}
                        </p>
                    @endif

                    <hr>

                    <a href="{{ route('admin.students.show', $arrearsData['student']) }}" class="btn btn-outline-primary btn-sm w-100">
                        <i class="fas fa-external-link-alt me-1"></i> View Full Profile
                    </a>
                </div>
            </div>

            <!-- Arrears Summary Card -->
            <div class="card border-danger shadow-sm mb-4">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="fas fa-exclamation-circle me-2"></i> Arrears Summary</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <h2 class="text-danger mb-0">RM {{ number_format($arrearsData['summary']['total_arrears'], 2) }}</h2>
                        <small class="text-muted">Total Outstanding</small>
                    </div>

                    <table class="table table-sm">
                        <tr>
                            <td>Unpaid Invoices:</td>
                            <td class="text-end"><strong>{{ $arrearsData['summary']['invoice_count'] }}</strong></td>
                        </tr>
                        <tr>
                            <td>Oldest Due Date:</td>
                            <td class="text-end">{{ $arrearsData['summary']['oldest_due']?->format('d M Y') ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td>Max Days Overdue:</td>
                            <td class="text-end">
                                @if($arrearsData['summary']['max_days_overdue'] > 0)
                                    <span class="badge bg-danger">{{ $arrearsData['summary']['max_days_overdue'] }} days</span>
                                @else
                                    <span class="badge bg-success">Current</span>
                                @endif
                            </td>
                        </tr>
                    </table>

                    <hr>

                    <h6 class="text-muted">Breakdown by Status</h6>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Pending:</span>
                        <span>RM {{ number_format($arrearsData['summary']['by_status']['pending'], 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Partial:</span>
                        <span>RM {{ number_format($arrearsData['summary']['by_status']['partial'], 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Overdue:</span>
                        <span class="text-danger">RM {{ number_format($arrearsData['summary']['by_status']['overdue'], 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-bolt me-2"></i> Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.payments.create', ['student_id' => $arrearsData['student']->id]) }}" class="btn btn-success">
                            <i class="fas fa-money-bill me-1"></i> Record Payment
                        </a>
                        <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#sendReminderModal">
                            <i class="fas fa-paper-plane me-1"></i> Send Reminder
                        </button>
                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#flagStudentModal">
                            <i class="fas fa-flag me-1"></i> Flag for Follow-up
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Invoices & Payment History -->
        <div class="col-md-8">
            <!-- Unpaid Invoices -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-file-invoice-dollar me-2"></i> Unpaid Invoices</h5>
                    <span class="badge bg-danger">{{ $arrearsData['unpaid_invoices']->count() }} invoices</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Invoice #</th>
                                    <th>Package</th>
                                    <th>Due Date</th>
                                    <th>Total</th>
                                    <th>Paid</th>
                                    <th>Balance</th>
                                    <th>Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($arrearsData['unpaid_invoices'] as $invoice)
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.invoices.show', $invoice) }}">
                                                {{ $invoice->invoice_number }}
                                            </a>
                                        </td>
                                        <td>{{ $invoice->enrollment?->package?->name ?? 'N/A' }}</td>
                                        <td>
                                            {{ $invoice->due_date?->format('d M Y') ?? 'N/A' }}
                                            @if($invoice->days_overdue > 0)
                                                <br><small class="text-danger">{{ $invoice->days_overdue }} days overdue</small>
                                            @endif
                                        </td>
                                        <td>RM {{ number_format($invoice->total_amount, 2) }}</td>
                                        <td class="text-success">RM {{ number_format($invoice->paid_amount, 2) }}</td>
                                        <td><strong class="text-danger">RM {{ number_format($invoice->balance, 2) }}</strong></td>
                                        <td>
                                            <x-arrears-badge :status="$invoice->status" :days="$invoice->days_overdue" />
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

                                    @if($invoice->installments && $invoice->installments->count() > 0)
                                        <tr class="table-light">
                                            <td colspan="8" class="py-2">
                                                <div class="ms-4">
                                                    <small class="text-muted"><strong>Installments:</strong></small>
                                                    <x-installment-progress :installments="$invoice->installments" />
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                                                <p class="mb-0">No unpaid invoices! All payments are up to date.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Installment Arrears (if any) -->
            @if($arrearsData['installment_arrears']->isNotEmpty())
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-calendar-times me-2"></i> Overdue Installments</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Invoice</th>
                                    <th>Installment #</th>
                                    <th>Due Date</th>
                                    <th>Amount</th>
                                    <th>Paid</th>
                                    <th>Balance</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($arrearsData['installment_arrears'] as $installment)
                                    <tr>
                                        <td>{{ $installment->invoice->invoice_number ?? 'N/A' }}</td>
                                        <td>{{ $installment->installment_number }}</td>
                                        <td>
                                            {{ $installment->due_date?->format('d M Y') ?? 'N/A' }}
                                            @if($installment->due_date && $installment->due_date->isPast())
                                                <br><small class="text-danger">{{ $installment->due_date->diffInDays(now()) }} days overdue</small>
                                            @endif
                                        </td>
                                        <td>RM {{ number_format($installment->amount, 2) }}</td>
                                        <td class="text-success">RM {{ number_format($installment->paid_amount, 2) }}</td>
                                        <td><strong class="text-danger">RM {{ number_format($installment->amount - $installment->paid_amount, 2) }}</strong></td>
                                        <td>
                                            @if($installment->status === 'overdue')
                                                <span class="badge bg-danger">Overdue</span>
                                            @elseif($installment->status === 'partial')
                                                <span class="badge bg-warning">Partial</span>
                                            @else
                                                <span class="badge bg-info">{{ ucfirst($installment->status) }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            <!-- Payment History -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-history me-2"></i> Recent Payment History</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Invoice</th>
                                    <th>Amount</th>
                                    <th>Method</th>
                                    <th>Reference</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($paymentHistory as $payment)
                                    <tr>
                                        <td>{{ $payment->payment_date?->format('d M Y') ?? 'N/A' }}</td>
                                        <td>{{ $payment->invoice?->invoice_number ?? 'N/A' }}</td>
                                        <td class="text-success"><strong>RM {{ number_format($payment->amount, 2) }}</strong></td>
                                        <td>{{ ucfirst($payment->payment_method) }}</td>
                                        <td>{{ $payment->reference_number ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-3 text-muted">No payment history found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Reminder History -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-bell me-2"></i> Reminder History</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Invoice</th>
                                    <th>Type</th>
                                    <th>Channel</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($reminderHistory as $reminder)
                                    <tr>
                                        <td>{{ $reminder->scheduled_date?->format('d M Y') ?? 'N/A' }}</td>
                                        <td>{{ $reminder->invoice?->invoice_number ?? 'N/A' }}</td>
                                        <td>
                                            @switch($reminder->reminder_type)
                                                @case('first')
                                                    <span class="badge bg-info">1st</span>
                                                    @break
                                                @case('second')
                                                    <span class="badge bg-warning">2nd</span>
                                                    @break
                                                @case('final')
                                                    <span class="badge bg-danger">Final</span>
                                                    @break
                                                @default
                                                    <span class="badge bg-secondary">{{ ucfirst($reminder->reminder_type) }}</span>
                                            @endswitch
                                        </td>
                                        <td>
                                            @if($reminder->channel === 'whatsapp')
                                                <i class="fab fa-whatsapp text-success"></i> WhatsApp
                                            @elseif($reminder->channel === 'email')
                                                <i class="fas fa-envelope text-primary"></i> Email
                                            @else
                                                <i class="fas fa-sms text-info"></i> SMS
                                            @endif
                                        </td>
                                        <td>
                                            @if($reminder->status === 'sent' || $reminder->status === 'delivered')
                                                <span class="badge bg-success">{{ ucfirst($reminder->status) }}</span>
                                            @elseif($reminder->status === 'failed')
                                                <span class="badge bg-danger">Failed</span>
                                            @else
                                                <span class="badge bg-secondary">{{ ucfirst($reminder->status) }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-3 text-muted">No reminder history found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Send Reminder Modal -->
<div class="modal fade" id="sendReminderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Send Payment Reminder</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.reminders.send-follow-up', $arrearsData['unpaid_invoices']->first() ?? 0) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Select Invoice</label>
                        <select name="invoice_id" class="form-select" required>
                            @foreach($arrearsData['unpaid_invoices'] as $invoice)
                                <option value="{{ $invoice->id }}">
                                    {{ $invoice->invoice_number }} - RM {{ number_format($invoice->balance, 2) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Custom Message (Optional)</label>
                        <textarea name="custom_message" class="form-control" rows="3" placeholder="Leave empty to use default template"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-paper-plane me-1"></i> Send Reminder
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Flag Student Modal -->
<div class="modal fade" id="flagStudentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Flag Student for Follow-up</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.arrears.flag-student', $arrearsData['student']) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        This will flag the student for management follow-up regarding outstanding arrears.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason for Flag <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control" rows="3" required placeholder="Enter reason for flagging this student..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-flag me-1"></i> Flag Student
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
