@extends('layouts.app')

@section('title', 'Class Details')
@section('page-title', 'Class Details')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0">{{ $class->name }}</h4>
            <span class="badge bg-{{ $class->type === 'online' ? 'primary' : 'secondary' }} me-2">
                {{ ucfirst($class->type) }}
            </span>
            <span class="badge bg-{{ $class->status === 'active' ? 'success' : ($class->status === 'full' ? 'danger' : 'secondary') }}">
                {{ ucfirst($class->status) }}
            </span>
        </div>
        <div>
            <a href="{{ route('admin.classes.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back
            </a>
            @can('edit-classes')
                <a href="{{ route('admin.classes.edit', $class) }}" class="btn btn-warning">
                    <i class="fas fa-edit me-2"></i>Edit
                </a>
            @endcan
            @can('manage-class-schedule')
                <a href="{{ route('admin.classes.schedule.index', $class) }}" class="btn btn-primary">
                    <i class="fas fa-calendar me-2"></i>Manage Schedule
                </a>
            @endcan
        </div>
    </div>

    <div class="row">
        <!-- Class Information -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Class Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <dl class="row">
                                <dt class="col-sm-5">Class Code:</dt>
                                <dd class="col-sm-7"><strong>{{ $class->code }}</strong></dd>

                                <dt class="col-sm-5">Subject:</dt>
                                <dd class="col-sm-7">
                                    <span class="badge bg-info">{{ $class->subject->name }}</span>
                                </dd>

                                <dt class="col-sm-5">Teacher:</dt>
                                <dd class="col-sm-7">
                                    @if($class->teacher)
                                        {{ $class->teacher->user->name }}
                                    @else
                                        <span class="text-muted">Not Assigned</span>
                                    @endif
                                </dd>

                                <dt class="col-sm-5">Grade Level:</dt>
                                <dd class="col-sm-7">{{ $class->grade_level ?? 'N/A' }}</dd>
                            </dl>
                        </div>
                        <div class="col-md-6">
                            <dl class="row">
                                <dt class="col-sm-5">Type:</dt>
                                <dd class="col-sm-7">
                                    @if($class->type === 'online')
                                        <span class="badge bg-primary">
                                            <i class="fas fa-video me-1"></i>Online
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">
                                            <i class="fas fa-chalkboard-teacher me-1"></i>Offline
                                        </span>
                                    @endif
                                </dd>

                                <dt class="col-sm-5">Location:</dt>
                                <dd class="col-sm-7">{{ $class->location ?? 'N/A' }}</dd>

                                <dt class="col-sm-5">Meeting Link:</dt>
                                <dd class="col-sm-7">
                                    @if($class->meeting_link)
                                        <a href="{{ $class->meeting_link }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-external-link-alt"></i> Join
                                        </a>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </dd>

                                <dt class="col-sm-5">Capacity:</dt>
                                <dd class="col-sm-7">
                                    <strong>{{ $class->current_enrollment }}/{{ $class->capacity }}</strong>
                                    <span class="text-muted">({{ $class->available_seats }} seats available)</span>
                                </dd>
                            </dl>
                        </div>
                    </div>

                    @if($class->description)
                        <div class="mt-3">
                            <strong>Description:</strong>
                            <p class="mt-2">{{ $class->description }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Class Schedule -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Class Schedule</h5>
                    @can('manage-class-schedule')
                        <a href="{{ route('admin.classes.schedule.index', $class) }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus me-1"></i>Manage
                        </a>
                    @endcan
                </div>
                <div class="card-body">
                    @if($class->schedules->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Day</th>
                                        <th>Time</th>
                                        <th>Duration</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($class->schedules as $schedule)
                                        <tr>
                                            <td><strong>{{ ucfirst($schedule->day_of_week) }}</strong></td>
                                            <td>
                                                {{ \Carbon\Carbon::parse($schedule->start_time)->format('h:i A') }} -
                                                {{ \Carbon\Carbon::parse($schedule->end_time)->format('h:i A') }}
                                            </td>
                                            <td>
                                                {{ \Carbon\Carbon::parse($schedule->start_time)->diffInMinutes($schedule->end_time) }} minutes
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $schedule->is_active ? 'success' : 'secondary' }}">
                                                    {{ $schedule->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted text-center py-3">No schedule set for this class</p>
                    @endif
                </div>
            </div>

            <!-- Enrolled Students -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Enrolled Students ({{ $stats['total_students'] }})</h5>
                </div>
                <div class="card-body">
                    @if($class->enrollments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Student ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Enrollment Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($class->enrollments->take(10) as $enrollment)
                                        <tr>
                                            <td>{{ $enrollment->student->student_id }}</td>
                                            <td>{{ $enrollment->student->user->name }}</td>
                                            <td>{{ $enrollment->student->user->email }}</td>
                                            <td>{{ $enrollment->created_at->format('d M Y') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if($class->enrollments->count() > 10)
                            <p class="text-center text-muted mt-2">
                                Showing 10 of {{ $class->enrollments->count() }} students
                            </p>
                        @endif
                    @else
                        <p class="text-muted text-center py-3">No students enrolled yet</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Statistics Sidebar -->
        <div class="col-md-4">
            <!-- Stats Cards -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted">Total Students</small>
                        <h3 class="mb-0">{{ $stats['total_students'] }}</h3>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <small class="text-muted">Attendance Rate</small>
                        <h3 class="mb-0">{{ $stats['attendance_rate'] }}%</h3>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-success" style="width: {{ $stats['attendance_rate'] }}%"></div>
                        </div>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <small class="text-muted">Sessions Completed</small>
                        <h3 class="mb-0">{{ $stats['sessions_completed'] }}</h3>
                    </div>
                    <hr>
                    <div class="mb-0">
                        <small class="text-muted">Upcoming Sessions</small>
                        <h3 class="mb-0">{{ $stats['sessions_upcoming'] }}</h3>
                    </div>
                </div>
            </div>

            <!-- Upcoming Sessions -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Upcoming Sessions</h5>
                </div>
                <div class="card-body">
                    @if($class->sessions->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($class->sessions as $session)
                                <div class="list-group-item px-0">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>{{ $session->session_date->format('d M Y') }}</strong><br>
                                            <small class="text-muted">
                                                {{ \Carbon\Carbon::parse($session->start_time)->format('h:i A') }}
                                            </small>
                                        </div>
                                        <span class="badge bg-info">{{ ucfirst($session->status) }}</span>
                                    </div>
                                    @if($session->topic)
                                        <p class="mb-0 mt-1 small">{{ $session->topic }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted text-center py-3">No upcoming sessions</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
