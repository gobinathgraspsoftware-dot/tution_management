@extends('layouts.app')

@section('title', 'Review Details')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('student.reviews.index') }}">Reviews</a></li>
                <li class="breadcrumb-item active">Review Details</li>
            </ol>
        </nav>
        <h2><i class="fas fa-star text-primary me-2"></i> Review Details</h2>
    </div>

    <div class="row">
        <div class="col-md-8">
            <!-- Review Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Your Review</h5>
                    <div>
                        @if($review->is_approved)
                            <span class="badge bg-success fs-6">
                                <i class="fas fa-check-circle me-1"></i> Approved
                            </span>
                        @else
                            <span class="badge bg-warning fs-6">
                                <i class="fas fa-clock me-1"></i> Pending Approval
                            </span>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <!-- Class Information -->
                    <div class="mb-4">
                        <h6 class="text-muted mb-2">Class Information</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="small text-muted">Class:</label>
                                <h5>{{ $review->class->name }}</h5>
                            </div>
                            <div class="col-md-6">
                                <label class="small text-muted">Subject:</label>
                                <h5>{{ $review->class->subject->name ?? 'N/A' }}</h5>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <!-- Teacher Information -->
                    <div class="mb-4">
                        <h6 class="text-muted mb-2">Teacher</h6>
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                                <i class="fas fa-user fa-2x text-primary"></i>
                            </div>
                            <div>
                                <h5 class="mb-0">{{ $review->teacher->user->name }}</h5>
                                <small class="text-muted">{{ $review->teacher->user->email }}</small>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <!-- Rating -->
                    <div class="mb-4">
                        <h6 class="text-muted mb-2">Rating</h6>
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star {{ $i <= $review->rating ? 'text-warning' : 'text-muted' }}" style="font-size: 1.5rem;"></i>
                                @endfor
                            </div>
                            <h4 class="mb-0">{{ $review->rating }}/5</h4>
                        </div>
                    </div>

                    <hr>

                    <!-- Review Text -->
                    <div class="mb-4">
                        <h6 class="text-muted mb-2">Review</h6>
                        <p class="lead">{{ $review->review }}</p>
                    </div>

                    <hr>

                    <!-- Metadata -->
                    <div class="row text-muted small">
                        <div class="col-md-6">
                            <i class="fas fa-calendar me-1"></i>
                            <strong>Submitted:</strong> {{ $review->created_at->format('d M Y, h:i A') }}
                        </div>
                        @if($review->updated_at != $review->created_at)
                        <div class="col-md-6">
                            <i class="fas fa-edit me-1"></i>
                            <strong>Last Updated:</strong> {{ $review->updated_at->format('d M Y, h:i A') }}
                        </div>
                        @endif
                    </div>

                    <!-- Status Message -->
                    @if(!$review->is_approved)
                        <div class="alert alert-info mt-4">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Pending Approval:</strong> Your review is waiting for admin approval before it becomes visible to others.
                        </div>
                    @endif
                </div>
            </div>

            <!-- Actions -->
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex gap-2">
                        <a href="{{ route('student.reviews.edit', $review) }}" class="btn btn-primary">
                            <i class="fas fa-edit me-1"></i> Edit Review
                        </a>
                        <button type="button"
                                class="btn btn-danger"
                                onclick="if(confirm('Are you sure you want to delete this review? This action cannot be undone.')) { document.getElementById('delete-form').submit(); }">
                            <i class="fas fa-trash me-1"></i> Delete Review
                        </button>
                        <a href="{{ route('student.reviews.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Back to Reviews
                        </a>
                    </div>

                    <!-- Delete Form -->
                    <form id="delete-form"
                          action="{{ route('student.reviews.destroy', $review) }}"
                          method="POST"
                          class="d-none">
                        @csrf
                        @method('DELETE')
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Review Information Card -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i> Review Information</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-muted" width="40%">Status:</td>
                            <td>
                                @if($review->is_approved)
                                    <span class="badge bg-success">Approved</span>
                                @else
                                    <span class="badge bg-warning">Pending</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Rating:</td>
                            <td>
                                <strong>{{ $review->rating }}/5</strong>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Created:</td>
                            <td>{{ $review->created_at->format('d M Y') }}</td>
                        </tr>
                        @if($review->updated_at != $review->created_at)
                        <tr>
                            <td class="text-muted">Updated:</td>
                            <td>{{ $review->updated_at->format('d M Y') }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td class="text-muted">Characters:</td>
                            <td>{{ strlen($review->review) }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Class Details Card -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="fas fa-chalkboard me-2"></i> Class Details</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-muted" width="40%">Class:</td>
                            <td><strong>{{ $review->class->name }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Subject:</td>
                            <td>{{ $review->class->subject->name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Type:</td>
                            <td>
                                <span class="badge bg-{{ $review->class->type === 'online' ? 'primary' : 'success' }}">
                                    {{ ucfirst($review->class->type ?? 'N/A') }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Teacher:</td>
                            <td><strong>{{ $review->teacher->user->name }}</strong></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
