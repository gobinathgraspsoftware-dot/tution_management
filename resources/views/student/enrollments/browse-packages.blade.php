@extends('layouts.app')

@section('title', 'Browse Packages')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-box"></i> Browse Available Packages
        </h1>
        <div>
            <a href="{{ route('student.enrollments.browse-classes') }}" class="btn btn-info">
                <i class="fas fa-search"></i> Browse Classes
            </a>
            <a href="{{ route('student.enrollments.my-enrollments') }}" class="btn btn-primary">
                <i class="fas fa-list"></i> My Enrollments
            </a>
        </div>
    </div>

    <!-- Search -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('student.enrollments.browse-packages') }}">
                <div class="input-group">
                    <input type="text" class="form-control" name="search"
                           value="{{ request('search') }}" placeholder="Search packages...">
                    <div class="input-group-append">
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Packages Grid -->
    <div class="row">
        @forelse($packages as $package)
        <div class="col-lg-6 col-xl-4 mb-4">
            <div class="card shadow h-100 border-primary">
                <div class="card-header bg-gradient-primary text-white">
                    <h4 class="mb-0">{{ $package->name }}</h4>
                    <div class="mt-2">
                        <span class="badge badge-light">
                            <i class="fas fa-clock"></i> {{ $package->duration_months }} Months
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    @if($package->description)
                    <p class="text-muted">{{ $package->description }}</p>
                    @endif

                    <div class="mb-3">
                        <h5 class="text-primary">Included Subjects:</h5>
                        <ul class="list-unstyled">
                            @foreach($package->subjects as $subject)
                                <li>
                                    <i class="fas fa-check-circle text-success"></i>
                                    {{ $subject->name }}
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    @if($package->discountRule)
                    <div class="alert alert-success mb-3">
                        <i class="fas fa-tag"></i> <strong>Special Offer!</strong>
                        <div class="small">{{ $package->discountRule->discount_percentage }}% discount applied</div>
                    </div>
                    @endif

                    <div class="pricing-box p-3 bg-light rounded text-center mb-3">
                        <div class="h2 text-success mb-0">
                            RM {{ number_format($package->price, 2) }}
                        </div>
                        <small class="text-muted">per month</small>
                    </div>

                    <div class="small text-muted">
                        <strong>Package Benefits:</strong>
                        <ul>
                            <li>Access to {{ $package->subjects->count() }} subjects</li>
                            <li>{{ $package->duration_months }} months duration</li>
                            <li>All class materials included</li>
                            <li>Save compared to individual classes</li>
                        </ul>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('student.enrollments.enroll-package', $package) }}"
                       class="btn btn-success btn-block btn-lg">
                        <i class="fas fa-shopping-cart"></i> Enroll in Package
                    </a>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> No packages available at the moment. Please check our individual classes or contact us for more information.
            </div>
        </div>
        @endforelse
    </div>

    <!-- Package Comparison Info -->
    <div class="card shadow mt-4">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0"><i class="fas fa-info-circle"></i> Why Choose a Package?</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="text-center mb-3">
                        <i class="fas fa-piggy-bank fa-3x text-success mb-2"></i>
                        <h5>Cost Savings</h5>
                        <p class="text-muted small">Save money compared to enrolling in individual classes</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center mb-3">
                        <i class="fas fa-book-open fa-3x text-primary mb-2"></i>
                        <h5>Comprehensive Learning</h5>
                        <p class="text-muted small">Get access to multiple subjects for well-rounded education</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center mb-3">
                        <i class="fas fa-calendar-check fa-3x text-info mb-2"></i>
                        <h5>Structured Schedule</h5>
                        <p class="text-muted small">Pre-planned schedule ensures consistent learning</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
