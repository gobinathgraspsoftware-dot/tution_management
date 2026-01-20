@extends('layouts.app')

@section('title', 'My Schedule')
@section('page-title', 'My Teaching Schedule')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1><i class="fas fa-calendar-alt me-2"></i> My Teaching Schedule</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('teacher.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">My Schedule</li>
            </ol>
        </nav>
    </div>
    <div class="btn-group">
        <button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
            <i class="fas fa-download me-1"></i> Export
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
            <li>
                <a class="dropdown-item" href="{{ route('teacher.schedule.export', ['format' => 'pdf', 'view' => $view, 'date' => $date->copy()->format('Y-m-d')]) }}">
                    <i class="fas fa-file-pdf me-2 text-danger"></i> Export PDF
                </a>
            </li>
            <li>
                <a class="dropdown-item" href="{{ route('teacher.schedule.export', ['format' => 'csv', 'view' => $view, 'date' => $date->copy()->format('Y-m-d')]) }}">
                    <i class="fas fa-file-csv me-2 text-success"></i> Export CSV
                </a>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
                <a class="dropdown-item" href="{{ route('teacher.schedule.sync-calendar') }}">
                    <i class="fas fa-calendar-plus me-2 text-info"></i> Sync to Calendar
                </a>
            </li>
        </ul>
    </div>
</div>

<!-- Schedule Statistics -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-0">{{ $stats['total_weekly_classes'] ?? 0 }}</h3>
                        <small>Weekly Classes</small>
                    </div>
                    <i class="fas fa-chalkboard fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-0">{{ $stats['total_weekly_hours'] ?? 0 }}</h3>
                        <small>Hours/Week</small>
                    </div>
                    <i class="fas fa-clock fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-0">{{ $stats['busiest_day'] ?? 'N/A' }}</h3>
                        <small>Busiest Day</small>
                    </div>
                    <i class="fas fa-calendar-day fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-dark">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-0">{{ $todaySessions->count() }}</h3>
                        <small>Classes Today</small>
                    </div>
                    <i class="fas fa-calendar-check fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- View Toggle & Date Navigation -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-4">
                <div class="btn-group" role="group">
                    <a href="{{ route('teacher.schedule.index', ['view' => 'daily', 'date' => $date->copy()->format('Y-m-d')]) }}"
                       class="btn {{ $view === 'daily' ? 'btn-primary' : 'btn-outline-primary' }}">
                        <i class="fas fa-calendar-day me-1"></i> Daily
                    </a>
                    <a href="{{ route('teacher.schedule.index', ['view' => 'weekly', 'date' => $date->copy()->format('Y-m-d')]) }}"
                       class="btn {{ $view === 'weekly' ? 'btn-primary' : 'btn-outline-primary' }}">
                        <i class="fas fa-calendar-week me-1"></i> Weekly
                    </a>
                    <a href="{{ route('teacher.schedule.index', ['view' => 'monthly', 'date' => $date->copy()->format('Y-m-d')]) }}"
                       class="btn {{ $view === 'monthly' ? 'btn-primary' : 'btn-outline-primary' }}">
                        <i class="fas fa-calendar me-1"></i> Monthly
                    </a>
                </div>
            </div>
            <div class="col-md-4 text-center">
                <div class="d-flex justify-content-center align-items-center">
                    @php
                        // IMPORTANT: Use copy() to avoid mutating the original $date
                        if ($view === 'monthly') {
                            $prevDate = $date->copy()->subMonth();
                            $nextDate = $date->copy()->addMonth();
                        } elseif ($view === 'weekly') {
                            $prevDate = $date->copy()->subWeek();
                            $nextDate = $date->copy()->addWeek();
                        } else {
                            $prevDate = $date->copy()->subDay();
                            $nextDate = $date->copy()->addDay();
                        }
                    @endphp
                    <a href="{{ route('teacher.schedule.index', ['view' => $view, 'date' => $prevDate->format('Y-m-d')]) }}"
                       class="btn btn-sm btn-outline-secondary me-2">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                    <h5 class="mb-0">
                        @if($view === 'monthly')
                            {{ $date->copy()->format('F Y') }}
                        @elseif($view === 'weekly')
                            {{ $date->copy()->startOfWeek()->format('d M') }} - {{ $date->copy()->endOfWeek()->format('d M Y') }}
                        @else
                            {{ $date->copy()->format('l, d F Y') }}
                        @endif
                    </h5>
                    <a href="{{ route('teacher.schedule.index', ['view' => $view, 'date' => $nextDate->format('Y-m-d')]) }}"
                       class="btn btn-sm btn-outline-secondary ms-2">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </div>
            </div>
            <div class="col-md-4 text-end">
                <a href="{{ route('teacher.schedule.index', ['view' => $view]) }}" class="btn btn-outline-info">
                    <i class="fas fa-calendar-day me-1"></i> Today
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Today's Sessions (Quick View) -->
@if($todaySessions->count() > 0)
<div class="card mb-4 border-success">
    <div class="card-header bg-success text-white">
        <i class="fas fa-clock me-2"></i> Today's Classes ({{ now()->format('l, d M Y') }})
    </div>
    <div class="card-body">
        <div class="row">
            @foreach($todaySessions as $session)
            <div class="col-md-4 mb-3">
                <div class="card h-100 {{ $session->start_time <= now()->format('H:i:s') && $session->end_time >= now()->format('H:i:s') ? 'border-primary' : '' }}">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="card-title mb-0">{{ $session->class->name ?? 'N/A' }}</h6>
                            @if($session->start_time <= now()->format('H:i:s') && $session->end_time >= now()->format('H:i:s'))
                                <span class="badge bg-primary">In Progress</span>
                            @elseif($session->start_time > now()->format('H:i:s'))
                                <span class="badge bg-info">Upcoming</span>
                            @else
                                <span class="badge bg-secondary">Completed</span>
                            @endif
                        </div>
                        <p class="text-muted small mb-2">
                            <i class="fas fa-book me-1"></i> {{ $session->class->subject->name ?? 'N/A' }}
                        </p>
                        <p class="text-muted small mb-0">
                            <i class="fas fa-clock me-1"></i> 
                            {{ \Carbon\Carbon::parse($session->start_time)->format('h:i A') }} - 
                            {{ \Carbon\Carbon::parse($session->end_time)->format('h:i A') }}
                        </p>
                        @if($session->class->enrollments ?? false)
                        <p class="text-muted small mb-0">
                            <i class="fas fa-users me-1"></i> {{ $session->class->enrollments->count() }} Students
                        </p>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif

<!-- Main Schedule View -->
@if($view === 'daily')
    <!-- Daily View -->
    <div class="card">
        <div class="card-header">
            <i class="fas fa-calendar-day me-2"></i> Daily Schedule - {{ $date->copy()->format('l, d F Y') }}
        </div>
        <div class="card-body">
            @if(isset($scheduleData['schedules']) && $scheduleData['schedules']->count() > 0)
                <div class="timeline">
                    @foreach($scheduleData['schedules'] as $schedule)
                    <div class="timeline-item mb-4">
                        <div class="row">
                            <div class="col-md-2 text-end">
                                <h5 class="text-primary mb-0">
                                    {{ \Carbon\Carbon::parse($schedule->start_time)->format('h:i A') }}
                                </h5>
                                <small class="text-muted">
                                    {{ \Carbon\Carbon::parse($schedule->end_time)->format('h:i A') }}
                                </small>
                            </div>
                            <div class="col-md-10">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h5 class="card-title mb-1">{{ $schedule->class->name ?? 'N/A' }}</h5>
                                                <p class="text-muted mb-2">
                                                    <i class="fas fa-book me-1"></i> {{ $schedule->class->subject->name ?? 'N/A' }}
                                                </p>
                                            </div>
                                            @if($schedule->class->enrollments ?? false)
                                            <span class="badge bg-success">
                                                {{ $schedule->class->enrollments->count() }} Students
                                            </span>
                                            @endif
                                        </div>
                                        @if($schedule->location)
                                        <p class="text-muted small mb-2">
                                            <i class="fas fa-map-marker-alt me-1"></i> {{ $schedule->location }}
                                        </p>
                                        @endif
                                        <div class="d-flex gap-2">
                                            @if(Route::has('teacher.classes.show'))
                                            <a href="{{ route('teacher.classes.show', $schedule->class) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye me-1"></i> View Class
                                            </a>
                                            @endif
                                            @if(Route::has('teacher.attendance.mark'))
                                            <a href="{{ route('teacher.attendance.mark', ['session' => $schedule->id]) }}" class="btn btn-sm btn-outline-success">
                                                <i class="fas fa-check-square me-1"></i> Take Attendance
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
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-calendar-times fa-3x mb-3"></i>
                    <h5>No Classes Scheduled</h5>
                    <p>You don't have any classes scheduled for this day.</p>
                </div>
            @endif
        </div>
    </div>

@elseif($view === 'weekly')
    <!-- Weekly View -->
    <div class="card">
        <div class="card-header">
            <i class="fas fa-calendar-week me-2"></i> Weekly Schedule
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered mb-0">
                    <thead class="table-light">
                        <tr>
                            @php
                                $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                            @endphp
                            @foreach($days as $day)
                                @php
                                    $dayData = $scheduleData['schedule'][$day] ?? null;
                                    $isToday = $dayData && ($dayData['is_today'] ?? false);
                                @endphp
                                <th class="text-center {{ $isToday ? 'bg-primary text-white' : '' }}" style="width: 14.28%;">
                                    {{ ucfirst($day) }}
                                    @if($dayData)
                                        <br><small>{{ \Carbon\Carbon::parse($dayData['date'])->format('d M') }}</small>
                                    @endif
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            @foreach($days as $day)
                                @php
                                    $dayData = $scheduleData['schedule'][$day] ?? null;
                                    $daySchedules = $dayData['schedules'] ?? collect();
                                @endphp
                                <td class="align-top p-2" style="min-height: 150px;">
                                    @forelse($daySchedules as $schedule)
                                        <div class="card mb-2 border-start border-3 border-primary">
                                            <div class="card-body p-2">
                                                <h6 class="card-title mb-1 small">{{ $schedule->class->name ?? 'N/A' }}</h6>
                                                <p class="text-muted mb-1 small">
                                                    <i class="fas fa-clock me-1"></i>
                                                    {{ \Carbon\Carbon::parse($schedule->start_time)->format('h:i A') }}
                                                </p>
                                                <p class="text-muted mb-0 small">
                                                    <i class="fas fa-book me-1"></i>
                                                    {{ Str::limit($schedule->class->subject->name ?? 'N/A', 15) }}
                                                </p>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="text-center text-muted py-3">
                                            <small>No classes</small>
                                        </div>
                                    @endforelse
                                </td>
                            @endforeach
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@else
    <!-- Monthly View -->
    <div class="card">
        <div class="card-header">
            <i class="fas fa-calendar me-2"></i> Monthly Schedule - {{ $scheduleData['month_name'] ?? $date->copy()->format('F Y') }}
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered mb-0 calendar-table">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center">Sun</th>
                            <th class="text-center">Mon</th>
                            <th class="text-center">Tue</th>
                            <th class="text-center">Wed</th>
                            <th class="text-center">Thu</th>
                            <th class="text-center">Fri</th>
                            <th class="text-center">Sat</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $month = $scheduleData['month'] ?? $date->month;
                            $year = $scheduleData['year'] ?? $date->year;
                            $firstDay = \Carbon\Carbon::createFromDate($year, $month, 1);
                            $startPadding = $firstDay->dayOfWeek;
                            $daysInMonth = $firstDay->daysInMonth;
                            $day = 1;
                            $weeks = ceil(($startPadding + $daysInMonth) / 7);
                            $calendar = $scheduleData['calendar'] ?? [];
                        @endphp

                        @for($week = 0; $week < $weeks; $week++)
                        <tr>
                            @for($weekDay = 0; $weekDay < 7; $weekDay++)
                                @php
                                    $cellIndex = $week * 7 + $weekDay;
                                    $currentDay = $cellIndex - $startPadding + 1;
                                @endphp

                                @if($currentDay > 0 && $currentDay <= $daysInMonth)
                                    @php
                                        $dateKey = sprintf('%04d-%02d-%02d', $year, $month, $currentDay);
                                        $dayData = $calendar[$dateKey] ?? null;
                                        $isToday = $dayData && ($dayData['is_today'] ?? false);
                                        $isWeekend = $dayData && ($dayData['is_weekend'] ?? false);
                                        $hasClasses = $dayData && ($dayData['has_classes'] ?? false);
                                    @endphp
                                    <td class="calendar-cell {{ $isToday ? 'bg-primary-light' : '' }} {{ $isWeekend ? 'bg-light' : '' }}">
                                        <div class="calendar-day-number {{ $isToday ? 'fw-bold text-primary' : '' }}">
                                            {{ $currentDay }}
                                        </div>
                                        @if($hasClasses && isset($dayData['schedules']))
                                            @foreach($dayData['schedules']->take(3) as $schedule)
                                            <div class="calendar-event small p-1 mb-1 rounded bg-success text-white"
                                                 title="{{ $schedule->class->name ?? 'Class' }} - {{ \Carbon\Carbon::parse($schedule->start_time)->format('h:i A') }}">
                                                {{ Str::limit($schedule->class->name ?? 'Class', 12) }}
                                            </div>
                                            @endforeach
                                            @if($dayData['schedules']->count() > 3)
                                                <small class="text-muted">+{{ $dayData['schedules']->count() - 3 }} more</small>
                                            @endif
                                        @endif
                                    </td>
                                @else
                                    <td class="calendar-cell bg-light"></td>
                                @endif
                            @endfor
                        </tr>
                        @endfor
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif
@endsection

@push('styles')
<style>
.calendar-table td {
    height: 120px;
    vertical-align: top;
    padding: 5px;
    width: 14.28%;
}

.calendar-day-number {
    font-weight: 500;
    margin-bottom: 5px;
}

.calendar-event {
    font-size: 0.75rem;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    cursor: pointer;
}

.calendar-event:hover {
    opacity: 0.9;
}

.bg-primary-light {
    background-color: rgba(13, 110, 253, 0.1) !important;
}

.schedule-item {
    font-size: 0.85rem;
}

.timeline-item {
    position: relative;
}

.timeline-item::before {
    content: '';
    position: absolute;
    left: calc(16.67% - 1px);
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e9ecef;
}

.timeline-item:last-child::before {
    display: none;
}
</style>
@endpush
