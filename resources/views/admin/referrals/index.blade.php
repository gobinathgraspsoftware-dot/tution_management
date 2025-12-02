@extends('layouts.app')

@section('title', 'Referral Management')
@section('page-title', 'Referral Management')

@section('content')
<div class="page-header">
    <div>
        <h1><i class="fas fa-users me-2"></i> Referral Management</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Referrals</li>
            </ol>
        </nav>
    </div>
    <div>
        <a href="{{ route('admin.referrals.vouchers') }}" class="btn btn-info">
            <i class="fas fa-ticket-alt me-1"></i> Manage Vouchers
        </a>
        <a href="{{ route('admin.referrals.export') }}" class="btn btn-success">
            <i class="fas fa-download me-1"></i> Export
        </a>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-2">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #e3f2fd; color: #2196f3;">
                <i class="fas fa-list"></i>
            </div>
            <div class="stat-details">
                <h3 class="mb-0">{{ $stats['total'] }}</h3>
                <p class="text-muted mb-0">Total Referrals</p>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #fff3e0; color: #ff9800;">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-details">
                <h3 class="mb-0">{{ $stats['pending'] }}</h3>
                <p class="text-muted mb-0">Pending</p>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #e8f5e9; color: #4caf50;">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-details">
                <h3 class="mb-0">{{ $stats['completed'] }}</h3>
                <p class="text-muted mb-0">Completed</p>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #f3e5f5; color: #9c27b0;">
                <i class="fas fa-ticket-alt"></i>
            </div>
            <div class="stat-details">
                <h3 class="mb-0">{{ $stats['total_vouchers_issued'] }}</h3>
                <p class="text-muted mb-0">Vouchers Issued</p>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #e8f5e9; color: #4caf50;">
                <i class="fas fa-receipt"></i>
            </div>
            <div class="stat-details">
                <h3 class="mb-0">{{ $stats['total_vouchers_used'] }}</h3>
                <p class="text-muted mb-0">Vouchers Used</p>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #fff3e0; color: #ff9800;">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="stat-details">
                <h3 class="mb-0">RM {{ number_format($stats['total_voucher_value'], 2) }}</h3>
                <p class="text-muted mb-0">Active Value</p>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('admin.referrals.index') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" placeholder="Search referrer, referred, or code..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="date_from" class="form-control" placeholder="From Date" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <input type="date" name="date_to" class="form-control" placeholder="To Date" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary"><i class="fas fa-search me-1"></i> Filter</button>
                <a href="{{ route('admin.referrals.index') }}" class="btn btn-secondary"><i class="fas fa-redo me-1"></i> Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Referrals Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Referral Code</th>
                        <th>Referrer</th>
                        <th>Referred Student</th>
                        <th>Status</th>
                        <th>Vouchers</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($referrals as $referral)
                    <tr>
                        <td>{{ $referral->id }}</td>
                        <td><code>{{ $referral->referral_code }}</code></td>
                        <td>
                            @if($referral->referrer)
                                <a href="{{ route('admin.students.profile', $referral->referrer) }}">
                                    {{ $referral->referrer->user->name ?? 'N/A' }}
                                </a>
                                <br><small class="text-muted">{{ $referral->referrer->student_id }}</small>
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                        <td>
                            @if($referral->referred)
                                <a href="{{ route('admin.students.profile', $referral->referred) }}">
                                    {{ $referral->referred->user->name ?? 'N/A' }}
                                </a>
                                <br><small class="text-muted">{{ $referral->referred->student_id }}</small>
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                        <td>
                            @if($referral->status === 'completed')
                                <span class="badge bg-success">Completed</span>
                            @elseif($referral->status === 'pending')
                                <span class="badge bg-warning">Pending</span>
                            @elseif($referral->status === 'expired')
                                <span class="badge bg-secondary">Expired</span>
                            @elseif($referral->status === 'cancelled')
                                <span class="badge bg-danger">Cancelled</span>
                            @endif
                        </td>
                        <td>
                            @if($referral->vouchers->count() > 0)
                                <span class="badge bg-info">{{ $referral->vouchers->count() }} issued</span>
                            @else
                                <span class="text-muted">None</span>
                            @endif
                        </td>
                        <td>{{ $referral->created_at->format('d M Y') }}</td>
                        <td>
                            <a href="{{ route('admin.referrals.show', $referral) }}" class="btn btn-sm btn-info" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            @if($referral->status === 'pending')
                                <form action="{{ route('admin.referrals.complete', $referral) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success" title="Complete & Generate Voucher" onclick="return confirm('Complete this referral and generate RM50 voucher?')">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </form>
                                <form action="{{ route('admin.referrals.cancel', $referral) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-danger" title="Cancel" onclick="return confirm('Cancel this referral?')">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">No referrals found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $referrals->links() }}
    </div>
</div>
@endsection
