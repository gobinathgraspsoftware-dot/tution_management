@extends('layouts.app')

@section('title', 'Review Details')
@section('page-title', 'Review Details')

@section('content')
<div class="page-header">
    <div>
        <h1><i class="fas fa-star me-2"></i> Review Details</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.reviews.index') }}">Reviews</a></li>
                <li class="breadcrumb-item active">Details</li>
            </ol>
        </nav>
    </div>
    <div>
        <a href="{{ route('admin.reviews.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
        <a href="{{ route('admin.reviews.edit', $review) }}" class="btn btn-primary">
            <i class="fas fa-edit me-1"></i> Edit
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <!-- Review Card -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-comment me-2"></i> Review</span>
                @if($review->is_approved)
                    <span class="badge bg-success">Approved</span>
                @else
                    <span class="badge bg-warning">Pending Approval</span>
                @endif
            </div>
            <div class="card-body">
                <!-- Rating -->
                <div class="text-center mb-4">
                    <div class="fs-1">
                        @for($i = 1; $i <= 5; $i++)
                            @if($i <= $review->rating)
                                <i class="fas fa-star text-warning"></i>
                            @else
                                <i class="far fa-star text-warning"></i>
                            @endif
                        @endfor
                    </div>
                    <p class="text-muted">{{ $review->rating }} out of 5 stars</p>
                </div>

                <!-- Review Text -->
                <div class="p-4 bg-light rounded mb-4">
                    @if($review->review)
                        <p class="mb-0 fs-5" style="line-height: 1.8;">{{ $review->review }}</p>
                    @else
                        <p class="mb-0 text-muted text-center">No written review provided</p>
                    @endif
                </div>

                <!-- Meta Info -->
                <div class="row text-center">
                    <div class="col-md-4">
                        <small class="text-muted">Submitted</small>
                        <p class="mb-0"><strong>{{ $review->created_at->format('d M Y, h:i A') }}</strong></p>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted">Class</small>
                        <p class="mb-0"><strong>{{ $review->class->name ?? 'General' }}</strong></p>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted">Teacher</small>
                        <p class="mb-0"><strong>{{ $review->teacher->user->name ?? 'N/A' }}</strong></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Student Information -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-user-graduate me-2"></i> Student Information
            </div>
            <div class="card-body">
                @if($review->student)
                <div class="row align-items-center">
                    <div class="col-md-2 text-center">
                        <div class="avatar-circle" style="width: 80px; height: 80px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                            <span style="font-size: 32px; color: white; font-weight: bold;">
                                {{ strtoupper(substr($review->student->user->name, 0, 2)) }}
                            </span>
                        </div>
                    </div>
                    <div class="col-md-10">
                        <h5 class="mb-1">{{ $review->student->user->name }}</h5>
                        <p class="text-muted mb-1">{{ $review->student->student_id }}</p>
                        <p class="mb-0">
                            <i class="fas fa-envelope me-1"></i> {{ $review->student->user->email }}
                            @if($review->student->user->phone)
                                | <i class="fas fa-phone me-1"></i> {{ $review->student->user->phone }}
                            @endif
                        </p>
                        <a href="{{ route('admin.students.profile', $review->student) }}" class="btn btn-sm btn-outline-primary mt-2">
                            View Profile
                        </a>
                    </div>
                </div>
                @else
                <p class="text-muted text-center mb-0">Student information not available</p>
                @endif
            </div>
        </div>

        <!-- Other Reviews from Same Student -->
        @if($otherReviews->count() > 0)
        <div class="card">
            <div class="card-header">
                <i class="fas fa-history me-2"></i> Other Reviews from This Student
            </div>
            <div class="card-body">
                @foreach($otherReviews as $other)
                <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                    <div>
                        <div class="mb-1">
                            @for($i = 1; $i <= 5; $i++)
                                @if($i <= $other->rating)
                                    <i class="fas fa-star text-warning small"></i>
                                @else
                                    <i class="far fa-star text-warning small"></i>
                                @endif
                            @endfor
                        </div>
                        <strong>{{ $other->class->name ?? 'General' }}</strong>
                        @if($other->teacher)
                            <br><small class="text-muted">Teacher: {{ $other->teacher->user->name }}</small>
                        @endif
                    </div>
                    <div class="text-end">
                        <small class="text-muted">{{ $other->created_at->format('d M Y') }}</small>
                        <br>
                        <a href="{{ route('admin.reviews.show', $other) }}" class="btn btn-sm btn-outline-info mt-1">View</a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <!-- Actions Sidebar -->
    <div class="col-md-4">
        <!-- Quick Actions -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-bolt me-2"></i> Actions
            </div>
            <div class="card-body">
                @if(!$review->is_approved)
                    <form action="{{ route('admin.reviews.approve', $review) }}" method="POST" class="mb-2">
                        @csrf
                        <button type="submit" class="btn btn-success w-100">
                            <i class="fas fa-check me-1"></i> Approve Review
                        </button>
                    </form>
                @else
                    <form action="{{ route('admin.reviews.reject', $review) }}" method="POST" class="mb-2">
                        @csrf
                        <button type="submit" class="btn btn-warning w-100">
                            <i class="fas fa-times me-1"></i> Unapprove Review
                        </button>
                    </form>
                @endif

                <a href="{{ route('admin.reviews.edit', $review) }}" class="btn btn-primary w-100 mb-2">
                    <i class="fas fa-edit me-1"></i> Edit Review
                </a>

                <form action="{{ route('admin.reviews.destroy', $review) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Are you sure you want to delete this review?')">
                        <i class="fas fa-trash me-1"></i> Delete Review
                    </button>
                </form>
            </div>
        </div>

        <!-- Class Info -->
        @if($review->class)
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-chalkboard me-2"></i> Class Information
            </div>
            <div class="card-body">
                <h6>{{ $review->class->name }}</h6>
                <p class="text-muted mb-2">{{ $review->class->subject->name ?? 'N/A' }}</p>
                <table class="table table-sm table-borderless">
                    <tr>
                        <td>Type</td>
                        <td><strong>{{ ucfirst($review->class->type) }}</strong></td>
                    </tr>
                    <tr>
                        <td>Grade Level</td>
                        <td><strong>{{ $review->class->grade_level ?? 'N/A' }}</strong></td>
                    </tr>
                    <tr>
                        <td>Status</td>
                        <td>
                            @if($review->class->status === 'active')
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">{{ ucfirst($review->class->status) }}</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        @endif

        <!-- Teacher Info -->
        @if($review->teacher)
        <div class="card">
            <div class="card-header">
                <i class="fas fa-chalkboard-teacher me-2"></i> Teacher Information
            </div>
            <div class="card-body">
                <h6>{{ $review->teacher->user->name }}</h6>
                <p class="text-muted mb-2">{{ $review->teacher->teacher_id }}</p>
                @if($review->teacher->specialization)
                <p class="mb-0"><small>Specialization: {{ $review->teacher->specialization }}</small></p>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
