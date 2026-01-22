@extends('layouts.app')

@section('title', 'Create Enrollment')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-user-graduate"></i> Create New Enrollment
        </h1>
        <a href="{{ route('staff.enrollments.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <!-- Enrollment Form -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Enrollment Details</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('staff.enrollments.store') }}" id="enrollmentForm">
                        @csrf

                        <!-- Student Selection with Select2 -->
                        <div class="form-group">
                            <label for="student_id">Student <span class="text-danger">*</span></label>
                            <select class="form-control select2-student @error('student_id') is-invalid @enderror"
                                    id="student_id" name="student_id" required>
                                <option value="">Search student by name, ID, or email...</option>
                                @if(old('student_id'))
                                    @php
                                        $selectedStudent = $students->firstWhere('id', old('student_id'));
                                    @endphp
                                    @if($selectedStudent)
                                        <option value="{{ $selectedStudent->id }}" selected>
                                            {{ $selectedStudent->user->name }} ({{ $selectedStudent->student_id }})
                                        </option>
                                    @endif
                                @endif
                            </select>
                            @error('student_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Start typing to search for students</small>
                        </div>

                        <!-- Student Info Display -->
                        <div id="studentInfo" class="alert alert-info" style="display: none;">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Student ID:</strong> <span id="studentIdDisplay">-</span><br>
                                    <strong>Email:</strong> <span id="studentEmailDisplay">-</span>
                                </div>
                                <div class="col-md-6">
                                    <strong>Phone:</strong> <span id="studentPhoneDisplay">-</span><br>
                                    <strong>Current Enrollments:</strong> <span id="studentEnrollmentsCount">0</span>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <!-- Enrollment Type -->
                        <div class="form-group">
                            <label>Enrollment Type <span class="text-danger">*</span></label>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="custom-control custom-radio">
                                        <input type="radio" id="type_package" name="enrollment_type"
                                               class="custom-control-input" value="package"
                                               {{ old('enrollment_type', 'package') == 'package' ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="type_package">
                                            <strong>Package</strong> (Multiple subjects)
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="custom-control custom-radio">
                                        <input type="radio" id="type_class" name="enrollment_type"
                                               class="custom-control-input" value="class"
                                               {{ old('enrollment_type') == 'class' ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="type_class">
                                            <strong>Single Class</strong>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Package Selection -->
                        <div class="form-group" id="package_section">
                            <label for="package_id">Package <span class="text-danger">*</span></label>
                            <select class="form-control @error('package_id') is-invalid @enderror"
                                    id="package_id" name="package_id">
                                <option value="">Select Package...</option>
                                @foreach($packages as $package)
                                    <option value="{{ $package->id }}"
                                            data-price="{{ $package->price }}"
                                            data-duration="{{ $package->duration_months }}"
                                            data-type="{{ $package->type }}"
                                            {{ old('package_id') == $package->id ? 'selected' : '' }}>
                                        {{ $package->name }} - RM {{ number_format($package->price, 2) }}/month
                                        ({{ $package->duration_months }} months)
                                    </option>
                                @endforeach
                            </select>
                            @error('package_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Package Details Display -->
                        <div id="packageDetails" class="alert alert-success" style="display: none;">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Monthly Fee:</strong> RM <span id="packageFee">0.00</span><br>
                                    <strong>Duration:</strong> <span id="packageDuration">-</span> months
                                </div>
                                <div class="col-md-6">
                                    <strong>Subjects Included:</strong>
                                    <ul id="packageSubjects" class="mb-0 small"></ul>
                                </div>
                            </div>
                        </div>

                        <!-- Class Selection Section (Single Class Enrollment) -->
                        <div id="class_selection_section" style="display: none;">
                            <div class="card mb-3">
                                <div class="card-header py-2 bg-light">
                                    <h6 class="m-0 font-weight-bold text-dark">Class Selection</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-7">
                                            <div class="form-group mb-0">
                                                <label for="class_id">Select Class <span class="text-danger">*</span></label>
                                                <select class="form-control @error('class_id') is-invalid @enderror"
                                                        id="class_id" name="class_id">
                                                    <option value="">Select Class...</option>
                                                    @foreach($classes as $class)
                                                        <option value="{{ $class->id }}"
                                                                data-fee="{{ $class->price }}"
                                                                data-subject="{{ $class->subject->name ?? 'N/A' }}"
                                                                data-teacher="{{ $class->teacher->user->name ?? 'TBA' }}"
                                                                {{ old('class_id') == $class->id ? 'selected' : '' }}>
                                                            {{ $class->name }} - RM {{ number_format($class->price, 2) }}/month ({{ $class->teacher->user->name ?? 'TBA' }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('class_id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-5">
                                            <div class="form-group mb-0">
                                                <label for="monthly_fee">Monthly Fee (RM)</label>
                                                <input type="number" step="0.01" class="form-control @error('monthly_fee') is-invalid @enderror"
                                                       id="monthly_fee" name="monthly_fee" value="{{ old('monthly_fee') }}"
                                                       placeholder="0.00" readonly style="background-color: #e9ecef;">
                                                @error('monthly_fee')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Class Details Display (Teacher & Subject info) -->
                        <div id="classDetails" class="alert alert-info" style="display: none;">
                            <div class="row">
                                <div class="col-md-6">
                                    <i class="fas fa-book mr-2"></i>
                                    <strong>Subject:</strong> <span id="classSubject">-</span>
                                </div>
                                <div class="col-md-6">
                                    <i class="fas fa-chalkboard-teacher mr-2"></i>
                                    <strong>Teacher:</strong> <span id="classTeacher">-</span>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="row">
                            <div class="col-md-6">
                                <!-- Start Date -->
                                <div class="form-group">
                                    <label for="start_date">Start Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('start_date') is-invalid @enderror"
                                           id="start_date" name="start_date"
                                           value="{{ old('start_date', date('Y-m-d')) }}"
                                           min="{{ date('Y-m-d') }}" required>
                                    @error('start_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <!-- Payment Cycle Day -->
                                <div class="form-group">
                                    <label for="payment_cycle_day">Payment Cycle Day <span class="text-danger">*</span></label>
                                    <select class="form-control @error('payment_cycle_day') is-invalid @enderror"
                                            id="payment_cycle_day" name="payment_cycle_day" required>
                                        @for($i = 1; $i <= 15; $i++)
                                            <option value="{{ $i }}" {{ old('payment_cycle_day') == $i ? 'selected' : '' }}>
                                                Day {{ $i }} of each month
                                            </option>
                                        @endfor
                                    </select>
                                    @error('payment_cycle_day')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Monthly payment will be due on this day.</small>
                                </div>
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select class="form-control" id="status" name="status">
                                <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="trial" {{ old('status') == 'trial' ? 'selected' : '' }}>Trial</option>
                            </select>
                        </div>

                        <!-- Notes -->
                        <div class="form-group">
                            <label for="notes">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="2"
                                      placeholder="Optional notes...">{{ old('notes') }}</textarea>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="form-group mb-0 mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-plus-circle mr-1"></i> Create Enrollment
                            </button>
                            <a href="{{ route('staff.enrollments.index') }}" class="btn btn-secondary btn-lg ml-2">
                                <i class="fas fa-times mr-1"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Side Panel - Existing Enrollments -->
        <div class="col-lg-4">
            <div class="card shadow mb-4" id="existingEnrollmentsCard" style="display: none;">
                <div class="card-header py-3 bg-warning text-dark">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-exclamation-triangle mr-1"></i> Existing Enrollments
                    </h6>
                </div>
                <div class="card-body">
                    <div id="existingEnrollmentsList">
                        <!-- Dynamically loaded -->
                    </div>
                </div>
            </div>

            <!-- Help Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">
                        <i class="fas fa-info-circle mr-1"></i> Enrollment Guide
                    </h6>
                </div>
                <div class="card-body small">
                    <p><strong>Package Enrollment:</strong></p>
                    <ul class="pl-3">
                        <li>Student gets access to multiple subjects</li>
                        <li>Fixed monthly fee for all subjects</li>
                        <li>Classes can be assigned later</li>
                    </ul>

                    <p class="mt-3"><strong>Single Class Enrollment:</strong></p>
                    <ul class="pl-3">
                        <li>Student enrolls in one specific class</li>
                        <li>Fee based on class price</li>
                        <li>Immediate class assignment</li>
                    </ul>

                    <p class="mt-3"><strong>Payment Cycle:</strong></p>
                    <p class="text-muted">Invoices will be generated on the selected day each month.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<style>
    .select2-container--bootstrap-5 .select2-selection {
        min-height: 38px;
        border: 1px solid #ced4da;
    }
    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
        line-height: 36px;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize Select2 for student search
    $('.select2-student').select2({
        theme: 'bootstrap-5',
        placeholder: 'Search student by name, ID, or email...',
        allowClear: true,
        minimumInputLength: 2,
        ajax: {
            url: '{{ route("staff.enrollments.search-students") }}',
            dataType: 'json',
            delay: 250,
            processResults: function(data) {
                return {
                    results: data.results
                };
            },
            cache: true
        }
    });

    // When student is selected, show info and existing enrollments
    $('#student_id').on('select2:select', function(e) {
        var data = e.params.data;

        // Show student info
        $('#studentInfo').show();
        $('#studentIdDisplay').text(data.student_id || '-');
        $('#studentEmailDisplay').text(data.email || '-');
        $('#studentPhoneDisplay').text(data.phone || '-');

        // Load existing enrollments
        loadStudentEnrollments(data.id);
    });

    $('#student_id').on('select2:clear', function() {
        $('#studentInfo').hide();
        $('#existingEnrollmentsCard').hide();
    });

    // Toggle enrollment type
    $('input[name="enrollment_type"]').change(function() {
        if ($(this).val() === 'package') {
            $('#package_section').show();
            $('#packageDetails').hide();
            $('#class_selection_section, #classDetails').hide();
            $('#package_id').prop('required', true);
            $('#class_id').prop('required', false);
            // Clear class selection
            $('#class_id').val('');
            $('#monthly_fee').val('');
        } else {
            $('#class_selection_section').show();
            $('#package_section, #packageDetails').hide();
            $('#class_id').prop('required', true);
            $('#package_id').prop('required', false);
            // Clear package selection
            $('#package_id').val('');
        }
    });

    // Load package details
    $('#package_id').change(function() {
        const packageId = $(this).val();
        if (packageId) {
            $.get('{{ url("staff/enrollments/package") }}/' + packageId + '/details', function(data) {
                $('#packageDetails').show();
                $('#packageFee').text(parseFloat(data.price).toFixed(2));
                $('#packageDuration').text(data.duration_months);

                let subjectsList = '';
                data.subjects.forEach(function(subject) {
                    subjectsList += '<li>' + subject.name + '</li>';
                });
                $('#packageSubjects').html(subjectsList || '<li>No subjects</li>');
            });
        } else {
            $('#packageDetails').hide();
        }
    });

    // Auto-fill monthly fee when class is selected
    $('#class_id').change(function() {
        const selectedOption = $(this).find(':selected');
        const fee = selectedOption.data('fee');
        const teacher = selectedOption.data('teacher');
        const subject = selectedOption.data('subject');

        if (fee !== undefined && fee !== null && fee !== '') {
            var feeValue = parseFloat(fee);
            if (!isNaN(feeValue)) {
                $('#monthly_fee').val(feeValue.toFixed(2));
            } else {
                $('#monthly_fee').val('0.00');
            }
            $('#classDetails').show();
            $('#classTeacher').text(teacher || '-');
            $('#classSubject').text(subject || '-');
        } else {
            $('#monthly_fee').val('');
            $('#classDetails').hide();
        }
    });

    // Function to load student's existing enrollments
    function loadStudentEnrollments(studentId) {
        $.get('{{ url("staff/enrollments/student") }}/' + studentId + '/enrollments', function(data) {
            if (data.length > 0) {
                let html = '<ul class="list-unstyled mb-0">';
                data.forEach(function(enrollment) {
                    let statusBadge = '';
                    switch(enrollment.status) {
                        case 'active':
                            statusBadge = '<span class="badge badge-success">Active</span>';
                            break;
                        case 'suspended':
                            statusBadge = '<span class="badge badge-warning">Suspended</span>';
                            break;
                        case 'expired':
                            statusBadge = '<span class="badge badge-danger">Expired</span>';
                            break;
                        case 'cancelled':
                            statusBadge = '<span class="badge badge-dark">Cancelled</span>';
                            break;
                        default:
                            statusBadge = '<span class="badge badge-secondary">' + enrollment.status + '</span>';
                    }

                    html += '<li class="mb-2 pb-2 border-bottom">' +
                        '<strong>' + enrollment.name + '</strong><br>' +
                        '<small>' + statusBadge + ' | RM ' + parseFloat(enrollment.monthly_fee).toFixed(2) + '/month</small>' +
                        '</li>';
                });
                html += '</ul>';

                $('#existingEnrollmentsList').html(html);
                $('#existingEnrollmentsCard').show();
                $('#studentEnrollmentsCount').text(data.length);
            } else {
                $('#existingEnrollmentsCard').hide();
                $('#studentEnrollmentsCount').text('0');
            }
        });
    }

    // Trigger initial state
    $('input[name="enrollment_type"]:checked').trigger('change');
});
</script>
@endpush
