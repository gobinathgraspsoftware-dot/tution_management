@extends('layouts.app')

@section('title', 'Permission Details')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Permission Details: {{ $permission->name }}</h1>
            <p class="text-muted">View permission information and role assignments</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.permissions.edit', $permission->id) }}" class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit Permission
            </a>
            <a href="{{ route('admin.permissions.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Permissions
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Permission Information -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Permission Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tbody>
                            <tr>
                                <th style="width: 30%">Permission Name:</th>
                                <td><code>{{ $permission->name }}</code></td>
                            </tr>
                            <tr>
                                <th>Module:</th>
                                <td>
                                    @php
                                        $parts = explode('-', $permission->name);
                                        $module = $parts[0] ?? 'other';
                                    @endphp
                                    <span class="badge bg-primary">{{ ucfirst($module) }}</span>
                                </td>
                            </tr>
                            <tr>
                                <th>Display Name:</th>
                                <td>{{ ucwords(str_replace('-', ' ', $permission->name)) }}</td>
                            </tr>
                            <tr>
                                <th>Description:</th>
                                <td>
                                    <span class="text-muted">
                                        Permission to {{ str_replace('-', ' ', $permission->name) }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Created:</th>
                                <td>{{ $permission->created_at->format('M d, Y H:i A') }}</td>
                            </tr>
                            <tr>
                                <th>Last Updated:</th>
                                <td>{{ $permission->updated_at->format('M d, Y H:i A') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Assigned Roles -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        Assigned to Roles ({{ $roles->count() }})
                    </h5>
                </div>
                <div class="card-body">
                    @if($roles->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th style="width: 5%">#</th>
                                        <th style="width: 30%">Role Name</th>
                                        <th style="width: 15%" class="text-center">Type</th>
                                        <th style="width: 15%" class="text-center">Permissions</th>
                                        <th style="width: 35%" class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $systemRoles = ['super-admin', 'admin', 'staff', 'teacher', 'parent', 'student'];
                                    @endphp
                                    @foreach($roles as $index => $role)
                                    @php
                                        $isSystemRole = in_array($role->name, $systemRoles);
                                    @endphp
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <strong>{{ $role->name }}</strong>
                                        </td>
                                        <td class="text-center">
                                            @if($isSystemRole)
                                                <span class="badge bg-primary">System</span>
                                            @else
                                                <span class="badge bg-success">Custom</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-info">{{ $role->permissions->count() }}</span>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.roles.show', $role->id) }}"
                                                   class="btn btn-sm btn-outline-info"
                                                   title="View Role">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                                <a href="{{ route('admin.roles.permissions', $role->id) }}"
                                                   class="btn btn-sm btn-outline-warning"
                                                   title="Manage Permissions">
                                                    <i class="fas fa-shield-alt"></i> Manage
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-users-slash fa-3x text-muted mb-3"></i>
                            <p class="text-muted">This permission is not assigned to any roles yet.</p>
                            <a href="{{ route('admin.roles.index') }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-shield-alt"></i> Assign to Role
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
                            <small class="text-muted">Assigned to Roles</small>
                            <div class="h3 mb-0">{{ $roles->count() }}</div>
                        </div>
                        <i class="fas fa-users fa-2x text-primary"></i>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted">Total Users Affected</small>
                            <div class="h3 mb-0">{{ $usersCount }}</div>
                        </div>
                        <i class="fas fa-user fa-2x text-info"></i>
                    </div>
                </div>
            </div>

            <!-- Module Info Card -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-folder"></i> Module Information</h6>
                </div>
                <div class="card-body">
                    @php
                        $parts = explode('-', $permission->name);
                        $module = $parts[0] ?? 'other';
                        $action = $parts[1] ?? 'action';
                    @endphp
                    <div class="mb-3">
                        <small class="text-muted">Module:</small>
                        <div><strong>{{ ucfirst($module) }}</strong></div>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Action:</small>
                        <div><strong>{{ ucfirst($action) }}</strong></div>
                    </div>
                    <div>
                        <small class="text-muted">Full Permission:</small>
                        <div><code>{{ $permission->name }}</code></div>
                    </div>
                </div>
            </div>

            <!-- Actions Card -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-cog"></i> Actions</h6>
                </div>
                <div class="card-body">
                    <a href="{{ route('admin.permissions.edit', $permission->id) }}" class="btn btn-primary w-100 mb-2">
                        <i class="fas fa-edit"></i> Edit Permission
                    </a>
                    <a href="{{ route('admin.permissions.index') }}" class="btn btn-secondary w-100 mb-2">
                        <i class="fas fa-list"></i> All Permissions
                    </a>
                    <hr>
                    <button type="button" class="btn btn-danger w-100" onclick="confirmDelete()">
                        <i class="fas fa-trash"></i> Delete Permission
                    </button>
                    <form id="delete-form" action="{{ route('admin.permissions.destroy', $permission->id) }}" method="POST" style="display: none;">
                        @csrf
                        @method('DELETE')
                    </form>
                </div>
            </div>

            <!-- Info Card -->
            <div class="card bg-light">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-info-circle"></i> Information</h6>
                </div>
                <div class="card-body">
                    <p class="small mb-0">
                        This permission is currently assigned to {{ $roles->count() }} role(s) and affects approximately {{ $usersCount }} user(s).
                        Modifying or deleting this permission will impact all assigned roles and users.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function confirmDelete() {
    if (confirm('Are you sure you want to delete this permission? This action cannot be undone and will remove this permission from {{ $roles->count() }} role(s).')) {
        document.getElementById('delete-form').submit();
    }
}
</script>
@endpush
@endsection
