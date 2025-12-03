@extends('layouts.app')

@section('title', 'Study Materials')
@section('page-title', 'Study Materials')

@section('content')
<div class="page-header">
    <h1><i class="fas fa-book me-2"></i> Study Materials</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Study Materials</li>
        </ol>
    </nav>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('student.materials.index') }}">
            <div class="row">
                <div class="col-md-5 mb-3 mb-md-0">
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
                    <select name="subject_id" class="form-select">
                        <option value="">All Subjects</option>
                        @foreach($classes as $class)
                            @if($class->subject)
                                <option value="{{ $class->subject->id }}" {{ request('subject_id') == $class->subject->id ? 'selected' : '' }}>
                                    {{ $class->subject->name }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1">
                    <div class="btn-group w-100">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i>
                        </button>
                        <a href="{{ route('student.materials.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-redo"></i>
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Materials Grid/List -->
<div class="card">
    <div class="card-header">
        <i class="fas fa-book-open me-2"></i> Available Materials
        <span class="badge bg-primary ms-2">{{ $materials->total() }} materials</span>
    </div>
    <div class="card-body">
        @if($materials->count() > 0)
        <div class="row">
            @foreach($materials as $material)
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="badge bg-{{ $material->type == 'notes' ? 'primary' : ($material->type == 'assignment' ? 'warning' : 'info') }}">
                                {{ ucfirst($material->type) }}
                            </span>
                            @if(!$material->is_downloadable)
                                <span class="badge bg-secondary">
                                    <i class="fas fa-eye me-1"></i>View Only
                                </span>
                            @endif
                        </div>

                        <h5 class="card-title">{{ $material->title }}</h5>

                        @if($material->description)
                            <p class="card-text text-muted small">{{ Str::limit($material->description, 80) }}</p>
                        @endif

                        <div class="mb-2">
                            <small class="text-muted">
                                <i class="fas fa-school me-1"></i>{{ $material->class->name ?? 'N/A' }}
                            </small>
                            <br>
                            <small class="text-muted">
                                <i class="fas fa-book me-1"></i>{{ $material->subject->name ?? 'N/A' }}
                            </small>
                            <br>
                            <small class="text-muted">
                                <i class="fas fa-user me-1"></i>{{ $material->teacher->user->name ?? 'N/A' }}
                            </small>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                <i class="fas fa-calendar me-1"></i>{{ $material->publish_date?->format('d M Y') ?? $material->created_at->format('d M Y') }}
                            </small>
                            <a href="{{ route('student.materials.view', $material) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-eye me-1"></i>View
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
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
            <p class="text-muted">No study materials have been published for your classes yet.</p>
        </div>
        @endif
    </div>
</div>
@endsection
