{{--
    Shared form partial for admin exam create/edit.
    Variables expected: $classes, $subjects, $gradeLevels
    Optional: $exam (for edit mode)
--}}
@php
    $isEdit = isset($exam) && $exam->exists;
@endphp

<div class="row">
    {{-- Exam Name --}}
    <div class="col-md-12 mb-3">
        <label for="name" class="form-label fw-semibold">
            Exam Name <span class="text-danger">*</span>
        </label>
        <input type="text" name="name" id="name"
               class="form-control @error('name') is-invalid @enderror"
               value="{{ old('name', $isEdit ? $exam->name : '') }}"
               placeholder="e.g., Mid-Term Exam 2025" required>
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Class (UNCHANGED - original flow) --}}
    <div class="col-md-6 mb-3">
        <label for="class_id" class="form-label fw-semibold">
            Class <span class="text-danger">*</span>
        </label>
        <select name="class_id" id="class_id"
                class="form-select @error('class_id') is-invalid @enderror" required>
            <option value="">-- Select Class --</option>
            @foreach($classes as $class)
                <option value="{{ $class->id }}"
                    data-subject="{{ $class->subject_id ?? '' }}"
                    {{ old('class_id', $isEdit ? $exam->class_id : '') == $class->id ? 'selected' : '' }}>
                    {{ $class->name }}
                </option>
            @endforeach
        </select>
        @error('class_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Subject (UNCHANGED flow, added data-grade-levels attribute) --}}
    <div class="col-md-6 mb-3">
        <label for="subject_id" class="form-label fw-semibold">
            Subject <span class="text-danger">*</span>
        </label>
        <select name="subject_id" id="subject_id"
                class="form-select @error('subject_id') is-invalid @enderror" required>
            <option value="">-- Select Subject --</option>
            @foreach($subjects as $subject)
                <option value="{{ $subject->id }}"
                    data-grade-levels="{{ json_encode($subject->grade_levels ?? []) }}"
                    {{ old('subject_id', $isEdit ? $exam->subject_id : '') == $subject->id ? 'selected' : '' }}>
                    {{ $subject->name }} ({{ $subject->code }})
                </option>
            @endforeach
        </select>
        @error('subject_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- NEW: Grade Level (filtered by selected subject - informational display) --}}
    <div class="col-md-12 mb-3">
        <label for="grade_level_id" class="form-label fw-semibold">Grade Level</label>
        <select class="form-select" id="grade_level_id" disabled>
            <option value="">-- Select Subject to view Grade Levels --</option>
        </select>
        <small class="form-text text-muted">
            <i class="fas fa-info-circle me-1"></i>Shows grade levels mapped to the selected subject
        </small>
    </div>

    {{-- Exam Date --}}
    <div class="col-md-4 mb-3">
        <label for="exam_date" class="form-label fw-semibold">
            Exam Date <span class="text-danger">*</span>
        </label>
        <input type="date" name="exam_date" id="exam_date"
               class="form-control @error('exam_date') is-invalid @enderror"
               value="{{ old('exam_date', $isEdit ? $exam->exam_date->format('Y-m-d') : '') }}"
               required>
        @error('exam_date')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Start Time --}}
    <div class="col-md-4 mb-3">
        <label for="start_time" class="form-label fw-semibold">
            Start Time <span class="text-danger">*</span>
        </label>
        <input type="time" name="start_time" id="start_time"
               class="form-control @error('start_time') is-invalid @enderror"
               value="{{ old('start_time', $isEdit ? \Carbon\Carbon::parse($exam->start_time)->format('H:i') : '') }}"
               required>
        @error('start_time')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Duration --}}
    <div class="col-md-4 mb-3">
        <label for="duration_minutes" class="form-label fw-semibold">
            Duration (Minutes) <span class="text-danger">*</span>
        </label>
        <input type="number" name="duration_minutes" id="duration_minutes"
               class="form-control @error('duration_minutes') is-invalid @enderror"
               value="{{ old('duration_minutes', $isEdit ? $exam->duration_minutes : '') }}"
               min="1" max="480" placeholder="e.g., 60" required>
        @error('duration_minutes')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Max Marks --}}
    <div class="col-md-6 mb-3">
        <label for="max_marks" class="form-label fw-semibold">
            Maximum Marks <span class="text-danger">*</span>
        </label>
        <input type="number" name="max_marks" id="max_marks"
               class="form-control @error('max_marks') is-invalid @enderror"
               value="{{ old('max_marks', $isEdit ? $exam->max_marks : '') }}"
               min="1" max="9999.99" step="0.01" placeholder="e.g., 100" required>
        @error('max_marks')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Passing Marks --}}
    <div class="col-md-6 mb-3">
        <label for="passing_marks" class="form-label fw-semibold">
            Passing Marks <span class="text-danger">*</span>
        </label>
        <input type="number" name="passing_marks" id="passing_marks"
               class="form-control @error('passing_marks') is-invalid @enderror"
               value="{{ old('passing_marks', $isEdit ? $exam->passing_marks : '') }}"
               min="0" max="9999.99" step="0.01" placeholder="e.g., 40" required>
        @error('passing_marks')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Status (only on edit) --}}
    @if($isEdit)
        <div class="col-md-6 mb-3">
            <label for="status" class="form-label fw-semibold">
                Status <span class="text-danger">*</span>
            </label>
            <select name="status" id="status"
                    class="form-select @error('status') is-invalid @enderror" required>
                <option value="scheduled" {{ old('status', $exam->status) == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                <option value="ongoing" {{ old('status', $exam->status) == 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                <option value="completed" {{ old('status', $exam->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="cancelled" {{ old('status', $exam->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
            @error('status')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    @else
        <input type="hidden" name="status" value="scheduled">
    @endif

    {{-- Description --}}
    <div class="col-md-12 mb-3">
        <label for="description" class="form-label fw-semibold">Description</label>
        <textarea name="description" id="description" rows="3"
                  class="form-control @error('description') is-invalid @enderror"
                  placeholder="Optional exam description or instructions...">{{ old('description', $isEdit ? $exam->description : '') }}</textarea>
        @error('description')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // All grade levels from server (for client-side filtering)
    const allGradeLevels = @json($gradeLevels);

    // Pre-selected grade level from exam's class (for edit mode highlight)
    const preSelectedGradeLevelId = '{{ $isEdit ? ($exam->class->grade_level_id ?? "") : "" }}';

    // =========================================================================
    // EXISTING: Auto-select subject based on class (UNCHANGED)
    // =========================================================================
    $('#class_id').on('change', function() {
        var subjectId = $(this).find(':selected').data('subject');
        if (subjectId) {
            $('#subject_id').val(subjectId).trigger('change');
        }
    });

    // =========================================================================
    // NEW: Subject Change → Load mapped Grade Levels
    // =========================================================================
    $('#subject_id').on('change', function() {
        var subjectId = $(this).val();
        var $gradeSelect = $('#grade_level_id');

        if (!subjectId) {
            $gradeSelect.html('<option value="">-- Select Subject to view Grade Levels --</option>').prop('disabled', true);
            return;
        }

        // Get mapped grade level IDs from the selected option's data attribute
        var mappedIds = $(this).find(':selected').data('grade-levels') || [];

        if (mappedIds.length === 0) {
            $gradeSelect.html('<option value="">-- No Grade Levels Mapped to this Subject --</option>').prop('disabled', true);
            return;
        }

        // Filter allGradeLevels by the mapped IDs and build options
        var options = '<option value="">-- ' + mappedIds.length + ' Grade Level(s) Mapped --</option>';
        allGradeLevels.forEach(function(gl) {
            if (mappedIds.includes(gl.id)) {
                var selected = (gl.id == preSelectedGradeLevelId) ? 'selected' : '';
                options += '<option value="' + gl.id + '" ' + selected + '>' + gl.name + '</option>';
            }
        });

        $gradeSelect.html(options).prop('disabled', false);
    });

    // Trigger on page load if subject is pre-selected (edit mode or old() re-fill)
    if ($('#subject_id').val()) {
        $('#subject_id').trigger('change');
    }
});
</script>
@endpush
