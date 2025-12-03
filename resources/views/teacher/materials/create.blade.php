@extends('layouts.app')

@section('title', 'Upload Material')
@section('page-title', 'Upload Material')

@section('content')
<div class="page-header">
    <h1><i class="fas fa-upload me-2"></i> Upload New Material</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('teacher.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('teacher.materials.index') }}">My Materials</a></li>
            <li class="breadcrumb-item active">Upload New</li>
        </ol>
    </nav>
</div>

<form action="{{ route('teacher.materials.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

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
                               value="{{ old('title') }}" placeholder="Enter material title" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                                  rows="4" placeholder="Describe the material content...">{{ old('description') }}</textarea>
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
                                            {{ old('class_id') == $class->id ? 'selected' : '' }}>
                                        {{ $class->name }} - {{ $class->subject->name ?? 'N/A' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('class_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Type <span class="text-danger">*</span></label>
                            <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                                <option value="">Select Type</option>
                                <option value="notes" {{ old('type') == 'notes' ? 'selected' : '' }}>Notes</option>
                                <option value="presentation" {{ old('type') == 'presentation' ? 'selected' : '' }}>Presentation</option>
                                <option value="worksheet" {{ old('type') == 'worksheet' ? 'selected' : '' }}>Worksheet</option>
                                <option value="assignment" {{ old('type') == 'assignment' ? 'selected' : '' }}>Assignment</option>
                                <option value="reference" {{ old('type') == 'reference' ? 'selected' : '' }}>Reference Material</option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Upload File <span class="text-danger">*</span></label>
                        <input type="file" name="file" id="fileInput"
                               class="form-control @error('file') is-invalid @enderror"
                               accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx" required>
                        <small class="form-text text-muted">
                            Allowed types: PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX. Max size: 10MB
                        </small>
                        @error('file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div id="fileInfo" class="mt-2"></div>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Note:</strong> Uploaded materials require admin approval before becoming visible to students.
                    </div>
                </div>
            </div>
        </div>

        <!-- Settings & Actions -->
        <div class="col-md-4">
            <!-- Settings Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-cog me-2"></i> Material Settings
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="draft" {{ old('status', 'draft') == 'draft' ? 'selected' : '' }}>Save as Draft</option>
                            <option value="published" {{ old('status') == 'published' ? 'selected' : '' }}>Submit for Approval</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Publish Date</label>
                        <input type="date" name="publish_date" class="form-control @error('publish_date') is-invalid @enderror"
                               value="{{ old('publish_date') }}" min="{{ date('Y-m-d') }}">
                        @error('publish_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-check mb-3">
                        <input type="checkbox" name="is_downloadable" class="form-check-input"
                               id="isDownloadable" value="1" {{ old('is_downloadable') ? 'checked' : '' }}>
                        <label class="form-check-label" for="isDownloadable">
                            Allow students to download
                        </label>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="card mb-4">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary w-100 mb-2">
                        <i class="fas fa-upload me-1"></i> Upload Material
                    </button>
                    <a href="{{ route('teacher.materials.index') }}" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-times me-1"></i> Cancel
                    </a>
                </div>
            </div>

            <!-- Help Card -->
            <div class="card">
                <div class="card-header bg-light">
                    <i class="fas fa-question-circle me-2"></i> Guidelines
                </div>
                <div class="card-body">
                    <ul class="small mb-0">
                        <li>Ensure file names are descriptive</li>
                        <li>Materials are reviewed by admin</li>
                        <li>Non-downloadable = View-only mode</li>
                        <li>Published materials notify students</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
// Display file information when selected
document.getElementById('fileInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const fileInfo = document.getElementById('fileInfo');

    if (file) {
        const sizeInMB = (file.size / 1024 / 1024).toFixed(2);
        fileInfo.innerHTML = `
            <div class="alert alert-success mb-0">
                <i class="fas fa-file me-2"></i>
                <strong>${file.name}</strong><br>
                <small>Size: ${sizeInMB} MB | Type: ${file.type || 'Unknown'}</small>
            </div>
        `;

        if (sizeInMB > 10) {
            fileInfo.innerHTML = `
                <div class="alert alert-danger mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    File size exceeds 10MB limit. Please compress or choose a smaller file.
                </div>
            `;
        }
    }
});
</script>
@endpush
