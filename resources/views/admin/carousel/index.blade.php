@extends('layouts.app')

@section('title', 'Carousel Images')
@section('page-title', 'Carousel Images')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h1>Carousel Images</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Carousel Images</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('admin.carousel.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Add Image
        </a>
    </div>
</div>

{{-- Storage Link Warning --}}
@if(!file_exists(public_path('storage')))
<div class="alert alert-warning alert-dismissible fade show" role="alert">
    <i class="fas fa-exclamation-triangle me-2"></i>
    <strong>Storage link not found!</strong> Images will not display until you run:
    <code>php artisan storage:link</code> in your project terminal.
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- Filters --}}
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.carousel.index') }}" class="row g-3 align-items-end">
            <div class="col-md-5">
                <label for="search" class="form-label">Search</label>
                <input type="text" name="search" id="search" class="form-control"
                       placeholder="Search by title or caption..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <label for="status" class="form-label">Status</label>
                <select name="status" id="status" class="form-select">
                    <option value="">All</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-secondary">
                    <i class="fas fa-search me-1"></i> Filter
                </button>
                <a href="{{ route('admin.carousel.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-undo me-1"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Stats Row --}}
<div class="row mb-4">
    <div class="col-md-4">
        <div class="stat-card text-center">
            <div class="stat-icon mx-auto" style="background-color: rgba(13,110,253,0.1); color: #0d6efd;">
                <i class="fas fa-images"></i>
            </div>
            <h3>{{ \App\Models\CarouselImage::count() }}</h3>
            <small class="text-muted">Total Images</small>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card text-center">
            <div class="stat-icon mx-auto" style="background-color: rgba(25,135,84,0.1); color: #198754;">
                <i class="fas fa-check-circle"></i>
            </div>
            <h3>{{ \App\Models\CarouselImage::active()->count() }}</h3>
            <small class="text-muted">Active</small>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card text-center">
            <div class="stat-icon mx-auto" style="background-color: rgba(220,53,69,0.1); color: #dc3545;">
                <i class="fas fa-eye-slash"></i>
            </div>
            <h3>{{ \App\Models\CarouselImage::where('is_active', false)->count() }}</h3>
            <small class="text-muted">Inactive</small>
        </div>
    </div>
</div>

{{-- Image Grid --}}
<div class="row">
    @forelse($carouselImages as $image)
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card h-100 position-relative">
            {{-- Status Badge --}}
            <span class="badge {{ $image->is_active ? 'bg-success' : 'bg-secondary' }} position-absolute top-0 end-0 m-2"
                  style="z-index:2;">
                {{ $image->is_active ? 'Active' : 'Inactive' }}
            </span>

            {{-- Order Badge --}}
            <span class="badge bg-dark position-absolute top-0 start-0 m-2" style="z-index:2;">
                #{{ $image->sort_order }}
            </span>

            {{-- Image --}}
            <div style="height:200px; overflow:hidden; background:#f0f0f0; border-radius:10px 10px 0 0; display:flex; align-items:center; justify-content:center;">
                @if($image->image_url)
                    <img src="{{ $image->image_url }}"
                         alt="{{ $image->title ?? 'Carousel Image' }}"
                         class="w-100 h-100"
                         style="object-fit:cover;">
                @else
                    <div class="text-center text-muted">
                        <i class="fas fa-image fa-3x mb-2"></i>
                        <p class="small mb-0">Image not found</p>
                        <p class="small text-danger mb-0">Run: php artisan storage:link</p>
                    </div>
                @endif
            </div>

            <div class="card-body pb-2">
                <h6 class="card-title mb-1">{{ $image->title ?? 'Untitled' }}</h6>
                @if($image->caption)
                    <p class="text-muted small mb-2">{{ Str::limit($image->caption, 80) }}</p>
                @endif
                <div class="small text-muted">
                    <i class="fas fa-user me-1"></i>{{ $image->createdBy->name ?? 'System' }}
                    &middot;
                    <i class="fas fa-clock me-1"></i>{{ $image->created_at->format('d M Y') }}
                </div>
            </div>

            <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center">
                {{-- Toggle Status --}}
                <form action="{{ route('admin.carousel.toggle-status', $image) }}" method="POST" class="d-inline">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-sm {{ $image->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}"
                            title="{{ $image->is_active ? 'Deactivate' : 'Activate' }}">
                        <i class="fas {{ $image->is_active ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                    </button>
                </form>

                <div class="d-flex gap-1">
                    {{-- Edit --}}
                    <a href="{{ route('admin.carousel.edit', $image) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                        <i class="fas fa-edit"></i>
                    </a>

                    {{-- Delete --}}
                    <form action="{{ route('admin.carousel.destroy', $image) }}" method="POST" class="d-inline"
                          onsubmit="return confirm('Are you sure you want to delete this carousel image? This action cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-images fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No Carousel Images Found</h5>
                <p class="text-muted">Upload your first banner image for the homepage carousel.</p>
                <a href="{{ route('admin.carousel.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> Add Image
                </a>
            </div>
        </div>
    </div>
    @endforelse
</div>

{{-- Pagination --}}
@if($carouselImages->hasPages())
<div class="d-flex justify-content-center mt-3">
    {{ $carouselImages->links() }}
</div>
@endif
@endsection
