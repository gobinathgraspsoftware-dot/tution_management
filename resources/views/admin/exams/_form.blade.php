{{--
    Shared form partial for admin exam create/edit.
    Variables expected: $classes, $subjects, $gradeLevels
    Optional: $exam (for edit mode)
--}}
@php
    $isEdit = isset($exam) && $exam->exists;

    // Prepare data for JS (must be done in @php to avoid Blade parse errors with closures)
    $jsSubjects = $subjects->map(function($s) {
        return ['id' => $s->id, 'name' => $s->name, 'code' => $s->code, 'grade_levels' => $s->grade_levels ?? []];
    })->values();

    $jsClasses = $classes->map(function($c) {
        return [
            'id' => $c->id,
            'name' => $c->name,
            'subject_id' => $c->subject_id,
            'grade_level_id' => $c->grade_level_id,
            'teacher_name' => optional(optional($c->teacher)->user)->name ?? 'N/A',
        ];
    })->values();
@endphp

<div class="row">
    {{-- 1. Exam Name --}}
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

    {{-- 2. Grade Level (select first) --}}
    <div class="col-md-4 mb-3">
        <label for="grade_level_id" class="form-label fw-semibold">
            Grade Level <span class="text-danger">*</span>
        </label>
        <select class="form-select" id="grade_level_id" required>
            <option value="">-- Select Grade Level --</option>
            @foreach($gradeLevels as $gl)
                <option value="{{ $gl->id }}"
                    {{ old('grade_level_id', $isEdit ? ($exam->class->grade_level_id ?? '') : '') == $gl->id ? 'selected' : '' }}>
                    {{ $gl->name }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- 3. Subject (filtered by grade level) --}}
    <div class="col-md-4 mb-3">
        <label for="subject_id" class="form-label fw-semibold">
            Subject <span class="text-danger">*</span>
        </label>
        <select name="subject_id" id="subject_id"
                class="form-select @error('subject_id') is-invalid @enderror" required>
            <option value="">-- Select Grade Level First --</option>
        </select>
        @error('subject_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- 4. Class (filtered by grade level + subject) --}}
    <div class="col-md-4 mb-3">
        <label for="class_id" class="form-label fw-semibold">
            Class <span class="text-danger">*</span>
        </label>
        <select name="class_id" id="class_id"
                class="form-select @error('class_id') is-invalid @enderror" required>
            <option value="">-- Select Subject First --</option>
        </select>
        @error('class_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
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
    var allSubjects = {!! json_encode($jsSubjects) !!};
    var allClasses  = {!! json_encode($jsClasses) !!};

    var preGradeLevel = '{{ old("grade_level_id", $isEdit ? ($exam->class->grade_level_id ?? "") : "") }}';
    var preSubject    = '{{ old("subject_id", $isEdit ? $exam->subject_id : "") }}';
    var preClass      = '{{ old("class_id", $isEdit ? $exam->class_id : "") }}';

    $('#grade_level_id').on('change', function() {
        var gradeLevelId = parseInt($(this).val()) || 0;
        var $subjectSelect = $('#subject_id');
        var $classSelect   = $('#class_id');

        $classSelect.html('<option value="">-- Select Subject First --</option>');

        if (!gradeLevelId) {
            $subjectSelect.html('<option value="">-- Select Grade Level First --</option>');
            return;
        }

        var filtered = allSubjects.filter(function(s) {
            return s.grade_levels && s.grade_levels.includes(gradeLevelId);
        });

        var options = '<option value="">-- Select Subject --</option>';
        if (filtered.length === 0) {
            options = '<option value="">-- No Subjects for this Grade Level --</option>';
        } else {
            filtered.forEach(function(s) {
                var selected = (s.id == preSubject) ? 'selected' : '';
                options += '<option value="' + s.id + '" ' + selected + '>' + s.name + ' (' + s.code + ')</option>';
            });
        }
        $subjectSelect.html(options);

        if (preSubject && $subjectSelect.val()) {
            $subjectSelect.trigger('change');
        }
    });

    $('#subject_id').on('change', function() {
        var subjectId    = parseInt($(this).val()) || 0;
        var gradeLevelId = parseInt($('#grade_level_id').val()) || 0;
        var $classSelect = $('#class_id');

        if (!subjectId) {
            $classSelect.html('<option value="">-- Select Subject First --</option>');
            return;
        }

        var filtered = allClasses.filter(function(c) {
            return c.subject_id == subjectId && c.grade_level_id == gradeLevelId;
        });

        var options = '<option value="">-- Select Class --</option>';
        if (filtered.length === 0) {
            options = '<option value="">-- No Classes Available --</option>';
        } else {
            filtered.forEach(function(c) {
                var selected = (c.id == preClass) ? 'selected' : '';
                options += '<option value="' + c.id + '" ' + selected + '>' + c.name + ' (' + c.teacher_name + ')</option>';
            });
        }
        $classSelect.html(options);
    });

    if (preGradeLevel) {
        $('#grade_level_id').trigger('change');
    }
});
</script>
@endpush
