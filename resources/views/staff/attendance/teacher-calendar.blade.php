@extends('layouts.app')

@section('title', 'Teacher Attendance Calendar')
@section('page-title', 'Teacher Attendance Calendar')

@push('styles')
<style>
.calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 5px;
}

.calendar-day {
    aspect-ratio: 1;
    min-height: 60px;
    border: 1px solid #dee2e6;
    padding: 5px;
    font-size: 0.8rem;
    border-radius: 4px;
    position: relative;
}

.calendar-day.other-month {
    background-color: #f8f9fa;
    color: #adb5bd;
}

.calendar-day.today {
    border: 2px solid #0d6efd;
    background-color: #e3f2fd;
}

.calendar-day-number {
    font-weight: bold;
    font-size: 0.9rem;
}

.calendar-header {
    background-color: #f8f9fa;
    padding: 8px;
    text-align: center;
    font-weight: bold;
    border-radius: 4px;
}

.status-badge {
    font-size: 0.65rem;
    padding: 2px 4px;
    border-radius: 3px;
}

.stat-mini-card {
    background: white;
    border-radius: 8px;
    padding: 15px;
    text-align: center;
    border: 1px solid #dee2e6;
}

.stat-mini-card .stat-value {
    font-size: 1.5rem;
    font-weight: bold;
}

.stat-mini-card .stat-label {
    font-size: 0.8rem;
    color: #6c757d;
}
</style>
@endpush

