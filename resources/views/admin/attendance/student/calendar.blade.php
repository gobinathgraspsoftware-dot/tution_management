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
            <li class="breadcrumb-item active">Student Calendar</li>
        </ol>
    </nav>
</div>

<!-- Filter Form -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-5">
                <label class="form-label">Class <span class="text-danger">*</span></label>
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
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>
            <i class="fas fa-calendar me-2"></i>
            {{ \Carbon\Carbon::parse($selectedMonth)->format('F Y') }} Attendance Calendar
        </span>
        <span class="badge bg-primary">
            {{ $classes->find($selectedClassId)->name ?? 'Class' }}
        </span>
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
                                        @if($isToday)
                                            <span class="badge bg-warning text-dark ms-1" style="font-size: 8px;">Today</span>
                                        @endif
                                    </div>

                                    @if($dayData && $isCurrentMonth)
                                        <div class="attendance-data">
                                            @foreach($dayData['sessions'] as $session)
                                                <div class="session-info mb-2">
                                                    <small class="text-muted d-block fw-bold">
                                                        <i class="fas fa-clock"></i> {{ $session['time'] }}
                                                    </small>
                                                    @if($session['topic'])
                                                        <small class="text-muted d-block mb-1" style="font-size: 9px;">
                                                            {{ Str::limit($session['topic'], 20) }}
                                                        </small>
                                                    @endif
                                                    <div class="attendance-badges">
                                                        <span class="badge bg-success" title="Present">
                                                            <i class="fas fa-check"></i> {{ $session['summary']['present'] }}
                                                        </span>
                                                        <span class="badge bg-danger" title="Absent">
                                                            <i class="fas fa-times"></i> {{ $session['summary']['absent'] }}
                                                        </span>
                                                        @if($session['summary']['late'] > 0)
                                                            <span class="badge bg-warning text-dark" title="Late">
                                                                <i class="fas fa-clock"></i> {{ $session['summary']['late'] }}
                                                            </span>
                                                        @endif
                                                        @if($session['summary']['excused'] > 0)
                                                            <span class="badge bg-info" title="Excused">
                                                                <i class="fas fa-user-slash"></i> {{ $session['summary']['excused'] }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach

                                            <!-- Daily Summary -->
                                            @if(count($dayData['sessions']) > 1)
                                                <div class="daily-summary mt-2 pt-2 border-top">
                                                    <small class="text-muted d-block mb-1"><strong>Daily Total:</strong></small>
                                                    <div class="attendance-badges">
                                                        <span class="badge bg-success" style="font-size: 9px;">
                                                            {{ $dayData['summary']['present'] }}
                                                        </span>
                                                        <span class="badge bg-danger" style="font-size: 9px;">
                                                            {{ $dayData['summary']['absent'] }}
                                                        </span>
                                                        @if($dayData['summary']['late'] > 0)
                                                            <span class="badge bg-warning text-dark" style="font-size: 9px;">
                                                                {{ $dayData['summary']['late'] }}
                                                            </span>
                                                        @endif
                                                        @if($dayData['summary']['excused'] > 0)
                                                            <span class="badge bg-info" style="font-size: 9px;">
                                                                {{ $dayData['summary']['excused'] }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    @elseif($isCurrentMonth && $currentDate < now())
                                        <div class="no-record text-center">
                                            <small class="text-muted">No sessions</small>
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
                $totalPresent = collect($calendarData)->sum('summary.present');
                $totalAbsent = collect($calendarData)->sum('summary.absent');
                $totalLate = collect($calendarData)->sum('summary.late');
                $totalExcused = collect($calendarData)->sum('summary.excused');
                $totalRecords = $totalPresent + $totalAbsent + $totalLate + $totalExcused;
                $attendanceRate = $totalRecords > 0 ? round((($totalPresent + $totalLate) / $totalRecords) * 100, 1) : 0;
                $totalSessions = collect($calendarData)->sum(fn($day) => count($day['sessions']));
            @endphp

            <div class="row text-center">
                <div class="col-md-2">
                    <div class="summary-card">
                        <h4 class="text-primary">{{ $totalSessions }}</h4>
                        <small>Total Sessions</small>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="summary-card">
                        <h4 class="text-success">{{ $totalPresent }}</h4>
                        <small>Present</small>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="summary-card">
                        <h4 class="text-danger">{{ $totalAbsent }}</h4>
                        <small>Absent</small>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="summary-card">
                        <h4 class="text-warning">{{ $totalLate }}</h4>
                        <small>Late</small>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="summary-card">
                        <h4 class="text-info">{{ $totalExcused }}</h4>
                        <small>Excused</small>
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
        <div class="mt-3 p-3 bg-light rounded">
            <h6 class="mb-2">Legend:</h6>
            <div class="d-flex flex-wrap gap-2">
                <span class="badge bg-success"><i class="fas fa-check"></i> Present</span>
                <span class="badge bg-danger"><i class="fas fa-times"></i> Absent</span>
                <span class="badge bg-warning text-dark"><i class="fas fa-clock"></i> Late</span>
                <span class="badge bg-info"><i class="fas fa-user-slash"></i> Excused</span>
                <span class="badge bg-light text-dark border"><i class="fas fa-calendar-day"></i> Today</span>
            </div>
        </div>
    </div>
</div>
@elseif($selectedClassId)
<div class="alert alert-info">
    <i class="fas fa-info-circle me-2"></i> No attendance records found for the selected month.
    <a href="{{ route('admin.attendance.student.mark') }}" class="alert-link">Start marking attendance</a>
</div>
@else
<div class="alert alert-warning">
    <i class="fas fa-exclamation-triangle me-2"></i> Please select a class to view the attendance calendar.
</div>
@endif
@endsection

@push('styles')
<style>
/* Calendar Table */
.calendar-table {
    margin-bottom: 0;
}

.calendar-table th {
    background-color: #f8f9fa;
    font-weight: 600;
    padding: 10px;
    font-size: 14px;
}

.calendar-table td {
    height: 140px;
    vertical-align: top;
    padding: 8px;
    border: 1px solid #dee2e6;
}

/* Calendar Cells */
.calendar-cell {
    position: relative;
    min-height: 120px;
}

.calendar-cell.other-month {
    background-color: #f8f9fa;
    opacity: 0.5;
}

.calendar-cell.today {
    background-color: #fff3cd;
    border: 2px solid #ffc107 !important;
}

/* Date Header */
.date-header {
    font-size: 14px;
    margin-bottom: 8px;
    font-weight: bold;
    color: #495057;
}

.date-header .badge {
    font-size: 8px;
    padding: 2px 4px;
}

/* Attendance Data */
.attendance-data {
    font-size: 11px;
}

.session-info {
    padding: 6px;
    background: #f8f9fa;
    border-radius: 4px;
    border-left: 3px solid #007bff;
}

.attendance-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 3px;
    margin-top: 4px;
}

.attendance-badges .badge {
    font-size: 10px;
    padding: 3px 6px;
    font-weight: 500;
}

/* Daily Summary */
.daily-summary {
    padding-top: 5px;
    border-top: 1px dashed #dee2e6 !important;
}

.daily-summary .attendance-badges .badge {
    font-size: 9px;
    padding: 2px 4px;
}

/* No Record */
.no-record {
    padding: 30px 0;
    color: #adb5bd;
}

/* Summary Cards */
.summary-card {
    padding: 15px;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    background: #ffffff;
    transition: all 0.3s ease;
}

.summary-card:hover {
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.summary-card h4 {
    margin-bottom: 5px;
    font-weight: bold;
    font-size: 24px;
}

.summary-card small {
    color: #6c757d;
    font-size: 12px;
}

/* Responsive Design */
@media (max-width: 768px) {
    .calendar-table td {
        height: 100px;
        padding: 4px;
    }

    .date-header {
        font-size: 12px;
    }

    .session-info {
        padding: 4px;
    }

    .attendance-badges .badge {
        font-size: 8px;
        padding: 2px 4px;
    }

    .summary-card {
        margin-bottom: 10px;
    }
}

/* Print Styles */
@media print {
    .card-header,
    .btn,
    .alert,
    .page-header nav {
        display: none;
    }

    .calendar-table td {
        height: auto;
        page-break-inside: avoid;
    }
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-select current month if not selected
    const monthInput = $('input[name="month"]');
    if (!monthInput.val()) {
        const currentMonth = new Date().toISOString().slice(0, 7);
        monthInput.val(currentMonth);
    }
});
</script>
@endpush
