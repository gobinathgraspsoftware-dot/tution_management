<div class="mb-3">
    <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
    <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror"
           value="{{ old('title', $announcement->title ?? '') }}" required>
    @error('title')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="content" class="form-label">Content <span class="text-danger">*</span></label>
    <textarea name="content" id="content" rows="8" class="form-control @error('content') is-invalid @enderror" required>{{ old('content', $announcement->content ?? '') }}</textarea>
    @error('content')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <label for="type" class="form-label">Type <span class="text-danger">*</span></label>
        <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required>
            <option value="">Select Type</option>
            <option value="general" {{ old('type', $announcement->type ?? '') == 'general' ? 'selected' : '' }}>General</option>
            <option value="academic" {{ old('type', $announcement->type ?? '') == 'academic' ? 'selected' : '' }}>Academic</option>
            <option value="event" {{ old('type', $announcement->type ?? '') == 'event' ? 'selected' : '' }}>Event</option>
            <option value="holiday" {{ old('type', $announcement->type ?? '') == 'holiday' ? 'selected' : '' }}>Holiday</option>
            <option value="urgent" {{ old('type', $announcement->type ?? '') == 'urgent' ? 'selected' : '' }}>Urgent</option>
        </select>
        @error('type')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4 mb-3">
        <label for="priority" class="form-label">Priority <span class="text-danger">*</span></label>
        <select name="priority" id="priority" class="form-select @error('priority') is-invalid @enderror" required>
            <option value="normal" {{ old('priority', $announcement->priority ?? 'normal') == 'normal' ? 'selected' : '' }}>Normal</option>
            <option value="high" {{ old('priority', $announcement->priority ?? '') == 'high' ? 'selected' : '' }}>High</option>
            <option value="urgent" {{ old('priority', $announcement->priority ?? '') == 'urgent' ? 'selected' : '' }}>Urgent</option>
        </select>
        @error('priority')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4 mb-3">
        <label for="target_audience" class="form-label">Target Audience <span class="text-danger">*</span></label>
        <select name="target_audience" id="target_audience" class="form-select @error('target_audience') is-invalid @enderror" required>
            <option value="all" {{ old('target_audience', $announcement->target_audience ?? '') == 'all' ? 'selected' : '' }}>All</option>
            <option value="students" {{ old('target_audience', $announcement->target_audience ?? '') == 'students' ? 'selected' : '' }}>Students</option>
            <option value="parents" {{ old('target_audience', $announcement->target_audience ?? '') == 'parents' ? 'selected' : '' }}>Parents</option>
            <option value="teachers" {{ old('target_audience', $announcement->target_audience ?? '') == 'teachers' ? 'selected' : '' }}>Teachers</option>
            <option value="specific_class" {{ old('target_audience', $announcement->target_audience ?? '') == 'specific_class' ? 'selected' : '' }}>Specific Class</option>
        </select>
        @error('target_audience')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="mb-3" id="classSelect" style="display: {{ old('target_audience', $announcement->target_audience ?? '') == 'specific_class' ? 'block' : 'none' }};">
    <label for="class_id" class="form-label">Select Class</label>
    <select name="class_id" id="class_id" class="form-select @error('class_id') is-invalid @enderror">
        <option value="">Select Class</option>
        @foreach($classes as $class)
            <option value="{{ $class->id }}" {{ old('class_id', $announcement->class_id ?? '') == $class->id ? 'selected' : '' }}>
                {{ $class->name }} - {{ $class->subject->name }}
            </option>
        @endforeach
    </select>
    @error('class_id')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="attachment" class="form-label">Attachment (optional)</label>
    <input type="file" name="attachment" id="attachment" class="form-control @error('attachment') is-invalid @enderror">
    <small class="text-muted">Max file size: 5MB. Allowed: PDF, DOC, DOCX, JPG, PNG</small>
    @error('attachment')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
    @if(isset($announcement) && $announcement->attachment_path)
        <div class="mt-2">
            <small>Current attachment: <a href="{{ Storage::url($announcement->attachment_path) }}" target="_blank">View</a></small>
        </div>
    @endif
</div>

<div class="mb-3">
    <label for="scheduled_at" class="form-label">Schedule for Later (optional)</label>
    <input type="datetime-local" name="scheduled_at" id="scheduled_at" class="form-control @error('scheduled_at') is-invalid @enderror"
           value="{{ old('scheduled_at', isset($announcement) && $announcement->scheduled_at ? $announcement->scheduled_at->format('Y-m-d\TH:i') : '') }}">
    <small class="text-muted">Leave empty to publish immediately</small>
    @error('scheduled_at')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="form-check mb-4">
    <input type="checkbox" name="is_pinned" id="is_pinned" class="form-check-input" value="1"
           {{ old('is_pinned', $announcement->is_pinned ?? false) ? 'checked' : '' }}>
    <label for="is_pinned" class="form-check-label">
        <i class="fas fa-thumbtack"></i> Pin this announcement (keeps it at the top)
    </label>
</div>

@push('scripts')
<script>
document.getElementById('target_audience').addEventListener('change', function() {
    document.getElementById('classSelect').style.display = this.value === 'specific_class' ? 'block' : 'none';
    if (this.value !== 'specific_class') {
        document.getElementById('class_id').value = '';
    }
});
</script>
@endpush
