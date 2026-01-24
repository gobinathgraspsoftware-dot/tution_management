@extends('layouts.app')

@section('title', 'Inventory Management')

@section('page-title', 'Inventory Management')

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">Inventory</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card bg-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-1">Total Items</h6>
                            <h3 class="mb-0">{{ number_format($statistics['total_items']) }}</h3>
                        </div>
                        <i class="fas fa-boxes fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-1">Stock Value</h6>
                            <h3 class="mb-0">RM {{ number_format($statistics['total_cost_value'], 2) }}</h3>
                        </div>
                        <i class="fas fa-money-bill-wave fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card bg-warning text-dark h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-1">Low Stock</h6>
                            <h3 class="mb-0">{{ number_format($statistics['low_stock_items']) }}</h3>
                        </div>
                        <i class="fas fa-exclamation-triangle fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card bg-danger text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-1">Out of Stock</h6>
                            <h3 class="mb-0">{{ number_format($statistics['out_of_stock_items']) }}</h3>
                        </div>
                        <i class="fas fa-times-circle fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters & Actions -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('admin.inventory.index') }}" method="GET" id="filterForm">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" class="form-control" placeholder="Name, SKU..." value="{{ $filters['search'] ?? '' }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-select">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ ($filters['category_id'] ?? '') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="active" {{ ($filters['status'] ?? '') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ ($filters['status'] ?? '') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="out_of_stock" {{ ($filters['status'] ?? '') == 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <div class="form-check mt-4">
                            <input type="checkbox" name="low_stock" value="1" class="form-check-input" id="lowStock" {{ ($filters['low_stock'] ?? '') ? 'checked' : '' }}>
                            <label class="form-check-label" for="lowStock">Low Stock Only</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="btn-group w-100">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-1"></i> Filter
                            </button>
                            <a href="{{ route('admin.inventory.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-redo me-1"></i> Reset
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="d-flex justify-content-between mb-3">
        <div>
            @can('create-inventory')
            <a href="{{ route('admin.inventory.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Add Item
            </a>
            @endcan
            @can('view-inventory-categories')
            <a href="{{ route('admin.inventory-categories.index') }}" class="btn btn-outline-primary">
                <i class="fas fa-tags me-1"></i> Categories
            </a>
            @endcan
            @can('manage-inventory-stock')
            <a href="{{ route('admin.inventory.low-stock') }}" class="btn btn-outline-warning">
                <i class="fas fa-exclamation-triangle me-1"></i> Low Stock
            </a>
            @endcan
        </div>
        <div>
            @can('view-inventory-reports')
            <a href="{{ route('admin.inventory.reports') }}" class="btn btn-info">
                <i class="fas fa-chart-bar me-1"></i> Reports
            </a>
            @endcan
            @can('export-inventory')
            <a href="{{ route('admin.inventory.export', request()->query()) }}" class="btn btn-success">
                <i class="fas fa-file-excel me-1"></i> Export
            </a>
            @endcan
        </div>
    </div>

    <!-- Inventory Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 60px;">Image</th>
                            <th>
                                <a href="{{ route('admin.inventory.index', array_merge(request()->query(), ['sort_by' => 'name', 'sort_direction' => ($filters['sort_by'] ?? '') == 'name' && ($filters['sort_direction'] ?? 'asc') == 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                                    Name
                                    @if(($filters['sort_by'] ?? '') == 'name')
                                    <i class="fas fa-sort-{{ ($filters['sort_direction'] ?? 'asc') == 'asc' ? 'up' : 'down' }}"></i>
                                    @endif
                                </a>
                            </th>
                            <th>SKU</th>
                            <th>Category</th>
                            <th class="text-end">Cost Price</th>
                            <th class="text-end">Selling Price</th>
                            <th class="text-center">
                                <a href="{{ route('admin.inventory.index', array_merge(request()->query(), ['sort_by' => 'current_stock', 'sort_direction' => ($filters['sort_by'] ?? '') == 'current_stock' && ($filters['sort_direction'] ?? 'asc') == 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                                    Stock
                                    @if(($filters['sort_by'] ?? '') == 'current_stock')
                                    <i class="fas fa-sort-{{ ($filters['sort_direction'] ?? 'asc') == 'asc' ? 'up' : 'down' }}"></i>
                                    @endif
                                </a>
                            </th>
                            <th class="text-center">Status</th>
                            <th class="text-center" style="width: 150px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                        <tr>
                            <td>
                                @if($item->image)
                                <img src="{{ Storage::url($item->image) }}" alt="{{ $item->name }}" class="rounded" style="width: 50px; height: 50px; object-fit: cover;">
                                @else
                                <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                    <i class="fas fa-image text-muted"></i>
                                </div>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.inventory.show', $item) }}" class="fw-semibold text-decoration-none">
                                    {{ $item->name }}
                                </a>
                                @if($item->description)
                                <br><small class="text-muted">{{ Str::limit($item->description, 50) }}</small>
                                @endif
                            </td>
                            <td><code>{{ $item->sku }}</code></td>
                            <td>{{ $item->category->name ?? 'N/A' }}</td>
                            <td class="text-end">RM {{ number_format($item->cost_price, 2) }}</td>
                            <td class="text-end">RM {{ number_format($item->selling_price, 2) }}</td>
                            <td class="text-center">
                                @if($item->current_stock == 0)
                                <span class="badge bg-danger">0</span>
                                @elseif($item->current_stock <= $item->reorder_level)
                                <span class="badge bg-warning text-dark">{{ $item->current_stock }}</span>
                                @else
                                <span class="badge bg-success">{{ $item->current_stock }}</span>
                                @endif
                                <small class="text-muted d-block">/ {{ $item->reorder_level }}</small>
                            </td>
                            <td class="text-center">
                                @if($item->status == 'active')
                                <span class="badge bg-success">Active</span>
                                @elseif($item->status == 'inactive')
                                <span class="badge bg-secondary">Inactive</span>
                                @else
                                <span class="badge bg-danger">Out of Stock</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.inventory.show', $item) }}" class="btn btn-outline-info" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @can('edit-inventory')
                                    <a href="{{ route('admin.inventory.edit', $item) }}" class="btn btn-outline-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endcan
                                    @can('manage-inventory-stock')
                                    <a href="{{ route('admin.inventory.adjust-stock', $item) }}" class="btn btn-outline-warning" title="Adjust Stock">
                                        <i class="fas fa-balance-scale"></i>
                                    </a>
                                    @endcan
                                    @can('delete-inventory')
                                    <button type="button" class="btn btn-outline-danger" title="Delete" onclick="confirmDelete({{ $item->id }}, '{{ $item->name }}')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-box-open fa-3x mb-3"></i>
                                    <p class="mb-0">No inventory items found.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted">
                    Showing {{ $items->firstItem() ?? 0 }} to {{ $items->lastItem() ?? 0 }} of {{ $items->total() }} items
                </div>
                {{ $items->links() }}
            </div>
        </div>
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
                <p>Are you sure you want to delete <strong id="itemName"></strong>?</p>
                <p class="text-danger mb-0"><small>This action cannot be undone.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function confirmDelete(id, name) {
    document.getElementById('itemName').textContent = name;
    document.getElementById('deleteForm').action = '{{ url("admin/inventory") }}/' + id;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
@endpush
