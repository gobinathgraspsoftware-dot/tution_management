@extends('layouts.app')

@section('title', 'My Students')
@section('page-title', 'My Students')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1><i class="fas fa-users me-2"></i> My Students</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('teacher.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">My Students</li>
            </ol>
        </nav>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('teacher.students.index') }}" method="GET">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control"
                           placeholder="Name, Email, or Student ID" value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Filter by Class</label>
                    <select name="class_id" class="form-select">
                        <option value="">All Classes</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                {{ $class->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-1"></i> Filter
                    </button>
                    <a href="{{ route('teacher.students.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i> Clear
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Students List -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-list me-2"></i> Students Enrolled in My Classes</span>
        <span class="badge bg-primary">{{ $students->total() }} Total</span>
    </div>
    <div class="card-body p-0">
        @if($students->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Student</th>
                        <th>Student ID</th>
                        <th>Contact</th>
                        <th>Parent</th>
                        <th>Classes</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($students as $student)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="user-avatar me-3" style="width: 40px; height: 40px; font-size: 1rem;">
                                    {{ substr($student->user->name, 0, 1) }}
                                </div>
                                <div>
                                    <h6 class="mb-0">{{ $student->user->name }}</h6>
                                    <small class="text-muted">{{ $student->school ?? 'N/A' }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-info">{{ $student->student_id }}</span>
                        </td>
                        <td>
                            <div>
                                <i class="fas fa-envelope text-muted me-1"></i>
                                <a href="mailto:{{ $student->user->email }}">{{ $student->user->email }}</a>
                            </div>
                            @if($student->user->phone)
                            <div>
                                <i class="fas fa-phone text-muted me-1"></i>
                                <a href="tel:{{ $student->user->phone }}">{{ $student->user->phone }}</a>
                            </div>
                            @endif
                        </td>
                        <td>
                            @if($student->parent)
                                <div>{{ $student->parent->user->name }}</div>
                                <small class="text-muted">
                                    <a href="tel:{{ $student->parent->user->phone }}">
                                        {{ $student->parent->user->phone ?? 'N/A' }}
                                    </a>
                                </small>
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $teacherId = auth()->user()->teacher->id;
                                $studentClasses = $student->enrollments()
                                    ->whereHas('class', fn($q) => $q->where('teacher_id', $teacherId))
                                    ->with('class')
                                    ->where('status', 'active')
                                    ->get();
                            @endphp
                            @foreach($studentClasses as $enrollment)
                                <span class="badge bg-success mb-1">{{ $enrollment->class->name }}</span>
                            @endforeach
                        </td>
                        <td class="text-center">
                            <div class="btn-group">
                                <a href="{{ route('teacher.students.show', $student) }}"
                                   class="btn btn-sm btn-outline-primary" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('teacher.students.attendance', $student) }}"
                                   class="btn btn-sm btn-outline-info" title="Attendance History">
                                    <i class="fas fa-calendar-check"></i>
                                </a>
                                <a href="{{ route('teacher.students.results', $student) }}"
                                   class="btn btn-sm btn-outline-success" title="Exam Results">
                                    <i class="fas fa-chart-bar"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($students->hasPages())
        <div class="card-footer">
            {{ $students->links() }}
        </div>
        @endif
        @else
        <div class="text-center py-5">
            <i class="fas fa-users-slash fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">No Students Found</h5>
            <p class="text-muted mb-0">
                @if(request()->hasAny(['search', 'class_id']))
                    No students match your search criteria.
                @else
                    You don't have any students enrolled in your classes yet.
                @endif
            </p>
        </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
.user-avatar {
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    font-weight: bold;
}
</style>
@endpush
