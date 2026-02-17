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
                        <h5 class="card-title mb-0">{{ $package->name }}</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-3">{{ $package->description }}</p>

                        <div class="mb-3">
                            <label class="text-muted small mb-2">Subjects Included:</label>
                            @if($package->subjects && $package->subjects->isNotEmpty())
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach($package->subjects as $subject)
                                        <span class="badge bg-soft-primary text-primary">
                                            {{ $subject->name }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted small mb-0">No subjects assigned</p>
                            @endif
                        </div>

                        @if($package->discountRule)
                            <div class="alert alert-success mb-3">
                                <i class="fas fa-tag me-1"></i>
                                <strong>Special Offer:</strong>
                                @if($package->discountRule->type === 'percentage')
                                    {{ $package->discountRule->value }}% discount
                                @else
                                    RM {{ number_format($package->discountRule->value, 2) }} off
                                @endif
                            </div>
                        @endif

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
                                @if($package->discountRule)
                                    @php
                                        $originalPrice = $package->price;
                                        $discountedPrice = $package->discountRule->type === 'percentage'
                                            ? $originalPrice - ($originalPrice * $package->discountRule->value / 100)
                                            : $originalPrice - $package->discountRule->value;
                                    @endphp
                                    <div>
                                        <small class="text-muted text-decoration-line-through">RM {{ number_format($originalPrice, 2) }}</small>
                                        <h4 class="mb-0 text-success">RM {{ number_format($discountedPrice, 2) }}</h4>
                                    </div>
                                @else
                                    <h4 class="mb-0 text-primary">RM {{ number_format($package->price, 2) }}</h4>
                                @endif
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
                        <div class="avatar-lg mx-auto mb-4">
                            <div class="avatar-title bg-soft-warning text-warning rounded-circle fs-1">
                                <i class="fas fa-box-open"></i>
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
@endsection
