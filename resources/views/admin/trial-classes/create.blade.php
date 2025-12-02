@extends('layouts.app')

@section('title', 'Schedule Trial Class')
@section('page-title', 'Schedule Trial Class')

@section('content')
<div class="page-header">
    <div>
        <h1><i class="fas fa-plus me-2"></i> Schedule Trial Class</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.trial-classes.index') }}">Trial Classes</a></li>
                <li class="breadcrumb-item active">Schedule Trial</li>
            </ol>
        </nav>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.trial-classes.store') }}" method="POST">
            @csrf

            <!-- Student Type Selection -->
            <div class="mb-4">
                <label class="form-label">Student Type <span class="text-danger">*</span></label>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="student_type" id="existingStudent" value="existing" checked>
                    <label class="form-check-label" for="existingStudent">Existing Student</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="student_type" id="newStudent" value="new">
                    <label class="form-check-label" for="newStudent">New Prospect</label>
                </div>
            </div>

            <!-- Existing Student Selection -->
            <div id="existingStudentSection">
                <div class="mb-3">
                    <label class="form-label">Select Student <span class="text-danger">*</span></label>
                    <select name="student_id" id="studentSelect" class="form-select @error('student_id') is-invalid @enderror">
                        <option value="">Select Student...</option>
                        @foreach($students as $student)
                            <option value="{{ $student->id }}" {{ old('student_id') == $student->id ? 'selected' : '' }}>
                                {{ $student->user->name }} ({{ $student->student_id }})
                            </option>
                        @endforeach
                    </select>
                    @error('student_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- New Student Details -->
            <div id="newStudentSection" style="display: none;">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Student Name <span class="text-danger">*</span></label>
                            <input type="text" name="student_name" class="form-control @error('student_name') is-invalid @enderror" value="{{ old('student_name') }}">
                            @error('student_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Parent Name <span class="text-danger">*</span></label>
                            <input type="text" name="parent_name" class="form-control @error('parent_name') is-invalid @enderror" value="{{ old('parent_name') }}">
                            @error('parent_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Parent Phone <span class="text-danger">*</span></label>
                            <input type="text" name="parent_phone" class="form-control @error('parent_phone') is-invalid @enderror" value="{{ old('parent_phone') }}" placeholder="01x-xxxxxxx">
                            @error('parent_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Parent Email</label>
                            <input type="email" name="parent_email" class="form-control @error('parent_email') is-invalid @enderror" value="{{ old('parent_email') }}">
                            @error('parent_email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <hr class="my-4">

            <!-- Class Selection -->
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Class <span class="text-danger">*</span></label>
                        <select name="class_id" id="classSelect" class="form-select @error('class_id') is-invalid @enderror" required>
                            <option value="">Select Class...</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}"
                                        data-type="{{ $class->type }}"
                                        data-teacher="{{ $class->teacher->user->name ?? 'N/A' }}"
                                        data-subject="{{ $class->subject->name ?? 'N/A' }}"
                                        {{ old('class_id') == $class->id ? 'selected' : '' }}>
                                    {{ $class->name }} ({{ $class->subject->name ?? 'N/A' }} - {{ ucfirst($class->type) }})
                                </option>
                            @endforeach
                        </select>
                        @error('class_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Class Info</label>
                        <div id="classInfo" class="form-control bg-light" style="min-height: 38px;">
                            <span class="text-muted">Select a class to see details</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Schedule -->
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Trial Date <span class="text-danger">*</span></label>
                        <input type="date" name="scheduled_date" class="form-control @error('scheduled_date') is-invalid @enderror"
                               value="{{ old('scheduled_date', date('Y-m-d')) }}" min="{{ date('Y-m-d') }}" required>
                        @error('scheduled_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Trial Time <span class="text-danger">*</span></label>
                        <input type="time" name="scheduled_time" class="form-control @error('scheduled_time') is-invalid @enderror"
                               value="{{ old('scheduled_time', '09:00') }}" required>
                        @error('scheduled_time')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <div class="mb-3">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3" placeholder="Any additional notes...">{{ old('notes') }}</textarea>
                @error('notes')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Submit -->
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('admin.trial-classes.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-calendar-plus me-1"></i> Schedule Trial Class
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const existingRadio = document.getElementById('existingStudent');
    const newRadio = document.getElementById('newStudent');
    const existingSection = document.getElementById('existingStudentSection');
    const newSection = document.getElementById('newStudentSection');
    const studentSelect = document.getElementById('studentSelect');
    const classSelect = document.getElementById('classSelect');
    const classInfo = document.getElementById('classInfo');

    // Toggle student sections
    function toggleStudentSections() {
        if (existingRadio.checked) {
            existingSection.style.display = 'block';
            newSection.style.display = 'none';
            studentSelect.required = true;
        } else {
            existingSection.style.display = 'none';
            newSection.style.display = 'block';
            studentSelect.required = false;
        }
    }

    existingRadio.addEventListener('change', toggleStudentSections);
    newRadio.addEventListener('change', toggleStudentSections);

    // Class info display
    classSelect.addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        if (this.value) {
            const type = selected.dataset.type;
            const teacher = selected.dataset.teacher;
            const subject = selected.dataset.subject;
            classInfo.innerHTML = `
                <strong>Subject:</strong> ${subject}<br>
                <strong>Teacher:</strong> ${teacher}<br>
                <strong>Type:</strong> ${type.charAt(0).toUpperCase() + type.slice(1)}
            `;
        } else {
            classInfo.innerHTML = '<span class="text-muted">Select a class to see details</span>';
        }
    });

    // Trigger change if class is pre-selected
    if (classSelect.value) {
        classSelect.dispatchEvent(new Event('change'));
    }
});
</script>
@endpush
@endsection
