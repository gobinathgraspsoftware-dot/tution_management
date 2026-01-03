@extends('layouts.app')

@section('title', 'Create Enrollment')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0">Create New Enrollment</h1>
                <a href="{{ route('admin.enrollments.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back to List
                </a>
            </div>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('admin.enrollments.store') }}" method="POST" id="enrollmentForm">
        @csrf

        <div class="row">
            <!-- Main Form -->
            <div class="col-lg-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0">Student Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Select Student <span class="text-danger">*</span></label>
                            <select name="student_id" id="student_id" class="form-select @error('student_id') is-invalid @enderror" required>
                                <option value="">-- Search and Select Student --</option>
                                @if(isset($selectedStudent))
                                    <option value="{{ $selectedStudent['id'] }}" selected>{{ $selectedStudent['text'] }}</option>
                                @endif
                            </select>
                            @error('student_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Type student name or student ID to search
                            </small>
                        </div>

                        <!-- Student Existing Enrollments Alert -->
                        <div id="studentEnrollmentsAlert" class="alert alert-info d-none">
                            <i class="fas fa-info-circle me-2"></i>
                            <span id="studentEnrollmentsText">This student has existing enrollments.</span>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0">Enrollment Type</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="enrollment_type"
                                       id="type_package" value="package" checked>
                                <label class="form-check-label" for="type_package">
                                    <i class="fas fa-box me-1"></i> Package Enrollment
                                </label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="enrollment_type"
                                       id="type_single" value="single">
                                <label class="form-check-label" for="type_single">
                                    <i class="fas fa-chalkboard me-1"></i> Single Class
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Package Enrollment Section -->
                <div id="packageSection" class="card shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0">Package Selection</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Select Package <span class="text-danger">*</span></label>
                            <select name="package_id" id="package_id" class="form-select @error('package_id') is-invalid @enderror">
                                <option value="">-- Select Package --</option>
                                @foreach($packages as $package)
                                    <option value="{{ $package->id }}"
                                            data-price="{{ $package->price }}"
                                            data-duration="{{ $package->duration_months }}"
                                            {{ old('package_id') == $package->id ? 'selected' : '' }}>
                                        {{ $package->name }} - RM {{ number_format($package->price, 2) }}
                                        ({{ $package->duration_months }} months)
                                    </option>
                                @endforeach
                            </select>
                            @error('package_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Package Subjects & Classes Container -->
                        <div id="subjectsContainer">
                            <div class="alert alert-secondary">
                                <i class="fas fa-info-circle me-2"></i>
                                Please select a student and package to view available subjects and classes.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Single Class Section -->
                <div id="singleClassSection" class="card shadow-sm mb-4 d-none">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0">Class Selection</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label">Select Class <span class="text-danger">*</span></label>
                                <select name="class_id" id="class_id" class="form-select @error('class_id') is-invalid @enderror">
                                    <option value="">-- Select Class --</option>
                                    @foreach($classes as $class)
                                        <option value="{{ $class->id }}"
                                                data-fee="{{ $class->price }}"
                                                {{ old('class_id') == $class->id ? 'selected' : '' }}>
                                            {{ $class->name }} - {{ $class->subject->name ?? 'N/A' }}
                                            ({{ $class->teacher->user->name ?? 'No Teacher' }}) - RM {{ number_format($class->price, 2) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('class_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Monthly Fee (RM)</label>
                                <input type="text" id="monthly_fee_display" class="form-control bg-light" readonly
                                       placeholder="Select a class">
                                <input type="hidden" name="monthly_fee" id="monthly_fee" value="{{ old('monthly_fee') }}">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Enrollment Details -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0">Enrollment Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Start Date <span class="text-danger">*</span></label>
                                <input type="date" name="start_date" id="start_date"
                                       class="form-control @error('start_date') is-invalid @enderror"
                                       value="{{ old('start_date', date('Y-m-d')) }}" required>
                                @error('start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Payment Cycle Day <span class="text-danger">*</span></label>
                                <select name="payment_cycle_day" id="payment_cycle_day"
                                        class="form-select @error('payment_cycle_day') is-invalid @enderror" required>
                                    @for($i = 1; $i <= 15; $i++)
                                        <option value="{{ $i }}" {{ old('payment_cycle_day', 1) == $i ? 'selected' : '' }}>
                                            {{ $i }}{{ $i == 1 ? 'st' : ($i == 2 ? 'nd' : ($i == 3 ? 'rd' : 'th')) }} of each month
                                        </option>
                                    @endfor
                                </select>
                                @error('payment_cycle_day')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" id="status" class="form-select @error('status') is-invalid @enderror">
                                <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="trial" {{ old('status') == 'trial' ? 'selected' : '' }}>Trial</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Summary Sidebar -->
            <div class="col-lg-4">
                <div class="card shadow-sm" style="top: 20px;">
                    <div class="card-header bg-primary text-white py-3">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-receipt me-2"></i> Enrollment Summary
                        </h5>
                    </div>
                    <div class="card-body">
                        <div id="summaryContent">
                            <p class="text-muted mb-0">Please complete the form to see summary.</p>
                        </div>
                    </div>
                    <div class="card-footer bg-white">
                        <button type="submit" class="btn btn-primary w-100" id="submitBtn" disabled>
                            <i class="fas fa-save me-1"></i> Create Enrollment
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let selectedPackageData = null;
    let studentEnrollments = [];

    // Initialize Select2 for Student dropdown with AJAX search
    $('#student_id').select2({
        theme: 'bootstrap-5',
        placeholder: 'Type student name or ID to search...',
        allowClear: true,
        minimumInputLength: 1,
        ajax: {
            url: '{{ route("admin.enrollments.search-students") }}',
            dataType: 'json',
            delay: 300,
            data: function(params) {
                return {
                    q: params.term,
                    page: params.page || 1
                };
            },
            processResults: function(data, params) {
                params.page = params.page || 1;
                return {
                    results: data.results,
                    pagination: {
                        more: data.pagination.more
                    }
                };
            },
            cache: true
        }
    });

    // When student is selected
    $('#student_id').on('select2:select', function(e) {
        fetchStudentEnrollments(e.params.data.id);
    });

    // When student selection is cleared
    $('#student_id').on('select2:clear', function() {
        studentEnrollments = [];
        $('#studentEnrollmentsAlert').addClass('d-none');
        updateSummary();
    });

    // Fetch student enrollments
    function fetchStudentEnrollments(studentId) {
        studentEnrollments = [];

        $.get(`/admin/enrollments/student/${studentId}/enrollments`, function(data) {
            studentEnrollments = data.enrolled_class_ids || [];

            if (studentEnrollments.length > 0) {
                $('#studentEnrollmentsAlert').removeClass('d-none');
                $('#studentEnrollmentsText').text(`This student is already enrolled in ${studentEnrollments.length} class(es).`);
            } else {
                $('#studentEnrollmentsAlert').addClass('d-none');
            }

            if ($('#package_id').val()) {
                loadPackageSubjects($('#package_id').val());
            }
        }).fail(function() {
            studentEnrollments = [];
            $('#studentEnrollmentsAlert').addClass('d-none');
            if ($('#package_id').val()) {
                loadPackageSubjects($('#package_id').val());
            }
        });

        updateSummary();
    }

    // Toggle enrollment type sections
    $('input[name="enrollment_type"]').change(function() {
        const type = $(this).val();
        if (type === 'package') {
            $('#packageSection').removeClass('d-none');
            $('#singleClassSection').addClass('d-none');
            $('#class_id').prop('required', false);
            $('#monthly_fee').prop('required', false);
            $('#package_id').prop('required', true);
        } else {
            $('#packageSection').addClass('d-none');
            $('#singleClassSection').removeClass('d-none');
            $('#class_id').prop('required', true);
            $('#monthly_fee').prop('required', true);
            $('#package_id').prop('required', false);
        }
        updateSummary();
    });

    // When package changes
    $('#package_id').change(function() {
        const packageId = $(this).val();
        if (packageId) {
            loadPackageSubjects(packageId);
        } else {
            $('#subjectsContainer').html(`
                <div class="alert alert-secondary">
                    <i class="fas fa-info-circle me-2"></i>
                    Please select a package to view available subjects and classes.
                </div>
            `);
        }
        updateSummary();
    });

    // Load package subjects
    function loadPackageSubjects(packageId) {
        const studentId = $('#student_id').val();
        let url = `/admin/enrollments/package/${packageId}/subjects-classes`;
        if (studentId) {
            url += `?student_id=${studentId}`;
        }

        $('#subjectsContainer').html(`
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-2 mb-0">Loading subjects and classes...</p>
            </div>
        `);

        $.get(url, function(data) {
            selectedPackageData = data;
            renderSubjectsAndClasses(data);
            updateSummary();
        }).fail(function() {
            $('#subjectsContainer').html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Failed to load package details. Please try again.
                </div>
            `);
        });
    }

    // Render subjects and classes (WITHOUT sessions/month badge)
    function renderSubjectsAndClasses(data) {
        if (!data.subjects || data.subjects.length === 0) {
            $('#subjectsContainer').html(`
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    This package has no subjects configured.
                </div>
            `);
            return;
        }

        let html = `
            <div class="mb-3">
                <div class="alert alert-info">
                    <i class="fas fa-lightbulb me-2"></i>
                    <strong>Package:</strong> ${data.name} - RM ${parseFloat(data.price).toFixed(2)} for ${data.duration_months} month(s)
                    <br><small>Select one class for each subject below.</small>
                </div>
            </div>
        `;

        data.subjects.forEach(function(subject) {
            html += `
                <div class="card mb-3 border">
                    <div class="card-header bg-light py-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-book me-2"></i><strong>${subject.name}</strong></span>
                        </div>
                    </div>
                    <div class="card-body">
            `;

            if (!subject.classes || subject.classes.length === 0) {
                html += `<div class="alert alert-warning mb-0">No active classes available.</div>`;
            } else {
                html += `<select name="subject_classes[${subject.id}]" class="form-select subject-class-select" data-subject="${subject.name}"><option value="">-- Select a class --</option>`;

                subject.classes.forEach(function(cls) {
                    const isEnrolled = cls.is_enrolled;
                    const isFull = cls.available_seats <= 0;
                    const isDisabled = isEnrolled || isFull;
                    let statusText = isEnrolled ? ' [ALREADY ENROLLED]' : (isFull ? ' [FULL]' : '');

                    html += `<option value="${cls.id}" data-enrolled="${isEnrolled ? '1' : '0'}" ${isDisabled ? 'disabled' : ''}>
                        ${cls.name} - ${cls.teacher_name || 'No Teacher'} (${cls.available_seats} seats)${statusText}
                    </option>`;
                });

                html += `</select>`;
            }

            html += `</div></div>`;
        });

        $('#subjectsContainer').html(html);
        $('.subject-class-select').change(updateSummary);
    }

    // When single class changes - show price in disabled field
    $('#class_id').change(function() {
        const fee = $(this).find(':selected').data('fee');
        if (fee) {
            const formattedFee = parseFloat(fee).toFixed(2);
            $('#monthly_fee_display').val(formattedFee);
            $('#monthly_fee').val(formattedFee);
        } else {
            $('#monthly_fee_display').val('');
            $('#monthly_fee').val('');
        }
        updateSummary();
    });

    // Update summary
    function updateSummary() {
        const studentId = $('#student_id').val();
        const enrollmentType = $('input[name="enrollment_type"]:checked').val();
        const startDate = $('#start_date').val();

        let html = '';
        let isValid = false;

        // Get student name from Select2
        let studentName = 'Not Selected';
        const studentData = $('#student_id').select2('data');
        if (studentData && studentData.length > 0 && studentData[0].text) {
            studentName = studentData[0].text;
        }

        if (!studentId) {
            html = '<p class="text-muted mb-0">Please select a student.</p>';
        } else {
            html = `<div class="mb-3 pb-3 border-bottom"><small class="text-muted d-block">Student</small><strong>${studentName}</strong></div>`;

            if (enrollmentType === 'package') {
                const packageId = $('#package_id').val();

                if (packageId && selectedPackageData) {
                    let selectedClasses = [];
                    let totalNewClasses = 0;

                    $('.subject-class-select').each(function() {
                        const classId = $(this).val();
                        if (classId) {
                            const isAlreadyEnrolled = $(this).find(':selected').data('enrolled') == 1;
                            selectedClasses.push({
                                subject: $(this).data('subject'),
                                class: $(this).find(':selected').text().split(' - ')[0],
                                enrolled: isAlreadyEnrolled
                            });
                            if (!isAlreadyEnrolled) totalNewClasses++;
                        }
                    });

                    html += `<div class="mb-3 pb-3 border-bottom"><small class="text-muted d-block">Package</small><strong>${selectedPackageData.name}</strong>
                        <div class="mt-1"><span class="badge bg-info">RM ${parseFloat(selectedPackageData.price).toFixed(2)}</span>
                        <span class="badge bg-secondary">${selectedPackageData.duration_months} months</span></div></div>`;

                    if (selectedClasses.length > 0) {
                        html += `<div class="mb-3 pb-3 border-bottom"><small class="text-muted d-block">Selected Classes (${selectedClasses.length})</small><ul class="list-unstyled mb-0 mt-2">`;
                        selectedClasses.forEach(function(cls) {
                            const badge = cls.enrolled ? '<span class="badge bg-warning ms-1">Enrolled</span>' : '<span class="badge bg-success ms-1">New</span>';
                            html += `<li class="small"><i class="fas fa-check text-success me-1"></i> ${cls.subject}: ${cls.class} ${badge}</li>`;
                        });
                        html += `</ul></div>`;

                        if (totalNewClasses > 0) {
                            isValid = true;
                        }
                    }
                } else {
                    html += '<p class="text-muted mb-0">Please select a package.</p>';
                }
            } else {
                const classId = $('#class_id').val();
                const monthlyFee = $('#monthly_fee').val();

                if (classId && monthlyFee) {
                    html += `<div class="mb-3 pb-3 border-bottom"><small class="text-muted d-block">Class</small><strong>${$('#class_id option:selected').text()}</strong></div>`;
                    html += `<div class="mb-3 pb-3 border-bottom"><small class="text-muted d-block">Monthly Fee</small><strong class="text-success">RM ${parseFloat(monthlyFee).toFixed(2)}</strong></div>`;
                    isValid = true;
                } else {
                    html += '<p class="text-muted mb-0">Please select a class.</p>';
                }
            }

            if (startDate) {
                html += `<div class="mb-0"><small class="text-muted d-block">Start Date</small><strong>${startDate}</strong></div>`;
            }
        }

        $('#summaryContent').html(html);
        $('#submitBtn').prop('disabled', !isValid);
    }

    // Form validation
    $('#enrollmentForm').submit(function(e) {
        if ($('input[name="enrollment_type"]:checked').val() === 'package') {
            let hasNewEnrollment = false;
            $('.subject-class-select').each(function() {
                if ($(this).val() && $(this).find(':selected').data('enrolled') != 1) {
                    hasNewEnrollment = true;
                }
            });

            if (!hasNewEnrollment) {
                e.preventDefault();
                alert('Please select at least one new class.');
                return false;
            }
        }
    });

    // Initialize if pre-selected student exists
    @if(isset($selectedStudent))
        fetchStudentEnrollments({{ $selectedStudent['id'] }});
    @endif

    updateSummary();
});
</script>
@endpush
