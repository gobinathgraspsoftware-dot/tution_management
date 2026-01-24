@extends('layouts.app')

@section('title', 'Inventory')

@section('page-title', 'Inventory')

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('staff.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">Inventory</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Low Stock Alert -->
    @if($lowStockCount > 0)
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <strong>Low Stock Alert!</strong> There are <strong>{{ $lowStockCount }}</strong> items running low on stock.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Search & Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('staff.inventory.index') }}" method="GET">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" class="form-control" placeholder="Item name or SKU..." value="{{ $filters['search'] ?? '' }}">
                    </div>
                    <div class="col-md-3">
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
                        <div class="form-check mt-4">
                            <input type="checkbox" name="low_stock" value="1" class="form-check-input" id="lowStock" {{ ($filters['low_stock'] ?? '') ? 'checked' : '' }}>
                            <label class="form-check-label" for="lowStock">Low Stock Only</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="btn-group w-100">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-1"></i> Search
                            </button>
                            <a href="{{ route('staff.inventory.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-redo me-1"></i> Reset
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Inventory Grid -->
    <div class="row">
        @forelse($items as $item)
        <div class="col-md-4 col-lg-3 mb-4">
            <div class="card h-100 {{ $item->current_stock <= $item->reorder_level ? 'border-warning' : '' }}">
                @if($item->current_stock <= $item->reorder_level)
                <div class="card-header bg-warning text-dark py-1 text-center">
                    <small><i class="fas fa-exclamation-triangle me-1"></i>Low Stock</small>
                </div>
                @endif
                <div class="card-body text-center">
                    <!-- Image -->
                    @if($item->image)
                    <img src="{{ Storage::url($item->image) }}" alt="{{ $item->name }}" class="rounded mb-3" style="width: 80px; height: 80px; object-fit: cover;">
                    @else
                    <div class="bg-light rounded d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px;">
                        <i class="fas fa-image fa-2x text-muted"></i>
                    </div>
                    @endif

                    <!-- Item Info -->
                    <h6 class="card-title mb-1">{{ $item->name }}</h6>
                    <small class="text-muted d-block mb-2">{{ $item->category->name ?? 'N/A' }}</small>
                    <code class="small">{{ $item->sku }}</code>

                    <!-- Stock Level -->
                    <div class="mt-3">
                        @if($item->current_stock == 0)
                        <span class="badge bg-danger fs-5">OUT OF STOCK</span>
                        @elseif($item->current_stock <= $item->reorder_level)
                        <span class="badge bg-warning text-dark fs-5">{{ $item->current_stock }} {{ $item->unit }}</span>
                        @else
                        <span class="badge bg-success fs-5">{{ $item->current_stock }} {{ $item->unit }}</span>
                        @endif
                        <div class="text-muted small mt-1">Reorder at: {{ $item->reorder_level }}</div>
                    </div>

                    <!-- Price -->
                    <div class="mt-2">
                        <strong class="text-primary">RM {{ number_format($item->selling_price, 2) }}</strong>
                    </div>
                </div>
                <div class="card-footer bg-transparent">
                    @can('manage-inventory-stock')
                    <a href="{{ route('staff.inventory.adjust-stock', $item) }}" class="btn btn-sm btn-outline-primary w-100">
                        <i class="fas fa-balance-scale me-1"></i> Adjust Stock
                    </a>
                    @endcan
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-box-open fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted">No inventory items found</h5>
                    <p class="text-muted mb-0">Try adjusting your search filters</p>
                </div>
            </div>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-center">
        {{ $items->links() }}
    </div>
</div>
@endsection
