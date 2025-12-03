@extends('layouts.app')

@section('title', 'Material Details')
@section('page-title', 'Material Details')

@section('content')
<div class="page-header">
    <h1><i class="fas fa-file-alt me-2"></i> Material Details</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.materials.index') }}">Materials</a></li>
            <li class="breadcrumb-item active">{{ $material->title }}</li>
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
                    @can('edit-materials')
                    <a href="{{ route('admin.materials.edit', $material) }}" class="btn btn-primary">
                        <i class="fas fa-edit me-1"></i> Edit
                    </a>
                    @endcan
                    @can('delete-materials')
                    <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                        <i class="fas fa-trash me-1"></i> Delete
                    </button>
                    @endcan
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted small mb-1">Title</label>
                        <p class="mb-0"><strong>{{ $material->title }}</strong></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small mb-1">Type</label>
                        <p class="mb-0">
                            <span class="badge bg-info">{{ ucfirst($material->type) }}</span>
                        </p>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label text-muted small mb-1">Description</label>
                    <p class="mb-0">{{ $material->description ?? 'No description provided' }}</p>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted small mb-1">Class</label>
                        <p class="mb-0">{{ $material->class->name ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small mb-1">Subject</label>
                        <p class="mb-0">{{ $material->subject->name ?? 'N/A' }}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted small mb-1">Teacher</label>
                        <p class="mb-0">{{ $material->teacher->user->name ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small mb-1">Publish Date</label>
                        <p class="mb-0">{{ $material->publish_date?->format('d M Y') ?? 'Not set' }}</p>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-md-4">
                        <label class="form-label text-muted small mb-1">File Type</label>
                        <p class="mb-0">{{ strtoupper($material->file_type) }}</p>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small mb-1">File Size</label>
                        <p class="mb-0">{{ number_format($material->file_size / 1024, 2) }} KB</p>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small mb-1">Downloadable</label>
                        <p class="mb-0">
                            @if($material->is_downloadable)
                                <span class="badge bg-success">Yes</span>
                            @else
                                <span class="badge bg-danger">No</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Access Statistics -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-chart-bar me-2"></i> Access Statistics
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3 mb-3">
                        <div class="stat-card">
                            <h4 class="mb-0">{{ $accessStats['total_students'] }}</h4>
                            <p class="text-muted small mb-0">Students with Access</p>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="stat-card">
                            <h4 class="mb-0">{{ $accessStats['total_views'] }}</h4>
                            <p class="text-muted small mb-0">Total Views</p>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="stat-card">
                            <h4 class="mb-0">{{ $accessStats['unique_viewers'] }}</h4>
                            <p class="text-muted small mb-0">Unique Viewers</p>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="stat-card">
                            <h4 class="mb-0">{{ number_format($accessStats['average_duration'] / 60, 1) }}m</h4>
                            <p class="text-muted small mb-0">Avg. View Time</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Viewers -->
        @if($recentViewers->count() > 0)
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-eye me-2"></i> Recent Viewers
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Viewed At</th>
                                <th>Duration</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentViewers as $view)
                            <tr>
                                <td>{{ $view->student->user->name }}</td>
                                <td>{{ $view->viewed_at->format('d M Y, h:i A') }}</td>
                                <td>
                                    @if($view->duration_seconds)
                                        {{ gmdate('i:s', $view->duration_seconds) }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Sidebar -->
    <div class="col-md-4">
        <!-- Status Card -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-info-circle me-2"></i> Status
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label text-muted small mb-1">Material Status</label>
                    <p class="mb-0">
                        @if($material->status == 'published')
                            <span class="badge bg-success">Published</span>
                        @elseif($material->status == 'draft')
                            <span class="badge bg-warning">Draft</span>
                        @else
                            <span class="badge bg-secondary">Archived</span>
                        @endif
                    </p>
                </div>

                <div class="mb-3">
                    <label class="form-label text-muted small mb-1">Approval Status</label>
                    <p class="mb-0">
                        @if($material->is_approved)
                            <span class="badge bg-success">Approved</span>
                        @else
                            <span class="badge bg-warning">Pending Approval</span>
                        @endif
                    </p>
                </div>

                @if($material->approved_by)
                <div class="mb-3">
                    <label class="form-label text-muted small mb-1">Approved By</label>
                    <p class="mb-0">{{ $material->approvedBy->name }}</p>
                </div>
                @endif

                @if($material->is_featured)
                <div class="alert alert-info mb-0">
                    <i class="fas fa-star me-2"></i> Featured Material
                </div>
                @endif
            </div>
        </div>

        <!-- Approval Actions -->
        @if(!$material->is_approved)
        <div class="card mb-4 border-warning">
            <div class="card-header bg-warning">
                <i class="fas fa-exclamation-triangle me-2"></i> Approval Required
            </div>
            <div class="card-body">
                <p class="mb-3">This material is waiting for approval.</p>
                <form method="POST" action="{{ route('admin.materials.approve', $material) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-success w-100">
                        <i class="fas fa-check me-1"></i> Approve Material
                    </button>
                </form>
            </div>
        </div>
        @endif

        <!-- File Actions -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-download me-2"></i> File Actions
            </div>
            <div class="card-body">
                <a href="{{ route('admin.materials.download', $material) }}" class="btn btn-primary w-100 mb-2">
                    <i class="fas fa-download me-1"></i> Download File
                </a>
                @if(in_array($material->file_type, ['pdf']))
                <button type="button" class="btn btn-outline-info w-100" onclick="previewFile()">
                    <i class="fas fa-eye me-1"></i> Preview File
                </button>
                @endif
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
                    <p class="mb-0 small">{{ $material->created_at->format('d M Y, h:i A') }}</p>
                </div>
                <div class="mb-0">
                    <small class="text-muted">Last Updated:</small>
                    <p class="mb-0 small">{{ $material->updated_at->format('d M Y, h:i A') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Form (Hidden) -->
<form id="deleteForm" method="POST" action="{{ route('admin.materials.destroy', $material) }}" style="display: none;">
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

function previewFile() {
    window.open('{{ route('admin.materials.download', $material) }}', '_blank');
}
</script>
@endpush
