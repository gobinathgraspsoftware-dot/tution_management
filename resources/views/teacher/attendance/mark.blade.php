@extends('layouts.app')

@section('title', 'Mark Attendance')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Mark Attendance</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('teacher.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('teacher.attendance.index') }}">Attendance</a></li>
                    <li class="breadcrumb-item active">Mark Attendance</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('teacher.attendance.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    <!-- Session Info Card -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <strong><i class="fas fa-chalkboard me-2"></i>Class:</strong>
                    <p class="mb-0">{{ $session->class->name }}</p>
                </div>
                <div class="col-md-3">
                    <strong><i class="fas fa-book me-2"></i>Subject:</strong>
                    <p class="mb-0">{{ $session->class->subject->name ?? 'N/A' }}</p>
                </div>
                <div class="col-md-3">
                    <strong><i class="fas fa-calendar me-2"></i>Date:</strong>
                    <p class="mb-0">{{ $session->session_date->format('l, M d, Y') }}</p>
                </div>
                <div class="col-md-3">
                    <strong><i class="fas fa-clock me-2"></i>Time:</strong>
                    <p class="mb-0">{{ \Carbon\Carbon::parse($session->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($session->end_time)->format('h:i A') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance Form -->
    <div class="card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-users me-2"></i>Students ({{ $enrolledStudents->count() }})</h5>
            <div>
                <button type="button" class="btn btn-sm btn-success me-2" onclick="markAll('present')">
                    <i class="fas fa-check-double me-1"></i> All Present
                </button>
                <button type="button" class="btn btn-sm btn-danger" onclick="markAll('absent')">
                    <i class="fas fa-times me-1"></i> All Absent
                </button>
            </div>
        </div>
        <div class="card-body">
            @if($enrolledStudents->isEmpty())
                <div class="text-center py-4">
                    <i class="fas fa-user-slash fa-3x text-muted mb-3"></i>
                    <p class="text-muted mb-0">No students enrolled in this class</p>
                </div>
            @else
                <form action="{{ route('teacher.attendance.store', $session) }}" method="POST" id="attendanceForm">
                    @csrf
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="30%">Student Name</th>
                                    <th width="35%">Status</th>
                                    <th width="30%">Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($enrolledStudents as $index => $student)
                                    @php
                                        $existing = $existingAttendance->get($student->id);
                                    @endphp
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm me-2">
                                                    <span class="avatar-initial rounded-circle bg-primary">
                                                        {{ strtoupper(substr($student->user->name ?? 'S', 0, 1)) }}
                                                    </span>
                                                </div>
                                                <div>
                                                    <strong>{{ $student->user->name ?? 'N/A' }}</strong>
                                                    <br><small class="text-muted">{{ $student->student_id ?? '' }}</small>
                                                </div>
                                            </div>
                                            <input type="hidden" name="attendance[{{ $index }}][student_id]" value="{{ $student->id }}">
                                        </td>
                                        <td>
                                            <div class="btn-group w-100" role="group">
                                                <input type="radio" class="btn-check status-radio" 
                                                       name="attendance[{{ $index }}][status]" 
                                                       id="present_{{ $student->id }}" 
                                                       value="present" 
                                                       {{ ($existing && $existing->status == 'present') ? 'checked' : '' }} required>
                                                <label class="btn btn-outline-success" for="present_{{ $student->id }}">
                                                    <i class="fas fa-check"></i> Present
                                                </label>

                                                <input type="radio" class="btn-check status-radio" 
                                                       name="attendance[{{ $index }}][status]" 
                                                       id="absent_{{ $student->id }}" 
                                                       value="absent"
                                                       {{ ($existing && $existing->status == 'absent') ? 'checked' : '' }}>
                                                <label class="btn btn-outline-danger" for="absent_{{ $student->id }}">
                                                    <i class="fas fa-times"></i> Absent
                                                </label>

                                                <input type="radio" class="btn-check status-radio" 
                                                       name="attendance[{{ $index }}][status]" 
                                                       id="late_{{ $student->id }}" 
                                                       value="late"
                                                       {{ ($existing && $existing->status == 'late') ? 'checked' : '' }}>
                                                <label class="btn btn-outline-warning" for="late_{{ $student->id }}">
                                                    <i class="fas fa-clock"></i> Late
                                                </label>

                                                <input type="radio" class="btn-check status-radio" 
                                                       name="attendance[{{ $index }}][status]" 
                                                       id="excused_{{ $student->id }}" 
                                                       value="excused"
                                                       {{ ($existing && $existing->status == 'excused') ? 'checked' : '' }}>
                                                <label class="btn btn-outline-info" for="excused_{{ $student->id }}">
                                                    <i class="fas fa-file-alt"></i> Excused
                                                </label>
                                            </div>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm" 
                                                   name="attendance[{{ $index }}][remarks]" 
                                                   placeholder="Optional remarks..."
                                                   value="{{ $existing->remarks ?? '' }}">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-end mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Save Attendance
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>

<style>
.avatar-initial {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    font-size: 14px;
    font-weight: 600;
    color: #fff;
}
</style>

@push('scripts')
<script>
function markAll(status) {
    document.querySelectorAll('input[value="' + status + '"].status-radio').forEach(function(radio) {
        radio.checked = true;
    });
}
</script>
@endpush
@endsection
