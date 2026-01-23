@extends('layouts.app')

@section('title', 'Mark Student Attendance')
@section('page-title', 'Mark Student Attendance')

@section('content')
<div class="page-header">
    <h1>
        <i class="fas fa-user-check me-2"></i> Mark Student Attendance
    </h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('staff.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('staff.attendance.index') }}">Attendance</a></li>
            <li class="breadcrumb-item active">Mark Student</li>
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

<!-- Filter Card -->
<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-filter me-2"></i> Select Class & Date
    </div>
    <div class="card-body">
        <form id="filterForm" method="GET" action="{{ route('staff.attendance.student.mark') }}">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="class_id" class="form-label">Class <span class="text-danger">*</span></label>
                    <select name="class_id" id="class_id" class="form-select" required>
                        <option value="">-- Select Class --</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" 
                                {{ $selectedClassId == $class->id ? 'selected' : '' }}>
                                {{ $class->name }} - {{ $class->subject->name ?? 'N/A' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="date" class="form-label">Date <span class="text-danger">*</span></label>
                    <input type="date" name="date" id="date" class="form-control" 
                           value="{{ $selectedDate }}" required>
                </div>
                <div class="col-md-3">
                    <label for="session_id" class="form-label">Session</label>
                    <select name="session_id" id="session_id" class="form-select">
                        <option value="">-- Select Session --</option>
                        @foreach($sessions as $session)
                            <option value="{{ $session->id }}" 
                                {{ request('session_id') == $session->id ? 'selected' : '' }}>
                                {{ Carbon\Carbon::parse($session->start_time)->format('h:i A') }} - 
                                {{ Carbon\Carbon::parse($session->end_time)->format('h:i A') }}
                                @if($session->topic) ({{ $session->topic }}) @endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-2"></i> Load Students
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Attendance Form -->
@if($selectedClassId && $students->isNotEmpty())
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>
            <i class="fas fa-users me-2"></i> 
            Student Attendance for {{ $classes->firstWhere('id', $selectedClassId)?->name ?? 'Selected Class' }}
        </span>
        <span class="badge bg-primary">{{ $students->count() }} Students</span>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('staff.attendance.student.store') }}" id="attendanceForm">
            @csrf
            <input type="hidden" name="class_id" value="{{ $selectedClassId }}">
            <input type="hidden" name="date" value="{{ $selectedDate }}">
            <input type="hidden" name="session_id" value="{{ request('session_id') }}">

            <!-- Quick Actions -->
            <div class="mb-4">
                <div class="d-flex gap-2 flex-wrap">
                    <button type="button" class="btn btn-sm btn-success" onclick="markAll('present')">
                        <i class="fas fa-check-double me-1"></i> Mark All Present
                    </button>
                    <button type="button" class="btn btn-sm btn-danger" onclick="markAll('absent')">
                        <i class="fas fa-times me-1"></i> Mark All Absent
                    </button>
                    <button type="button" class="btn btn-sm btn-secondary" onclick="clearAll()">
                        <i class="fas fa-eraser me-1"></i> Clear All
                    </button>
                </div>
            </div>

            <!-- Students Table -->
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="attendanceTable">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">#</th>
                            <th width="15%">Student ID</th>
                            <th width="25%">Name</th>
                            <th width="20%">Status</th>
                            <th width="15%">Check-in Time</th>
                            <th width="20%">Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $index => $student)
                        @php
                            $existingRecord = $existingAttendance->get($student->id);
                        @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <span class="fw-medium">{{ $student->student_id }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm bg-light rounded-circle me-2 d-flex align-items-center justify-content-center">
                                        @if($student->user && $student->user->photo)
                                            <img src="{{ asset('storage/' . $student->user->photo) }}" 
                                                 class="rounded-circle" width="32" height="32" alt="Photo">
                                        @else
                                            <i class="fas fa-user text-muted"></i>
                                        @endif
                                    </div>
                                    <span>{{ $student->user->name ?? 'N/A' }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm w-100" role="group">
                                    <input type="radio" class="btn-check status-radio" 
                                           name="attendance[{{ $student->id }}][status]" 
                                           id="present_{{ $student->id }}" 
                                           value="present"
                                           {{ $existingRecord && $existingRecord->status == 'present' ? 'checked' : '' }}>
                                    <label class="btn btn-outline-success" for="present_{{ $student->id }}" title="Present">
                                        <i class="fas fa-check"></i>
                                    </label>

                                    <input type="radio" class="btn-check status-radio" 
                                           name="attendance[{{ $student->id }}][status]" 
                                           id="absent_{{ $student->id }}" 
                                           value="absent"
                                           {{ $existingRecord && $existingRecord->status == 'absent' ? 'checked' : '' }}>
                                    <label class="btn btn-outline-danger" for="absent_{{ $student->id }}" title="Absent">
                                        <i class="fas fa-times"></i>
                                    </label>

                                    <input type="radio" class="btn-check status-radio" 
                                           name="attendance[{{ $student->id }}][status]" 
                                           id="late_{{ $student->id }}" 
                                           value="late"
                                           {{ $existingRecord && $existingRecord->status == 'late' ? 'checked' : '' }}>
                                    <label class="btn btn-outline-warning" for="late_{{ $student->id }}" title="Late">
                                        <i class="fas fa-clock"></i>
                                    </label>

                                    <input type="radio" class="btn-check status-radio" 
                                           name="attendance[{{ $student->id }}][status]" 
                                           id="excused_{{ $student->id }}" 
                                           value="excused"
                                           {{ $existingRecord && $existingRecord->status == 'excused' ? 'checked' : '' }}>
                                    <label class="btn btn-outline-info" for="excused_{{ $student->id }}" title="Excused">
                                        <i class="fas fa-user-shield"></i>
                                    </label>
                                </div>
                            </td>
                            <td>
                                <input type="time" 
                                       name="attendance[{{ $student->id }}][check_in_time]" 
                                       class="form-control form-control-sm"
                                       value="{{ $existingRecord?->check_in_time ? Carbon\Carbon::parse($existingRecord->check_in_time)->format('H:i') : '' }}">
                            </td>
                            <td>
                                <input type="text" 
                                       name="attendance[{{ $student->id }}][remarks]" 
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

            <!-- Notification Option -->
            <div class="mt-4 p-3 bg-light rounded">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="send_notifications" name="send_notifications" value="1">
                    <label class="form-check-label" for="send_notifications">
                        <i class="fab fa-whatsapp text-success me-1"></i>
                        Send attendance notifications to parents via WhatsApp
                    </label>
                </div>
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
    </div>
</div>
@elseif($selectedClassId && $students->isEmpty())
<div class="card">
    <div class="card-body text-center py-5">
        <i class="fas fa-user-slash text-muted fa-4x mb-3"></i>
        <h5>No Students Found</h5>
        <p class="text-muted">There are no active enrollments for the selected class.</p>
    </div>
</div>
@else
<div class="card">
    <div class="card-body text-center py-5">
        <i class="fas fa-hand-pointer text-muted fa-4x mb-3"></i>
        <h5>Select Class & Date</h5>
        <p class="text-muted">Please select a class and date above to load students for attendance marking.</p>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
// Auto-submit filter form when class or date changes
document.getElementById('class_id').addEventListener('change', function() {
    document.getElementById('filterForm').submit();
});

document.getElementById('date').addEventListener('change', function() {
    if (document.getElementById('class_id').value) {
        document.getElementById('filterForm').submit();
    }
});

document.getElementById('session_id').addEventListener('change', function() {
    if (document.getElementById('class_id').value) {
        document.getElementById('filterForm').submit();
    }
});

// Mark all students with a specific status
function markAll(status) {
    document.querySelectorAll(`input[value="${status}"].status-radio`).forEach(radio => {
        radio.checked = true;
    });
}

// Clear all selections
function clearAll() {
    document.querySelectorAll('.status-radio').forEach(radio => {
        radio.checked = false;
    });
}

// Form validation
document.getElementById('attendanceForm')?.addEventListener('submit', function(e) {
    const checkedRadios = document.querySelectorAll('.status-radio:checked');
    if (checkedRadios.length === 0) {
        e.preventDefault();
        alert('Please mark attendance for at least one student.');
        return false;
    }
    
    // Check if session is selected
    const sessionId = document.querySelector('input[name="session_id"]').value;
    if (!sessionId) {
        e.preventDefault();
        alert('Please select a session before marking attendance.');
        return false;
    }
});
</script>
@endpush
