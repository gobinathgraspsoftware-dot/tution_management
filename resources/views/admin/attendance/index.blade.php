@extends('layouts.app')

@section('title', 'Attendance Dashboard')
@section('page-title', 'Attendance Dashboard')

@section('content')
<div class="page-header">
    <h1><i class="fas fa-chart-line me-2"></i> Attendance Dashboard</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Attendance</li>
        </ol>
    </nav>
</div>

<!-- Quick Stats -->
<div class="row mb-4">
    <!-- Today's Stats -->
    <div class="col-md-6 mb-3">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <i class="fas fa-calendar-day me-2"></i> Today's Statistics
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6 mb-3">
                        <div class="stat-box">
                            <div class="stat-icon bg-info">
                                <i class="fas fa-chalkboard"></i>
                            </div>
                            <div class="stat-details">
                                <h4>{{ $todayStats['total_sessions'] }}</h4>
                                <p>Total Sessions</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="stat-box">
                            <div class="stat-icon bg-success">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="stat-details">
                                <h4>{{ $todayStats['completed_sessions'] }}</h4>
                                <p>Completed</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stat-box">
                            <div class="stat-icon bg-success">
                                <i class="fas fa-user-check"></i>
                            </div>
                            <div class="stat-details">
                                <h4>{{ $todayStats['students_present'] }}</h4>
                                <p>Students Present</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stat-box">
                            <div class="stat-icon bg-danger">
                                <i class="fas fa-user-times"></i>
                            </div>
                            <div class="stat-details">
                                <h4>{{ $todayStats['students_absent'] }}</h4>
                                <p>Students Absent</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- This Week's Stats -->
    <div class="col-md-6 mb-3">
        <div class="card">
            <div class="card-header bg-success text-white">
                <i class="fas fa-calendar-week me-2"></i> This Week's Statistics
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6 mb-3">
                        <div class="stat-box">
                            <div class="stat-icon bg-primary">
                                <i class="fas fa-chalkboard-teacher"></i>
                            </div>
                            <div class="stat-details">
                                <h4>{{ $weekStats['total_sessions'] }}</h4>
                                <p>Total Sessions</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="stat-box">
                            <div class="stat-icon bg-info">
                                <i class="fas fa-percentage"></i>
                            </div>
                            <div class="stat-details">
                                <h4>{{ number_format($weekStats['attendance_rate'], 1) }}%</h4>
                                <p>Attendance Rate</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stat-box">
                            <div class="stat-icon bg-success">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                            <div class="stat-details">
                                <h4>{{ $todayStats['teachers_present'] }}</h4>
                                <p>Teachers Present</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stat-box">
                            <div class="stat-icon bg-warning">
                                <i class="fas fa-user-clock"></i>
                            </div>
                            <div class="stat-details">
                                <h4>{{ $todayStats['teachers_absent'] }}</h4>
                                <p>Teachers Absent</p>
                            </div>
                        </div>
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
                        <a href="{{ route('admin.attendance.student.mark') }}" class="quick-action-btn">
                            <i class="fas fa-user-check fa-2x mb-2"></i>
                            <h6>Mark Student Attendance</h6>
                            <small class="text-muted">Mark attendance for students</small>
                        </a>
                    </div>
                    @endcan

                    @can('view-student-attendance-all')
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('admin.attendance.student.calendar') }}" class="quick-action-btn">
                            <i class="fas fa-calendar-alt fa-2x mb-2"></i>
                            <h6>Student Calendar</h6>
                            <small class="text-muted">View attendance calendar</small>
                        </a>
                    </div>
                    @endcan

                    @can('mark-teacher-attendance')
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('admin.attendance.teacher.mark') }}" class="quick-action-btn">
                            <i class="fas fa-chalkboard-teacher fa-2x mb-2"></i>
                            <h6>Mark Teacher Attendance</h6>
                            <small class="text-muted">Mark attendance for teachers</small>
                        </a>
                    </div>
                    @endcan

                    @can('view-attendance-reports')
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('admin.attendance.reports.index') }}" class="quick-action-btn">
                            <i class="fas fa-chart-bar fa-2x mb-2"></i>
                            <h6>Attendance Reports</h6>
                            <small class="text-muted">View detailed reports</small>
                        </a>
                    </div>
                    @endcan
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Sessions Today -->
@if($recentSessions->count() > 0)
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-clock me-2"></i> Recent Sessions Today
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>Class</th>
                                <th>Subject</th>
                                <th>Status</th>
                                <th>Attendance</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentSessions as $session)
                            <tr>
                                <td>
                                    {{ $session->start_time->format('H:i') }} -
                                    {{ $session->end_time->format('H:i') }}
                                </td>
                                <td>{{ $session->class->name }}</td>
                                <td>{{ $session->class->subject->name ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge bg-{{ $session->status == 'completed' ? 'success' : ($session->status == 'scheduled' ? 'primary' : 'secondary') }}">
                                        {{ ucfirst($session->status) }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $present = $session->attendance->where('status', 'present')->count();
                                        $total = $session->attendance->count();
                                    @endphp
                                    <span class="badge bg-info">{{ $present }}/{{ $total }}</span>
                                </td>
                                <td>
                                    @can('mark-student-attendance')
                                    <a href="{{ route('admin.attendance.student.mark', ['class_id' => $session->class_id, 'date' => $session->session_date->format('Y-m-d')]) }}"
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
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@push('styles')
<style>
.stat-box {
    display: flex;
    align-items: center;
    padding: 10px;
    border-radius: 8px;
    background: #f8f9fa;
}

.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 20px;
    margin-right: 15px;
}

.stat-details h4 {
    margin: 0;
    font-size: 24px;
    font-weight: bold;
    color: #333;
}

.stat-details p {
    margin: 0;
    font-size: 12px;
    color: #666;
}

.quick-action-btn {
    display: block;
    text-align: center;
    padding: 25px 15px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    text-decoration: none;
    color: #333;
    transition: all 0.3s;
}

.quick-action-btn:hover {
    border-color: #667eea;
    background: #f8f9ff;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.quick-action-btn i {
    color: #667eea;
}

.quick-action-btn h6 {
    margin: 10px 0 5px;
    font-weight: 600;
}
</style>
@endpush
