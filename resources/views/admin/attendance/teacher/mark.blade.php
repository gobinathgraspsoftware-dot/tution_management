@extends('layouts.app')

@section('title', 'Mark Teacher Attendance')
@section('page-title', 'Mark Teacher Attendance')

@section('content')
<div class="page-header">
    <h1><i class="fas fa-chalkboard-teacher me-2"></i> Mark Teacher Attendance</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.attendance.index') }}">Attendance</a></li>
            <li class="breadcrumb-item active">Mark Teacher Attendance</li>
        </ol>
    </nav>
</div>

<!-- Filter Form -->
<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-calendar me-2"></i> Select Date
    </div>
    <div class="card-body">
        <form method="GET" class="row align-items-end">
            <div class="col-md-4">
                <label class="form-label">Date <span class="text-danger">*</span></label>
                <input type="date" name="date" class="form-control"
                       value="{{ $selectedDate }}" max="{{ now()->format('Y-m-d') }}" required>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search me-1"></i> Load Teachers
                </button>
            </div>
            <div class="col-md-6 text-end">
                <a href="{{ route('admin.attendance.teacher.calendar') }}" class="btn btn-info">
                    <i class="fas fa-calendar-alt me-1"></i> View Calendar
                </a>
                <a href="{{ route('admin.attendance.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
                </a>
            </div>
        </form>
    </div>
</div>

@if($teachers->isNotEmpty())
<!-- Attendance Form -->
<form action="{{ route('admin.attendance.teacher.store') }}" method="POST">
    @csrf
    <input type="hidden" name="date" value="{{ $selectedDate }}">

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-list me-2"></i> Teacher List ({{ $teachers->count() }} Teachers)</span>
            <div>
                <button type="button" class="btn btn-sm btn-success me-2" onclick="markAll('present')">
                    <i class="fas fa-check-double"></i> All Present
                </button>
                <button type="button" class="btn btn-sm btn-danger" onclick="markAll('absent')">
                    <i class="fas fa-times-circle"></i> All Absent
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th width="5%">#</th>
                            <th width="20%">Teacher</th>
                            <th width="12%">Teacher ID</th>
                            <th width="15%">Status <span class="text-danger">*</span></th>
                            <th width="12%">Time In</th>
                            <th width="12%">Time Out</th>
                            <th width="24%">Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($teachers as $index => $teacher)
                        @php
                            $existing = $attendanceRecords->get($teacher->id);
                        @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="user-avatar me-2 bg-success">
                                        {{ substr($teacher->user->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <strong>{{ $teacher->user->name }}</strong><br>
                                        <small class="text-muted">{{ $teacher->specialization ?? 'Teacher' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $teacher->teacher_id }}</td>
                            <td>
                                <select name="attendance[{{ $teacher->id }}][status]"
                                        class="form-select form-select-sm status-select" required>
                                    <option value="">Select Status</option>
                                    <option value="present" {{ $existing && $existing->status == 'present' ? 'selected' : '' }}>
                                        Present
                                    </option>
                                    <option value="absent" {{ $existing && $existing->status == 'absent' ? 'selected' : '' }}>
                                        Absent
                                    </option>
                                    <option value="half_day" {{ $existing && $existing->status == 'half_day' ? 'selected' : '' }}>
                                        Half Day
                                    </option>
                                    <option value="leave" {{ $existing && $existing->status == 'leave' ? 'selected' : '' }}>
                                        On Leave
                                    </option>
                                </select>
                            </td>
                            <td>
                                <input type="time" name="attendance[{{ $teacher->id }}][time_in]"
                                       class="form-control form-control-sm"
                                       value="{{ $existing ? $existing->time_in?->format('H:i') : '' }}">
                            </td>
                            <td>
                                <input type="time" name="attendance[{{ $teacher->id }}][time_out]"
                                       class="form-control form-control-sm"
                                       value="{{ $existing ? $existing->time_out?->format('H:i') : '' }}">
                            </td>
                            <td>
                                <input type="text" name="attendance[{{ $teacher->id }}][remarks]"
                                       class="form-control form-control-sm"
                                       placeholder="Optional remarks"
                                       value="{{ $existing ? $existing->remarks : '' }}">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Summary Card -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row text-center">
                <div class="col-md-3">
                    <div class="summary-stat">
                        <i class="fas fa-users fa-2x text-primary mb-2"></i>
                        <h4>{{ $teachers->count() }}</h4>
                        <p class="text-muted mb-0">Total Teachers</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="summary-stat">
                        <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                        <h4 id="presentCount">0</h4>
                        <p class="text-muted mb-0">Present</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="summary-stat">
                        <i class="fas fa-times-circle fa-2x text-danger mb-2"></i>
                        <h4 id="absentCount">0</h4>
                        <p class="text-muted mb-0">Absent</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="summary-stat">
                        <i class="fas fa-calendar-minus fa-2x text-warning mb-2"></i>
                        <h4 id="leaveCount">0</h4>
                        <p class="text-muted mb-0">On Leave</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Submit Buttons -->
    <div class="d-flex justify-content-end gap-2 mb-4">
        <a href="{{ route('admin.attendance.index') }}" class="btn btn-secondary">
            <i class="fas fa-times me-1"></i> Cancel
        </a>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-1"></i> Save Attendance
        </button>
    </div>
</form>
@else
<div class="alert alert-info">
    <i class="fas fa-info-circle me-2"></i> No active teachers found in the system.
</div>
@endif
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Update counts when status changes
    $('.status-select').on('change', updateCounts);

    // Initial count on page load
    updateCounts();
});

function markAll(status) {
    $('.status-select').val(status);
    updateCounts();
}

function updateCounts() {
    let presentCount = 0;
    let absentCount = 0;
    let leaveCount = 0;

    $('.status-select').each(function() {
        const status = $(this).val();
        if (status === 'present') presentCount++;
        else if (status === 'absent') absentCount++;
        else if (status === 'leave' || status === 'half_day') leaveCount++;
    });

    $('#presentCount').text(presentCount);
    $('#absentCount').text(absentCount);
    $('#leaveCount').text(leaveCount);
}
</script>
@endpush

@push('styles')
<style>
.user-avatar {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    background: linear-gradient(135deg, #4caf50 0%, #2e7d32 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 14px;
}

.summary-stat {
    padding: 15px;
}

.table-responsive {
    max-height: 600px;
    overflow-y: auto;
}

.table thead th {
    position: sticky;
    top: 0;
    background-color: #fff;
    z-index: 10;
    box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.1);
}
</style>
@endpush
