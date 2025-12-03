<div class="mb-3">
    <label for="name" class="form-label">Exam Name <span class="text-danger">*</span></label>
    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
           value="{{ old('name', $exam->name ?? '') }}" required>
    @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="class_id" class="form-label">Class <span class="text-danger">*</span></label>
        <select name="class_id" id="class_id" class="form-select @error('class_id') is-invalid @enderror" required>
            <option value="">Select Class</option>
            @foreach($classes as $class)
                <option value="{{ $class->id }}" {{ old('class_id', $exam->class_id ?? '') == $class->id ? 'selected' : '' }}>
                    {{ $class->name }} - {{ $class->subject->name }}
                </option>
            @endforeach
        </select>
        @error('class_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label for="subject_id" class="form-label">Subject <span class="text-danger">*</span></label>
        <select name="subject_id" id="subject_id" class="form-select @error('subject_id') is-invalid @enderror" required>
            <option value="">Select Subject</option>
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

<div class="mb-3">
    <label for="description" class="form-label">Description</label>
    <textarea name="description" id="description" rows="2" class="form-control @error('description') is-invalid @enderror">{{ old('description', $exam->description ?? '') }}</textarea>
    @error('description')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <label for="exam_date" class="form-label">Exam Date <span class="text-danger">*</span></label>
        <input type="date" name="exam_date" id="exam_date" class="form-control @error('exam_date') is-invalid @enderror"
               value="{{ old('exam_date', isset($exam) ? $exam->exam_date : '') }}" required>
        @error('exam_date')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4 mb-3">
        <label for="start_time" class="form-label">Start Time <span class="text-danger">*</span></label>
        <input type="time" name="start_time" id="start_time" class="form-control @error('start_time') is-invalid @enderror"
               value="{{ old('start_time', isset($exam) ? substr($exam->start_time, 0, 5) : '') }}" required>
        @error('start_time')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4 mb-3">
        <label for="end_time" class="form-label">End Time <span class="text-danger">*</span></label>
        <input type="time" name="end_time" id="end_time" class="form-control @error('end_time') is-invalid @enderror"
               value="{{ old('end_time', isset($exam) ? substr($exam->end_time, 0, 5) : '') }}" required>
        @error('end_time')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <label for="duration" class="form-label">Duration (minutes) <span class="text-danger">*</span></label>
        <input type="number" name="duration" id="duration" class="form-control @error('duration') is-invalid @enderror"
               value="{{ old('duration', $exam->duration ?? '') }}" min="1" required>
        @error('duration')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4 mb-3">
        <label for="max_marks" class="form-label">Maximum Marks <span class="text-danger">*</span></label>
        <input type="number" name="max_marks" id="max_marks" class="form-control @error('max_marks') is-invalid @enderror"
               value="{{ old('max_marks', $exam->max_marks ?? '') }}" min="1" step="0.01" required>
        @error('max_marks')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4 mb-3">
        <label for="passing_marks" class="form-label">Passing Marks <span class="text-danger">*</span></label>
        <input type="number" name="passing_marks" id="passing_marks" class="form-control @error('passing_marks') is-invalid @enderror"
               value="{{ old('passing_marks', $exam->passing_marks ?? '') }}" min="1" step="0.01" required>
        @error('passing_marks')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="mb-3">
    <label for="instructions" class="form-label">Instructions for Students</label>
    <textarea name="instructions" id="instructions" rows="4" class="form-control @error('instructions') is-invalid @enderror">{{ old('instructions', $exam->instructions ?? '') }}</textarea>
    <small class="text-muted">These instructions will be displayed to students</small>
    @error('instructions')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
