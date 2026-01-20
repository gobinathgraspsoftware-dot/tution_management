@extends('layouts.app')

@section('title', 'My Payslips')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">My Payslips</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('teacher.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Payslips</li>
                </ol>
            </nav>
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
                            <h6 class="text-white-50">Paid</h6>
                            <h3 class="mb-0">{{ number_format($stats['paid']) }}</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-money-bill-wave fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card bg-dark text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-white-50">Total Earned</h6>
                            <h3 class="mb-0">RM {{ number_format($stats['total_earned'], 2) }}</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-wallet fa-2x opacity-50"></i>
                        </div>
                    </div>
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
                            <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>{{ $year }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Payslip Number..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-1"></i> Search
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Payslips List -->
    <div class="card">
        <div class="card-header">
            <i class="fas fa-list me-2"></i> Payslip History
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Payslip #</th>
                            <th>Period</th>
                            <th class="text-end">Basic Pay</th>
                            <th class="text-end">Deductions</th>
                            <th class="text-end">Net Pay</th>
                            <th>Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payslips as $payslip)
                        <tr>
                            <td>
                                <a href="{{ route('teacher.payslips.show', $payslip) }}" class="fw-bold text-decoration-none">
                                    {{ $payslip->payslip_number }}
                                </a>
                            </td>
                            <td>
                                <div>{{ $payslip->period_start->format('d M') }} - {{ $payslip->period_end->format('d M Y') }}</div>
                                <small class="text-muted">{{ $payslip->total_hours }} hrs | {{ $payslip->total_classes }} classes</small>
                            </td>
                            <td class="text-end">RM {{ number_format($payslip->basic_pay, 2) }}</td>
                            <td class="text-end text-danger">
                                RM {{ number_format($payslip->epf_employee + $payslip->socso_employee + $payslip->deductions, 2) }}
                            </td>
                            <td class="text-end fw-bold text-success">RM {{ number_format($payslip->net_pay, 2) }}</td>
                            <td>
                                @if($payslip->status == 'draft')
                                    <span class="badge bg-warning">Draft</span>
                                @elseif($payslip->status == 'approved')
                                    <span class="badge bg-info">Approved</span>
                                @elseif($payslip->status == 'paid')
                                    <span class="badge bg-success">Paid</span>
                                    @if($payslip->payment_date)
                                        <br><small class="text-muted">{{ $payslip->payment_date->format('d M Y') }}</small>
                                    @endif
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('teacher.payslips.show', $payslip) }}" class="btn btn-outline-primary" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('teacher.payslips.print', $payslip) }}" class="btn btn-outline-secondary" title="Print" target="_blank">
                                        <i class="fas fa-print"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-file-invoice fa-3x mb-3 d-block"></i>
                                    <p class="mb-0">No payslips found</p>
                                    <small>Your payslips will appear here once generated</small>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($payslips->hasPages())
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted">
                    Showing {{ $payslips->firstItem() ?? 0 }} to {{ $payslips->lastItem() ?? 0 }} of {{ $payslips->total() }} entries
                </div>
                {{ $payslips->links() }}
            </div>
            @endif
        </div>
    </div>

    <!-- Helpful Info -->
    <div class="card mt-4">
        <div class="card-header">
            <i class="fas fa-info-circle me-2"></i> Information
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <h6><i class="fas fa-warning text-warning me-1"></i> Draft</h6>
                    <p class="text-muted small mb-0">Payslip is being prepared and not yet finalized.</p>
                </div>
                <div class="col-md-4">
                    <h6><i class="fas fa-check text-info me-1"></i> Approved</h6>
                    <p class="text-muted small mb-0">Payslip has been approved and pending payment.</p>
                </div>
                <div class="col-md-4">
                    <h6><i class="fas fa-check-double text-success me-1"></i> Paid</h6>
                    <p class="text-muted small mb-0">Payment has been processed to your bank account.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
