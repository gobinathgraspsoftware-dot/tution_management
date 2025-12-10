@extends('layouts.app')

@section('title', 'Installment Plans')
@section('page-title', 'Installment Plans Management')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="fas fa-calendar-alt me-2"></i> Installment Plans</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.invoices.index') }}">Invoices</a></li>
                <li class="breadcrumb-item active">Installments</li>
            </ol>
        </nav>
    </div>
    <div>
        @can('manage-installments')
        <a href="{{ route('admin.installments.overdue') }}" class="btn btn-outline-danger me-2">
            <i class="fas fa-exclamation-triangle me-1"></i> Overdue ({{ $statistics['total_installments_overdue'] ?? 0 }})
        </a>
        <a href="{{ route('admin.installments.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> New Plan
        </a>
        @endcan
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-white-50">Active Plans</h6>
                        <h2 class="mb-0">{{ $statistics['total_active_plans'] ?? 0 }}</h2>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-file-invoice-dollar fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-warning text-dark">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="opacity-75">Pending Installments</h6>
                        <h2 class="mb-0">{{ $statistics['total_installments_pending'] ?? 0 }}</h2>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-clock fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-danger text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-white-50">Overdue</h6>
                        <h2 class="mb-0">{{ $statistics['total_installments_overdue'] ?? 0 }}</h2>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-exclamation-circle fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-white-50">Total Amount Due</h6>
                        <h2 class="mb-0">RM {{ number_format($statistics['total_amount_due'] ?? 0, 2) }}</h2>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-money-bill-wave fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Stats -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-calendar-day text-primary me-2"></i> Due Today</span>
                    <span class="badge bg-primary fs-6">{{ $statistics['installments_due_today'] ?? 0 }}</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-calendar-week text-warning me-2"></i> Due This Week</span>
                    <span class="badge bg-warning text-dark fs-6">{{ $statistics['installments_due_this_week'] ?? 0 }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('admin.installments.index') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Invoice # or Student name..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>Partial</option>
                    <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Student</label>
                <select name="student_id" class="form-select">
                    <option value="">All Students</option>
                    @foreach($students as $student)
                        <option value="{{ $student->id }}" {{ request('student_id') == $student->id ? 'selected' : '' }}>
                            {{ $student->user->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search me-1"></i> Filter
                </button>
                <a href="{{ route('admin.installments.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-1"></i> Clear
                </a>
                <a href="{{ route('admin.installments.export') }}" class="btn btn-outline-success">
                    <i class="fas fa-download me-1"></i> Export
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Installment Plans List -->
<div class="card">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="fas fa-list me-2"></i> Installment Plans</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Invoice #</th>
                        <th>Student</th>
                        <th>Package</th>
                        <th>Total Amount</th>
                        <th>Paid</th>
                        <th>Balance</th>
                        <th>Progress</th>
                        <th>Next Due</th>
                        <th>Status</th>
                        <th width="120">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoicesWithInstallments as $invoice)
                        @php
                            $totalAmount = $invoice->installments->sum('amount');
                            $totalPaid = $invoice->installments->sum('paid_amount');
                            $progress = $totalAmount > 0 ? ($totalPaid / $totalAmount) * 100 : 0;
                            $nextDue = $invoice->installments->where('status', '!=', 'paid')->sortBy('due_date')->first();
                            $overdueCount = $invoice->installments->where('status', 'overdue')->count();
                        @endphp
                        <tr>
                            <td>
                                <a href="{{ route('admin.invoices.show', $invoice) }}" class="fw-bold text-primary">
                                    {{ $invoice->invoice_number }}
                                </a>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="user-avatar me-2" style="width:30px;height:30px;font-size:0.8rem;">
                                        {{ substr($invoice->student->user->name ?? 'N', 0, 1) }}
                                    </div>
                                    <div>
                                        <strong>{{ $invoice->student->user->name ?? 'N/A' }}</strong>
                                        <br><small class="text-muted">{{ $invoice->student->student_id ?? '' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $invoice->enrollment->package->name ?? 'N/A' }}</td>
                            <td>RM {{ number_format($totalAmount, 2) }}</td>
                            <td class="text-success">RM {{ number_format($totalPaid, 2) }}</td>
                            <td class="text-danger">RM {{ number_format($totalAmount - $totalPaid, 2) }}</td>
                            <td style="min-width: 150px;">
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $progress }}%"></div>
                                </div>
                                <small class="text-muted">{{ $invoice->installments->where('status', 'paid')->count() }}/{{ $invoice->installments->count() }} paid</small>
                            </td>
                            <td>
                                @if($nextDue)
                                    <span class="{{ $nextDue->due_date->isPast() ? 'text-danger' : '' }}">
                                        {{ $nextDue->due_date->format('d M Y') }}
                                    </span>
                                    <br>
                                    <small class="text-muted">
                                        #{{ $nextDue->installment_number }} - RM {{ number_format($nextDue->balance, 2) }}
                                    </small>
                                @else
                                    <span class="text-success">Completed</span>
                                @endif
                            </td>
                            <td>
                                @if($overdueCount > 0)
                                    <span class="badge bg-danger">{{ $overdueCount }} Overdue</span>
                                @elseif($invoice->status == 'paid')
                                    <span class="badge bg-success">Completed</span>
                                @else
                                    <span class="badge bg-warning text-dark">In Progress</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.installments.show', $invoice) }}" class="btn btn-outline-info" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.installments.student-history', $invoice->student) }}" class="btn btn-outline-secondary" title="Student History">
                                        <i class="fas fa-history"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-4">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No installment plans found.</p>
                                <a href="{{ route('admin.installments.create') }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus me-1"></i> Create First Plan
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($invoicesWithInstallments->hasPages())
        <div class="d-flex justify-content-between align-items-center mt-4">
            <div class="text-muted">
                Showing {{ $invoicesWithInstallments->firstItem() ?? 0 }} to {{ $invoicesWithInstallments->lastItem() ?? 0 }} of {{ $invoicesWithInstallments->total() }} entries
            </div>
            {{ $invoicesWithInstallments->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
.user-avatar {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
}
</style>
@endpush
