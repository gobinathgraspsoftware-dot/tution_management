@extends('layouts.app')

@section('title', 'My Materials')
@section('page-title', 'My Materials')

@section('content')
<div class="page-header">
    <h1><i class="fas fa-file-upload me-2"></i> My Materials</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('teacher.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">My Materials</li>
        </ol>
    </nav>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #e3f2fd; color: #2196f3;">
                <i class="fas fa-file-alt"></i>
            </div>
            <div class="stat-details">
                <h3 class="mb-0">{{ $stats['total'] }}</h3>
                <p class="text-muted mb-0">Total Materials</p>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #e8f5e9; color: #4caf50;">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-details">
                <h3 class="mb-0">{{ $stats['published'] }}</h3>
                <p class="text-muted mb-0">Published</p>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #fff3e0; color: #ff9800;">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-details">
                <h3 class="mb-0">{{ $stats['pending_approval'] }}</h3>
                <p class="text-muted mb-0">Pending Approval</p>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #f3e5f5; color: #9c27b0;">
                <i class="fas fa-file"></i>
            </div>
            <div class="stat-details">
                <h3 class="mb-0">{{ $stats['draft'] }}</h3>
                <p class="text-muted mb-0">Drafts</p>
            </div>
        </div>
    </div>
</div>

<!-- Filters & Actions -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('teacher.materials.index') }}">
            <div class="row">
                <div class="col-md-4 mb-3 mb-md-0">
                    <input type="text" name="search" class="form-control"
                           placeholder="Search materials..."
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="type" class="form-select">
                        <option value="">All Types</option>
                        <option value="notes" {{ request('type') == 'notes' ? 'selected' : '' }}>Notes</option>
                        <option value="presentation" {{ request('type') == 'presentation' ? 'selected' : '' }}>Presentation</option>
                        <option value="worksheet" {{ request('type') == 'worksheet' ? 'selected' : '' }}>Worksheet</option>
                        <option value="assignment" {{ request('type') == 'assignment' ? 'selected' : '' }}>Assignment</option>
                        <option value="reference" {{ request('type') == 'reference' ? 'selected' : '' }}>Reference</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                        <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>Archived</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="class_id" class="form-select">
                        <option value="">All Classes</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                {{ $class->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <div class="btn-group w-100">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i>
                        </button>
                        <a href="{{ route('teacher.materials.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-redo"></i>
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Materials Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-list me-2"></i> My Uploaded Materials</span>
        <a href="{{ route('teacher.materials.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i> Upload New Material
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>Class/Subject</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Approval</th>
                        <th>Uploaded</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($materials as $material)
                        <tr>
                            <td>{{ $loop->iteration + $materials->firstItem() - 1 }}</td>
                            <td>
                                <strong>{{ $material->title }}</strong>
                                @if($material->description)
                                    <br><small class="text-muted">{{ Str::limit($material->description, 40) }}</small>
                                @endif
                            </td>
                            <td>
                                {{ $material->class->name ?? 'N/A' }}
                                <br><small class="text-muted">{{ $material->subject->name ?? 'N/A' }}</small>
                            </td>
                            <td>
                                <span class="badge bg-info">{{ ucfirst($material->type) }}</span>
                            </td>
                            <td>
                                @if($material->status == 'published')
                                    <span class="badge bg-success">Published</span>
                                @elseif($material->status == 'draft')
                                    <span class="badge bg-warning">Draft</span>
                                @else
                                    <span class="badge bg-secondary">Archived</span>
                                @endif
                            </td>
                            <td>
                                @if($material->is_approved)
                                    <span class="badge bg-success">
                                        <i class="fas fa-check me-1"></i>Approved
                                    </span>
                                @else
                                    <span class="badge bg-warning">
                                        <i class="fas fa-clock me-1"></i>Pending
                                    </span>
                                @endif
                            </td>
                            <td>{{ $material->created_at->format('d M Y') }}</td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('teacher.materials.show', $material) }}"
                                       class="btn btn-outline-info" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('teacher.materials.edit', $material) }}"
                                       class="btn btn-outline-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-danger" title="Delete"
                                            onclick="confirmDelete('{{ $material->id }}', '{{ $material->title }}')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No materials uploaded yet.</p>
                                <a href="{{ route('teacher.materials.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-1"></i> Upload Your First Material
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center mt-4">
            <div class="text-muted">
                Showing {{ $materials->firstItem() ?? 0 }} to {{ $materials->lastItem() ?? 0 }} of {{ $materials->total() }} entries
            </div>
            {{ $materials->links() }}
        </div>
    </div>
</div>

<!-- Delete Form (Hidden) -->
<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endsection

@push('scripts')
<script>
function confirmDelete(id, title) {
    if (confirm(`Are you sure you want to delete "${title}"? This action cannot be undone.`)) {
        const form = document.getElementById('deleteForm');
        form.action = `/teacher/materials/${id}`;
        form.submit();
    }
}
</script>
@endpush
