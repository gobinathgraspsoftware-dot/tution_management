@extends('layouts.app')

@section('title', 'Exam Management')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Exam Management</h1>
            <p class="text-muted mb-0">Create and manage exams</p>
        </div>
        @can('create-exams')
            <a href="{{ route('admin.exams.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create Exam
            </a>
        @endcan
    </div>

    @include('admin.exams._stats')

    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">All Exams</h5>
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#filtersCollapse">
                    <i class="fas fa-filter"></i> Filters
                </button>
            </div>
        </div>
        <div class="card-body">
            @include('admin.exams._filters')

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Exam Name</th>
                            <th>Class</th>
                            <th>Subject</th>
                            <th>Date & Time</th>
                            <th>Marks</th>
                            <th>Duration</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($exams as $exam)
                            <tr>
                                <td>
                                    <strong>{{ $exam->name }}</strong>
                                    @if($exam->description)
                                        <br><small class="text-muted">{{ Str::limit($exam->description, 40) }}</small>
                                    @endif
                                </td>
                                <td>{{ $exam->class->name }}</td>
                                <td>
                                    <span class="badge bg-info">{{ $exam->subject->name }}</span>
                                </td>
                                <td>
                                    <div>{{ \Carbon\Carbon::parse($exam->exam_date)->format('M j, Y') }}</div>
                                    <small class="text-muted">
                                        {{ \Carbon\Carbon::parse($exam->start_time)->format('h:i A') }} -
                                        {{ \Carbon\Carbon::parse($exam->end_time)->format('h:i A') }}
                                    </small>
                                </td>
                                <td>
                                    <div>{{ $exam->max_marks }} marks</div>
                                    <small class="text-muted">Pass: {{ $exam->passing_marks }}</small>
                                </td>
                                <td>{{ $exam->duration }} min</td>
                                <td>
                                    <span class="badge
                                        @if($exam->status == 'completed') bg-success
                                        @elseif($exam->status == 'ongoing') bg-warning
                                        @elseif($exam->status == 'cancelled') bg-danger
                                        @else bg-primary
                                        @endif">
                                        {{ ucfirst($exam->status) }}
                                    </span>
                                </td>
                                <td>
                                    @include('admin.exams._actions', ['exam' => $exam])
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <i class="fas fa-inbox"></i> No exams found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $exams->links() }}
        </div>
    </div>
</div>
@endsection
