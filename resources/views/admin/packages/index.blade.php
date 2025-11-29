@extends('layouts.app')

@section('title', 'Package Management')
@section('page-title', 'Package Management')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="fas fa-box me-2"></i> Package Management</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Packages</li>
            </ol>
        </nav>
    </div>
    <div>
        @can('create-packages')
        <a href="{{ route('admin.packages.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Add Package
        </a>
        @endcan
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="mb-0">{{ \App\Models\Package::count() }}</h4>
                        <small>Total Packages</small>
                    </div>
                    <i class="fas fa-box fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="mb-0">{{ \App\Models\Package::where('type', 'online')->count() }}</h4>
                        <small>Online Packages</small>
                    </div>
                    <i class="fas fa-globe fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="mb-0">{{ \App\Models\Package::where('type', 'offline')->count() }}</h4>
                        <small>Offline Packages</small>
                    </div>
                    <i class="fas fa-building fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="mb-0">{{ \App\Models\Package::active()->count() }}</h4>
                        <small>Active Packages</small>
                    </div>
                    <i class="fas fa-check-circle fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Online Fee Info -->
<div class="alert alert-info mb-4">
    <i class="fas fa-info-circle me-2"></i>
    <strong>Online Payment Fee:</strong> RM 130.00 is automatically added for Online and Hybrid packages.
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('admin.packages.index') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control"
                       placeholder="Name, code, description..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Type</label>
                <select name="type" class="form-select">
                    <option value="">All Types</option>
                    <option value="online" {{ request('type') == 'online' ? 'selected' : '' }}>Online</option>
                    <option value="offline" {{ request('type') == 'offline' ? 'selected' : '' }}>Offline</option>
                    <option value="hybrid" {{ request('type') == 'hybrid' ? 'selected' : '' }}>Hybrid</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Min Price (RM)</label>
                <input type="number" name="min_price" class="form-control"
                       placeholder="Min" value="{{ request('min_price') }}" min="0" step="0.01">
            </div>
            <div class="col-md-3 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search me-1"></i> Filter
                </button>
                <a href="{{ route('admin.packages.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-1"></i> Clear
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Packages List -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Code</th>
                        <th>Package Name</th>
                        <th>Type</th>
                        <th>Duration</th>
                        <th>Subjects</th>
                        <th>Price</th>
                        <th>Online Fee</th>
                        <th>Total</th>
                        <th>Enrollments</th>
                        <th>Status</th>
                        <th width="180">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($packages as $package)
                        <tr>
                            <td><span class="badge bg-secondary">{{ $package->code }}</span></td>
                            <td>
                                <strong>{{ $package->name }}</strong>
                                @if($package->includes_materials)
                                    <br><small class="text-success"><i class="fas fa-book me-1"></i>Includes Materials</small>
                                @endif
                            </td>
                            <td>
                                @if($package->type == 'online')
                                    <span class="badge bg-info">Online</span>
                                @elseif($package->type == 'offline')
                                    <span class="badge bg-warning">Offline</span>
                                @else
                                    <span class="badge bg-primary">Hybrid</span>
                                @endif
                            </td>
                            <td>{{ $package->duration_months }} month(s)</td>
                            <td><span class="badge bg-info">{{ $package->subjects_count }}</span></td>
                            <td>RM {{ number_format($package->price, 2) }}</td>
                            <td>
                                @if($package->online_fee)
                                    RM {{ number_format($package->online_fee, 2) }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <strong class="text-primary">RM {{ number_format($package->total_price, 2) }}</strong>
                            </td>
                            <td><span class="badge bg-success">{{ $package->enrollments_count }}</span></td>
                            <td>
                                @if($package->status == 'active')
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    @can('view-packages')
                                    <a href="{{ route('admin.packages.show', $package) }}"
                                       class="btn btn-outline-info" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @endcan
                                    @can('edit-packages')
                                    <a href="{{ route('admin.packages.edit', $package) }}"
                                       class="btn btn-outline-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-success" title="Duplicate"
                                            onclick="confirmDuplicate('{{ $package->id }}', '{{ $package->name }}')">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-warning" title="Toggle Status"
                                            onclick="confirmToggle('{{ $package->id }}', '{{ $package->name }}', '{{ $package->status }}')">
                                        <i class="fas fa-power-off"></i>
                                    </button>
                                    @endcan
                                    @can('delete-packages')
                                    <button type="button" class="btn btn-outline-danger" title="Delete"
                                            onclick="confirmDelete('{{ $package->id }}', '{{ $package->name }}')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center py-4">
                                <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No packages found.</p>
                                @can('create-packages')
                                <a href="{{ route('admin.packages.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-1"></i> Add First Package
                                </a>
                                @endcan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($packages->hasPages())
        <div class="d-flex justify-content-between align-items-center mt-4">
            <div class="text-muted">
                Showing {{ $packages->firstItem() ?? 0 }} to {{ $packages->lastItem() ?? 0 }}
                of {{ $packages->total() }} packages
            </div>
            {{ $packages->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete package <strong id="deletePackageName"></strong>?</p>
                <p class="text-danger mb-0">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    This will also remove all subject associations.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i> Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Toggle Status Modal -->
<div class="modal fade" id="toggleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Status Change</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to <span id="toggleAction"></span> package <strong id="togglePackageName"></strong>?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="toggleForm" method="POST" class="d-inline">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-power-off me-1"></i> Confirm
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Duplicate Modal -->
<div class="modal fade" id="duplicateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Duplicate Package</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Create a copy of package <strong id="duplicatePackageName"></strong>?</p>
                <p class="text-info mb-0">
                    <i class="fas fa-info-circle me-1"></i>
                    The copy will be created as inactive. You can then edit the details.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="duplicateForm" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-copy me-1"></i> Duplicate
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function confirmDelete(id, name) {
    document.getElementById('deletePackageName').textContent = name;
    document.getElementById('deleteForm').action = `/admin/packages/${id}`;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

function confirmToggle(id, name, currentStatus) {
    const action = currentStatus === 'active' ? 'deactivate' : 'activate';
    document.getElementById('togglePackageName').textContent = name;
    document.getElementById('toggleAction').textContent = action;
    document.getElementById('toggleForm').action = `/admin/packages/${id}/toggle-status`;
    new bootstrap.Modal(document.getElementById('toggleModal')).show();
}

function confirmDuplicate(id, name) {
    document.getElementById('duplicatePackageName').textContent = name;
    document.getElementById('duplicateForm').action = `/admin/packages/${id}/duplicate`;
    new bootstrap.Modal(document.getElementById('duplicateModal')).show();
}
</script>
@endpush
