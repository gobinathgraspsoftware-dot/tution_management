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

<!-- Date Selector -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Select Date</label>
                <input type="date" name="date" class="form-control"
                       value="{{ $selectedDate }}" onchange="this.form.submit()">
            </div>
            <div class="col-md-8">
                <label class="form-label">&nbsp;</label>
                <div>
                    <button type="button" class="btn btn-primary" onclick="setToday()">
                        <i class="fas fa-calendar-day me-1"></i> Today
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="setYesterday()">
                        <i class="fas fa-calendar-minus me-1"></i> Yesterday
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Attendance Form -->
<form action="{{ route('admin.attendance.teacher.store') }}" method="POST">
    @csrf
    <input type="hidden" name="date" value="{{ $selectedDate }}">

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>
                <i class="fas fa-list me-2"></i> Teacher List ({{ $teachers->count() }} Teachers)
                <small class="text-muted ms-2">{{ \Carbon\Carbon::parse($selectedDate)->format('l, d M Y') }}</small>
            </span>
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
                            <th width="10%">Teacher ID</th>
                            <th width="15%">Status</th>
                            <th width="12%">Time In</th>
                            <th width="12%">Time Out</th>
                            <th width="8%">Hours</th>
                            <th width="18%">Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($teachers as $index => $teacher)
                        @php
                            $existing = $attendanceRecords->get($teacher->id);
                            $hoursWorked = 0;
                            if ($existing && $existing->hours_worked) {
                                $hoursWorked = number_format($existing->hours_worked, 2);
                            }
                        @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="user-avatar me-2">{{ substr($teacher->user->name, 0, 1) }}</div>
                                    <div>
                                        <strong>{{ $teacher->user->name }}</strong><br>
                                        <small class="text-muted">{{ $teacher->specialization ?? 'N/A' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $teacher->teacher_id }}</td>
                            <td>
                                <select name="attendance[{{ $teacher->id }}][status]"
                                        class="form-select form-select-sm status-select" required>
                                    <option value="">Select</option>
                                    <option value="present" {{ $existing && $existing->status == 'present' ? 'selected' : '' }}>Present</option>
                                    <option value="absent" {{ $existing && $existing->status == 'absent' ? 'selected' : '' }}>Absent</option>
                                    <option value="half_day" {{ $existing && $existing->status == 'half_day' ? 'selected' : '' }}>Half Day</option>
                                    <option value="leave" {{ $existing && $existing->status == 'leave' ? 'selected' : '' }}>Leave</option>
                                </select>
                            </td>
                            <td>
                                <input type="time" name="attendance[{{ $teacher->id }}][time_in]"
                                       class="form-control form-control-sm time-in"
                                       data-teacher="{{ $teacher->id }}"
                                       value="{{ $existing ? $existing->time_in?->format('H:i') : '' }}">
                            </td>
                            <td>
                                <input type="time" name="attendance[{{ $teacher->id }}][time_out]"
                                       class="form-control form-control-sm time-out"
                                       data-teacher="{{ $teacher->id }}"
                                       value="{{ $existing ? $existing->time_out?->format('H:i') : '' }}">
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm hours-display"
                                       id="hours_{{ $teacher->id }}" readonly
                                       value="{{ $hoursWorked }}" placeholder="0.00">
                            </td>
                            <td>
                                <input type="text" name="attendance[{{ $teacher->id }}][remarks]"
                                       class="form-control form-control-sm"
                                       placeholder="Optional"
                                       value="{{ $existing ? $existing->remarks : '' }}">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
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
@endsection

@push('scripts')
<script>
function setToday() {
    const today = new Date().toISOString().split('T')[0];
    $('input[name="date"]').val(today);
    $('form').first().submit();
}

function setYesterday() {
    const yesterday = new Date();
    yesterday.setDate(yesterday.getDate() - 1);
    $('input[name="date"]').val(yesterday.toISOString().split('T')[0]);
    $('form').first().submit();
}

function markAll(status) {
    $('.status-select').val(status);
}

// Calculate hours worked when time in/out changes
$(document).ready(function() {
    $('.time-in, .time-out').on('change', function() {
        const teacherId = $(this).data('teacher');
        const timeIn = $(`input[name="attendance[${teacherId}][time_in]"]`).val();
        const timeOut = $(`input[name="attendance[${teacherId}][time_out]"]`).val();

        if (timeIn && timeOut) {
            const start = new Date(`2000-01-01 ${timeIn}`);
            const end = new Date(`2000-01-01 ${timeOut}`);

            if (end > start) {
                const diffMs = end - start;
                const diffHours = (diffMs / (1000 * 60 * 60)).toFixed(2);
                $(`#hours_${teacherId}`).val(diffHours);
            } else {
                $(`#hours_${teacherId}`).val('0.00');
            }
        }
    });
});
</script>
@endpush

@push('styles')
<style>
.user-avatar {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 14px;
}

.hours-display {
    background-color: #e9ecef;
    font-weight: bold;
    text-align: center;
}
</style>
@endpush
