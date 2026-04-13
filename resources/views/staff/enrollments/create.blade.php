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
                                @if(isset($selectedStudent))
                                    <option value="{{ $selectedStudent['id'] }}" selected>
                                        {{ $selectedStudent['text'] }}
                                    </option>
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
                                    <strong>Student:</strong> <span id="studentNameDisplay">-</span><br>
                                    <strong>Grade Level:</strong>
                                    <span id="studentGradeLevelDisplay" class="badge badge-primary">-</span>
                                </div>
                                <div class="col-md-6">
                                    <strong>Current Enrollments:</strong> <span id="studentEnrollmentsCount">0</span>
                                    <br>
                                    <small class="text-muted">
                                        <i class="fas fa-filter mr-1"></i>
                                        Packages &amp; classes filtered by grade level
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- All-enrolled warning -->
                        <div id="allEnrolledWarning" class="alert alert-warning" style="display: none;">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            <strong>Cannot enroll:</strong>
                            <span id="allEnrolledWarningText">Student is already enrolled in all classes of this package.</span>
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
                                            data-grade-levels="{{ json_encode($package->subject_grade_level_ids ?? []) }}"
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
                                                                data-grade-level-id="{{ $class->grade_level_id }}"
                                                                {{ old('class_id') == $class->id ? 'selected' : '' }}>
                                                            {{ $class->name }} - RM {{ number_format($class->price, 2) }}/month ({{ $class->teacher->user->name ?? 'TBA' }})
                                                            @if($class->gradeLevel) [{{ $class->gradeLevel->name }}] @endif
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('class_id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror

                                                <!-- Duplicate warning -->
                                                <div id="singleClassDuplicateWarning" class="text-danger mt-2" style="display: none;">
                                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                                    <strong>Student is already enrolled in this class.</strong> Please select a different class.
                                                </div>
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

                        <!-- Class Details Display -->
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

                        <div class="form-group">
                            <label for="status">Status</label>
                            <select class="form-control" id="status" name="status">
                                <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="trial" {{ old('status') == 'trial' ? 'selected' : '' }}>Trial</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="notes">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="2"
                                      placeholder="Optional notes...">{{ old('notes') }}</textarea>
                        </div>

                        <div class="form-group mb-0 mt-4">
                            <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
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

        <!-- Side Panel -->
        <div class="col-lg-4">
            <div class="card shadow mb-4" id="existingEnrollmentsCard" style="display: none;">
                <div class="card-header py-3 bg-warning text-dark">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-exclamation-triangle mr-1"></i> Existing Enrollments
                    </h6>
                </div>
                <div class="card-body">
                    <div id="existingEnrollmentsList"></div>
                </div>
            </div>

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
<script>
$(document).ready(function() {
    var selectedGradeLevelId = null;
    var enrolledClassIds = [];
    var enrolledPackageIds = [];
    var isSubmitting = false;

    // ==================== SELECT2 ====================
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
                return { results: data.results };
            },
            cache: true
        }
    });

    // ==================== STUDENT SELECTED ====================
    $('#student_id').on('select2:select', function(e) {
        var data = e.params.data;

        selectedGradeLevelId = data.grade_level_id || null;
        var gradeLevelName = data.grade_level_name || null;

        $('#studentInfo').show();
        $('#studentNameDisplay').text(data.text || '-');
        $('#studentGradeLevelDisplay').text(gradeLevelName || 'Not Set');

        filterPackagesByGradeLevel();
        filterClassesByGradeLevel();
        loadStudentEnrollments(data.id);
    });

    $('#student_id').on('select2:clear', function() {
        selectedGradeLevelId = null;
        enrolledClassIds = [];
        enrolledPackageIds = [];
        $('#studentInfo').hide();
        $('#existingEnrollmentsCard').hide();
        $('#allEnrolledWarning').hide();
        $('#singleClassDuplicateWarning').hide();
        resetPackageFilter();
        resetClassFilter();
    });

    // ==================== GRADE LEVEL FILTERING ====================
    function filterPackagesByGradeLevel() {
        var currentVal = $('#package_id').val();
        var matchFound = false;

        $('#package_id option').each(function() {
            var $opt = $(this);
            if (!$opt.val()) return;
            if (!selectedGradeLevelId) { $opt.prop('disabled', false).show(); return; }

            var gl = $opt.data('grade-levels');
            var arr = [];
            if (Array.isArray(gl)) arr = gl;
            else if (typeof gl === 'string') { try { arr = JSON.parse(gl); } catch(e) { arr = []; } }
            else if (typeof gl === 'number') arr = [gl];
            arr = (arr || []).map(Number);

            if (arr.includes(Number(selectedGradeLevelId))) {
                $opt.prop('disabled', false).show();
                if ($opt.val() == currentVal) matchFound = true;
            } else {
                $opt.prop('disabled', true).hide();
            }
        });

        if (currentVal && !matchFound) {
            $('#package_id').val('').trigger('change');
            $('#packageDetails').hide();
        }
    }

    function resetPackageFilter() {
        $('#package_id option').each(function() { $(this).prop('disabled', false).show(); });
    }

    function filterClassesByGradeLevel() {
        var currentVal = $('#class_id').val();
        var matchFound = false;

        $('#class_id option').each(function() {
            var $opt = $(this);
            if (!$opt.val()) return;
            if (!selectedGradeLevelId) { $opt.prop('disabled', false).show(); return; }

            var clsGL = Number($opt.data('grade-level-id'));
            if (!clsGL || clsGL === Number(selectedGradeLevelId)) {
                $opt.prop('disabled', false).show();
                if ($opt.val() == currentVal) matchFound = true;
            } else {
                $opt.prop('disabled', true).hide();
            }
        });

        if (currentVal && !matchFound) {
            $('#class_id').val('').trigger('change');
            $('#monthly_fee').val('');
            $('#classDetails').hide();
        }
        markEnrolledClassesInDropdown();
    }

    function resetClassFilter() {
        $('#class_id option').each(function() { $(this).prop('disabled', false).show(); });
    }

    // ==================== DUPLICATE DETECTION ====================
    function markEnrolledClassesInDropdown() {
        $('#class_id option').each(function() {
            var $opt = $(this);
            if (!$opt.val()) return;
            if (enrolledClassIds.includes(Number($opt.val()))) {
                var t = $opt.text();
                if (t.indexOf('[ALREADY ENROLLED]') === -1) $opt.text(t.trim() + ' [ALREADY ENROLLED]');
                $opt.prop('disabled', true);
            }
        });
    }

    // ==================== ENROLLMENT TYPE TOGGLE ====================
    $('input[name="enrollment_type"]').change(function() {
        if ($(this).val() === 'package') {
            $('#package_section').show();
            $('#packageDetails').hide();
            $('#class_selection_section, #classDetails').hide();
            $('#package_id').prop('required', true);
            $('#class_id').prop('required', false);
            $('#class_id').val('');
            $('#monthly_fee').val('');
        } else {
            $('#class_selection_section').show();
            $('#package_section, #packageDetails').hide();
            $('#class_id').prop('required', true);
            $('#package_id').prop('required', false);
            $('#package_id').val('');
        }
        $('#allEnrolledWarning').hide();
        $('#singleClassDuplicateWarning').hide();
    });

    // ==================== PACKAGE DETAILS ====================
    $('#package_id').change(function() {
        var packageId = $(this).val();
        if (packageId) {
            $.get('{{ url("staff/enrollments/package") }}/' + packageId + '/details', function(data) {
                $('#packageDetails').show();
                $('#packageFee').text(parseFloat(data.price).toFixed(2));
                $('#packageDuration').text(data.duration_months);
                var list = '';
                if (data.subjects) {
                    data.subjects.forEach(function(s) { list += '<li>' + (s.name || s) + '</li>'; });
                }
                $('#packageSubjects').html(list || '<li>No subjects</li>');
            });
        } else {
            $('#packageDetails').hide();
        }
        $('#allEnrolledWarning').hide();
    });

    // ==================== CLASS SELECTION ====================
    $('#class_id').change(function() {
        var sel = $(this).find(':selected');
        var fee = sel.data('fee');
        var teacher = sel.data('teacher');
        var subject = sel.data('subject');
        var classId = Number($(this).val());

        if (fee !== undefined && fee !== null && fee !== '') {
            var fv = parseFloat(fee);
            $('#monthly_fee').val(!isNaN(fv) ? fv.toFixed(2) : '0.00');
            $('#classDetails').show();
            $('#classTeacher').text(teacher || '-');
            $('#classSubject').text(subject || '-');
        } else {
            $('#monthly_fee').val('');
            $('#classDetails').hide();
        }

        if (classId && enrolledClassIds.includes(classId)) {
            $('#singleClassDuplicateWarning').show();
        } else {
            $('#singleClassDuplicateWarning').hide();
        }
    });

    // ==================== LOAD STUDENT ENROLLMENTS ====================
    function loadStudentEnrollments(studentId) {
        enrolledClassIds = [];
        enrolledPackageIds = [];

        $.get('{{ url("staff/enrollments/student") }}/' + studentId + '/enrollments', function(data) {
            enrolledClassIds = data.enrolled_class_ids || [];
            enrolledPackageIds = data.enrolled_package_ids || [];
            var enrollments = data.enrollments || [];

            // Fallback grade level
            if (!selectedGradeLevelId && data.grade_level_id) {
                selectedGradeLevelId = data.grade_level_id;
                $('#studentGradeLevelDisplay').text(data.grade_level_name || 'Grade ' + data.grade_level_id);
                filterPackagesByGradeLevel();
                filterClassesByGradeLevel();
            }

            markEnrolledClassesInDropdown();

            if (enrollments.length > 0) {
                var html = '<ul class="list-unstyled mb-0">';
                enrollments.forEach(function(en) {
                    var badge = '';
                    switch(en.status) {
                        case 'active':    badge = '<span class="badge badge-success">Active</span>'; break;
                        case 'suspended': badge = '<span class="badge badge-warning">Suspended</span>'; break;
                        case 'expired':   badge = '<span class="badge badge-danger">Expired</span>'; break;
                        case 'cancelled': badge = '<span class="badge badge-dark">Cancelled</span>'; break;
                        default:          badge = '<span class="badge badge-secondary">' + en.status + '</span>';
                    }
                    var name = en.package_name ? en.package_name : (en.class_name || 'Unknown');
                    if (en.subject_name) name += ' - ' + en.subject_name;

                    html += '<li class="mb-2 pb-2 border-bottom">' +
                        '<strong>' + name + '</strong><br>' +
                        '<small>' + badge + ' | RM ' + parseFloat(en.monthly_fee).toFixed(2) + '/month</small></li>';
                });
                html += '</ul>';
                $('#existingEnrollmentsList').html(html);
                $('#existingEnrollmentsCard').show();
                $('#studentEnrollmentsCount').text(enrollments.length);
            } else {
                $('#existingEnrollmentsCard').hide();
                $('#studentEnrollmentsCount').text('0');
            }
        });
    }

    // ==================== FORM SUBMIT ====================
    $('#enrollmentForm').submit(function(e) {
        if (isSubmitting) { e.preventDefault(); return false; }

        var type = $('input[name="enrollment_type"]:checked').val();

        if (type === 'class') {
            var classId = Number($('#class_id').val());
            if (!classId) {
                e.preventDefault();
                alert('Please select a class.');
                return false;
            }
            if (enrolledClassIds.includes(classId)) {
                e.preventDefault();
                alert('Student is already enrolled in this class. Please select a different class.');
                return false;
            }
        } else {
            if (!$('#package_id').val()) {
                e.preventDefault();
                alert('Please select a package.');
                return false;
            }
        }

        isSubmitting = true;
        $('#submitBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Creating...');
    });

    // ==================== INIT ====================
    $('input[name="enrollment_type"]:checked').trigger('change');

    @if(isset($selectedStudent))
        selectedGradeLevelId = {{ $selectedStudent['grade_level_id'] ?? 'null' }};
        @if(!empty($selectedStudent['grade_level_id']))
            filterPackagesByGradeLevel();
            filterClassesByGradeLevel();
        @endif
        loadStudentEnrollments({{ $selectedStudent['id'] }});
        $('#studentInfo').show();
        $('#studentNameDisplay').text({!! json_encode($selectedStudent['text'] ?? '-') !!});
    @endif
});
</script>
@endpush
