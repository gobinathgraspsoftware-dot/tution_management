@extends('layouts.app')

@section('title', 'Upload New Material')
@section('page-title', 'Upload New Material')

@section('content')
<div class="page-header">
    <h1><i class="fas fa-upload me-2"></i> Upload New Material</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.materials.index') }}">Materials</a></li>
            <li class="breadcrumb-item active">Upload New</li>
        </ol>
    </nav>
</div>

<form action="{{ route('admin.materials.store') }}" method="POST" enctype="multipart/form-data" id="materialForm">
    @csrf

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-info-circle me-2"></i> Material Information
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                               value="{{ old('title') }}" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Class <span class="text-danger">*</span></label>
                            <select name="class_id" id="classSelect" class="form-select @error('class_id') is-invalid @enderror" required>
                                <option value="">Select Class</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}" data-subject="{{ $class->subject_id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>
                                        {{ $class->name }} ({{ $class->subject->name }})
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
                                    <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>
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
                                    <option value="{{ $teacher->id }}" {{ old('teacher_id') == $teacher->id ? 'selected' : '' }}>
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
                                <option value="notes" {{ old('type') == 'notes' ? 'selected' : '' }}>Notes</option>
                                <option value="presentation" {{ old('type') == 'presentation' ? 'selected' : '' }}>Presentation</option>
                                <option value="video" {{ old('type') == 'video' ? 'selected' : '' }}>Video</option>
                                <option value="document" {{ old('type') == 'document' ? 'selected' : '' }}>Document</option>
                                <option value="other" {{ old('type') == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Upload File <span class="text-danger">*</span></label>
                        <input type="file" name="file" id="fileInput" class="form-control @error('file') is-invalid @enderror" required>
                        <small class="form-text text-muted">
                            Accepted: PDF, DOC, DOCX, PPT, PPTX, MP4, AVI, MOV. Max size: 50MB
                        </small>
                        @error('file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div id="filePreview" class="mt-2"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" rows="4" class="form-control @error('description') is-invalid @enderror" placeholder="Enter material description...">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-cog me-2"></i> Settings
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Access Type <span class="text-danger">*</span></label>
                        <select name="access_type" class="form-select @error('access_type') is-invalid @enderror" required>
                            <option value="view_only" {{ old('access_type') == 'view_only' ? 'selected' : '' }}>
                                View Only (Non-downloadable)
                            </option>
                            <option value="downloadable" {{ old('access_type') == 'downloadable' ? 'selected' : '' }}>
                                Downloadable
                            </option>
                        </select>
                        @error('access_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            View Only: Students can only view the material online
                        </small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Publish Date</label>
                        <input type="date" name="publish_date" class="form-control @error('publish_date') is-invalid @enderror" value="{{ old('publish_date', date('Y-m-d')) }}">
                        @error('publish_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="published" {{ old('status', 'published') == 'published' ? 'selected' : '' }}>Published</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            Draft materials are not visible to students
                        </small>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary w-100 mb-2">
                        <i class="fas fa-upload me-1"></i> Upload Material
                    </button>
                    <a href="{{ route('admin.materials.index') }}" class="btn btn-outline-secondary w-100">
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
// Auto-select subject when class is selected
document.getElementById('classSelect').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const subjectId = selectedOption.getAttribute('data-subject');

    if (subjectId) {
        document.getElementById('subjectSelect').value = subjectId;
    }
});

// File preview
document.getElementById('fileInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('filePreview');

    if (file) {
        const fileSize = (file.size / 1024 / 1024).toFixed(2); // Size in MB
        preview.innerHTML = `
            <div class="alert alert-info">
                <strong><i class="fas fa-file me-2"></i>${file.name}</strong><br>
                <small>Size: ${fileSize} MB</small>
            </div>
        `;
    } else {
        preview.innerHTML = '';
    }
});
</script>
@endpush
