@extends('layouts.app')

@section('title', 'Edit Subject')
@section('page-title', 'Edit Subject')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="fas fa-edit me-2"></i> Edit Subject</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.subjects.index') }}">Subjects</a></li>
                <li class="breadcrumb-item active">Edit {{ $subject->name }}</li>
            </ol>
        </nav>
    </div>
    <div>
        <a href="{{ route('admin.subjects.show', $subject) }}" class="btn btn-outline-info me-2">
            <i class="fas fa-eye me-1"></i> View
        </a>
        <a href="{{ route('admin.subjects.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to List
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-book me-2"></i> Subject Information</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.subjects.update', $subject) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                <!-- Subject Name -->
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="name">Subject Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                           id="name" name="name" value="{{ old('name', $subject->name) }}"
                           placeholder="e.g., Mathematics" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Subject Code -->
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="code">Subject Code <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('code') is-invalid @enderror"
                           id="code" name="code" value="{{ old('code', $subject->code) }}"
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
                              placeholder="Brief description of the subject...">{{ old('description', $subject->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Grade Levels -->
                <div class="col-md-8 mb-3">
                    <label class="form-label">Grade Levels</label>
                    <div class="row">
                        @php
                            $selectedGrades = old('grade_levels', $subject->grade_levels ?? []);
                        @endphp
                        @foreach($gradeLevels as $key => $label)
                            <div class="col-md-4 col-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox"
                                           name="grade_levels[]" value="{{ $key }}"
                                           id="grade_{{ Str::slug($key) }}"
                                           {{ in_array($key, $selectedGrades) ? 'checked' : '' }}>
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
                        <option value="active" {{ old('status', $subject->status) == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $subject->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Usage Information -->
            <div class="alert alert-info mb-4">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Usage Information:</strong>
                This subject is linked to <strong>{{ $subject->packages()->count() }}</strong> package(s)
                and <strong>{{ $subject->classes()->count() }}</strong> class(es).
            </div>

            <hr>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('admin.subjects.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times me-1"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Update Subject
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
