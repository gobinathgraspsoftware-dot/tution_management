@extends('layouts.app')

@section('title', 'Edit Material')
@section('page-title', 'Edit Material')

@section('content')
<div class="page-header">
    <h1><i class="fas fa-edit me-2"></i> Edit Material</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('teacher.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('teacher.materials.index') }}">My Materials</a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol>
    </nav>
</div>

<form action="{{ route('teacher.materials.update', $material) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    {{-- Hidden access_type field — synced from is_downloadable checkbox via JS --}}
    <input type="hidden" name="access_type" id="accessTypeHidden" value="{{ old('access_type', $material->access_type ?? 'view_only') }}">

    <div class="row">
        <!-- Material Information -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-file-alt me-2"></i> Material Information
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                               value="{{ old('title', $material->title) }}" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                                  rows="4">{{ old('description', $material->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- ADDED: Grade Level → Subject → Class cascading dropdowns --}}
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Grade Level <span class="text-danger">*</span></label>
                            <select name="grade_level_id" id="gradeLevelSelect" class="form-select @error('grade_level_id') is-invalid @enderror" required>
                                <option value="">Select Grade Level</option>
                                @foreach($gradeLevels as $gradeLevel)
                                    <option value="{{ $gradeLevel->id }}" {{ old('grade_level_id', $material->grade_level_id) == $gradeLevel->id ? 'selected' : '' }}>
                                        {{ $gradeLevel->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('grade_level_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Subject <span class="text-danger">*</span></label>
                            <select name="subject_id" id="subjectSelect" class="form-select @error('subject_id') is-invalid @enderror" required>
                                <option value="">Select Subject</option>
                            </select>
                            @error('subject_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Class <span class="text-danger">*</span></label>
                            <select name="class_id" id="classSelect" class="form-select @error('class_id') is-invalid @enderror" required>
                                <option value="">Select Class</option>
                            </select>
                            @error('class_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Type <span class="text-danger">*</span></label>
                        <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                            <option value="notes" {{ old('type', $material->type) == 'notes' ? 'selected' : '' }}>Notes</option>
                            <option value="presentation" {{ old('type', $material->type) == 'presentation' ? 'selected' : '' }}>Presentation</option>
                            <option value="worksheet" {{ old('type', $material->type) == 'worksheet' ? 'selected' : '' }}>Worksheet</option>
                            <option value="assignment" {{ old('type', $material->type) == 'assignment' ? 'selected' : '' }}>Assignment</option>
                            <option value="reference" {{ old('type', $material->type) == 'reference' ? 'selected' : '' }}>Reference Material</option>
                        </select>
                        @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Current File</label>
                        <div class="alert alert-info mb-2">
                            <i class="fas fa-file me-2"></i>
                            <strong>{{ $material->title }}.{{ $material->file_type }}</strong>
                            <span class="ms-2 text-muted">({{ number_format($material->file_size / 1024, 2) }} KB)</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Replace File (Optional)</label>
                        <input type="file" name="file" class="form-control @error('file') is-invalid @enderror"
                               accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx">
                        <small class="form-text text-muted">
                            Leave empty to keep current file. Uploading new file will reset approval status.
                        </small>
                        @error('file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Settings & Actions -->
        <div class="col-md-4">
            <!-- Settings Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-cog me-2"></i> Settings
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="draft" {{ old('status', $material->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="published" {{ old('status', $material->status) == 'published' ? 'selected' : '' }}>Published</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Publish Date</label>
                        <input type="date" name="publish_date" class="form-control @error('publish_date') is-invalid @enderror"
                               value="{{ old('publish_date', $material->publish_date?->format('Y-m-d')) }}">
                        @error('publish_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-check mb-3">
                        <input type="checkbox" name="is_downloadable" class="form-check-input"
                               id="isDownloadable" value="1"
                               {{ old('is_downloadable', $material->access_type == 'downloadable' ? 1 : 0) ? 'checked' : '' }}>
                        <label class="form-check-label" for="isDownloadable">
                            Allow Download
                        </label>
                    </div>
                </div>
            </div>

            <!-- Approval Status -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <i class="fas fa-info-circle me-2"></i> Approval Status
                </div>
                <div class="card-body">
                    @if($material->is_approved)
                        <div class="alert alert-success mb-0">
                            <i class="fas fa-check-circle me-2"></i>
                            This material has been approved
                        </div>
                    @else
                        <div class="alert alert-warning mb-0">
                            <i class="fas fa-clock me-2"></i>
                            Waiting for admin approval
                        </div>
                    @endif
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="card">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary w-100 mb-2">
                        <i class="fas fa-save me-1"></i> Update Material
                    </button>
                    <a href="{{ route('teacher.materials.show', $material) }}" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-times me-1"></i> Cancel
                    </a>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    const curGL  = '{{ old("grade_level_id", $material->grade_level_id) }}';
    const curSub = '{{ old("subject_id", $material->subject_id) }}';
    const curCls = '{{ old("class_id", $material->class_id) }}';

    /**
     * Cascading Dropdown: Grade Level → Subject → Class
     * Teacher-scoped: only shows teacher's own classes/subjects
     */

    // Grade Level change → load Subjects
    $('#gradeLevelSelect').on('change', function() {
        const gradeLevelId = $(this).val();
        $('#subjectSelect').html('<option value="">Select Subject</option>').prop('disabled', true);
        $('#classSelect').html('<option value="">Select Class</option>').prop('disabled', true);
        if (!gradeLevelId) return;

        $.ajax({
            url: '{{ route("teacher.materials.get-subjects-by-grade-level") }}',
            data: { grade_level_id: gradeLevelId },
            success: function(subjects) {
                let opts = '<option value="">Select Subject</option>';
                subjects.forEach(function(s) {
                    const sel = (curSub == s.id) ? 'selected' : '';
                    opts += `<option value="${s.id}" ${sel}>${s.name}</option>`;
                });
                $('#subjectSelect').html(opts).prop('disabled', false);
                if (curSub) $('#subjectSelect').val(curSub).trigger('change');
            },
            error: function() {
                $('#subjectSelect').html('<option value="">Failed to load</option>');
            }
        });
    });

    // Subject change → load Classes
    $('#subjectSelect').on('change', function() {
        const subjectId = $(this).val();
        const gradeLevelId = $('#gradeLevelSelect').val();
        $('#classSelect').html('<option value="">Select Class</option>').prop('disabled', true);
        if (!subjectId || !gradeLevelId) return;

        $.ajax({
            url: '{{ route("teacher.materials.get-classes-by-filters") }}',
            data: { grade_level_id: gradeLevelId, subject_id: subjectId },
            success: function(classes) {
                let opts = '<option value="">Select Class</option>';
                classes.forEach(function(c) {
                    const sel = (curCls == c.id) ? 'selected' : '';
                    opts += `<option value="${c.id}" ${sel}>${c.name} (${c.grade_level_name})</option>`;
                });
                $('#classSelect').html(opts).prop('disabled', false);
                if (curCls) $('#classSelect').val(curCls);
            },
            error: function() {
                $('#classSelect').html('<option value="">Failed to load</option>');
            }
        });
    });

    // On page load: trigger cascade to populate with current values
    if (curGL) {
        $('#gradeLevelSelect').trigger('change');
    }

    // Sync is_downloadable checkbox → hidden access_type field
    function syncAccessType() {
        $('#accessTypeHidden').val($('#isDownloadable').is(':checked') ? 'downloadable' : 'view_only');
    }
    $('#isDownloadable').on('change', syncAccessType);
    syncAccessType();
});
</script>
@endpush
