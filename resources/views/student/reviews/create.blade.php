@extends('layouts.app')

@section('title', 'Write Review')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('student.reviews.index') }}">Reviews</a></li>
                <li class="breadcrumb-item active">Write Review</li>
            </ol>
        </nav>
        <h2><i class="fas fa-star text-primary me-2"></i> Write a Review</h2>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form action="{{ route('student.reviews.store') }}" method="POST">
                        @csrf

                        <!-- Class Selection -->
                        <div class="mb-4">
                            <label for="class_id" class="form-label">
                                <i class="fas fa-chalkboard me-1"></i> Select Class <span class="text-danger">*</span>
                            </label>
                            <select name="class_id" id="class_id" class="form-select @error('class_id') is-invalid @enderror" required>
                                <option value="">-- Choose a Class --</option>
                                @foreach($enrolledClasses as $class)
                                    <option value="{{ $class->id }}"
                                            data-teacher-id="{{ $class->teacher_id }}"
                                            data-teacher-name="{{ $class->teacher->user->name ?? 'N/A' }}"
                                            {{ old('class_id', $selectedClassId) == $class->id ? 'selected' : '' }}>
                                        {{ $class->name }} - {{ $class->subject->name ?? '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('class_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Select a class you want to review</small>
                        </div>

                        <!-- Teacher (Auto-filled) -->
                        <div class="mb-4">
                            <label for="teacher_id" class="form-label">
                                <i class="fas fa-user me-1"></i> Teacher <span class="text-danger">*</span>
                            </label>
                            <input type="hidden" name="teacher_id" id="teacher_id" value="{{ old('teacher_id', $selectedTeacherId) }}">
                            <input type="text" id="teacher_name" class="form-control bg-light" readonly placeholder="Select a class first">
                            @error('teacher_id')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Rating -->
                        <div class="mb-4">
                            <label class="form-label">
                                <i class="fas fa-star me-1"></i> Rating <span class="text-danger">*</span>
                            </label>
                            <div class="rating-selector">
                                <input type="hidden" name="rating" id="rating" value="{{ old('rating') }}">
                                <div class="star-rating">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="fas fa-star star" data-rating="{{ $i }}" style="font-size: 2rem; cursor: pointer; color: #ddd;"></i>
                                    @endfor
                                </div>
                                <div class="mt-2">
                                    <span id="rating-text" class="fw-bold text-muted">Select your rating</span>
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
                                      placeholder="Share your experience with this class and teacher... (Minimum 10 characters)"
                                      required>{{ old('review') }}</textarea>
                            <div class="form-text">
                                <span id="char-count">0</span>/1000 characters (Min: 10)
                            </div>
                            @error('review')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-1"></i> Submit Review
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
            <!-- Guidelines -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="fas fa-lightbulb me-2"></i> Review Guidelines</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled small mb-0">
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Be honest and constructive
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Focus on teaching quality and class experience
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Minimum 10 characters required
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Reviews are pending admin approval
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            You can edit your review later
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-times text-danger me-2"></i>
                            Avoid offensive or inappropriate language
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Rating Guide -->
            <div class="card border-0 shadow-sm mt-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="fas fa-star me-2"></i> Rating Guide</h6>
                </div>
                <div class="card-body">
                    <div class="small">
                        <div class="mb-2">
                            <strong>5 Stars</strong> - Excellent
                            <div class="text-muted">Outstanding teaching and class experience</div>
                        </div>
                        <div class="mb-2">
                            <strong>4 Stars</strong> - Good
                            <div class="text-muted">Very good with minor areas for improvement</div>
                        </div>
                        <div class="mb-2">
                            <strong>3 Stars</strong> - Average
                            <div class="text-muted">Satisfactory but room for improvement</div>
                        </div>
                        <div class="mb-2">
                            <strong>2 Stars</strong> - Below Average
                            <div class="text-muted">Needs significant improvement</div>
                        </div>
                        <div class="mb-2">
                            <strong>1 Star</strong> - Poor
                            <div class="text-muted">Very unsatisfactory experience</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Auto-fill teacher when class is selected
document.getElementById('class_id').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const teacherId = selectedOption.getAttribute('data-teacher-id');
    const teacherName = selectedOption.getAttribute('data-teacher-name');

    if (teacherId) {
        document.getElementById('teacher_id').value = teacherId;
        document.getElementById('teacher_name').value = teacherName;
    } else {
        document.getElementById('teacher_id').value = '';
        document.getElementById('teacher_name').value = '';
    }
});

// Initialize teacher if class is pre-selected
if (document.getElementById('class_id').value) {
    document.getElementById('class_id').dispatchEvent(new Event('change'));
}

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

// Initialize stars if rating is pre-filled
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

// Initialize character count
if (reviewTextarea.value) {
    charCount.textContent = reviewTextarea.value.length;
}
</script>
@endpush
@endsection
