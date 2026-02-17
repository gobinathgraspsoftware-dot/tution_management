@extends('layouts.app')

@section('title', 'My Exam Results')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">My Exam Results</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Results</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body">
                    <h6 class="text-white-50">Total Exams</h6>
                    <h3 class="mb-0">{{ $stats['total'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body">
                    <h6 class="text-white-50">Passed</h6>
                    <h3 class="mb-0">{{ $stats['passed'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-warning text-dark">
                <div class="card-body">
                    <h6 class="opacity-75">Average Marks</h6>
                    <h3 class="mb-0">{{ $stats['avg_marks'] }}%</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-info text-white">
                <div class="card-body">
                    <h6 class="text-white-50">Pass Rate</h6>
                    <h3 class="mb-0">{{ $stats['pass_rate'] }}%</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('student.results.index') }}" method="GET" class="row g-3">
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
                <div class="col-md-3">
                    <label class="form-label">Exam Type</label>
                    <select name="exam_type" class="form-select">
                        <option value="">All Types</option>
                        <option value="quiz" {{ request('exam_type') == 'quiz' ? 'selected' : '' }}>Quiz</option>
                        <option value="test" {{ request('exam_type') == 'test' ? 'selected' : '' }}>Test</option>
                        <option value="midterm" {{ request('exam_type') == 'midterm' ? 'selected' : '' }}>Midterm</option>
                        <option value="final" {{ request('exam_type') == 'final' ? 'selected' : '' }}>Final</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-1"></i> Filter
                        </button>
                        <a href="{{ route('student.results.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-redo me-1"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Results Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Exam Results</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Exam</th>
                            <th>Class/Subject</th>
                            <th>Date</th>
                            <th class="text-center">Marks</th>
                            <th class="text-center">Percentage</th>
                            <th class="text-center">Grade</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($examResults as $result)
                            <tr>
                                <td>
                                    <strong>{{ $result->exam->title }}</strong>
                                    <br>
                                    <small class="text-muted">{{ ucfirst($result->exam->exam_type) }}</small>
                                </td>
                                <td>
                                    {{ $result->exam->class->name ?? 'N/A' }}
                                    <br>
                                    <small class="text-muted">{{ $result->exam->class->subject->name ?? 'N/A' }}</small>
                                </td>
                                <td>
                                    {{ $result->exam->exam_date ? $result->exam->exam_date->format('d M Y') : 'N/A' }}
                                </td>
                                <td class="text-center">
                                    <strong>{{ $result->marks_obtained }} / {{ $result->exam->total_marks }}</strong>
                                </td>
                                <td class="text-center">
                                    <span class="badge {{ $result->percentage >= 60 ? 'bg-success' : 'bg-danger' }}">
                                        {{ number_format($result->percentage, 1) }}%
                                    </span>
                                </td>
                                <td class="text-center">
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
                                    <span class="badge bg-{{ $color }} fs-6">{{ $result->grade }}</span>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('student.results.show', $result->id) }}"
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <p class="text-muted mb-0">No exam results found.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($examResults->hasPages())
            <div class="card-footer bg-white">
                {{ $examResults->appends(request()->query())->links() }}
            </div>
        @endif
    </div>

    <!-- Grade Legend -->
    @if($examResults->count() > 0)
        <div class="card shadow-sm mt-4">
            <div class="card-body">
                <h6 class="mb-3">Grade Legend:</h6>
                <div class="row">
                    <div class="col-md-12">
                        <span class="badge bg-success me-2">A+ / A</span> Excellent (80-100%)
                        <span class="badge bg-info ms-3 me-2">B+ / B</span> Good (60-79%)
                        <span class="badge bg-warning text-dark ms-3 me-2">C+ / C</span> Average (50-59%)
                        <span class="badge bg-danger ms-3 me-2">F</span> Fail (Below 50%)
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
