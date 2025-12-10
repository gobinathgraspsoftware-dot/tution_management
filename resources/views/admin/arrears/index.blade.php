@extends('layouts.app')

@section('title', 'Arrears Management')
@section('page-title', 'Arrears Management')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Arrears</li>
        </ol>
    </nav>

    <!-- Dashboard Stats -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle fa-2x opacity-75"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0">RM {{ number_format($dashboardStats['total_arrears'] ?? 0, 2) }}</h3>
                            <small>Total Arrears</small>
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
                            <i class="fas fa-clock fa-2x opacity-75"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0">{{ $dashboardStats['overdue_invoices'] ?? 0 }}</h3>
                            <small>Overdue Invoices</small>
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
                            <i class="fas fa-users fa-2x opacity-75"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0">{{ $dashboardStats['students_with_arrears'] ?? 0 }}</h3>
                            <small>Students with Arrears</small>
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
                            <i class="fas fa-percentage fa-2x opacity-75"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0">{{ $dashboardStats['collection_rate'] ?? 0 }}%</h3>
                            <small>Collection Rate</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Due This Week / Next Week -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-calendar-week text-warning fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4 class="mb-0">RM {{ number_format($dashboardStats['due_this_week'] ?? 0, 2) }}</h4>
                            <p class="text-muted mb-0">Due This Week</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-info bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-calendar-alt text-info fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4 class="mb-0">RM {{ number_format($dashboardStats['due_next_week'] ?? 0, 2) }}</h4>
                            <p class="text-muted mb-0">Due Next Week</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Links -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('admin.arrears.students-list') }}" class="btn btn-outline-primary">
                            <i class="fas fa-users me-1"></i> Students with Arrears
                        </a>
                        <a href="{{ route('admin.arrears.by-class') }}" class="btn btn-outline-info">
                            <i class="fas fa-chalkboard me-1"></i> Arrears by Class
                        </a>
                        <a href="{{ route('admin.arrears.by-subject') }}" class="btn btn-outline-warning">
                            <i class="fas fa-book me-1"></i> Arrears by Subject
                        </a>
                        <a href="{{ route('admin.arrears.due-report') }}" class="btn btn-outline-success">
                            <i class="fas fa-calendar-check me-1"></i> Due Report
                        </a>
                        <a href="{{ route('admin.arrears.forecast') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-chart-line me-1"></i> Collection Forecast
                        </a>
                        <a href="{{ route('admin.arrears.aging-analysis') }}" class="btn btn-outline-danger">
                            <i class="fas fa-chart-pie me-1"></i> Aging Analysis
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.arrears.index') }}" class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">Date From</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Date To</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Class</label>
                    <select name="class_id" class="form-select">
                        <option value="">All Classes</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                {{ $class->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Subject</label>
                    <select name="subject_id" class="form-select">
                        <option value="">All Subjects</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                                {{ $subject->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>Partial</option>
                        <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search me-1"></i> Filter
                    </button>
                    <a href="{{ route('admin.arrears.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-undo"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Arrears by Age Summary -->
    @if(!empty($dashboardStats['arrears_by_age']))
    <div class="row mb-4">
        @foreach($dashboardStats['arrears_by_age'] as $range => $data)
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="text-muted">{{ $range }} Days Overdue</h6>
                        <h4 class="text-danger mb-1">RM {{ number_format($data['amount'], 2) }}</h4>
                        <small class="text-muted">{{ $data['count'] }} invoices</small>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    @endif

    <!-- Critical Arrears Alert -->
    @if($criticalArrears->isNotEmpty())
    <div class="card border-danger mb-4">
        <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
            <span><i class="fas fa-exclamation-triangle me-2"></i> Critical Arrears - Immediate Attention Required</span>
            <span class="badge bg-white text-danger">{{ $criticalArrears->count() }} cases</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Student</th>
                            <th>Invoice</th>
                            <th>Balance</th>
                            <th>Days Overdue</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($criticalArrears->take(5) as $invoice)
                            <tr>
                                <td>
                                    <strong>{{ $invoice->student?->user?->name ?? 'N/A' }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $invoice->student?->parent?->user?->phone ?? '' }}</small>
                                </td>
                                <td>{{ $invoice->invoice_number }}</td>
                                <td><strong class="text-danger">RM {{ number_format($invoice->balance, 2) }}</strong></td>
                                <td><span class="badge bg-danger">{{ $invoice->days_overdue }} days</span></td>
                                <td>
                                    <a href="{{ route('admin.arrears.student', $invoice->student) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Arrears List -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-file-invoice-dollar me-2"></i> Arrears List</h5>
            <div>
                <a href="{{ route('admin.arrears.print', request()->all()) }}" class="btn btn-outline-secondary btn-sm" target="_blank">
                    <i class="fas fa-print me-1"></i> Print
                </a>
                <a href="{{ route('admin.arrears.export', request()->all()) }}" class="btn btn-outline-success btn-sm">
                    <i class="fas fa-file-excel me-1"></i> Export
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            <form id="bulkForm" action="{{ route('admin.arrears.send-bulk-reminders') }}" method="POST">
                @csrf
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="40">
                                    <input type="checkbox" class="form-check-input" id="selectAll">
                                </th>
                                <th>Invoice #</th>
                                <th>Student</th>
                                <th>Package</th>
                                <th>Due Date</th>
                                <th>Total</th>
                                <th>Paid</th>
                                <th>Balance</th>
                                <th>Status</th>
                                <th>Days Overdue</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($report['invoices'] as $invoice)
                                <tr>
                                    <td>
                                        <input type="checkbox" name="invoice_ids[]" value="{{ $invoice->id }}" class="form-check-input invoice-checkbox">
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.invoices.show', $invoice) }}">{{ $invoice->invoice_number }}</a>
                                    </td>
                                    <td>
                                        <strong>{{ $invoice->student?->user?->name ?? 'N/A' }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $invoice->student?->student_id ?? '' }}</small>
                                    </td>
                                    <td>{{ $invoice->enrollment?->package?->name ?? 'N/A' }}</td>
                                    <td>
                                        {{ $invoice->due_date?->format('d M Y') ?? 'N/A' }}
                                    </td>
                                    <td>RM {{ number_format($invoice->total_amount, 2) }}</td>
                                    <td class="text-success">RM {{ number_format($invoice->paid_amount, 2) }}</td>
                                    <td><strong class="text-danger">RM {{ number_format($invoice->balance, 2) }}</strong></td>
                                    <td>
                                        <x-arrears-badge :status="$invoice->status" :days="$invoice->days_overdue" />
                                    </td>
                                    <td>
                                        @if($invoice->days_overdue > 0)
                                            <span class="badge bg-{{ $invoice->days_overdue > 60 ? 'danger' : ($invoice->days_overdue > 30 ? 'warning' : 'info') }}">
                                                {{ $invoice->days_overdue }} days
                                            </span>
                                        @else
                                            <span class="badge bg-success">Current</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('admin.arrears.student', $invoice->student) }}" class="btn btn-outline-primary" title="Student Details">
                                                <i class="fas fa-user"></i>
                                            </a>
                                            <a href="{{ route('admin.payments.create', ['invoice_id' => $invoice->id]) }}" class="btn btn-outline-success" title="Record Payment">
                                                <i class="fas fa-money-bill"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="fas fa-check-circle fa-3x mb-3 text-success"></i>
                                            <p>No arrears found! All payments are up to date.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($report['invoices']->count() > 0)
                    <div class="card-footer bg-white d-flex justify-content-between align-items-center">
                        <div>
                            <button type="submit" class="btn btn-warning" id="bulkReminderBtn" disabled>
                                <i class="fas fa-paper-plane me-1"></i> Send Reminders (<span id="selectedCount">0</span> selected)
                            </button>
                        </div>
                        <div>
                            {{ $report['invoices']->links() }}
                        </div>
                    </div>
                @endif
            </form>
        </div>
    </div>

    <!-- Summary -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted mb-3">Arrears Summary</h6>
                    <table class="table table-sm">
                        <tr>
                            <td>Total Arrears Amount:</td>
                            <td class="text-end"><strong class="text-danger">RM {{ number_format($report['summary']['total_arrears'], 2) }}</strong></td>
                        </tr>
                        <tr>
                            <td>Total Unpaid Invoices:</td>
                            <td class="text-end">{{ $report['summary']['total_invoices'] }}</td>
                        </tr>
                        <tr>
                            <td>Students with Arrears:</td>
                            <td class="text-end">{{ $report['summary']['total_students'] }}</td>
                        </tr>
                        <tr>
                            <td>Overdue Invoices:</td>
                            <td class="text-end"><span class="badge bg-danger">{{ $report['summary']['overdue_count'] }}</span></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Select all checkbox
    $('#selectAll').on('change', function() {
        $('.invoice-checkbox').prop('checked', $(this).is(':checked'));
        updateSelectedCount();
    });

    // Individual checkbox
    $('.invoice-checkbox').on('change', function() {
        updateSelectedCount();
    });

    function updateSelectedCount() {
        var count = $('.invoice-checkbox:checked').length;
        $('#selectedCount').text(count);
        $('#bulkReminderBtn').prop('disabled', count === 0);
    }
});
</script>
@endpush
@endsection
