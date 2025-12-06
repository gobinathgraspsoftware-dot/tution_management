@extends('layouts.app')

@section('title', 'Overdue Invoices')
@section('page-title', 'Overdue Invoices')

@push('styles')
<style>
    .overdue-stats {
        background: linear-gradient(135deg, #e53935 0%, #e35d5b 100%);
        color: white;
        border-radius: 15px;
        padding: 25px;
    }
    .overdue-card {
        border-left: 4px solid #e53935;
        transition: all 0.3s ease;
    }
    .overdue-card:hover {
        box-shadow: 0 4px 15px rgba(0,0,0,0.15);
    }
    .days-overdue {
        font-size: 1.5rem;
        font-weight: bold;
    }
    .days-overdue.critical { color: #c62828; }
    .days-overdue.high { color: #e53935; }
    .days-overdue.medium { color: #ff9800; }
    .student-issues-card {
        border-left: 4px solid #ff9800;
    }
    .reminder-badge {
        font-size: 0.75rem;
    }
    .action-btn-group .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1>
        <i class="fas fa-exclamation-triangle text-danger me-2"></i> Overdue Invoices
    </h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.invoices.index') }}">Invoices</a></li>
            <li class="breadcrumb-item active">Overdue</li>
        </ol>
    </nav>
</div>

<!-- Summary Stats -->
<div class="row mb-4">
    <div class="col-lg-8">
        <div class="overdue-stats h-100">
            <h4 class="mb-4"><i class="fas fa-chart-bar me-2"></i> Overdue Overview</h4>
            <div class="row text-center">
                <div class="col-3">
                    <h2 class="mb-0">{{ $overdueInvoices->count() }}</h2>
                    <small>Total Overdue</small>
                </div>
                <div class="col-3">
                    <h2 class="mb-0">RM {{ number_format($overdueInvoices->sum('balance'), 2) }}</h2>
                    <small>Total Outstanding</small>
                </div>
                <div class="col-3">
                    <h2 class="mb-0">{{ $overdueInvoices->where('days_overdue', '>', 30)->count() }}</h2>
                    <small>30+ Days Overdue</small>
                </div>
                <div class="col-3">
                    <h2 class="mb-0">{{ $studentsWithIssues->count() }}</h2>
                    <small>Students with Issues</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header bg-warning text-dark">
                <i class="fas fa-tools me-2"></i> Quick Actions
            </div>
            <div class="card-body d-flex flex-column justify-content-center">
                <a href="#" class="btn btn-outline-danger mb-2" onclick="sendBulkReminders()">
                    <i class="fas fa-envelope me-2"></i> Send Bulk Reminders
                </a>
                <a href="{{ route('admin.invoices.export', ['status' => 'overdue']) }}" class="btn btn-outline-primary mb-2">
                    <i class="fas fa-download me-2"></i> Export to CSV
                </a>
                <a href="{{ route('admin.invoices.index', ['status' => 'overdue']) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-list me-2"></i> View All Overdue
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Overdue Invoices Table -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-file-invoice-dollar me-2"></i> Overdue Invoices List</span>
        <div class="d-flex gap-2">
            <select id="sortBy" class="form-select form-select-sm" style="width: auto;">
                <option value="days">Sort by Days Overdue</option>
                <option value="amount">Sort by Amount</option>
                <option value="student">Sort by Student</option>
            </select>
        </div>
    </div>
    <div class="card-body p-0">
        @if($overdueInvoices->isEmpty())
            <div class="text-center py-5">
                <i class="fas fa-check-circle text-success fa-4x mb-3"></i>
                <h4>No Overdue Invoices!</h4>
                <p class="text-muted">All invoices are up to date.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Invoice #</th>
                            <th>Student</th>
                            <th>Package</th>
                            <th>Due Date</th>
                            <th class="text-center">Days Overdue</th>
                            <th class="text-end">Balance</th>
                            <th>Reminders</th>
                            <th>Parent Contact</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($overdueInvoices as $invoice)
                            @php
                                $urgencyClass = $invoice['days_overdue'] > 30 ? 'critical' : ($invoice['days_overdue'] > 14 ? 'high' : 'medium');
                            @endphp
                            <tr class="overdue-row" data-days="{{ $invoice['days_overdue'] }}"
                                data-amount="{{ $invoice['balance'] }}" data-student="{{ $invoice['student_name'] }}">
                                <td>
                                    <a href="{{ route('admin.invoices.show', $invoice['invoice_id']) }}"
                                       class="fw-bold text-decoration-none">
                                        {{ $invoice['invoice_number'] }}
                                    </a>
                                </td>
                                <td>
                                    <div>{{ $invoice['student_name'] }}</div>
                                    <small class="text-muted">{{ $invoice['student_code'] }}</small>
                                </td>
                                <td>{{ $invoice['package'] }}</td>
                                <td>{{ \Carbon\Carbon::parse($invoice['due_date'])->format('d M Y') }}</td>
                                <td class="text-center">
                                    <span class="days-overdue {{ $urgencyClass }}">
                                        {{ $invoice['days_overdue'] }}
                                    </span>
                                    <small class="d-block text-muted">days</small>
                                </td>
                                <td class="text-end">
                                    <strong class="text-danger">RM {{ number_format($invoice['balance'], 2) }}</strong>
                                    @if($invoice['paid_amount'] > 0)
                                        <br><small class="text-muted">Paid: RM {{ number_format($invoice['paid_amount'], 2) }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $invoice['reminder_count'] > 2 ? 'danger' : ($invoice['reminder_count'] > 0 ? 'warning' : 'secondary') }} reminder-badge">
                                        {{ $invoice['reminder_count'] }} sent
                                    </span>
                                    @if($invoice['last_reminder'])
                                        <br><small class="text-muted">Last: {{ \Carbon\Carbon::parse($invoice['last_reminder'])->format('d M') }}</small>
                                    @endif
                                </td>
                                <td>
                                    <div>{{ $invoice['parent_name'] }}</div>
                                    @if($invoice['parent_phone'] !== 'N/A')
                                        <small>
                                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $invoice['parent_phone']) }}"
                                               target="_blank" class="text-success text-decoration-none">
                                                <i class="fab fa-whatsapp"></i> {{ $invoice['parent_phone'] }}
                                            </a>
                                        </small>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group action-btn-group">
                                        <a href="{{ route('admin.invoices.show', $invoice['invoice_id']) }}"
                                           class="btn btn-outline-primary" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-warning"
                                                onclick="sendReminder({{ $invoice['invoice_id'] }})" title="Send Reminder">
                                            <i class="fas fa-bell"></i>
                                        </button>
                                        <a href="{{ route('admin.payments.create', ['invoice_id' => $invoice['invoice_id']]) }}"
                                           class="btn btn-outline-success" title="Record Payment">
                                            <i class="fas fa-money-bill"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<!-- Students with Multiple Overdue -->
