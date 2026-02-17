@extends('layouts.app')

@section('title', 'Exam Result Details')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Exam Result Details</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('student.results.index') }}">Exam Results</a></li>
                    <li class="breadcrumb-item active">{{ $result->exam->title }}</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('student.results.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Results
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Main Result Card -->
        <div class="col-lg-8">
            <!-- Score Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-gradient-primary text-white">
                    <div class="row align-items-center">
                        <div class="col">
                            <h4 class="mb-0">{{ $result->exam->title }}</h4>
                            <small>{{ ucfirst($result->exam->exam_type) }} Exam</small>
                        </div>
                        <div class="col-auto">
                            @php
                                $gradeColors = [
                                    'A+' => 'success',
                                    'A' => 'success',
                                    'B+' => 'info',
                                    'B' => 'info',
                                    'C+' => 'warning',
                                    'C' => 'warning',
                                    'D' => 'orange',
                                    'F' => 'danger'
                                ];
                                $color = $gradeColors[$result->grade] ?? 'secondary';
                            @endphp
                            <div class="badge bg-{{ $color }} fs-1 p-3">
                                {{ $result->grade }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Score Display -->
                    <div class="row text-center mb-4">
                        <div class="col-md-4">
                            <div class="border rounded p-3">
                                <h6 class="text-muted mb-2">Marks Obtained</h6>
                                <h2 class="mb-0 text-primary">{{ $result->marks_obtained }}</h2>
                                <small class="text-muted">out of {{ $result->exam->total_marks }}</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded p-3">
                                <h6 class="text-muted mb-2">Percentage</h6>
                                <h2 class="mb-0 {{ $result->percentage >= 60 ? 'text-success' : 'text-danger' }}">
                                    {{ number_format($result->percentage, 1) }}%
                                </h2>
                                <small class="text-muted">
                                    @if($result->percentage >= 80)
                                        Excellent
                                    @elseif($result->percentage >= 60)
                                        Good
                                    @elseif($result->percentage >= 50)
                                        Average
                                    @else
                                        Need Improvement
                                    @endif
                                </small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded p-3">
                                <h6 class="text-muted mb-2">Class Average</h6>
                                <h2 class="mb-0 text-info">{{ number_format($classAverage, 1) }}</h2>
                                <small class="text-muted">
                                    @if($result->marks_obtained > $classAverage)
                                        <i class="fas fa-arrow-up text-success"></i> Above Average
                                    @elseif($result->marks_obtained < $classAverage)
                                        <i class="fas fa-arrow-down text-danger"></i> Below Average
                                    @else
                                        <i class="fas fa-equals text-warning"></i> At Average
                                    @endif
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Exam Information -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong><i class="fas fa-book me-2 text-primary"></i>Subject:</strong>
                            <p class="mb-0">{{ $result->exam->class->subject->name ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong><i class="fas fa-chalkboard me-2 text-info"></i>Class:</strong>
                            <p class="mb-0">{{ $result->exam->class->name ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong><i class="fas fa-calendar me-2 text-success"></i>Exam Date:</strong>
                            <p class="mb-0">
                                {{ $result->exam->exam_date ? $result->exam->exam_date->format('d M Y') : 'N/A' }}
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong><i class="fas fa-clock me-2 text-warning"></i>Duration:</strong>
                            <p class="mb-0">{{ $result->exam->duration ?? 'N/A' }} minutes</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong><i class="fas fa-chalkboard-teacher me-2 text-secondary"></i>Teacher:</strong>
                            <p class="mb-0">{{ $result->exam->class->teacher->user->name ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong><i class="fas fa-check-circle me-2 text-success"></i>Status:</strong>
                            <p class="mb-0">
                                @if($result->percentage >= 50)
                                    <span class="badge bg-success">Passed</span>
                                @else
                                    <span class="badge bg-danger">Failed</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    @if($result->remarks)
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <strong><i class="fas fa-comment me-2"></i>Remarks:</strong>
                                    <p class="mb-0 mt-2">{{ $result->remarks }}</p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Performance Analysis -->
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Performance Analysis</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12 mb-4">
                            <h6 class="mb-3">Score Breakdown</h6>
                            <div class="progress" style="height: 30px;">
                                @php
                                    // Fix division by zero
                                    $percentage = $result->exam->total_marks > 0
                                        ? ($result->marks_obtained / $result->exam->total_marks) * 100
                                        : 0;
                                    $progressColor = $percentage >= 80 ? 'success' : ($percentage >= 60 ? 'info' : ($percentage >= 50 ? 'warning' : 'danger'));
                                @endphp
                                <div class="progress-bar bg-{{ $progressColor }}"
                                     role="progressbar"
                                     style="width: {{ $percentage }}%"
                                     aria-valuenow="{{ $percentage }}"
                                     aria-valuemin="0"
                                     aria-valuemax="100">
                                    {{ number_format($percentage, 1) }}%
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <h6 class="mb-3">Your Performance</h6>
                            <ul class="list-unstyled">
                                <li class="mb-2">
                                    <i class="fas fa-star text-warning me-2"></i>
                                    Grade: <strong>{{ $result->grade }}</strong>
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-trophy text-success me-2"></i>
                                    Marks: <strong>{{ $result->marks_obtained }}/{{ $result->exam->total_marks }}</strong>
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-chart-line text-info me-2"></i>
                                    Percentage: <strong>{{ number_format($result->percentage, 1) }}%</strong>
                                </li>
                            </ul>
                        </div>

                        <div class="col-md-6">
                            <h6 class="mb-3">Class Comparison</h6>
                            <ul class="list-unstyled">
                                <li class="mb-2">
                                    <i class="fas fa-users text-primary me-2"></i>
                                    Class Average: <strong>{{ number_format($classAverage, 1) }}</strong>
                                </li>
                                <li class="mb-2">
                                    @if($result->marks_obtained > $classAverage)
                                        <i class="fas fa-arrow-up text-success me-2"></i>
                                        Above Average by: <strong class="text-success">
                                            {{ number_format($result->marks_obtained - $classAverage, 1) }} marks
                                        </strong>
                                    @elseif($result->marks_obtained < $classAverage)
                                        <i class="fas fa-arrow-down text-danger me-2"></i>
                                        Below Average by: <strong class="text-danger">
                                            {{ number_format($classAverage - $result->marks_obtained, 1) }} marks
                                        </strong>
                                    @else
                                        <i class="fas fa-equals text-warning me-2"></i>
                                        Exactly at average
                                    @endif
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Student Info -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Student Information</h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="avatar bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-2"
                             style="width: 60px; height: 60px; font-size: 24px;">
                            {{ substr($student->user->name ?? 'S', 0, 1) }}
                        </div>
                        <h6 class="mb-1">{{ $student->user->name ?? 'N/A' }}</h6>
                        <small class="text-muted">Student ID: {{ $student->student_id ?? 'N/A' }}</small>
                    </div>
                </div>
            </div>

            <!-- Grade Scale -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Grade Scale</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tbody>
                            <tr>
                                <td><span class="badge bg-success">A+</span></td>
                                <td>90-100%</td>
                                <td>Excellent</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-success">A</span></td>
                                <td>80-89%</td>
                                <td>Very Good</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-info">B+</span></td>
                                <td>70-79%</td>
                                <td>Good</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-info">B</span></td>
                                <td>60-69%</td>
                                <td>Above Average</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-warning text-dark">C+</span></td>
                                <td>55-59%</td>
                                <td>Average</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-warning text-dark">C</span></td>
                                <td>50-54%</td>
                                <td>Pass</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-danger">F</span></td>
                                <td>0-49%</td>
                                <td>Fail</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <a href="{{ route('student.results.index') }}" class="btn btn-outline-primary w-100 mb-2">
                        <i class="fas fa-list me-1"></i> All Results
                    </a>

                    @if(Route::has('student.classes.show'))
                        <a href="{{ route('student.classes.show', $result->exam->class_id) }}" class="btn btn-outline-info w-100 mb-2">
                            <i class="fas fa-chalkboard me-1"></i> View Class
                        </a>
                    @endif

                    @if(Route::has('student.attendance.index'))
                        <a href="{{ route('student.attendance.index', ['class_id' => $result->exam->class_id]) }}" class="btn btn-outline-success w-100">
                            <i class="fas fa-calendar-check me-1"></i> View Attendance
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
.avatar {
    font-weight: bold;
}
</style>
@endpush
