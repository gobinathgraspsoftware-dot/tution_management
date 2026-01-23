@extends('layouts.app')

@section('title', 'Students with Arrears')
@section('page-title', 'Students with Arrears')

@section('content')
<div class="container-fluid">
    <!-- Back Button -->
    <div class="mb-3">
        <a href="{{ route('staff.arrears.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Arrears Dashboard
        </a>
    </div>

    <!-- Summary Card -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 opacity-75">Total Students with Arrears</p>
                            <h2 class="mb-0">{{ $studentsWithArrears->count() }}</h2>
                        </div>
                        <i class="fas fa-users fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 opacity-75">Total Arrears Amount</p>
                            <h2 class="mb-0">RM {{ number_format($studentsWithArrears->sum('total_arrears'), 2) }}</h2>
                        </div>
                        <i class="fas fa-money-bill-wave fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 opacity-75">Avg Arrears per Student</p>
                            <h2 class="mb-0">
                                RM {{ $studentsWithArrears->count() > 0 ? number_format($studentsWithArrears->sum('total_arrears') / $studentsWithArrears->count(), 2) : '0.00' }}
                            </h2>
                        </div>
                        <i class="fas fa-chart-line fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-filter me-2 text-primary"></i>Filter Students</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('staff.arrears.students-list') }}" method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Minimum Arrears Amount (RM)</label>
                    <input type="number" class="form-control" name="min_arrears" 
                           value="{{ request('min_arrears') }}" placeholder="e.g. 500" min="0" step="0.01">
                </div>
                <div class="col-md-4">
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
                <div class="col-md-4 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="fas fa-search me-1"></i> Filter
                    </button>
                    <a href="{{ route('staff.arrears.students-list') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-redo"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Students List -->
    <div class="card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-user-times me-2 text-danger"></i>Students with Outstanding Arrears</h5>
            <span class="badge bg-danger">{{ $studentsWithArrears->count() }} students</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Student</th>
                            <th>Parent/Guardian</th>
                            <th>Contact</th>
                            <th class="text-center">Unpaid Invoices</th>
                            <th class="text-end">Total Arrears</th>
                            <th class="text-center">Oldest Due</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($studentsWithArrears as $student)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle bg-primary text-white me-2">
                                        {{ $student->user ? strtoupper(substr($student->user->name, 0, 1)) : '?' }}
                                    </div>
                                    <div>
                                        <strong>{{ $student->user->name ?? 'N/A' }}</strong>
                                        <br><small class="text-muted">{{ $student->student_id ?? 'N/A' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($student->parent && $student->parent->user)
                                {{ $student->parent->user->name }}
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($student->parent && $student->parent->user && $student->parent->user->phone)
                                <a href="tel:{{ $student->parent->user->phone }}" class="text-decoration-none">
                                    <i class="fas fa-phone me-1"></i>{{ $student->parent->user->phone }}
                                </a>
                                @elseif($student->user && $student->user->phone)
                                <a href="tel:{{ $student->user->phone }}" class="text-decoration-none">
                                    <i class="fas fa-phone me-1"></i>{{ $student->user->phone }}
                                </a>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge bg-warning">{{ $student->unpaid_invoice_count ?? 0 }}</span>
                            </td>
                            <td class="text-end">
                                <strong class="text-danger">RM {{ number_format($student->total_arrears ?? 0, 2) }}</strong>
                            </td>
                            <td class="text-center">
                                @if($student->oldest_due_date)
                                @php
                                    $oldestDue = \Carbon\Carbon::parse($student->oldest_due_date);
                                    $daysOverdue = max(0, now()->diffInDays($oldestDue, false) * -1);
                                @endphp
                                <span class="{{ $oldestDue->isPast() ? 'text-danger' : '' }}">
                                    {{ $oldestDue->format('d M Y') }}
                                </span>
                                @if($daysOverdue > 0)
                                <br><small class="badge bg-danger">{{ $daysOverdue }} days overdue</small>
                                @endif
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @can('view-student-arrears')
                                <a href="{{ route('staff.arrears.student', $student) }}" 
                                   class="btn btn-sm btn-outline-primary" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @endcan
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-check-circle fa-3x mb-3 text-success"></i>
                                    <h5>No Students with Arrears</h5>
                                    <p class="mb-0">All students are up to date with their payments.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
.avatar-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 16px;
    flex-shrink: 0;
}
</style>
@endsection
