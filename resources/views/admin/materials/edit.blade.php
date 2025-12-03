@extends('layouts.app')

@section('title', 'Edit Material')
@section('page-title', 'Edit Material')

@section('content')
<div class="page-header">
    <h1><i class="fas fa-edit me-2"></i> Edit Material</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.materials.index') }}">Materials</a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol>
    </nav>
</div>

<form action="{{ route('admin.materials.update', $material) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

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

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Class <span class="text-danger">*</span></label>
                            <select name="class_id" id="classSelect" class="form-select @error('class_id') is-invalid @enderror" required>
                                <option value="">Select Class</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}"
                                            data-subject="{{ $class->subject_id }}"
                                            {{ old('class_id', $material->class_id) == $class->id ? 'selected' : '' }}>
                                        {{ $class->name }} - {{ $class->subject->name ?? 'N/A' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('class_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Subject <span class="text-danger">*</span></label>
                            <select name="subject_id" id="subjectSelect" class="form-select @error('subject_id') is-invalid @enderror" required>
                                <option value="">Select Subject</option>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}" {{ old('subject_id', $material->subject_id) == $subject->id ? 'selected' : '' }}>
                                        {{ $subject->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('subject_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Teacher <span class="text-danger">*</span></label>
                            <select name="teacher_id" class="form-select @error('teacher_id') is-invalid @enderror" required>
                                <option value="">Select Teacher</option>
                                @foreach($teachers as $teacher)
                                    <option value="{{ $teacher->id }}" {{ old('teacher_id', $material->teacher_id) == $teacher->id ? 'selected' : '' }}>
                                        {{ $teacher->user->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('teacher_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Type <span class="text-danger">*</span></label>
                            <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                                <option value="">Select Type</option>
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
                            Allowed types: PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX. Max size: 10MB.
                            <strong>Note:</strong> Replacing the file will reset approval status.
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
            <!-- Status Card -->
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
                            <option value="archived" {{ old('status', $material->status) == 'archived' ? 'selected' : '' }}>Archived</option>
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
                               {{ old('is_downloadable', $material->is_downloadable) ? 'checked' : '' }}>
                        <label class="form-check-label" for="isDownloadable">
                            Allow Download
                        </label>
                    </div>

                    <div class="form-check mb-3">
                        <input type="checkbox" name="is_featured" class="form-check-input"
                               id="isFeatured" value="1"
                               {{ old('is_featured', $material->is_featured) ? 'checked' : '' }}>
                        <label class="form-check-label" for="isFeatured">
                            Featured Material
                        </label>
                    </div>
                </div>
            </div>

            <!-- Current Status -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <i class="fas fa-info-circle me-2"></i> Current Status
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <small class="text-muted">Approval Status:</small>
                        <p class="mb-0">
                            @if($material->is_approved)
                                <span class="badge bg-success">Approved</span>
                            @else
                                <span class="badge bg-warning">Pending Approval</span>
                            @endif
                        </p>
                    </div>
                    @if($material->approved_by)
                    <div class="mb-2">
                        <small class="text-muted">Approved By:</small>
                        <p class="mb-0">{{ $material->approvedBy->name }}</p>
                    </div>
                    @endif
                    <div class="mb-0">
                        <small class="text-muted">Uploaded:</small>
                        <p class="mb-0">{{ $material->created_at->format('d M Y') }}</p>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="card">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary w-100 mb-2">
                        <i class="fas fa-save me-1"></i> Update Material
                    </button>
                    <a href="{{ route('admin.materials.show', $material) }}" class="btn btn-outline-secondary w-100">
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
// Auto-fill subject when class is selected
document.getElementById('classSelect').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const subjectId = selectedOption.getAttribute('data-subject');

    if (subjectId) {
        document.getElementById('subjectSelect').value = subjectId;
    }
});
</script>
@endpush
