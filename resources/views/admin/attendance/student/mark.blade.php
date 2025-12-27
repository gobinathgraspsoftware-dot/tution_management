@extends('layouts.app')

@section('title', 'Mark Student Attendance')
@section('page-title', 'Mark Student Attendance')

@section('content')
<div class="page-header">
    <h1><i class="fas fa-user-check me-2"></i> Mark Student Attendance</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.attendance.index') }}">Attendance</a></li>
            <li class="breadcrumb-item active">Mark Student Attendance</li>
        </ol>
    </nav>
</div>

<!-- Filter Form -->
<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-filter me-2"></i> Select Class & Date
    </div>
    <div class="card-body">
        <form id="filterForm" method="GET">
            <div class="row">
                <div class="col-md-4">
                    <label class="form-label">Date <span class="text-danger">*</span></label>
                    <input type="date" name="date" id="dateInput" class="form-control"
                           value="{{ $selectedDate }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Class <span class="text-danger">*</span></label>
                    <select name="class_id" id="classSelect" class="form-select" required>
                        <option value="">Select Class</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ $selectedClassId == $class->id ? 'selected' : '' }}>
                                {{ $class->name }} - {{ $class->subject->name ?? 'N/A' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-1"></i> Load Students
                    </button>
                </div>
            </div>

            @if($classInfo && $selectedClassId)
            <div class="mt-3">
                <div class="alert alert-info mb-0">
                    <div class="row">
                        <div class="col-md-4">
                            <strong><i class="fas fa-chalkboard me-1"></i> Class:</strong> {{ $classInfo->name }}
                        </div>
                        <div class="col-md-4">
                            <strong><i class="fas fa-book me-1"></i> Subject:</strong> {{ $classInfo->subject->name ?? 'N/A' }}
                        </div>
                        <div class="col-md-4">
                            <strong><i class="fas fa-user-tie me-1"></i> Teacher:</strong>
                            {{ $classInfo->teacher->user->name ?? 'Not Assigned' }}
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </form>
    </div>
</div>

@if($selectedClassId && $selectedDate && $students->isNotEmpty())
<!-- Attendance Form -->
<form action="{{ route('admin.attendance.student.store') }}" method="POST">
    @csrf
    <input type="hidden" name="class_id" value="{{ $selectedClassId }}">
    <input type="hidden" name="date" value="{{ $selectedDate }}">

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-list me-2"></i> Student List ({{ $students->count() }} Students)</span>
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
                            <th width="20%">Student</th>
                            <th width="15%">Student ID</th>
                            <th width="15%">Status <span class="text-danger">*</span></th>
                            <th width="15%">Check-in Time</th>
                            <th width="30%">Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $index => $student)
                        @php
                            $existing = $attendanceRecords->get($student->id);
                        @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="user-avatar me-2">{{ substr($student->user->name, 0, 1) }}</div>
                                    <div>
                                        <strong>{{ $student->user->name }}</strong><br>
                                        <small class="text-muted">{{ $student->user->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $student->student_id }}</td>
                            <td>
                                <select name="attendance[{{ $student->id }}][status]" class="form-select form-select-sm status-select" required>
                                    <option value="">Select Status</option>
                                    <option value="present" {{ $existing && $existing->status == 'present' ? 'selected' : '' }}>
                                        Present
                                    </option>
                                    <option value="absent" {{ $existing && $existing->status == 'absent' ? 'selected' : '' }}>
                                        Absent
                                    </option>
                                    <option value="late" {{ $existing && $existing->status == 'late' ? 'selected' : '' }}>
                                        Late
                                    </option>
                                    <option value="excused" {{ $existing && $existing->status == 'excused' ? 'selected' : '' }}>
                                        Excused
                                    </option>
                                </select>
                            </td>
                            <td>
                                <input type="time" name="attendance[{{ $student->id }}][check_in_time]"
                                       class="form-control form-control-sm"
                                       value="{{ $existing ? $existing->check_in_time?->format('H:i') : '' }}">
                            </td>
                            <td>
                                <input type="text" name="attendance[{{ $student->id }}][remarks]"
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

    <!-- Notification Options -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="send_notifications"
                       id="sendNotifications" value="1" checked>
                <label class="form-check-label" for="sendNotifications">
                    <i class="fab fa-whatsapp text-success me-1"></i>
                    Send WhatsApp notifications to parents
                </label>
                <small class="d-block text-muted ms-4">
                    Parents will receive instant attendance notifications via WhatsApp
                </small>
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
@elseif($selectedClassId && $selectedDate && $students->isEmpty())
<div class="alert alert-warning">
    <i class="fas fa-exclamation-triangle me-2"></i> No active students enrolled in this class.
</div>
@elseif($selectedClassId || $selectedDate)
<div class="alert alert-info">
    <i class="fas fa-info-circle me-2"></i> Please select both Date and Class to load students.
</div>
@else
<div class="card">
    <div class="card-body text-center py-5">
        <i class="fas fa-calendar-check fa-3x text-muted mb-3"></i>
        <h5>Mark Student Attendance</h5>
        <p class="text-muted">Select a date and class above to begin marking attendance.</p>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-submit form when date or class changes
    $('#dateInput, #classSelect').on('change', function() {
        const date = $('#dateInput').val();
        const classId = $('#classSelect').val();

        if (date && classId) {
            $('#filterForm').submit();
        }
    });
});

function markAll(status) {
    $('.status-select').val(status);
}
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
