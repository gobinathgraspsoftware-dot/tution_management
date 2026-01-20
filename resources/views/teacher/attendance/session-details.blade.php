@extends('layouts.app')

@section('title', 'Session Details')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Session Details</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('teacher.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('teacher.attendance.index') }}">Attendance</a></li>
                    <li class="breadcrumb-item active">Session Details</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('teacher.attendance.mark', $session) }}" class="btn btn-primary me-2">
                <i class="fas fa-edit me-1"></i> Edit Attendance
            </a>
            <a href="{{ route('teacher.attendance.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <!-- Session Info Card -->
    <div class="card mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Session Information</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <strong><i class="fas fa-chalkboard me-2"></i>Class:</strong>
                    <p class="mb-0">{{ $session->class->name }}</p>
                </div>
                <div class="col-md-3">
                    <strong><i class="fas fa-book me-2"></i>Subject:</strong>
                    <p class="mb-0">{{ $session->class->subject->name ?? 'N/A' }}</p>
                </div>
                <div class="col-md-3">
                    <strong><i class="fas fa-calendar me-2"></i>Date:</strong>
                    <p class="mb-0">{{ $session->session_date->format('l, M d, Y') }}</p>
                </div>
                <div class="col-md-3">
                    <strong><i class="fas fa-clock me-2"></i>Time:</strong>
                    <p class="mb-0">{{ \Carbon\Carbon::parse($session->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($session->end_time)->format('h:i A') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0">{{ $stats['total'] }}</h3>
                    <small>Total Students</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0">{{ $stats['marked'] }}</h3>
                    <small>Marked</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0">{{ $stats['present'] }}</h3>
                    <small>Present</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-danger text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0">{{ $stats['absent'] }}</h3>
                    <small>Absent</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-warning text-dark">
                <div class="card-body text-center">
                    <h3 class="mb-0">{{ $stats['late'] }}</h3>
                    <small>Late</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-secondary text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0">{{ $stats['excused'] }}</h3>
                    <small>Excused</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance Records -->
    <div class="card">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-users me-2"></i>Attendance Records</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Student</th>
                            <th>Status</th>
                            <th>Remarks</th>
                            <th>Marked At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($enrolledStudents as $index => $student)
                            @php
                                $attendance = $session->attendances->where('student_id', $student->id)->first();
                            @endphp
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-2">
                                            <span class="avatar-initial rounded-circle bg-primary">
                                                {{ strtoupper(substr($student->user->name ?? 'S', 0, 1)) }}
                                            </span>
                                        </div>
                                        <div>
                                            <strong>{{ $student->user->name ?? 'N/A' }}</strong>
                                            <br><small class="text-muted">{{ $student->student_id ?? '' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($attendance)
                                        @switch($attendance->status)
                                            @case('present')
                                                <span class="badge bg-success">Present</span>
                                                @break
                                            @case('absent')
                                                <span class="badge bg-danger">Absent</span>
                                                @break
                                            @case('late')
                                                <span class="badge bg-warning text-dark">Late</span>
                                                @break
                                            @case('excused')
                                                <span class="badge bg-info">Excused</span>
                                                @break
                                        @endswitch
                                    @else
                                        <span class="badge bg-secondary">Not Marked</span>
                                    @endif
                                </td>
                                <td>{{ $attendance->remarks ?? '-' }}</td>
                                <td>{{ $attendance && $attendance->marked_at ? $attendance->marked_at->format('M d, h:i A') : '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
.avatar-initial {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    font-size: 14px;
    font-weight: 600;
    color: #fff;
}
</style>
@endsection
