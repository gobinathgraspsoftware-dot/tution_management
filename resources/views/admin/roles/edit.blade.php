@extends('layouts.app')

@section('title', 'Edit Role')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Edit Role: {{ $role->name }}</h1>
            <p class="text-muted">Update role information</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.roles.show', $role->id) }}" class="btn btn-info">
                <i class="fas fa-eye"></i> View Details
            </a>
            <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Roles
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Role Information</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.roles.update', $role->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        @if($isSystemRole)
                        <!-- System Role Warning -->
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>System Role:</strong> This is a system role. The role name cannot be changed.
                        </div>
                        @endif

                        <!-- Role Name -->
                        <div class="mb-3">
                            <label for="name" class="form-label">
                                Role Name <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control @error('name') is-invalid @enderror"
                                   id="name"
                                   name="name"
                                   value="{{ old('name', $role->name) }}"
                                   placeholder="e.g., custom-manager"
                                   {{ $isSystemRole ? 'readonly' : '' }}
                                   required>
                            <small class="form-text text-muted">
                                @if($isSystemRole)
                                    System role names cannot be modified
                                @else
                                    Use lowercase letters, numbers, and hyphens only
                                @endif
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
                                <i class="fas fa-save"></i> Update Role
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
            <!-- Role Stats Card -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-chart-bar"></i> Role Statistics</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted">Type:</small>
                        <div>
                            @if($isSystemRole)
                                <span class="badge bg-primary">System Role</span>
                            @else
                                <span class="badge bg-success">Custom Role</span>
                            @endif
                        </div>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Permissions Assigned:</small>
                        <div class="h4 mb-0">{{ $role->permissions()->count() }}</div>
                    </div>
                    <a href="{{ route('admin.roles.permissions', $role->id) }}" class="btn btn-sm btn-outline-primary w-100">
                        <i class="fas fa-shield-alt"></i> Manage Permissions
                    </a>
                </div>
            </div>

            @if($isSystemRole)
            <!-- System Role Info -->
            <div class="card bg-light mt-3">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-info-circle"></i> System Role</h6>
                </div>
                <div class="card-body">
                    <p class="small mb-0">
                        This is a system-defined role that is essential for the application.
                        While you can update its display name and description, the role name itself cannot be changed or deleted.
                    </p>
                </div>
            </div>
            @else
            <!-- Custom Role Info -->
            <div class="card bg-light mt-3">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-info-circle"></i> Custom Role</h6>
                </div>
                <div class="card-body">
                    <p class="small">
                        This is a custom role that you have created. You can modify all its properties and delete it if it's no longer needed.
                    </p>
                    <hr>
                    <h6 class="small"><strong>Delete Role</strong></h6>
                    <p class="small text-muted">
                        Removing this role will unassign it from all users. This action cannot be undone.
                    </p>
                    <button type="button" class="btn btn-sm btn-danger w-100" onclick="confirmDelete()">
                        <i class="fas fa-trash"></i> Delete Role
                    </button>
                    <form id="delete-form" action="{{ route('admin.roles.destroy', $role->id) }}" method="POST" style="display: none;">
                        @csrf
                        @method('DELETE')
                    </form>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@if(!$isSystemRole)
@push('scripts')
<script>
function confirmDelete() {
    if (confirm('Are you sure you want to delete this role? This action cannot be undone and will unassign this role from all users.')) {
        document.getElementById('delete-form').submit();
    }
}
</script>
@endpush
@endif
@endsection
