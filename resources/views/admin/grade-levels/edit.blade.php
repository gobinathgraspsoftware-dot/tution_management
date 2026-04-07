@extends('layouts.app')

@section('title', 'Edit Grade Level')
@section('page-title', 'Edit Grade Level')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="fas fa-edit me-2 text-warning"></i>Edit Grade Level</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.grade-levels.index') }}">Grade Levels</a>
                    </li>
                    <li class="breadcrumb-item active">Edit: {{ $gradeLevel->name }}</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('admin.grade-levels.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to List
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-layer-group me-1"></i> Edit Grade Level
                <span class="badge bg-secondary ms-2"># {{ $gradeLevel->id }}</span>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.grade-levels.update', $gradeLevel) }}"
                      method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="name" class="form-label fw-semibold">
                            Grade Level Name <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               id="name"
                               name="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $gradeLevel->name) }}"
                               placeholder="e.g. Standard 1, Form 3, Pre-University"
                               autofocus
                               maxlength="100">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text text-muted">
                            Must be unique. Maximum 100 characters.
                        </div>
                    </div>

                    {{-- Timestamps (read-only info) --}}
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label text-muted small">Created At</label>
                            <p class="form-control-plaintext small text-muted">
                                {{ $gradeLevel->created_at->format('d M Y, h:i A') }}
                            </p>
                        </div>
                        <div class="col-6">
                            <label class="form-label text-muted small">Last Updated</label>
                            <p class="form-control-plaintext small text-muted">
                                {{ $gradeLevel->updated_at->format('d M Y, h:i A') }}
                            </p>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Update Grade Level
                        </button>
                        <a href="{{ route('admin.grade-levels.index') }}" class="btn btn-outline-secondary">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Warning --}}
        <div class="card mt-3 border-warning">
            <div class="card-body py-2 px-3">
                <small class="text-warning">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    <strong>Caution:</strong> Renaming a grade level will affect all students and subjects referencing it.
                </small>
            </div>
        </div>
    </div>
</div>
@endsection
