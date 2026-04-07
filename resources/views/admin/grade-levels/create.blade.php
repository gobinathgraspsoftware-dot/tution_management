@extends('layouts.app')

@section('title', 'Add Grade Level')
@section('page-title', 'Add Grade Level')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="fas fa-plus-circle me-2 text-warning"></i>Add Grade Level</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.grade-levels.index') }}">Grade Levels</a>
                    </li>
                    <li class="breadcrumb-item active">Add New</li>
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
                <i class="fas fa-layer-group me-1"></i> Grade Level Details
            </div>
            <div class="card-body">
                <form action="{{ route('admin.grade-levels.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="name" class="form-label fw-semibold">
                            Grade Level Name <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               id="name"
                               name="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}"
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

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Save Grade Level
                        </button>
                        <a href="{{ route('admin.grade-levels.index') }}" class="btn btn-outline-secondary">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Info Box --}}
        <div class="card mt-3 border-warning">
            <div class="card-body py-2 px-3">
                <small class="text-muted">
                    <i class="fas fa-info-circle text-warning me-1"></i>
                    Grade levels are used across Student profiles and Subject assignments.
                    Once created, a grade level cannot be deleted to preserve data integrity.
                </small>
            </div>
        </div>
    </div>
</div>
@endsection
