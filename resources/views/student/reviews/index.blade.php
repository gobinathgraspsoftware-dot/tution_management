@extends('layouts.app')

@section('title', 'My Reviews')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="fas fa-star text-primary me-2"></i> My Reviews</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Reviews</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('student.reviews.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Write Review
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-opacity-10 rounded p-3">
                                <i class="fas fa-star text-primary fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Total Reviews</h6>
                            <h4 class="mb-0">{{ $stats['total'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-opacity-10 rounded p-3">
                                <i class="fas fa-check-circle text-success fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Approved</h6>
                            <h4 class="mb-0">{{ $stats['approved'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-opacity-10 rounded p-3">
                                <i class="fas fa-clock text-warning fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Pending</h6>
                            <h4 class="mb-0">{{ $stats['pending'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-info bg-opacity-10 rounded p-3">
                                <i class="fas fa-chart-line text-info fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Average Rating</h6>
                            <h4 class="mb-0">{{ $stats['average_rating'] ?: 'N/A' }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('student.reviews.index') }}" method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small">Filter by Status</label>
                    <select name="filter" class="form-select">
                        <option value="all" {{ $filter === 'all' ? 'selected' : '' }}>All Reviews</option>
                        <option value="approved" {{ $filter === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="pending" {{ $filter === 'pending' ? 'selected' : '' }}>Pending Approval</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Filter by Rating</label>
                    <select name="rating" class="form-select">
                        <option value="">All Ratings</option>
                        <option value="5" {{ request('rating') == 5 ? 'selected' : '' }}>5 Stars</option>
                        <option value="4" {{ request('rating') == 4 ? 'selected' : '' }}>4 Stars</option>
                        <option value="3" {{ request('rating') == 3 ? 'selected' : '' }}>3 Stars</option>
                        <option value="2" {{ request('rating') == 2 ? 'selected' : '' }}>2 Stars</option>
                        <option value="1" {{ request('rating') == 1 ? 'selected' : '' }}>1 Star</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Filter by Class</label>
                    <select name="class_id" class="form-select">
                        <option value="">All Classes</option>
                        @foreach($enrolledClasses as $class)
                            <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                {{ $class->name }} - {{ $class->subject->name ?? 'N/A' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">&nbsp;</label>
                    <div class="btn-group w-100">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter me-1"></i> Filter
                        </button>
                        <a href="{{ route('student.reviews.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-redo"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Reviews List -->
    <div class="row">
        @forelse($reviews as $review)
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <!-- Header -->
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h5 class="mb-1">{{ $review->class->name }}</h5>
                                <p class="text-muted small mb-0">
                                    <i class="fas fa-book me-1"></i> {{ $review->class->subject->name ?? 'N/A' }}
                                </p>
                            </div>
                            <div>
                                @if($review->is_approved)
                                    <span class="badge bg-success">
                                        <i class="fas fa-check-circle me-1"></i> Approved
                                    </span>
                                @else
                                    <span class="badge bg-warning">
                                        <i class="fas fa-clock me-1"></i> Pending
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Teacher -->
                        <div class="mb-3">
                            <label class="text-muted small">Teacher:</label>
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-primary bg-opacity-10 p-2 me-2">
                                    <i class="fas fa-user text-primary"></i>
                                </div>
                                <strong>{{ $review->teacher->user->name }}</strong>
                            </div>
                        </div>

                        <!-- Rating -->
                        <div class="mb-3">
                            <label class="text-muted small">Rating:</label>
                            <div>
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star {{ $i <= $review->rating ? 'text-warning' : 'text-muted' }}"></i>
                                @endfor
                                <span class="ms-2 fw-bold">{{ $review->rating }}/5</span>
                            </div>
                        </div>

                        <!-- Review Text -->
                        <div class="mb-3">
                            <label class="text-muted small">Review:</label>
                            <p class="mb-0">{{ Str::limit($review->review, 150) }}</p>
                        </div>

                        <!-- Date -->
                        <div class="text-muted small mb-3">
                            <i class="fas fa-calendar me-1"></i> {{ $review->created_at->format('d M Y, h:i A') }}
                        </div>

                        <!-- Actions -->
                        <div class="btn-group w-100">
                            <a href="{{ route('student.reviews.show', $review) }}" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-eye me-1"></i> View
                            </a>
                            <a href="{{ route('student.reviews.edit', $review) }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-edit me-1"></i> Edit
                            </a>
                            <button type="button"
                                    class="btn btn-outline-danger btn-sm"
                                    onclick="deleteReview({{ $review->id }})">
                                <i class="fas fa-trash me-1"></i> Delete
                            </button>
                        </div>

                        <!-- Delete Form (Hidden) -->
                        <form id="delete-form-{{ $review->id }}"
                              action="{{ route('student.reviews.destroy', $review) }}"
                              method="POST"
                              class="d-none">
                            @csrf
                            @method('DELETE')
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-star fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No reviews yet</h5>
                        <p class="text-muted">Start sharing your experience by writing your first review!</p>
                        <a href="{{ route('student.reviews.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i> Write Your First Review
                        </a>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($reviews->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $reviews->links() }}
        </div>
    @endif
</div>

@push('scripts')
<script>
function deleteReview(reviewId) {
    if (confirm('Are you sure you want to delete this review? This action cannot be undone.')) {
        document.getElementById('delete-form-' + reviewId).submit();
    }
}
</script>
@endpush
@endsection
