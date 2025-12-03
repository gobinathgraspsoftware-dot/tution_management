@extends('layouts.app')

@section('title', 'Digital Materials Management')
@section('page-title', 'Digital Materials Management')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="fas fa-file-alt me-2"></i> Digital Materials Management</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Materials</li>
            </ol>
        </nav>
    </div>
    <div>
        @can('create-materials')
        <a href="{{ route('admin.materials.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Add Material
        </a>
        @endcan
    </div>
</div>

<!-- Statistics Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1 small">Total Materials</p>
                        <h3 class="mb-0">{{ $stats['total'] }}</h3>
                    </div>
                    <div class="stat-icon" style="background: rgba(76, 175, 80, 0.1); color: #4caf50;">
                        <i class="fas fa-file-alt"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1 small">Published</p>
                        <h3 class="mb-0">{{ $stats['published'] }}</h3>
                    </div>
                    <div class="stat-icon" style="background: rgba(33, 150, 243, 0.1); color: #2196f3;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1 small">Pending Approval</p>
                        <h3 class="mb-0">{{ $stats['pending_approval'] }}</h3>
                    </div>
                    <div class="stat-icon" style="background: rgba(255, 152, 0, 0.1); color: #ff9800;">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1 small">Draft</p>
                        <h3 class="mb-0">{{ $stats['draft'] }}</h3>
                    </div>
                    <div class="stat-icon" style="background: rgba(158, 158, 158, 0.1); color: #9e9e9e;">
                        <i class="fas fa-edit"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('admin.materials.index') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Title, description..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Type</label>
                <select name="type" class="form-select">
                    <option value="">All Types</option>
                    <option value="notes" {{ request('type') == 'notes' ? 'selected' : '' }}>Notes</option>
                    <option value="presentation" {{ request('type') == 'presentation' ? 'selected' : '' }}>Presentation</option>
                    <option value="video" {{ request('type') == 'video' ? 'selected' : '' }}>Video</option>
                    <option value="document" {{ request('type') == 'document' ? 'selected' : '' }}>Document</option>
                    <option value="other" {{ request('type') == 'other' ? 'selected' : '' }}>Other</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Approval</label>
                <select name="is_approved" class="form-select">
                    <option value="">All</option>
                    <option value="1" {{ request('is_approved') == '1' ? 'selected' : '' }}>Approved</option>
                    <option value="0" {{ request('is_approved') == '0' ? 'selected' : '' }}>Pending</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Class</label>
                <select name="class_id" class="form-select">
                    <option value="">All Classes</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                            {{ $class->name }} ({{ $class->subject->name }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="fas fa-search me-1"></i> Search
                </button>
                <a href="{{ route('admin.materials.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-redo me-1"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Materials Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Title</th>
                        <th>Class</th>
                        <th>Subject</th>
                        <th>Teacher</th>
                        <th>Type</th>
                        <th>Access</th>
                        <th>Approval</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($materials as $material)
                        <tr>
                            <td>
                                <strong>{{ $material->title }}</strong>
                                <br>
                                <small class="text-muted">
                                    <i class="fas fa-calendar me-1"></i>
                                    {{ $material->publish_date ? $material->publish_date->format('M d, Y') : 'Not published' }}
                                </small>
                            </td>
                            <td>{{ $material->class->name }}</td>
                            <td>{{ $material->subject->name }}</td>
                            <td>{{ $material->teacher->user->name }}</td>
                            <td>
                                <span class="badge bg-secondary">{{ ucfirst($material->type) }}</span>
                            </td>
                            <td>
                                @if($material->access_type == 'view_only')
                                    <span class="badge bg-info">View Only</span>
                                @else
                                    <span class="badge bg-success">Downloadable</span>
                                @endif
                            </td>
                            <td>
                                @if($material->is_approved)
                                    <span class="badge bg-success">
                                        <i class="fas fa-check me-1"></i> Approved
                                    </span>
                                @else
                                    <span class="badge bg-warning text-dark">
                                        <i class="fas fa-clock me-1"></i> Pending
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($material->status == 'published')
                                    <span class="badge bg-success">Published</span>
                                @else
                                    <span class="badge bg-secondary">Draft</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    @can('view-materials')
                                    <a href="{{ route('admin.materials.show', $material) }}" class="btn btn-outline-primary" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @endcan

                                    @can('edit-materials')
                                    <a href="{{ route('admin.materials.edit', $material) }}" class="btn btn-outline-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endcan

                                    @if(!$material->is_approved)
                                        @can('approve-materials')
                                        <form action="{{ route('admin.materials.approve', $material) }}" method="POST" style="display: inline;">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-outline-success" title="Approve">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                        @endcan
                                    @endif

                                    @can('delete-materials')
                                    <form action="{{ route('admin.materials.destroy', $material) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <p class="text-muted mb-0">No materials found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-4">
            {{ $materials->links() }}
        </div>
    </div>
</div>
@endsection
