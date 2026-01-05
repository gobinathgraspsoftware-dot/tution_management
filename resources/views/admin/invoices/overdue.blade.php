@extends('layouts.app')

@section('title', 'Overdue Invoices')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-danger">Overdue Invoices</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.invoices.index') }}">Invoices</a></li>
                    <li class="breadcrumb-item active">Overdue</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.invoices.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Invoices
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card bg-danger text-white h-100">
                <div class="card-body text-center">
                    <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                    <h3 class="mb-0">{{ $overdueInvoices->count() }}</h3>
                    <small>Overdue Invoices</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-warning text-dark h-100">
                <div class="card-body text-center">
                    <i class="fas fa-users fa-2x mb-2"></i>
                    <h3 class="mb-0">{{ $studentsWithIssues->count() }}</h3>
                    <small>Students With Issues</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-dark text-white h-100">
                <div class="card-body text-center">
                    <i class="fas fa-money-bill-wave fa-2x mb-2"></i>
                    <h3 class="mb-0">RM {{ number_format($overdueInvoices->sum('balance'), 2) }}</h3>
                    <small>Total Outstanding</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Overdue Invoices List -->
    <div class="card mb-4">
        <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-file-invoice-dollar me-2"></i>Overdue Invoices</h5>
            <button class="btn btn-light btn-sm" id="bulkReminderBtn" disabled>
                <i class="fas fa-paper-plane me-1"></i> Send Bulk Reminders
            </button>
        </div>
        <div class="card-body p-0">
            @if($overdueInvoices->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="overdueTable">
                        <thead class="table-light">
                            <tr>
                                <th>
                                    <input type="checkbox" id="selectAll" class="form-check-input">
                                </th>
                                <th>Invoice</th>
                                <th>Student</th>
                                <th>Parent Contact</th>
                                <th>Package</th>
                                <th>Total</th>
                                <th>Paid</th>
                                <th>Balance</th>
                                <th>Days Overdue</th>
                                <th>Reminders</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($overdueInvoices as $invoice)
                                <tr class="{{ $invoice['days_overdue'] > 30 ? 'table-danger' : ($invoice['days_overdue'] > 14 ? 'table-warning' : '') }}">
                                    <td>
                                        <input type="checkbox" class="form-check-input invoice-checkbox" value="{{ $invoice['invoice_id'] }}">
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.invoices.show', $invoice['invoice_id']) }}" class="fw-bold">
                                            {{ $invoice['invoice_number'] }}
                                        </a>
                                    </td>
                                    <td>
                                        <strong>{{ $invoice['student_name'] }}</strong>
                                        <br><small class="text-muted">{{ $invoice['student_code'] }}</small>
                                    </td>
                                    <td>
                                        {{ $invoice['parent_name'] }}
                                        @if($invoice['parent_phone'])
                                            <br>
                                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $invoice['parent_phone']) }}" target="_blank" class="text-success">
                                                <i class="fab fa-whatsapp"></i> {{ $invoice['parent_phone'] }}
                                            </a>
                                        @endif
                                    </td>
                                    <td>{{ $invoice['package'] }}</td>
                                    <td>RM {{ number_format($invoice['total_amount'], 2) }}</td>
                                    <td class="text-success">RM {{ number_format($invoice['paid_amount'], 2) }}</td>
                                    <td class="text-danger fw-bold">RM {{ number_format($invoice['balance'], 2) }}</td>
                                    <td>
                                        @if($invoice['days_overdue'] > 30)
                                            <span class="badge bg-danger">{{ $invoice['days_overdue'] }} days</span>
                                        @elseif($invoice['days_overdue'] > 14)
                                            <span class="badge bg-warning text-dark">{{ $invoice['days_overdue'] }} days</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $invoice['days_overdue'] }} days</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $invoice['reminder_count'] }} sent</span>
                                        @if($invoice['last_reminder'])
                                            <br><small class="text-muted">Last: {{ \Carbon\Carbon::parse($invoice['last_reminder'])->format('d M') }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('admin.invoices.show', $invoice['invoice_id']) }}"
                                               class="btn btn-outline-primary" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.payments.create', ['invoice_id' => $invoice['invoice_id']]) }}"
                                               class="btn btn-outline-success" title="Record Payment">
                                                <i class="fas fa-money-bill"></i>
                                            </a>
                                            <form action="{{ route('admin.invoices.reminder', $invoice['invoice_id']) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-warning" title="Send Reminder">
                                                    <i class="fas fa-paper-plane"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-check-circle fa-3x mb-3 text-success"></i>
                    <p class="mb-0">No overdue invoices! Great job keeping payments on track.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Students With Payment Issues -->
    <div class="card">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0"><i class="fas fa-user-times me-2"></i>Students With Multiple Overdue Invoices</h5>
        </div>
        <div class="card-body p-0">
            @if($studentsWithIssues->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Student</th>
                                <th>Parent Contact</th>
                                <th>Overdue Invoices</th>
                                <th>Total Outstanding</th>
                                <th>Oldest Overdue</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($studentsWithIssues as $student)
                                <tr class="{{ $student['overdue_count'] >= 3 ? 'table-danger' : '' }}">
                                    <td>
                                        <strong>{{ $student['student_name'] }}</strong>
                                        <br><small class="text-muted">{{ $student['student_code'] }}</small>
                                    </td>
                                    <td>
                                        {{ $student['parent_name'] }}
                                        @if($student['parent_phone'])
                                            <br>
                                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $student['parent_phone']) }}" target="_blank" class="text-success">
                                                <i class="fab fa-whatsapp"></i> {{ $student['parent_phone'] }}
                                            </a>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-danger">{{ $student['overdue_count'] }} invoices</span>
                                        <br>
                                        @foreach($student['invoices'] as $inv)
                                            <a href="{{ route('admin.invoices.show', $inv['invoice_id']) }}" class="small">
                                                {{ $inv['invoice_number'] }}
                                            </a>
                                            @if(!$loop->last), @endif
                                        @endforeach
                                    </td>
                                    <td class="text-danger fw-bold">RM {{ number_format($student['total_overdue'], 2) }}</td>
                                    <td>
                                        @if($student['oldest_overdue'])
                                            {{ $student['oldest_overdue']->format('d M Y') }}
                                            <br>
                                            <small class="text-muted">{{ $student['oldest_overdue']->diffInDays(now()) }} days ago</small>
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('admin.students.show', $student['student_id']) }}"
                                               class="btn btn-outline-primary" title="View Student">
                                                <i class="fas fa-user"></i>
                                            </a>
                                            <a href="{{ route('admin.invoices.index', ['student_id' => $student['student_id']]) }}"
                                               class="btn btn-outline-info" title="View All Invoices">
                                                <i class="fas fa-file-invoice"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-smile fa-3x mb-3 text-success"></i>
                    <p class="mb-0">No students with multiple overdue invoices</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Select all checkbox
    $('#selectAll').on('change', function() {
        $('.invoice-checkbox').prop('checked', $(this).is(':checked'));
        updateBulkButton();
    });

    // Individual checkbox
    $('.invoice-checkbox').on('change', function() {
        updateBulkButton();
    });

    function updateBulkButton() {
        var checkedCount = $('.invoice-checkbox:checked').length;
        $('#bulkReminderBtn').prop('disabled', checkedCount === 0);
        if (checkedCount > 0) {
            $('#bulkReminderBtn').text('Send ' + checkedCount + ' Reminder(s)');
        } else {
            $('#bulkReminderBtn').text('Send Bulk Reminders');
        }
    }

    // DataTable initialization
    if ($.fn.DataTable) {
        $('#overdueTable').DataTable({
            pageLength: 25,
            order: [[8, 'desc']],
            columnDefs: [
                { orderable: false, targets: [0, 10] }
            ]
        });
    }
});
</script>
@endpush
