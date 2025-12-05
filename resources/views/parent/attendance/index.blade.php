@extends('layouts.parent')

@section('title', 'Attendance Overview')

@section('content')
<div class="container-fluid">
    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Children's Attendance</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('parent.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Attendance</li>
                </ol>
            </nav>
        </div>
    </div>

    {{-- Alert Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Overall Statistics --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-child fa-2x opacity-75"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0">{{ $children->count() }}</h3>
                            <small class="opacity-75">Total Children</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-percentage fa-2x opacity-75"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0">{{ number_format($overallAttendance, 1) }}%</h3>
                            <small class="opacity-75">Overall Attendance</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-info text-white h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-calendar-check fa-2x opacity-75"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0">{{ $totalPresent }}</h3>
                            <small class="opacity-75">Days Present (This Month)</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-warning text-dark h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-calendar-times fa-2x opacity-50"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0">{{ $totalAbsent }}</h3>
                            <small>Days Absent (This Month)</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Low Attendance Alerts --}}
    @if($lowAttendanceAlerts->count() > 0)
        <div class="alert alert-danger border-0 shadow-sm mb-4">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                <div>
                    <h5 class="mb-1">Attendance Alert</h5>
                    <p class="mb-0">
                        @foreach($lowAttendanceAlerts as $alert)
                            <strong>{{ $alert->student->user->name }}</strong> has 
                            {{ number_format($alert->attendance_percentage, 1) }}% attendance in 
                            {{ $alert->class->name }}.
                            @if(!$loop->last) <br> @endif
                        @endforeach
                    </p>
                </div>
            </div>
        </div>
    @endif

    {{-- Children Attendance Cards --}}
    <div class="row">
        @forelse($children as $child)
            @php
                $childStats = $childrenStats[$child->id] ?? [
                    'total_sessions' => 0,
                    'present' => 0,
                    'absent' => 0,
                    'late' => 0,
                    'percentage' => 0
                ];
                
                $percentageClass = 'success';
                if ($childStats['percentage'] < 75) $percentageClass = 'danger';
                elseif ($childStats['percentage'] < 85) $percentageClass = 'warning';
            @endphp
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-bottom">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-lg bg-primary text-white rounded-circle me-3">
                                <span class="avatar-text">{{ substr($child->user->name, 0, 1) }}</span>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="mb-0">{{ $child->user->name }}</h5>
                                <small class="text-muted">{{ $child->student_id }}</small>
                            </div>
                            <div class="text-end">
                                <div class="h4 mb-0 text-{{ $percentageClass }}">
                                    {{ number_format($childStats['percentage'], 1) }}%
                                </div>
                                <small class="text-muted">Attendance</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        {{-- Progress Bar --}}
                        <div class="mb-3">
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-{{ $percentageClass }}" 
                                     role="progressbar" 
                                     style="width: {{ $childStats['percentage'] }}%"
                                     aria-valuenow="{{ $childStats['percentage'] }}" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100">
                                </div>
                            </div>
                        </div>

                        {{-- Stats Grid --}}
                        <div class="row text-center g-2 mb-3">
                            <div class="col-3">
                                <div class="border rounded py-2">
                                    <h5 class="mb-0 text-primary">{{ $childStats['total_sessions'] }}</h5>
                                    <small class="text-muted">Sessions</small>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="border rounded py-2">
                                    <h5 class="mb-0 text-success">{{ $childStats['present'] }}</h5>
                                    <small class="text-muted">Present</small>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="border rounded py-2">
                                    <h5 class="mb-0 text-danger">{{ $childStats['absent'] }}</h5>
                                    <small class="text-muted">Absent</small>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="border rounded py-2">
                                    <h5 class="mb-0 text-warning">{{ $childStats['late'] }}</h5>
                                    <small class="text-muted">Late</small>
                                </div>
                            </div>
                        </div>

                        {{-- Classes Enrolled --}}
                        @if($child->enrollments->count() > 0)
                            <div class="mb-3">
                                <small class="text-muted d-block mb-2">Enrolled Classes:</small>
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach($child->enrollments->take(3) as $enrollment)
                                        <span class="badge bg-light text-dark">
                                            {{ $enrollment->class->name ?? 'N/A' }}
                                        </span>
                                    @endforeach
                                    @if($child->enrollments->count() > 3)
                                        <span class="badge bg-secondary">
                                            +{{ $child->enrollments->count() - 3 }} more
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endif

                        {{-- Recent Attendance --}}
                        @if(isset($recentAttendance[$child->id]) && $recentAttendance[$child->id]->count() > 0)
                            <div class="mb-3">
                                <small class="text-muted d-block mb-2">Recent Attendance:</small>
                                <div class="d-flex gap-1">
                                    @foreach($recentAttendance[$child->id]->take(7) as $attendance)
                                        @php
                                            $statusIcon = match($attendance->status) {
                                                'present' => ['check', 'success'],
                                                'absent' => ['times', 'danger'],
                                                'late' => ['clock', 'warning'],
                                                'excused' => ['info', 'info'],
                                                default => ['question', 'secondary']
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $statusIcon[1] }}" 
                                              title="{{ ucfirst($attendance->status) }} - {{ $attendance->classSession->session_date->format('d/m') }}">
                                            <i class="fas fa-{{ $statusIcon[0] }}"></i>
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="card-footer bg-white border-top">
                        <a href="{{ route('parent.attendance.child', $child->id) }}" class="btn btn-primary btn-sm w-100">
                            <i class="fas fa-chart-bar me-2"></i>View Detailed Report
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-child fa-4x text-muted mb-3"></i>
                        <h5 class="text-muted">No Children Found</h5>
                        <p class="text-muted">You don't have any children registered in the system yet.</p>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Notifications History --}}
    @if($notificationHistory->count() > 0)
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">
                    <i class="fas fa-bell me-2 text-warning"></i>Recent Attendance Notifications
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @foreach($notificationHistory as $notification)
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="fw-semibold">
                                        {{ $notification->data['student_name'] ?? 'Your child' }}'s attendance marked
                                    </div>
                                    <small class="text-muted">
                                        {{ $notification->data['class_name'] ?? 'N/A' }} - 
                                        {{ $notification->data['attendance_status'] ?? 'N/A' }}
                                    </small>
                                </div>
                                <small class="text-muted">
                                    {{ $notification->created_at->diffForHumans() }}
                                </small>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="card-footer bg-white text-center">
                <a href="{{ route('parent.notifications.index') }}" class="btn btn-outline-primary btn-sm">
                    View All Notifications
                </a>
            </div>
        </div>
    @endif
</div>
@endsection

@push('styles')
<style>
.avatar-lg {
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.avatar-text {
    font-weight: 600;
    font-size: 20px;
}
</style>
@endpush
