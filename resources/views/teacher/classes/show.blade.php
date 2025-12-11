@extends('layouts.app')

@section('title', 'Class Details - ' . $class->name)
@section('page-title', 'Class Details')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="fas fa-school me-2"></i> {{ $class->name }}</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('teacher.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('teacher.classes.index') }}">My Classes</a></li>
                <li class="breadcrumb-item active">{{ $class->code }}</li>
            </ol>
        </nav>
    </div>
    <div>
        <a href="{{ route('teacher.classes.students', $class) }}" class="btn btn-info me-2">
            <i class="fas fa-users me-1"></i> View Students
        </a>
        @if(Route::has('teacher.attendance.mark'))
        <a href="{{ route('teacher.attendance.mark', ['class_id' => $class->id]) }}" class="btn btn-success">
            <i class="fas fa-clipboard-check me-1"></i> Mark Attendance
        </a>
        @endif
    </div>
</div>

<div class="row">
    <!-- Class Overview -->
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-info-circle me-2"></i> Class Information
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    <div class="user-avatar mx-auto mb-3"
                         style="width: 80px; height: 80px; font-size: 2rem; background: linear-gradient(135deg, #2196f3 0%, #1976d2 100%);">
                        <i class="fas fa-school" style="color: white;"></i>
                    </div>
                    <h5 class="mb-1">{{ $class->name }}</h5>
                    <span class="badge bg-secondary">{{ $class->code }}</span>

                    <div class="mt-2">
                        @if($class->status == 'active')
                            <span class="badge bg-success">Active</span>
                        @elseif($class->status == 'completed')
                            <span class="badge bg-info">Completed</span>
                        @else
                            <span class="badge bg-secondary">{{ ucfirst($class->status) }}</span>
                        @endif

                        <span class="badge bg-{{ $class->type == 'online' ? 'primary' : 'warning' }}">
                            {{ ucfirst($class->type) }}
                        </span>
                    </div>
                </div>

                <hr>

                <div class="mb-3">
                    <label class="form-label text-muted small mb-1">Subject</label>
                    <p class="mb-0 fw-medium">
                        <i class="fas fa-book text-primary me-2"></i>
                        {{ $class->subject->name ?? 'N/A' }}
                    </p>
                </div>

                <div class="mb-3">
                    <label class="form-label text-muted small mb-1">Grade Level</label>
                    <p class="mb-0 fw-medium">{{ $class->grade_level ?? 'All Levels' }}</p>
                </div>

                <div class="mb-3">
                    <label class="form-label text-muted small mb-1">Location</label>
                    <p class="mb-0">
                        @if($class->type == 'online')
                            <i class="fas fa-video text-info me-2"></i>
                            Online Class
                            @if($class->meeting_link)
                                <br>
                                <a href="{{ $class->meeting_link }}" target="_blank" class="btn btn-sm btn-outline-primary mt-2">
                                    <i class="fas fa-external-link-alt me-1"></i> Join Meeting
                                </a>
                            @endif
                        @else
                            <i class="fas fa-map-marker-alt text-danger me-2"></i>
                            {{ $class->location ?? 'Location TBD' }}
                        @endif
                    </p>
                </div>

                @if($class->description)
                    <div class="mb-0">
                        <label class="form-label text-muted small mb-1">Description</label>
                        <p class="mb-0">{{ $class->description }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Enrollment Status -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-user-graduate me-2"></i> Enrollment Status
            </div>
            <div class="card-body">
                @php
                    $activeEnrollments = $class->enrollments->where('status', 'active')->count();
                    $enrollmentPercentage = $class->capacity > 0 ? ($activeEnrollments / $class->capacity) * 100 : 0;
                @endphp

                <div class="text-center mb-3">
                    <h2 class="mb-0">{{ $activeEnrollments }} / {{ $class->capacity }}</h2>
                    <small class="text-muted">Students Enrolled</small>
                </div>

                <div class="progress mb-3" style="height: 15px;">
                    <div class="progress-bar bg-{{ $enrollmentPercentage >= 90 ? 'danger' : ($enrollmentPercentage >= 70 ? 'warning' : 'success') }}"
                         style="width: {{ $enrollmentPercentage }}%">
                        {{ round($enrollmentPercentage) }}%
                    </div>
                </div>

                <div class="row text-center">
                    <div class="col-6">
                        <h4 class="mb-0 text-success">{{ $activeEnrollments }}</h4>
                        <small class="text-muted">Active</small>
                    </div>
                    <div class="col-6">
                        <h4 class="mb-0 text-secondary">{{ $class->capacity - $activeEnrollments }}</h4>
                        <small class="text-muted">Available</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attendance Statistics -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-chart-pie me-2"></i> Attendance Statistics
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6 mb-3">
                        <h4 class="mb-0 text-primary">{{ $attendanceStats['total_sessions'] }}</h4>
                        <small class="text-muted">Total Sessions</small>
                    </div>
                    <div class="col-6 mb-3">
                        <h4 class="mb-0 text-success">{{ $attendanceStats['present_rate'] }}%</h4>
                        <small class="text-muted">Present Rate</small>
                    </div>
                    <div class="col-6">
                        <h4 class="mb-0 text-info">{{ $attendanceStats['present_count'] }}</h4>
                        <small class="text-muted">Present</small>
                    </div>
                    <div class="col-6">
                        <h4 class="mb-0 text-danger">{{ $attendanceStats['absent_count'] }}</h4>
                        <small class="text-muted">Absent</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="col-lg-8">
        <!-- Weekly Schedule -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-calendar-alt me-2"></i> Class Schedule</span>
                <a href="{{ route('teacher.classes.schedule', $class) }}" class="btn btn-sm btn-outline-primary">
                    View Full Schedule
                </a>
            </div>
            <div class="card-body">
                @if($class->schedules->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Day</th>
                                    <th>Time</th>
                                    <th>Duration</th>
                                    <th>Room</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($class->schedules as $schedule)
                                    @php
                                        $isToday = strtolower(now()->format('l')) == $schedule->day_of_week;
                                    @endphp
                                    <tr class="{{ $isToday ? 'table-primary' : '' }}">
                                        <td>
                                            <strong>{{ ucfirst($schedule->day_of_week) }}</strong>
                                            @if($isToday)
                                                <span class="badge bg-primary ms-1">Today</span>
                                            @endif
                                        </td>
                                        <td>
                                            {{ \Carbon\Carbon::parse($schedule->start_time)->format('h:i A') }} -
                                            {{ \Carbon\Carbon::parse($schedule->end_time)->format('h:i A') }}
                                        </td>
                                        <td>
                                            @php
                                                $duration = \Carbon\Carbon::parse($schedule->start_time)->diffInMinutes(\Carbon\Carbon::parse($schedule->end_time));
                                            @endphp
                                            {{ floor($duration / 60) }}h {{ $duration % 60 }}m
                                        </td>
                                        <td>{{ $schedule->room ?? $class->location ?? 'Online' }}</td>
                                        <td>
                                            @if($schedule->is_active)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-secondary">Inactive</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                        <p class="text-muted mb-0">No schedule set for this class.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Upcoming Sessions -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-clock me-2"></i> Upcoming Sessions
            </div>
            <div class="card-body">
                @if($upcomingSessions->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($upcomingSessions as $session)
                            <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div>
                                    <h6 class="mb-1">
                                        {{ \Carbon\Carbon::parse($session->session_date)->format('l, d M Y') }}
                                    </h6>
                                    <small class="text-muted">
                                        <i class="fas fa-clock me-1"></i>
                                        {{ \Carbon\Carbon::parse($session->start_time)->format('h:i A') }} -
                                        {{ \Carbon\Carbon::parse($session->end_time)->format('h:i A') }}
                                    </small>
                                </div>
                                <div>
                                    @if($session->status == 'scheduled')
                                        <span class="badge bg-primary">Scheduled</span>
                                    @elseif($session->status == 'completed')
                                        <span class="badge bg-success">Completed</span>
                                    @else
                                        <span class="badge bg-secondary">{{ ucfirst($session->status) }}</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-calendar-check fa-3x text-muted mb-3"></i>
                        <p class="text-muted mb-0">No upcoming sessions.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Enrolled Students Preview -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-users me-2"></i> Enrolled Students</span>
                <a href="{{ route('teacher.classes.students', $class) }}" class="btn btn-sm btn-outline-primary">
                    View All ({{ $class->enrollments->where('status', 'active')->count() }})
                </a>
            </div>
            <div class="card-body">
                @if($class->enrollments->where('status', 'active')->count() > 0)
                    <div class="row">
                        @foreach($class->enrollments->where('status', 'active')->take(6) as $enrollment)
                            <div class="col-md-6 mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="user-avatar me-3"
                                         style="width: 45px; height: 45px; font-size: 1rem; background: linear-gradient(135deg, #9c27b0 0%, #7b1fa2 100%);">
                                        {{ substr($enrollment->student->user->name ?? 'S', 0, 1) }}
                                    </div>
                                    <div>
                                        <h6 class="mb-0">{{ $enrollment->student->user->name ?? 'N/A' }}</h6>
                                        <small class="text-muted">{{ $enrollment->student->student_id ?? '' }}</small>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @if($class->enrollments->where('status', 'active')->count() > 6)
                        <div class="text-center mt-2">
                            <small class="text-muted">
                                +{{ $class->enrollments->where('status', 'active')->count() - 6 }} more students
                            </small>
                        </div>
                    @endif
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-user-graduate fa-3x text-muted mb-3"></i>
                        <p class="text-muted mb-0">No students enrolled yet.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Recent Materials -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-file-alt me-2"></i> Recent Materials</span>
                @if(Route::has('teacher.materials.index'))
                <a href="{{ route('teacher.materials.index', ['class_id' => $class->id]) }}" class="btn btn-sm btn-outline-primary">
                    Manage Materials
                </a>
                @endif
            </div>
            <div class="card-body">
                @if($class->materials->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($class->materials as $material)
                            <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div>
                                    <i class="fas fa-file-{{ $material->type == 'pdf' ? 'pdf text-danger' : ($material->type == 'video' ? 'video text-primary' : 'alt text-secondary') }} me-2"></i>
                                    <span>{{ $material->title }}</span>
                                    <br>
                                    <small class="text-muted">{{ $material->created_at->diffForHumans() }}</small>
                                </div>
                                <span class="badge bg-{{ $material->status == 'published' ? 'success' : 'secondary' }}">
                                    {{ ucfirst($material->status) }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                        <p class="text-muted mb-0">No materials uploaded yet.</p>
                    </div>
                @endif
            </div>
        </div>
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
    color: white;
    font-weight: bold;
}
</style>
@endpush
