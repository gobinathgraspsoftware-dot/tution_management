@extends('layouts.app')

@section('title', 'Low Stock Alerts')

@section('page-title', 'Low Stock Alerts')

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.inventory.index') }}">Inventory</a></li>
        <li class="breadcrumb-item active">Low Stock Alerts</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-1">Healthy Stock</h6>
                            <h3 class="mb-0">{{ $stockSummary['healthy_stock'] }}</h3>
                        </div>
                        <i class="fas fa-check-circle fa-2x opacity-50"></i>
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
                            <h3 class="mb-0">{{ $stockSummary['low_stock_items'] }}</h3>
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
                            <h3 class="mb-0">{{ $stockSummary['out_of_stock_items'] }}</h3>
                        </div>
                        <i class="fas fa-times-circle fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card bg-dark text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-1">Critical</h6>
                            <h3 class="mb-0">{{ $stockSummary['critical_items'] }}</h3>
                        </div>
                        <i class="fas fa-skull-crossbones fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Out of Stock Items -->
        <div class="col-lg-6 mb-4">
            <div class="card border-danger h-100">
                <div class="card-header bg-danger text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-times-circle me-2"></i>Out of Stock ({{ $outOfStockItems->count() }})
                    </h5>
                </div>
                <div class="card-body p-0">
                    @if($outOfStockItems->count() > 0)
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-hover mb-0">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>Item</th>
                                    <th>Category</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($outOfStockItems as $item)
                                <tr>
                                    <td>
                                        <strong>{{ $item->name }}</strong>
                                        <br><small class="text-muted">{{ $item->sku }}</small>
                                    </td>
                                    <td><small>{{ $item->category->name ?? 'N/A' }}</small></td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.inventory.adjust-stock', $item) }}" class="btn btn-sm btn-success">
                                            <i class="fas fa-plus"></i> Restock
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-check-circle fa-3x mb-3 text-success"></i>
                        <p class="mb-0">No items are out of stock!</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Critical Stock Items -->
        <div class="col-lg-6 mb-4">
            <div class="card border-warning h-100">
                <div class="card-header bg-warning text-dark">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>Low Stock ({{ $lowStockItems->count() }})
                    </h5>
                </div>
                <div class="card-body p-0">
                    @if($lowStockItems->count() > 0)
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-hover mb-0">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>Item</th>
                                    <th class="text-center">Stock</th>
                                    <th class="text-center">Reorder</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($lowStockItems as $item)
                                <tr class="{{ $item->current_stock <= ($item->reorder_level * 0.25) ? 'table-danger' : '' }}">
                                    <td>
                                        <strong>{{ $item->name }}</strong>
                                        <br><small class="text-muted">{{ $item->sku }}</small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-{{ $item->current_stock <= ($item->reorder_level * 0.25) ? 'danger' : 'warning text-dark' }}">
                                            {{ $item->current_stock }}
                                        </span>
                                    </td>
                                    <td class="text-center">{{ $item->reorder_level }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.inventory.adjust-stock', $item) }}" class="btn btn-sm btn-outline-success">
                                            <i class="fas fa-plus"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-check-circle fa-3x mb-3 text-success"></i>
                        <p class="mb-0">All items have healthy stock levels!</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Reorder Suggestions -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-shopping-cart me-2"></i>Reorder Suggestions
            </h5>
        </div>
        <div class="card-body">
            @if(count($reorderSuggestions) > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Item</th>
                            <th>Category</th>
                            <th class="text-center">Current Stock</th>
                            <th class="text-center">Reorder Level</th>
                            <th class="text-center">Suggested Qty</th>
                            <th class="text-end">Est. Cost</th>
                            <th class="text-center">Priority</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reorderSuggestions as $suggestion)
                        <tr>
                            <td>
                                <strong>{{ $suggestion['item']->name }}</strong>
                                <br><small class="text-muted">{{ $suggestion['item']->sku }}</small>
                            </td>
                            <td>{{ $suggestion['item']->category->name ?? 'N/A' }}</td>
                            <td class="text-center">{{ $suggestion['current_stock'] }} {{ $suggestion['item']->unit }}</td>
                            <td class="text-center">{{ $suggestion['reorder_level'] }}</td>
                            <td class="text-center"><strong>{{ $suggestion['suggested_quantity'] }} {{ $suggestion['item']->unit }}</strong></td>
                            <td class="text-end">RM {{ number_format($suggestion['estimated_cost'], 2) }}</td>
                            <td class="text-center">
                                @if($suggestion['priority'] == 'critical')
                                <span class="badge bg-danger">CRITICAL</span>
                                @elseif($suggestion['priority'] == 'high')
                                <span class="badge bg-warning text-dark">HIGH</span>
                                @else
                                <span class="badge bg-info">MEDIUM</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('admin.inventory.adjust-stock', $suggestion['item']) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-plus me-1"></i> Restock
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="5" class="text-end">Total Estimated Cost:</th>
                            <th class="text-end">RM {{ number_format(collect($reorderSuggestions)->sum('estimated_cost'), 2) }}</th>
                            <th colspan="2"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @else
            <div class="text-center py-4 text-muted">
                <i class="fas fa-thumbs-up fa-3x mb-3 text-success"></i>
                <p class="mb-0">No reorder suggestions at this time. All stock levels are healthy!</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
