@extends('layouts.app')

@section('title', 'Student Attendance Calendar')
@section('page-title', 'Student Attendance Calendar')

@section('content')
<div class="page-header">
    <h1><i class="fas fa-calendar-alt me-2"></i> Student Attendance Calendar</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.attendance.index') }}">Attendance</a></li>
            <li class="breadcrumb-item active">Calendar</li>
        </ol>
    </nav>
</div>

<!-- Filter Form -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-5">
                <label class="form-label">Class</label>
                <select name="class_id" class="form-select" onchange="this.form.submit()">
                    <option value="">Select Class</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ $selectedClassId == $class->id ? 'selected' : '' }}>
                            {{ $class->name }} - {{ $class->subject->name ?? 'N/A' }}
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
                    <a href="{{ route('admin.attendance.student.mark') }}" class="btn btn-success">
                        <i class="fas fa-plus me-1"></i> Mark Attendance
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

@if($selectedClassId && !empty($calendarData))
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
                                            @foreach($dayData['sessions'] as $session)
                                                <div class="session-info mb-2">
                                                    <small class="text-muted d-block">{{ $session['time'] }}</small>
                                                    <div class="attendance-badges">
                                                        <span class="badge bg-success" title="Present">
                                                            <i class="fas fa-check"></i> {{ $session['summary']['present'] }}
                                                        </span>
                                                        <span class="badge bg-danger" title="Absent">
                                                            <i class="fas fa-times"></i> {{ $session['summary']['absent'] }}
                                                        </span>
                                                        @if($session['summary']['late'] > 0)
                                                            <span class="badge bg-warning" title="Late">
                                                                <i class="fas fa-clock"></i> {{ $session['summary']['late'] }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
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

        <!-- Legend -->
        <div class="mt-3">
            <h6>Legend:</h6>
            <span class="badge bg-success me-2"><i class="fas fa-check"></i> Present</span>
            <span class="badge bg-danger me-2"><i class="fas fa-times"></i> Absent</span>
            <span class="badge bg-warning me-2"><i class="fas fa-clock"></i> Late</span>
            <span class="badge bg-info"><i class="fas fa-user-slash"></i> Excused</span>
        </div>
    </div>
</div>
@elseif($selectedClassId)
<div class="alert alert-info">
    <i class="fas fa-info-circle me-2"></i> No attendance records found for the selected month.
</div>
@else
<div class="alert alert-warning">
    <i class="fas fa-exclamation-triangle me-2"></i> Please select a class to view the attendance calendar.
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
}

.attendance-data {
    font-size: 11px;
}

.session-info {
    padding: 4px;
    background: #f8f9fa;
    border-radius: 4px;
}

.attendance-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 2px;
    margin-top: 3px;
}

.attendance-badges .badge {
    font-size: 10px;
    padding: 2px 5px;
}
</style>
@endpush
