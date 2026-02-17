@extends('layouts.app')

@section('title', 'My Classes')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">My Classes</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Classes</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Summary Card -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <h4 class="mb-0">{{ $classes->count() }} Active Classes</h4>
                            <p class="mb-0 opacity-75">Total Enrollments: {{ $enrollments->count() }}</p>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-graduation-cap fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Classes Grid -->
    <div class="row">
        @forelse($classes as $class)
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">{{ $class->name }}</h5>
                        <small class="text-muted">{{ $class->subject->name ?? 'N/A' }}</small>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <i class="fas fa-chalkboard-teacher me-2 text-primary"></i>
                            <strong>Teacher:</strong> {{ $class->teacher->user->name ?? 'N/A' }}
                        </div>

                        <div class="mb-3">
                            <i class="fas fa-clock me-2 text-info"></i>
                            <strong>Schedule:</strong>
                            @if($class->schedule_days)
                                {{ $class->schedule_days }} @ {{ $class->schedule_time }}
                            @else
                                Not set
                            @endif
                        </div>

                        <div class="mb-3">
                            <i class="fas fa-map-marker-alt me-2 text-success"></i>
                            <strong>Location:</strong> {{ $class->location ?? 'TBA' }}
                        </div>

                        @php
                            $enrollment = $enrollments->where('class_id', $class->id)->first();
                        @endphp

                        @if($enrollment)
                            <div class="alert alert-success mb-0">
                                <small>
                                    <i class="fas fa-check-circle me-1"></i>
                                    Enrolled: {{ $enrollment->created_at->format('d M Y') }}
                                </small>
                            </div>
                        @endif
                    </div>
                    <div class="card-footer bg-light">
                        <a href="{{ route('student.classes.show', $class->id) }}" class="btn btn-primary btn-sm w-100">
                            <i class="fas fa-eye me-1"></i> View Details
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    You are not enrolled in any classes yet.
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection
