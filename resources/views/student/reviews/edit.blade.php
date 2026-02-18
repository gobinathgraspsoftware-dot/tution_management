@extends('layouts.app')

@section('title', 'Edit Review')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('student.reviews.index') }}">Reviews</a></li>
                <li class="breadcrumb-item active">Edit Review</li>
            </ol>
        </nav>
        <h2><i class="fas fa-edit text-primary me-2"></i> Edit Review</h2>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <!-- Class & Teacher Info (Read-only) -->
                    <div class="alert alert-info mb-4">
                        <div class="row">
                            <div class="col-md-6">
                                <strong><i class="fas fa-chalkboard me-1"></i> Class:</strong>
                                <div>{{ $review->class->name }} - {{ $review->class->subject->name ?? 'N/A' }}</div>
                            </div>
                            <div class="col-md-6">
                                <strong><i class="fas fa-user me-1"></i> Teacher:</strong>
                                <div>{{ $review->teacher->user->name }}</div>
                            </div>
                        </div>
                        <small class="text-muted d-block mt-2">
                            <i class="fas fa-info-circle me-1"></i> You cannot change the class or teacher when editing a review.
                        </small>
                    </div>

                    <form action="{{ route('student.reviews.update', $review) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Rating -->
                        <div class="mb-4">
                            <label class="form-label">
                                <i class="fas fa-star me-1"></i> Rating <span class="text-danger">*</span>
                            </label>
                            <div class="rating-selector">
                                <input type="hidden" name="rating" id="rating" value="{{ old('rating', $review->rating) }}">
                                <div class="star-rating">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="fas fa-star star" data-rating="{{ $i }}" style="font-size: 2rem; cursor: pointer;"></i>
                                    @endfor
                                </div>
                                <div class="mt-2">
                                    <span id="rating-text" class="fw-bold"></span>
                                </div>
                            </div>
                            @error('rating')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Review Text -->
                        <div class="mb-4">
                            <label for="review" class="form-label">
                                <i class="fas fa-comment me-1"></i> Your Review <span class="text-danger">*</span>
                            </label>
                            <textarea name="review"
                                      id="review"
                                      rows="6"
                                      class="form-control @error('review') is-invalid @enderror"
                                      placeholder="Share your experience..."
                                      required>{{ old('review', $review->review) }}</textarea>
                            <div class="form-text">
                                <span id="char-count">{{ strlen(old('review', $review->review)) }}</span>/1000 characters (Min: 10)
                            </div>
                            @error('review')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Warning -->
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Note:</strong> Editing your review will reset its approval status. It will need to be re-approved by admin.
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Update Review
                            </button>
                            <a href="{{ route('student.reviews.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Original Review Info -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i> Review Information</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-muted">Original Rating:</td>
                            <td>
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star {{ $i <= $review->rating ? 'text-warning' : 'text-muted' }} small"></i>
                                @endfor
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Created:</td>
                            <td>{{ $review->created_at->format('d M Y') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Status:</td>
                            <td>
                                @if($review->is_approved)
                                    <span class="badge bg-success">Approved</span>
                                @else
                                    <span class="badge bg-warning">Pending</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Guidelines -->
            <div class="card border-0 shadow-sm mt-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="fas fa-lightbulb me-2"></i> Guidelines</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled small mb-0">
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Be honest and constructive
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Minimum 10 characters required
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Review will be re-checked by admin
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-times text-danger me-2"></i>
                            Avoid offensive language
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Star rating functionality
const stars = document.querySelectorAll('.star');
const ratingInput = document.getElementById('rating');
const ratingText = document.getElementById('rating-text');

const ratingLabels = {
    1: '1 Star - Poor',
    2: '2 Stars - Below Average',
    3: '3 Stars - Average',
    4: '4 Stars - Good',
    5: '5 Stars - Excellent'
};

stars.forEach(star => {
    star.addEventListener('click', function() {
        const rating = this.getAttribute('data-rating');
        ratingInput.value = rating;
        ratingText.textContent = ratingLabels[rating];
        updateStars(rating);
    });

    star.addEventListener('mouseenter', function() {
        const rating = this.getAttribute('data-rating');
        updateStars(rating);
    });
});

document.querySelector('.star-rating').addEventListener('mouseleave', function() {
    const currentRating = ratingInput.value || 0;
    updateStars(currentRating);
});

function updateStars(rating) {
    stars.forEach(star => {
        const starRating = star.getAttribute('data-rating');
        if (starRating <= rating) {
            star.style.color = '#ffc107';
        } else {
            star.style.color = '#ddd';
        }
    });
}

// Initialize stars with current rating
if (ratingInput.value) {
    updateStars(ratingInput.value);
    ratingText.textContent = ratingLabels[ratingInput.value];
}

// Character counter
const reviewTextarea = document.getElementById('review');
const charCount = document.getElementById('char-count');

reviewTextarea.addEventListener('input', function() {
    charCount.textContent = this.value.length;

    if (this.value.length < 10) {
        charCount.classList.add('text-danger');
        charCount.classList.remove('text-success');
    } else {
        charCount.classList.add('text-success');
        charCount.classList.remove('text-danger');
    }
});
</script>
@endpush
@endsection
