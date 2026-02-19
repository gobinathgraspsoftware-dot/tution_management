@extends('layouts.app')

@section('title', 'Edit Result')

@section('content')
<div class="container-fluid">
    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="fas fa-edit me-2"></i>Edit Result</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.exams.index') }}">Exams</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.exams.show', $result->exam) }}">{{ $result->exam->name }}</a></li>
                    <li class="breadcrumb-item active">Edit Result</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('admin.exam-results.index', $result->exam) }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Results
        </a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="fas fa-user-edit me-2"></i>Update Result for {{ $result->student->user->name ?? 'Student' }}</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.exam-results.update', $result) }}" method="POST" id="editResultForm">
                        @csrf
                        @method('PUT')

                        {{-- Student Info (Read Only) --}}
                        <div class="alert alert-light border mb-4">
                            <div class="row">
                                <div class="col-md-4">
                                    <strong>Student:</strong> {{ $result->student->user->name ?? 'N/A' }}
                                </div>
                                <div class="col-md-4">
                                    <strong>ID:</strong> {{ $result->student->student_id ?? 'N/A' }}
                                </div>
                                <div class="col-md-4">
                                    <strong>Exam:</strong> {{ $result->exam->name }}
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="marks_obtained" class="form-label fw-semibold">
                                        Marks Obtained <span class="text-danger">*</span>
                                        <small class="text-muted">(Max: {{ number_format($result->exam->max_marks, 0) }})</small>
                                    </label>
                                    <input type="number" name="marks_obtained" id="marks_obtained" 
                                           class="form-control @error('marks_obtained') is-invalid @enderror"
                                           value="{{ old('marks_obtained', $result->marks_obtained) }}"
                                           min="0" max="{{ $result->exam->max_marks }}" step="0.01" required>
                                    @error('marks_obtained')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Percentage</label>
                                    <div class="form-control bg-light" id="percentageDisplay">
                                        {{ number_format($result->percentage, 1) }}%
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Grade</label>
                                    <div id="gradeDisplay">
                                        @php
                                            $gradeColors = [
                                                'A+' => 'success', 'A' => 'success',
                                                'B+' => 'info', 'B' => 'info',
                                                'C' => 'warning', 'D' => 'warning',
                                                'F' => 'danger',
                                            ];
                                        @endphp
                                        <span class="badge bg-{{ $gradeColors[$result->grade] ?? 'secondary' }} fs-6">
                                            {{ $result->grade }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="remarks" class="form-label fw-semibold">Remarks</label>
                            <textarea name="remarks" id="remarks" class="form-control @error('remarks') is-invalid @enderror"
                                      rows="3" maxlength="500" placeholder="Optional remarks or feedback...">{{ old('remarks', $result->remarks) }}</textarea>
                            @error('remarks')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr>
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.exam-results.index', $result->exam) }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Update Result
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Sidebar Info --}}
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Exam Details</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless table-sm mb-0">
                        <tr>
                            <td class="text-muted">Exam</td>
                            <td class="fw-semibold">{{ $result->exam->name }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Max Marks</td>
                            <td><span class="badge bg-primary">{{ number_format($result->exam->max_marks, 0) }}</span></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Pass Marks</td>
                            <td><span class="badge bg-warning text-dark">{{ number_format($result->exam->passing_marks, 0) }}</span></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Current Rank</td>
                            <td>{{ $result->rank ? '#' . $result->rank : 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Published</td>
                            <td>
                                @if($result->is_published)
                                    <span class="badge bg-success">Yes</span>
                                @else
                                    <span class="badge bg-secondary">No</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    var maxMarks = {{ $result->exam->max_marks }};

    $('#marks_obtained').on('input change', function() {
        var marks = parseFloat($(this).val()) || 0;
        if (marks > maxMarks) {
            $(this).val(maxMarks);
            marks = maxMarks;
        }

        var percentage = (marks / maxMarks) * 100;
        var grade = calculateGrade(percentage);

        $('#percentageDisplay').text(percentage.toFixed(1) + '%');

        var gradeColor = getGradeColor(grade);
        $('#gradeDisplay').html('<span class="badge bg-' + gradeColor + ' fs-6">' + grade + '</span>');
    });

    function calculateGrade(pct) {
        if (pct >= 90) return 'A+';
        if (pct >= 80) return 'A';
        if (pct >= 70) return 'B+';
        if (pct >= 60) return 'B';
        if (pct >= 50) return 'C';
        if (pct >= 40) return 'D';
        return 'F';
    }

    function getGradeColor(grade) {
        var colors = {'A+':'success','A':'success','B+':'info','B':'info','C':'warning','D':'warning','F':'danger'};
        return colors[grade] || 'secondary';
    }
});
</script>
@endpush
@endsection
