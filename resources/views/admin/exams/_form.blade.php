{{-- Exam Form Fields --}}
<div class="row">
    <div class="col-md-6">
        <div class="mb-3">
            <label for="name" class="form-label fw-semibold">Exam Name <span class="text-danger">*</span></label>
            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                   value="{{ old('name', $exam->name ?? '') }}" placeholder="e.g., Mid Term Mathematics" required>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <label for="class_id" class="form-label fw-semibold">Class <span class="text-danger">*</span></label>
            <select name="class_id" id="class_id" class="form-select @error('class_id') is-invalid @enderror" required>
                <option value="">-- Select Class --</option>
                @foreach($classes as $class)
                    <option value="{{ $class->id }}" 
                            data-subject-id="{{ $class->subject_id }}"
                            {{ old('class_id', $exam->class_id ?? '') == $class->id ? 'selected' : '' }}>
                        {{ $class->name }} {{ $class->subject ? '(' . $class->subject->name . ')' : '' }}
                    </option>
                @endforeach
            </select>
            @error('class_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="mb-3">
            <label for="subject_id" class="form-label fw-semibold">Subject <span class="text-danger">*</span></label>
            <select name="subject_id" id="subject_id" class="form-select @error('subject_id') is-invalid @enderror" required>
                <option value="">-- Select Subject --</option>
                @foreach($subjects as $subject)
                    <option value="{{ $subject->id }}" {{ old('subject_id', $exam->subject_id ?? '') == $subject->id ? 'selected' : '' }}>
                        {{ $subject->name }}
                    </option>
                @endforeach
            </select>
            @error('subject_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <label for="exam_date" class="form-label fw-semibold">Exam Date <span class="text-danger">*</span></label>
            <input type="date" name="exam_date" id="exam_date" class="form-control @error('exam_date') is-invalid @enderror"
                   value="{{ old('exam_date', isset($exam) && $exam->exam_date ? $exam->exam_date->format('Y-m-d') : '') }}" required>
            @error('exam_date')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="mb-3">
            <label for="start_time" class="form-label fw-semibold">Start Time <span class="text-danger">*</span></label>
            <input type="time" name="start_time" id="start_time" class="form-control @error('start_time') is-invalid @enderror"
                   value="{{ old('start_time', isset($exam) && $exam->start_time ? \Carbon\Carbon::parse($exam->start_time)->format('H:i') : '') }}" required>
            @error('start_time')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="mb-3">
            <label for="duration_minutes" class="form-label fw-semibold">Duration (Minutes) <span class="text-danger">*</span></label>
            <input type="number" name="duration_minutes" id="duration_minutes" class="form-control @error('duration_minutes') is-invalid @enderror"
                   value="{{ old('duration_minutes', $exam->duration_minutes ?? 60) }}" min="1" max="480" required>
            @error('duration_minutes')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="mb-3">
            <label for="status" class="form-label fw-semibold">Status</label>
            <select name="status" id="status" class="form-select @error('status') is-invalid @enderror">
                <option value="scheduled" {{ old('status', $exam->status ?? 'scheduled') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                <option value="ongoing" {{ old('status', $exam->status ?? '') == 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                <option value="completed" {{ old('status', $exam->status ?? '') == 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="cancelled" {{ old('status', $exam->status ?? '') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
            @error('status')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="mb-3">
            <label for="max_marks" class="form-label fw-semibold">Maximum Marks <span class="text-danger">*</span></label>
            <input type="number" name="max_marks" id="max_marks" class="form-control @error('max_marks') is-invalid @enderror"
                   value="{{ old('max_marks', $exam->max_marks ?? 100) }}" min="1" step="0.01" required>
            @error('max_marks')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <label for="passing_marks" class="form-label fw-semibold">Passing Marks <span class="text-danger">*</span></label>
            <input type="number" name="passing_marks" id="passing_marks" class="form-control @error('passing_marks') is-invalid @enderror"
                   value="{{ old('passing_marks', $exam->passing_marks ?? 40) }}" min="0" step="0.01" required>
            @error('passing_marks')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="mb-3">
            <label for="description" class="form-label fw-semibold">Description</label>
            <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror"
                      rows="3" placeholder="Exam description or instructions...">{{ old('description', $exam->description ?? '') }}</textarea>
            @error('description')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-fill subject when class is selected
    $('#class_id').on('change', function() {
        var subjectId = $(this).find(':selected').data('subject-id');
        if (subjectId) {
            $('#subject_id').val(subjectId);
        }
    });

    // Validate passing marks <= max marks
    $('#passing_marks, #max_marks').on('change', function() {
        var maxMarks = parseFloat($('#max_marks').val()) || 0;
        var passingMarks = parseFloat($('#passing_marks').val()) || 0;
        if (passingMarks > maxMarks) {
            $('#passing_marks').val(maxMarks);
            alert('Passing marks cannot exceed maximum marks.');
        }
    });
});
</script>
@endpush
