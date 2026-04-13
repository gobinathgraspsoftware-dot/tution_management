@extends('layouts.app')

@section('title', 'Edit Exam - ' . $exam->name)

@php
    // Prepare data for JS (must be in @php to avoid Blade parse errors with closures)
    $jsSubjects = $subjects->map(function($s) {
        return ['id' => $s->id, 'name' => $s->name, 'grade_levels' => $s->grade_levels ?? []];
    })->values();

    $jsClasses = $classes->map(function($c) {
        return ['id' => $c->id, 'name' => $c->name, 'subject_id' => $c->subject_id, 'grade_level_id' => $c->grade_level_id];
    })->values();
@endphp

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Edit Exam</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('teacher.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('teacher.exams.index') }}">Exams</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('teacher.exams.show', $exam) }}">{{ $exam->name }}</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('teacher.exams.show', $exam) }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Details
        </a>
    </div>

    <!-- Alert Messages -->
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Edit Form -->
    <div class="card">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Edit Exam Details</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('teacher.exams.update', $exam) }}" method="POST" id="examForm">
                @csrf
                @method('PUT')

                <div class="row">
                    <!-- Basic Information -->
                    <div class="col-md-8">
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Basic Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    {{-- 1. Exam Name --}}
                                    <div class="col-md-12 mb-3">
                                        <label for="name" class="form-label">Exam Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                                               id="name" name="name" value="{{ old('name', $exam->name) }}"
                                               placeholder="Enter exam name" required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    {{-- 2. Grade Level (select first) --}}
                                    <div class="col-md-4 mb-3">
                                        <label for="grade_level_id" class="form-label">Grade Level <span class="text-danger">*</span></label>
                                        <select class="form-select" id="grade_level_id" required>
                                            <option value="">Select Grade Level</option>
                                            @foreach($gradeLevels as $gl)
                                                <option value="{{ $gl->id }}"
                                                    {{ old('grade_level_id', $exam->class->grade_level_id ?? '') == $gl->id ? 'selected' : '' }}>
                                                    {{ $gl->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- 3. Subject (filtered by grade level) --}}
                                    <div class="col-md-4 mb-3">
                                        <label for="subject_id" class="form-label">Subject <span class="text-danger">*</span></label>
                                        <select class="form-select @error('subject_id') is-invalid @enderror"
                                                id="subject_id" name="subject_id" required>
                                            <option value="">-- Select Grade Level First --</option>
                                        </select>
                                        @error('subject_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    {{-- 4. Class (filtered by grade level + subject) --}}
                                    <div class="col-md-4 mb-3">
                                        <label for="class_id" class="form-label">Class <span class="text-danger">*</span></label>
                                        <select class="form-select @error('class_id') is-invalid @enderror"
                                                id="class_id" name="class_id" required>
                                            <option value="">-- Select Subject First --</option>
                                        </select>
                                        @error('class_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                        <select class="form-select @error('status') is-invalid @enderror"
                                                id="status" name="status" required>
                                            <option value="scheduled" {{ old('status', $exam->status) == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                                            <option value="ongoing" {{ old('status', $exam->status) == 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                                            <option value="completed" {{ old('status', $exam->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                                            <option value="cancelled" {{ old('status', $exam->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                        </select>
                                        @error('status')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="exam_date" class="form-label">Exam Date <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control @error('exam_date') is-invalid @enderror"
                                               id="exam_date" name="exam_date"
                                               value="{{ old('exam_date', $exam->exam_date->format('Y-m-d')) }}" required>
                                        @error('exam_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="start_time" class="form-label">Start Time <span class="text-danger">*</span></label>
                                        <input type="time" class="form-control @error('start_time') is-invalid @enderror"
                                               id="start_time" name="start_time"
                                               value="{{ old('start_time', $exam->start_time ? \Carbon\Carbon::parse($exam->start_time)->format('H:i') : '') }}" required>
                                        @error('start_time')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="duration_minutes" class="form-label">Duration (Minutes) <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control @error('duration_minutes') is-invalid @enderror"
                                               id="duration_minutes" name="duration_minutes"
                                               value="{{ old('duration_minutes', $exam->duration_minutes) }}"
                                               min="1" max="480" required>
                                        @error('duration_minutes')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">Max: 480 mins (8 hours)</small>
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control @error('description') is-invalid @enderror"
                                                  id="description" name="description" rows="3"
                                                  placeholder="Enter exam description (optional)">{{ old('description', $exam->description) }}</textarea>
                                        @error('description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">Maximum 1000 characters</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Marks Configuration -->
                    <div class="col-md-4">
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fas fa-star me-2"></i>Marks Configuration</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="max_marks" class="form-label">Maximum Marks <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('max_marks') is-invalid @enderror"
                                           id="max_marks" name="max_marks"
                                           value="{{ old('max_marks', $exam->max_marks) }}"
                                           min="1" max="1000" step="0.01" required>
                                    @error('max_marks')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="passing_marks" class="form-label">Passing Marks <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('passing_marks') is-invalid @enderror"
                                           id="passing_marks" name="passing_marks"
                                           value="{{ old('passing_marks', $exam->passing_marks) }}"
                                           min="0" step="0.01" required>
                                    @error('passing_marks')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <hr>

                                <div class="alert alert-info mb-0">
                                    <h6 class="alert-heading"><i class="fas fa-info-circle me-2"></i>Grading Scale</h6>
                                    <small>
                                        <ul class="mb-0 ps-3">
                                            <li>A+ : 90% and above</li>
                                            <li>A : 80% - 89%</li>
                                            <li>B : 70% - 79%</li>
                                            <li>C : 60% - 69%</li>
                                            <li>D : 50% - 59%</li>
                                            <li>E : 40% - 49%</li>
                                            <li>F : Below 40%</li>
                                        </ul>
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- End Time Calculator -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fas fa-clock me-2"></i>End Time Calculator</h6>
                            </div>
                            <div class="card-body">
                                <div class="text-center">
                                    <h4 id="endTimeDisplay" class="text-primary mb-2">--:-- --</h4>
                                    <small class="text-muted">Calculated End Time</small>
                                </div>
                            </div>
                        </div>

                        <!-- Current Info -->
                        <div class="card">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fas fa-history me-2"></i>Exam Info</h6>
                            </div>
                            <div class="card-body">
                                <small class="text-muted d-block mb-2">
                                    <strong>Created:</strong><br>
                                    {{ $exam->created_at->format('M d, Y h:i A') }}
                                </small>
                                <small class="text-muted d-block">
                                    <strong>Last Updated:</strong><br>
                                    {{ $exam->updated_at->format('M d, Y h:i A') }}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('teacher.exams.show', $exam) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Update Exam
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    var allSubjects = {!! json_encode($jsSubjects) !!};
    var allClasses  = {!! json_encode($jsClasses) !!};

    var preGradeLevel = '{{ old("grade_level_id", $exam->class->grade_level_id ?? "") }}';
    var preSubject    = '{{ old("subject_id", $exam->subject_id) }}';
    var preClass      = '{{ old("class_id", $exam->class_id) }}';

    $('#grade_level_id').on('change', function() {
        var gradeLevelId = parseInt($(this).val()) || 0;
        var $subjectSelect = $('#subject_id');
        var $classSelect   = $('#class_id');

        $classSelect.html('<option value="">-- Select Subject First --</option>');

        if (!gradeLevelId) {
            $subjectSelect.html('<option value="">-- Select Grade Level First --</option>');
            return;
        }

        var filtered = allSubjects.filter(function(s) {
            return s.grade_levels && s.grade_levels.includes(gradeLevelId);
        });

        var options = '<option value="">Select Subject</option>';
        if (filtered.length === 0) {
            options = '<option value="">-- No Subjects for this Grade Level --</option>';
        } else {
            filtered.forEach(function(s) {
                var selected = (s.id == preSubject) ? 'selected' : '';
                options += '<option value="' + s.id + '" ' + selected + '>' + s.name + '</option>';
            });
        }
        $subjectSelect.html(options);

        if (preSubject && $subjectSelect.val()) {
            $subjectSelect.trigger('change');
        }
    });

    $('#subject_id').on('change', function() {
        var subjectId    = parseInt($(this).val()) || 0;
        var gradeLevelId = parseInt($('#grade_level_id').val()) || 0;
        var $classSelect = $('#class_id');

        if (!subjectId) {
            $classSelect.html('<option value="">-- Select Subject First --</option>');
            return;
        }

        var filtered = allClasses.filter(function(c) {
            return c.subject_id == subjectId && c.grade_level_id == gradeLevelId;
        });

        var options = '<option value="">Select Class</option>';
        if (filtered.length === 0) {
            options = '<option value="">-- No Classes Available --</option>';
        } else {
            filtered.forEach(function(c) {
                var selected = (c.id == preClass) ? 'selected' : '';
                options += '<option value="' + c.id + '" ' + selected + '>' + c.name + '</option>';
            });
        }
        $classSelect.html(options);
    });

    // Initialize cascade on page load (edit mode pre-population)
    if (preGradeLevel) {
        $('#grade_level_id').trigger('change');
    }

    function calculateEndTime() {
        var startTime = $('#start_time').val();
        var duration = parseInt($('#duration_minutes').val()) || 0;

        if (startTime && duration > 0) {
            var start = new Date('2000-01-01 ' + startTime);
            var end = new Date(start.getTime() + duration * 60000);
            var hours = end.getHours();
            var mins = end.getMinutes();
            var ampm = hours >= 12 ? 'PM' : 'AM';
            hours = hours % 12;
            hours = hours ? hours : 12;
            mins = mins < 10 ? '0' + mins : mins;
            $('#endTimeDisplay').text(hours + ':' + mins + ' ' + ampm).removeClass('text-danger').addClass('text-primary');
        } else {
            $('#endTimeDisplay').text('--:-- --');
        }
    }

    calculateEndTime();
    $('#start_time, #duration_minutes').on('change input', calculateEndTime);

    $('#max_marks, #passing_marks').on('change', function() {
        var max = parseFloat($('#max_marks').val()) || 0;
        var passing = parseFloat($('#passing_marks').val()) || 0;
        if (passing > max) {
            $('#passing_marks').val(max);
            alert('Passing marks cannot exceed maximum marks!');
        }
    });
});
</script>
@endpush
