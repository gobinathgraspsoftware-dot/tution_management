@extends('layouts.app')

@section('title', 'Teacher Payslips')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Teacher Payslips</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Teacher Payslips</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.teacher-payslips.export', request()->query()) }}" class="btn btn-outline-success">
                <i class="fas fa-file-excel me-1"></i> Export
            </a>
            <a href="{{ route('admin.teacher-payslips.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Generate Payslip
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card bg-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-white-50">Total Payslips</h6>
                            <h3 class="mb-0">{{ number_format($stats['total_payslips']) }}</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-file-invoice-dollar fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card bg-warning text-dark h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-dark-50">Draft</h6>
                            <h3 class="mb-0">{{ number_format($stats['draft']) }}</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-edit fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card bg-info text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-white-50">Approved</h6>
                            <h3 class="mb-0">{{ number_format($stats['approved']) }}</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-check-circle fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-white-50">Total Paid</h6>
                            <h3 class="mb-0">RM {{ number_format($stats['total_amount'], 2) }}</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-money-bill-wave fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
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
                        <option value="">All</option>
                        @for($i = 1; $i <= 12; $i++)
                            <option value="{{ $i }}" {{ request('month') == $i ? 'selected' : '' }}>
                                {{ date('F', mktime(0, 0, 0, $i, 1)) }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Year</label>
                    <select name="year" class="form-select">
                        <option value="">All</option>
                        @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                            <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Payslip #" value="{{ request('search') }}">
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Payslips Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Payslip #</th>
                            <th>Teacher</th>
                            <th>Period</th>
                            <th class="text-end">Basic Pay</th>
                            <th class="text-end">EPF</th>
                            <th class="text-end">SOCSO</th>
                            <th class="text-end">Net Pay</th>
                            <th>Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payslips as $payslip)
                        <tr>
                            <td>
                                <a href="{{ route('admin.teacher-payslips.show', $payslip) }}" class="fw-bold text-decoration-none">
                                    {{ $payslip->payslip_number }}
                                </a>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm me-2 bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                                        {{ strtoupper(substr($payslip->teacher->user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-medium">{{ $payslip->teacher->user->name }}</div>
                                        <small class="text-muted">{{ $payslip->teacher->teacher_id }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <small>{{ $payslip->period_start->format('d M') }} - {{ $payslip->period_end->format('d M Y') }}</small>
                            </td>
                            <td class="text-end">RM {{ number_format($payslip->basic_pay, 2) }}</td>
                            <td class="text-end">
                                @if($payslip->epf_employee > 0)
                                    <span class="text-danger">RM {{ number_format($payslip->epf_employee, 2) }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if($payslip->socso_employee > 0)
                                    <span class="text-danger">RM {{ number_format($payslip->socso_employee, 2) }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-end fw-bold text-success">RM {{ number_format($payslip->net_pay, 2) }}</td>
                            <td>
                                @if($payslip->status == 'draft')
                                    <span class="badge bg-warning">Draft</span>
                                @elseif($payslip->status == 'approved')
                                    <span class="badge bg-info">Approved</span>
                                @elseif($payslip->status == 'paid')
                                    <span class="badge bg-success">Paid</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.teacher-payslips.show', $payslip) }}" class="btn btn-outline-primary" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.teacher-payslips.print', $payslip) }}" class="btn btn-outline-secondary" title="Print" target="_blank">
                                        <i class="fas fa-print"></i>
                                    </a>
                                    @if($payslip->status == 'draft')
                                    <form action="{{ route('admin.teacher-payslips.destroy', $payslip) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this draft payslip?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-file-invoice fa-3x mb-3"></i>
                                    <p>No payslips found</p>
                                    <a href="{{ route('admin.teacher-payslips.create') }}" class="btn btn-primary btn-sm">
                                        <i class="fas fa-plus me-1"></i> Generate First Payslip
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted">
                    Showing {{ $payslips->firstItem() ?? 0 }} to {{ $payslips->lastItem() ?? 0 }} of {{ $payslips->total() }} entries
                </div>
                {{ $payslips->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
