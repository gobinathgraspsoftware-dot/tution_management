@extends('layouts.app')

@section('title', 'Teacher Attendance Calendar')
@section('page-title', 'Teacher Attendance Calendar')

@section('content')
<div class="page-header">
    <h1><i class="fas fa-calendar-alt me-2"></i> Teacher Attendance Calendar</h1>
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
                                @endphp
                                <td class="calendar-cell {{ !$isCurrentMonth ? 'other-month' : '' }} {{ $isToday ? 'today' : '' }}">
                                    <div class="date-header">
                                        <strong>{{ $currentDate->day }}</strong>
                                    </div>

                                    @if($dayData && $isCurrentMonth)
                                        <div class="attendance-data">
                                            @if($dayData['status'])
                                                <div class="status-badge">
                                                    @php
                                                        $statusClass = [
                                                            'present' => 'bg-success',
                                                            'absent' => 'bg-danger',
                                                            'half_day' => 'bg-warning text-dark',
                                                            'leave' => 'bg-info',
                                                        ][$dayData['status']] ?? 'bg-secondary';
                                                    @endphp
                                                    <span class="badge {{ $statusClass }} w-100">
                                                        {{ ucfirst(str_replace('_', ' ', $dayData['status'])) }}
                                                    </span>
                                                </div>

                                                @if($dayData['time_in'] || $dayData['time_out'])
                                                    <div class="time-info mt-2">
                                                        @if($dayData['time_in'])
                                                            <small class="d-block text-muted">
                                                                <i class="fas fa-sign-in-alt"></i> {{ $dayData['time_in'] }}
                                                            </small>
                                                        @endif
                                                        @if($dayData['time_out'])
                                                            <small class="d-block text-muted">
                                                                <i class="fas fa-sign-out-alt"></i> {{ $dayData['time_out'] }}
                                                            </small>
                                                        @endif
                                                    </div>
                                                @endif

                                                @if($dayData['hours_worked'])
                                                    <div class="hours-badge mt-1">
                                                        <small class="badge bg-primary">
                                                            {{ number_format($dayData['hours_worked'], 1) }} hrs
                                                        </small>
                                                    </div>
                                                @endif

                                                @if($dayData['remarks'])
                                                    <div class="remarks-info mt-1">
                                                        <small class="text-muted" title="{{ $dayData['remarks'] }}">
                                                            <i class="fas fa-comment"></i>
                                                        </small>
                                                    </div>
                                                @endif
                                            @else
                                                <div class="no-record">
                                                    <small class="text-muted">No record</small>
                                                </div>
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

        <!-- Monthly Summary -->
        <div class="mt-4">
            @php
                $presentDays = collect($calendarData)->where('status', 'present')->count();
                $absentDays = collect($calendarData)->where('status', 'absent')->count();
                $halfDays = collect($calendarData)->where('status', 'half_day')->count();
                $leaveDays = collect($calendarData)->where('status', 'leave')->count();
                $totalHours = collect($calendarData)->sum('hours_worked');
                $workingDays = $presentDays + $halfDays;
                $totalDays = $presentDays + $absentDays + $halfDays + $leaveDays;
                $attendanceRate = $totalDays > 0 ? round(($workingDays / $totalDays) * 100, 1) : 0;
            @endphp

            <div class="row text-center">
                <div class="col-md-2">
                    <div class="summary-card">
                        <h4 class="text-success">{{ $presentDays }}</h4>
                        <small>Present Days</small>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="summary-card">
                        <h4 class="text-danger">{{ $absentDays }}</h4>
                        <small>Absent Days</small>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="summary-card">
                        <h4 class="text-warning">{{ $halfDays }}</h4>
                        <small>Half Days</small>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="summary-card">
                        <h4 class="text-info">{{ $leaveDays }}</h4>
                        <small>Leave Days</small>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="summary-card">
                        <h4 class="text-primary">{{ number_format($totalHours, 1) }}</h4>
                        <small>Total Hours</small>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="summary-card">
                        <h4 class="text-success">{{ $attendanceRate }}%</h4>
                        <small>Attendance Rate</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Legend -->
        <div class="mt-3">
            <h6>Legend:</h6>
            <span class="badge bg-success me-2"><i class="fas fa-check"></i> Present</span>
            <span class="badge bg-danger me-2"><i class="fas fa-times"></i> Absent</span>
            <span class="badge bg-warning text-dark me-2"><i class="fas fa-adjust"></i> Half Day</span>
            <span class="badge bg-info"><i class="fas fa-calendar-times"></i> On Leave</span>
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
    margin-bottom: 5px;
    font-weight: bold;
}

.attendance-data {
    font-size: 11px;
}

.status-badge {
    margin-bottom: 5px;
}

.status-badge .badge {
    font-size: 10px;
    padding: 3px 6px;
}

.time-info {
    font-size: 10px;
}

.hours-badge .badge {
    font-size: 9px;
}

.summary-card {
    padding: 15px;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    background: #f8f9fa;
}

.summary-card h4 {
    margin-bottom: 5px;
    font-weight: bold;
}

.summary-card small {
    color: #666;
}

.no-record {
    text-align: center;
    padding: 20px 0;
}
</style>
@endpush
