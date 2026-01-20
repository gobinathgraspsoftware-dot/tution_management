@extends('layouts.app')

@section('title', 'Enter Results - ' . $exam->name)

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Enter Exam Results</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('teacher.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('teacher.exams.index') }}">Exams</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('teacher.exams.show', $exam) }}">{{ $exam->name }}</a></li>
                    <li class="breadcrumb-item active">Enter Results</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('teacher.exams.show', $exam) }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Exam
        </a>
    </div>

    <!-- Exam Info Card -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <label class="text-muted small">Exam Name</label>
                    <p class="mb-0 fw-semibold">{{ $exam->name }}</p>
                </div>
                <div class="col-md-2">
                    <label class="text-muted small">Class</label>
                    <p class="mb-0">{{ $exam->class->name ?? 'N/A' }}</p>
                </div>
                <div class="col-md-2">
                    <label class="text-muted small">Subject</label>
                    <p class="mb-0">{{ $exam->subject->name ?? 'N/A' }}</p>
                </div>
                <div class="col-md-2">
                    <label class="text-muted small">Max Marks</label>
                    <p class="mb-0 fw-bold text-primary">{{ number_format($exam->max_marks, 2) }}</p>
                </div>
                <div class="col-md-2">
                    <label class="text-muted small">Passing Marks</label>
                    <p class="mb-0 fw-bold text-success">{{ number_format($exam->passing_marks, 2) }}</p>
                </div>
                <div class="col-md-1">
                    <label class="text-muted small">Date</label>
                    <p class="mb-0">{{ $exam->exam_date->format('M d, Y') }}</p>
                </div>
            </div>
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

    <!-- Results Form -->
    <div class="card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Student Results</h5>
            <div>
                <span class="badge bg-info me-2">Total Students: {{ $enrolledStudents->count() }}</span>
                <span class="badge bg-success">Results Entered: {{ $existingResults->count() }}</span>
            </div>
        </div>
        <div class="card-body">
            @if($enrolledStudents->count() > 0)
                <form action="{{ route('teacher.exams.store-results', $exam) }}" method="POST" id="resultsForm">
                    @csrf

                    <!-- Quick Actions -->
                    <div class="mb-4 p-3 bg-light rounded">
                        <div class="row align-items-center">
                            <div class="col-md-4">
                                <label class="form-label mb-0">Quick Fill All Marks:</label>
                            </div>
                            <div class="col-md-3">
                                <input type="number" class="form-control" id="quickFillMarks" 
                                       min="0" max="{{ $exam->max_marks }}" step="0.01" placeholder="Enter marks">
                            </div>
                            <div class="col-md-3">
                                <button type="button" class="btn btn-outline-primary" id="applyQuickFill">
                                    <i class="fas fa-fill me-1"></i> Apply to Empty
                                </button>
                                <button type="button" class="btn btn-outline-secondary" id="clearAll">
                                    <i class="fas fa-eraser me-1"></i> Clear All
                                </button>
                            </div>
                            <div class="col-md-2 text-end">
                                <span class="text-muted small">Max: {{ number_format($exam->max_marks, 2) }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover" id="resultsTable">
                            <thead class="table-light">
                                <tr>
                                    <th width="50">#</th>
                                    <th>Student</th>
                                    <th width="150">Marks Obtained <span class="text-danger">*</span></th>
                                    <th width="120">Percentage</th>
                                    <th width="100">Grade</th>
                                    <th width="100">Status</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($enrolledStudents as $index => $student)
                                    @php
                                        $existingResult = $existingResults->get($student->id);
                                    @endphp
                                    <tr data-student-id="{{ $student->id }}">
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <input type="hidden" name="results[{{ $index }}][student_id]" value="{{ $student->id }}">
                                            <strong>{{ $student->user->name ?? 'N/A' }}</strong>
                                            <br><small class="text-muted">{{ $student->student_id ?? '' }}</small>
                                        </td>
                                        <td>
                                            <input type="number" 
                                                   class="form-control marks-input @error('results.'.$index.'.marks_obtained') is-invalid @enderror" 
                                                   name="results[{{ $index }}][marks_obtained]" 
                                                   value="{{ old('results.'.$index.'.marks_obtained', $existingResult->marks_obtained ?? '') }}"
                                                   min="0" 
                                                   max="{{ $exam->max_marks }}" 
                                                   step="0.01"
                                                   data-max="{{ $exam->max_marks }}"
                                                   data-passing="{{ $exam->passing_marks }}"
                                                   required>
                                            @error('results.'.$index.'.marks_obtained')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </td>
                                        <td>
                                            <span class="percentage-display fw-bold">
                                                {{ $existingResult ? number_format($existingResult->percentage, 1) . '%' : '-' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="grade-display badge bg-secondary">
                                                {{ $existingResult->grade ?? '-' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="status-display badge bg-secondary">-</span>
                                        </td>
                                        <td>
                                            <input type="text" 
                                                   class="form-control form-control-sm @error('results.'.$index.'.remarks') is-invalid @enderror" 
                                                   name="results[{{ $index }}][remarks]" 
                                                   value="{{ old('results.'.$index.'.remarks', $existingResult->remarks ?? '') }}"
                                                   placeholder="Optional remarks"
                                                   maxlength="500">
                                            @error('results.'.$index.'.remarks')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Summary Section -->
                    <div class="row mt-4">
                        <div class="col-md-8">
                            <div class="alert alert-info">
                                <h6 class="alert-heading"><i class="fas fa-info-circle me-2"></i>Grading Scale</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <small>
                                            <span class="badge bg-success me-1">A+</span> 90% and above<br>
                                            <span class="badge bg-success me-1">A</span> 80% - 89%<br>
                                            <span class="badge bg-info me-1">B</span> 70% - 79%<br>
                                            <span class="badge bg-primary me-1">C</span> 60% - 69%
                                        </small>
                                    </div>
                                    <div class="col-md-6">
                                        <small>
                                            <span class="badge bg-warning me-1">D</span> 50% - 59%<br>
                                            <span class="badge bg-secondary me-1">E</span> 40% - 49%<br>
                                            <span class="badge bg-danger me-1">F</span> Below 40%
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">Live Summary</h6>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Entries Filled:</span>
                                        <span class="fw-bold" id="entriesFilled">0</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Pass:</span>
                                        <span class="fw-bold text-success" id="passCount">0</span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>Fail:</span>
                                        <span class="fw-bold text-danger" id="failCount">0</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('teacher.exams.show', $exam) }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-success" id="submitBtn">
                            <i class="fas fa-save me-1"></i> Save Results
                        </button>
                    </div>
                </form>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-user-slash fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted">No Students Enrolled</h5>
                    <p class="text-muted">There are no students enrolled in this class.</p>
                    <a href="{{ route('teacher.exams.show', $exam) }}" class="btn btn-primary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Exam
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    var maxMarks = {{ $exam->max_marks }};
    var passingMarks = {{ $exam->passing_marks }};

    // Calculate grade based on percentage
    function calculateGrade(percentage) {
        if (percentage >= 90) return { grade: 'A+', class: 'success' };
        if (percentage >= 80) return { grade: 'A', class: 'success' };
        if (percentage >= 70) return { grade: 'B', class: 'info' };
        if (percentage >= 60) return { grade: 'C', class: 'primary' };
        if (percentage >= 50) return { grade: 'D', class: 'warning' };
        if (percentage >= 40) return { grade: 'E', class: 'secondary' };
        return { grade: 'F', class: 'danger' };
    }

    // Update row display
    function updateRow($row, marks) {
        var $percentageDisplay = $row.find('.percentage-display');
        var $gradeDisplay = $row.find('.grade-display');
        var $statusDisplay = $row.find('.status-display');

        if (marks === '' || marks === null || isNaN(marks)) {
            $percentageDisplay.text('-');
            $gradeDisplay.removeClass().addClass('grade-display badge bg-secondary').text('-');
            $statusDisplay.removeClass().addClass('status-display badge bg-secondary').text('-');
            return;
        }

        marks = parseFloat(marks);
        var percentage = (marks / maxMarks) * 100;
        var gradeInfo = calculateGrade(percentage);
        var isPassing = marks >= passingMarks;

        $percentageDisplay.text(percentage.toFixed(1) + '%');
        $gradeDisplay.removeClass().addClass('grade-display badge bg-' + gradeInfo.class).text(gradeInfo.grade);
        
        if (isPassing) {
            $statusDisplay.removeClass().addClass('status-display badge bg-success').text('Pass');
        } else {
            $statusDisplay.removeClass().addClass('status-display badge bg-danger').text('Fail');
        }
    }

    // Update summary
    function updateSummary() {
        var filled = 0;
        var pass = 0;
        var fail = 0;

        $('.marks-input').each(function() {
            var val = $(this).val();
            if (val !== '' && val !== null) {
                filled++;
                var marks = parseFloat(val);
                if (marks >= passingMarks) {
                    pass++;
                } else {
                    fail++;
                }
            }
        });

        $('#entriesFilled').text(filled + ' / {{ $enrolledStudents->count() }}');
        $('#passCount').text(pass);
        $('#failCount').text(fail);
    }

    // Handle marks input change
    $('.marks-input').on('input change', function() {
        var $row = $(this).closest('tr');
        var marks = $(this).val();
        
        // Validate max
        if (marks !== '' && parseFloat(marks) > maxMarks) {
            $(this).val(maxMarks);
            marks = maxMarks;
        }
        
        // Validate min
        if (marks !== '' && parseFloat(marks) < 0) {
            $(this).val(0);
            marks = 0;
        }

        updateRow($row, marks);
        updateSummary();
    });

    // Quick fill functionality
    $('#applyQuickFill').on('click', function() {
        var quickMarks = $('#quickFillMarks').val();
        if (quickMarks === '' || quickMarks === null) {
            alert('Please enter marks to fill.');
            return;
        }

        if (parseFloat(quickMarks) > maxMarks) {
            alert('Marks cannot exceed ' + maxMarks);
            return;
        }

        $('.marks-input').each(function() {
            if ($(this).val() === '' || $(this).val() === null) {
                $(this).val(quickMarks);
                var $row = $(this).closest('tr');
                updateRow($row, quickMarks);
            }
        });
        updateSummary();
    });

    // Clear all functionality
    $('#clearAll').on('click', function() {
        if (confirm('Are you sure you want to clear all marks?')) {
            $('.marks-input').val('');
            $('input[name*="[remarks]"]').val('');
            $('.percentage-display').text('-');
            $('.grade-display').removeClass().addClass('grade-display badge bg-secondary').text('-');
            $('.status-display').removeClass().addClass('status-display badge bg-secondary').text('-');
            updateSummary();
        }
    });

    // Form validation
    $('#resultsForm').on('submit', function(e) {
        var emptyCount = 0;
        $('.marks-input').each(function() {
            if ($(this).val() === '' || $(this).val() === null) {
                emptyCount++;
            }
        });

        if (emptyCount > 0) {
            if (!confirm('There are ' + emptyCount + ' student(s) without marks. Continue anyway?')) {
                e.preventDefault();
                return false;
            }
        }

        // Show loading state
        $('#submitBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Saving...');
    });

    // Initialize on page load
    $('.marks-input').each(function() {
        var $row = $(this).closest('tr');
        var marks = $(this).val();
        if (marks !== '' && marks !== null) {
            updateRow($row, marks);
        }
    });
    updateSummary();

    // Keyboard navigation
    $('.marks-input').on('keydown', function(e) {
        if (e.key === 'Enter' || e.key === 'Tab') {
            e.preventDefault();
            var $allInputs = $('.marks-input');
            var currentIndex = $allInputs.index(this);
            var nextIndex = e.shiftKey ? currentIndex - 1 : currentIndex + 1;
            
            if (nextIndex >= 0 && nextIndex < $allInputs.length) {
                $allInputs.eq(nextIndex).focus().select();
            }
        }
    });
});
</script>
@endpush

@push('styles')
<style>
.marks-input:focus {
    background-color: #fffde7;
    border-color: #ffc107;
    box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25);
}

.marks-input.is-invalid {
    background-color: #fff5f5;
}

#resultsTable tbody tr:hover {
    background-color: #f8f9fa;
}
</style>
@endpush
