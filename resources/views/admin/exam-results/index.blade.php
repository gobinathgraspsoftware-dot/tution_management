@extends('layouts.app')

@section('title', 'Exam Results')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.exams.index') }}">Exams</a></li>
                <li class="breadcrumb-item active">Results</li>
            </ol>
        </nav>
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-1">{{ $exam->name }} - Results</h1>
                <p class="text-muted mb-0">{{ $exam->class->name }} | {{ $exam->subject->name }}</p>
            </div>
            <div class="btn-group">
                @can('create-exam-results')
                    <a href="{{ route('admin.exam-results.create', $exam) }}" class="btn btn-success">
                        <i class="fas fa-pen-square"></i> Enter Results
                    </a>
                @endcan
                <a href="{{ route('admin.exam-results.statistics', $exam) }}" class="btn btn-info">
                    <i class="fas fa-chart-bar"></i> Statistics
                </a>
                @can('publish-exam-results')
                    @if($exam->results()->whereNotNull('marks_obtained')->where('is_published', false)->count() > 0)
                        <form action="{{ route('admin.exam-results.publish', $exam) }}" method="POST" class="d-inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-primary" onclick="return confirm('Publish all results and notify parents?')">
                                <i class="fas fa-paper-plane"></i> Publish Results
                            </button>
                        </form>
                    @endif
                @endcan
                @can('export-exam-results')
                    <a href="{{ route('admin.exam-results.export', $exam) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-download"></i> Export
                    </a>
                @endcan
            </div>
        </div>
    </div>

    @include('admin.exam-results._stats')

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Student Results</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Student ID</th>
                            <th>Marks Obtained</th>
                            <th>Percentage</th>
                            <th>Grade</th>
                            <th>Rank</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($students as $enrollment)
                            @php
                                $student = $enrollment->student;
                                $result = $exam->results()->where('student_id', $student->id)->first();
                            @endphp
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="me-2">
                                            @if($student->user->avatar)
                                                <img src="{{ Storage::url($student->user->avatar) }}" alt="{{ $student->user->name }}"
                                                     class="rounded-circle" style="width: 32px; height: 32px; object-fit: cover;">
                                            @else
                                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center"
                                                     style="width: 32px; height: 32px;">
                                                    {{ substr($student->user->name, 0, 1) }}
                                                </div>
                                            @endif
                                        </div>
                                        <div>
                                            <strong>{{ $student->user->name }}</strong>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $student->student_id }}</td>
                                <td>
                                    @if($result && $result->marks_obtained !== null)
                                        <strong>{{ $result->marks_obtained }}</strong> / {{ $exam->max_marks }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($result && $result->percentage !== null)
                                        {{ number_format($result->percentage, 2) }}%
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($result && $result->grade)
                                        <span class="badge bg-primary">{{ $result->grade }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($result && $result->rank)
                                        <span class="badge bg-success">{{ $result->rank }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($result)
                                        @if($result->is_published)
                                            <span class="badge bg-success">Published</span>
                                        @else
                                            <span class="badge bg-warning">Unpublished</span>
                                        @endif
                                    @else
                                        <span class="badge bg-secondary">Not Entered</span>
                                    @endif
                                </td>
                                <td>
                                    @if($result)
                                        <div class="btn-group btn-group-sm">
                                            @can('edit-exam-results')
                                                <a href="{{ route('admin.exam-results.edit', $result) }}" class="btn btn-outline-primary" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endcan
                                            @can('generate-result-cards')
                                                <a href="{{ route('admin.exam-results.result-card', $result) }}" class="btn btn-outline-info" title="View Result Card">
                                                    <i class="fas fa-id-card"></i>
                                                </a>
                                                <a href="{{ route('admin.exam-results.download-result-card', $result) }}" class="btn btn-outline-success" title="Download PDF">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                            @endcan
                                        </div>
                                    @else
                                        <span class="text-muted small">No result</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <i class="fas fa-inbox"></i> No students found in this class.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
