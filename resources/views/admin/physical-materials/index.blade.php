@extends('layouts.app')

@section('title', 'Physical Materials')
@section('page-title', 'Physical Materials')

@section('content')
<div class="page-header">
    <h1><i class="fas fa-box me-2"></i> Physical Materials Management</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Physical Materials</li>
        </ol>
    </nav>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #e3f2fd; color: #2196f3;">
                <i class="fas fa-box"></i>
            </div>
            <div class="stat-details">
                <h3 class="mb-0">{{ $stats['total'] }}</h3>
                <p class="text-muted mb-0">Total Materials</p>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #e8f5e9; color: #4caf50;">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-details">
                <h3 class="mb-0">{{ $stats['available'] }}</h3>
                <p class="text-muted mb-0">Available</p>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #ffebee; color: #f44336;">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <div class="stat-details">
                <h3 class="mb-0">{{ $stats['out_of_stock'] }}</h3>
                <p class="text-muted mb-0">Out of Stock</p>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #f3e5f5; color: #9c27b0;">
                <i class="fas fa-layer-group"></i>
            </div>
            <div class="stat-details">
                <h3 class="mb-0">{{ $stats['total_quantity'] }}</h3>
                <p class="text-muted mb-0">Total Quantity</p>
            </div>
        </div>
    </div>
</div>

<!-- Filters & Actions -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.physical-materials.index') }}">
            <div class="row">
                <div class="col-md-3 mb-3 mb-md-0">
                    <input type="text" name="search" class="form-control"
                           placeholder="Search materials..."
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>Available</option>
                        <option value="low_stock" {{ request('status') == 'low_stock' ? 'selected' : '' }}>Low Stock</option>
                        <option value="out_of_stock" {{ request('status') == 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="subject_id" class="form-select">
                        <option value="">All Subjects</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                                {{ $subject->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="month" class="form-select">
                        <option value="">All Months</option>
                        @foreach($months as $month)
                            <option value="{{ $month }}" {{ request('month') == $month ? 'selected' : '' }}>
                                {{ $month }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1">
                    <select name="year" class="form-select">
                        <option value="">Year</option>
                        @foreach($years as $year)
                            <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>
                                {{ $year }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <div class="btn-group w-100">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i>
                        </button>
                        <a href="{{ route('admin.physical-materials.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-redo"></i>
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Materials Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-list me-2"></i> Physical Materials List</span>
        @can('create-physical-materials')
        <a href="{{ route('admin.physical-materials.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i> Add New Material
        </a>
        @endcan
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Subject</th>
                        <th>Grade</th>
                        <th>Month/Year</th>
                        <th>Quantity</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($physicalMaterials as $material)
                        <tr>
                            <td>{{ $loop->iteration + $physicalMaterials->firstItem() - 1 }}</td>
                            <td>
                                <strong>{{ $material->name }}</strong>
                                @if($material->description)
                                    <br><small class="text-muted">{{ Str::limit($material->description, 50) }}</small>
                                @endif
                            </td>
                            <td>{{ $material->subject->name ?? 'N/A' }}</td>
                            <td>{{ $material->grade_level ?? '-' }}</td>
                            <td>
                                @if($material->month)
                                    {{ $material->month }} {{ $material->year }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                <strong>{{ $material->quantity_available }}</strong> / {{ $material->quantity_total }}
                                @if($material->quantity_available <= $material->minimum_quantity)
                                    <i class="fas fa-exclamation-triangle text-warning ms-1" title="Low Stock"></i>
                                @endif
                            </td>
                            <td>
                                @if($material->status == 'available')
                                    <span class="badge bg-success">Available</span>
                                @elseif($material->status == 'low_stock')
                                    <span class="badge bg-warning">Low Stock</span>
                                @else
                                    <span class="badge bg-danger">Out of Stock</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    @can('view-physical-materials')
                                    <a href="{{ route('admin.physical-materials.show', $material) }}"
                                       class="btn btn-outline-info" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @endcan
                                    @can('manage-collections')
                                    <a href="{{ route('admin.physical-materials.collections', $material) }}"
                                       class="btn btn-outline-success" title="Collections">
                                        <i class="fas fa-clipboard-check"></i>
                                    </a>
                                    @endcan
                                    @can('edit-physical-materials')
                                    <a href="{{ route('admin.physical-materials.edit', $material) }}"
                                       class="btn btn-outline-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endcan
                                    @can('delete-physical-materials')
                                    <button type="button" class="btn btn-outline-danger" title="Delete"
                                            onclick="confirmDelete('{{ $material->id }}', '{{ $material->name }}')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No physical materials found.</p>
                                @can('create-physical-materials')
                                <a href="{{ route('admin.physical-materials.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-1"></i> Add First Material
                                </a>
                                @endcan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center mt-4">
            <div class="text-muted">
                Showing {{ $physicalMaterials->firstItem() ?? 0 }} to {{ $physicalMaterials->lastItem() ?? 0 }} of {{ $physicalMaterials->total() }} entries
            </div>
            {{ $physicalMaterials->links() }}
        </div>
    </div>
</div>

<!-- Delete Form (Hidden) -->
<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endsection

@push('scripts')
<script>
function confirmDelete(id, name) {
    if (confirm(`Are you sure you want to delete "${name}"? This action cannot be undone.`)) {
        const form = document.getElementById('deleteForm');
        form.action = `/admin/physical-materials/${id}`;
        form.submit();
    }
}
</script>
@endpush
