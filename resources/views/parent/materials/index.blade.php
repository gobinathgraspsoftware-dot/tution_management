@extends('layouts.app')

@section('title', 'Study Materials')
@section('page-title', 'Study Materials')

@section('content')
<div class="page-header">
    <h1><i class="fas fa-book me-2"></i> Children's Study Materials</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('parent.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Study Materials</li>
        </ol>
    </nav>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('parent.materials.index') }}">
            <div class="row">
                <div class="col-md-3 mb-3 mb-md-0">
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
                    <select name="student_id" class="form-select">
                        <option value="">All Children</option>
                        @foreach($children as $child)
                            <option value="{{ $child->id }}" {{ request('student_id') == $child->id ? 'selected' : '' }}>
                                {{ $child->user->name }}
                            </option>
                        @endforeach
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
                        <a href="{{ route('parent.materials.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-redo"></i>
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Materials List -->
<div class="card">
    <div class="card-header">
        <i class="fas fa-list me-2"></i> Study Materials
        <span class="badge bg-primary ms-2">{{ $materials->total() }} materials</span>
    </div>
    <div class="card-body">
        @if($materials->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Class/Subject</th>
                        <th>Type</th>
                        <th>Teacher</th>
                        <th>Published</th>
                        <th>Download</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($materials as $material)
                    <tr>
                        <td>
                            <strong>{{ $material->title }}</strong>
                            @if($material->description)
                                <br><small class="text-muted">{{ Str::limit($material->description, 60) }}</small>
                            @endif
                        </td>
                        <td>
                            {{ $material->class->name ?? 'N/A' }}
                            <br><small class="text-muted">{{ $material->subject->name ?? 'N/A' }}</small>
                        </td>
                        <td>
                            <span class="badge bg-info">{{ ucfirst($material->type) }}</span>
                            @if(!$material->is_downloadable)
                                <br><span class="badge bg-secondary mt-1">View Only</span>
                            @endif
                        </td>
                        <td>{{ $material->teacher->user->name ?? 'N/A' }}</td>
                        <td>{{ $material->publish_date?->format('d M Y') ?? $material->created_at->format('d M Y') }}</td>
                        <td>
                            @if($material->is_downloadable)
                                <a href="{{ route('admin.materials.download', $material) }}"
                                   class="btn btn-sm btn-success" target="_blank">
                                    <i class="fas fa-download me-1"></i> Download
                                </a>
                            @else
                                <span class="badge bg-secondary">View Only</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
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
        @else
        <div class="text-center py-5">
            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">No Materials Available</h5>
            <p class="text-muted">No study materials have been published for your children's classes yet.</p>
        </div>
        @endif
    </div>
</div>

<!-- Information Card -->
<div class="card mt-4">
    <div class="card-header bg-info text-white">
        <i class="fas fa-info-circle me-2"></i> Information for Parents
    </div>
    <div class="card-body">
        <h6>About Study Materials:</h6>
        <ul class="mb-0">
            <li>View all study materials published for your children's classes</li>
            <li>"View Only" materials cannot be downloaded but can be viewed online</li>
            <li>Downloadable materials can be saved to your device</li>
            <li>Filter by child, class, or material type to find specific content</li>
            <li>Contact your child's teacher if you have questions about any material</li>
        </ul>
    </div>
</div>
@endsection
