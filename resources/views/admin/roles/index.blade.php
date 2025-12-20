@extends('layouts.app')

@section('title', 'Roles Management')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Roles Management</h1>
            <p class="text-muted">Manage system and custom roles</p>
        </div>
        <a href="{{ route('admin.roles.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Create New Role
        </a>
    </div>

    <!-- Filters Card -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.roles.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Search by role name..."
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Type</label>
                    <select name="type" class="form-select">
                        <option value="">All Types</option>
                        <option value="system" {{ request('type') === 'system' ? 'selected' : '' }}>System Roles</option>
                        <option value="custom" {{ request('type') === 'custom' ? 'selected' : '' }}>Custom Roles</option>
                    </select>
                </div>
                <div class="col-md-5 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Search
                    </button>
                    <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">
                        <i class="fas fa-redo"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Roles Table Card -->
    <div class="card">
        <div class="card-body">
            @if($roles->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 5%">#</th>
                            <th style="width: 20%">Role Name</th>
                            <th style="width: 30%">Description</th>
                            <th style="width: 15%" class="text-center">Permissions</th>
                            <th style="width: 10%" class="text-center">Type</th>
                            <th style="width: 20%" class="text-center">Actions</th>
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
                            <td>{{ $roles->firstItem() + $index }}</td>
                            <td>
                                <strong class="text-primary">{{ $role->name }}</strong>
                            </td>
                            <td>
                                @if($isSystemRole)
                                    <span class="text-muted">
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
                                    </span>
                                @else
                                    <span class="text-muted">Custom role</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge bg-info">{{ $role->permissions_count }}</span>
                            </td>
                            <td class="text-center">
                                @if($isSystemRole)
                                    <span class="badge bg-primary">System</span>
                                @else
                                    <span class="badge bg-success">Custom</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    <!-- View Button -->
                                    <a href="{{ route('admin.roles.show', $role->id) }}"
                                       class="btn btn-sm btn-outline-info"
                                       title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>

                                    <!-- Edit Button -->
                                    <a href="{{ route('admin.roles.edit', $role->id) }}"
                                       class="btn btn-sm btn-outline-primary"
                                       title="Edit Role">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <!-- Permissions Button -->
                                    <a href="{{ route('admin.roles.permissions', $role->id) }}"
                                       class="btn btn-sm btn-outline-warning"
                                       title="Manage Permissions">
                                        <i class="fas fa-shield-alt"></i>
                                    </a>

                                    <!-- Delete Button (only for custom roles) -->
                                    @if(!$isSystemRole)
                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger"
                                            onclick="confirmDelete({{ $role->id }})"
                                            title="Delete Role">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endif
                                </div>

                                <!-- Delete Form (hidden) -->
                                @if(!$isSystemRole)
                                <form id="delete-form-{{ $role->id }}"
                                      action="{{ route('admin.roles.destroy', $role->id) }}"
                                      method="POST"
                                      style="display: none;">
                                    @csrf
                                    @method('DELETE')
                                </form>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $roles->links() }}
            </div>
            @else
            <div class="text-center py-5">
                <i class="fas fa-users-cog fa-4x text-muted mb-3"></i>
                <p class="text-muted">No roles found</p>
                <a href="{{ route('admin.roles.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create First Role
                </a>
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
function confirmDelete(roleId) {
    if (confirm('Are you sure you want to delete this role? This action cannot be undone.')) {
        document.getElementById('delete-form-' + roleId).submit();
    }
}
</script>
@endpush
@endsection
