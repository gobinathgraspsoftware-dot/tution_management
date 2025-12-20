@extends('layouts.app')

@section('title', 'Manage Role Permissions')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Manage Permissions: {{ $role->name }}</h1>
            <p class="text-muted">Assign or remove permissions for this role</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.roles.show', $role->id) }}" class="btn btn-info">
                <i class="fas fa-eye"></i> View Role
            </a>
            <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Roles
            </a>
        </div>
    </div>

    @if($isSystemRole)
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle"></i>
        <strong>System Role:</strong> Be careful when modifying permissions for system roles as it may affect core functionality.
    </div>
    @endif

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col-md-4">
                            <h5 class="mb-0">Permissions ({{ $groupedPermissions->sum(fn($group) => $group->count()) }} total)</h5>
                        </div>
                        <div class="col-md-8">
                            <div class="d-flex gap-2 justify-content-end">
                                <input type="text" id="searchPermissions" class="form-control" placeholder="Search permissions..." style="max-width: 300px;">
                                <select id="moduleFilter" class="form-select" style="max-width: 200px;">
                                    <option value="">All Modules</option>
                                    @foreach($groupedPermissions->keys() as $module)
                                        <option value="{{ $module }}">{{ ucfirst($module) }}</option>
                                    @endforeach
                                </select>
                                <div class="form-check form-switch d-flex align-items-center ms-2">
                                    <input class="form-check-input me-2" type="checkbox" id="groupByModule" checked>
                                    <label class="form-check-label small" for="groupByModule">Group by Module</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.roles.updatePermissions', $role->id) }}" method="POST" id="permissionsForm">
                        @csrf
                        @method('PUT')

                        <!-- Select/Deselect All -->
                        <div class="mb-3 pb-3 border-bottom">
                            <button type="button" class="btn btn-sm btn-outline-primary" id="selectAll">
                                <i class="fas fa-check-square"></i> Select All
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="deselectAll">
                                <i class="fas fa-square"></i> Deselect All
                            </button>
                            <span class="ms-3 text-muted">
                                Selected: <strong id="selectedCount">{{ count($rolePermissions) }}</strong> / {{ $groupedPermissions->sum(fn($group) => $group->count()) }}
                            </span>
                        </div>

                        <!-- Permissions by Module -->
                        <div id="permissionsContainer">
                            @foreach($groupedPermissions as $module => $permissions)
                            <div class="permission-module mb-4" data-module="{{ $module }}">
                                <div class="d-flex align-items-center mb-3">
                                    <h6 class="mb-0 text-uppercase text-primary">
                                        <i class="fas fa-folder me-2"></i>{{ ucfirst($module) }} Module
                                    </h6>
                                    <span class="badge bg-secondary ms-2">{{ $permissions->count() }}</span>
                                    <div class="ms-auto">
                                        <button type="button" class="btn btn-sm btn-outline-primary select-module" data-module="{{ $module }}">
                                            <i class="fas fa-check"></i> Select All
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary deselect-module" data-module="{{ $module }}">
                                            <i class="fas fa-times"></i> Deselect All
                                        </button>
                                    </div>
                                </div>

                                <div class="row">
                                    @foreach($permissions as $permission)
                                    <div class="col-md-4 col-lg-3 mb-2 permission-item" data-permission-name="{{ $permission->name }}">
                                        <div class="form-check">
                                            <input class="form-check-input permission-checkbox"
                                                   type="checkbox"
                                                   name="permissions[]"
                                                   value="{{ $permission->id }}"
                                                   id="permission_{{ $permission->id }}"
                                                   data-module="{{ $module }}"
                                                   {{ in_array($permission->id, $rolePermissions) ? 'checked' : '' }}>
                                            <label class="form-check-label small" for="permission_{{ $permission->id }}">
                                                {{ $permission->name }}
                                            </label>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                <hr>
                            </div>
                            @endforeach
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Permissions
                            </button>
                            <a href="{{ route('admin.roles.show', $role->id) }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchPermissions');
    const moduleFilter = document.getElementById('moduleFilter');
    const groupToggle = document.getElementById('groupByModule');
    const selectAllBtn = document.getElementById('selectAll');
    const deselectAllBtn = document.getElementById('deselectAll');
    const selectedCount = document.getElementById('selectedCount');
    const checkboxes = document.querySelectorAll('.permission-checkbox');

    // Update selected count
    function updateSelectedCount() {
        const checked = document.querySelectorAll('.permission-checkbox:checked').length;
        selectedCount.textContent = checked;
    }

    // Select All
    selectAllBtn.addEventListener('click', function() {
        checkboxes.forEach(cb => {
            if (cb.closest('.permission-item').style.display !== 'none') {
                cb.checked = true;
            }
        });
        updateSelectedCount();
    });

    // Deselect All
    deselectAllBtn.addEventListener('click', function() {
        checkboxes.forEach(cb => {
            if (cb.closest('.permission-item').style.display !== 'none') {
                cb.checked = false;
            }
        });
        updateSelectedCount();
    });

    // Module Select/Deselect
    document.querySelectorAll('.select-module').forEach(btn => {
        btn.addEventListener('click', function() {
            const module = this.dataset.module;
            document.querySelectorAll(`.permission-checkbox[data-module="${module}"]`).forEach(cb => {
                if (cb.closest('.permission-item').style.display !== 'none') {
                    cb.checked = true;
                }
            });
            updateSelectedCount();
        });
    });

    document.querySelectorAll('.deselect-module').forEach(btn => {
        btn.addEventListener('click', function() {
            const module = this.dataset.module;
            document.querySelectorAll(`.permission-checkbox[data-module="${module}"]`).forEach(cb => {
                if (cb.closest('.permission-item').style.display !== 'none') {
                    cb.checked = false;
                }
            });
            updateSelectedCount();
        });
    });

    // Search functionality
    searchInput.addEventListener('keyup', function() {
        const searchTerm = this.value.toLowerCase();
        document.querySelectorAll('.permission-item').forEach(item => {
            const permissionName = item.dataset.permissionName.toLowerCase();
            if (permissionName.includes(searchTerm)) {
                item.style.display = '';
            } else {
                item.style.display = 'none';
            }
        });
        updateModuleVisibility();
    });

    // Module filter
    moduleFilter.addEventListener('change', function() {
        const selectedModule = this.value;
        document.querySelectorAll('.permission-module').forEach(module => {
            if (selectedModule === '' || module.dataset.module === selectedModule) {
                module.style.display = '';
            } else {
                module.style.display = 'none';
            }
        });
    });

    // Group by module toggle
    groupToggle.addEventListener('change', function() {
        if (this.checked) {
            document.querySelectorAll('.permission-module hr').forEach(hr => hr.style.display = '');
            document.querySelectorAll('.permission-module h6').forEach(h6 => h6.style.display = '');
        } else {
            document.querySelectorAll('.permission-module hr').forEach(hr => hr.style.display = 'none');
            document.querySelectorAll('.permission-module h6').forEach(h6 => h6.style.display = 'none');
        }
    });

    // Update module visibility based on search
    function updateModuleVisibility() {
        document.querySelectorAll('.permission-module').forEach(module => {
            const visibleItems = module.querySelectorAll('.permission-item:not([style*="display: none"])');
            if (visibleItems.length === 0) {
                module.style.display = 'none';
            } else {
                module.style.display = '';
            }
        });
    }

    // Update count on checkbox change
    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateSelectedCount);
    });

    // Initial count
    updateSelectedCount();
});
</script>
@endpush
@endsection
