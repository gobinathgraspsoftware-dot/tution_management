@extends('layouts.app')

@section('title', 'Upload Document')
@section('page-title', 'Upload Document')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1"><i class="fas fa-upload me-2"></i> Upload Document</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('teacher.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('teacher.documents.index') }}">My Documents</a></li>
                    <li class="breadcrumb-item active">Upload</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('teacher.documents.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Documents
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-file-upload me-2"></i> Document Details</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('teacher.documents.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="document_type" class="form-label">Document Type <span class="text-danger">*</span></label>
                                <select name="document_type" id="document_type" class="form-select @error('document_type') is-invalid @enderror" required>
                                    <option value="">-- Select Document Type --</option>
                                    @foreach($documentTypes as $key => $name)
                                        <option value="{{ $key }}" {{ old('document_type') == $key ? 'selected' : '' }}>
                                            {{ $name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('document_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="title" class="form-label">Document Title <span class="text-danger">*</span></label>
                                <input type="text" name="title" id="title" 
                                       class="form-control @error('title') is-invalid @enderror" 
                                       value="{{ old('title') }}" 
                                       placeholder="e.g., MyKad, Degree Certificate"
                                       required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="document" class="form-label">Upload File <span class="text-danger">*</span></label>
                            <input type="file" name="document" id="document" 
                                   class="form-control @error('document') is-invalid @enderror" 
                                   accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                                   required>
                            <div class="form-text">
                                Accepted formats: PDF, JPG, JPEG, PNG, DOC, DOCX. Maximum size: 10MB.
                            </div>
                            @error('document')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- File Preview -->
                        <div id="filePreview" class="mb-3 d-none">
                            <div class="alert alert-info">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-file fa-2x me-3" id="fileIcon"></i>
                                    <div>
                                        <strong id="fileName">filename.pdf</strong>
                                        <br><small class="text-muted" id="fileSize">0 KB</small>
                                    </div>
                                </div>
                            </div>
                            <div id="imagePreviewContainer" class="d-none">
                                <img id="imagePreview" src="" alt="Preview" class="img-thumbnail" style="max-height: 200px;">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="expiry_date" class="form-label">Expiry Date</label>
                            <input type="date" name="expiry_date" id="expiry_date" 
                                   class="form-control @error('expiry_date') is-invalid @enderror" 
                                   value="{{ old('expiry_date') }}"
                                   min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                            <div class="form-text">
                                Leave blank if the document does not expire.
                            </div>
                            @error('expiry_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea name="description" id="description" rows="3" 
                                      class="form-control @error('description') is-invalid @enderror" 
                                      placeholder="Add any notes or description about this document (optional)">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('teacher.documents.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-upload me-1"></i> Upload Document
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Upload Guidelines -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i> Upload Guidelines</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            File size must be under 10MB
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Ensure documents are clear and readable
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Use PDF format for multi-page documents
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Include all relevant pages
                        </li>
                        <li class="mb-0">
                            <i class="fas fa-check text-success me-2"></i>
                            Documents will be reviewed by admin
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Document Types Info -->
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="fas fa-folder me-2"></i> Document Types</h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @foreach($documentTypes as $key => $name)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>{{ $name }}</span>
                            <span class="badge bg-light text-dark">{{ $key }}</span>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('document').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('filePreview');
    const fileName = document.getElementById('fileName');
    const fileSize = document.getElementById('fileSize');
    const fileIcon = document.getElementById('fileIcon');
    const imagePreviewContainer = document.getElementById('imagePreviewContainer');
    const imagePreview = document.getElementById('imagePreview');

    if (file) {
        preview.classList.remove('d-none');
        fileName.textContent = file.name;
        fileSize.textContent = formatFileSize(file.size);

        // Set icon based on file type
        const ext = file.name.split('.').pop().toLowerCase();
        if (ext === 'pdf') {
            fileIcon.className = 'fas fa-file-pdf fa-2x me-3 text-danger';
        } else if (['jpg', 'jpeg', 'png'].includes(ext)) {
            fileIcon.className = 'fas fa-file-image fa-2x me-3 text-primary';
        } else if (['doc', 'docx'].includes(ext)) {
            fileIcon.className = 'fas fa-file-word fa-2x me-3 text-info';
        } else {
            fileIcon.className = 'fas fa-file fa-2x me-3';
        }

        // Show image preview for images
        if (['jpg', 'jpeg', 'png'].includes(ext)) {
            const reader = new FileReader();
            reader.onload = function(e) {
                imagePreview.src = e.target.result;
                imagePreviewContainer.classList.remove('d-none');
            }
            reader.readAsDataURL(file);
        } else {
            imagePreviewContainer.classList.add('d-none');
        }
    } else {
        preview.classList.add('d-none');
    }
});

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Auto-generate title based on document type
document.getElementById('document_type').addEventListener('change', function() {
    const titleInput = document.getElementById('title');
    if (!titleInput.value) {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value) {
            titleInput.value = selectedOption.text;
        }
    }
});
</script>
@endpush
