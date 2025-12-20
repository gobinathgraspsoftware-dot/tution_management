@extends('layouts.app')

@section('title', 'Permissions Management')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Permissions Management</h1>
            <p class="text-muted">Manage system permissions</p>
        </div>
        <a href="{{ route('admin.permissions.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Create New Permission
        </a>
    </div>

    <!-- Filters Card -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.permissions.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Search by permission name..."
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Module</label>
                    <select name="module" class="form-select">
                        <option value="">All Modules</option>
                        @if(isset($modules))
                            @foreach($modules as $module)
                                <option value="{{ $module }}" {{ request('module') === $module ? 'selected' : '' }}>
                                    {{ ucfirst($module) }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Group by Module</label>
                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" name="group_by_module" value="1"
                               id="groupByModule" {{ request('group_by_module') === '1' ? 'checked' : '' }}
                               onchange="this.form.submit()">
                        <label class="form-check-label" for="groupByModule">
                            Group
                        </label>
                    </div>
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Search
                    </button>
                    <a href="{{ route('admin.permissions.index') }}" class="btn btn-secondary">
                        <i class="fas fa-redo"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Permissions Display -->
    <div class="card">
        <div class="card-body">
            @if(isset($groupedPermissions) && $groupedPermissions && $groupedPermissions->count() > 0)
                <!-- Grouped View -->
                @foreach($groupedPermissions as $module => $modulePermissions)
                <div class="mb-4">
                    <h5 class="text-uppercase text-primary mb-3">
                        <i class="fas fa-folder"></i> {{ ucfirst($module) }} Module
                        <span class="badge bg-secondary ms-2">{{ $modulePermissions->count() }}</span>
                    </h5>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 5%">#</th>
                                    <th style="width: 40%">Permission Name</th>
                                    <th style="width: 40%">Description</th>
                                    <th style="width: 15%" class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($modulePermissions as $index => $permission)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td><code>{{ $permission->name }}</code></td>
                                    <td>
                                        <span class="text-muted small">
                                            {{ ucwords(str_replace('-', ' ', $permission->name)) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.permissions.show', $permission->id) }}"
                                               class="btn btn-sm btn-outline-info"
                                               title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.permissions.edit', $permission->id) }}"
                                               class="btn btn-sm btn-outline-primary"
                                               title="Edit Permission">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-danger"
                                                    onclick="confirmDelete({{ $permission->id }})"
                                                    title="Delete Permission">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                        <form id="delete-form-{{ $permission->id }}"
                                              action="{{ route('admin.permissions.destroy', $permission->id) }}"
                                              method="POST"
                                              style="display: none;">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if(!$loop->last)
                        <hr>
                    @endif
                </div>
                @endforeach
            @elseif(isset($permissions) && $permissions->count() > 0)
                <!-- List View -->
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 5%">#</th>
                                <th style="width: 15%">Module</th>
                                <th style="width: 35%">Permission Name</th>
                                <th style="width: 30%">Description</th>
                                <th style="width: 15%" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($permissions as $index => $permission)
                            @php
                                $parts = explode('-', $permission->name);
                                $module = $parts[0] ?? 'other';
                            @endphp
                            <tr>
                                <td>{{ $permissions->firstItem() + $index }}</td>
                                <td>
                                    <span class="badge bg-primary">{{ ucfirst($module) }}</span>
                                </td>
                                <td><code>{{ $permission->name }}</code></td>
                                <td>
                                    <span class="text-muted small">
                                        {{ ucwords(str_replace('-', ' ', $permission->name)) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.permissions.show', $permission->id) }}"
                                           class="btn btn-sm btn-outline-info"
                                           title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.permissions.edit', $permission->id) }}"
                                           class="btn btn-sm btn-outline-primary"
                                           title="Edit Permission">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button"
                                                class="btn btn-sm btn-outline-danger"
                                                onclick="confirmDelete({{ $permission->id }})"
                                                title="Delete Permission">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                    <form id="delete-form-{{ $permission->id }}"
                                          action="{{ route('admin.permissions.destroy', $permission->id) }}"
                                          method="POST"
                                          style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-4">
                    {{ $permissions->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-shield-alt fa-4x text-muted mb-3"></i>
                    <p class="text-muted">No permissions found</p>
                    <a href="{{ route('admin.permissions.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create First Permission
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
function confirmDelete(permissionId) {
    if (confirm('Are you sure you want to delete this permission? This action cannot be undone and will remove this permission from all roles.')) {
        document.getElementById('delete-form-' + permissionId).submit();
    }
}
</script>
@endpush
@endsection
