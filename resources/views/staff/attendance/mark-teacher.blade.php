@extends('layouts.app')

@section('title', 'Mark Teacher Attendance')
@section('page-title', 'Mark Teacher Attendance')

@section('content')
<div class="page-header">
    <h1>
        <i class="fas fa-chalkboard-teacher me-2"></i> Mark Teacher Attendance
    </h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('staff.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('staff.attendance.index') }}">Attendance</a></li>
            <li class="breadcrumb-item active">Mark Teacher</li>
        </ol>
    </nav>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<!-- Date Selection Card -->
<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-calendar me-2"></i> Select Date
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('staff.attendance.teacher.mark') }}">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="date" class="form-label">Date <span class="text-danger">*</span></label>
                    <input type="date" name="date" id="date" class="form-control" 
                           value="{{ $selectedDate }}" required>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-2"></i> Load Teachers
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Attendance Form -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>
            <i class="fas fa-users me-2"></i> 
            Teacher Attendance for {{ Carbon\Carbon::parse($selectedDate)->format('l, d F Y') }}
        </span>
        <span class="badge bg-primary">{{ $teachers->count() }} Teachers</span>
    </div>
    <div class="card-body">
        @if($teachers->isEmpty())
            <div class="text-center py-5">
                <i class="fas fa-user-slash text-muted fa-4x mb-3"></i>
                <h5>No Teachers Found</h5>
                <p class="text-muted">There are no active teachers in the system.</p>
            </div>
        @else
        <form method="POST" action="{{ route('staff.attendance.teacher.store') }}" id="attendanceForm">
            @csrf
            <input type="hidden" name="date" value="{{ $selectedDate }}">

            <!-- Quick Actions -->
            <div class="mb-4">
                <div class="d-flex gap-2 flex-wrap">
                    <button type="button" class="btn btn-sm btn-success" onclick="markAllTeachers('present')">
                        <i class="fas fa-check-double me-1"></i> Mark All Present
                    </button>
                    <button type="button" class="btn btn-sm btn-danger" onclick="markAllTeachers('absent')">
                        <i class="fas fa-times me-1"></i> Mark All Absent
                    </button>
                    <button type="button" class="btn btn-sm btn-secondary" onclick="clearAllTeachers()">
                        <i class="fas fa-eraser me-1"></i> Clear All
                    </button>
                    <button type="button" class="btn btn-sm btn-info" onclick="setCurrentTimeAll()">
                        <i class="fas fa-clock me-1"></i> Set Current Time for All
                    </button>
                </div>
            </div>

            <!-- Teachers Table -->
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="attendanceTable">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">#</th>
                            <th width="20%">Teacher Name</th>
                            <th width="20%">Status</th>
                            <th width="12%">Time In</th>
                            <th width="12%">Time Out</th>
                            <th width="8%">Hours</th>
                            <th width="23%">Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($teachers as $index => $teacher)
                        @php
                            $existingRecord = $existingAttendance->get($teacher->id);
                        @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm bg-light rounded-circle me-2 d-flex align-items-center justify-content-center">
                                        @if($teacher->user && $teacher->user->photo)
                                            <img src="{{ asset('storage/' . $teacher->user->photo) }}" 
                                                 class="rounded-circle" width="32" height="32" alt="Photo">
                                        @else
                                            <i class="fas fa-chalkboard-teacher text-muted"></i>
                                        @endif
                                    </div>
                                    <div>
                                        <span class="fw-medium">{{ $teacher->user->name ?? 'N/A' }}</span>
                                        @if($teacher->employee_id)
                                            <br><small class="text-muted">{{ $teacher->employee_id }}</small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <select name="attendance[{{ $teacher->id }}][status]" 
                                        class="form-select form-select-sm status-select"
                                        data-teacher-id="{{ $teacher->id }}">
                                    <option value="">-- Select --</option>
                                    <option value="present" {{ $existingRecord && $existingRecord->status == 'present' ? 'selected' : '' }}>
                                        ✓ Present
                                    </option>
                                    <option value="absent" {{ $existingRecord && $existingRecord->status == 'absent' ? 'selected' : '' }}>
                                        ✗ Absent
                                    </option>
                                    <option value="half_day" {{ $existingRecord && $existingRecord->status == 'half_day' ? 'selected' : '' }}>
                                        ½ Half Day
                                    </option>
                                    <option value="on_leave" {{ $existingRecord && $existingRecord->status == 'on_leave' ? 'selected' : '' }}>
                                        📅 On Leave
                                    </option>
                                </select>
                            </td>
                            <td>
                                <input type="time" 
                                       name="attendance[{{ $teacher->id }}][time_in]" 
                                       class="form-control form-control-sm time-in"
                                       data-teacher-id="{{ $teacher->id }}"
                                       value="{{ $existingRecord?->time_in ? Carbon\Carbon::parse($existingRecord->time_in)->format('H:i') : '' }}">
                            </td>
                            <td>
                                <input type="time" 
                                       name="attendance[{{ $teacher->id }}][time_out]" 
                                       class="form-control form-control-sm time-out"
                                       data-teacher-id="{{ $teacher->id }}"
                                       value="{{ $existingRecord?->time_out ? Carbon\Carbon::parse($existingRecord->time_out)->format('H:i') : '' }}">
                            </td>
                            <td>
                                <span class="hours-display text-muted" data-teacher-id="{{ $teacher->id }}">
                                    {{ $existingRecord?->hours_worked ? number_format($existingRecord->hours_worked, 1) . 'h' : '-' }}
                                </span>
                            </td>
                            <td>
                                <input type="text" 
                                       name="attendance[{{ $teacher->id }}][remarks]" 
                                       class="form-control form-control-sm" 
                                       placeholder="Remarks..."
                                       value="{{ $existingRecord?->remarks ?? '' }}"
                                       maxlength="255">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Submit Buttons -->
            <div class="mt-4 d-flex justify-content-end gap-2">
                <a href="{{ route('staff.attendance.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i> Back
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i> Save Attendance
                </button>
            </div>
        </form>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
