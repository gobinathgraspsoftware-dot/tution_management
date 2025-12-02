@extends('layouts.app')

@section('title', 'Weekly Timetable')
@section('page-title', 'Weekly Timetable')

@section('content')
<div class="container-fluid">
    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.classes.timetable') }}">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Filter by Teacher</label>
                        <select name="teacher_id" class="form-select">
                            <option value="">All Teachers</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}" {{ request('teacher_id') == $teacher->id ? 'selected' : '' }}>
                                    {{ $teacher->user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Filter by Subject</label>
                        <select name="subject_id" class="form-select">
                            <option value="">All Subjects</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                                    {{ $subject->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">&nbsp;</label>
                        <div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter me-2"></i>Filter
                            </button>
                            <a href="{{ route('admin.classes.timetable') }}" class="btn btn-secondary">
                                <i class="fas fa-redo me-2"></i>Reset
                            </a>
                            <a href="{{ route('admin.classes.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Back to Classes
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Timetable -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-calendar-week me-2"></i>Weekly Class Schedule
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered timetable-table">
                    <thead>
                        <tr>
                            <th style="width: 100px;">Time</th>
                            @foreach($days as $day)
                                <th class="text-center">{{ ucfirst($day) }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            // Get all unique time slots
                            $timeSlots = [];
                            foreach($timetable as $daySchedules) {
                                foreach($daySchedules as $schedule) {
                                    $startTime = \Carbon\Carbon::parse($schedule->start_time)->format('H:i');
                                    if (!in_array($startTime, $timeSlots)) {
                                        $timeSlots[] = $startTime;
                                    }
                                }
                            }
                            sort($timeSlots);
                        @endphp

                        @forelse($timeSlots as $timeSlot)
                            <tr>
                                <td class="text-center align-middle" style="background-color: #f8f9fa;">
                                    <strong>{{ \Carbon\Carbon::parse($timeSlot)->format('h:i A') }}</strong>
                                </td>
                                @foreach($days as $day)
                                    @php
                                        $daySchedules = $timetable[$day]->filter(function($schedule) use ($timeSlot) {
                                            $scheduleStart = \Carbon\Carbon::parse($schedule->start_time)->format('H:i');
                                            return $scheduleStart === $timeSlot;
                                        });
                                    @endphp
                                    <td class="align-top">
                                        @foreach($daySchedules as $schedule)
                                            <div class="class-card mb-2"
                                                 style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                                                        color: white;
                                                        padding: 10px;
                                                        border-radius: 8px;
                                                        box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div class="flex-grow-1">
                                                        <strong class="d-block">{{ $schedule->class->name }}</strong>
                                                        <small class="d-block">
                                                            <i class="fas fa-book me-1"></i>{{ $schedule->class->subject->name }}
                                                        </small>
                                                        @if($schedule->class->teacher)
                                                            <small class="d-block">
                                                                <i class="fas fa-user me-1"></i>{{ $schedule->class->teacher->user->name }}
                                                            </small>
                                                        @endif
                                                        <small class="d-block mt-1">
                                                            <i class="fas fa-clock me-1"></i>
                                                            {{ \Carbon\Carbon::parse($schedule->start_time)->format('h:i A') }} -
                                                            {{ \Carbon\Carbon::parse($schedule->end_time)->format('h:i A') }}
                                                        </small>
                                                        @if($schedule->class->type === 'online')
                                                            <small class="d-block">
                                                                <i class="fas fa-video me-1"></i>Online
                                                            </small>
                                                        @else
                                                            <small class="d-block">
                                                                <i class="fas fa-map-marker-alt me-1"></i>{{ $schedule->class->location }}
                                                            </small>
                                                        @endif
                                                        <small class="d-block">
                                                            <i class="fas fa-users me-1"></i>
                                                            {{ $schedule->class->current_enrollment }}/{{ $schedule->class->capacity }}
                                                        </small>
                                                    </div>
                                                    <div class="ms-2">
                                                        <a href="{{ route('admin.classes.show', $schedule->class) }}"
                                                           class="btn btn-sm btn-light"
                                                           title="View Details">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($days) + 1 }}" class="text-center text-muted py-5">
                                    <i class="fas fa-calendar-times fa-3x mb-3 d-block"></i>
                                    No schedules found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Legend -->
            <div class="mt-4">
                <h6>Legend:</h6>
                <div class="row">
                    <div class="col-md-3">
                        <span class="badge bg-primary me-2"><i class="fas fa-video"></i></span> Online Class
                    </div>
                    <div class="col-md-3">
                        <span class="badge bg-secondary me-2"><i class="fas fa-map-marker-alt"></i></span> Offline Class
                    </div>
                    <div class="col-md-3">
                        <span class="badge bg-success me-2"><i class="fas fa-users"></i></span> Enrollment Status
                    </div>
                    <div class="col-md-3">
                        <span class="badge bg-info me-2"><i class="fas fa-clock"></i></span> Class Duration
                    </div>
                </div>
            </div>

            <!-- Print Button -->
            <div class="mt-4 text-center">
                <button onclick="window.print()" class="btn btn-primary">
                    <i class="fas fa-print me-2"></i>Print Timetable
                </button>
                <a href="{{ route('admin.classes.export') }}" class="btn btn-outline-primary">
                    <i class="fas fa-download me-2"></i>Export All Classes
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<style>
.timetable-table {
    font-size: 0.9rem;
}

.timetable-table th,
.timetable-table td {
    vertical-align: top;
    padding: 0.75rem;
}

.timetable-table td {
    min-height: 100px;
}

.class-card {
    transition: transform 0.2s;
}

.class-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2) !important;
}

@media print {
    .card-header,
    .btn,
    .sidebar,
    .top-header {
        display: none !important;
    }

    .timetable-table {
        font-size: 0.75rem;
    }

    .class-card {
        page-break-inside: avoid;
    }
}
</style>
@endpush
