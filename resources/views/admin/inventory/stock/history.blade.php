@extends('layouts.app')

@section('title', 'Stock History')

@section('page-title', 'Stock History: ' . $inventory->name)

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.inventory.index') }}">Inventory</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.inventory.show', $inventory) }}">{{ $inventory->name }}</a></li>
        <li class="breadcrumb-item active">Stock History</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Item Summary Card -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="d-flex align-items-center">
                        @if($inventory->image)
                        <img src="{{ Storage::url($inventory->image) }}" alt="{{ $inventory->name }}" class="rounded me-3" style="width: 60px; height: 60px; object-fit: cover;">
                        @else
                        <div class="bg-light rounded me-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                            <i class="fas fa-image fa-2x text-muted"></i>
                        </div>
                        @endif
                        <div>
                            <h5 class="mb-0">{{ $inventory->name }}</h5>
                            <small class="text-muted"><code>{{ $inventory->sku }}</code></small>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 text-md-end mt-3 mt-md-0">
                    <div class="d-inline-block text-center me-4">
                        <small class="text-muted d-block">Current Stock</small>
                        <span class="fs-4 fw-bold text-{{ $inventory->current_stock <= $inventory->reorder_level ? 'warning' : 'success' }}">
                            {{ $inventory->current_stock }} {{ $inventory->unit }}
                        </span>
                    </div>
                    <div class="d-inline-block text-center">
                        <small class="text-muted d-block">Reorder Level</small>
                        <span class="fs-4 fw-bold">{{ $inventory->reorder_level }} {{ $inventory->unit }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('admin.inventory.stock-history', $inventory) }}" method="GET">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-select">
                            <option value="">All Types</option>
                            <option value="add" {{ ($filters['type'] ?? '') == 'add' ? 'selected' : '' }}>Add</option>
                            <option value="remove" {{ ($filters['type'] ?? '') == 'remove' ? 'selected' : '' }}>Remove</option>
                            <option value="adjust" {{ ($filters['type'] ?? '') == 'adjust' ? 'selected' : '' }}>Adjust</option>
                            <option value="sale" {{ ($filters['type'] ?? '') == 'sale' ? 'selected' : '' }}>Sale</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">From Date</label>
                        <input type="date" name="date_from" class="form-control" value="{{ $filters['date_from'] ?? '' }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">To Date</label>
                        <input type="date" name="date_to" class="form-control" value="{{ $filters['date_to'] ?? '' }}">
                    </div>
                    <div class="col-md-3">
                        <div class="btn-group w-100">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter me-1"></i> Filter
                            </button>
                            <a href="{{ route('admin.inventory.stock-history', $inventory) }}" class="btn btn-outline-secondary">
                                <i class="fas fa-redo me-1"></i> Reset
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- History Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0"><i class="fas fa-history me-2"></i>Stock Movement History</h5>
            <a href="{{ route('admin.inventory.show', $inventory) }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Item
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Date & Time</th>
                            <th>Type</th>
                            <th class="text-center">Quantity</th>
                            <th class="text-center">Previous Stock</th>
                            <th class="text-center">New Stock</th>
                            <th>Reference</th>
                            <th>Notes</th>
                            <th>By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                        <tr>
                            <td>
                                <div>{{ $log->created_at->format('d M Y') }}</div>
                                <small class="text-muted">{{ $log->created_at->format('h:i A') }}</small>
                            </td>
                            <td>
                                @if($log->type == 'add')
                                <span class="badge bg-success"><i class="fas fa-plus me-1"></i>Add</span>
                                @elseif($log->type == 'remove')
                                <span class="badge bg-warning text-dark"><i class="fas fa-minus me-1"></i>Remove</span>
                                @elseif($log->type == 'sale')
                                <span class="badge bg-info"><i class="fas fa-shopping-cart me-1"></i>Sale</span>
                                @else
                                <span class="badge bg-secondary"><i class="fas fa-edit me-1"></i>Adjust</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if(in_array($log->type, ['add']))
                                <span class="text-success fw-bold">+{{ $log->quantity }}</span>
                                @elseif(in_array($log->type, ['remove', 'sale']))
                                <span class="text-danger fw-bold">-{{ $log->quantity }}</span>
                                @else
                                <span class="fw-bold">{{ $log->quantity }}</span>
                                @endif
                            </td>
                            <td class="text-center">{{ $log->previous_stock }}</td>
                            <td class="text-center">
                                <strong>{{ $log->new_stock }}</strong>
                                @if($log->new_stock < $log->previous_stock)
                                <i class="fas fa-arrow-down text-danger ms-1"></i>
                                @elseif($log->new_stock > $log->previous_stock)
                                <i class="fas fa-arrow-up text-success ms-1"></i>
                                @endif
                            </td>
                            <td>
                                @if($log->reference)
                                <code>{{ $log->reference }}</code>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($log->notes)
                                <small title="{{ $log->notes }}">{{ Str::limit($log->notes, 30) }}</small>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <small>{{ $log->createdBy->name ?? 'System' }}</small>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-history fa-3x mb-3"></i>
                                    <p class="mb-0">No stock history found.</p>
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
                    Showing {{ $logs->firstItem() ?? 0 }} to {{ $logs->lastItem() ?? 0 }} of {{ $logs->total() }} records
                </div>
                {{ $logs->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
