@extends('layouts.app')

@section('title', 'Physical Material Details')
@section('page-title', 'Physical Material Details')

@section('content')
<div class="page-header">
    <h1><i class="fas fa-box me-2"></i> Physical Material Details</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.physical-materials.index') }}">Physical Materials</a></li>
            <li class="breadcrumb-item active">{{ $physicalMaterial->name }}</li>
        </ol>
    </nav>
</div>

<div class="row">
    <!-- Main Content -->
    <div class="col-md-8">
        <!-- Material Information -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-info-circle me-2"></i> Material Information</span>
                <div class="btn-group btn-group-sm">
                    @can('edit-physical-materials')
                    <a href="{{ route('admin.physical-materials.edit', $physicalMaterial) }}" class="btn btn-primary">
                        <i class="fas fa-edit me-1"></i> Edit
                    </a>
                    @endcan
                    @can('manage-collections')
                    <a href="{{ route('admin.physical-materials.collections', $physicalMaterial) }}" class="btn btn-success">
                        <i class="fas fa-clipboard-check me-1"></i> Manage Collections
                    </a>
                    @endcan
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label class="form-label text-muted small mb-1">Material Name</label>
                        <h4 class="mb-0">{{ $physicalMaterial->name }}</h4>
                    </div>
                </div>

                @if($physicalMaterial->description)
                <div class="mb-3">
                    <label class="form-label text-muted small mb-1">Description</label>
                    <p class="mb-0">{{ $physicalMaterial->description }}</p>
                </div>
                @endif

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted small mb-1">Subject</label>
                        <p class="mb-0">{{ $physicalMaterial->subject->name ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small mb-1">Grade Level</label>
                        <p class="mb-0">{{ $physicalMaterial->grade_level ?? 'Not specified' }}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted small mb-1">Month/Year</label>
                        <p class="mb-0">
                            @if($physicalMaterial->month && $physicalMaterial->year)
                                {{ $physicalMaterial->month }} {{ $physicalMaterial->year }}
                            @else
                                Not specified
                            @endif
                        </p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small mb-1">Status</label>
                        <p class="mb-0">
                            @if($physicalMaterial->status == 'available')
                                <span class="badge bg-success">Available</span>
                            @elseif($physicalMaterial->status == 'low_stock')
                                <span class="badge bg-warning">Low Stock</span>
                            @else
                                <span class="badge bg-danger">Out of Stock</span>
                            @endif
                        </p>
                    </div>
                </div>

                <hr>

                <h6 class="mb-3">Quantity Information</h6>
                <div class="row">
                    <div class="col-md-4">
                        <div class="text-center p-3 bg-light rounded">
                            <h3 class="mb-0">{{ $physicalMaterial->quantity_total }}</h3>
                            <small class="text-muted">Total Quantity</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center p-3 bg-light rounded">
                            <h3 class="mb-0">{{ $physicalMaterial->quantity_available }}</h3>
                            <small class="text-muted">Available</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center p-3 bg-light rounded">
                            <h3 class="mb-0">{{ $physicalMaterial->quantity_total - $physicalMaterial->quantity_available }}</h3>
                            <small class="text-muted">Collected</small>
                        </div>
                    </div>
                </div>

                @if($physicalMaterial->quantity_available <= $physicalMaterial->minimum_quantity)
                <div class="alert alert-warning mt-3 mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Low Stock Alert:</strong> Current quantity ({{ $physicalMaterial->quantity_available }}) is at or below minimum level ({{ $physicalMaterial->minimum_quantity }}).
                </div>
                @endif
            </div>
        </div>

        <!-- Collection Statistics -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-chart-bar me-2"></i> Collection Statistics
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-4">
                        <h4 class="mb-0">{{ $stats['total_collections'] }}</h4>
                        <p class="text-muted small mb-0">Total Collections</p>
                    </div>
                    <div class="col-md-4">
                        <h4 class="mb-0">{{ $stats['collections_this_month'] }}</h4>
                        <p class="text-muted small mb-0">This Month</p>
                    </div>
                    <div class="col-md-4">
                        <h4 class="mb-0">{{ $stats['pending_students'] }}</h4>
                        <p class="text-muted small mb-0">Pending Students</p>
                    </div>
                </div>

                @if($stats['total_collections'] > 0)
                <hr>
                <div class="progress" style="height: 25px;">
                    @php
                        $collectedPercentage = ($physicalMaterial->quantity_total > 0)
                            ? (($physicalMaterial->quantity_total - $physicalMaterial->quantity_available) / $physicalMaterial->quantity_total * 100)
                            : 0;
                    @endphp
                    <div class="progress-bar bg-success" role="progressbar"
                         style="width: {{ $collectedPercentage }}%;"
                         aria-valuenow="{{ $collectedPercentage }}" aria-valuemin="0" aria-valuemax="100">
                        {{ number_format($collectedPercentage, 1) }}% Collected
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Recent Collections -->
        @if($physicalMaterial->collections()->count() > 0)
        <div class="card">
            <div class="card-header">
                <i class="fas fa-history me-2"></i> Recent Collections
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Collected By</th>
                                <th>Date</th>
                                <th>Staff</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($physicalMaterial->collections()->latest('collected_at')->limit(10)->get() as $collection)
                            <tr>
                                <td>{{ $collection->student->user->name }}</td>
                                <td>{{ $collection->collected_by_name }}</td>
                                <td>{{ $collection->collected_at->format('d M Y, h:i A') }}</td>
                                <td>{{ $collection->staff->user->name ?? 'N/A' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <a href="{{ route('admin.physical-materials.collections', $physicalMaterial) }}" class="btn btn-sm btn-outline-primary">
                    View All Collections <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
        @endif
    </div>

    <!-- Sidebar -->
    <div class="col-md-4">
        <!-- Quick Actions -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-bolt me-2"></i> Quick Actions
            </div>
            <div class="card-body">
                @can('manage-collections')
                <a href="{{ route('admin.physical-materials.collections', $physicalMaterial) }}" class="btn btn-success w-100 mb-2">
                    <i class="fas fa-clipboard-check me-1"></i> Record Collection
                </a>
                @endcan
                @can('edit-physical-materials')
                <a href="{{ route('admin.physical-materials.edit', $physicalMaterial) }}" class="btn btn-primary w-100 mb-2">
                    <i class="fas fa-edit me-1"></i> Edit Material
                </a>
                @endcan
                @can('delete-physical-materials')
                <button type="button" class="btn btn-danger w-100" onclick="confirmDelete()">
                    <i class="fas fa-trash me-1"></i> Delete Material
                </button>
                @endcan
            </div>
        </div>

        <!-- Status Summary -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-info-circle me-2"></i> Status Summary
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Total Quantity:</span>
                    <strong>{{ $physicalMaterial->quantity_total }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Available:</span>
                    <strong class="text-success">{{ $physicalMaterial->quantity_available }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Collected:</span>
                    <strong class="text-primary">{{ $physicalMaterial->quantity_total - $physicalMaterial->quantity_available }}</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Min. Quantity:</span>
                    <strong class="text-warning">{{ $physicalMaterial->minimum_quantity }}</strong>
                </div>
            </div>
        </div>

        <!-- Metadata -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-clock me-2"></i> Metadata
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <small class="text-muted">Created:</small>
                    <p class="mb-0 small">{{ $physicalMaterial->created_at->format('d M Y, h:i A') }}</p>
                </div>
                <div class="mb-0">
                    <small class="text-muted">Last Updated:</small>
                    <p class="mb-0 small">{{ $physicalMaterial->updated_at->format('d M Y, h:i A') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Form (Hidden) -->
<form id="deleteForm" method="POST" action="{{ route('admin.physical-materials.destroy', $physicalMaterial) }}" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endsection

@push('scripts')
<script>
function confirmDelete() {
    if (confirm('Are you sure you want to delete this material? This action cannot be undone.')) {
        document.getElementById('deleteForm').submit();
    }
}
</script>
@endpush
