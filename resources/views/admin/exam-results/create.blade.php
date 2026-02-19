@extends('layouts.app')

@section('title', 'Enter Results - ' . $exam->name)

@section('content')
<div class="container-fluid">
    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="fas fa-plus-circle me-2"></i>Enter Exam Results</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.exams.index') }}">Exams</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.exams.show', $exam) }}">{{ $exam->name }}</a></li>
                    <li class="breadcrumb-item active">Enter Results</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('admin.exam-results.index', $exam) }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Results
        </a>
    </div>

    {{-- Exam Info Banner --}}
    <div class="alert alert-light border mb-4">
        <div class="row align-items-center">
            <div class="col-md-3">
                <strong>Exam:</strong> {{ $exam->name }}
            </div>
            <div class="col-md-3">
                <strong>Class:</strong> {{ $exam->class->name ?? 'N/A' }}
            </div>
            <div class="col-md-2">
                <strong>Subject:</strong> {{ $exam->subject->name ?? 'N/A' }}
            </div>
            <div class="col-md-2">
                <strong>Max Marks:</strong> <span class="badge bg-primary">{{ number_format($exam->max_marks, 0) }}</span>
            </div>
            <div class="col-md-2">
                <strong>Pass Marks:</strong> <span class="badge bg-warning text-dark">{{ number_format($exam->passing_marks, 0) }}</span>
            </div>
        </div>
    </div>

    {{-- Bulk Entry Form --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="fas fa-edit me-2"></i>Bulk Marks Entry</h6>
            <span class="badge bg-secondary">{{ $students->count() }} students</span>
        </div>
        <div class="card-body p-0">
            @if($students->count() > 0)
                <form action="{{ route('admin.exam-results.bulk-store', $exam) }}" method="POST" id="bulkEntryForm">
                    @csrf
                    @include('admin.exam-results._bulk-entry-form')

                    <div class="p-3 border-top d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted" id="filledCount">0</span> of {{ $students->count() }} marks entered
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.exam-results.index', $exam) }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="fas fa-save me-1"></i> Save All Results
                            </button>
                        </div>
                    </div>
                </form>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-users fa-3x text-muted mb-3 d-block"></i>
                    <p class="text-muted">No enrolled students found for this class.</p>
                    <a href="{{ route('admin.exams.show', $exam) }}" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Exam
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Track filled marks count
    function updateFilledCount() {
        var filled = $('.marks-input').filter(function() {
            return $(this).val() !== '' && $(this).val() !== null;
        }).length;
        $('#filledCount').text(filled);
    }

    $('.marks-input').on('input', function() {
        updateFilledCount();
    });

    // Initial count
    updateFilledCount();

    // Form validation before submit
    $('#bulkEntryForm').on('submit', function(e) {
        var filled = $('.marks-input').filter(function() {
            return $(this).val() !== '' && $(this).val() !== null;
        }).length;

        if (filled === 0) {
            e.preventDefault();
            alert('Please enter marks for at least one student.');
            return false;
        }

        // Validate marks don't exceed max
        var isValid = true;
        $('.marks-input').each(function() {
            var val = parseFloat($(this).val());
            var max = parseFloat($(this).data('max'));
            if (!isNaN(val) && val > max) {
                isValid = false;
                $(this).addClass('is-invalid');
            } else {
                $(this).removeClass('is-invalid');
            }
        });

        if (!isValid) {
            e.preventDefault();
            alert('Some marks exceed the maximum marks. Please correct them.');
            return false;
        }

        $('#submitBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Saving...');
    });
});
</script>
@endpush
@endsection
