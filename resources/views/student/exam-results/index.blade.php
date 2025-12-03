@extends('layouts.app')

@section('title', 'My Exam Results')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h1 class="h3 mb-1">My Exam Results</h1>
        <p class="text-muted mb-0">View your examination performance</p>
    </div>

    @php
        $results = auth()->user()->student->results()
            ->with(['exam.class.subject', 'exam.subject'])
            ->where('is_published', true)
            ->latest('created_at')
            ->paginate(12);
    @endphp

    @if($results->count() > 0)
        <div class="row">
            @foreach($results as $result)
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="badge bg-info">{{ $result->exam->subject->name }}</span>
                                @if($result->marks_obtained >= $result->exam->passing_marks)
                                    <span class="badge bg-success">Passed</span>
                                @else
                                    <span class="badge bg-danger">Failed</span>
                                @endif
                            </div>

                            <h5 class="card-title">{{ $result->exam->name }}</h5>
                            <p class="text-muted small">{{ $result->exam->class->name }}</p>

                            <div class="row text-center mb-3">
                                <div class="col-6">
                                    <h4 class="mb-0 text-primary">{{ $result->marks_obtained }}/{{ $result->exam->max_marks }}</h4>
                                    <small class="text-muted">Marks</small>
                                </div>
                                <div class="col-6">
                                    <h4 class="mb-0 text-success">{{ number_format($result->percentage, 1) }}%</h4>
                                    <small class="text-muted">Percentage</small>
                                </div>
                            </div>

                            <div class="row text-center">
                                <div class="col-6">
                                    <span class="badge bg-primary">Grade: {{ $result->grade }}</span>
                                </div>
                                <div class="col-6">
                                    <span class="badge bg-warning">Rank: {{ $result->rank }}</span>
                                </div>
                            </div>

                            <div class="mt-3">
                                <a href="{{ route('student.exam-results.show', $result) }}" class="btn btn-sm btn-outline-primary w-100">
                                    View Details <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{ $results->links() }}
    @else
        <div class="alert alert-info text-center">
            <i class="fas fa-info-circle"></i> No exam results available yet.
        </div>
    @endif
</div>
@endsection
