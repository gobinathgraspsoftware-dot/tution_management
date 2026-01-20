@extends('layouts.app')

@section('title', 'Exam Results')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Exam Results</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('teacher.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Results</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title mb-0">Total Results</h6>
                            <h2 class="mb-0">{{ $stats['total_results'] }}</h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-file-alt fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title mb-0">Average Score</h6>
                            <h2 class="mb-0">{{ $stats['average_score'] }}%</h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-chart-line fa-2x opacity-50"></i>
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
                            <h6 class="card-title mb-0">Pass Rate</h6>
                            <h2 class="mb-0">{{ $stats['pass_rate'] }}%</h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-check-circle fa-2x opacity-50"></i>
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
                            <h6 class="card-title mb-0">Top Performers</h6>
                            <h2 class="mb-0">{{ $stats['top_performers'] }}</h2>
                            <small>A+ Grade</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-star fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('teacher.results.index') }}" method="GET" class="row g-3">
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
                    <label class="form-label">Exam</label>
                    <select name="exam_id" class="form-select">
                        <option value="">All Exams</option>
                        @foreach($exams as $exam)
                            <option value="{{ $exam->id }}" {{ request('exam_id') == $exam->id ? 'selected' : '' }}>
                                {{ $exam->title }} ({{ $exam->exam_date->format('M d') }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Grade</label>
                    <select name="grade" class="form-select">
                        <option value="">All Grades</option>
                        <option value="A+" {{ request('grade') == 'A+' ? 'selected' : '' }}>A+</option>
                        <option value="A" {{ request('grade') == 'A' ? 'selected' : '' }}>A</option>
                        <option value="B" {{ request('grade') == 'B' ? 'selected' : '' }}>B</option>
                        <option value="C" {{ request('grade') == 'C' ? 'selected' : '' }}>C</option>
                        <option value="D" {{ request('grade') == 'D' ? 'selected' : '' }}>D</option>
                        <option value="E" {{ request('grade') == 'E' ? 'selected' : '' }}>E</option>
                        <option value="F" {{ request('grade') == 'F' ? 'selected' : '' }}>F</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        <option value="pass" {{ request('status') == 'pass' ? 'selected' : '' }}>Passed</option>
                        <option value="fail" {{ request('status') == 'fail' ? 'selected' : '' }}>Failed</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search"></i> Filter
                    </button>
                    <a href="{{ route('teacher.results.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-undo"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Results List -->
    <div class="card">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Results List</h5>
        </div>
        <div class="card-body">
            @if($results->isEmpty())
                <div class="text-center py-5">
                    <i class="fas fa-clipboard-list fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted">No Results Found</h5>
                    <p class="text-muted">Results will appear here after exams are completed.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Student</th>
                                <th>Exam</th>
                                <th>Class</th>
                                <th>Marks</th>
                                <th>Percentage</th>
                                <th>Grade</th>
                                <th>Status</th>
                                <th width="120">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($results as $result)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm me-2">
                                                <span class="avatar-initial rounded-circle bg-primary">
                                                    {{ strtoupper(substr($result->student->user->name ?? 'S', 0, 1)) }}
                                                </span>
                                            </div>
                                            <div>
                                                <strong>{{ $result->student->user->name ?? 'N/A' }}</strong>
                                                <br><small class="text-muted">{{ $result->student->student_id ?? '' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <strong>{{ $result->exam->title }}</strong>
                                        <br><small class="text-muted">{{ $result->exam->exam_date->format('M d, Y') }}</small>
                                    </td>
                                    <td>{{ $result->exam->class->name ?? 'N/A' }}</td>
                                    <td>
                                        <strong>{{ $result->marks_obtained }}</strong> / {{ $result->exam->total_marks }}
                                    </td>
                                    <td>
                                        <div class="progress" style="height: 20px; width: 80px;">
                                            <div class="progress-bar {{ $result->percentage >= 50 ? 'bg-success' : 'bg-danger' }}" 
                                                 role="progressbar" 
                                                 style="width: {{ $result->percentage }}%">
                                                {{ round($result->percentage, 1) }}%
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $result->grade == 'F' ? 'danger' : ($result->grade == 'A+' || $result->grade == 'A' ? 'success' : 'primary') }} fs-6">
                                            {{ $result->grade }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($result->marks_obtained >= $result->exam->passing_marks)
                                            <span class="badge bg-success">Passed</span>
                                        @else
                                            <span class="badge bg-danger">Failed</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('teacher.results.show', $result) }}" 
                                               class="btn btn-sm btn-outline-primary" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('teacher.results.edit', $result) }}" 
                                               class="btn btn-sm btn-outline-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $results->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<style>
.avatar-initial {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    font-size: 14px;
    font-weight: 600;
    color: #fff;
}
</style>
@endsection
