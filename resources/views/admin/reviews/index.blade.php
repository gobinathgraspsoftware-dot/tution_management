@extends('layouts.app')

@section('title', 'Student Reviews')
@section('page-title', 'Student Reviews')

@section('content')
<div class="page-header">
    <div>
        <h1><i class="fas fa-star me-2"></i> Student Reviews</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Reviews</li>
            </ol>
        </nav>
    </div>
    <div>
        <a href="{{ route('admin.reviews.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Add Review
        </a>
        <a href="{{ route('admin.reviews.export') }}" class="btn btn-success">
            <i class="fas fa-download me-1"></i> Export
        </a>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-2">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #e3f2fd; color: #2196f3;">
                <i class="fas fa-comments"></i>
            </div>
            <div class="stat-details">
                <h3 class="mb-0">{{ $stats['total'] }}</h3>
                <p class="text-muted mb-0">Total Reviews</p>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #e8f5e9; color: #4caf50;">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-details">
                <h3 class="mb-0">{{ $stats['approved'] }}</h3>
                <p class="text-muted mb-0">Approved</p>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #fff3e0; color: #ff9800;">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-details">
                <h3 class="mb-0">{{ $stats['pending'] }}</h3>
                <p class="text-muted mb-0">Pending</p>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #fff8e1; color: #ffc107;">
                <i class="fas fa-star"></i>
            </div>
            <div class="stat-details">
                <h3 class="mb-0">{{ $stats['average_rating'] }}</h3>
                <p class="text-muted mb-0">Avg Rating</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body py-2">
                <small class="text-muted">Rating Distribution</small>
                <div class="d-flex justify-content-between small">
                    <span>5⭐ {{ $stats['five_star'] }}</span>
                    <span>4⭐ {{ $stats['four_star'] }}</span>
                    <span>3⭐ {{ $stats['three_star'] }}</span>
                    <span>2⭐ {{ $stats['two_star'] }}</span>
                    <span>1⭐ {{ $stats['one_star'] }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('admin.reviews.index') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" placeholder="Search reviews..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="is_approved" class="form-select">
                    <option value="">All Status</option>
                    <option value="yes" {{ request('is_approved') === 'yes' ? 'selected' : '' }}>Approved</option>
                    <option value="no" {{ request('is_approved') === 'no' ? 'selected' : '' }}>Pending</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="rating" class="form-select">
                    <option value="">All Ratings</option>
                    @for($i = 5; $i >= 1; $i--)
                        <option value="{{ $i }}" {{ request('rating') == $i ? 'selected' : '' }}>{{ $i }} Star{{ $i > 1 ? 's' : '' }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-2">
                <select name="class_id" class="form-select">
                    <option value="">All Classes</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary"><i class="fas fa-search me-1"></i> Filter</button>
                <a href="{{ route('admin.reviews.index') }}" class="btn btn-secondary"><i class="fas fa-redo me-1"></i> Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Bulk Actions -->
@if($reviews->where('is_approved', false)->count() > 0)
<div class="card mb-4">
    <div class="card-body py-2">
        <form action="{{ route('admin.reviews.bulk-approve') }}" method="POST" id="bulkApproveForm">
            @csrf
            <div class="d-flex align-items-center gap-3">
                <span class="text-muted">Bulk Actions:</span>
                <button type="submit" class="btn btn-sm btn-success" disabled id="bulkApproveBtn">
                    <i class="fas fa-check me-1"></i> Approve Selected (<span id="selectedCount">0</span>)
                </button>
            </div>
        </form>
    </div>
</div>
@endif

<!-- Reviews Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="selectAll"></th>
                        <th>Student</th>
                        <th>Class/Teacher</th>
                        <th>Rating</th>
                        <th>Review</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reviews as $review)
                    <tr>
                        <td>
                            @if(!$review->is_approved)
                            <input type="checkbox" name="review_ids[]" value="{{ $review->id }}" class="review-checkbox" form="bulkApproveForm">
                            @endif
                        </td>
                        <td>
                            @if($review->student)
                                <a href="{{ route('admin.students.profile', $review->student) }}">
                                    {{ $review->student->user->name ?? 'N/A' }}
                                </a>
                                <br><small class="text-muted">{{ $review->student->student_id }}</small>
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                        <td>
                            {{ $review->class->name ?? 'General' }}
                            @if($review->teacher)
                                <br><small class="text-muted">{{ $review->teacher->user->name }}</small>
                            @endif
                        </td>
                        <td>
                            @for($i = 1; $i <= 5; $i++)
                                @if($i <= $review->rating)
                                    <i class="fas fa-star text-warning"></i>
                                @else
                                    <i class="far fa-star text-warning"></i>
                                @endif
                            @endfor
                        </td>
                        <td>{{ Str::limit($review->review, 80) ?? '-' }}</td>
                        <td>
                            @if($review->is_approved)
                                <span class="badge bg-success">Approved</span>
                            @else
                                <span class="badge bg-warning">Pending</span>
                            @endif
                        </td>
                        <td>{{ $review->created_at->format('d M Y') }}</td>
                        <td>
                            <div class="btn-group">
                                <a href="{{ route('admin.reviews.show', $review) }}" class="btn btn-sm btn-info" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if(!$review->is_approved)
                                    <form action="{{ route('admin.reviews.approve', $review) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success" title="Approve">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('admin.reviews.reject', $review) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-warning" title="Unapprove">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </form>
                                @endif
                                <a href="{{ route('admin.reviews.edit', $review) }}" class="btn btn-sm btn-primary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.reviews.destroy', $review) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Delete this review?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">No reviews found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $reviews->links() }}
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.review-checkbox');
    const bulkApproveBtn = document.getElementById('bulkApproveBtn');
    const selectedCount = document.getElementById('selectedCount');

    function updateSelectedCount() {
        const checked = document.querySelectorAll('.review-checkbox:checked').length;
        selectedCount.textContent = checked;
        bulkApproveBtn.disabled = checked === 0;
    }

    selectAll?.addEventListener('change', function() {
        checkboxes.forEach(cb => cb.checked = this.checked);
        updateSelectedCount();
    });

    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateSelectedCount);
    });
});
</script>
@endpush
@endsection
