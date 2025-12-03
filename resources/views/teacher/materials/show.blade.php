@extends('layouts.app')

@section('title', 'Material Details')
@section('page-title', 'Material Details')

@section('content')
<div class="page-header">
    <h1><i class="fas fa-file-alt me-2"></i> Material Details</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('teacher.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('teacher.materials.index') }}">My Materials</a></li>
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
                    <a href="{{ route('teacher.materials.edit', $material) }}" class="btn btn-primary">
                        <i class="fas fa-edit me-1"></i> Edit
                    </a>
                    <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                        <i class="fas fa-trash me-1"></i> Delete
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-8">
                        <label class="form-label text-muted small mb-1">Title</label>
                        <h4 class="mb-0">{{ $material->title }}</h4>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small mb-1">Type</label>
                        <p class="mb-0"><span class="badge bg-info">{{ ucfirst($material->type) }}</span></p>
                    </div>
                </div>

                @if($material->description)
                <div class="mb-3">
                    <label class="form-label text-muted small mb-1">Description</label>
                    <p class="mb-0">{{ $material->description }}</p>
                </div>
                @endif

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
                        <label class="form-label text-muted small mb-1">Status</label>
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
                                <span class="badge bg-danger">No (View Only)</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Access Statistics -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-chart-bar me-2"></i> Student Engagement
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-4 mb-3">
                        <h3 class="mb-0">{{ $accessStats['total_students'] }}</h3>
                        <p class="text-muted small mb-0">Students with Access</p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <h3 class="mb-0">{{ $accessStats['total_views'] }}</h3>
                        <p class="text-muted small mb-0">Total Views</p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <h3 class="mb-0">{{ $accessStats['unique_viewers'] }}</h3>
                        <p class="text-muted small mb-0">Unique Viewers</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Viewers -->
        @if($recentViewers->count() > 0)
        <div class="card">
            <div class="card-header">
                <i class="fas fa-eye me-2"></i> Recent Student Views
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
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
        <!-- Approval Status -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-check-circle me-2"></i> Approval Status
            </div>
            <div class="card-body">
                @if($material->is_approved)
                    <div class="alert alert-success mb-3">
                        <i class="fas fa-check-circle me-2"></i>
                        <strong>Approved</strong>
                        @if($material->approvedBy)
                            <br><small>By: {{ $material->approvedBy->name }}</small>
                        @endif
                    </div>
                @else
                    <div class="alert alert-warning mb-3">
                        <i class="fas fa-clock me-2"></i>
                        <strong>Pending Approval</strong>
                        <br><small>Waiting for admin review</small>
                    </div>
                @endif

                <p class="mb-0 small text-muted">
                    Uploaded: {{ $material->created_at->format('d M Y, h:i A') }}
                </p>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-bolt me-2"></i> Quick Actions
            </div>
            <div class="card-body">
                <a href="{{ route('teacher.materials.edit', $material) }}" class="btn btn-primary w-100 mb-2">
                    <i class="fas fa-edit me-1"></i> Edit Material
                </a>
                <button type="button" class="btn btn-danger w-100" onclick="confirmDelete()">
                    <i class="fas fa-trash me-1"></i> Delete Material
                </button>
            </div>
        </div>

        <!-- Material Settings -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-cog me-2"></i> Settings
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Status:</span>
                    <strong>{{ ucfirst($material->status) }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Downloadable:</span>
                    <strong>{{ $material->is_downloadable ? 'Yes' : 'No' }}</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Featured:</span>
                    <strong>{{ $material->is_featured ? 'Yes' : 'No' }}</strong>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Form (Hidden) -->
<form id="deleteForm" method="POST" action="{{ route('teacher.materials.destroy', $material) }}" style="display: none;">
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