@section('content')
<div class="page-header">
    <h1>
        <i class="fas fa-calendar-alt me-2"></i> Teacher Attendance Calendar
    </h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('staff.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('staff.attendance.index') }}">Attendance</a></li>
            <li class="breadcrumb-item active">Teacher Calendar</li>
        </ol>
    </nav>
</div>

<!-- Filter Card -->
<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-filter me-2"></i> Filter Options
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('staff.attendance.teacher.calendar') }}">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="teacher_id" class="form-label">Teacher <span class="text-danger">*</span></label>
                    <select name="teacher_id" id="teacher_id" class="form-select" required>
                        <option value="">-- Select Teacher --</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}" {{ $selectedTeacherId == $teacher->id ? 'selected' : '' }}>
                                {{ $teacher->user->name ?? 'N/A' }}
                                @if($teacher->employee_id) ({{ $teacher->employee_id }}) @endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="month" class="form-label">Month</label>
                    <select name="month" id="month" class="form-select">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                {{ Carbon\Carbon::create()->month($m)->format('F') }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="year" class="form-label">Year</label>
                    <select name="year" id="year" class="form-select">
                        @for($y = now()->year - 2; $y <= now()->year + 1; $y++)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>
                                {{ $y }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-2"></i> Filter
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@if($selectedTeacherId)
<!-- Statistics Cards -->
@if($stats && is_array($stats) && count($stats) > 0)
<div class="row mb-4">
    <div class="col-md-2">
        <div class="stat-mini-card">
            <div class="stat-value text-primary">{{ $stats['total_days'] ?? 0 }}</div>
            <div class="stat-label">Working Days</div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="stat-mini-card">
            <div class="stat-value text-success">{{ $stats['present'] ?? 0 }}</div>
            <div class="stat-label">Present</div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="stat-mini-card">
            <div class="stat-value text-danger">{{ $stats['absent'] ?? 0 }}</div>
            <div class="stat-label">Absent</div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="stat-mini-card">
            <div class="stat-value text-warning">{{ $stats['half_day'] ?? 0 }}</div>
            <div class="stat-label">Half Days</div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="stat-mini-card">
            <div class="stat-value text-info">{{ $stats['on_leave'] ?? 0 }}</div>
            <div class="stat-label">On Leave</div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="stat-mini-card">
            <div class="stat-value">{{ number_format($stats['total_hours'] ?? 0, 1) }}h</div>
            <div class="stat-label">Total Hours</div>
        </div>
    </div>
</div>
@endif

<!-- Calendar Card -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>
            <i class="fas fa-calendar me-2"></i> 
            {{ Carbon\Carbon::create($year, $month)->format('F Y') }}
        </span>
        <div>
            <span class="badge bg-success me-1">P - Present</span>
            <span class="badge bg-danger me-1">A - Absent</span>
            <span class="badge bg-warning me-1">H - Half Day</span>
            <span class="badge bg-info">L - Leave</span>
        </div>
    </div>
    <div class="card-body">
        @php
            $firstDay = Carbon\Carbon::create($year, $month, 1);
            $lastDay = $firstDay->copy()->endOfMonth();
            $startOfCalendar = $firstDay->copy()->startOfWeek();
            $endOfCalendar = $lastDay->copy()->endOfWeek();
            $today = Carbon\Carbon::today();
        @endphp

        <!-- Calendar Header -->
        <div class="calendar-grid mb-3">
            @foreach(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $dayName)
                <div class="calendar-header">{{ $dayName }}</div>
            @endforeach
        </div>

        <!-- Calendar Days -->
        <div class="calendar-grid">
            @php
                $currentDate = $startOfCalendar->copy();
            @endphp
            
            @while($currentDate <= $endOfCalendar)
                @php
                    $isCurrentMonth = $currentDate->month == $month;
                    $isToday = $currentDate->isSameDay($today);
                    $isWeekend = in_array($currentDate->dayOfWeek, [0, 6]); // Sunday=0, Saturday=6
                    $dateKey = $currentDate->format('Y-m-d');
                    $dayData = $calendarData[$dateKey] ?? null;
                @endphp
                
                <div class="calendar-day {{ !$isCurrentMonth ? 'other-month' : '' }} {{ $isToday ? 'today' : '' }}" 
                     style="{{ $isWeekend && $isCurrentMonth ? 'background-color: #f0f0f0;' : '' }}">
                    <div class="calendar-day-number">{{ $currentDate->day }}</div>
                    
                    @if($dayData && $isCurrentMonth)
                        <div class="mt-1">
                            @switch($dayData['status'] ?? '')
                                @case('present')
                                    <span class="status-badge bg-success text-white">P</span>
                                    @break
                                @case('absent')
                                    <span class="status-badge bg-danger text-white">A</span>
                                    @break
                                @case('half_day')
                                    <span class="status-badge bg-warning text-dark">H</span>
                                    @break
                                @case('on_leave')
                                    <span class="status-badge bg-info text-white">L</span>
                                    @break
                            @endswitch
                            
                            @if(isset($dayData['hours_worked']) && $dayData['hours_worked'] > 0)
                                <div class="text-muted" style="font-size: 0.6rem;">
                                    {{ number_format($dayData['hours_worked'], 1) }}h
                                </div>
                            @endif
                        </div>
                    @elseif($isCurrentMonth && !$isWeekend && $currentDate <= $today)
                        <div class="mt-1">
                            <span class="text-muted" style="font-size: 0.6rem;">-</span>
                        </div>
                    @endif
                </div>
                
                @php
                    $currentDate->addDay();
                @endphp
            @endwhile
        </div>
    </div>
</div>

<!-- Detailed Records -->
<div class="card mt-4">
    <div class="card-header">
        <i class="fas fa-list me-2"></i> Monthly Records
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Day</th>
                        <th>Status</th>
                        <th>Time In</th>
                        <th>Time Out</th>
                        <th>Hours</th>
                        <th>Remarks</th>
                        @can('edit-teacher-attendance')
                        <th>Action</th>
                        @endcan
                    </tr>
                </thead>
                <tbody>
                    @forelse($calendarData as $date => $data)
                    <tr>
                        <td>{{ Carbon\Carbon::parse($date)->format('d M Y') }}</td>
                        <td>{{ Carbon\Carbon::parse($date)->format('l') }}</td>
                        <td>
                            @switch($data['status'] ?? '')
                                @case('present')
                                    <span class="badge bg-success">Present</span>
                                    @break
                                @case('absent')
                                    <span class="badge bg-danger">Absent</span>
                                    @break
                                @case('half_day')
                                    <span class="badge bg-warning">Half Day</span>
                                    @break
                                @case('on_leave')
                                    <span class="badge bg-info">On Leave</span>
                                    @break
                            @endswitch
                        </td>
                        <td>{{ isset($data['time_in']) ? Carbon\Carbon::parse($data['time_in'])->format('h:i A') : '-' }}</td>
                        <td>{{ isset($data['time_out']) ? Carbon\Carbon::parse($data['time_out'])->format('h:i A') : '-' }}</td>
                        <td>{{ isset($data['hours_worked']) && $data['hours_worked'] > 0 ? number_format($data['hours_worked'], 1) . 'h' : '-' }}</td>
                        <td>{{ $data['remarks'] ?? '-' }}</td>
                        @can('edit-teacher-attendance')
                        <td>
                            @if(isset($data['id']))
                            <a href="{{ route('staff.attendance.edit-teacher', $data['id']) }}" 
                               class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-edit"></i>
                            </a>
                            @endif
                        </td>
                        @endcan
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4">
                            <i class="fas fa-inbox text-muted fa-2x mb-2"></i>
                            <p class="text-muted mb-0">No attendance records for this period.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@else
<!-- No Selection -->
<div class="card">
    <div class="card-body text-center py-5">
        <i class="fas fa-hand-pointer text-muted fa-4x mb-3"></i>
        <h5>Select a Teacher</h5>
        <p class="text-muted">Please select a teacher above to view their attendance calendar.</p>
    </div>
</div>
@endif
@endsection
