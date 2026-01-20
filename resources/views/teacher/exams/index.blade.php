@extends('layouts.app')

@section('title', 'My Exams')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">My Exams</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('teacher.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Exams</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('teacher.exams.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Create Exam
        </a>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title mb-0">Total Exams</h6>
                            <h2 class="mb-0">{{ $stats['total_exams'] }}</h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-file-alt fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title mb-0">Upcoming</h6>
                            <h2 class="mb-0">{{ $stats['upcoming_exams'] }}</h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-calendar-alt fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title mb-0">Completed</h6>
                            <h2 class="mb-0">{{ $stats['completed_exams'] }}</h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-check-circle fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title mb-0">Pending Results</h6>
                            <h2 class="mb-0">{{ $stats['pending_results'] }}</h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-clipboard-list fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('teacher.exams.index') }}" method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Class</label>
                    <select name="class_id" class="form-select">
                        <option value="">All Classes</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                {{ $class->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Subject</label>
                    <select name="subject_id" class="form-select">
                        <option value="">All Subjects</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                                {{ $subject->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                        <option value="ongoing" {{ request('status') == 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">From Date</label>
                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search"></i> Filter
                    </button>
                    <a href="{{ route('teacher.exams.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-undo"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Exams List -->
    <div class="card">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-file-signature me-2"></i>Exams List</h5>
        </div>
        <div class="card-body">
            @if($exams->isEmpty())
                <div class="text-center py-5">
                    <i class="fas fa-clipboard-list fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted">No Exams Found</h5>
                    <p class="text-muted">Create your first exam to get started.</p>
                    <a href="{{ route('teacher.exams.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Create Exam
                    </a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Title</th>
                                <th>Class</th>
                                <th>Subject</th>
                                <th>Date</th>
                                <th>Total Marks</th>
                                <th>Status</th>
                                <th>Results</th>
                                <th width="150">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($exams as $exam)
                                <tr>
                                    <td>
                                        <strong>{{ $exam->title }}</strong>
                                        <br><small class="text-muted">{{ ucfirst($exam->exam_type) }}</small>
                                    </td>
                                    <td>{{ $exam->class->name ?? 'N/A' }}</td>
                                    <td>{{ $exam->subject->name ?? 'N/A' }}</td>
                                    <td>
                                        {{ $exam->exam_date->format('M d, Y') }}
                                        <br><small class="text-muted">{{ \Carbon\Carbon::parse($exam->start_time)->format('h:i A') }}</small>
                                    </td>
                                    <td>
                                        <strong>{{ $exam->total_marks }}</strong>
                                        <br><small class="text-muted">Pass: {{ $exam->passing_marks }}</small>
                                    </td>
                                    <td>
                                        @switch($exam->status)
                                            @case('scheduled')
                                                <span class="badge bg-info">Scheduled</span>
                                                @break
                                            @case('ongoing')
                                                <span class="badge bg-warning text-dark">Ongoing</span>
                                                @break
                                            @case('completed')
                                                <span class="badge bg-success">Completed</span>
                                                @break
                                            @case('cancelled')
                                                <span class="badge bg-danger">Cancelled</span>
                                                @break
                                        @endswitch
                                    </td>
                                    <td>
                                        @php
                                            $resultCount = $exam->results->count();
                                        @endphp
                                        @if($resultCount > 0)
                                            <span class="badge bg-success">{{ $resultCount }} entered</span>
                                        @else
                                            <span class="badge bg-secondary">None</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('teacher.exams.show', $exam) }}" 
                                               class="btn btn-sm btn-outline-primary" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($exam->status == 'completed' || $exam->status == 'scheduled')
                                                <a href="{{ route('teacher.exams.enter-results', $exam) }}" 
                                                   class="btn btn-sm btn-outline-success" title="Enter Results">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endif
                                            @if($exam->results->count() == 0)
                                                <a href="{{ route('teacher.exams.edit', $exam) }}" 
                                                   class="btn btn-sm btn-outline-warning" title="Edit">
                                                    <i class="fas fa-pencil-alt"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $exams->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
