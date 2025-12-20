@extends('layouts.app')

@section('title', 'Create New Role')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Create New Role</h1>
            <p class="text-muted">Add a new custom role to the system</p>
        </div>
        <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Roles
        </a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Role Information</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.roles.store') }}" method="POST">
                        @csrf

                        <!-- Role Name -->
                        <div class="mb-3">
                            <label for="name" class="form-label">
                                Role Name <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control @error('name') is-invalid @enderror"
                                   id="name"
                                   name="name"
                                   value="{{ old('name') }}"
                                   placeholder="e.g., custom-manager"
                                   required>
                            <small class="form-text text-muted">
                                Use lowercase letters, numbers, and hyphens only. Example: custom-manager, sales-rep
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
                                   placeholder="e.g., Custom Manager">
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
                                      placeholder="Describe what this role can do...">{{ old('description') }}</textarea>
                            <small class="form-text text-muted">
                                Optional: Provide a brief description of this role's responsibilities
                            </small>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Create Role
                            </button>
                            <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">
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
                    <h6 class="mb-0"><i class="fas fa-info-circle"></i> Naming Guidelines</h6>
                </div>
                <div class="card-body">
                    <h6>Role Name Format:</h6>
                    <ul class="small mb-3">
                        <li>Use lowercase letters only</li>
                        <li>Use hyphens (-) to separate words</li>
                        <li>No spaces or special characters</li>
                        <li>Must be unique</li>
                    </ul>

                    <h6>Examples:</h6>
                    <ul class="small mb-3">
                        <li><code>custom-manager</code></li>
                        <li><code>branch-supervisor</code></li>
                        <li><code>content-editor</code></li>
                        <li><code>finance-officer</code></li>
                    </ul>

                    <div class="alert alert-warning small mb-0">
                        <strong>Note:</strong> System roles (super-admin, admin, staff, teacher, parent, student) cannot be used as custom role names.
                    </div>
                </div>
            </div>

            <!-- Next Steps Card -->
            <div class="card bg-info text-white mt-3">
                <div class="card-body">
                    <h6><i class="fas fa-lightbulb"></i> Next Steps</h6>
                    <p class="small mb-0">
                        After creating the role, you can assign permissions to it from the role management page.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
