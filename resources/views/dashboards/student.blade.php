@extends('layouts.app')

@section('title', 'Student Dashboard')
@section('page-title', 'Student Dashboard')

@section('content')
<div class="page-header">
    <h1>
        <i class="fas fa-user-graduate me-2"></i> Student Dashboard
    </h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item active">Dashboard</li>
        </ol>
    </nav>
</div>

<!-- Welcome Message -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0" style="background: linear-gradient(135deg, #fda530 0%, #4c4c4c 100%);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-2">Hello, {{ auth()->user()->student->name ?? auth()->user()->name }}!</h4>
                        <p class="mb-0 opacity-75">Ready to learn something new today?</p>
                    </div>
                    <div class="text-end d-none d-md-block">
                        <div class="display-1 opacity-25">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #e3f2fd; color: #2196f3;">
                <i class="fas fa-book-open"></i>
            </div>
            <div class="stat-details">
                <h3 class="mb-0">{{ $enrollments->count() }}</h3>
                <p class="text-muted mb-0">My Classes</p>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #e8f5e9; color: #4caf50;">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div class="stat-details">
                @php
                    $totalAttendance = $recent_attendance->count();
                    $presentCount = $recent_attendance->where('status', 'present')->count();
                    $attendanceRate = $totalAttendance > 0 ? round(($presentCount / $totalAttendance) * 100) : 0;
                @endphp
                <h3 class="mb-0">{{ $attendanceRate }}%</h3>
                <p class="text-muted mb-0">Attendance Rate</p>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #f3e5f5; color: #9c27b0;">
                <i class="fas fa-file-alt"></i>
            </div>
            <div class="stat-details">
                <h3 class="mb-0">{{ $recent_materials->count() }}</h3>
                <p class="text-muted mb-0">New Materials</p>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #fff3e0; color: #ff9800;">
                <i class="fas fa-clipboard-check"></i>
            </div>
            <div class="stat-details">
                <h3 class="mb-0">{{ $upcoming_exams->count() }}</h3>
                <p class="text-muted mb-0">Upcoming Exams</p>
            </div>
        </div>
    </div>
</div>

<!-- My Classes -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-school me-2"></i> My Classes</span>
                <span class="badge bg-primary">{{ $enrollments->count() }} Active</span>
            </div>
            <div class="card-body">
                <div class="row">
                    @forelse($enrollments as $enrollment)
                        <div class="col-md-6 mb-3">
                            <div class="card border">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <h5 class="mb-1">{{ $enrollment->class->name ?? 'Class Not Assigned' }}</h5>
                                            <p class="text-muted mb-1">
                                                <i class="fas fa-book me-1"></i> {{ $enrollment->class->subject->name ?? 'Subject' }}
                                            </p>
                                            <p class="text-muted mb-0 small">
                                                <i class="fas fa-chalkboard-teacher me-1"></i>
                                                {{ $enrollment->class->teacher->user->name ?? 'Teacher' }}
                                            </p>
                                        </div>
                                        <span class="badge bg-success">{{ ucfirst($enrollment->status) }}</span>
                                    </div>

                                    <div class="mb-3 p-2 bg-light rounded">
                                        <div class="row text-center">
                                            <div class="col-6">
                                                <small class="text-muted d-block">Schedule</small>
                                                <strong>{{ $enrollment->class->schedule ?? 'TBA' }}</strong>
                                            </div>
                                            <div class="col-6">
                                                <small class="text-muted d-block">Type</small>
                                                <strong>{{ ucfirst($enrollment->class->class_type ?? 'N/A') }}</strong>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-grid gap-2 d-md-flex">
                                        <a href="#" class="btn btn-sm btn-primary flex-fill">
                                            <i class="fas fa-book-reader"></i> Materials
                                        </a>
                                        <a href="#" class="btn btn-sm btn-success flex-fill">
                                            <i class="fas fa-calendar-alt"></i> Schedule
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="alert alert-info text-center">
                                <i class="fas fa-info-circle fa-2x mb-2"></i>
                                <p class="mb-0">You haven't enrolled in any classes yet. Please contact the administrator.</p>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Learning Materials & Upcoming Exams -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-file-download me-2"></i> Learning Materials</span>
                <a href="#" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                @forelse($recent_materials as $material)
                    <div class="d-flex align-items-start mb-3 pb-3 border-bottom">
                        <div class="me-3">
                            <div class="stat-icon" style="background-color: #ffebee; color: #f44336; width: 40px; height: 40px; font-size: 1.2rem;">
                                <i class="fas fa-file-pdf"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1">{{ $material->title }}</h6>
                            <p class="text-muted mb-1 small">
                                {{ $material->class->name }} • {{ $material->created_at->diffForHumans() }}
                            </p>
                            <div class="d-flex gap-2">
                                <a href="#" class="btn btn-sm btn-primary">
                                    <i class="fas fa-download"></i> Download
                                </a>
                                <a href="#" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-4">
                        <i class="fas fa-folder-open fa-3x text-muted mb-2"></i>
                        <p class="text-muted mb-0">No materials available yet</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-clipboard-check me-2"></i> Upcoming Exams</span>
                @if($upcoming_exams->count() > 0)
                    <span class="badge bg-warning">{{ $upcoming_exams->count() }} Exam(s)</span>
                @endif
            </div>
            <div class="card-body">
                @forelse($upcoming_exams as $exam)
                    <div class="mb-3 p-3 border rounded">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h6 class="mb-1">{{ $exam->title }}</h6>
                                <p class="text-muted mb-1 small">
                                    <i class="fas fa-book me-1"></i> {{ $exam->subject->name }}
                                </p>
                                <p class="text-muted mb-0 small">
                                    <i class="fas fa-school me-1"></i> {{ $exam->class->name }}
                                </p>
                            </div>
                            <span class="badge bg-danger">
                                {{ \Carbon\Carbon::parse($exam->exam_date)->format('d M') }}
                            </span>
                        </div>

                        <div class="mb-2">
                            <div class="d-flex justify-content-between small">
                                <span class="text-muted">Date & Time:</span>
                                <strong>{{ \Carbon\Carbon::parse($exam->exam_date)->format('D, d M Y') }} at {{ $exam->start_time }}</strong>
                            </div>
                            <div class="d-flex justify-content-between small">
                                <span class="text-muted">Duration:</span>
                                <strong>{{ $exam->duration }} minutes</strong>
                            </div>
                        </div>

                        <a href="#" class="btn btn-sm btn-outline-primary w-100">
                            <i class="fas fa-info-circle me-1"></i> View Details
                        </a>
                    </div>
                @empty
                    <div class="text-center py-4">
                        <i class="fas fa-check-circle fa-3x text-success mb-2"></i>
                        <p class="text-muted mb-0">No upcoming exams</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Attendance History -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-history me-2"></i> Recent Attendance</span>
                <a href="#" class="btn btn-sm btn-outline-primary">View Full History</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Class</th>
                                <th>Subject</th>
                                <th>Status</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recent_attendance as $attendance)
                                <tr>
                                    <td>{{ $attendance->created_at->format('d M Y') }}</td>
                                    <td>{{ $attendance->classSession->class->name ?? 'N/A' }}</td>
                                    <td>{{ $attendance->classSession->class->subject->name ?? 'N/A' }}</td>
                                    <td>
                                        @if($attendance->status === 'present')
                                            <span class="badge bg-success">
                                                <i class="fas fa-check"></i> Present
                                            </span>
                                        @elseif($attendance->status === 'absent')
                                            <span class="badge bg-danger">
                                                <i class="fas fa-times"></i> Absent
                                            </span>
                                        @elseif($attendance->status === 'late')
                                            <span class="badge bg-warning">
                                                <i class="fas fa-clock"></i> Late
                                            </span>
                                        @else
                                            <span class="badge bg-info">
                                                <i class="fas fa-file-medical"></i> Excused
                                            </span>
                                        @endif
                                    </td>
                                    <td>{{ $attendance->remarks ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No attendance records yet</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
