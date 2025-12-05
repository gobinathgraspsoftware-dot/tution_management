@extends('layouts.app')

@section('title', 'Teacher Attendance Calendar')
@section('page-title', 'Teacher Attendance Calendar')

@section('content')
<div class="page-header">
    <h1><i class="fas fa-calendar-check me-2"></i> Teacher Attendance Calendar</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.attendance.index') }}">Attendance</a></li>
            <li class="breadcrumb-item active">Teacher Calendar</li>
        </ol>
    </nav>
</div>

<!-- Filter Form -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-5">
                <label class="form-label">Teacher</label>
                <select name="teacher_id" class="form-select" onchange="this.form.submit()">
                    <option value="">Select Teacher</option>
                    @foreach($teachers as $teacher)
                        <option value="{{ $teacher->id }}" {{ $selectedTeacherId == $teacher->id ? 'selected' : '' }}>
                            {{ $teacher->user->name }} ({{ $teacher->teacher_id }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Month</label>
                <input type="month" name="month" class="form-control"
                       value="{{ $selectedMonth }}" onchange="this.form.submit()">
            </div>
            <div class="col-md-4">
                <label class="form-label">&nbsp;</label>
                <div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-1"></i> View Calendar
                    </button>
                    <a href="{{ route('admin.attendance.teacher.mark') }}" class="btn btn-success">
                        <i class="fas fa-plus me-1"></i> Mark Attendance
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

@if($selectedTeacherId && !empty($calendarData))
<!-- Calendar -->
<div class="card">
    <div class="card-header">
        <i class="fas fa-calendar me-2"></i>
        {{ \Carbon\Carbon::parse($selectedMonth)->format('F Y') }} Attendance Calendar
    </div>
    <div class="card-body">
        @php
            $date = \Carbon\Carbon::parse($selectedMonth . '-01');
            $startOfMonth = $date->copy()->startOfMonth();
            $endOfMonth = $date->copy()->endOfMonth();
            $startOfCalendar = $startOfMonth->copy()->startOfWeek();
            $endOfCalendar = $endOfMonth->copy()->endOfWeek();
            $currentDate = $startOfCalendar->copy();

            // Calculate summary stats
            $totalDays = 0;
            $presentDays = 0;
            $absentDays = 0;
            $halfDays = 0;
            $leaveDays = 0;
            $totalHours = 0;
        @endphp

        <div class="table-responsive">
            <table class="table table-bordered calendar-table">
                <thead>
                    <tr>
                        <th class="text-center">Sunday</th>
                        <th class="text-center">Monday</th>
                        <th class="text-center">Tuesday</th>
                        <th class="text-center">Wednesday</th>
                        <th class="text-center">Thursday</th>
                        <th class="text-center">Friday</th>
                        <th class="text-center">Saturday</th>
                    </tr>
                </thead>
                <tbody>
                    @while($currentDate <= $endOfCalendar)
                        <tr>
                            @for($i = 0; $i < 7; $i++)
                                @php
                                    $dateKey = $currentDate->format('Y-m-d');
                                    $dayData = $calendarData[$dateKey] ?? null;
                                    $isCurrentMonth = $currentDate->month == $date->month;
                                    $isToday = $currentDate->isToday();

                                    // Update stats
                                    if ($isCurrentMonth && $dayData && $dayData['status']) {
                                        $totalDays++;
                                        if ($dayData['status'] == 'present') $presentDays++;
                                        if ($dayData['status'] == 'absent') $absentDays++;
                                        if ($dayData['status'] == 'half_day') $halfDays++;
                                        if ($dayData['status'] == 'leave') $leaveDays++;
                                        if ($dayData['hours_worked']) $totalHours += $dayData['hours_worked'];
                                    }
                                @endphp
                                <td class="calendar-cell {{ !$isCurrentMonth ? 'other-month' : '' }} {{ $isToday ? 'today' : '' }}">
                                    <div class="date-header">
                                        <strong>{{ $currentDate->day }}</strong>
                                    </div>

                                    @if($dayData && $dayData['status'] && $isCurrentMonth)
                                        <div class="attendance-data">
                                            @if($dayData['status'] == 'present')
                                                <span class="badge bg-success w-100 mb-1">
                                                    <i class="fas fa-check"></i> Present
                                                </span>
                                            @elseif($dayData['status'] == 'absent')
                                                <span class="badge bg-danger w-100 mb-1">
                                                    <i class="fas fa-times"></i> Absent
                                                </span>
                                            @elseif($dayData['status'] == 'half_day')
                                                <span class="badge bg-warning w-100 mb-1">
                                                    <i class="fas fa-clock"></i> Half Day
                                                </span>
                                            @elseif($dayData['status'] == 'leave')
                                                <span class="badge bg-info w-100 mb-1">
                                                    <i class="fas fa-user-slash"></i> Leave
                                                </span>
                                            @endif

                                            @if($dayData['time_in'] && $dayData['time_out'])
                                                <small class="d-block text-muted">
                                                    <i class="fas fa-clock"></i>
                                                    {{ $dayData['time_in'] }} - {{ $dayData['time_out'] }}
                                                </small>
                                            @endif

                                            @if($dayData['hours_worked'])
                                                <small class="d-block text-primary">
                                                    <strong>{{ number_format($dayData['hours_worked'], 2) }}h</strong>
                                                </small>
                                            @endif

                                            @if($dayData['remarks'])
                                                <small class="d-block text-muted" title="{{ $dayData['remarks'] }}">
                                                    <i class="fas fa-comment"></i> Note
                                                </small>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                @php $currentDate->addDay(); @endphp
                            @endfor
                        </tr>
                    @endwhile
                </tbody>
            </table>
        </div>

        <!-- Summary Statistics -->
        <div class="row mt-4">
            <div class="col-md-12">
                <h6>Monthly Summary</h6>
            </div>
            <div class="col-md-2">
                <div class="stat-box">
                    <span class="badge bg-success">Present</span>
                    <h4>{{ $presentDays }}</h4>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-box">
                    <span class="badge bg-danger">Absent</span>
                    <h4>{{ $absentDays }}</h4>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-box">
                    <span class="badge bg-warning">Half Day</span>
                    <h4>{{ $halfDays }}</h4>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-box">
                    <span class="badge bg-info">Leave</span>
                    <h4>{{ $leaveDays }}</h4>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-box">
                    <span class="badge bg-primary">Total Hours</span>
                    <h4>{{ number_format($totalHours, 2) }}h</h4>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-box">
                    <span class="badge bg-secondary">Attendance %</span>
                    <h4>{{ $totalDays > 0 ? number_format(($presentDays / $totalDays) * 100, 1) : 0 }}%</h4>
                </div>
            </div>
        </div>

        <!-- Legend -->
        <div class="mt-3">
            <h6>Legend:</h6>
            <span class="badge bg-success me-2"><i class="fas fa-check"></i> Present</span>
            <span class="badge bg-danger me-2"><i class="fas fa-times"></i> Absent</span>
            <span class="badge bg-warning me-2"><i class="fas fa-clock"></i> Half Day</span>
            <span class="badge bg-info"><i class="fas fa-user-slash"></i> Leave</span>
        </div>
    </div>
</div>
@elseif($selectedTeacherId)
<div class="alert alert-info">
    <i class="fas fa-info-circle me-2"></i> No attendance records found for the selected month.
</div>
@else
<div class="alert alert-warning">
    <i class="fas fa-exclamation-triangle me-2"></i> Please select a teacher to view the attendance calendar.
</div>
@endif
@endsection

@push('styles')
<style>
.calendar-table {
    margin-bottom: 0;
}

.calendar-table td {
    height: 120px;
    vertical-align: top;
    padding: 8px;
}

.calendar-cell {
    position: relative;
}

.calendar-cell.other-month {
    background-color: #f8f9fa;
    opacity: 0.5;
}

.calendar-cell.today {
    background-color: #fff3cd;
}

.date-header {
    font-size: 14px;
    margin-bottom: 8px;
}

.attendance-data {
    font-size: 11px;
}

.stat-box {
    text-align: center;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    margin-bottom: 10px;
}

.stat-box h4 {
    margin: 10px 0 0 0;
    font-weight: bold;
}
</style>
@endpush
