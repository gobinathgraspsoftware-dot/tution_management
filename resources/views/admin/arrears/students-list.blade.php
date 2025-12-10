@extends('layouts.app')

@section('title', 'Students with Arrears')
@section('page-title', 'Students with Arrears')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.arrears.index') }}">Arrears</a></li>
            <li class="breadcrumb-item active">Students List</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="fas fa-users me-2"></i> Students with Arrears</h4>
            <p class="text-muted mb-0">List of all students with outstanding balances</p>
        </div>
        <div>
            <a href="{{ route('admin.arrears.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Arrears
            </a>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-users fa-2x opacity-75"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0">{{ $studentsWithArrears->count() }}</h3>
                            <small>Students with Arrears</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-warning text-dark">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-money-bill-wave fa-2x opacity-75"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0">RM {{ number_format($studentsWithArrears->sum('total_arrears'), 2) }}</h3>
                            <small>Total Outstanding</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-info text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-file-invoice fa-2x opacity-75"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0">{{ $studentsWithArrears->sum('unpaid_invoice_count') }}</h3>
                            <small>Total Unpaid Invoices</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.arrears.students-list') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Minimum Arrears (RM)</label>
                    <input type="number" name="min_arrears" class="form-control" value="{{ request('min_arrears') }}" placeholder="e.g., 500">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Class</label>
                    <select name="class_id" class="form-select">
                        <option value="">All Classes</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                {{ $class->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search me-1"></i> Filter
                    </button>
                    <a href="{{ route('admin.arrears.students-list') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-undo"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Students List -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i> Students List</h5>
            <span class="badge bg-danger">{{ $studentsWithArrears->count() }} students</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Student</th>
                            <th>Parent Contact</th>
                            <th>Class(es)</th>
                            <th>Unpaid Invoices</th>
                            <th>Total Arrears</th>
                            <th>Oldest Due</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($studentsWithArrears as $student)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm me-2">
                                            <span class="avatar-title rounded-circle bg-primary text-white">
                                                {{ strtoupper(substr($student->user->name ?? 'S', 0, 1)) }}
                                            </span>
                                        </div>
                                        <div>
                                            <strong>{{ $student->user->name ?? 'N/A' }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $student->student_id ?? '' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($student->parent)
                                        <strong>{{ $student->parent->user->name ?? 'N/A' }}</strong>
                                        <br>
                                        <small>
                                            <i class="fab fa-whatsapp text-success me-1"></i>
                                            {{ $student->parent->user->phone ?? 'N/A' }}
                                        </small>
                                    @else
                                        <span class="text-muted">No parent linked</span>
                                    @endif
                                </td>
                                <td>
                                    @forelse($student->enrollments->take(2) as $enrollment)
                                        <span class="badge bg-light text-dark mb-1">{{ $enrollment->class->name ?? 'N/A' }}</span>
                                    @empty
                                        <span class="text-muted">-</span>
                                    @endforelse
                                    @if($student->enrollments->count() > 2)
                                        <br><small class="text-muted">+{{ $student->enrollments->count() - 2 }} more</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-warning text-dark">{{ $student->unpaid_invoice_count }} invoices</span>
                                </td>
                                <td>
                                    <strong class="text-danger">RM {{ number_format($student->total_arrears, 2) }}</strong>
                                </td>
                                <td>
                                    @if($student->oldest_due_date)
                                        @php
                                            $oldestDue = \Carbon\Carbon::parse($student->oldest_due_date);
                                            $daysOverdue = $oldestDue->isPast() ? $oldestDue->diffInDays(now()) : 0;
                                        @endphp
                                        {{ $oldestDue->format('d M Y') }}
                                        @if($daysOverdue > 0)
                                            <br>
                                            <span class="badge bg-{{ $daysOverdue > 60 ? 'danger' : ($daysOverdue > 30 ? 'warning' : 'info') }}">
                                                {{ $daysOverdue }} days overdue
                                            </span>
                                        @endif
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.arrears.student', $student) }}" class="btn btn-outline-primary" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.payments.create', ['student_id' => $student->id]) }}" class="btn btn-outline-success" title="Record Payment">
                                            <i class="fas fa-money-bill"></i>
                                        </a>
                                        <a href="{{ route('admin.students.show', $student) }}" class="btn btn-outline-info" title="Student Profile">
                                            <i class="fas fa-user"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                                        <p>No students with arrears found!</p>
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
.avatar-sm {
    width: 40px;
    height: 40px;
}
.avatar-title {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
    font-size: 14px;
    font-weight: 600;
}
</style>
@endsection
