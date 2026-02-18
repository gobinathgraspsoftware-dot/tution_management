@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0">Browse Packages</h4>
                <div class="page-title-right">
                    <a href="{{ route('student.enrollments.my-enrollments') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to My Enrollments
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Search --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('student.enrollments.browse-packages') }}">
                        <div class="row g-3">
                            <div class="col-md-10">
                                <input type="text"
                                       name="search"
                                       class="form-control"
                                       placeholder="Search packages by name or description..."
                                       value="{{ request('search') }}">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search me-1"></i> Search
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Packages Grid --}}
    <div class="row">
        @forelse($packages as $package)
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 border-primary shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0 text-white">{{ $package->name }}</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-3">{{ $package->description }}</p>

                        <div class="mb-3">
                            <label class="text-muted small mb-2">Subjects Included:</label>
                            @if($package->subjects && $package->subjects->isNotEmpty())
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach($package->subjects as $subject)
                                        <span class="badge bg-primary bg-soft text-primary">
                                            {{ $subject->name }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted small mb-0">No subjects assigned</p>
                            @endif
                        </div>

                        <div class="mb-3">
                            <div class="row">
                                <div class="col-6">
                                    <small class="text-muted">Duration</small>
                                    <p class="mb-0"><strong>{{ $package->duration_months }} months</strong></p>
                                </div>
                                <div class="col-6 text-end">
                                    <small class="text-muted">Status</small>
                                    <p class="mb-0">
                                        <span class="badge {{ $package->is_active ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $package->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="mb-0 text-primary">RM {{ number_format($package->price, 2) }}</h4>
                                <small class="text-muted">total package price</small>
                            </div>
                            @if($package->is_active)
                                <a href="{{ route('student.enrollments.enroll-package', $package) }}" class="btn btn-success">
                                    <i class="fas fa-shopping-cart me-1"></i> Enroll
                                </a>
                            @else
                                <button class="btn btn-secondary" disabled>
                                    Not Available
                                </button>
                            @endif
                        </div>
                    </div>

                    @if($package->subjects && $package->subjects->isNotEmpty())
                        <div class="card-footer bg-light">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Includes {{ $package->subjects->count() }} subject{{ $package->subjects->count() > 1 ? 's' : '' }}
                            </small>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <div class="mb-4">
                            <div class="avatar-lg mx-auto rounded-circle bg-warning bg-soft d-flex align-items-center justify-content-center" style="width: 5rem; height: 5rem;">
                                <i class="fas fa-box-open text-warning" style="font-size: 2.5rem;"></i>
                            </div>
                        </div>
                        <h5>No Packages Found</h5>
                        <p class="text-muted mb-4">
                            @if(request('search'))
                                No packages match your search criteria. Try a different search term.
                            @else
                                No packages are currently available. Check back later or browse individual classes.
                            @endif
                        </p>
                        <a href="{{ route('student.enrollments.browse-classes') }}" class="btn btn-primary">
                            <i class="fas fa-book me-1"></i> Browse Individual Classes
                        </a>
                    </div>
                </div>
            </div>
        @endforelse
    </div>
</div>

@push('styles')
<style>
    /* Soft background colors */
    .bg-primary.bg-soft {
        background-color: rgba(13, 110, 253, 0.1) !important;
    }
    .bg-warning.bg-soft {
        background-color: rgba(255, 193, 7, 0.1) !important;
    }

    /* Card styling */
    .card {
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }

    /* Badge styling */
    .badge {
        padding: 0.35em 0.65em;
        font-weight: 500;
    }
</style>
@endpush
@endsection
