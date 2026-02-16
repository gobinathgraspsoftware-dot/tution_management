{{-- resources/views/admin/classes/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Create Class')
@section('page-title', 'Create New Class')

@section('content')
<div class="container-fluid">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Class Information</h4>
            <p class="text-muted mb-0">Fill in the details to create a new class</p>
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

    <form action="{{ route('admin.classes.store') }}" method="POST" id="classForm">
        @csrf

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="row">
                    {{-- Class Name --}}
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">Class Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}" placeholder="e.g., SEJARAH" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Class Code (Auto-Generated Preview) --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Class Code</label>
                        <div class="input-group">
                            <span class="input-group-text" id="codeIcon">
                                <i class="fas fa-hashtag text-primary"></i>
                            </span>
                            <input type="text" class="form-control bg-light fw-bold" id="codePreview"
                                   value="Select a subject to preview code" disabled readonly>
                        </div>
                        <small class="text-muted" id="codeHelpText">
                            <i class="fas fa-info-circle me-1"></i>
                            Code is auto-generated based on the selected subject (e.g., SEJ001, MAT002)
                        </small>
                    </div>

                    {{-- Subject (with data attributes for client-side code preview) --}}
                    <div class="col-md-6 mb-3">
                        <label for="subject_id" class="form-label">Subject <span class="text-danger">*</span></label>
                        <select name="subject_id" id="subject_id"
                                class="form-select @error('subject_id') is-invalid @enderror" required>
                            <option value="">-- Select Subject --</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}"
                                        data-code="{{ $subject->code ?? '' }}"
                                        data-name="{{ $subject->name }}"
                                        {{ old('subject_id') == $subject->id ? 'selected' : '' }}>
                                    {{ $subject->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('subject_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Teacher --}}
                    <div class="col-md-6 mb-3">
                        <label for="teacher_id" class="form-label">Teacher</label>
                        <select name="teacher_id" id="teacher_id"
                                class="form-select @error('teacher_id') is-invalid @enderror">
                            <option value="">-- Select Teacher --</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}" {{ old('teacher_id') == $teacher->id ? 'selected' : '' }}>
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
                            <option value="online" {{ old('type') == 'online' ? 'selected' : '' }}>Online</option>
                            <option value="offline" {{ old('type') == 'offline' ? 'selected' : '' }}>Offline</option>
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
                               value="{{ old('grade_level') }}" placeholder="e.g., FORM 2">
                        @error('grade_level')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Capacity --}}
                    <div class="col-md-6 mb-3">
                        <label for="capacity" class="form-label">Class Capacity <span class="text-danger">*</span></label>
                        <input type="number" name="capacity" id="capacity" min="1" max="100"
                               class="form-control @error('capacity') is-invalid @enderror"
                               value="{{ old('capacity', 30) }}" required>
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
                                   value="{{ old('price', '0.00') }}" placeholder="0.00" required>
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
                            <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
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
                               value="{{ old('meeting_link') }}" placeholder="https://...">
                        @error('meeting_link')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Location (shown for offline classes) --}}
                    <div class="col-md-6 mb-3" id="location_field" style="display: none;">
                        <label for="location" class="form-label">Location <span class="text-danger">*</span></label>
                        <input type="text" name="location" id="location"
                               class="form-control @error('location') is-invalid @enderror"
                               value="{{ old('location') }}" placeholder="e.g., Room 101, Block A">
                        @error('location')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Description --}}
                    <div class="col-12 mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea name="description" id="description" rows="3"
                                  class="form-control @error('description') is-invalid @enderror"
                                  placeholder="Optional class description...">{{ old('description') }}</textarea>
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
                    <i class="fas fa-save me-1"></i> Create Class
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

    // ==================================================================
    // Client-side class code preview (no AJAX, no extra route needed)
    // Reads subject code/name from data attributes on select options
    // and generates the preview prefix + next number format
    // ==================================================================

    // Pre-built map of existing class code counts per prefix from server
    var existingCodes = {!! json_encode(
        \App\Models\ClassModel::withTrashed()
            ->pluck('code')
            ->filter(function($code) {
                return !str_contains($code, '_del_');
            })
            ->values()
    ) !!};

    function getNextCode(prefix) {
        // Find the highest number used for this prefix from existing codes
        var maxNum = 0;
        for (var i = 0; i < existingCodes.length; i++) {
            var code = existingCodes[i];
            if (code && code.indexOf(prefix) === 0) {
                var numPart = code.substring(prefix.length);
                var num = parseInt(numPart, 10);
                if (!isNaN(num) && num > maxNum) {
                    maxNum = num;
                }
            }
        }
        var nextNum = maxNum + 1;
        // Pad to 3 digits
        var padded = ('000' + nextNum).slice(-3);
        return prefix + padded;
    }

    $('#subject_id').on('change', function() {
        var $selected = $(this).find('option:selected');
        var $preview = $('#codePreview');
        var $icon = $('#codeIcon i');

        if (!$(this).val()) {
            $preview.val('Select a subject to preview code');
            $icon.attr('class', 'fas fa-hashtag text-primary');
            return;
        }

        // Get prefix from subject code or name (first 3 chars, uppercase)
        var subjectCode = $selected.data('code') || '';
        var subjectName = $selected.data('name') || '';
        var source = subjectCode || subjectName;
        var prefix = source.substring(0, 3).toUpperCase().replace(/[^A-Z0-9]/g, '');

        if (prefix.length === 0) {
            prefix = 'CLS';
        }

        var nextCode = getNextCode(prefix);
        $preview.val(nextCode);
        $icon.attr('class', 'fas fa-check-circle text-success');
    });

    // Trigger preview if subject was pre-selected (from old() on validation error)
    if ($('#subject_id').val()) {
        $('#subject_id').trigger('change');
    }
});
</script>
@endpush
