@extends('layouts.app')

@section('title', 'Assign Schedule')
@section('page-title', 'Assign Teaching Schedule')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1><i class="fas fa-calendar-plus me-2"></i> Assign Teaching Schedule</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.teachers.index') }}">Teachers</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.teachers.show', $teacher) }}">{{ $teacher->user->name }}</a></li>
                <li class="breadcrumb-item active">Schedule Assignment</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('admin.teachers.show', $teacher) }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back to Teacher
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- Teacher Info Card -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-1">
                <div class="user-avatar" style="width: 60px; height: 60px; font-size: 1.5rem; background: linear-gradient(135deg, #4caf50 0%, #2e7d32 100%);">
                    {{ substr($teacher->user->name, 0, 1) }}
                </div>
            </div>
            <div class="col-md-5">
                <h5 class="mb-0">{{ $teacher->user->name }}</h5>
                <span class="badge bg-info">{{ $teacher->teacher_id }}</span>
                <span class="text-muted ms-2">{{ $teacher->specialization ?? 'Teacher' }}</span>
            </div>
            <div class="col-md-6">
                <div class="row text-center">
                    <div class="col-3">
                        <h5 class="text-primary mb-0">{{ $teacher->classes->count() }}</h5>
                        <small class="text-muted">Total Classes</small>
                    </div>
                    <div class="col-3">
                        <h5 class="text-success mb-0">{{ $scheduleStats['total_weekly_classes'] }}</h5>
                        <small class="text-muted">Weekly Sessions</small>
                    </div>
                    <div class="col-3">
                        <h5 class="text-info mb-0">{{ $scheduleStats['total_weekly_hours'] }}</h5>
                        <small class="text-muted">Hours/Week</small>
                    </div>
                    <div class="col-3">
                        <h5 class="mb-0">{{ $scheduleStats['busiest_day'] }}</h5>
                        <small class="text-muted">Busiest Day</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Assigned Classes -->
    <div class="col-md-5">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-school me-2"></i> Assigned Classes</span>
                <span class="badge bg-primary">{{ $teacher->classes->count() }}</span>
            </div>
            <div class="card-body p-0">
                @if($teacher->classes->count() > 0)
                <div class="list-group list-group-flush">
                    @foreach($teacher->classes as $class)
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1">{{ $class->name }}</h6>
                                <small class="text-muted">
                                    <i class="fas fa-book me-1"></i> {{ $class->subject->name ?? 'N/A' }}
                                </small>
                            </div>
                            <span class="badge {{ $class->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                                {{ ucfirst($class->status) }}
                            </span>
                        </div>
                        <div class="mt-2">
                            @if($class->schedules->count() > 0)
                                @foreach($class->schedules as $schedule)
                                <small class="d-block text-muted">
                                    <i class="fas fa-clock me-1"></i>
                                    {{ ucfirst($schedule->day_of_week) }}:
                                    {{ \Carbon\Carbon::parse($schedule->start_time)->format('h:i A') }} -
                                    {{ \Carbon\Carbon::parse($schedule->end_time)->format('h:i A') }}
                                </small>
                                @endforeach
                            @else
                                <small class="text-warning">
                                    <i class="fas fa-exclamation-triangle me-1"></i> No schedule set
                                </small>
                            @endif
                        </div>
                        <div class="mt-2">
                            <a href="{{ route('admin.classes.schedule.index', $class) }}"
                               class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-calendar-alt me-1"></i> Manage Schedule
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-school fa-2x mb-2"></i>
                    <p class="mb-0">No classes assigned</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Assign New Class -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-plus-circle me-2"></i> Assign New Class
            </div>
            <div class="card-body">
                <form action="{{ route('admin.teachers.assign-class', $teacher) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Select Class <span class="text-danger">*</span></label>
                        <select name="class_id" class="form-select" required>
                            <option value="">Choose a class...</option>
                            @foreach($availableClasses as $class)
                                <option value="{{ $class->id }}">
                                    {{ $class->name }} - {{ $class->subject->name ?? 'N/A' }}
                                    @if($class->teacher_id)
                                        (Currently: {{ $class->teacher->user->name ?? 'N/A' }})
                                    @else
                                        (Unassigned)
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-plus me-1"></i> Assign Class
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Weekly Schedule Overview -->
    <div class="col-md-7">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-calendar-week me-2"></i> Weekly Schedule Overview
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 100px;">Time</th>
                                @foreach(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day)
                                <th class="text-center">{{ ucfirst(substr($day, 0, 3)) }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $allSchedules = collect();
                                foreach($teacher->classes as $class) {
                                    foreach($class->schedules as $schedule) {
                                        $allSchedules->push([
                                            'class' => $class,
                                            'schedule' => $schedule,
                                        ]);
                                    }
                                }

                                $timeSlots = $allSchedules->pluck('schedule.start_time')->unique()->sort();
                            @endphp

                            @if($timeSlots->count() > 0)
                                @foreach($timeSlots as $timeSlot)
                                <tr>
                                    <td class="bg-light">
                                        <strong>{{ \Carbon\Carbon::parse($timeSlot)->format('h:i A') }}</strong>
                                    </td>
                                    @foreach(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day)
                                    <td class="p-1">
                                        @foreach($allSchedules as $item)
                                            @if($item['schedule']->day_of_week === $day && $item['schedule']->start_time === $timeSlot)
                                            <div class="schedule-item p-2 rounded text-white"
                                                 style="background: linear-gradient(135deg, #4caf50 0%, #2e7d32 100%); font-size: 0.75rem;">
                                                <strong>{{ $item['class']->name }}</strong><br>
                                                <small>
                                                    {{ \Carbon\Carbon::parse($item['schedule']->start_time)->format('h:i') }} -
                                                    {{ \Carbon\Carbon::parse($item['schedule']->end_time)->format('h:i') }}
                                                </small>
                                            </div>
                                            @endif
                                        @endforeach
                                    </td>
                                    @endforeach
                                </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">
                                        <i class="fas fa-calendar-times fa-2x mb-2"></i><br>
                                        No schedules configured for this teacher
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Schedule Conflicts Warning -->
        @if(isset($conflicts) && $conflicts->count() > 0)
        <div class="card mt-4 border-danger">
            <div class="card-header bg-danger text-white">
                <i class="fas fa-exclamation-triangle me-2"></i> Schedule Conflicts Detected
            </div>
            <div class="card-body">
                <ul class="mb-0">
                    @foreach($conflicts as $conflict)
                    <li>
                        <strong>{{ $conflict['day'] }}:</strong>
                        {{ $conflict['class1'] }} overlaps with {{ $conflict['class2'] }}
                        ({{ $conflict['time_range'] }})
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif

        <!-- Quick Add Schedule -->
        <div class="card mt-4">
            <div class="card-header">
                <i class="fas fa-plus me-2"></i> Quick Add Schedule
            </div>
            <div class="card-body">
                @if($teacher->classes->count() > 0)
                <form action="{{ route('admin.teachers.schedule.quick-add', $teacher) }}" method="POST">
                    @csrf
                    <div class="row g-2">
                        <div class="col-md-3">
                            <select name="class_id" class="form-select form-select-sm" required>
                                <option value="">Select Class</option>
                                @foreach($teacher->classes as $class)
                                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="day_of_week" class="form-select form-select-sm" required>
                                <option value="">Day</option>
                                @foreach(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day)
                                    <option value="{{ $day }}">{{ ucfirst($day) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="time" name="start_time" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-2">
                            <input type="time" name="end_time" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-success btn-sm w-100">
                                <i class="fas fa-plus me-1"></i> Add Schedule
                            </button>
                        </div>
                    </div>
                </form>
                @else
                <div class="text-center text-muted">
                    <p class="mb-0">Assign a class first to add schedules.</p>
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

.schedule-item {
    min-height: 50px;
}
</style>
@endpush
