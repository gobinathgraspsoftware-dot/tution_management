@extends('layouts.app')

@section('title', 'Attendance Management')
@section('page-title', 'Attendance Management')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="fas fa-clipboard-check me-2"></i> Attendance Management</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Attendance</li>
            </ol>
        </nav>
    </div>
    <div>
        @can('mark-student-attendance')
        <a href="{{ route('admin.attendance.student.mark') }}" class="btn btn-primary me-2">
            <i class="fas fa-user-graduate me-1"></i> Mark Student Attendance
        </a>
        @endcan
        @can('mark-teacher-attendance')
        <a href="{{ route('admin.attendance.teacher.mark') }}" class="btn btn-success">
            <i class="fas fa-chalkboard-teacher me-1"></i> Mark Teacher Attendance
        </a>
        @endcan
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <!-- Today's Sessions -->
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Today's Sessions</h6>
                        <h3 class="mb-0">{{ $todayStats['total_sessions'] }}</h3>
                        <small class="text-success">{{ $todayStats['completed_sessions'] }} Completed</small>
                    </div>
                    <div class="fs-2 text-primary">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Students Present -->
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Students Present</h6>
                        <h3 class="mb-0">{{ $todayStats['students_present'] }}</h3>
                        <small class="text-danger">{{ $todayStats['students_absent'] }} Absent</small>
                    </div>
                    <div class="fs-2 text-success">
                        <i class="fas fa-user-check"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Teachers Present -->
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Teachers Present</h6>
                        <h3 class="mb-0">{{ $todayStats['teachers_present'] }}</h3>
                        <small class="text-danger">{{ $todayStats['teachers_absent'] }} Absent</small>
                    </div>
                    <div class="fs-2 text-info">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Week Attendance Rate -->
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Week Attendance</h6>
                        <h3 class="mb-0">{{ $weekStats['attendance_rate'] }}%</h3>
                        <small class="text-muted">{{ $weekStats['total_sessions'] }} Sessions</small>
                    </div>
                    <div class="fs-2 text-warning">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-bolt me-2"></i> Quick Actions
            </div>
            <div class="card-body">
                <div class="row">
                    @can('mark-student-attendance')
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('admin.attendance.student.mark') }}" class="text-decoration-none">
                            <div class="p-3 border rounded text-center hover-shadow">
                                <i class="fas fa-user-check fa-2x text-primary mb-2"></i>
                                <h6>Mark Student Attendance</h6>
                            </div>
                        </a>
                    </div>
                    @endcan

                    @can('view-student-attendance-all')
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('admin.attendance.student.calendar') }}" class="text-decoration-none">
                            <div class="p-3 border rounded text-center hover-shadow">
                                <i class="fas fa-calendar-alt fa-2x text-success mb-2"></i>
                                <h6>Student Calendar</h6>
                            </div>
                        </a>
                    </div>
                    @endcan

                    @can('mark-teacher-attendance')
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('admin.attendance.teacher.mark') }}" class="text-decoration-none">
                            <div class="p-3 border rounded text-center hover-shadow">
                                <i class="fas fa-chalkboard-teacher fa-2x text-info mb-2"></i>
                                <h6>Mark Teacher Attendance</h6>
                            </div>
                        </a>
                    </div>
                    @endcan

                    @can('view-teacher-attendance-all')
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('admin.attendance.teacher.calendar') }}" class="text-decoration-none">
                            <div class="p-3 border rounded text-center hover-shadow">
                                <i class="fas fa-calendar-check fa-2x text-warning mb-2"></i>
                                <h6>Teacher Calendar</h6>
                            </div>
                        </a>
                    </div>
                    @endcan
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Today's Sessions -->
<div class="card">
    <div class="card-header">
        <i class="fas fa-clock me-2"></i> Today's Sessions
    </div>
    <div class="card-body">
        @if($recentSessions->isEmpty())
            <p class="text-muted text-center py-3">No sessions scheduled for today.</p>
        @else
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Class</th>
                            <th>Subject</th>
                            <th>Topic</th>
                            <th>Status</th>
                            <th>Attendance</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentSessions as $session)
                        <tr>
                            <td>{{ $session->start_time->format('H:i') }} - {{ $session->end_time->format('H:i') }}</td>
                            <td>{{ $session->class->name }}</td>
                            <td>{{ $session->class->subject->name ?? 'N/A' }}</td>
                            <td>{{ $session->topic ?? 'N/A' }}</td>
                            <td>
                                @if($session->status == 'scheduled')
                                    <span class="badge bg-primary">Scheduled</span>
                                @elseif($session->status == 'completed')
                                    <span class="badge bg-success">Completed</span>
                                @else
                                    <span class="badge bg-secondary">{{ ucfirst($session->status) }}</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $total = $session->attendance->count();
                                    $present = $session->attendance->where('status', 'present')->count();
                                @endphp
                                @if($total > 0)
                                    <span class="badge bg-info">{{ $present }}/{{ $total }}</span>
                                @else
                                    <span class="text-muted">Not marked</span>
                                @endif
                            </td>
                            <td>
                                @can('mark-student-attendance')
                                <a href="{{ route('admin.attendance.student.mark', ['session_id' => $session->id, 'class_id' => $session->class_id, 'date' => $session->session_date->format('Y-m-d')]) }}"
                                   class="btn btn-sm btn-primary">
                                    <i class="fas fa-check"></i> Mark
                                </a>
                                @endcan
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
.hover-shadow:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    transition: box-shadow 0.3s ease-in-out;
    cursor: pointer;
}
</style>
@endpush
