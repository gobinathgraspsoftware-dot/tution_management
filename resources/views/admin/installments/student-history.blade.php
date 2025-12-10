@extends('layouts.app')

@section('title', 'Student Installment History')
@section('page-title', 'Student Installment History')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="fas fa-history me-2"></i> Installment History</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.installments.index') }}">Installments</a></li>
                <li class="breadcrumb-item active">{{ $student->user->name ?? 'Student' }}</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('admin.installments.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back to List
    </a>
</div>

<div class="row">
    <!-- Student Profile Card -->
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-user-graduate me-2"></i> Student Profile</h5>
            </div>
            <div class="card-body text-center">
                <div class="user-avatar mx-auto mb-3" style="width:80px;height:80px;font-size:2rem;">
                    {{ substr($student->user->name ?? 'S', 0, 1) }}
                </div>
                <h4>{{ $student->user->name ?? 'N/A' }}</h4>
                <p class="text-muted mb-3">{{ $student->student_id ?? '' }}</p>

                <div class="d-flex justify-content-around text-center mb-3">
                    <div>
                        <h5 class="mb-0">{{ count($history) }}</h5>
                        <small class="text-muted">Plans</small>
                    </div>
                    <div>
                        @php
                            $totalPaid = collect($history)->sum(fn($h) => $h['summary']['total_paid'] ?? 0);
                        @endphp
                        <h5 class="mb-0 text-success">RM {{ number_format($totalPaid, 2) }}</h5>
                        <small class="text-muted">Total Paid</small>
                    </div>
                    <div>
                        @php
                            $totalBalance = collect($history)->sum(fn($h) => $h['summary']['total_balance'] ?? 0);
                        @endphp
                        <h5 class="mb-0 {{ $totalBalance > 0 ? 'text-danger' : 'text-success' }}">RM {{ number_format($totalBalance, 2) }}</h5>
                        <small class="text-muted">Balance</small>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-light">
                <div class="row text-center">
                    <div class="col-6">
                        <a href="{{ route('admin.students.show', $student) }}" class="btn btn-sm btn-outline-primary w-100">
                            <i class="fas fa-eye me-1"></i> View Profile
                        </a>
                    </div>
                    <div class="col-6">
                        @if(Route::has('admin.arrears.student'))
                        <a href="{{ route('admin.arrears.student', $student) }}" class="btn btn-sm btn-outline-danger w-100">
                            <i class="fas fa-exclamation-circle me-1"></i> Arrears
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Info -->
        <div class="card mt-4">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0"><i class="fas fa-address-card me-2"></i> Contact Information</h6>
            </div>
            <div class="card-body">
                <p class="mb-2"><i class="fas fa-phone text-muted me-2"></i> {{ $student->user->phone ?? 'N/A' }}</p>
                <p class="mb-2"><i class="fas fa-envelope text-muted me-2"></i> {{ $student->user->email ?? 'N/A' }}</p>
                @if($student->parent)
                    <hr>
                    <small class="text-muted d-block mb-2">Parent/Guardian:</small>
                    <p class="mb-1"><strong>{{ $student->parent->user->name ?? 'N/A' }}</strong></p>
                    <p class="mb-1"><i class="fas fa-phone text-muted me-2"></i> {{ $student->parent->user->phone ?? 'N/A' }}</p>
                    <p class="mb-0"><i class="fas fa-envelope text-muted me-2"></i> {{ $student->parent->user->email ?? 'N/A' }}</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Installment History -->
    <div class="col-lg-8">
        @forelse($history as $item)
            @php
                $invoice = $item['invoice'];
                $summary = $item['summary'];
            @endphp
            <div class="card mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">
                            <a href="{{ route('admin.installments.show', $invoice) }}" class="text-primary">
                                {{ $invoice->invoice_number }}
                            </a>
                        </h5>
                        <small class="text-muted">{{ $invoice->enrollment->package->name ?? 'N/A' }}</small>
                    </div>
                    <div class="text-end">
                        @if($summary['completion_percentage'] == 100)
                            <span class="badge bg-success fs-6">Completed</span>
                        @elseif($summary['overdue_installments'] > 0)
                            <span class="badge bg-danger fs-6">{{ $summary['overdue_installments'] }} Overdue</span>
                        @else
                            <span class="badge bg-warning text-dark fs-6">In Progress</span>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <!-- Progress Bar -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <small>Progress</small>
                            <small>{{ $summary['completion_percentage'] }}%</small>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-success" role="progressbar"
                                 style="width: {{ $summary['completion_percentage'] }}%"></div>
                        </div>
                    </div>

                    <!-- Summary Stats -->
                    <div class="row text-center mb-3">
                        <div class="col-3">
                            <h6 class="mb-0">{{ $summary['total_installments'] }}</h6>
                            <small class="text-muted">Total</small>
                        </div>
                        <div class="col-3">
                            <h6 class="mb-0 text-success">{{ $summary['paid_installments'] }}</h6>
                            <small class="text-muted">Paid</small>
                        </div>
                        <div class="col-3">
                            <h6 class="mb-0 text-warning">{{ $summary['pending_installments'] }}</h6>
                            <small class="text-muted">Pending</small>
                        </div>
                        <div class="col-3">
                            <h6 class="mb-0 text-danger">{{ $summary['overdue_installments'] }}</h6>
                            <small class="text-muted">Overdue</small>
                        </div>
                    </div>

                    <!-- Financial Summary -->
                    <div class="row">
                        <div class="col-md-4">
                            <div class="bg-light p-2 rounded text-center">
                                <small class="text-muted d-block">Total Amount</small>
                                <strong>RM {{ number_format($summary['total_amount'], 2) }}</strong>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="bg-light p-2 rounded text-center">
                                <small class="text-muted d-block">Paid</small>
                                <strong class="text-success">RM {{ number_format($summary['total_paid'], 2) }}</strong>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="bg-light p-2 rounded text-center">
                                <small class="text-muted d-block">Balance</small>
                                <strong class="{{ $summary['total_balance'] > 0 ? 'text-danger' : 'text-success' }}">
                                    RM {{ number_format($summary['total_balance'], 2) }}
                                </strong>
                            </div>
                        </div>
                    </div>

                    <!-- Installments Table -->
                    <div class="table-responsive mt-3">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Due Date</th>
                                    <th>Amount</th>
                                    <th>Paid</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($invoice->installments as $installment)
                                    <tr class="{{ $installment->isOverdue() ? 'table-danger' : ($installment->isPaid() ? 'table-success' : '') }}">
                                        <td>{{ $installment->installment_number }}</td>
                                        <td>
                                            {{ $installment->due_date->format('d M Y') }}
                                            @if($installment->isOverdue())
                                                <span class="badge bg-danger ms-1">{{ $installment->days_overdue }}d</span>
                                            @endif
                                        </td>
                                        <td>RM {{ number_format($installment->amount, 2) }}</td>
                                        <td>RM {{ number_format($installment->paid_amount, 2) }}</td>
                                        <td>
                                            @switch($installment->status)
                                                @case('paid')
                                                    <span class="badge bg-success">Paid</span>
                                                    @break
                                                @case('partial')
                                                    <span class="badge bg-info">Partial</span>
                                                    @break
                                                @case('overdue')
                                                    <span class="badge bg-danger">Overdue</span>
                                                    @break
                                                @default
                                                    <span class="badge bg-warning text-dark">Pending</span>
                                            @endswitch
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Next Due Alert -->
                    @if($summary['next_due'])
                        <div class="alert {{ $summary['next_due']->isOverdue() ? 'alert-danger' : 'alert-warning' }} mt-3 mb-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-{{ $summary['next_due']->isOverdue() ? 'exclamation-triangle' : 'clock' }} me-2"></i>
                                    <strong>Next Due:</strong>
                                    Installment #{{ $summary['next_due']->installment_number }} -
                                    RM {{ number_format($summary['next_due']->balance, 2) }}
                                    <span class="ms-2">{{ $summary['next_due']->due_date->format('d M Y') }}</span>
                                </div>
                                <a href="{{ route('admin.installments.show', $invoice) }}" class="btn btn-sm btn-{{ $summary['next_due']->isOverdue() ? 'danger' : 'warning' }}">
                                    View Details
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="card-footer bg-light">
                    <small class="text-muted">Created: {{ $invoice->created_at->format('d M Y H:i') }}</small>
                    @if($invoice->installment_notes)
                        <br><small class="text-muted"><i class="fas fa-sticky-note me-1"></i> {{ Str::limit($invoice->installment_notes, 100) }}</small>
                    @endif
                </div>
            </div>
        @empty
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted">No Installment Plans</h5>
                    <p class="text-muted">This student has no installment payment plans.</p>
                    <a href="{{ route('admin.installments.create') }}?student_id={{ $student->id }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Create Plan
                    </a>
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection

@push('styles')
<style>
.user-avatar {
    width: 80px;
    height: 80px;
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
