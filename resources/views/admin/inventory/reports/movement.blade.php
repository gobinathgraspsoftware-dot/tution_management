@extends('layouts.app')

@section('title', 'Movement Report')

@section('page-title', 'Stock Movement Report')

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.inventory.index') }}">Inventory</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.inventory.reports') }}">Reports</a></li>
        <li class="breadcrumb-item active">Movement Report</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('admin.inventory.reports.movement') }}" method="GET">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start_date" class="form-control" value="{{ $startDate->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end_date" class="form-control" value="{{ $endDate->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-3">
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
                    <div class="col-md-3">
                        <div class="btn-group w-100">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter me-1"></i> Generate
                            </button>
                            <a href="{{ route('admin.inventory.reports.movement') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-redo me-1"></i> Reset
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-1">Total Added</h6>
                            <h3 class="mb-0">{{ number_format($report['summary']['total_added']) }}</h3>
                        </div>
                        <i class="fas fa-plus-circle fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card bg-warning text-dark h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-1">Total Removed</h6>
                            <h3 class="mb-0">{{ number_format($report['summary']['total_removed']) }}</h3>
                        </div>
                        <i class="fas fa-minus-circle fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card bg-info text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-1">Total Sales</h6>
                            <h3 class="mb-0">{{ number_format($report['summary']['total_sales']) }}</h3>
                        </div>
                        <i class="fas fa-shopping-cart fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card bg-secondary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-1">Adjustments</h6>
                            <h3 class="mb-0">{{ number_format($report['summary']['total_adjusted']) }}</h3>
                        </div>
                        <i class="fas fa-edit fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Movement Chart -->
        <div class="col-lg-8 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><i class="fas fa-chart-bar me-2"></i>Daily Movement</h5>
                    <a href="{{ route('admin.inventory.reports.export-movement', request()->query()) }}" class="btn btn-sm btn-success">
                        <i class="fas fa-file-excel me-1"></i> Export
                    </a>
                </div>
                <div class="card-body">
                    <canvas id="movementChart" height="120"></canvas>
                </div>
            </div>
        </div>

        <!-- Movement by Type -->
        <div class="col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-chart-pie me-2"></i>By Type</h5>
                </div>
                <div class="card-body">
                    <canvas id="typeChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Movement Details -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-list me-2"></i>Movement Details
                <span class="badge bg-secondary ms-2">{{ $report['details']->count() }} records</span>
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-sm align-middle" id="movementTable">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Item</th>
                            <th>Category</th>
                            <th class="text-center">Type</th>
                            <th class="text-center">Qty</th>
                            <th class="text-center">Previous</th>
                            <th class="text-center">New</th>
                            <th>Reference</th>
                            <th>By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($report['details'] as $log)
                        <tr>
                            <td><small>{{ $log->created_at->format('d M Y, h:i A') }}</small></td>
                            <td>
                                <a href="{{ route('admin.inventory.show', $log->inventory) }}" class="text-decoration-none">
                                    {{ $log->inventory->name ?? 'N/A' }}
                                </a>
                            </td>
                            <td><small>{{ $log->inventory->category->name ?? 'N/A' }}</small></td>
                            <td class="text-center">
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
                            <td colspan="9" class="text-center py-4 text-muted">
                                No movement data found for the selected period.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Movement Chart
const chartData = @json($chartData);
const movementCtx = document.getElementById('movementChart').getContext('2d');
new Chart(movementCtx, {
    type: 'bar',
    data: chartData,
    options: {
        responsive: true,
        scales: {
            x: { stacked: false },
            y: { beginAtZero: true }
        },
        plugins: {
            legend: { position: 'top' }
        }
    }
});

// Type Chart
const typeData = @json($report['by_type']);
const typeLabels = Object.keys(typeData);
const typeValues = typeLabels.map(key => typeData[key].total_quantity);
const typeColors = {
    'add': 'rgba(40, 167, 69, 0.8)',
    'remove': 'rgba(255, 193, 7, 0.8)',
    'sale': 'rgba(0, 123, 255, 0.8)',
    'adjust': 'rgba(108, 117, 125, 0.8)'
};

const typeCtx = document.getElementById('typeChart').getContext('2d');
new Chart(typeCtx, {
    type: 'doughnut',
    data: {
        labels: typeLabels.map(l => l.charAt(0).toUpperCase() + l.slice(1)),
        datasets: [{
            data: typeValues,
            backgroundColor: typeLabels.map(l => typeColors[l] || 'rgba(128, 128, 128, 0.8)')
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'bottom' }
        }
    }
});
</script>
@endpush
