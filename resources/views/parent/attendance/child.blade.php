@extends('layouts.parent')

@section('title', $child->user->name . ' - Attendance')

@section('content')
<div class="container-fluid">
    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">{{ $child->user->name }}'s Attendance</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('parent.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('parent.attendance.index') }}">Attendance</a></li>
                    <li class="breadcrumb-item active">{{ $child->user->name }}</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-success" onclick="printReport()">
                <i class="fas fa-print me-2"></i>Print
            </button>
            <a href="{{ route('parent.attendance.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back
            </a>
        </div>
    </div>

    {{-- Student Info Card --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-auto">
                    <div class="avatar avatar-xl bg-primary text-white rounded-circle">
                        <span class="avatar-text">{{ substr($child->user->name, 0, 1) }}</span>
                    </div>
                </div>
                <div class="col">
                    <h4 class="mb-1">{{ $child->user->name }}</h4>
                    <p class="text-muted mb-0">
                        <span class="me-3"><i class="fas fa-id-card me-1"></i>{{ $child->student_id }}</span>
                        @if($child->enrollments->count() > 0)
                            <span><i class="fas fa-school me-1"></i>{{ $child->enrollments->count() }} Classes Enrolled</span>
                        @endif
                    </p>
                </div>
                <div class="col-auto text-end">
                    <div class="h2 mb-0 text-{{ $overallPercentage >= 85 ? 'success' : ($overallPercentage >= 75 ? 'warning' : 'danger') }}">
                        {{ number_format($overallPercentage, 1) }}%
                    </div>
                    <small class="text-muted">Overall Attendance</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Period --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('parent.attendance.child', $child->id) }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="month" class="form-label">Month</label>
                    <select name="month" id="month" class="form-select">
                        @for($i = 1; $i <= 12; $i++)
                            <option value="{{ $i }}" {{ $selectedMonth == $i ? 'selected' : '' }}>
                                {{ date('F', mktime(0, 0, 0, $i, 1)) }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="year" class="form-label">Year</label>
                    <select name="year" id="year" class="form-select">
                        @for($y = date('Y'); $y >= date('Y') - 2; $y--)
                            <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="class_id" class="form-label">Class</label>
                    <select name="class_id" id="class_id" class="form-select">
                        <option value="">All Classes</option>
                        @foreach($child->enrollments as $enrollment)
                            <option value="{{ $enrollment->class_id }}" {{ request('class_id') == $enrollment->class_id ? 'selected' : '' }}>
                                {{ $enrollment->class->name ?? 'N/A' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter me-2"></i>Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        {{-- Statistics --}}
        <div class="col-lg-4">
            {{-- Summary Card --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-pie me-2 text-primary"></i>
                        {{ date('F Y', mktime(0, 0, 0, $selectedMonth, 1, $selectedYear)) }} Summary
                    </h5>
                </div>
                <div class="card-body">
                    {{-- Circular Progress --}}
                    <div class="text-center mb-4">
                        <div class="position-relative d-inline-block">
                            <svg viewBox="0 0 36 36" class="circular-chart" style="width: 150px; height: 150px;">
                                <path class="circle-bg"
                                    d="M18 2.0845
                                    a 15.9155 15.9155 0 0 1 0 31.831
                                    a 15.9155 15.9155 0 0 1 0 -31.831"
                                    fill="none"
                                    stroke="#eee"
                                    stroke-width="3"
                                />
                                <path class="circle"
                                    stroke-dasharray="{{ $monthlyStats['percentage'] }}, 100"
                                    d="M18 2.0845
                                    a 15.9155 15.9155 0 0 1 0 31.831
                                    a 15.9155 15.9155 0 0 1 0 -31.831"
                                    fill="none"
                                    stroke="{{ $monthlyStats['percentage'] >= 85 ? '#28a745' : ($monthlyStats['percentage'] >= 75 ? '#ffc107' : '#dc3545') }}"
                                    stroke-width="3"
                                    stroke-linecap="round"
                                />
                            </svg>
                            <div class="position-absolute top-50 start-50 translate-middle text-center">
                                <div class="h3 mb-0">{{ number_format($monthlyStats['percentage'], 1) }}%</div>
                                <small class="text-muted">Attendance</small>
                            </div>
                        </div>
                    </div>

                    {{-- Stats List --}}
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span><i class="fas fa-calendar text-primary me-2"></i>Total Sessions</span>
                            <span class="badge bg-primary rounded-pill">{{ $monthlyStats['total_sessions'] }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span><i class="fas fa-check-circle text-success me-2"></i>Present</span>
                            <span class="badge bg-success rounded-pill">{{ $monthlyStats['present'] }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span><i class="fas fa-times-circle text-danger me-2"></i>Absent</span>
                            <span class="badge bg-danger rounded-pill">{{ $monthlyStats['absent'] }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span><i class="fas fa-clock text-warning me-2"></i>Late</span>
                            <span class="badge bg-warning text-dark rounded-pill">{{ $monthlyStats['late'] }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span><i class="fas fa-info-circle text-info me-2"></i>Excused</span>
                            <span class="badge bg-info rounded-pill">{{ $monthlyStats['excused'] }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Class-wise Breakdown --}}
            @if($classwiseStats->count() > 0)
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="fas fa-school me-2 text-info"></i>Class-wise Attendance
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            @foreach($classwiseStats as $classStat)
                                <li class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="fw-semibold">{{ $classStat['class_name'] }}</span>
                                        <span class="badge bg-{{ $classStat['percentage'] >= 85 ? 'success' : ($classStat['percentage'] >= 75 ? 'warning text-dark' : 'danger') }}">
                                            {{ number_format($classStat['percentage'], 1) }}%
                                        </span>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar bg-{{ $classStat['percentage'] >= 85 ? 'success' : ($classStat['percentage'] >= 75 ? 'warning' : 'danger') }}" 
                                             style="width: {{ $classStat['percentage'] }}%"></div>
                                    </div>
                                    <small class="text-muted">
                                        {{ $classStat['present'] }}/{{ $classStat['total'] }} sessions
                                    </small>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif
        </div>

        {{-- Attendance Calendar & Records --}}
        <div class="col-lg-8">
            {{-- Calendar View --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-alt me-2 text-success"></i>
                        Attendance Calendar - {{ date('F Y', mktime(0, 0, 0, $selectedMonth, 1, $selectedYear)) }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered text-center mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Sun</th>
                                    <th>Mon</th>
                                    <th>Tue</th>
                                    <th>Wed</th>
                                    <th>Thu</th>
                                    <th>Fri</th>
                                    <th>Sat</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $firstDay = date('w', mktime(0, 0, 0, $selectedMonth, 1, $selectedYear));
                                    $daysInMonth = date('t', mktime(0, 0, 0, $selectedMonth, 1, $selectedYear));
                                    $day = 1;
                                @endphp
                                @for($row = 0; $row < 6; $row++)
                                    @if($day > $daysInMonth) @break @endif
                                    <tr>
                                        @for($col = 0; $col < 7; $col++)
                                            @if(($row == 0 && $col < $firstDay) || $day > $daysInMonth)
                                                <td class="bg-light"></td>
                                            @else
                                                @php
                                                    $dateKey = sprintf('%04d-%02d-%02d', $selectedYear, $selectedMonth, $day);
                                                    $dayAttendance = $calendarData[$dateKey] ?? null;
                                                    $dayClass = '';
                                                    $dayIcon = '';
                                                    
                                                    if ($dayAttendance) {
                                                        switch($dayAttendance['status']) {
                                                            case 'present':
                                                                $dayClass = 'bg-success-subtle text-success';
                                                                $dayIcon = 'check';
                                                                break;
                                                            case 'absent':
                                                                $dayClass = 'bg-danger-subtle text-danger';
                                                                $dayIcon = 'times';
                                                                break;
                                                            case 'late':
                                                                $dayClass = 'bg-warning-subtle text-warning';
                                                                $dayIcon = 'clock';
                                                                break;
                                                            case 'excused':
                                                                $dayClass = 'bg-info-subtle text-info';
                                                                $dayIcon = 'info';
                                                                break;
                                                        }
                                                    }
                                                @endphp
                                                <td class="{{ $dayClass }}" 
                                                    @if($dayAttendance) title="{{ ucfirst($dayAttendance['status']) }} - {{ $dayAttendance['class_name'] ?? 'N/A' }}" @endif>
                                                    <div class="fw-semibold">{{ $day }}</div>
                                                    @if($dayIcon)
                                                        <i class="fas fa-{{ $dayIcon }} small"></i>
                                                    @endif
                                                </td>
                                                @php $day++; @endphp
                                            @endif
                                        @endfor
                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>
                    
                    {{-- Legend --}}
                    <div class="d-flex justify-content-center gap-4 mt-3">
                        <span><span class="badge bg-success">&nbsp;</span> Present</span>
                        <span><span class="badge bg-danger">&nbsp;</span> Absent</span>
                        <span><span class="badge bg-warning">&nbsp;</span> Late</span>
                        <span><span class="badge bg-info">&nbsp;</span> Excused</span>
                    </div>
                </div>
            </div>

            {{-- Detailed Records --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-list me-2 text-warning"></i>Attendance Records
                    </h5>
                    <span class="badge bg-secondary">{{ $attendanceRecords->count() }} Records</span>
                </div>
                <div class="card-body p-0">
                    @if($attendanceRecords->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Time</th>
                                        <th>Class</th>
                                        <th>Subject</th>
                                        <th class="text-center">Status</th>
                                        <th>Remarks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($attendanceRecords as $record)
                                        <tr>
                                            <td>
                                                <div class="fw-semibold">
                                                    {{ $record->classSession->session_date->format('d/m/Y') }}
                                                </div>
                                                <small class="text-muted">
                                                    {{ $record->classSession->session_date->format('l') }}
                                                </small>
                                            </td>
                                            <td>
                                                {{ $record->classSession->start_time->format('H:i') }} - 
                                                {{ $record->classSession->end_time->format('H:i') }}
                                            </td>
                                            <td>{{ $record->classSession->class->name ?? 'N/A' }}</td>
                                            <td>{{ $record->classSession->class->subject->name ?? 'N/A' }}</td>
                                            <td class="text-center">
                                                @php
                                                    $statusConfig = [
                                                        'present' => ['success', 'check-circle'],
                                                        'absent' => ['danger', 'times-circle'],
                                                        'late' => ['warning', 'clock'],
                                                        'excused' => ['info', 'info-circle']
                                                    ];
                                                    $config = $statusConfig[$record->status] ?? ['secondary', 'question'];
                                                @endphp
                                                <span class="badge bg-{{ $config[0] }}">
                                                    <i class="fas fa-{{ $config[1] }} me-1"></i>
                                                    {{ ucfirst($record->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                <small class="text-muted">{{ $record->remarks ?? '-' }}</small>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">No attendance records for this period.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.avatar-xl {
    width: 70px;
    height: 70px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.avatar-text {
    font-weight: 600;
    font-size: 28px;
}
.circular-chart {
    transform: rotate(-90deg);
}
.circle-bg {
    fill: none;
}
.circle {
    fill: none;
    stroke-width: 3;
    stroke-linecap: round;
    animation: progress 1s ease-out forwards;
}
@keyframes progress {
    0% {
        stroke-dasharray: 0 100;
    }
}
.bg-success-subtle { background-color: rgba(40, 167, 69, 0.15) !important; }
.bg-danger-subtle { background-color: rgba(220, 53, 69, 0.15) !important; }
.bg-warning-subtle { background-color: rgba(255, 193, 7, 0.15) !important; }
.bg-info-subtle { background-color: rgba(23, 162, 184, 0.15) !important; }
</style>
@endpush

@push('scripts')
<script>
function printReport() {
    window.print();
}
</script>
@endpush
