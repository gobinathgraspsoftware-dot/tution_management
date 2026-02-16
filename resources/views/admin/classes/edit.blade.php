{{-- resources/views/admin/classes/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'Edit Class')
@section('page-title', 'Edit Class')

@section('content')
<div class="container-fluid">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Edit Class: {{ $class->name }}</h4>
            <p class="text-muted mb-0">Update class details below</p>
        </div>
        <a href="{{ route('admin.classes.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to List
        </a>
    </div>

    {{-- Alert Messages --}}
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('admin.classes.update', $class) }}" method="POST" id="classForm">
        @csrf
        @method('PUT')

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="row">
                    {{-- Class Name --}}
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">Class Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $class->name) }}" placeholder="e.g., SEJARAH" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Class Code (Read-Only) --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Class Code</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock text-secondary"></i></span>
                            <input type="text" class="form-control bg-light" value="{{ $class->code }}" disabled readonly>
                        </div>
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Class code is auto-generated and cannot be changed
                        </small>
                    </div>

                    {{-- Subject (LOCKED - cannot change because class code is derived from subject) --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Subject <span class="text-danger">*</span></label>
                        {{-- Hidden input sends the actual value since disabled select won't submit --}}
                        <input type="hidden" name="subject_id" value="{{ $class->subject_id }}">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock text-secondary"></i></span>
                            <select class="form-select bg-light" disabled>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}" {{ $class->subject_id == $subject->id ? 'selected' : '' }}>
                                        {{ $subject->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Subject cannot be changed because the class code ({{ $class->code }}) is linked to it.
                            To use a different subject, create a new class.
                        </small>
                        @error('subject_id')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Teacher --}}
                    <div class="col-md-6 mb-3">
                        <label for="teacher_id" class="form-label">Teacher</label>
                        <select name="teacher_id" id="teacher_id"
                                class="form-select @error('teacher_id') is-invalid @enderror">
                            <option value="">-- Select Teacher --</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}" {{ old('teacher_id', $class->teacher_id) == $teacher->id ? 'selected' : '' }}>
                                    {{ $teacher->user->name ?? 'N/A' }}
                                </option>
                            @endforeach
                        </select>
                        @error('teacher_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Class Type --}}
                    <div class="col-md-6 mb-3">
                        <label for="type" class="form-label">Class Type <span class="text-danger">*</span></label>
                        <select name="type" id="type"
                                class="form-select @error('type') is-invalid @enderror" required>
                            <option value="">-- Select Type --</option>
                            <option value="online" {{ old('type', $class->type) == 'online' ? 'selected' : '' }}>Online</option>
                            <option value="offline" {{ old('type', $class->type) == 'offline' ? 'selected' : '' }}>Offline</option>
                        </select>
                        @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Grade Level --}}
                    <div class="col-md-6 mb-3">
                        <label for="grade_level" class="form-label">Grade Level</label>
                        <input type="text" name="grade_level" id="grade_level"
                               class="form-control @error('grade_level') is-invalid @enderror"
                               value="{{ old('grade_level', $class->grade_level) }}" placeholder="e.g., FORM 2">
                        @error('grade_level')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Capacity --}}
                    <div class="col-md-6 mb-3">
                        <label for="capacity" class="form-label">Class Capacity <span class="text-danger">*</span></label>
                        <input type="number" name="capacity" id="capacity" min="1" max="100"
                               class="form-control @error('capacity') is-invalid @enderror"
                               value="{{ old('capacity', $class->capacity) }}" required>
                        @if($class->current_enrollment > 0)
                            <small class="text-muted">
                                Current enrollment: {{ $class->current_enrollment }} students (minimum capacity)
                            </small>
                        @endif
                        @error('capacity')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Price --}}
                    <div class="col-md-6 mb-3">
                        <label for="price" class="form-label">Class Price (RM) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">RM</span>
                            <input type="number" name="price" id="price" step="0.01" min="0" max="99999.99"
                                   class="form-control @error('price') is-invalid @enderror"
                                   value="{{ old('price', $class->price) }}" placeholder="0.00" required>
                        </div>
                        <small class="text-muted">Enter 0.00 for free classes</small>
                        @error('price')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Status --}}
                    <div class="col-md-6 mb-3">
                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" id="status"
                                class="form-select @error('status') is-invalid @enderror" required>
                            <option value="active" {{ old('status', $class->status) == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $class->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="full" {{ old('status', $class->status) == 'full' ? 'selected' : '' }}>Full</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Meeting Link (shown for online classes) --}}
                    <div class="col-md-6 mb-3" id="meeting_link_field" style="display: none;">
                        <label for="meeting_link" class="form-label">Meeting Link <span class="text-danger">*</span></label>
                        <input type="url" name="meeting_link" id="meeting_link"
                               class="form-control @error('meeting_link') is-invalid @enderror"
                               value="{{ old('meeting_link', $class->meeting_link) }}" placeholder="https://...">
                        @error('meeting_link')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Location (shown for offline classes) --}}
                    <div class="col-md-6 mb-3" id="location_field" style="display: none;">
                        <label for="location" class="form-label">Location <span class="text-danger">*</span></label>
                        <input type="text" name="location" id="location"
                               class="form-control @error('location') is-invalid @enderror"
                               value="{{ old('location', $class->location) }}" placeholder="e.g., Room 101, Block A">
                        @error('location')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Description --}}
                    <div class="col-12 mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea name="description" id="description" rows="3"
                                  class="form-control @error('description') is-invalid @enderror"
                                  placeholder="Optional class description...">{{ old('description', $class->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Form Actions --}}
            <div class="card-footer text-end">
                <a href="{{ route('admin.classes.index') }}" class="btn btn-secondary me-2">
                    <i class="fas fa-times me-1"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Update Class
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Toggle meeting link / location fields based on class type
    function toggleTypeFields() {
        var type = $('#type').val();
        if (type === 'online') {
            $('#meeting_link_field').show();
            $('#location_field').hide();
        } else if (type === 'offline') {
            $('#meeting_link_field').hide();
            $('#location_field').show();
        } else {
            $('#meeting_link_field').hide();
            $('#location_field').hide();
        }
    }

    $('#type').on('change', toggleTypeFields);
    toggleTypeFields();
});
</script>
@endpush
