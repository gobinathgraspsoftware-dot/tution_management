@extends('layouts.app')

@section('title', 'Edit Permission')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Edit Permission: {{ $permission->name }}</h1>
            <p class="text-muted">Update permission information</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.permissions.show', $permission->id) }}" class="btn btn-info">
                <i class="fas fa-eye"></i> View Details
            </a>
            <a href="{{ route('admin.permissions.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Permissions
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Permission Information</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.permissions.update', $permission->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Permission Name -->
                        <div class="mb-3">
                            <label for="name" class="form-label">
                                Permission Name <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control @error('name') is-invalid @enderror"
                                   id="name"
                                   name="name"
                                   value="{{ old('name', $permission->name) }}"
                                   placeholder="e.g., view-custom-reports"
                                   required>
                            <small class="form-text text-muted">
                                Format: <code>action-module-target</code> (e.g., view-reports, create-users)
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
                                <i class="fas fa-save"></i> Update Permission
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
            <!-- Permission Stats Card -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-chart-bar"></i> Permission Usage</h6>
                </div>
                <div class="card-body">
                    @php
                        $parts = explode('-', $permission->name);
                        $module = $parts[0] ?? 'other';
                    @endphp
                    <div class="mb-3">
                        <small class="text-muted">Module:</small>
                        <div>
                            <span class="badge bg-primary">{{ ucfirst($module) }}</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Created:</small>
                        <div class="small">{{ $permission->created_at->format('M d, Y') }}</div>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Last Updated:</small>
                        <div class="small">{{ $permission->updated_at->format('M d, Y') }}</div>
                    </div>
                </div>
            </div>

            <!-- Danger Zone -->
            <div class="card bg-light mt-3">
                <div class="card-header bg-danger text-white">
                    <h6 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Danger Zone</h6>
                </div>
                <div class="card-body">
                    <h6 class="small"><strong>Delete Permission</strong></h6>
                    <p class="small text-muted">
                        Deleting this permission will remove it from all roles and users. This action cannot be undone.
                    </p>
                    <button type="button" class="btn btn-sm btn-danger w-100" onclick="confirmDelete()">
                        <i class="fas fa-trash"></i> Delete Permission
                    </button>
                    <form id="delete-form" action="{{ route('admin.permissions.destroy', $permission->id) }}" method="POST" style="display: none;">
                        @csrf
                        @method('DELETE')
                    </form>
                </div>
            </div>

            <!-- Help Card -->
            <div class="card bg-info text-white mt-3">
                <div class="card-body">
                    <h6><i class="fas fa-lightbulb"></i> Tip</h6>
                    <p class="small mb-0">
                        Changing the permission name may break existing role assignments. Make sure to test after updating.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function confirmDelete() {
    if (confirm('Are you sure you want to delete this permission? This action cannot be undone and will remove this permission from all roles.')) {
        document.getElementById('delete-form').submit();
    }
}
</script>
@endpush
@endsection