@if($studentsWithIssues->isNotEmpty())
<div class="card">
    <div class="card-header bg-warning text-dark">
        <i class="fas fa-user-times me-2"></i> Students with Payment Issues (Multiple Overdue Invoices)
    </div>
    <div class="card-body">
        <div class="row">
            @foreach($studentsWithIssues as $student)
                <div class="col-lg-6 mb-3">
                    <div class="card student-issues-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h5 class="mb-1">{{ $student['student_name'] }}</h5>
                                    <small class="text-muted">{{ $student['student_code'] }}</small>
                                </div>
                                <span class="badge bg-danger fs-6">{{ $student['overdue_count'] }} overdue</span>
                            </div>

                            <div class="mt-3">
                                <div class="row">
                                    <div class="col-6">
                                        <small class="text-muted">Total Outstanding</small>
                                        <div class="fw-bold text-danger">RM {{ number_format($student['total_overdue'], 2) }}</div>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">Oldest Overdue</small>
                                        <div class="fw-bold">
                                            @if($student['oldest_overdue'])
                                                {{ \Carbon\Carbon::parse($student['oldest_overdue'])->format('d M Y') }}
                                                <br><small class="text-danger">({{ $student['oldest_days'] }} days ago)</small>
                                            @else
                                                N/A
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-3">
                                <small class="text-muted">Parent: {{ $student['parent_name'] }}</small>
                                @if($student['parent_phone'] !== 'N/A')
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $student['parent_phone']) }}"
                                       target="_blank" class="btn btn-success btn-sm float-end">
                                        <i class="fab fa-whatsapp me-1"></i> Contact
                                    </a>
                                @endif
                            </div>
                        </div>
                        <div class="card-footer bg-transparent">
                            <a href="{{ route('admin.students.show', $student['student_id']) }}" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-user me-1"></i> View Student
                            </a>
                            <a href="{{ route('admin.invoices.index', ['student_id' => $student['student_id'], 'status' => 'overdue']) }}"
                               class="btn btn-outline-danger btn-sm">
                                <i class="fas fa-file-invoice me-1"></i> View Invoices
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endif

<!-- Send Reminder Modal -->
<div class="modal fade" id="reminderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-bell me-2"></i> Send Payment Reminder</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="reminderForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Send Via</label>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="channels[]" value="whatsapp" id="whatsappChannel" checked>
                            <label class="form-check-label" for="whatsappChannel">
                                <i class="fab fa-whatsapp text-success me-1"></i> WhatsApp
                            </label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="channels[]" value="email" id="emailChannel">
                            <label class="form-check-label" for="emailChannel">
                                <i class="fas fa-envelope text-primary me-1"></i> Email
                            </label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="channels[]" value="sms" id="smsChannel">
                            <label class="form-check-label" for="smsChannel">
                                <i class="fas fa-sms text-info me-1"></i> SMS
                            </label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="customMessage" class="form-label">Additional Message (Optional)</label>
                        <textarea class="form-control" name="custom_message" id="customMessage" rows="3"
                                  placeholder="Add any additional message..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-paper-plane me-2"></i> Send Reminder
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function sendReminder(invoiceId) {
    $('#reminderForm').attr('action', '/admin/invoices/' + invoiceId + '/send-reminder');
    $('#reminderModal').modal('show');
}

function sendBulkReminders() {
    if (confirm('Send reminders to all parents with overdue invoices?')) {
        window.location.href = '{{ route("admin.invoices.index") }}?action=bulk-remind';
    }
}

// Sorting functionality
$('#sortBy').on('change', function() {
    var sortBy = $(this).val();
    var rows = $('.overdue-row').get();

    rows.sort(function(a, b) {
        if (sortBy === 'days') {
            return $(b).data('days') - $(a).data('days');
        } else if (sortBy === 'amount') {
            return $(b).data('amount') - $(a).data('amount');
        } else {
            return $(a).data('student').localeCompare($(b).data('student'));
        }
    });

    $.each(rows, function(index, row) {
        $('tbody').append(row);
    });
});
</script>
@endpush
