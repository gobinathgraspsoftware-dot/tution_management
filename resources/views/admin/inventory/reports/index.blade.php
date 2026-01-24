@extends('layouts.app')

@section('title', 'Inventory Reports')

@section('page-title', 'Inventory Reports')

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.inventory.index') }}">Inventory</a></li>
        <li class="breadcrumb-item active">Reports</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Statistics Overview -->
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
                            <h6 class="card-title mb-1">Total Stock Value</h6>
                            <h3 class="mb-0">RM {{ number_format($statistics['total_cost_value'], 0) }}</h3>
                        </div>
                        <i class="fas fa-money-bill-wave fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card bg-info text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-1">Retail Value</h6>
                            <h3 class="mb-0">RM {{ number_format($statistics['total_retail_value'], 0) }}</h3>
                        </div>
                        <i class="fas fa-tags fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card bg-warning text-dark h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-1">Potential Profit</h6>
                            <h3 class="mb-0">RM {{ number_format($statistics['potential_profit'], 0) }}</h3>
                        </div>
                        <i class="fas fa-chart-line fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Quick Report Links -->
        <div class="col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-file-alt me-2"></i>Available Reports</h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <a href="{{ route('admin.inventory.reports.movement') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-exchange-alt text-primary me-2"></i>
                                <strong>Movement Report</strong>
                                <p class="mb-0 small text-muted">Track stock additions, removals, and sales</p>
                            </div>
                            <i class="fas fa-chevron-right"></i>
                        </a>
                        <a href="{{ route('admin.inventory.reports.valuation') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-calculator text-success me-2"></i>
                                <strong>Valuation Report</strong>
                                <p class="mb-0 small text-muted">View inventory cost and retail values</p>
                            </div>
                            <i class="fas fa-chevron-right"></i>
                        </a>
                        <a href="{{ route('admin.inventory.low-stock') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                                <strong>Low Stock Report</strong>
                                <p class="mb-0 small text-muted">Items below reorder level</p>
                            </div>
                            <span class="badge bg-warning text-dark">{{ $stockSummary['low_stock_items'] + $stockSummary['out_of_stock_items'] }}</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stock Summary -->
        <div class="col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-chart-pie me-2"></i>Stock Summary</h5>
                </div>
                <div class="card-body">
                    <canvas id="stockStatusChart" height="200"></canvas>
                    <hr>
                    <div class="row text-center">
                        <div class="col-4">
                            <span class="badge bg-success d-block mb-1">Healthy</span>
                            <strong>{{ $stockSummary['healthy_stock'] }}</strong>
                        </div>
                        <div class="col-4">
                            <span class="badge bg-warning text-dark d-block mb-1">Low</span>
                            <strong>{{ $stockSummary['low_stock_items'] }}</strong>
                        </div>
                        <div class="col-4">
                            <span class="badge bg-danger d-block mb-1">Out</span>
                            <strong>{{ $stockSummary['out_of_stock_items'] }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-history me-2"></i>Recent Activity</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush" style="max-height: 350px; overflow-y: auto;">
                        @forelse($recentActivity as $activity)
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    @if($activity->type == 'add')
                                    <span class="badge bg-success me-1">+{{ $activity->quantity }}</span>
                                    @elseif(in_array($activity->type, ['remove', 'sale']))
                                    <span class="badge bg-danger me-1">-{{ $activity->quantity }}</span>
                                    @else
                                    <span class="badge bg-secondary me-1">{{ $activity->quantity }}</span>
                                    @endif
                                    <strong>{{ $activity->inventory->name ?? 'Unknown' }}</strong>
                                </div>
                                <small class="text-muted">{{ $activity->created_at->diffForHumans() }}</small>
                            </div>
                            <small class="text-muted">
                                {{ ucfirst($activity->type) }} by {{ $activity->createdBy->name ?? 'System' }}
                            </small>
                        </div>
                        @empty
                        <div class="list-group-item text-center text-muted py-4">
                            No recent activity
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reorder Suggestions -->
    @if(count($reorderSuggestions) > 0)
    <div class="card mb-4">
        <div class="card-header bg-warning text-dark">
            <h5 class="card-title mb-0"><i class="fas fa-shopping-cart me-2"></i>Reorder Suggestions ({{ count($reorderSuggestions) }} items)</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Item</th>
                            <th class="text-center">Current</th>
                            <th class="text-center">Suggested Qty</th>
                            <th class="text-end">Est. Cost</th>
                            <th class="text-center">Priority</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(array_slice($reorderSuggestions, 0, 5) as $suggestion)
                        <tr>
                            <td>{{ $suggestion['item']->name }}</td>
                            <td class="text-center">{{ $suggestion['current_stock'] }}</td>
                            <td class="text-center"><strong>{{ $suggestion['suggested_quantity'] }}</strong></td>
                            <td class="text-end">RM {{ number_format($suggestion['estimated_cost'], 2) }}</td>
                            <td class="text-center">
                                <span class="badge bg-{{ $suggestion['priority'] == 'critical' ? 'danger' : ($suggestion['priority'] == 'high' ? 'warning text-dark' : 'info') }}">
                                    {{ strtoupper($suggestion['priority']) }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if(count($reorderSuggestions) > 5)
            <div class="text-center mt-3">
                <a href="{{ route('admin.inventory.low-stock') }}" class="btn btn-outline-warning">
                    View All {{ count($reorderSuggestions) }} Suggestions
                </a>
            </div>
            @endif
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Stock Status Chart
const stockCtx = document.getElementById('stockStatusChart').getContext('2d');
new Chart(stockCtx, {
    type: 'doughnut',
    data: {
        labels: ['Healthy', 'Low Stock', 'Out of Stock'],
        datasets: [{
            data: [
                {{ $stockSummary['healthy_stock'] }},
                {{ $stockSummary['low_stock_items'] }},
                {{ $stockSummary['out_of_stock_items'] }}
            ],
            backgroundColor: [
                'rgba(40, 167, 69, 0.8)',
                'rgba(255, 193, 7, 0.8)',
                'rgba(220, 53, 69, 0.8)'
            ],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false
            }
        }
    }
});
</script>
@endpush
