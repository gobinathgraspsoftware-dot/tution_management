@extends('layouts.app')

@section('title', $inventory->name)

@section('page-title', $inventory->name)

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.inventory.index') }}">Inventory</a></li>
        <li class="breadcrumb-item active">{{ $inventory->name }}</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Action Buttons -->
    <div class="d-flex justify-content-between mb-4">
        <a href="{{ route('admin.inventory.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to List
        </a>
        <div>
            @can('manage-inventory-stock')
            <a href="{{ route('admin.inventory.adjust-stock', $inventory) }}" class="btn btn-warning">
                <i class="fas fa-balance-scale me-1"></i> Adjust Stock
            </a>
            @endcan
            @can('edit-inventory')
            <a href="{{ route('admin.inventory.edit', $inventory) }}" class="btn btn-primary">
                <i class="fas fa-edit me-1"></i> Edit
            </a>
            @endcan
            @can('delete-inventory')
            <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                <i class="fas fa-trash me-1"></i> Delete
            </button>
            @endcan
        </div>
    </div>

    <div class="row">
        <!-- Main Info -->
        <div class="col-lg-8">
            <!-- Product Details Card -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 text-center mb-3 mb-md-0">
                            @if($inventory->image)
                            <img src="{{ Storage::url($inventory->image) }}" alt="{{ $inventory->name }}" class="img-fluid rounded shadow" style="max-height: 250px;">
                            @else
                            <div class="bg-light rounded d-flex align-items-center justify-content-center mx-auto" style="width: 250px; height: 250px;">
                                <i class="fas fa-image fa-5x text-muted"></i>
                            </div>
                            @endif
                        </div>
                        <div class="col-md-8">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h3 class="mb-1">{{ $inventory->name }}</h3>
                                    <p class="text-muted mb-0">
                                        <code class="me-2">{{ $inventory->sku }}</code>
                                        <span class="badge bg-info">{{ $inventory->category->name ?? 'Uncategorized' }}</span>
                                    </p>
                                </div>
                                @if($inventory->status == 'active')
                                <span class="badge bg-success fs-6">Active</span>
                                @elseif($inventory->status == 'inactive')
                                <span class="badge bg-secondary fs-6">Inactive</span>
                                @else
                                <span class="badge bg-danger fs-6">Out of Stock</span>
                                @endif
                            </div>

                            @if($inventory->description)
                            <p class="text-muted mt-3">{{ $inventory->description }}</p>
                            @endif

                            <hr>

                            <div class="row">
                                <div class="col-6">
                                    <small class="text-muted">Cost Price</small>
                                    <h5>RM {{ number_format($inventory->cost_price, 2) }}</h5>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Selling Price</small>
                                    <h5 class="text-success">RM {{ number_format($inventory->selling_price, 2) }}</h5>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-6">
                                    <small class="text-muted">Profit Margin</small>
                                    @php
                                        $margin = $inventory->cost_price > 0
                                            ? (($inventory->selling_price - $inventory->cost_price) / $inventory->cost_price) * 100
                                            : 0;
                                    @endphp
                                    <h5 class="{{ $margin > 0 ? 'text-success' : 'text-danger' }}">
                                        {{ number_format($margin, 1) }}%
                                    </h5>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Unit</small>
                                    <h5>{{ ucfirst($inventory->unit) }}</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stock Trend Chart -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-chart-line me-2"></i>Stock Trend (Last 30 Days)</h5>
                </div>
                <div class="card-body">
                    <canvas id="stockTrendChart" height="100"></canvas>
                </div>
            </div>

            <!-- Recent Stock History -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><i class="fas fa-history me-2"></i>Recent Stock History</h5>
                    <a href="{{ route('admin.inventory.stock-history', $inventory) }}" class="btn btn-sm btn-outline-primary">
                        View All
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th class="text-center">Quantity</th>
                                    <th class="text-center">Previous</th>
                                    <th class="text-center">New</th>
                                    <th>Reference</th>
                                    <th>By</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentLogs as $log)
                                <tr>
                                    <td>{{ $log->created_at->format('d M Y, h:i A') }}</td>
                                    <td>
                                        @if($log->type == 'add')
                                        <span class="badge bg-success">Add</span>
                                        @elseif($log->type == 'remove')
                                        <span class="badge bg-warning text-dark">Remove</span>
                                        @elseif($log->type == 'sale')
                                        <span class="badge bg-info">Sale</span>
                                        @else
                                        <span class="badge bg-secondary">Adjust</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if(in_array($log->type, ['add']))
                                        <span class="text-success">+{{ $log->quantity }}</span>
                                        @elseif(in_array($log->type, ['remove', 'sale']))
                                        <span class="text-danger">-{{ $log->quantity }}</span>
                                        @else
                                        {{ $log->quantity }}
                                        @endif
                                    </td>
                                    <td class="text-center">{{ $log->previous_stock }}</td>
                                    <td class="text-center">{{ $log->new_stock }}</td>
                                    <td><small>{{ $log->reference ?? '-' }}</small></td>
                                    <td><small>{{ $log->createdBy->name ?? 'System' }}</small></td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-3">
                                        No stock history available.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Stock Status Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-boxes me-2"></i>Stock Status</h5>
                </div>
                <div class="card-body">
                    @php
                        $stockPercentage = $inventory->reorder_level > 0
                            ? min(100, ($inventory->current_stock / ($inventory->reorder_level * 2)) * 100)
                            : ($inventory->current_stock > 0 ? 100 : 0);
                        $stockColor = $inventory->current_stock == 0 ? 'danger'
                            : ($inventory->current_stock <= $inventory->reorder_level ? 'warning' : 'success');
                    @endphp

                    <div class="text-center mb-3">
                        <h1 class="display-4 mb-0 text-{{ $stockColor }}">{{ $inventory->current_stock }}</h1>
                        <p class="text-muted">{{ ucfirst($inventory->unit) }} in stock</p>
                    </div>

                    <div class="progress mb-3" style="height: 25px;">
                        <div class="progress-bar bg-{{ $stockColor }}" role="progressbar" style="width: {{ $stockPercentage }}%">
                            {{ number_format($stockPercentage, 0) }}%
                        </div>
                    </div>

                    <div class="row text-center">
                        <div class="col-6">
                            <small class="text-muted d-block">Reorder Level</small>
                            <strong>{{ $inventory->reorder_level }}</strong>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Status</small>
                            @if($inventory->current_stock == 0)
                            <strong class="text-danger">Out of Stock</strong>
                            @elseif($inventory->current_stock <= $inventory->reorder_level)
                            <strong class="text-warning">Low Stock</strong>
                            @else
                            <strong class="text-success">Healthy</strong>
                            @endif
                        </div>
                    </div>

                    <hr>

                    @if($avgConsumption > 0)
                    <div class="mb-2">
                        <small class="text-muted">Avg. Daily Consumption</small>
                        <p class="mb-0"><strong>{{ number_format($avgConsumption, 1) }} {{ $inventory->unit }}/day</strong></p>
                    </div>
                    @endif

                    @if($daysUntilOut !== null)
                    <div>
                        <small class="text-muted">Est. Days Until Out of Stock</small>
                        <p class="mb-0">
                            <strong class="{{ $daysUntilOut <= 7 ? 'text-danger' : ($daysUntilOut <= 14 ? 'text-warning' : 'text-success') }}">
                                {{ $daysUntilOut }} days
                            </strong>
                        </p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Value Summary Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-dollar-sign me-2"></i>Value Summary</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Stock Cost Value</span>
                            <strong>RM {{ number_format($inventory->current_stock * $inventory->cost_price, 2) }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Stock Retail Value</span>
                            <strong class="text-success">RM {{ number_format($inventory->current_stock * $inventory->selling_price, 2) }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Potential Profit</span>
                            <strong class="text-primary">RM {{ number_format($inventory->current_stock * ($inventory->selling_price - $inventory->cost_price), 2) }}</strong>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Quick Actions Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @can('manage-inventory-stock')
                        <a href="{{ route('admin.inventory.adjust-stock', $inventory) }}" class="btn btn-outline-success">
                            <i class="fas fa-plus me-1"></i> Add Stock
                        </a>
                        @endcan
                        <a href="{{ route('admin.inventory.stock-history', $inventory) }}" class="btn btn-outline-info">
                            <i class="fas fa-history me-1"></i> Full History
                        </a>
                        @can('edit-inventory')
                        <form action="{{ route('admin.inventory.toggle-status', $inventory) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-outline-{{ $inventory->status == 'active' ? 'warning' : 'success' }} w-100">
                                <i class="fas fa-toggle-{{ $inventory->status == 'active' ? 'on' : 'off' }} me-1"></i>
                                {{ $inventory->status == 'active' ? 'Deactivate' : 'Activate' }}
                            </button>
                        </form>
                        @endcan
                    </div>
                </div>
            </div>

            <!-- Item Info Card -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-info-circle me-2"></i>Item Information</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <small class="text-muted d-block">Created At</small>
                            {{ $inventory->created_at->format('d M Y, h:i A') }}
                        </li>
                        <li class="mb-2">
                            <small class="text-muted d-block">Last Updated</small>
                            {{ $inventory->updated_at->format('d M Y, h:i A') }}
                        </li>
                        <li>
                            <small class="text-muted d-block">Total Transactions</small>
                            {{ $inventory->logs->count() }} records
                        </li>
                    </ul>
                </div>
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
                <p>Are you sure you want to delete <strong>{{ $inventory->name }}</strong>?</p>
                <p class="text-danger mb-0"><small>This action cannot be undone. All stock history will also be deleted.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('admin.inventory.destroy', $inventory) }}" method="POST" class="d-inline">
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
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function confirmDelete() {
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

// Stock Trend Chart
const stockTrendData = @json($stockTrend);
const ctx = document.getElementById('stockTrendChart').getContext('2d');

new Chart(ctx, {
    type: 'line',
    data: {
        labels: stockTrendData.map(d => d.date),
        datasets: [{
            label: 'Stock Level',
            data: stockTrendData.map(d => d.stock_level),
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.1,
            fill: true
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Stock Level'
                }
            },
            x: {
                title: {
                    display: true,
                    text: 'Date'
                }
            }
        }
    }
});
</script>
@endpush
