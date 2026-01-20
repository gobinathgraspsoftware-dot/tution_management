@extends('layouts.app')

@section('title', 'Exam Details - ' . $exam->name)

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">{{ $exam->name }}</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('teacher.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('teacher.exams.index') }}">Exams</a></li>
                    <li class="breadcrumb-item active">Details</li>
                </ol>
            </nav>
        </div>
        <div>
            @if($exam->status == 'scheduled' || $exam->status == 'completed')
                <a href="{{ route('teacher.exams.enter-results', $exam) }}" class="btn btn-success me-2">
                    <i class="fas fa-edit me-1"></i> Enter Results
                </a>
            @endif
            @if($exam->results->count() == 0)
                <a href="{{ route('teacher.exams.edit', $exam) }}" class="btn btn-warning me-2">
                    <i class="fas fa-pencil-alt me-1"></i> Edit Exam
                </a>
            @endif
            <a href="{{ route('teacher.exams.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to List
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Exam Information -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Exam Information</h5>
                    @switch($exam->status)
                        @case('scheduled')
                            <span class="badge bg-info fs-6">Scheduled</span>
                            @break
                        @case('ongoing')
                            <span class="badge bg-warning text-dark fs-6">Ongoing</span>
                            @break
                        @case('completed')
                            <span class="badge bg-success fs-6">Completed</span>
                            @break
                        @case('cancelled')
                            <span class="badge bg-danger fs-6">Cancelled</span>
                            @break
                    @endswitch
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Exam Name</label>
                            <p class="mb-0 fw-semibold">{{ $exam->name }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Class</label>
                            <p class="mb-0">{{ $exam->class->name ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Subject</label>
                            <p class="mb-0">{{ $exam->subject->name ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Exam Date</label>
                            <p class="mb-0">
                                <i class="fas fa-calendar me-1 text-primary"></i>
                                {{ $exam->exam_date->format('l, M d, Y') }}
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Start Time</label>
                            <p class="mb-0">
                                <i class="fas fa-clock me-1 text-primary"></i>
                                @if($exam->start_time)
                                    {{ \Carbon\Carbon::parse($exam->start_time)->format('h:i A') }}
                                @else
                                    N/A
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Duration</label>
                            <p class="mb-0">
                                <i class="fas fa-hourglass-half me-1 text-primary"></i>
                                @if($exam->duration_minutes)
                                    @php
                                        $hours = floor($exam->duration_minutes / 60);
                                        $mins = $exam->duration_minutes % 60;
                                    @endphp
                                    @if($hours > 0)
                                        {{ $hours }} hr {{ $mins > 0 ? $mins . ' mins' : '' }}
                                    @else
                                        {{ $mins }} mins
                                    @endif
                                @else
                                    N/A
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Maximum Marks</label>
                            <p class="mb-0 fs-5 fw-bold text-primary">{{ number_format($exam->max_marks, 2) }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Passing Marks</label>
                            <p class="mb-0 fs-5 fw-bold text-success">{{ number_format($exam->passing_marks, 2) }}</p>
                        </div>
                    </div>

                    @if($exam->description)
                        <hr>
                        <div class="mb-0">
                            <label class="text-muted small">Description</label>
                            <p class="mb-0">{{ $exam->description }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Results Table -->
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-list-ol me-2"></i>Exam Results</h5>
                    @if($exam->results->count() > 0)
                        <span class="badge bg-success">{{ $exam->results->count() }} Results Entered</span>
                    @endif
                </div>
                <div class="card-body">
                    @if($exam->results->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Student</th>
                                        <th class="text-center">Marks</th>
                                        <th class="text-center">Percentage</th>
                                        <th class="text-center">Grade</th>
                                        <th class="text-center">Status</th>
                                        <th>Remarks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($exam->results->sortByDesc('marks_obtained') as $index => $result)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <strong>{{ $result->student->user->name ?? 'N/A' }}</strong>
                                                <br><small class="text-muted">{{ $result->student->student_id ?? '' }}</small>
                                            </td>
                                            <td class="text-center">
                                                <span class="fw-bold">{{ number_format($result->marks_obtained, 2) }}</span>
                                                <span class="text-muted">/ {{ number_format($exam->max_marks, 2) }}</span>
                                            </td>
                                            <td class="text-center">{{ number_format($result->percentage, 1) }}%</td>
                                            <td class="text-center">
                                                @php
                                                    $gradeClass = match($result->grade) {
                                                        'A+', 'A' => 'success',
                                                        'B' => 'info',
                                                        'C' => 'primary',
                                                        'D' => 'warning',
                                                        'E' => 'secondary',
                                                        default => 'danger'
                                                    };
                                                @endphp
                                                <span class="badge bg-{{ $gradeClass }}">{{ $result->grade }}</span>
                                            </td>
                                            <td class="text-center">
                                                @if($result->marks_obtained >= $exam->passing_marks)
                                                    <span class="badge bg-success"><i class="fas fa-check"></i> Pass</span>
                                                @else
                                                    <span class="badge bg-danger"><i class="fas fa-times"></i> Fail</span>
                                                @endif
                                            </td>
                                            <td>
                                                <small>{{ $result->remarks ?? '-' }}</small>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No Results Entered Yet</h5>
                            <p class="text-muted">Click the button below to enter student results.</p>
                            <a href="{{ route('teacher.exams.enter-results', $exam) }}" class="btn btn-primary">
                                <i class="fas fa-edit me-1"></i> Enter Results
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Statistics Sidebar -->
        <div class="col-md-4">
            <!-- Quick Stats -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="border rounded p-3 text-center bg-light">
                                <h3 class="mb-0 text-primary">{{ $stats['total_students'] }}</h3>
                                <small class="text-muted">Total Students</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-3 text-center bg-light">
                                <h3 class="mb-0 text-success">{{ $stats['results_entered'] }}</h3>
                                <small class="text-muted">Results Entered</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-3 text-center bg-light">
                                <h3 class="mb-0 text-warning">{{ $stats['pending_results'] }}</h3>
                                <small class="text-muted">Pending</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-3 text-center bg-light">
                                <h3 class="mb-0 text-info">{{ number_format($stats['average_score'], 1) }}</h3>
                                <small class="text-muted">Average Score</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Performance Summary -->
            @if($stats['results_entered'] > 0)
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-trophy me-2"></i>Performance Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <span>Highest Score</span>
                            <span class="fw-bold text-success">{{ number_format($stats['highest_score'], 2) }} / {{ number_format($exam->max_marks, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span>Lowest Score</span>
                            <span class="fw-bold text-danger">{{ number_format($stats['lowest_score'], 2) }} / {{ number_format($exam->max_marks, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span>Average Score</span>
                            <span class="fw-bold text-primary">{{ number_format($stats['average_score'], 1) }} / {{ number_format($exam->max_marks, 2) }}</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-2">
                            <span><i class="fas fa-check-circle text-success me-1"></i> Passed</span>
                            <span class="badge bg-success">{{ $stats['pass_count'] }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span><i class="fas fa-times-circle text-danger me-1"></i> Failed</span>
                            <span class="badge bg-danger">{{ $stats['fail_count'] }}</span>
                        </div>

                        @if($stats['results_entered'] > 0)
                            <hr>
                            <div class="progress" style="height: 25px;">
                                @php
                                    $passPercentage = ($stats['pass_count'] / $stats['results_entered']) * 100;
                                @endphp
                                <div class="progress-bar bg-success" role="progressbar" 
                                     style="width: {{ $passPercentage }}%">
                                    {{ number_format($passPercentage, 0) }}% Pass
                                </div>
                                <div class="progress-bar bg-danger" role="progressbar" 
                                     style="width: {{ 100 - $passPercentage }}%">
                                    {{ number_format(100 - $passPercentage, 0) }}% Fail
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Enrolled Students -->
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-users me-2"></i>Enrolled Students</h5>
                </div>
                <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                    @if($enrolledStudents->count() > 0)
                        <ul class="list-group list-group-flush">
                            @foreach($enrolledStudents as $student)
                                @php
                                    $hasResult = $exam->results->where('student_id', $student->id)->first();
                                @endphp
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <div>
                                        <strong>{{ $student->user->name ?? 'N/A' }}</strong>
                                        <br><small class="text-muted">{{ $student->student_id ?? '' }}</small>
                                    </div>
                                    @if($hasResult)
                                        <span class="badge bg-success"><i class="fas fa-check"></i></span>
                                    @else
                                        <span class="badge bg-secondary"><i class="fas fa-minus"></i></span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted text-center mb-0">No students enrolled</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
