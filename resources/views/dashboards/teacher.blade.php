@extends('layouts.app')

@section('title', 'Teacher Dashboard')
@section('page-title', 'Teacher Dashboard')

@section('content')
<div class="page-header">
    <h1>
        <i class="fas fa-chalkboard-teacher me-2"></i> Teacher Dashboard
    </h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item active">Dashboard</li>
        </ol>
    </nav>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #e3f2fd; color: #2196f3;">
                <i class="fas fa-school"></i>
            </div>
            <div class="stat-details">
                <h3 class="mb-0">{{ $my_classes->count() }}</h3>
                <p class="text-muted mb-0">My Classes</p>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #e8f5e9; color: #4caf50;">
                <i class="fas fa-calendar-day"></i>
            </div>
            <div class="stat-details">
                <h3 class="mb-0">{{ $today_classes }}</h3>
                <p class="text-muted mb-0">Today's Classes</p>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #f3e5f5; color: #9c27b0;">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-details">
                <h3 class="mb-0">{{ $total_students }}</h3>
                <p class="text-muted mb-0">Total Students</p>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #fff3e0; color: #ff9800;">
                <i class="fas fa-book"></i>
            </div>
            <div class="stat-details">
                <h3 class="mb-0">{{ $recent_materials->count() }}</h3>
                <p class="text-muted mb-0">Materials Uploaded</p>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-bolt me-2"></i> Quick Actions
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <a href="#" class="btn btn-primary w-100">
                            <i class="fas fa-check-circle me-2"></i> Mark Attendance
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="#" class="btn btn-success w-100">
                            <i class="fas fa-upload me-2"></i> Upload Material
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="#" class="btn btn-info w-100">
                            <i class="fas fa-file-alt me-2"></i> Create Exam
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="#" class="btn btn-warning w-100">
                            <i class="fas fa-chart-line me-2"></i> View Reports
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- My Classes & Today's Schedule -->
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-school me-2"></i> My Classes</span>
                <a href="#" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                @forelse($my_classes as $class)
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1">{{ $class->name }}</h6>
                                <p class="text-muted mb-1 small">
                                    <i class="fas fa-book me-1"></i> {{ $class->subject->name }}
                                </p>
                                <p class="text-muted mb-0 small">
                                    <i class="fas fa-clock me-1"></i> {{ $class->schedule }}
                                </p>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-primary">{{ $class->enrollments_count ?? 0 }} Students</span>
                                <br>
                                <small class="text-muted">{{ $class->class_type }}</small>
                            </div>
                        </div>
                        <div class="mt-2">
                            <a href="#" class="btn btn-sm btn-outline-primary me-2">
                                <i class="fas fa-users"></i> Students
                            </a>
                            <a href="#" class="btn btn-sm btn-outline-success">
                                <i class="fas fa-check"></i> Attendance
                            </a>
                        </div>
                    </div>
                @empty
                    <p class="text-muted text-center">No classes assigned yet</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-calendar-alt me-2"></i> Today's Schedule</span>
                <span class="badge bg-primary">{{ \Carbon\Carbon::now()->format('D, d M Y') }}</span>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-item mb-3">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <div class="badge bg-success" style="width: 60px; padding: 0.5rem; font-size: 0.85rem;">
                                    09:00 AM
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-0">Mathematics - Form 5</h6>
                                <small class="text-muted">Class A • 25 Students</small>
                            </div>
                            <a href="#" class="btn btn-sm btn-primary">Start</a>
                        </div>
                    </div>

                    <div class="timeline-item mb-3">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <div class="badge bg-warning" style="width: 60px; padding: 0.5rem; font-size: 0.85rem;">
                                    11:30 AM
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-0">Additional Mathematics</h6>
                                <small class="text-muted">Class B • 18 Students</small>
                            </div>
                            <a href="#" class="btn btn-sm btn-outline-primary">Details</a>
                        </div>
                    </div>

                    <div class="timeline-item mb-3">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <div class="badge bg-secondary" style="width: 60px; padding: 0.5rem; font-size: 0.85rem;">
                                    02:00 PM
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-0">Mathematics - Form 4</h6>
                                <small class="text-muted">Class C • 22 Students</small>
                            </div>
                            <a href="#" class="btn btn-sm btn-outline-primary">Details</a>
                        </div>
                    </div>

                    <div class="timeline-item">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <div class="badge bg-info" style="width: 60px; padding: 0.5rem; font-size: 0.85rem;">
                                    04:30 PM
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-0">Trial Class - New Students</h6>
                                <small class="text-muted">5 Trial Students</small>
                            </div>
                            <a href="#" class="btn btn-sm btn-outline-primary">Details</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Materials & Upcoming Exams -->
<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-file-pdf me-2"></i> Recent Materials</span>
                <a href="#" class="btn btn-sm btn-outline-primary">Upload New</a>
            </div>
            <div class="card-body">
                @forelse($recent_materials as $material)
                    <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                        <div class="me-3">
                            <i class="fas fa-file-pdf fa-2x text-danger"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-0">{{ $material->title }}</h6>
                            <small class="text-muted">
                                {{ $material->class->name }} • {{ $material->created_at->diffForHumans() }}
                            </small>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-success">{{ $material->views_count ?? 0 }} views</span>
                        </div>
                    </div>
                @empty
                    <p class="text-muted text-center">No materials uploaded yet</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-clipboard-check me-2"></i> Upcoming Exams</span>
                <a href="#" class="btn btn-sm btn-outline-primary">Create Exam</a>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <div class="list-group-item px-0">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1">Mid-Term Examination</h6>
                                <small class="text-muted">Mathematics - Form 5</small>
                            </div>
                            <span class="badge bg-warning">15 Dec 2025</span>
                        </div>
                        <div class="mt-2">
                            <a href="#" class="btn btn-sm btn-outline-primary">View Details</a>
                        </div>
                    </div>

                    <div class="list-group-item px-0">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1">Monthly Test</h6>
                                <small class="text-muted">Additional Mathematics</small>
                            </div>
                            <span class="badge bg-info">22 Dec 2025</span>
                        </div>
                        <div class="mt-2">
                            <a href="#" class="btn btn-sm btn-outline-primary">View Details</a>
                        </div>
                    </div>

                    <div class="list-group-item px-0">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1">Final Term Exam</h6>
                                <small class="text-muted">Mathematics - Form 4</small>
                            </div>
                            <span class="badge bg-secondary">05 Jan 2026</span>
                        </div>
                        <div class="mt-2">
                            <a href="#" class="btn btn-sm btn-outline-primary">View Details</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
