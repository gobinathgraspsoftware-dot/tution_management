@extends('layouts.app')

@section('title', 'Exam Results - ' . $exam->name)

@section('content')
<div class="container-fluid">
    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="fas fa-clipboard-list me-2"></i>Exam Results</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.exams.index') }}">Exams</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.exams.show', $exam) }}">{{ $exam->name }}</a></li>
                    <li class="breadcrumb-item active">Results</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            @if(Route::has('admin.exam-results.create'))
                <a href="{{ route('admin.exam-results.create', $exam) }}" class="btn btn-success">
                    <i class="fas fa-plus me-1"></i> Enter Results
                </a>
            @endif
            @if(Route::has('admin.exam-results.export'))
                <a href="{{ route('admin.exam-results.export', $exam) }}" class="btn btn-outline-success">
                    <i class="fas fa-download me-1"></i> Export CSV
                </a>
            @endif
            @if(Route::has('admin.exam-results.statistics'))
                <a href="{{ route('admin.exam-results.statistics', $exam) }}" class="btn btn-outline-info">
                    <i class="fas fa-chart-pie me-1"></i> Statistics
                </a>
            @endif
            <a href="{{ route('admin.exams.show', $exam) }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Exam
            </a>
        </div>
    </div>

    {{-- Exam Info Banner --}}
    <div class="alert alert-light border mb-4">
        <div class="row align-items-center">
            <div class="col-md-3">
                <strong>Exam:</strong> {{ $exam->name }}
            </div>
            <div class="col-md-3">
                <strong>Class:</strong> {{ $exam->class->name ?? 'N/A' }}
            </div>
            <div class="col-md-2">
                <strong>Subject:</strong> {{ $exam->subject->name ?? 'N/A' }}
            </div>
            <div class="col-md-2">
                <strong>Max Marks:</strong> <span class="badge bg-primary">{{ number_format($exam->max_marks, 0) }}</span>
            </div>
            <div class="col-md-2">
                <strong>Pass Marks:</strong> <span class="badge bg-warning text-dark">{{ number_format($exam->passing_marks, 0) }}</span>
            </div>
        </div>
    </div>

    {{-- Statistics --}}
    @include('admin.exam-results._stats')

    {{-- Publish Actions --}}
    @if(($stats['results_entered'] ?? 0) > 0)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    @if(($stats['published_results'] ?? 0) > 0)
                        <span class="badge bg-success fs-6 me-2">
                            <i class="fas fa-check-circle me-1"></i> Published
                        </span>
                        <span class="text-muted">{{ $stats['published_results'] }} of {{ $stats['results_entered'] }} results published</span>
                    @else
                        <span class="badge bg-secondary fs-6 me-2">
                            <i class="fas fa-eye-slash me-1"></i> Not Published
                        </span>
                        <span class="text-muted">{{ $stats['results_entered'] }} results ready to publish</span>
                    @endif
                </div>
                <div>
                    @if(($stats['published_results'] ?? 0) > 0)
                        @if(Route::has('admin.exam-results.unpublish'))
                            <form action="{{ route('admin.exam-results.unpublish', $exam) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                        onclick="return confirm('Unpublish all results?')">
                                    <i class="fas fa-eye-slash me-1"></i> Unpublish
                                </button>
                            </form>
                        @endif
                    @else
                        @if(Route::has('admin.exam-results.publish'))
                            <form action="{{ route('admin.exam-results.publish', $exam) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success"
                                        onclick="return confirm('Publish results and notify parents?')">
                                    <i class="fas fa-bullhorn me-1"></i> Publish & Notify Parents
                                </button>
                            </form>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- Results Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="fas fa-table me-2"></i>Results List</h6>
            <span class="badge bg-secondary">{{ $stats['results_entered'] ?? 0 }} / {{ $stats['total_students'] ?? 0 }} entered</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 5%;">#</th>
                            <th style="width: 12%;">Student ID</th>
                            <th style="width: 18%;">Student Name</th>
                            <th style="width: 10%;">Marks</th>
                            <th style="width: 10%;">Percentage</th>
                            <th style="width: 8%;">Grade</th>
                            <th style="width: 8%;">Rank</th>
                            <th style="width: 8%;">Status</th>
                            <th style="width: 8%;">Published</th>
                            <th style="width: 13%;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $enrolledStudents = $students;
                            $resultMap = $exam->results->keyBy('student_id');
                        @endphp
                        @forelse($enrolledStudents as $enrollment)
                            @php
                                $student = $enrollment->student;
                                $result = $resultMap[$student->id] ?? null;
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td><span class="text-muted">{{ $student->student_id ?? 'N/A' }}</span></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-2" style="width:32px;height:32px;">
                                            <i class="fas fa-user text-muted small"></i>
                                        </div>
                                        <span class="fw-semibold">{{ $student->user->name ?? 'N/A' }}</span>
                                    </div>
                                </td>
                                <td>
                                    @if($result && $result->marks_obtained !== null)
                                        <span class="fw-semibold">{{ number_format($result->marks_obtained, 0) }}</span>
                                        <small class="text-muted">/ {{ number_format($exam->max_marks, 0) }}</small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($result && $result->percentage)
                                        {{ number_format($result->percentage, 1) }}%
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($result && $result->grade)
                                        @php
                                            $gradeColors = [
                                                'A+' => 'success', 'A' => 'success',
                                                'B+' => 'info', 'B' => 'info',
                                                'C' => 'warning', 'D' => 'warning',
                                                'F' => 'danger',
                                            ];
                                        @endphp
                                        <span class="badge bg-{{ $gradeColors[$result->grade] ?? 'secondary' }}">{{ $result->grade }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($result && $result->rank)
                                        @if($result->rank <= 3)
                                            <span class="badge bg-warning text-dark">
                                                <i class="fas fa-trophy me-1"></i>#{{ $result->rank }}
                                            </span>
                                        @else
                                            #{{ $result->rank }}
                                        @endif
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($result && $result->marks_obtained !== null)
                                        @if($result->marks_obtained >= $exam->passing_marks)
                                            <span class="badge bg-success">Pass</span>
                                        @else
                                            <span class="badge bg-danger">Fail</span>
                                        @endif
                                    @else
                                        <span class="badge bg-secondary">Pending</span>
                                    @endif
                                </td>
                                <td>
                                    @if($result && $result->is_published)
                                        <span class="badge bg-success"><i class="fas fa-check"></i></span>
                                    @else
                                        <span class="badge bg-secondary"><i class="fas fa-times"></i></span>
                                    @endif
                                </td>
                                <td>
                                    @if($result)
                                        <div class="btn-group btn-group-sm">
                                            @if(Route::has('admin.exam-results.edit'))
                                                <a href="{{ route('admin.exam-results.edit', $result) }}" class="btn btn-outline-primary" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endif
                                            @if(Route::has('admin.exam-results.card'))
                                                <a href="{{ route('admin.exam-results.card', $result) }}" class="btn btn-outline-info" title="Result Card" target="_blank">
                                                    <i class="fas fa-id-card"></i>
                                                </a>
                                            @endif
                                            @if(Route::has('admin.exam-results.destroy'))
                                                <form action="{{ route('admin.exam-results.destroy', $result) }}" method="POST" class="d-inline"
                                                      onsubmit="return confirm('Delete this result?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-muted small">No result</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-users fa-3x mb-3 d-block"></i>
                                        <p>No enrolled students found for this class.</p>
                                    </div>
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
