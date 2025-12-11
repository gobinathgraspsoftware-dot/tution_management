@extends('layouts.app')

@section('title', 'Teacher Payslips')
@section('page-title', 'Teacher Payslips')

@section('content')
<div class="page-header">
    <h1>
        <i class="fas fa-file-invoice-dollar me-2"></i> Teacher Payslips
    </h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Teacher Payslips</li>
        </ol>
    </nav>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #e3f2fd; color: #2196f3;">
                <i class="fas fa-file-invoice"></i>
            </div>
            <div class="stat-details">
                <h3 class="mb-0">{{ $stats['total_payslips'] }}</h3>
                <p class="text-muted mb-0">Total Payslips</p>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #fff3e0; color: #ff9800;">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-details">
                <h3 class="mb-0">{{ $stats['draft'] }}</h3>
                <p class="text-muted mb-0">Draft</p>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #e8f5e9; color: #4caf50;">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-details">
                <h3 class="mb-0">{{ $stats['paid'] }}</h3>
                <p class="text-muted mb-0">Paid</p>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #f3e5f5; color: #9c27b0;">
                <i class="fas fa-money-bill-wave"></i>
            </div>
            <div class="stat-details">
                <h3 class="mb-0">RM {{ number_format($stats['total_amount'], 2) }}</h3>
                <p class="text-muted mb-0">Total Paid</p>
            </div>
        </div>
    </div>
</div>

<!-- Filters and Actions -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.teacher-payslips.index') }}" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Teacher</label>
                <select name="teacher_id" class="form-select">
                    <option value="">All Teachers</option>
                    @foreach($teachers as $teacher)
                        <option value="{{ $teacher->id }}" {{ request('teacher_id') == $teacher->id ? 'selected' : '' }}>
                            {{ $teacher->user->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label">Month</label>
                <select name="month" class="form-select">
                    <option value="">All Months</option>
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                            {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                        </option>
                    @endfor
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label">Year</label>
                <select name="year" class="form-select">
                    <option value="">All Years</option>
                    @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                        <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>
                            {{ $y }}
                        </option>
                    @endfor
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Payslip number..." value="{{ request('search') }}">
            </div>

            <div class="col-md-12">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Filter
                </button>
                <a href="{{ route('admin.teacher-payslips.index') }}" class="btn btn-secondary">
                    <i class="fas fa-redo"></i> Reset
                </a>
                @can('generate-teacher-payslip')
                <a href="{{ route('admin.teacher-payslips.create') }}" class="btn btn-success">
                    <i class="fas fa-plus"></i> Generate Payslip
                </a>
                @endcan
                <a href="{{ route('admin.teacher-payslips.export', request()->all()) }}" class="btn btn-info">
                    <i class="fas fa-file-excel"></i> Export
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Payslips Table -->
<div class="card">
    <div class="card-body">
        @if($payslips->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Payslip No.</th>
                        <th>Teacher</th>
                        <th>Period</th>
                        <th>Pay Type</th>
                        <th>Hours/Classes</th>
                        <th>Net Pay</th>
                        <th>Status</th>
                        <th>Payment Date</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($payslips as $payslip)
                    <tr>
                        <td>
                            <strong>{{ $payslip->payslip_number }}</strong>
                        </td>
                        <td>
                            <div>{{ $payslip->teacher->user->name }}</div>
                            <small class="text-muted">{{ $payslip->teacher->user->email }}</small>
                        </td>
                        <td>
                            <div>{{ $payslip->period_start->format('d M Y') }}</div>
                            <small class="text-muted">to {{ $payslip->period_end->format('d M Y') }}</small>
                        </td>
                        <td>
                            <span class="badge bg-secondary">
                                {{ ucfirst(str_replace('_', ' ', $payslip->teacher->pay_type)) }}
                            </span>
                        </td>
                        <td>
                            @if($payslip->teacher->pay_type == 'hourly')
                                {{ number_format($payslip->total_hours, 2) }} hrs
                            @else
                                {{ $payslip->total_classes }} classes
                            @endif
                        </td>
                        <td>
                            <strong class="text-success">RM {{ number_format($payslip->net_pay, 2) }}</strong>
                        </td>
                        <td>
                            @if($payslip->status == 'draft')
                                <span class="badge bg-warning text-dark">Draft</span>
                            @elseif($payslip->status == 'approved')
                                <span class="badge bg-info">Approved</span>
                            @else
                                <span class="badge bg-success">Paid</span>
                            @endif
                        </td>
                        <td>
                            {{ $payslip->payment_date ? $payslip->payment_date->format('d M Y') : '-' }}
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                @can('view-teacher-salary')
                                <a href="{{ route('admin.teacher-payslips.show', $payslip) }}" class="btn btn-outline-info" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @endcan
                                <a href="{{ route('admin.teacher-payslips.print', $payslip) }}" class="btn btn-outline-primary" title="Print" target="_blank">
                                    <i class="fas fa-print"></i>
                                </a>
                                @can('manage-teacher-salary')
                                @if($payslip->status == 'draft')
                                <button type="button" class="btn btn-outline-danger" onclick="deletePayslip({{ $payslip->id }})" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endif
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $payslips->links() }}
        </div>
        @else
        <div class="text-center py-5">
            <i class="fas fa-file-invoice fa-3x text-muted mb-3"></i>
            <p class="text-muted">No payslips found.</p>
            @can('generate-teacher-payslip')
            <a href="{{ route('admin.teacher-payslips.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Generate First Payslip
            </a>
            @endcan
        </div>
        @endif
    </div>
</div>

<!-- Delete Form -->
<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endsection

@push('scripts')
<script>
function deletePayslip(id) {
    if (confirm('Are you sure you want to delete this draft payslip?')) {
        const form = document.getElementById('deleteForm');
        form.action = `/admin/teacher-payslips/${id}`;
        form.submit();
    }
}
</script>
@endpush
