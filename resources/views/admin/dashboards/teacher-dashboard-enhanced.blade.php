@extends('layouts.app')

@section('title', 'Teacher Dashboard')
@section('page-title', 'Teacher Dashboard')

@section('content')
<!-- Welcome Header -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card bg-gradient-success text-white">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h3 class="mb-1">Welcome back, {{ auth()->user()->name }}!</h3>
                        <p class="mb-0 opacity-75">
                            <i class="fas fa-calendar me-1"></i> {{ now()->format('l, d F Y') }}
                            <span class="mx-2">|</span>
                            <i class="fas fa-clock me-1"></i> <span id="current-time">{{ now()->format('h:i A') }}</span>
                        </p>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <span class="badge bg-white text-success fs-6">
                            <i class="fas fa-chalkboard-teacher me-1"></i> {{ $teacher->teacher_id }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="text-primary mb-0">{{ $stats['total_classes'] }}</h3>
                        <small class="text-muted">Total Classes</small>
                    </div>
                    <div class="icon-box bg-primary-light text-primary rounded-circle p-3">
                        <i class="fas fa-school fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="text-success mb-0">{{ $stats['active_classes'] }}</h3>
                        <small class="text-muted">Active Classes</small>
                    </div>
                    <div class="icon-box bg-success-light text-success rounded-circle p-3">
                        <i class="fas fa-check-circle fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="text-info mb-0">{{ $stats['total_students'] }}</h3>
                        <small class="text-muted">Total Students</small>
                    </div>
                    <div class="icon-box bg-info-light text-info rounded-circle p-3">
                        <i class="fas fa-users fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="text-warning mb-0">{{ $stats['classes_today'] }}</h3>
                        <small class="text-muted">Classes Today</small>
                    </div>
                    <div class="icon-box bg-warning-light text-warning rounded-circle p-3">
                        <i class="fas fa-calendar-day fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Today's Schedule -->
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-clock me-2"></i> Today's Schedule</span>
                @if(Route::has('teacher.schedule.index'))
                <a href="{{ route('teacher.schedule.index') }}" class="btn btn-sm btn-outline-primary">
                    View Full Schedule
                </a>
                @endif
            </div>
            <div class="card-body">
                @if($todaySchedule->count() > 0)
                <div class="timeline">
                    @foreach($todaySchedule as $schedule)
                    @php
                        $now = now();
                        $startTime = \Carbon\Carbon::parse($schedule->start_time);
                        $endTime = \Carbon\Carbon::parse($schedule->end_time);
                        $isCurrentClass = $now->format('H:i:s') >= $schedule->start_time &&
                                          $now->format('H:i:s') <= $schedule->end_time;
                        $isPast = $now->format('H:i:s') > $schedule->end_time;
                    @endphp
                    <div class="timeline-item d-flex mb-3 {{ $isCurrentClass ? 'current-class' : '' }}">
                        <div class="timeline-time text-end pe-3" style="width: 100px;">
                            <strong class="{{ $isCurrentClass ? 'text-primary' : ($isPast ? 'text-muted' : '') }}">
                                {{ $startTime->format('h:i A') }}
                            </strong>
                            <br>
                            <small class="text-muted">{{ $endTime->format('h:i A') }}</small>
                        </div>
                        <div class="timeline-content flex-grow-1">
                            <div class="card {{ $isCurrentClass ? 'border-primary border-2' : ($isPast ? 'bg-light' : '') }}">
                                <div class="card-body py-2">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1">
                                                {{ $schedule->class->name }}
                                                @if($isCurrentClass)
                                                    <span class="badge bg-primary ms-2">In Progress</span>
                                                @elseif($isPast)
                                                    <span class="badge bg-secondary ms-2">Completed</span>
                                                @else
                                                    <span class="badge bg-info ms-2">Upcoming</span>
                                                @endif
                                            </h6>
                                            <small class="text-muted">
                                                <i class="fas fa-book me-1"></i> {{ $schedule->class->subject->name ?? 'N/A' }}
                                                <span class="mx-2">|</span>
                                                <i class="fas fa-users me-1"></i> {{ $schedule->class->enrollments_count ?? $schedule->class->enrollments->count() }} Students
                                            </small>
                                        </div>
                                        <div class="btn-group btn-group-sm">
                                            @if(Route::has('teacher.classes.show'))
                                            <a href="{{ route('teacher.classes.show', $schedule->class) }}"
                                               class="btn btn-outline-primary" title="View Class">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @endif
                                            @if(Route::has('teacher.attendance.take'))
                                            <a href="{{ route('teacher.attendance.take', $schedule->class) }}"
                                               class="btn btn-outline-success" title="Take Attendance">
                                                <i class="fas fa-check-square"></i>
                                            </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-4">
                    <i class="fas fa-calendar-check fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Classes Today</h5>
                    <p class="text-muted mb-0">You don't have any classes scheduled for today.</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-history me-2"></i> Recent Activity
            </div>
            <div class="card-body p-0">
                @if($recentActivity->count() > 0)
                <div class="list-group list-group-flush">
                    @foreach($recentActivity as $activity)
                    <div class="list-group-item">
                        <div class="d-flex align-items-center">
                            <div class="activity-icon me-3">
                                @php
                                    $iconClass = match($activity->action) {
                                        'create' => 'fas fa-plus-circle text-success',
                                        'update' => 'fas fa-edit text-primary',
                                        'delete' => 'fas fa-trash text-danger',
                                        'login' => 'fas fa-sign-in-alt text-info',
                                        default => 'fas fa-circle text-secondary',
                                    };
                                @endphp
                                <i class="{{ $iconClass }}"></i>
                            </div>
                            <div class="flex-grow-1">
                                <p class="mb-0">{{ $activity->description }}</p>
                                <small class="text-muted">
                                    <i class="fas fa-clock me-1"></i> {{ $activity->created_at->diffForHumans() }}
                                </small>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-info-circle me-1"></i> No recent activity
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Right Sidebar -->
    <div class="col-md-4">
        <!-- Quick Actions -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-bolt me-2"></i> Quick Actions
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    @if(Route::has('teacher.classes.index'))
                    <a href="{{ route('teacher.classes.index') }}" class="btn btn-outline-primary">
                        <i class="fas fa-school me-2"></i> My Classes
                    </a>
                    @endif
                    @if(Route::has('teacher.students.index'))
                    <a href="{{ route('teacher.students.index') }}" class="btn btn-outline-info">
                        <i class="fas fa-users me-2"></i> My Students
                    </a>
                    @endif
                    @if(Route::has('teacher.schedule.index'))
                    <a href="{{ route('teacher.schedule.index') }}" class="btn btn-outline-success">
                        <i class="fas fa-calendar-alt me-2"></i> My Schedule
                    </a>
                    @endif
                    @if(Route::has('teacher.materials.create'))
                    <a href="{{ route('teacher.materials.create') }}" class="btn btn-outline-warning">
                        <i class="fas fa-upload me-2"></i> Upload Material
                    </a>
                    @endif
                    <a href="{{ route('profile.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-user me-2"></i> My Profile
                    </a>
                </div>
            </div>
        </div>

        <!-- My Classes Summary -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-school me-2"></i> My Classes</span>
                @if(Route::has('teacher.classes.index'))
                <a href="{{ route('teacher.classes.index') }}" class="btn btn-sm btn-link p-0">View All</a>
                @endif
            </div>
            <div class="card-body p-0">
                @if($myClasses->count() > 0)
                <div class="list-group list-group-flush">
                    @foreach($myClasses->take(5) as $class)
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">{{ $class->name }}</h6>
                                <small class="text-muted">{{ $class->subject->name ?? 'N/A' }}</small>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-info">{{ $class->enrollments_count ?? $class->enrollments->count() }}</span>
                                <br>
                                <small class="text-muted">Students</small>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-4 text-muted">
                    <p class="mb-0">No classes assigned yet</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Weekly Schedule Summary -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-chart-bar me-2"></i> Weekly Overview
            </div>
            <div class="card-body">
                @php
                    $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
                    $maxClasses = max($weeklyStats->max() ?: 1, 1);
                @endphp
                <div class="weekly-chart d-flex justify-content-between align-items-end" style="height: 100px;">
                    @foreach($days as $index => $day)
                    @php
                        $fullDay = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'][$index];
                        $count = $weeklyStats[$fullDay] ?? 0;
                        $height = $maxClasses > 0 ? ($count / $maxClasses) * 80 : 0;
                        $isToday = strtolower(now()->format('l')) === $fullDay;
                    @endphp
                    <div class="day-column text-center" style="width: 13%;">
                        <div class="bar mx-auto {{ $isToday ? 'bg-primary' : 'bg-secondary' }}"
                             style="height: {{ max($height, 5) }}px; width: 20px; border-radius: 3px 3px 0 0;"
                             title="{{ $count }} class(es)">
                        </div>
                        <small class="{{ $isToday ? 'fw-bold text-primary' : 'text-muted' }}">{{ $day }}</small>
                    </div>
                    @endforeach
                </div>
                <hr>
                <div class="row text-center">
                    <div class="col-6">
                        <h5 class="text-primary mb-0">{{ $scheduleStats['total_weekly_classes'] ?? 0 }}</h5>
                        <small class="text-muted">Classes/Week</small>
                    </div>
                    <div class="col-6">
                        <h5 class="text-success mb-0">{{ $scheduleStats['total_weekly_hours'] ?? 0 }}</h5>
                        <small class="text-muted">Hours/Week</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Announcements -->
        @if(isset($announcements) && $announcements->count() > 0)
        <div class="card">
            <div class="card-header">
                <i class="fas fa-bullhorn me-2"></i> Announcements
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @foreach($announcements->take(3) as $announcement)
                    <div class="list-group-item">
                        <h6 class="mb-1">{{ $announcement->title }}</h6>
                        <p class="mb-1 small text-muted">{{ Str::limit($announcement->content, 80) }}</p>
                        <small class="text-muted">
                            <i class="fas fa-clock me-1"></i> {{ $announcement->created_at->diffForHumans() }}
                        </small>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
.bg-gradient-success {
    background: linear-gradient(135deg, #4caf50 0%, #2e7d32 100%);
}

.icon-box {
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.bg-primary-light { background-color: rgba(13, 110, 253, 0.1); }
.bg-success-light { background-color: rgba(25, 135, 84, 0.1); }
.bg-info-light { background-color: rgba(13, 202, 240, 0.1); }
.bg-warning-light { background-color: rgba(255, 193, 7, 0.1); }

.timeline-item {
    position: relative;
}

.timeline-item::before {
    content: '';
    position: absolute;
    left: 90px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e9ecef;
}

.timeline-item:last-child::before {
    display: none;
}

.current-class .card {
    box-shadow: 0 0 15px rgba(13, 110, 253, 0.3);
}

.day-column .bar {
    transition: height 0.3s ease;
}
</style>
@endpush

@push('scripts')
<script>
// Update current time
function updateTime() {
    const now = new Date();
    const timeString = now.toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
        hour12: true
    });
    document.getElementById('current-time').textContent = timeString;
}

setInterval(updateTime, 60000);
</script>
@endpush
