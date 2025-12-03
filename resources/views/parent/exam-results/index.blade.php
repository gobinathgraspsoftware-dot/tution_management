@extends('layouts.app')

@section('title', 'Children Exam Results')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h1 class="h3 mb-1">Children's Exam Results</h1>
        <p class="text-muted mb-0">View your children's examination performance</p>
    </div>

    @php
        $parent = auth()->user()->parent;
        $children = $parent->students;
    @endphp

    @if($children->count() > 0)
        @foreach($children as $child)
            @php
                $results = $child->results()
                    ->with(['exam.class.subject', 'exam.subject'])
                    ->where('is_published', true)
                    ->latest('created_at')
                    ->limit(4)
                    ->get();
            @endphp

            @if($results->count() > 0)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">{{ $child->user->name }} - Recent Results</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($results as $result)
                                <div class="col-md-6 col-lg-3 mb-3">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6 class="card-title">{{ $result->exam->name }}</h6>
                                            <p class="small text-muted">{{ $result->exam->subject->name }}</p>
                                            <div class="text-center">
                                                <h4 class="text-primary">{{ $result->marks_obtained }}/{{ $result->exam->max_marks }}</h4>
                                                <p class="small mb-0">Grade: <span class="badge bg-primary">{{ $result->grade }}</span></p>
                                            </div>
                                            <a href="{{ route('parent.exam-results.show', $result) }}" class="btn btn-sm btn-outline-primary w-100 mt-2">
                                                View Details
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    @else
        <div class="alert alert-info text-center">
            <i class="fas fa-info-circle"></i> No children records found.
        </div>
    @endif
</div>
@endsection
