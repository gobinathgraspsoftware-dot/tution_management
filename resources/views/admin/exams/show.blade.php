@extends('layouts.app')

@section('title', 'Exam Details')

@section('content')
<div class="container-fluid">
    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="fas fa-file-signature me-2"></i>Exam Details</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.exams.index') }}">Exams</a></li>
                    <li class="breadcrumb-item active">{{ $exam->name }}</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            @if(Route::has('admin.exam-results.create'))
                <a href="{{ route('admin.exam-results.create', $exam) }}" class="btn btn-success">
                    <i class="fas fa-plus me-1"></i> Enter Results
                </a>
            @endif
            <a href="{{ route('admin.exams.edit', $exam) }}" class="btn btn-warning">
                <i class="fas fa-edit me-1"></i> Edit
            </a>
            <a href="{{ route('admin.exams.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <div class="row">
        {{-- Left Column: Exam Info --}}
        <div class="col-lg-8">
            {{-- Exam Info Card --}}
            @include('admin.exams._exam_card')

            {{-- Results Summary --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Results Summary</h6>
                    <div class="d-flex gap-2">
                        @if(Route::has('admin.exam-results.index'))
                            <a href="{{ route('admin.exam-results.index', $exam) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-list me-1"></i> View All Results
                            </a>
                        @endif
                        @if(Route::has('admin.exam-results.statistics'))
                            <a href="{{ route('admin.exam-results.statistics', $exam) }}" class="btn btn-sm btn-outline-info">
                                <i class="fas fa-chart-pie me-1"></i> Statistics
                            </a>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-2">
                            <div class="border rounded p-3">
                                <h4 class="mb-0 text-primary">{{ $stats['total_students'] ?? 0 }}</h4>
                                <small class="text-muted">Total Students</small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="border rounded p-3">
                                <h4 class="mb-0 text-info">{{ $stats['results_entered'] ?? 0 }}</h4>
                                <small class="text-muted">Results Entered</small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="border rounded p-3">
                                <h4 class="mb-0 text-success">{{ $stats['published_results'] ?? 0 }}</h4>
                                <small class="text-muted">Published</small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="border rounded p-3">
                                <h4 class="mb-0 text-warning">{{ $stats['average_marks'] ? number_format($stats['average_marks'], 1) : '0' }}</h4>
                                <small class="text-muted">Average Marks</small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="border rounded p-3">
                                <h4 class="mb-0 text-success">{{ $stats['pass_count'] ?? 0 }}</h4>
                                <small class="text-muted">Passed</small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="border rounded p-3">
                                @php
                                    $failCount = ($stats['results_entered'] ?? 0) - ($stats['pass_count'] ?? 0);
                                @endphp
                                <h4 class="mb-0 text-danger">{{ max(0, $failCount) }}</h4>
                                <small class="text-muted">Failed</small>
                            </div>
                        </div>
                    </div>

                    @if(($stats['results_entered'] ?? 0) > 0)
                        @php
                            $passPercentage = ($stats['results_entered'] > 0) 
                                ? round(($stats['pass_count'] / $stats['results_entered']) * 100, 1) 
                                : 0;
                        @endphp
                        <div class="mt-3">
                            <div class="d-flex justify-content-between mb-1">
                                <small class="fw-semibold">Pass Rate</small>
                                <small class="fw-semibold">{{ $passPercentage }}%</small>
                            </div>
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar bg-success" role="progressbar" 
                                     style="width: {{ $passPercentage }}%"></div>
                                <div class="progress-bar bg-danger" role="progressbar" 
                                     style="width: {{ 100 - $passPercentage }}%"></div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Recent Results Table --}}
            @if($exam->results->count() > 0)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="fas fa-clipboard-list me-2"></i>Recent Results</h6>
                        @if(Route::has('admin.exam-results.export'))
                            <a href="{{ route('admin.exam-results.export', $exam) }}" class="btn btn-sm btn-outline-success">
                                <i class="fas fa-download me-1"></i> Export CSV
                            </a>
                        @endif
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Student</th>
                                        <th>Marks</th>
                                        <th>Percentage</th>
                                        <th>Grade</th>
                                        <th>Rank</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($exam->results->sortBy('rank')->take(10) as $result)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-2" style="width:32px;height:32px;">
                                                        <i class="fas fa-user text-muted"></i>
                                                    </div>
                                                    <div>
                                                        <span class="fw-semibold">{{ $result->student->user->name ?? 'N/A' }}</span>
                                                        <br><small class="text-muted">{{ $result->student->student_id ?? '' }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="fw-semibold">{{ number_format($result->marks_obtained, 0) }}</span>
                                                <small class="text-muted">/ {{ number_format($exam->max_marks, 0) }}</small>
                                            </td>
                                            <td>{{ number_format($result->percentage, 1) }}%</td>
                                            <td>
                                                @php
                                                    $gradeColors = [
                                                        'A+' => 'success', 'A' => 'success',
                                                        'B+' => 'info', 'B' => 'info',
                                                        'C' => 'warning', 'D' => 'warning',
                                                        'F' => 'danger',
                                                    ];
                                                @endphp
                                                <span class="badge bg-{{ $gradeColors[$result->grade] ?? 'secondary' }}">
                                                    {{ $result->grade }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($result->rank)
                                                    @if($result->rank <= 3)
                                                        <span class="badge bg-warning text-dark">
                                                            <i class="fas fa-trophy me-1"></i>#{{ $result->rank }}
                                                        </span>
                                                    @else
                                                        #{{ $result->rank }}
                                                    @endif
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                @if($result->marks_obtained >= $exam->passing_marks)
                                                    <span class="badge bg-success">Pass</span>
                                                @else
                                                    <span class="badge bg-danger">Fail</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if($exam->results->count() > 10)
                        <div class="card-footer bg-white text-center">
                            <a href="{{ route('admin.exam-results.index', $exam) }}" class="text-decoration-none">
                                View all {{ $exam->results->count() }} results <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        {{-- Right Column: Actions --}}
        <div class="col-lg-4">
            {{-- Quick Actions --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if(Route::has('admin.exam-results.create'))
                            <a href="{{ route('admin.exam-results.create', $exam) }}" class="btn btn-success">
                                <i class="fas fa-plus-circle me-2"></i> Enter Results
                            </a>
                        @endif
                        @if(Route::has('admin.exam-results.index'))
                            <a href="{{ route('admin.exam-results.index', $exam) }}" class="btn btn-info text-white">
                                <i class="fas fa-clipboard-list me-2"></i> View Results
                            </a>
                        @endif
                        <a href="{{ route('admin.exams.edit', $exam) }}" class="btn btn-warning">
                            <i class="fas fa-edit me-2"></i> Edit Exam
                        </a>
                        @if(Route::has('admin.exams.duplicate'))
                            <form action="{{ route('admin.exams.duplicate', $exam) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-outline-secondary w-100">
                                    <i class="fas fa-copy me-2"></i> Duplicate Exam
                                </button>
                            </form>
                        @endif
                        @if(Route::has('admin.exam-results.export'))
                            <a href="{{ route('admin.exam-results.export', $exam) }}" class="btn btn-outline-success">
                                <i class="fas fa-download me-2"></i> Export Results
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Update Status Card --}}
            
            @if(Route::has('admin.exams.update-status'))
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="fas fa-sync-alt me-2"></i>Update Status</h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.exams.update-status', $exam) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Current Status</label>
                                @php
                                    $statusColors = [
                                        'scheduled' => 'warning',
                                        'ongoing' => 'primary',
                                        'completed' => 'success',
                                        'cancelled' => 'danger',
                                    ];
                                @endphp
                                <div>
                                    <span class="badge bg-{{ $statusColors[$exam->status] ?? 'secondary' }} fs-6">
                                        {{ ucfirst($exam->status) }}
                                    </span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="statusSelect" class="form-label fw-semibold">Change To</label>
                                <select name="status" id="statusSelect" class="form-select" required>
                                    <option value="">-- Select Status --</option>
                                    <option value="scheduled" {{ $exam->status == 'scheduled' ? 'selected' : '' }}>
                                        Scheduled
                                    </option>
                                    <option value="ongoing" {{ $exam->status == 'ongoing' ? 'selected' : '' }}>
                                        Ongoing
                                    </option>
                                    <option value="completed" {{ $exam->status == 'completed' ? 'selected' : '' }}>
                                        Completed
                                    </option>
                                    <option value="cancelled" {{ $exam->status == 'cancelled' ? 'selected' : '' }}>
                                        Cancelled
                                    </option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-check me-1"></i> Update Status
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            {{-- Publish / Unpublish Results --}}
            @if(($stats['results_entered'] ?? 0) > 0)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="fas fa-bullhorn me-2"></i>Publish Results</h6>
                    </div>
                    <div class="card-body">
                        @if(($stats['published_results'] ?? 0) > 0)
                            <div class="alert alert-success mb-3">
                                <i class="fas fa-check-circle me-1"></i>
                                Results are published ({{ $stats['published_results'] }} of {{ $stats['results_entered'] }})
                            </div>
                            @if(Route::has('admin.exam-results.unpublish'))
                                <form action="{{ route('admin.exam-results.unpublish', $exam) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-danger w-100" 
                                            onclick="return confirm('Are you sure you want to unpublish all results?')">
                                        <i class="fas fa-eye-slash me-1"></i> Unpublish Results
                                    </button>
                                </form>
                            @endif
                        @else
                            <div class="alert alert-info mb-3">
                                <i class="fas fa-info-circle me-1"></i>
                                {{ $stats['results_entered'] }} results ready to publish
                            </div>
                            @if(Route::has('admin.exam-results.publish'))
                                <form action="{{ route('admin.exam-results.publish', $exam) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-success w-100"
                                            onclick="return confirm('Publish results and notify parents?')">
                                        <i class="fas fa-bullhorn me-1"></i> Publish & Notify Parents
                                    </button>
                                </form>
                            @endif
                        @endif
                    </div>
                </div>
            @endif

            {{-- Danger Zone --}}
            <div class="card border-0 shadow-sm border-danger">
                <div class="card-header bg-white">
                    <h6 class="mb-0 text-danger"><i class="fas fa-exclamation-triangle me-2"></i>Danger Zone</h6>
                </div>
                <div class="card-body">
                    @if($exam->results->count() > 0)
                        <p class="text-muted small mb-2">
                            <i class="fas fa-info-circle me-1"></i>
                            This exam has {{ $exam->results->count() }} results. Delete all results first before deleting the exam.
                        </p>
                        <button class="btn btn-outline-danger w-100" disabled>
                            <i class="fas fa-trash me-1"></i> Delete Exam
                        </button>
                    @else
                        <form action="{{ route('admin.exams.destroy', $exam) }}" method="POST"
                              onsubmit="return confirm('Are you sure you want to permanently delete this exam?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger w-100">
                                <i class="fas fa-trash me-1"></i> Delete Exam
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
