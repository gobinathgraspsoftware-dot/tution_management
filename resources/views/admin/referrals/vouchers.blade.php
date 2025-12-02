@extends('layouts.app')

@section('title', 'Voucher Management')
@section('page-title', 'Voucher Management')

@section('content')
<div class="page-header">
    <div>
        <h1><i class="fas fa-ticket-alt me-2"></i> Voucher Management</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.referrals.index') }}">Referrals</a></li>
                <li class="breadcrumb-item active">Vouchers</li>
            </ol>
        </nav>
    </div>
    <div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#generateVoucherModal">
            <i class="fas fa-plus me-1"></i> Generate Voucher
        </button>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-2">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #e3f2fd; color: #2196f3;">
                <i class="fas fa-ticket-alt"></i>
            </div>
            <div class="stat-details">
                <h3 class="mb-0">{{ $stats['total'] }}</h3>
                <p class="text-muted mb-0">Total Vouchers</p>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #e8f5e9; color: #4caf50;">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-details">
                <h3 class="mb-0">{{ $stats['active'] }}</h3>
                <p class="text-muted mb-0">Active</p>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #f3e5f5; color: #9c27b0;">
                <i class="fas fa-receipt"></i>
            </div>
            <div class="stat-details">
                <h3 class="mb-0">{{ $stats['used'] }}</h3>
                <p class="text-muted mb-0">Used</p>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #ffebee; color: #f44336;">
                <i class="fas fa-times-circle"></i>
            </div>
            <div class="stat-details">
                <h3 class="mb-0">{{ $stats['expired'] }}</h3>
                <p class="text-muted mb-0">Expired</p>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #e8f5e9; color: #4caf50;">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="stat-details">
                <h3 class="mb-0">RM {{ number_format($stats['active_value'], 2) }}</h3>
                <p class="text-muted mb-0">Active Value</p>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #fff3e0; color: #ff9800;">
                <i class="fas fa-money-bill"></i>
            </div>
            <div class="stat-details">
                <h3 class="mb-0">RM {{ number_format($stats['used_value'], 2) }}</h3>
                <p class="text-muted mb-0">Redeemed Value</p>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('admin.referrals.vouchers') }}" method="GET" class="row g-3">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Search voucher code or student..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="used" {{ request('status') === 'used' ? 'selected' : '' }}>Used</option>
                    <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary"><i class="fas fa-search me-1"></i> Filter</button>
                <a href="{{ route('admin.referrals.vouchers') }}" class="btn btn-secondary"><i class="fas fa-redo me-1"></i> Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Vouchers Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Voucher Code</th>
                        <th>Student</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Expires At</th>
                        <th>Used On</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vouchers as $voucher)
                    <tr>
                        <td><code class="fs-6">{{ $voucher->voucher_code }}</code></td>
                        <td>
                            @if($voucher->student)
                                <a href="{{ route('admin.students.profile', $voucher->student) }}">
                                    {{ $voucher->student->user->name ?? 'N/A' }}
                                </a>
                                <br><small class="text-muted">{{ $voucher->student->student_id }}</small>
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                        <td><strong>RM {{ number_format($voucher->amount, 2) }}</strong></td>
                        <td>
                            @if($voucher->status === 'active')
                                <span class="badge bg-success">Active</span>
                            @elseif($voucher->status === 'used')
                                <span class="badge bg-info">Used</span>
                            @else
                                <span class="badge bg-secondary">Expired</span>
                            @endif
                        </td>
                        <td>
                            @if($voucher->expires_at)
                                {{ $voucher->expires_at->format('d M Y') }}
                                @if($voucher->expires_at < now() && $voucher->status === 'active')
                                    <br><small class="text-danger">Overdue</small>
                                @elseif($voucher->expires_at->diffInDays(now()) <= 7 && $voucher->status === 'active')
                                    <br><small class="text-warning">Expiring soon</small>
                                @endif
                            @else
                                <span class="text-muted">No expiry</span>
                            @endif
                        </td>
                        <td>
                            @if($voucher->used_at)
                                {{ $voucher->used_at->format('d M Y') }}
                                @if($voucher->usedOnInvoice)
                                    <br><small class="text-muted">Invoice: {{ $voucher->usedOnInvoice->invoice_number }}</small>
                                @endif
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>{{ $voucher->created_at->format('d M Y') }}</td>
                        <td>
                            @if($voucher->status === 'active')
                                <form action="{{ route('admin.referrals.vouchers.expire', $voucher) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-warning" title="Expire" onclick="return confirm('Expire this voucher?')">
                                        <i class="fas fa-ban"></i>
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">No vouchers found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $vouchers->links() }}
    </div>
</div>

<!-- Generate Voucher Modal -->
<div class="modal fade" id="generateVoucherModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.referrals.vouchers.generate') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus me-2"></i> Generate Voucher</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Student <span class="text-danger">*</span></label>
                        <select name="student_id" class="form-select" required>
                            <option value="">Select Student...</option>
                            @foreach(\App\Models\Student::approved()->with('user')->get() as $student)
                                <option value="{{ $student->id }}">{{ $student->user->name }} ({{ $student->student_id }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount (RM) <span class="text-danger">*</span></label>
                        <input type="number" name="amount" class="form-control" value="50" min="1" max="500" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Expires At</label>
                        <input type="date" name="expires_at" class="form-control" min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                        <small class="text-muted">Leave empty for 90 days validity</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control" rows="2" required placeholder="Reason for issuing this voucher..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Generate Voucher</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
