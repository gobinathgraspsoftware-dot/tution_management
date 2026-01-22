@extends('layouts.app')

@section('title', 'All Students')

@section('styles')
<style>
    .stat-card {
        border-radius: 10px;
        padding: 20px;
        color: white;
        margin-bottom: 20px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    .stat-card.primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
    .stat-card.success { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
    .stat-card.warning { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
    .stat-card.info { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
    .stat-card.danger { background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%); }
    .stat-card h3 { font-size: 2rem; font-weight: bold; margin-bottom: 5px; }
    .stat-card p { margin: 0; opacity: 0.9; }
    .stat-card .icon { font-size: 2.5rem; opacity: 0.3; position: absolute; right: 20px; top: 20px; }
    
    .student-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        font-size: 14px;
    }
    .badge-approval {
        font-size: 0.75rem;
        padding: 0.35em 0.65em;
    }
    .table-hover tbody tr:hover {
        background-color: rgba(102, 126, 234, 0.05);
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">
                <i class="fas fa-users text-primary me-2"></i>All Students
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('staff.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">All Students</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-2 col-sm-4 col-6">
            <div class="stat-card primary position-relative">
                <i class="fas fa-users icon"></i>
                <h3>{{ number_format($stats['total']) }}</h3>
                <p>Total Students</p>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 col-6">
            <div class="stat-card success position-relative">
                <i class="fas fa-check-circle icon"></i>
                <h3>{{ number_format($stats['approved']) }}</h3>
                <p>Approved</p>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 col-6">
            <div class="stat-card warning position-relative">
                <i class="fas fa-clock icon"></i>
                <h3>{{ number_format($stats['pending']) }}</h3>
                <p>Pending</p>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 col-6">
            <div class="stat-card info position-relative">
                <i class="fas fa-user-check icon"></i>
                <h3>{{ number_format($stats['active']) }}</h3>
                <p>Active</p>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 col-6">
            <div class="stat-card danger position-relative">
                <i class="fas fa-user-times icon"></i>
                <h3>{{ number_format($stats['inactive']) }}</h3>
                <p>Inactive</p>
            </div>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('staff.students.index') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" class="form-control" 
                               placeholder="Search name, email, IC, student ID..." 
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Approval</label>
                        <select name="approval_status" class="form-select">
                            <option value="">All</option>
                            <option value="approved" {{ request('approval_status') == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="pending" {{ request('approval_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="rejected" {{ request('approval_status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Grade Level</label>
                        <select name="grade_level" class="form-select">
                            <option value="">All Grades</option>
                            @foreach($gradeLevels as $grade)
                                <option value="{{ $grade }}" {{ request('grade_level') == $grade ? 'selected' : '' }}>
                                    {{ $grade }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Class</label>
                        <select name="class_id" class="form-select">
                            <option value="">All Classes</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                    {{ $class->name }} ({{ $class->subject->name ?? 'N/A' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                @if(request()->hasAny(['search', 'status', 'approval_status', 'grade_level', 'class_id', 'gender', 'registration_type']))
                <div class="mt-2">
                    <a href="{{ route('staff.students.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-times me-1"></i>Clear Filters
                    </a>
                </div>
                @endif
            </form>
        </div>
    </div>

    <!-- Students Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-list me-2"></i>Students List
            </h5>
            <span class="badge bg-primary">{{ $students->total() }} total</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>Student</th>
                            <th>Student ID</th>
                            <th>Parent</th>
                            <th>Grade</th>
                            <th>Enrollments</th>
                            <th>Status</th>
                            <th>Approval</th>
                            <th class="text-center" style="width: 100px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($students as $student)
                        <tr>
                            <td>{{ $loop->iteration + ($students->currentPage() - 1) * $students->perPage() }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="student-avatar me-2">
                                        {{ strtoupper(substr($student->user->name ?? 'S', 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-semibold">{{ $student->user->name ?? 'N/A' }}</div>
                                        <small class="text-muted">{{ $student->user->email ?? 'N/A' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">{{ $student->student_id }}</span>
                            </td>
                            <td>
                                @if($student->parent && $student->parent->user)
                                    <div>{{ $student->parent->user->name }}</div>
                                    <small class="text-muted">
                                        <i class="fas fa-phone me-1"></i>{{ $student->parent->user->phone ?? 'N/A' }}
                                    </small>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>{{ $student->grade_level ?? 'N/A' }}</td>
                            <td>
                                @php
                                    $activeCount = $student->enrollments->where('status', 'active')->count();
                                    $totalCount = $student->enrollments->count();
                                @endphp
                                <span class="badge bg-{{ $activeCount > 0 ? 'success' : 'secondary' }}">
                                    {{ $activeCount }} active / {{ $totalCount }} total
                                </span>
                            </td>
                            <td>
                                @if($student->user && $student->user->status == 'active')
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td>
                                @switch($student->approval_status)
                                    @case('approved')
                                        <span class="badge bg-success badge-approval">Approved</span>
                                        @break
                                    @case('pending')
                                        <span class="badge bg-warning text-dark badge-approval">Pending</span>
                                        @break
                                    @case('rejected')
                                        <span class="badge bg-danger badge-approval">Rejected</span>
                                        @break
                                    @default
                                        <span class="badge bg-secondary badge-approval">{{ $student->approval_status }}</span>
                                @endswitch
                            </td>
                            <td class="text-center">
                                <a href="{{ route('staff.students.show', $student) }}" 
                                   class="btn btn-sm btn-outline-primary" 
                                   title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-users fa-3x mb-3"></i>
                                    <p class="mb-0">No students found</p>
                                    <small>Try adjusting your search or filter criteria</small>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($students->hasPages())
        <div class="card-footer bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    Showing {{ $students->firstItem() }} to {{ $students->lastItem() }} of {{ $students->total() }} entries
                </small>
                {{ $students->links() }}
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Auto-submit form on select change
    $('select[name="status"], select[name="approval_status"], select[name="grade_level"], select[name="class_id"]').on('change', function() {
        $(this).closest('form').submit();
    });
});
</script>
@endsection
