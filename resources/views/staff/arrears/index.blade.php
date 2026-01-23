@extends('layouts.app')

@section('title', 'Arrears Dashboard')
@section('page-title', 'Arrears Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card h-100 border-start border-danger border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Total Arrears</p>
                            <h3 class="mb-0 text-danger">RM {{ number_format($dashboardStats['total_arrears'] ?? 0, 2) }}</h3>
                        </div>
                        <div class="stat-icon bg-danger bg-opacity-10 text-danger rounded-circle p-3">
                            <i class="fas fa-exclamation-triangle fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card h-100 border-start border-warning border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Overdue Invoices</p>
                            <h3 class="mb-0 text-warning">{{ number_format($dashboardStats['overdue_invoices'] ?? 0) }}</h3>
                        </div>
                        <div class="stat-icon bg-warning bg-opacity-10 text-warning rounded-circle p-3">
                            <i class="fas fa-clock fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card h-100 border-start border-info border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Students with Arrears</p>
                            <h3 class="mb-0 text-info">{{ number_format($dashboardStats['students_with_arrears'] ?? 0) }}</h3>
                        </div>
                        <div class="stat-icon bg-info bg-opacity-10 text-info rounded-circle p-3">
                            <i class="fas fa-users fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card h-100 border-start border-success border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Collection Rate</p>
                            <h3 class="mb-0 text-success">{{ number_format($dashboardStats['collection_rate'] ?? 0, 1) }}%</h3>
                        </div>
                        <div class="stat-icon bg-success bg-opacity-10 text-success rounded-circle p-3">
                            <i class="fas fa-percentage fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Arrears by Age Summary -->
    @if(isset($dashboardStats['arrears_by_age']) && count($dashboardStats['arrears_by_age']) > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-chart-pie me-2 text-primary"></i>Arrears by Age (Days Overdue)</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @php
                            $ageColors = [
                                '0-30' => ['bg' => 'success', 'text' => '0-30 Days'],
                                '31-60' => ['bg' => 'warning', 'text' => '31-60 Days'],
                                '61-90' => ['bg' => 'orange', 'text' => '61-90 Days'],
                                '90+' => ['bg' => 'danger', 'text' => '90+ Days'],
                            ];
                        @endphp
                        @foreach($dashboardStats['arrears_by_age'] as $age => $data)
                        <div class="col-md-3 col-6 mb-3">
                            <div class="border rounded p-3 text-center h-100">
                                <span class="badge bg-{{ $ageColors[$age]['bg'] ?? 'secondary' }} mb-2">
                                    {{ $ageColors[$age]['text'] ?? $age }}
                                </span>
                                <h4 class="mb-1 text-{{ $ageColors[$age]['bg'] ?? 'secondary' }}">
                                    RM {{ number_format($data['amount'] ?? 0, 2) }}
                                </h4>
                                <small class="text-muted">{{ $data['count'] ?? 0 }} invoices</small>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-filter me-2 text-primary"></i>Filter Arrears</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('staff.arrears.index') }}" method="GET" class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">From Date</label>
                    <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">To Date</label>
                    <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Class</label>
                    <select class="form-select" name="class_id">
                        <option value="">All Classes</option>
                        @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                            {{ $class->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="status">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>Partial</option>
                        <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Min Days Overdue</label>
                    <input type="number" class="form-control" name="days_overdue_min" value="{{ request('days_overdue_min') }}" placeholder="0" min="0">
                </div>
                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="fas fa-search me-1"></i> Filter
                    </button>
                    <a href="{{ route('staff.arrears.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-redo"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <!-- Main Arrears Table -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-list me-2 text-primary"></i>Arrears List</h5>
                    <span class="badge bg-danger">
                        @if(is_object($report) && method_exists($report, 'total'))
                            {{ $report->total() }} records
                        @elseif(is_array($report) && isset($report['invoices']))
                            {{ count($report['invoices']) }} records
                        @else
                            0 records
                        @endif
                    </span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Invoice</th>
                                    <th>Student</th>
                                    <th class="text-end">Amount Due</th>
                                    <th class="text-center">Days Overdue</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $invoices = is_object($report) && method_exists($report, 'items') 
                                        ? $report 
                                        : (is_array($report) && isset($report['invoices']) ? $report['invoices'] : collect());
                                @endphp
                                @forelse($invoices as $invoice)
                                <tr>
                                    <td>
                                        <strong>{{ $invoice->invoice_number ?? 'N/A' }}</strong>
                                        <br><small class="text-muted">Due: {{ $invoice->due_date ? $invoice->due_date->format('d M Y') : '-' }}</small>
                                    </td>
                                    <td>
                                        @if($invoice->student && $invoice->student->user)
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-circle bg-primary text-white me-2">
                                                {{ strtoupper(substr($invoice->student->user->name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <strong>{{ $invoice->student->user->name }}</strong>
                                                <br><small class="text-muted">{{ $invoice->student->student_id ?? '' }}</small>
                                            </div>
                                        </div>
                                        @else
                                        <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <strong class="text-danger">RM {{ number_format($invoice->balance ?? ($invoice->total_amount - $invoice->paid_amount), 2) }}</strong>
                                        <br><small class="text-muted">of RM {{ number_format($invoice->total_amount ?? 0, 2) }}</small>
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $daysOverdue = $invoice->due_date ? max(0, now()->diffInDays($invoice->due_date, false) * -1) : 0;
                                            $overdueClass = $daysOverdue > 90 ? 'danger' : ($daysOverdue > 60 ? 'warning' : ($daysOverdue > 30 ? 'info' : 'secondary'));
                                        @endphp
                                        <span class="badge bg-{{ $overdueClass }}">
                                            {{ $daysOverdue }} days
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $statusColors = [
                                                'pending' => 'warning',
                                                'partial' => 'info',
                                                'overdue' => 'danger',
                                                'paid' => 'success'
                                            ];
                                        @endphp
                                        <span class="badge bg-{{ $statusColors[$invoice->status] ?? 'secondary' }}">
                                            {{ ucfirst($invoice->status ?? 'Unknown') }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @can('view-student-arrears')
                                        @if($invoice->student)
                                        <a href="{{ route('staff.arrears.student', $invoice->student) }}" 
                                           class="btn btn-sm btn-outline-primary" title="View Student Arrears">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @endif
                                        @endcan
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="fas fa-check-circle fa-3x mb-3 text-success"></i>
                                            <p class="mb-0">No arrears found.</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if(is_object($report) && method_exists($report, 'hasPages') && $report->hasPages())
                <div class="card-footer bg-white">
                    {{ $report->withQueryString()->links() }}
                </div>
                @endif
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Critical Arrears -->
            <div class="card border-danger mb-4">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="fas fa-exclamation-circle me-2"></i>Critical Arrears</h5>
                </div>
                <div class="card-body p-0">
                    @if($criticalArrears && count($criticalArrears) > 0)
                    <ul class="list-group list-group-flush">
                        @foreach($criticalArrears->take(8) as $invoice)
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    @if($invoice->student && $invoice->student->user)
                                    <strong>{{ Str::limit($invoice->student->user->name, 20) }}</strong>
                                    @else
                                    <strong class="text-muted">Unknown</strong>
                                    @endif
                                    <br>
                                    <small class="text-muted">
                                        {{ $invoice->invoice_number ?? 'N/A' }} |
                                        @if($invoice->due_date)
                                        {{ max(0, now()->diffInDays($invoice->due_date, false) * -1) }} days
                                        @endif
                                    </small>
                                </div>
                                <span class="badge bg-danger">
                                    RM {{ number_format($invoice->balance ?? ($invoice->total_amount - $invoice->paid_amount), 2) }}
                                </span>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                    @else
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-smile fa-2x mb-2"></i>
                        <p class="mb-0">No critical arrears</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Quick Links -->
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-link me-2 text-primary"></i>Quick Links</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @can('view-arrears')
                        <a href="{{ route('staff.arrears.students-list') }}" class="btn btn-outline-primary">
                            <i class="fas fa-users me-2"></i>Students with Arrears
                        </a>
                        @endcan
                        @can('view-due-reports')
                        <a href="{{ route('staff.arrears.due-report') }}" class="btn btn-outline-warning">
                            <i class="fas fa-calendar-times me-2"></i>Due Report (Next 30 Days)
                        </a>
                        @endcan
                    </div>
                </div>
            </div>

            <!-- Due This Week -->
            <div class="card mt-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-calendar-week me-2 text-warning"></i>Upcoming Dues</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border rounded p-3">
                                <small class="text-muted d-block">This Week</small>
                                <strong class="text-warning h5">RM {{ number_format($dashboardStats['due_this_week'] ?? 0, 2) }}</strong>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-3">
                                <small class="text-muted d-block">Next Week</small>
                                <strong class="text-info h5">RM {{ number_format($dashboardStats['due_next_week'] ?? 0, 2) }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.avatar-circle {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 14px;
}
.bg-orange {
    background-color: #fd7e14 !important;
    color: white;
}
.text-orange {
    color: #fd7e14 !important;
}
.stat-card {
    transition: transform 0.2s;
}
.stat-card:hover {
    transform: translateY(-2px);
}
</style>
@endsection
