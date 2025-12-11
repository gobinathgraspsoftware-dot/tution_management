@extends('layouts.app')

@section('title', 'My Payslips')
@section('page-title', 'My Payslips')

@section('content')
<div class="page-header">
    <h1>
        <i class="fas fa-file-invoice-dollar me-2"></i> My Payslips
    </h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('teacher.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">My Payslips</li>
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
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-details">
                <h3 class="mb-0">{{ $stats['approved'] }}</h3>
                <p class="text-muted mb-0">Approved</p>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #e8f5e9; color: #4caf50;">
                <i class="fas fa-money-check"></i>
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
                <h3 class="mb-0">RM {{ number_format($stats['total_earned'], 2) }}</h3>
                <p class="text-muted mb-0">Total Earned</p>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('teacher.payslips.index') }}" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Year</label>
                <select name="year" class="form-select">
                    <option value="">All Years</option>
                    @foreach($years as $year)
                        <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>
                            {{ $year }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Payslip number..." value="{{ request('search') }}">
            </div>

            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search"></i> Filter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Payslips List -->
<div class="card">
    <div class="card-body">
        @if($payslips->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Payslip No.</th>
                        <th>Period</th>
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
                            <div>{{ $payslip->period_start->format('d M Y') }}</div>
                            <small class="text-muted">to {{ $payslip->period_end->format('d M Y') }}</small>
                        </td>
                        <td>
                            <div>{{ number_format($payslip->total_hours, 2) }} hours</div>
                            <small class="text-muted">{{ $payslip->total_classes }} classes</small>
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
                                <a href="{{ route('teacher.payslips.show', $payslip) }}" class="btn btn-outline-info" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('teacher.payslips.print', $payslip) }}" class="btn btn-outline-primary" title="Print" target="_blank">
                                    <i class="fas fa-print"></i>
                                </a>
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
        </div>
        @endif
    </div>
</div>
@endsection
