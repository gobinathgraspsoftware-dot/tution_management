@extends('layouts.app')

@section('title', 'Student Attendance Calendar')
@section('page-title', 'Student Attendance Calendar')

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

.attendance-indicator {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    display: inline-block;
    margin: 1px;
}

.attendance-indicator.present { background-color: #28a745; }
.attendance-indicator.absent { background-color: #dc3545; }
.attendance-indicator.late { background-color: #ffc107; }
.attendance-indicator.excused { background-color: #17a2b8; }

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
        <i class="fas fa-calendar-alt me-2"></i> Student Attendance Calendar
    </h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('staff.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('staff.attendance.index') }}">Attendance</a></li>
            <li class="breadcrumb-item active">Student Calendar</li>
        </ol>
    </nav>
</div>

<!-- Filter Card -->
<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-filter me-2"></i> Filter Options
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('staff.attendance.student.calendar') }}" id="filterForm">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="class_id" class="form-label">Class <span class="text-danger">*</span></label>
                    <select name="class_id" id="class_id" class="form-select" required>
                        <option value="">-- Select Class --</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ $selectedClassId == $class->id ? 'selected' : '' }}>
                                {{ $class->name }} - {{ $class->subject->name ?? 'N/A' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="student_id" class="form-label">Student <small class="text-muted">(Optional)</small></label>
                    <select name="student_id" id="student_id" class="form-select" {{ !$selectedClassId ? 'disabled' : '' }}>
                        <option value="">-- All Students --</option>
                        @foreach($students as $student)
                            <option value="{{ $student->id }}" {{ $selectedStudentId == $student->id ? 'selected' : '' }}>
                                {{ $student->student_id }} - {{ $student->user->name ?? 'N/A' }}
                            </option>
                        @endforeach
                    </select>
                    {{-- @if(!$selectedClassId)
                    <small class="text-muted">Select a class first</small>
                    @endif --}}
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

@if($selectedStudentId || $selectedClassId)
<!-- Statistics Cards -->
@if($stats && is_array($stats) && count($stats) > 0)
<div class="row mb-4">
    <div class="col-md-2">
        <div class="stat-mini-card">
            <div class="stat-value text-primary">{{ $stats['total_sessions'] ?? 0 }}</div>
            <div class="stat-label">Total Sessions</div>
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
            <div class="stat-value text-warning">{{ $stats['late'] ?? 0 }}</div>
            <div class="stat-label">Late</div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="stat-mini-card">
            <div class="stat-value text-info">{{ $stats['excused'] ?? 0 }}</div>
            <div class="stat-label">Excused</div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="stat-mini-card">
            <div class="stat-value {{ ($stats['percentage'] ?? 0) >= 80 ? 'text-success' : 'text-danger' }}">
                {{ $stats['percentage'] ?? 0 }}%
            </div>
            <div class="stat-label">Attendance Rate</div>
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
            @if($selectedStudent)
                - {{ $selectedStudent->user->name ?? 'Student' }}
            @elseif($selectedClassId)
                - {{ $classes->firstWhere('id', $selectedClassId)?->name ?? 'Class' }}
            @endif
        </span>
        <div>
            <span class="badge bg-success me-1"><i class="fas fa-circle text-xs me-1"></i> Present</span>
            <span class="badge bg-danger me-1"><i class="fas fa-circle text-xs me-1"></i> Absent</span>
            <span class="badge bg-warning me-1"><i class="fas fa-circle text-xs me-1"></i> Late</span>
            <span class="badge bg-info"><i class="fas fa-circle text-xs me-1"></i> Excused</span>
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
                    $dateKey = $currentDate->format('Y-m-d');
                    $dayData = $calendarData[$dateKey] ?? null;
                @endphp

                <div class="calendar-day {{ !$isCurrentMonth ? 'other-month' : '' }} {{ $isToday ? 'today' : '' }}">
                    <div class="calendar-day-number">{{ $currentDate->day }}</div>

                    @if($dayData && $isCurrentMonth)
                        <div class="mt-1">
                            @if(isset($dayData['status']))
                                {{-- Student view - single status --}}
                                <span class="attendance-indicator {{ $dayData['status'] }}"
                                      title="{{ ucfirst($dayData['status']) }}"></span>
                            @elseif(isset($dayData['sessions']))
                                {{-- Class view - multiple sessions --}}
                                @foreach($dayData['sessions'] as $session)
                                    <span class="attendance-indicator present" title="Session"></span>
                                @endforeach
                            @elseif(isset($dayData['summary']))
                                {{-- Summary view --}}
                                @for($i = 0; $i < min($dayData['summary']['present'] ?? 0, 3); $i++)
                                    <span class="attendance-indicator present"></span>
                                @endfor
                                @for($i = 0; $i < min($dayData['summary']['absent'] ?? 0, 3); $i++)
                                    <span class="attendance-indicator absent"></span>
                                @endfor
                            @endif
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
@else
<!-- No Selection -->
<div class="card">
    <div class="card-body text-center py-5">
        <i class="fas fa-hand-pointer text-muted fa-4x mb-3"></i>
        <h5>Select a Class</h5>
        <p class="text-muted">Please select a class above to view the attendance calendar.<br>
        You can then optionally filter by a specific student.</p>
    </div>
</div>
@endif

<!-- Export Section - Only available when specific student is selected -->
@if($selectedStudentId)
<div class="card mt-4">
    <div class="card-header">
        <i class="fas fa-download me-2"></i> Export Options
    </div>
    <div class="card-body">
        @can('export-student-attendance')
        <a href="{{ route('staff.attendance.export-student', [
            'student_id' => $selectedStudentId,
            'class_id' => $selectedClassId,
            'date_from' => Carbon\Carbon::create($year, $month, 1)->toDateString(),
            'date_to' => Carbon\Carbon::create($year, $month)->endOfMonth()->toDateString(),
        ]) }}" class="btn btn-success">
            <i class="fas fa-file-excel me-2"></i> Export to Excel
        </a>
        @endcan
    </div>
</div>
@elseif($selectedClassId)
<div class="card mt-4">
    <div class="card-body text-center py-3">
        <i class="fas fa-info-circle text-info me-2"></i>
        <span class="text-muted">Select a specific student to enable export functionality.</span>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
// Auto-submit when class is selected (like Mark Student Attendance)
document.getElementById('class_id').addEventListener('change', function() {
    // Clear student selection when class changes
    document.getElementById('student_id').value = '';

    if (this.value) {
        // Submit form to load students for selected class
        document.getElementById('filterForm').submit();
    } else {
        // Disable student dropdown if no class selected
        document.getElementById('student_id').disabled = true;
    }
});

// Enable student dropdown if class is already selected
document.addEventListener('DOMContentLoaded', function() {
    const classId = document.getElementById('class_id').value;
    document.getElementById('student_id').disabled = !classId;
});
</script>
@endpush
