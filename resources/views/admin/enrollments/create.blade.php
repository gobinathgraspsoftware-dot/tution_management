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
                                <option value="">-- Select Student --</option>
                                @foreach($students as $student)
                                    <option value="{{ $student->id }}" {{ old('student_id') == $student->id ? 'selected' : '' }}>
                                        {{ $student->user->name }} ({{ $student->student_id ?? 'ID: '.$student->id }})
                                    </option>
                                @endforeach
                            </select>
                            @error('student_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
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
                        <div class="mb-3">
                            <label class="form-label">Select Class <span class="text-danger">*</span></label>
                            <select name="class_id" id="class_id" class="form-select @error('class_id') is-invalid @enderror">
                                <option value="">-- Select Class --</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}"
                                            data-fee="{{ $class->monthly_fee }}"
                                            data-subject="{{ $class->subject->name ?? 'N/A' }}"
                                            {{ old('class_id') == $class->id ? 'selected' : '' }}>
                                        {{ $class->name }} - {{ $class->subject->name ?? 'N/A' }}
                                        ({{ $class->teacher->user->name ?? 'No Teacher' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('class_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Monthly Fee (RM) <span class="text-danger">*</span></label>
                            <input type="number" name="monthly_fee" id="monthly_fee" step="0.01" min="0"
                                   class="form-control @error('monthly_fee') is-invalid @enderror"
                                   value="{{ old('monthly_fee') }}">
                            @error('monthly_fee')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
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
                                    @for($i = 1; $i <= 28; $i++)
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

    // When student changes, fetch their existing enrollments
    $('#student_id').change(function() {
        const studentId = $(this).val();
        studentEnrollments = [];

        if (studentId) {
            // Fetch student's existing enrollments
            $.get(`/admin/enrollments/student/${studentId}/enrollments`, function(data) {
                studentEnrollments = data.enrolled_class_ids || [];

                if (studentEnrollments.length > 0) {
                    $('#studentEnrollmentsAlert').removeClass('d-none');
                    $('#studentEnrollmentsText').text(`This student is already enrolled in ${studentEnrollments.length} class(es). Already enrolled classes will be shown as disabled.`);
                } else {
                    $('#studentEnrollmentsAlert').addClass('d-none');
                }

                // Reload package subjects if package is selected
                if ($('#package_id').val()) {
                    loadPackageSubjects($('#package_id').val());
                }
            }).fail(function() {
                // Endpoint might not exist yet, continue without enrollment data
                studentEnrollments = [];
                $('#studentEnrollmentsAlert').addClass('d-none');

                if ($('#package_id').val()) {
                    loadPackageSubjects($('#package_id').val());
                }
            });
        } else {
            $('#studentEnrollmentsAlert').addClass('d-none');
        }

        updateSummary();
    });

    // When package changes, load subjects
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

    // Load package subjects with classes
    function loadPackageSubjects(packageId) {
        const studentId = $('#student_id').val();
        let url = `/admin/enrollments/package/${packageId}/subjects-classes`;

        // Add student_id as query param to get enrollment status
        if (studentId) {
            url += `?student_id=${studentId}`;
        }

        $('#subjectsContainer').html(`
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
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

    // Render subjects and their classes
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
                    <br><small>Select one class for each subject below. Already enrolled classes are marked and disabled.</small>
                </div>
            </div>
        `;

        data.subjects.forEach(function(subject) {
            const hasAvailableClasses = subject.classes && subject.classes.some(c => !c.is_enrolled && c.available_seats > 0);

            html += `
                <div class="card mb-3 border ${hasAvailableClasses ? '' : 'border-warning'}">
                    <div class="card-header bg-light py-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>
                                <i class="fas fa-book me-2"></i>
                                <strong>${subject.name}</strong>
                                ${subject.code ? `<small class="text-muted">(${subject.code})</small>` : ''}
                            </span>
                            <span class="badge bg-secondary">${subject.sessions_per_month || 4} sessions/month</span>
                        </div>
                    </div>
                    <div class="card-body">
            `;

            if (!subject.classes || subject.classes.length === 0) {
                html += `
                    <div class="alert alert-warning mb-0">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        No active classes available for this subject.
                    </div>
                `;
            } else {
                html += `
                    <select name="subject_classes[${subject.id}]" class="form-select subject-class-select" data-subject="${subject.name}">
                        <option value="">-- Select a class --</option>
                `;

                subject.classes.forEach(function(cls) {
                    const isEnrolled = cls.is_enrolled;
                    const isFull = cls.available_seats <= 0;
                    const isDisabled = isEnrolled || isFull;

                    let statusText = '';
                    if (isEnrolled) {
                        statusText = ' [ALREADY ENROLLED]';
                    } else if (isFull) {
                        statusText = ' [FULL]';
                    }

                    html += `
                        <option value="${cls.id}"
                                data-teacher="${cls.teacher_name || 'No Teacher'}"
                                data-seats="${cls.available_seats}"
                                data-enrolled="${isEnrolled ? '1' : '0'}"
                                ${isDisabled ? 'disabled' : ''}>
                            ${cls.name} - ${cls.teacher_name || 'No Teacher'}
                            (${cls.available_seats} seats available)${statusText}
                        </option>
                    `;
                });

                html += `</select>`;

                // Add helper text
                const enrolledCount = subject.classes.filter(c => c.is_enrolled).length;
                if (enrolledCount > 0) {
                    html += `<small class="text-warning d-block mt-1"><i class="fas fa-info-circle me-1"></i>${enrolledCount} class(es) disabled (already enrolled)</small>`;
                }
            }

            html += `
                    </div>
                </div>
            `;
        });

        $('#subjectsContainer').html(html);

        // Attach change event to class selects
        $('.subject-class-select').change(function() {
            updateSummary();
        });
    }

    // When single class changes, update fee
    $('#class_id').change(function() {
        const selectedOption = $(this).find(':selected');
        const fee = selectedOption.data('fee');
        if (fee) {
            $('#monthly_fee').val(parseFloat(fee).toFixed(2));
        }
        updateSummary();
    });

    // Update summary panel
    function updateSummary() {
        const studentId = $('#student_id').val();
        const studentName = $('#student_id option:selected').text();
        const enrollmentType = $('input[name="enrollment_type"]:checked').val();
        const startDate = $('#start_date').val();
        const paymentDay = $('#payment_cycle_day').val();

        let html = '';
        let isValid = false;

        if (!studentId) {
            html = '<p class="text-muted mb-0">Please select a student.</p>';
        } else {
            html = `
                <div class="mb-3 pb-3 border-bottom">
                    <small class="text-muted d-block">Student</small>
                    <strong>${studentName}</strong>
                </div>
            `;

            if (enrollmentType === 'package') {
                const packageId = $('#package_id').val();
                const packageName = $('#package_id option:selected').text();

                if (packageId && selectedPackageData) {
                    // Count selected classes
                    let selectedClasses = [];
                    let totalNewClasses = 0;

                    $('.subject-class-select').each(function() {
                        const classId = $(this).val();
                        const subjectName = $(this).data('subject');
                        const selectedOption = $(this).find(':selected');

                        if (classId) {
                            const isAlreadyEnrolled = selectedOption.data('enrolled') === 1 || selectedOption.data('enrolled') === '1';
                            selectedClasses.push({
                                subject: subjectName,
                                class: selectedOption.text().split(' - ')[0],
                                enrolled: isAlreadyEnrolled
                            });

                            if (!isAlreadyEnrolled) {
                                totalNewClasses++;
                            }
                        }
                    });

                    html += `
                        <div class="mb-3 pb-3 border-bottom">
                            <small class="text-muted d-block">Package</small>
                            <strong>${selectedPackageData.name}</strong>
                            <div class="mt-1">
                                <span class="badge bg-info">RM ${parseFloat(selectedPackageData.price).toFixed(2)}</span>
                                <span class="badge bg-secondary">${selectedPackageData.duration_months} months</span>
                            </div>
                        </div>
                    `;

                    if (selectedClasses.length > 0) {
                        html += `
                            <div class="mb-3 pb-3 border-bottom">
                                <small class="text-muted d-block">Selected Classes (${selectedClasses.length})</small>
                                <ul class="list-unstyled mb-0 mt-2">
                        `;

                        selectedClasses.forEach(function(cls) {
                            const statusBadge = cls.enrolled
                                ? '<span class="badge bg-warning ms-1">Already Enrolled</span>'
                                : '<span class="badge bg-success ms-1">New</span>';
                            html += `<li class="small"><i class="fas fa-check text-success me-1"></i> ${cls.subject}: ${cls.class} ${statusBadge}</li>`;
                        });

                        html += `</ul></div>`;

                        // Show new enrollment count
                        if (totalNewClasses > 0) {
                            html += `
                                <div class="alert alert-success py-2 mb-3">
                                    <small><i class="fas fa-plus-circle me-1"></i> ${totalNewClasses} new enrollment(s) will be created</small>
                                </div>
                            `;
                            isValid = true;
                        } else {
                            html += `
                                <div class="alert alert-warning py-2 mb-3">
                                    <small><i class="fas fa-exclamation-triangle me-1"></i> All selected classes are already enrolled</small>
                                </div>
                            `;
                        }
                    } else {
                        html += `
                            <div class="alert alert-warning py-2 mb-0">
                                <small><i class="fas fa-exclamation-circle me-1"></i> Please select at least one class</small>
                            </div>
                        `;
                    }
                } else {
                    html += '<p class="text-muted mb-0">Please select a package.</p>';
                }
            } else {
                // Single class enrollment
                const classId = $('#class_id').val();
                const className = $('#class_id option:selected').text();
                const monthlyFee = $('#monthly_fee').val();

                if (classId && monthlyFee) {
                    html += `
                        <div class="mb-3 pb-3 border-bottom">
                            <small class="text-muted d-block">Class</small>
                            <strong>${className}</strong>
                        </div>
                        <div class="mb-3 pb-3 border-bottom">
                            <small class="text-muted d-block">Monthly Fee</small>
                            <strong class="text-success">RM ${parseFloat(monthlyFee).toFixed(2)}</strong>
                        </div>
                    `;
                    isValid = true;
                } else {
                    html += '<p class="text-muted mb-0">Please select a class.</p>';
                }
            }

            if (startDate) {
                html += `
                    <div class="mb-0">
                        <small class="text-muted d-block">Start Date</small>
                        <strong>${startDate}</strong>
                        <small class="text-muted d-block mt-1">Payment on ${paymentDay}${getOrdinalSuffix(paymentDay)} of each month</small>
                    </div>
                `;
            }
        }

        $('#summaryContent').html(html);
        $('#submitBtn').prop('disabled', !isValid);
    }

    function getOrdinalSuffix(n) {
        const s = ["th", "st", "nd", "rd"];
        const v = n % 100;
        return (s[(v - 20) % 10] || s[v] || s[0]);
    }

    // Validate before submit
    $('#enrollmentForm').submit(function(e) {
        const enrollmentType = $('input[name="enrollment_type"]:checked').val();

        if (enrollmentType === 'package') {
            let hasSelection = false;
            let hasNewEnrollment = false;

            $('.subject-class-select').each(function() {
                const classId = $(this).val();
                if (classId) {
                    hasSelection = true;
                    const selectedOption = $(this).find(':selected');
                    const isAlreadyEnrolled = selectedOption.data('enrolled') === 1 || selectedOption.data('enrolled') === '1';
                    if (!isAlreadyEnrolled) {
                        hasNewEnrollment = true;
                    }
                }
            });

            if (!hasSelection) {
                e.preventDefault();
                alert('Please select at least one class for the package enrollment.');
                return false;
            }

            if (!hasNewEnrollment) {
                e.preventDefault();
                alert('All selected classes are already enrolled. Please select at least one new class.');
                return false;
            }
        }
    });

    // Initialize
    updateSummary();
});
</script>
@endpush
