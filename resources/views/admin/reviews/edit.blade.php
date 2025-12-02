@extends('layouts.app')

@section('title', 'Edit Review')
@section('page-title', 'Edit Review')

@section('content')
<div class="page-header">
    <div>
        <h1><i class="fas fa-edit me-2"></i> Edit Review</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.reviews.index') }}">Reviews</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </nav>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.reviews.update', $review) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Student <span class="text-danger">*</span></label>
                        <select name="student_id" class="form-select @error('student_id') is-invalid @enderror" required>
                            <option value="">Select Student...</option>
                            @foreach($students as $student)
                                <option value="{{ $student->id }}" {{ old('student_id', $review->student_id) == $student->id ? 'selected' : '' }}>
                                    {{ $student->user->name }} ({{ $student->student_id }})
                                </option>
                            @endforeach
                        </select>
                        @error('student_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Class</label>
                        <select name="class_id" class="form-select @error('class_id') is-invalid @enderror">
                            <option value="">Select Class (Optional)...</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" {{ old('class_id', $review->class_id) == $class->id ? 'selected' : '' }}>
                                    {{ $class->name }} ({{ $class->subject->name ?? 'N/A' }})
                                </option>
                            @endforeach
                        </select>
                        @error('class_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Teacher</label>
                        <select name="teacher_id" class="form-select @error('teacher_id') is-invalid @enderror">
                            <option value="">Select Teacher (Optional)...</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}" {{ old('teacher_id', $review->teacher_id) == $teacher->id ? 'selected' : '' }}>
                                    {{ $teacher->user->name }} ({{ $teacher->teacher_id }})
                                </option>
                            @endforeach
                        </select>
                        @error('teacher_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Rating <span class="text-danger">*</span></label>
                        <div class="rating-input p-2 bg-light rounded">
                            @for($i = 5; $i >= 1; $i--)
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="rating" id="rating{{ $i }}" value="{{ $i }}" {{ old('rating', $review->rating) == $i ? 'checked' : '' }} required>
                                    <label class="form-check-label" for="rating{{ $i }}">
                                        {{ $i }} <i class="fas fa-star text-warning"></i>
                                    </label>
                                </div>
                            @endfor
                        </div>
                        @error('rating')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Review Text</label>
                <textarea name="review" class="form-control @error('review') is-invalid @enderror" rows="5" placeholder="Enter the review...">{{ old('review', $review->review) }}</textarea>
                @error('review')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_approved" id="is_approved" value="1" {{ old('is_approved', $review->is_approved) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_approved">
                        Approve this review
                    </label>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('admin.reviews.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Update Review
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
