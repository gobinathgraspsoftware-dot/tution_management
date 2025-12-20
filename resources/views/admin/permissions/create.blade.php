@extends('layouts.app')

@section('title', 'Create New Permission')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Create New Permission</h1>
            <p class="text-muted">Add a new permission to the system</p>
        </div>
        <a href="{{ route('admin.permissions.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Permissions
        </a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Permission Information</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.permissions.store') }}" method="POST">
                        @csrf

                        <!-- Permission Name -->
                        <div class="mb-3">
                            <label for="name" class="form-label">
                                Permission Name <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control @error('name') is-invalid @enderror"
                                   id="name"
                                   name="name"
                                   value="{{ old('name') }}"
                                   placeholder="e.g., view-custom-reports"
                                   required>
                            <small class="form-text text-muted">
                                Format: <code>action-module-target</code> (e.g., view-reports, create-users, edit-settings)
                            </small>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Display Name -->
                        <div class="mb-3">
                            <label for="display_name" class="form-label">
                                Display Name
                            </label>
                            <input type="text"
                                   class="form-control @error('display_name') is-invalid @enderror"
                                   id="display_name"
                                   name="display_name"
                                   value="{{ old('display_name') }}"
                                   placeholder="e.g., View Custom Reports">
                            <small class="form-text text-muted">
                                Human-readable name for display purposes
                            </small>
                            @error('display_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label for="description" class="form-label">
                                Description
                            </label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description"
                                      name="description"
                                      rows="3"
                                      placeholder="Describe what this permission allows...">{{ old('description') }}</textarea>
                            <small class="form-text text-muted">
                                Optional: Provide a brief description of what this permission grants access to
                            </small>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Create Permission
                            </button>
                            <a href="{{ route('admin.permissions.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Help Card -->
            <div class="card bg-light">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-info-circle"></i> Naming Convention</h6>
                </div>
                <div class="card-body">
                    <h6>Permission Format:</h6>
                    <p class="small mb-3">
                        <code>action-module-target</code>
                    </p>

                    <h6>Common Actions:</h6>
                    <ul class="small mb-3">
                        <li><code>view</code> - Read access</li>
                        <li><code>create</code> - Create new items</li>
                        <li><code>edit</code> - Modify existing items</li>
                        <li><code>delete</code> - Remove items</li>
                        <li><code>manage</code> - Full control</li>
                    </ul>

                    <h6>Examples:</h6>
                    <ul class="small mb-3">
                        <li><code>view-reports</code></li>
                        <li><code>create-invoices</code></li>
                        <li><code>edit-students</code></li>
                        <li><code>delete-payments</code></li>
                        <li><code>manage-settings</code></li>
                    </ul>

                    <div class="alert alert-info small mb-0">
                        <strong>Tip:</strong> Group related permissions by starting with the same prefix (e.g., all student permissions start with "student-")
                    </div>
                </div>
            </div>

            <!-- Existing Modules -->
            @if($modules->count() > 0)
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-folder"></i> Existing Modules</h6>
                </div>
                <div class="card-body">
                    <p class="small text-muted mb-2">You can add permissions to these existing modules:</p>
                    <div class="d-flex flex-wrap gap-1">
                        @foreach($modules as $module)
                            <span class="badge bg-primary">{{ ucfirst($module) }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
