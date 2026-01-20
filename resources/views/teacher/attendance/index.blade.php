@extends('layouts.app')

@section('title', 'Attendance Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Attendance Dashboard</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('teacher.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Attendance</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title mb-0">Total Sessions</h6>
                            <h2 class="mb-0">{{ $stats['total_sessions'] }}</h2>
                            <small>This Month</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-calendar-check fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title mb-0">Marked Sessions</h6>
                            <h2 class="mb-0">{{ $stats['marked_sessions'] }}</h2>
                            <small>Completed</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-check-circle fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title mb-0">Pending Sessions</h6>
                            <h2 class="mb-0">{{ $stats['pending_sessions'] }}</h2>
                            <small>To Mark</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-clock fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title mb-0">Attendance Rate</h6>
                            <h2 class="mb-0">{{ $stats['attendance_rate'] }}%</h2>
                            <small>This Month</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-percentage fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Today's Sessions -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-calendar-day me-2"></i>Today's Sessions</h5>
                </div>
                <div class="card-body">
                    @if($todaySessions->isEmpty())
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">No sessions scheduled for today</p>
                        </div>
                    @else
                        <div class="list-group list-group-flush">
                            @foreach($todaySessions as $session)
                                <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <div>
                                        <h6 class="mb-1">{{ $session->class->name }}</h6>
                                        <small class="text-muted">
                                            <i class="fas fa-clock me-1"></i>
                                            {{ \Carbon\Carbon::parse($session->start_time)->format('h:i A') }} - 
                                            {{ \Carbon\Carbon::parse($session->end_time)->format('h:i A') }}
                                        </small>
                                    </div>
                                    <div>
                                        @if($session->attendances->count() > 0)
                                            <span class="badge bg-success me-2">Marked</span>
                                            <a href="{{ route('teacher.attendance.session-details', $session) }}" 
                                               class="btn btn-sm btn-outline-primary">View</a>
                                        @else
                                            <a href="{{ route('teacher.attendance.mark', $session) }}" 
                                               class="btn btn-sm btn-primary">Mark Attendance</a>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Upcoming Sessions -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Upcoming Sessions (7 Days)</h5>
                </div>
                <div class="card-body">
                    @if($upcomingSessions->isEmpty())
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-check fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">No upcoming sessions</p>
                        </div>
                    @else
                        <div class="list-group list-group-flush">
                            @foreach($upcomingSessions as $session)
                                <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <div>
                                        <h6 class="mb-1">{{ $session->class->name }}</h6>
                                        <small class="text-muted">
                                            <i class="fas fa-calendar me-1"></i>
                                            {{ $session->session_date->format('D, M d') }} |
                                            <i class="fas fa-clock me-1"></i>
                                            {{ \Carbon\Carbon::parse($session->start_time)->format('h:i A') }}
                                        </small>
                                    </div>
                                    <span class="badge bg-secondary">
                                        {{ $session->session_date->diffForHumans() }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- My Classes -->
    <div class="card mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-chalkboard me-2"></i>My Classes - Attendance History</h5>
        </div>
        <div class="card-body">
            @if($classes->isEmpty())
                <div class="text-center py-4">
                    <i class="fas fa-chalkboard-teacher fa-3x text-muted mb-3"></i>
                    <p class="text-muted mb-0">No active classes assigned</p>
                </div>
            @else
                <div class="row">
                    @foreach($classes as $class)
                        <div class="col-md-4 mb-3">
                            <div class="card border">
                                <div class="card-body">
                                    <h6 class="card-title">{{ $class->name }}</h6>
                                    <p class="card-text text-muted small mb-2">
                                        <i class="fas fa-book me-1"></i>{{ $class->subject->name ?? 'N/A' }}
                                    </p>
                                    <a href="{{ route('teacher.attendance.class-history', $class) }}" 
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-history me-1"></i>View History
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- Recent Attendance Records -->
    <div class="card">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Attendance Records</h5>
        </div>
        <div class="card-body">
            @if($recentAttendance->isEmpty())
                <div class="text-center py-4">
                    <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                    <p class="text-muted mb-0">No attendance records yet</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Student</th>
                                <th>Class</th>
                                <th>Session Date</th>
                                <th>Status</th>
                                <th>Marked At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentAttendance as $record)
                                <tr>
                                    <td>{{ $record->student->user->name ?? 'N/A' }}</td>
                                    <td>{{ $record->classSession->class->name ?? 'N/A' }}</td>
                                    <td>{{ $record->classSession->session_date->format('M d, Y') }}</td>
                                    <td>
                                        @switch($record->status)
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
                                    </td>
                                    <td>{{ $record->marked_at ? $record->marked_at->format('M d, h:i A') : '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
