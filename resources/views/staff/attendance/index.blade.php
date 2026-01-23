@extends('layouts.app')

@section('title', 'Attendance Dashboard')
@section('page-title', 'Attendance Management')

@section('content')
<div class="page-header">
    <h1>
        <i class="fas fa-clipboard-check me-2"></i> Attendance Dashboard
    </h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('staff.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Attendance</li>
        </ol>
    </nav>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #e3f2fd; color: #2196f3;">
                <i class="fas fa-calendar-day"></i>
            </div>
            <div class="stat-details">
                <h3 class="mb-0">{{ $todayStats['total_sessions'] }}</h3>
                <p class="text-muted mb-0">Today's Sessions</p>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #e8f5e9; color: #4caf50;">
                <i class="fas fa-user-check"></i>
            </div>
            <div class="stat-details">
                <h3 class="mb-0">{{ $todayStats['students_present'] }}</h3>
                <p class="text-muted mb-0">Students Present</p>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #ffebee; color: #f44336;">
                <i class="fas fa-user-times"></i>
            </div>
            <div class="stat-details">
                <h3 class="mb-0">{{ $todayStats['students_absent'] }}</h3>
                <p class="text-muted mb-0">Students Absent</p>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #fff3e0; color: #ff9800;">
                <i class="fas fa-chalkboard-teacher"></i>
            </div>
            <div class="stat-details">
                <h3 class="mb-0">{{ $todayStats['teachers_present'] }}/{{ $todayStats['teachers_present'] + $todayStats['teachers_absent'] }}</h3>
                <p class="text-muted mb-0">Teachers Present</p>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-bolt me-2"></i> Quick Actions
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @can('mark-student-attendance')
                    <div class="col-md-3">
                        <a href="{{ route('staff.attendance.student.mark') }}" class="btn btn-primary w-100">
                            <i class="fas fa-user-check me-2"></i> Mark Student Attendance
                        </a>
                    </div>
                    @endcan
                    
                    @can('mark-teacher-attendance')
                    <div class="col-md-3">
                        <a href="{{ route('staff.attendance.teacher.mark') }}" class="btn btn-success w-100">
                            <i class="fas fa-chalkboard-teacher me-2"></i> Mark Teacher Attendance
                        </a>
                    </div>
                    @endcan
                    
                    @can('view-student-attendance-all')
                    <div class="col-md-3">
                        <a href="{{ route('staff.attendance.student.calendar') }}" class="btn btn-info w-100">
                            <i class="fas fa-calendar-alt me-2"></i> Student Calendar
                        </a>
                    </div>
                    @endcan
                    
                    @can('view-attendance-reports')
                    <div class="col-md-3">
                        <a href="{{ route('staff.attendance.reports') }}" class="btn btn-warning w-100">
                            <i class="fas fa-chart-bar me-2"></i> View Reports
                        </a>
                    </div>
                    @endcan
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Today's Sessions -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-calendar-day me-2"></i> Today's Sessions</span>
                <span class="badge bg-primary">{{ Carbon\Carbon::now()->format('D, d M Y') }}</span>
            </div>
            <div class="card-body">
                @if($todaySessions->isEmpty())
                    <div class="text-center py-4">
                        <i class="fas fa-calendar-times text-muted fa-3x mb-3"></i>
                        <p class="text-muted">No sessions scheduled for today.</p>
                    </div>
                @else
                    <div class="list-group list-group-flush">
                        @foreach($todaySessions as $session)
                        <div class="list-group-item px-0">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <div class="badge {{ $session->attendances->count() > 0 ? 'bg-success' : 'bg-warning' }}" 
                                         style="width: 60px; padding: 0.5rem;">
                                        {{ Carbon\Carbon::parse($session->start_time)->format('h:i A') }}
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0">{{ $session->class->name }}</h6>
                                    <small class="text-muted">
                                        {{ $session->class->subject->name ?? 'N/A' }} | 
                                        Teacher: {{ $session->class->teacher->user->name ?? 'Not Assigned' }}
                                    </small>
                                </div>
                                @if($session->attendances->count() > 0)
                                    <span class="badge bg-success">
                                        <i class="fas fa-check me-1"></i> Marked
                                    </span>
                                @else
                                    @can('mark-student-attendance')
                                    <a href="{{ route('staff.attendance.student.mark', [
                                        'class_id' => $session->class_id,
                                        'date' => $session->session_date->toDateString(),
                                        'session_id' => $session->id
                                    ]) }}" class="btn btn-sm btn-outline-primary">
                                        Mark Now
                                    </a>
                                    @endcan
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Recent Student Attendance -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-history me-2"></i> Recent Student Attendance</span>
                @can('view-attendance-reports')
                <a href="{{ route('staff.attendance.reports') }}" class="btn btn-sm btn-outline-primary">View All</a>
                @endcan
            </div>
            <div class="card-body p-0">
                @if($recentStudentAttendance->isEmpty())
                    <div class="text-center py-4">
                        <i class="fas fa-inbox text-muted fa-3x mb-3"></i>
                        <p class="text-muted">No recent attendance records.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Student</th>
                                    <th>Class</th>
                                    <th>Status</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentStudentAttendance as $record)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-light rounded-circle me-2 d-flex align-items-center justify-content-center">
                                                <i class="fas fa-user text-muted"></i>
                                            </div>
                                            <div>
                                                <span class="fw-medium">{{ $record->student->user->name ?? 'N/A' }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $record->classSession->class->name ?? 'N/A' }}</td>
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
                                        <small class="text-muted">{{ $record->created_at->diffForHumans() }}</small>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Recent Teacher Attendance -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-chalkboard-teacher me-2"></i> Recent Teacher Attendance</span>
                @can('view-teacher-attendance-all')
                <a href="{{ route('staff.attendance.teacher.calendar') }}" class="btn btn-sm btn-outline-primary">View Calendar</a>
                @endcan
            </div>
            <div class="card-body p-0">
                @if($recentTeacherAttendance->isEmpty())
                    <div class="text-center py-4">
                        <i class="fas fa-inbox text-muted fa-3x mb-3"></i>
                        <p class="text-muted">No recent teacher attendance records.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Teacher</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Time In</th>
                                    <th>Time Out</th>
                                    <th>Hours</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentTeacherAttendance as $record)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-light rounded-circle me-2 d-flex align-items-center justify-content-center">
                                                <i class="fas fa-chalkboard-teacher text-muted"></i>
                                            </div>
                                            <span class="fw-medium">{{ $record->teacher->user->name ?? 'N/A' }}</span>
                                        </div>
                                    </td>
                                    <td>{{ Carbon\Carbon::parse($record->date)->format('d M Y') }}</td>
                                    <td>
                                        @switch($record->status)
                                            @case('present')
                                                <span class="badge bg-success">Present</span>
                                                @break
                                            @case('absent')
                                                <span class="badge bg-danger">Absent</span>
                                                @break
                                            @case('half_day')
                                                <span class="badge bg-warning">Half Day</span>
                                                @break
                                            @case('leave')
                                                <span class="badge bg-info">On Leave</span>
                                                @break
                                        @endswitch
                                    </td>
                                    <td>{{ $record->time_in ? Carbon\Carbon::parse($record->time_in)->format('h:i A') : '-' }}</td>
                                    <td>{{ $record->time_out ? Carbon\Carbon::parse($record->time_out)->format('h:i A') : '-' }}</td>
                                    <td>{{ $record->hours_worked ? number_format($record->hours_worked, 1) . 'h' : '-' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
