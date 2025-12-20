@extends('layouts.app')

@section('title', 'Role Details')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Role Details: {{ $role->name }}</h1>
            <p class="text-muted">View role information and assigned permissions</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.roles.edit', $role->id) }}" class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit Role
            </a>
            <a href="{{ route('admin.roles.permissions', $role->id) }}" class="btn btn-warning">
                <i class="fas fa-shield-alt"></i> Manage Permissions
            </a>
            <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Roles
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Role Information -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Role Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tbody>
                            <tr>
                                <th style="width: 30%">Role Name:</th>
                                <td>
                                    <code>{{ $role->name }}</code>
                                </td>
                            </tr>
                            <tr>
                                <th>Type:</th>
                                <td>
                                    @if($isSystemRole)
                                        <span class="badge bg-primary">System Role</span>
                                    @else
                                        <span class="badge bg-success">Custom Role</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Description:</th>
                                <td>
                                    @if($isSystemRole)
                                        @switch($role->name)
                                            @case('super-admin')
                                                Full system access and administration
                                                @break
                                            @case('admin')
                                                Operational management and administration
                                                @break
                                            @case('staff')
                                                Front desk and attendance management
                                                @break
                                            @case('teacher')
                                                Teaching and material management
                                                @break
                                            @case('parent')
                                                View student progress and payments
                                                @break
                                            @case('student')
                                                Access materials and view schedules
                                                @break
                                            @default
                                                System role
                                        @endswitch
                                    @else
                                        <span class="text-muted">Custom role</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Created:</th>
                                <td>{{ $role->created_at->format('M d, Y H:i A') }}</td>
                            </tr>
                            <tr>
                                <th>Last Updated:</th>
                                <td>{{ $role->updated_at->format('M d, Y H:i A') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Assigned Permissions -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Assigned Permissions ({{ $role->permissions->count() }})</h5>
                    <a href="{{ route('admin.roles.permissions', $role->id) }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-edit"></i> Manage Permissions
                    </a>
                </div>
                <div class="card-body">
                    @if($role->permissions->count() > 0)
                        @php
                            // Group permissions by module
                            $groupedPermissions = $role->permissions->groupBy(function ($permission) {
                                $parts = explode('-', $permission->name);
                                return $parts[0] ?? 'other';
                            });
                        @endphp

                        @foreach($groupedPermissions as $module => $permissions)
                        <div class="mb-4">
                            <h6 class="text-uppercase text-muted mb-3">
                                <i class="fas fa-folder"></i> {{ ucfirst($module) }} Module
                            </h6>
                            <div class="row">
                                @foreach($permissions as $permission)
                                <div class="col-md-6 mb-2">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-check-circle text-success me-2"></i>
                                        <code class="small">{{ $permission->name }}</code>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        <hr>
                        @endforeach
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-shield-alt fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No permissions assigned to this role yet.</p>
                            <a href="{{ route('admin.roles.permissions', $role->id) }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Assign Permissions
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Statistics Card -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-chart-bar"></i> Statistics</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <small class="text-muted">Total Permissions</small>
                            <div class="h3 mb-0">{{ $role->permissions->count() }}</div>
                        </div>
                        <i class="fas fa-shield-alt fa-2x text-primary"></i>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted">Users with Role</small>
                            <div class="h3 mb-0">{{ $usersCount }}</div>
                        </div>
                        <i class="fas fa-users fa-2x text-info"></i>
                    </div>
                </div>
            </div>

            <!-- Actions Card -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-cog"></i> Actions</h6>
                </div>
                <div class="card-body">
                    <a href="{{ route('admin.roles.edit', $role->id) }}" class="btn btn-primary w-100 mb-2">
                        <i class="fas fa-edit"></i> Edit Role
                    </a>
                    <a href="{{ route('admin.roles.permissions', $role->id) }}" class="btn btn-warning w-100 mb-2">
                        <i class="fas fa-shield-alt"></i> Manage Permissions
                    </a>
                    @if(!$isSystemRole)
                    <hr>
                    <button type="button" class="btn btn-danger w-100" onclick="confirmDelete()">
                        <i class="fas fa-trash"></i> Delete Role
                    </button>
                    <form id="delete-form" action="{{ route('admin.roles.destroy', $role->id) }}" method="POST" style="display: none;">
                        @csrf
                        @method('DELETE')
                    </form>
                    @endif
                </div>
            </div>

            <!-- Info Card -->
            <div class="card bg-light">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-info-circle"></i> Information</h6>
                </div>
                <div class="card-body">
                    @if($isSystemRole)
                    <p class="small mb-0">
                        This is a <strong>system role</strong> that is essential for the application.
                        It cannot be deleted but its permissions can be modified.
                    </p>
                    @else
                    <p class="small mb-0">
                        This is a <strong>custom role</strong>. You can modify or delete it as needed.
                        Deleting this role will remove it from all assigned users.
                    </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@if(!$isSystemRole)
@push('scripts')
<script>
function confirmDelete() {
    if (confirm('Are you sure you want to delete this role? This action cannot be undone and will unassign this role from {{ $usersCount }} user(s).')) {
        document.getElementById('delete-form').submit();
    }
}
</script>
@endpush
@endif
@endsection
