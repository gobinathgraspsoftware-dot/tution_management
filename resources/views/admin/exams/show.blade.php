@extends('layouts.app')

@section('title', 'Exam Details')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.exams.index') }}">Exams</a></li>
                <li class="breadcrumb-item active">Details</li>
            </ol>
        </nav>
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-0">{{ $exam->name }}</h1>
            <div class="btn-group">
                @can('edit-exams')
                    <a href="{{ route('admin.exams.edit', $exam) }}" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                @endcan
                @can('create-exam-results')
                    <a href="{{ route('admin.exam-results.create', $exam) }}" class="btn btn-success">
                        <i class="fas fa-pen-square"></i> Enter Results
                    </a>
                @endcan
                <a href="{{ route('admin.exam-results.index', $exam) }}" class="btn btn-info">
                    <i class="fas fa-chart-bar"></i> View Results
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Exam Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <dl class="row">
                                <dt class="col-sm-5">Class:</dt>
                                <dd class="col-sm-7">{{ $exam->class->name }}</dd>

                                <dt class="col-sm-5">Subject:</dt>
                                <dd class="col-sm-7">
                                    <span class="badge bg-info">{{ $exam->subject->name }}</span>
                                </dd>

                                <dt class="col-sm-5">Exam Date:</dt>
                                <dd class="col-sm-7">{{ \Carbon\Carbon::parse($exam->exam_date)->format('F j, Y') }}</dd>

                                <dt class="col-sm-5">Time:</dt>
                                <dd class="col-sm-7">
                                    {{ \Carbon\Carbon::parse($exam->start_time)->format('h:i A') }} -
                                    {{ \Carbon\Carbon::parse($exam->end_time)->format('h:i A') }}
                                </dd>
                            </dl>
                        </div>
                        <div class="col-md-6">
                            <dl class="row">
                                <dt class="col-sm-5">Duration:</dt>
                                <dd class="col-sm-7">{{ $exam->duration }} minutes</dd>

                                <dt class="col-sm-5">Max Marks:</dt>
                                <dd class="col-sm-7">{{ $exam->max_marks }}</dd>

                                <dt class="col-sm-5">Passing Marks:</dt>
                                <dd class="col-sm-7">{{ $exam->passing_marks }}</dd>

                                <dt class="col-sm-5">Status:</dt>
                                <dd class="col-sm-7">
                                    <span class="badge
                                        @if($exam->status == 'completed') bg-success
                                        @elseif($exam->status == 'ongoing') bg-warning
                                        @elseif($exam->status == 'cancelled') bg-danger
                                        @else bg-primary
                                        @endif">
                                        {{ ucfirst($exam->status) }}
                                    </span>
                                </dd>
                            </dl>
                        </div>
                    </div>

                    @if($exam->description)
                        <div class="mt-3">
                            <h6>Description</h6>
                            <p class="text-muted">{{ $exam->description }}</p>
                        </div>
                    @endif

                    @if($exam->instructions)
                        <div class="mt-3">
                            <h6>Instructions</h6>
                            <div class="bg-light p-3 rounded">
                                {!! nl2br(e($exam->instructions)) !!}
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Results Statistics -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Results Overview</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <h3 class="text-primary">{{ $stats['total_students'] }}</h3>
                            <p class="text-muted small mb-0">Total Students</p>
                        </div>
                        <div class="col-md-3">
                            <h3 class="text-info">{{ $stats['results_entered'] }}</h3>
                            <p class="text-muted small mb-0">Results Entered</p>
                        </div>
                        <div class="col-md-3">
                            <h3 class="text-success">{{ $stats['pass_count'] }}</h3>
                            <p class="text-muted small mb-0">Passed</p>
                        </div>
                        <div class="col-md-3">
                            <h3 class="text-warning">{{ number_format($stats['average_marks'], 2) }}</h3>
                            <p class="text-muted small mb-0">Average Marks</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">Quick Actions</h6>
                </div>
                <div class="card-body">
                    @can('create-exam-results')
                        <a href="{{ route('admin.exam-results.create', $exam) }}" class="btn btn-success w-100 mb-2">
                            <i class="fas fa-pen-square"></i> Enter Results
                        </a>
                    @endcan
                    <a href="{{ route('admin.exam-results.index', $exam) }}" class="btn btn-info w-100 mb-2">
                        <i class="fas fa-list"></i> View All Results
                    </a>
                    <a href="{{ route('admin.exam-results.statistics', $exam) }}" class="btn btn-primary w-100 mb-2">
                        <i class="fas fa-chart-bar"></i> Detailed Statistics
                    </a>
                    @can('edit-exams')
                        <form action="{{ route('admin.exams.duplicate', $exam) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-copy"></i> Duplicate Exam
                            </button>
                        </form>
                    @endcan
                </div>
            </div>

            <!-- Status Update -->
            @can('edit-exams')
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Update Status</h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.exams.updateStatus', $exam) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <select name="status" class="form-select mb-2" required>
                                <option value="scheduled" {{ $exam->status == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                                <option value="ongoing" {{ $exam->status == 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                                <option value="completed" {{ $exam->status == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="cancelled" {{ $exam->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                            <button type="submit" class="btn btn-primary w-100">
                                Update Status
                            </button>
                        </form>
                    </div>
                </div>
            @endcan
        </div>
    </div>
</div>
@endsection