// Auto-submit when date changes
document.getElementById('date').addEventListener('change', function() {
    this.closest('form').submit();
});

// Mark all teachers with a specific status
function markAllTeachers(status) {
    document.querySelectorAll('.status-select').forEach(select => {
        select.value = status;
    });
}

// Clear all selections
function clearAllTeachers() {
    document.querySelectorAll('.status-select').forEach(select => {
        select.value = '';
    });
    document.querySelectorAll('.time-in, .time-out').forEach(input => {
        input.value = '';
    });
    document.querySelectorAll('.hours-display').forEach(span => {
        span.textContent = '-';
    });
}

// Set current time for all teachers
function setCurrentTimeAll() {
    const now = new Date();
    const timeString = now.toTimeString().slice(0, 5);
    
    document.querySelectorAll('.time-in').forEach(input => {
        if (!input.value) {
            input.value = timeString;
        }
    });
    
    calculateAllHours();
}

// Calculate hours worked for a specific teacher
function calculateHours(teacherId) {
    const timeIn = document.querySelector(`.time-in[data-teacher-id="${teacherId}"]`).value;
    const timeOut = document.querySelector(`.time-out[data-teacher-id="${teacherId}"]`).value;
    const hoursDisplay = document.querySelector(`.hours-display[data-teacher-id="${teacherId}"]`);
    
    if (timeIn && timeOut) {
        const [inHour, inMin] = timeIn.split(':').map(Number);
        const [outHour, outMin] = timeOut.split(':').map(Number);
        
        const inMinutes = inHour * 60 + inMin;
        const outMinutes = outHour * 60 + outMin;
        
        if (outMinutes > inMinutes) {
            const hours = ((outMinutes - inMinutes) / 60).toFixed(1);
            hoursDisplay.textContent = hours + 'h';
        } else {
            hoursDisplay.textContent = '-';
        }
    } else {
        hoursDisplay.textContent = '-';
    }
}

// Calculate hours for all teachers
function calculateAllHours() {
    document.querySelectorAll('.time-in').forEach(input => {
        const teacherId = input.dataset.teacherId;
        calculateHours(teacherId);
    });
}

// Add event listeners for time inputs
document.querySelectorAll('.time-in, .time-out').forEach(input => {
    input.addEventListener('change', function() {
        const teacherId = this.dataset.teacherId;
        calculateHours(teacherId);
    });
});

// Form validation
document.getElementById('attendanceForm')?.addEventListener('submit', function(e) {
    const hasSelection = Array.from(document.querySelectorAll('.status-select'))
        .some(select => select.value !== '');
    
    if (!hasSelection) {
        e.preventDefault();
        alert('Please mark attendance for at least one teacher.');
        return false;
    }
});
</script>
@endpush
