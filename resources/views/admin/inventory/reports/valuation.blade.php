@extends('layouts.app')

@section('title', 'Valuation Report')

@section('page-title', 'Inventory Valuation Report')

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.inventory.index') }}">Inventory</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.inventory.reports') }}">Reports</a></li>
        <li class="breadcrumb-item active">Valuation Report</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('admin.inventory.reports.valuation') }}" method="GET">
                <div class="row g-3 align-items-end">
                    <div class="col-md-6">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-select">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ $categoryId == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <div class="btn-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter me-1"></i> Filter
                            </button>
                            <a href="{{ route('admin.inventory.reports.valuation') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-redo me-1"></i> Reset
                            </a>
                            <a href="{{ route('admin.inventory.reports.export-valuation', request()->query()) }}" class="btn btn-success">
                                <i class="fas fa-file-excel me-1"></i> Export
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-2 col-sm-6 mb-3">
            <div class="card bg-primary text-white h-100">
                <div class="card-body text-center">
                    <h6 class="card-title mb-1">Total Items</h6>
                    <h3 class="mb-0">{{ number_format($report['total_items']) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-sm-6 mb-3">
            <div class="card bg-info text-white h-100">
                <div class="card-body text-center">
                    <h6 class="card-title mb-1">Stock Units</h6>
                    <h3 class="mb-0">{{ number_format($report['total_stock_units']) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card bg-secondary text-white h-100">
                <div class="card-body text-center">
                    <h6 class="card-title mb-1">Cost Value</h6>
                    <h3 class="mb-0">RM {{ number_format($report['total_cost_value'], 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card bg-success text-white h-100">
                <div class="card-body text-center">
                    <h6 class="card-title mb-1">Retail Value</h6>
                    <h3 class="mb-0">RM {{ number_format($report['total_retail_value'], 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-sm-6 mb-3">
            <div class="card bg-warning text-dark h-100">
                <div class="card-body text-center">
                    <h6 class="card-title mb-1">Profit Margin</h6>
                    <h3 class="mb-0">{{ number_format($report['profit_margin'], 1) }}%</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Value by Category Chart -->
        <div class="col-lg-5 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-chart-pie me-2"></i>Value by Category</h5>
                </div>
                <div class="card-body">
                    <canvas id="categoryChart" height="250"></canvas>
                </div>
            </div>
        </div>

        <!-- Category Breakdown -->
        <div class="col-lg-7 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-tags me-2"></i>Category Breakdown</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Category</th>
                                    <th class="text-center">Items</th>
                                    <th class="text-center">Stock</th>
                                    <th class="text-end">Cost Value</th>
                                    <th class="text-end">Retail Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($report['by_category'] as $categoryName => $data)
                                <tr>
                                    <td><strong>{{ $categoryName }}</strong></td>
                                    <td class="text-center">{{ $data['items_count'] }}</td>
                                    <td class="text-center">{{ number_format($data['total_stock']) }}</td>
                                    <td class="text-end">RM {{ number_format($data['cost_value'], 2) }}</td>
                                    <td class="text-end text-success">RM {{ number_format($data['retail_value'], 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th>Total</th>
                                    <th class="text-center">{{ $report['total_items'] }}</th>
                                    <th class="text-center">{{ number_format($report['total_stock_units']) }}</th>
                                    <th class="text-end">RM {{ number_format($report['total_cost_value'], 2) }}</th>
                                    <th class="text-end text-success">RM {{ number_format($report['total_retail_value'], 2) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Item Details -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-list me-2"></i>Item Valuation Details
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-sm align-middle" id="valuationTable">
                    <thead class="table-light">
                        <tr>
                            <th>SKU</th>
                            <th>Item Name</th>
                            <th>Category</th>
                            <th class="text-center">Stock</th>
                            <th class="text-end">Cost Price</th>
                            <th class="text-end">Selling Price</th>
                            <th class="text-end">Cost Value</th>
                            <th class="text-end">Retail Value</th>
                            <th class="text-end">Profit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($report['items'] as $item)
                        @php
                            $costValue = $item->current_stock * $item->cost_price;
                            $retailValue = $item->current_stock * $item->selling_price;
                            $profit = $retailValue - $costValue;
                        @endphp
                        <tr>
                            <td><code>{{ $item->sku }}</code></td>
                            <td>
                                <a href="{{ route('admin.inventory.show', $item) }}" class="text-decoration-none">
                                    {{ $item->name }}
                                </a>
                            </td>
                            <td><small>{{ $item->category->name ?? 'N/A' }}</small></td>
                            <td class="text-center">
                                @if($item->current_stock == 0)
                                <span class="badge bg-danger">0</span>
                                @elseif($item->current_stock <= $item->reorder_level)
                                <span class="badge bg-warning text-dark">{{ $item->current_stock }}</span>
                                @else
                                <span class="badge bg-success">{{ $item->current_stock }}</span>
                                @endif
                            </td>
                            <td class="text-end">RM {{ number_format($item->cost_price, 2) }}</td>
                            <td class="text-end">RM {{ number_format($item->selling_price, 2) }}</td>
                            <td class="text-end">RM {{ number_format($costValue, 2) }}</td>
                            <td class="text-end text-success">RM {{ number_format($retailValue, 2) }}</td>
                            <td class="text-end {{ $profit >= 0 ? 'text-success' : 'text-danger' }}">
                                RM {{ number_format($profit, 2) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="6" class="text-end">Totals:</th>
                            <th class="text-end">RM {{ number_format($report['total_cost_value'], 2) }}</th>
                            <th class="text-end text-success">RM {{ number_format($report['total_retail_value'], 2) }}</th>
                            <th class="text-end text-success">RM {{ number_format($report['potential_profit'], 2) }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Category Chart
const chartData = @json($chartData);
const categoryCtx = document.getElementById('categoryChart').getContext('2d');
new Chart(categoryCtx, {
    type: 'pie',
    data: chartData,
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        const value = context.raw;
                        return context.label + ': RM ' + value.toLocaleString('en-MY', {minimumFractionDigits: 2});
                    }
                }
            }
        }
    }
});
</script>
@endpush
