@extends('layouts.app')

@section('title', 'Edit Student Attendance')
@section('page-title', 'Edit Student Attendance')

@section('content')
<div class="page-header">
    <h1>
        <i class="fas fa-edit me-2"></i> Edit Student Attendance
    </h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('staff.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('staff.attendance.index') }}">Attendance</a></li>
            <li class="breadcrumb-item"><a href="{{ route('staff.attendance.reports') }}">Reports</a></li>
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
                <i class="fas fa-user-edit me-2"></i> Attendance Record Details
            </div>
            <div class="card-body">
                <!-- Student Info -->
                <div class="mb-4 p-3 bg-light rounded">
                    <div class="row">
                        <div class="col-md-6">
                            <strong><i class="fas fa-user me-2"></i> Student:</strong>
                            {{ $attendance->student->user->name ?? 'N/A' }}
                            <br><small class="text-muted">ID: {{ $attendance->student->student_id ?? 'N/A' }}</small>
                        </div>
                        <div class="col-md-6">
                            <strong><i class="fas fa-chalkboard me-2"></i> Class:</strong>
                            {{ $attendance->classSession->class->name ?? 'N/A' }}
                            <br><small class="text-muted">{{ $attendance->classSession->class->subject->name ?? '' }}</small>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <strong><i class="fas fa-calendar me-2"></i> Date:</strong>
                            {{ $attendance->classSession?->session_date ? Carbon\Carbon::parse($attendance->classSession->session_date)->format('l, d F Y') : 'N/A' }}
                        </div>
                        <div class="col-md-6">
                            <strong><i class="fas fa-clock me-2"></i> Session Time:</strong>
                            {{ $attendance->classSession?->start_time ? Carbon\Carbon::parse($attendance->classSession->start_time)->format('h:i A') : '' }} - 
                            {{ $attendance->classSession?->end_time ? Carbon\Carbon::parse($attendance->classSession->end_time)->format('h:i A') : '' }}
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('staff.attendance.update-student', $attendance->id) }}">
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
                                <input class="form-check-input" type="radio" name="status" id="status_late" 
                                       value="late" {{ $attendance->status == 'late' ? 'checked' : '' }}>
                                <label class="form-check-label text-warning" for="status_late">
                                    <i class="fas fa-clock me-1"></i> Late
                                </label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="status" id="status_excused" 
                                       value="excused" {{ $attendance->status == 'excused' ? 'checked' : '' }}>
                                <label class="form-check-label text-info" for="status_excused">
                                    <i class="fas fa-user-shield me-1"></i> Excused
                                </label>
                            </div>
                        </div>
                        @error('status')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="check_in_time" class="form-label"><strong>Check-in Time</strong></label>
                        <input type="time" name="check_in_time" id="check_in_time" class="form-control" 
                               value="{{ $attendance->check_in_time ? Carbon\Carbon::parse($attendance->check_in_time)->format('H:i') : '' }}"
                               style="max-width: 200px;">
                        <small class="text-muted">Optional - Record actual arrival time</small>
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
                        <a href="{{ route('staff.attendance.reports') }}" class="btn btn-secondary">
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
                    <tr>
                        <th>Parent Notified:</th>
                        <td>
                            @if($attendance->parent_notified)
                                <span class="badge bg-success">
                                    <i class="fas fa-check me-1"></i> Yes
                                </span>
                                @if($attendance->notified_at)
                                    <br><small class="text-muted">{{ Carbon\Carbon::parse($attendance->notified_at)->format('d M Y, h:i A') }}</small>
                                @endif
                            @else
                                <span class="badge bg-secondary">
                                    <i class="fas fa-times me-1"></i> No
                                </span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
