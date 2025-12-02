@extends('layouts.app')

@section('title', 'Edit Class')
@section('page-title', 'Edit Class')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Edit Class: {{ $class->name }}</h5>
                    <a href="{{ route('admin.classes.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left me-2"></i>Back to List
                    </a>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.classes.update', $class) }}" method="POST" id="classForm">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <!-- Class Name -->
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Class Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       id="name" name="name" value="{{ old('name', $class->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Class Code -->
                            <div class="col-md-6 mb-3">
                                <label for="code" class="form-label">Class Code</label>
                                <input type="text" class="form-control @error('code') is-invalid @enderror"
                                       id="code" name="code" value="{{ old('code', $class->code) }}">
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Subject -->
                            <div class="col-md-6 mb-3">
                                <label for="subject_id" class="form-label">Subject <span class="text-danger">*</span></label>
                                <select class="form-select @error('subject_id') is-invalid @enderror"
                                        id="subject_id" name="subject_id" required>
                                    <option value="">Select Subject</option>
                                    @foreach($subjects as $subject)
                                        <option value="{{ $subject->id }}"
                                            {{ old('subject_id', $class->subject_id) == $subject->id ? 'selected' : '' }}>
                                            {{ $subject->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('subject_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Teacher -->
                            <div class="col-md-6 mb-3">
                                <label for="teacher_id" class="form-label">Teacher</label>
                                <select class="form-select @error('teacher_id') is-invalid @enderror"
                                        id="teacher_id" name="teacher_id">
                                    <option value="">Select Teacher (Optional)</option>
                                    @foreach($teachers as $teacher)
                                        <option value="{{ $teacher->id }}"
                                            {{ old('teacher_id', $class->teacher_id) == $teacher->id ? 'selected' : '' }}>
                                            {{ $teacher->user->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('teacher_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Class Type -->
                            <div class="col-md-6 mb-3">
                                <label for="type" class="form-label">Class Type <span class="text-danger">*</span></label>
                                <select class="form-select @error('type') is-invalid @enderror"
                                        id="type" name="type" required>
                                    <option value="">Select Type</option>
                                    <option value="online" {{ old('type', $class->type) == 'online' ? 'selected' : '' }}>Online</option>
                                    <option value="offline" {{ old('type', $class->type) == 'offline' ? 'selected' : '' }}>Offline</option>
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Grade Level -->
                            <div class="col-md-6 mb-3">
                                <label for="grade_level" class="form-label">Grade Level</label>
                                <input type="text" class="form-control @error('grade_level') is-invalid @enderror"
                                       id="grade_level" name="grade_level" value="{{ old('grade_level', $class->grade_level) }}"
                                       placeholder="e.g., Form 1, Form 2">
                                @error('grade_level')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Capacity -->
                            <div class="col-md-6 mb-3">
                                <label for="capacity" class="form-label">Class Capacity <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('capacity') is-invalid @enderror"
                                       id="capacity" name="capacity" value="{{ old('capacity', $class->capacity) }}"
                                       min="1" max="100" required>
                                <small class="text-muted">Current Enrollment: {{ $class->current_enrollment }}</small>
                                @error('capacity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Status -->
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select @error('status') is-invalid @enderror"
                                        id="status" name="status">
                                    <option value="active" {{ old('status', $class->status) == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('status', $class->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    <option value="full" {{ old('status', $class->status) == 'full' ? 'selected' : '' }}>Full</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Location (for offline) -->
                            <div class="col-md-6 mb-3" id="locationField">
                                <label for="location" class="form-label">Location <span class="text-danger" id="locationRequired">*</span></label>
                                <input type="text" class="form-control @error('location') is-invalid @enderror"
                                       id="location" name="location" value="{{ old('location', $class->location) }}"
                                       placeholder="Room/Building">
                                @error('location')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Meeting Link (for online) -->
                            <div class="col-md-6 mb-3" id="meetingLinkField">
                                <label for="meeting_link" class="form-label">Meeting Link <span class="text-danger" id="linkRequired">*</span></label>
                                <input type="url" class="form-control @error('meeting_link') is-invalid @enderror"
                                       id="meeting_link" name="meeting_link" value="{{ old('meeting_link', $class->meeting_link) }}"
                                       placeholder="https://meet.google.com/xxx-xxxx-xxx">
                                @error('meeting_link')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Description -->
                            <div class="col-md-12 mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror"
                                          id="description" name="description" rows="3">{{ old('description', $class->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.classes.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Update Class
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Show/hide location and meeting link fields based on class type
    function toggleTypeFields() {
        var type = $('#type').val();

        if (type === 'online') {
            $('#meetingLinkField').show();
            $('#locationField').hide();
            $('#meeting_link').prop('required', true);
            $('#location').prop('required', false);
        } else if (type === 'offline') {
            $('#locationField').show();
            $('#meetingLinkField').hide();
            $('#location').prop('required', true);
            $('#meeting_link').prop('required', false);
        } else {
            $('#meetingLinkField').hide();
            $('#locationField').hide();
            $('#meeting_link').prop('required', false);
            $('#location').prop('required', false);
        }
    }

    // Initial check
    toggleTypeFields();

    // On change
    $('#type').change(toggleTypeFields);

    // Form validation
    $('#classForm').submit(function(e) {
        var capacity = parseInt($('#capacity').val());
        var currentEnrollment = {{ $class->current_enrollment }};

        if (capacity < currentEnrollment) {
            e.preventDefault();
            alert('Capacity cannot be less than current enrollment (' + currentEnrollment + ' students).');
            return false;
        }

        if (capacity < 1) {
            e.preventDefault();
            alert('Capacity must be at least 1 student.');
            return false;
        }

        if (capacity > 100) {
            e.preventDefault();
            alert('Capacity cannot exceed 100 students.');
            return false;
        }
    });
});
</script>
@endpush
