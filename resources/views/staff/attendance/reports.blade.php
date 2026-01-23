@extends('layouts.app')

@section('title', 'Attendance Reports')
@section('page-title', 'Attendance Reports')

@section('content')
<div class="page-header">
    <h1>
        <i class="fas fa-chart-bar me-2"></i> Attendance Reports
    </h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('staff.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('staff.attendance.index') }}">Attendance</a></li>
            <li class="breadcrumb-item active">Reports</li>
        </ol>
    </nav>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<!-- Filter Card -->
<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-filter me-2"></i> Filter Reports
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('staff.attendance.reports') }}">
            <div class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label for="class_id" class="form-label">Class</label>
                    <select name="class_id" id="class_id" class="form-select">
                        <option value="">All Classes</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ $classId == $class->id ? 'selected' : '' }}>
                                {{ $class->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="student_id" class="form-label">Student</label>
                    <select name="student_id" id="student_id" class="form-select">
                        <option value="">All Students</option>
                        @foreach($students as $student)
                            <option value="{{ $student->id }}" {{ $studentId == $student->id ? 'selected' : '' }}>
                                {{ $student->student_id }} - {{ $student->user->name ?? 'N/A' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="date_from" class="form-label">Date From</label>
                    <input type="date" name="date_from" id="date_from" class="form-control" 
                           value="{{ $dateFrom }}">
                </div>
                <div class="col-md-2">
                    <label for="date_to" class="form-label">Date To</label>
                    <input type="date" name="date_to" id="date_to" class="form-control" 
                           value="{{ $dateTo }}">
                </div>
                <div class="col-md-3">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1">
                            <i class="fas fa-search me-2"></i> Filter
                        </button>
                        <a href="{{ route('staff.attendance.reports') }}" class="btn btn-secondary">
                            <i class="fas fa-redo"></i>
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@if($summary)
<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-md-2">
        <div class="card text-center">
            <div class="card-body py-3">
                <h3 class="mb-0 text-primary">{{ $summary['total'] }}</h3>
                <small class="text-muted">Total Records</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-center">
            <div class="card-body py-3">
                <h3 class="mb-0 text-success">{{ $summary['present'] }}</h3>
                <small class="text-muted">Present</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-center">
            <div class="card-body py-3">
                <h3 class="mb-0 text-danger">{{ $summary['absent'] }}</h3>
                <small class="text-muted">Absent</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-center">
            <div class="card-body py-3">
                <h3 class="mb-0 text-warning">{{ $summary['late'] }}</h3>
                <small class="text-muted">Late</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-center">
            <div class="card-body py-3">
                <h3 class="mb-0 text-info">{{ $summary['excused'] }}</h3>
                <small class="text-muted">Excused</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-center">
            <div class="card-body py-3">
                <h3 class="mb-0 {{ $summary['attendance_rate'] >= 80 ? 'text-success' : 'text-danger' }}">
                    {{ $summary['attendance_rate'] }}%
                </h3>
                <small class="text-muted">Attendance Rate</small>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Results Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-table me-2"></i> Attendance Records</span>
        @if($attendanceData && $attendanceData->total() > 0)
        <div class="d-flex gap-2">
            @can('export-student-attendance')
            <a href="{{ route('staff.attendance.export-student', request()->all()) }}" 
               class="btn btn-sm btn-success">
                <i class="fas fa-file-excel me-1"></i> Export Excel
            </a>
            @endcan
        </div>
        @endif
    </div>
    <div class="card-body p-0">
        @if(!$classId && !$studentId)
            <div class="text-center py-5">
                <i class="fas fa-filter text-muted fa-4x mb-3"></i>
                <h5>Apply Filters to View Reports</h5>
                <p class="text-muted">Please select a class or student to view attendance reports.</p>
            </div>
        @elseif($attendanceData && $attendanceData->isEmpty())
            <div class="text-center py-5">
                <i class="fas fa-inbox text-muted fa-4x mb-3"></i>
                <h5>No Records Found</h5>
                <p class="text-muted">No attendance records match your filter criteria.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Student</th>
                            <th>Class</th>
                            <th>Subject</th>
                            <th>Status</th>
                            <th>Check-in</th>
                            <th>Marked By</th>
                            <th>Remarks</th>
                            @can('edit-student-attendance')
                            <th>Action</th>
                            @endcan
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($attendanceData as $record)
                        <tr>
                            <td>{{ $record->classSession?->session_date ? Carbon\Carbon::parse($record->classSession->session_date)->format('d M Y') : 'N/A' }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm bg-light rounded-circle me-2 d-flex align-items-center justify-content-center">
                                        <i class="fas fa-user text-muted"></i>
                                    </div>
                                    <div>
                                        <span class="fw-medium">{{ $record->student->user->name ?? 'N/A' }}</span>
                                        <br><small class="text-muted">{{ $record->student->student_id ?? '' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $record->classSession->class->name ?? 'N/A' }}</td>
                            <td>{{ $record->classSession->class->subject->name ?? 'N/A' }}</td>
                            <td>
                                @switch($record->status)
                                    @case('present')
                                        <span class="badge bg-success">Present</span>
                                        @break
                                    @case('absent')
                                        <span class="badge bg-danger">Absent</span>
                                        @break
                                    @case('late')
                                        <span class="badge bg-warning">Late</span>
                                        @break
                                    @case('excused')
                                        <span class="badge bg-info">Excused</span>
                                        @break
                                @endswitch
                            </td>
                            <td>
                                {{ $record->check_in_time ? Carbon\Carbon::parse($record->check_in_time)->format('h:i A') : '-' }}
                            </td>
                            <td>
                                <small>{{ $record->markedBy->name ?? 'System' }}</small>
                            </td>
                            <td>
                                <small class="text-muted">{{ $record->remarks ?? '-' }}</small>
                            </td>
                            @can('edit-student-attendance')
                            <td>
                                <a href="{{ route('staff.attendance.edit-student', $record->id) }}" 
                                   class="btn btn-sm btn-outline-primary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                            @endcan
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($attendanceData->hasPages())
            <div class="card-footer">
                {{ $attendanceData->links() }}
            </div>
            @endif
        @endif
    </div>
</div>
@endsection
