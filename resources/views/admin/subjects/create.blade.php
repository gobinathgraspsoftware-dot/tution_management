@extends('layouts.app')

@section('title', 'Add Subject')
@section('page-title', 'Add Subject')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="fas fa-plus me-2"></i> Add New Subject</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.subjects.index') }}">Subjects</a></li>
                <li class="breadcrumb-item active">Add Subject</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('admin.subjects.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back to List
    </a>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-book me-2"></i> Subject Information</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.subjects.store') }}" method="POST">
            @csrf

            <div class="row">
                <!-- Subject Name -->
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="name">Subject Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                           id="name" name="name" value="{{ old('name') }}"
                           placeholder="e.g., Mathematics" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Subject Code -->
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="code">Subject Code <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('code') is-invalid @enderror"
                           id="code" name="code" value="{{ old('code') }}"
                           placeholder="e.g., MATH" required maxlength="20">
                    @error('code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Unique identifier for this subject (max 20 characters)</small>
                </div>

                <!-- Description -->
                <div class="col-12 mb-3">
                    <label class="form-label" for="description">Description</label>
                    <textarea class="form-control @error('description') is-invalid @enderror"
                              id="description" name="description" rows="3"
                              placeholder="Brief description of the subject...">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Grade Levels -->
                <div class="col-md-8 mb-3">
                    <label class="form-label">Grade Levels</label>
                    <div class="row">
                        @foreach($gradeLevels as $key => $label)
                            <div class="col-md-4 col-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox"
                                           name="grade_levels[]" value="{{ $key }}"
                                           id="grade_{{ Str::slug($key) }}"
                                           {{ in_array($key, old('grade_levels', [])) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="grade_{{ Str::slug($key) }}">
                                        {{ $label }}
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @error('grade_levels')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Select applicable grade levels for this subject</small>
                </div>

                <!-- Status -->
                <div class="col-md-4 mb-3">
                    <label class="form-label" for="status">Status <span class="text-danger">*</span></label>
                    <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                        <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <hr>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('admin.subjects.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times me-1"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Create Subject
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Auto-generate code from name
document.getElementById('name').addEventListener('input', function() {
    const codeField = document.getElementById('code');
    if (!codeField.value || codeField.dataset.autoGenerated === 'true') {
        const code = this.value
            .toUpperCase()
            .replace(/[^A-Z0-9]/g, '')
            .substring(0, 10);
        codeField.value = code;
        codeField.dataset.autoGenerated = 'true';
    }
});

document.getElementById('code').addEventListener('input', function() {
    this.dataset.autoGenerated = 'false';
});
</script>
@endsection
