@extends('layouts.app')

@section('title', 'Edit Teacher Attendance')
@section('page-title', 'Edit Teacher Attendance')

@section('content')
<div class="page-header">
    <h1>
        <i class="fas fa-edit me-2"></i> Edit Teacher Attendance
    </h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('staff.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('staff.attendance.index') }}">Attendance</a></li>
            <li class="breadcrumb-item"><a href="{{ route('staff.attendance.teacher.calendar') }}">Teacher Calendar</a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol>
    </nav>
</div>

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-chalkboard-teacher me-2"></i> Attendance Record Details
            </div>
            <div class="card-body">
                <!-- Teacher Info -->
                <div class="mb-4 p-3 bg-light rounded">
                    <div class="row">
                        <div class="col-md-6">
                            <strong><i class="fas fa-user me-2"></i> Teacher:</strong>
                            {{ $attendance->teacher->user->name ?? 'N/A' }}
                            @if($attendance->teacher->employee_id)
                                <br><small class="text-muted">ID: {{ $attendance->teacher->employee_id }}</small>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <strong><i class="fas fa-calendar me-2"></i> Date:</strong>
                            {{ Carbon\Carbon::parse($attendance->date)->format('l, d F Y') }}
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('staff.attendance.update-teacher', $attendance->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label class="form-label"><strong>Attendance Status</strong> <span class="text-danger">*</span></label>
                        <div class="d-flex gap-3 flex-wrap">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="status" id="status_present" 
                                       value="present" {{ $attendance->status == 'present' ? 'checked' : '' }} required>
                                <label class="form-check-label text-success" for="status_present">
                                    <i class="fas fa-check me-1"></i> Present
                                </label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="status" id="status_absent" 
                                       value="absent" {{ $attendance->status == 'absent' ? 'checked' : '' }}>
                                <label class="form-check-label text-danger" for="status_absent">
                                    <i class="fas fa-times me-1"></i> Absent
                                </label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="status" id="status_half_day" 
                                       value="half_day" {{ $attendance->status == 'half_day' ? 'checked' : '' }}>
                                <label class="form-check-label text-warning" for="status_half_day">
                                    <i class="fas fa-clock me-1"></i> Half Day
                                </label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="status" id="status_on_leave" 
                                       value="on_leave" {{ $attendance->status == 'on_leave' ? 'checked' : '' }}>
                                <label class="form-check-label text-info" for="status_on_leave">
                                    <i class="fas fa-calendar-alt me-1"></i> On Leave
                                </label>
                            </div>
                        </div>
                        @error('status')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label for="time_in" class="form-label"><strong>Time In</strong></label>
                            <input type="time" name="time_in" id="time_in" class="form-control" 
                                   value="{{ $attendance->time_in ? Carbon\Carbon::parse($attendance->time_in)->format('H:i') : '' }}">
                        </div>
                        <div class="col-md-4">
                            <label for="time_out" class="form-label"><strong>Time Out</strong></label>
                            <input type="time" name="time_out" id="time_out" class="form-control" 
                                   value="{{ $attendance->time_out ? Carbon\Carbon::parse($attendance->time_out)->format('H:i') : '' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label"><strong>Hours Worked</strong></label>
                            <div class="form-control-plaintext">
                                <span id="hours_display" class="fw-bold">
                                    {{ $attendance->hours_worked ? number_format($attendance->hours_worked, 1) . ' hours' : '-' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="remarks" class="form-label"><strong>Remarks</strong></label>
                        <textarea name="remarks" id="remarks" class="form-control" rows="3" 
                                  maxlength="500" placeholder="Enter any notes or remarks...">{{ old('remarks', $attendance->remarks) }}</textarea>
                        <small class="text-muted">Maximum 500 characters</small>
                        @error('remarks')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('staff.attendance.teacher.calendar', ['teacher_id' => $attendance->teacher_id]) }}" 
                           class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i> Back
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> Update Attendance
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-info-circle me-2"></i> Record Information
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <th>Record ID:</th>
                        <td>#{{ $attendance->id }}</td>
                    </tr>
                    <tr>
                        <th>Created:</th>
                        <td>{{ $attendance->created_at?->format('d M Y, h:i A') ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Last Updated:</th>
                        <td>{{ $attendance->updated_at?->format('d M Y, h:i A') ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Marked By:</th>
                        <td>{{ $attendance->markedBy->name ?? 'System' }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Calculate hours worked
function calculateHours() {
    const timeIn = document.getElementById('time_in').value;
    const timeOut = document.getElementById('time_out').value;
    const hoursDisplay = document.getElementById('hours_display');
    
    if (timeIn && timeOut) {
        const [inHour, inMin] = timeIn.split(':').map(Number);
        const [outHour, outMin] = timeOut.split(':').map(Number);
        
        const inMinutes = inHour * 60 + inMin;
        const outMinutes = outHour * 60 + outMin;
        
        if (outMinutes > inMinutes) {
            const hours = ((outMinutes - inMinutes) / 60).toFixed(1);
            hoursDisplay.textContent = hours + ' hours';
        } else {
            hoursDisplay.textContent = '-';
        }
    } else {
        hoursDisplay.textContent = '-';
    }
}

document.getElementById('time_in').addEventListener('change', calculateHours);
document.getElementById('time_out').addEventListener('change', calculateHours);
</script>
@endpush
