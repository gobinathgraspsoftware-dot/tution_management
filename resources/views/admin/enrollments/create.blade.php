@extends('layouts.app')

@section('title', 'Create Enrollment')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-user-graduate"></i> Create New Enrollment
        </h1>
        <a href="{{ route('admin.enrollments.index') }}" class="btn btn-secondary">
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
                    <form method="POST" action="{{ route('admin.enrollments.store') }}">
                        @csrf

                        <!-- Student Selection -->
                        <div class="form-group">
                            <label for="student_id">Student <span class="text-danger">*</span></label>
                            <select class="form-control @error('student_id') is-invalid @enderror"
                                    id="student_id" name="student_id" required>
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
                                            <strong>Package</strong> (Multiple classes)
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
                                            {{ old('package_id') == $package->id ? 'selected' : '' }}>
                                        {{ $package->name }} - RM {{ number_format($package->price, 2) }}/month
                                        ({{ $package->duration_months }} months)
                                    </option>
                                @endforeach
                            </select>
                            @error('package_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted" id="package_classes"></small>
                        </div>

                        <!-- Class Selection -->
                        <div class="form-group" id="class_section" style="display: none;">
                            <label for="class_id">Class <span class="text-danger">*</span></label>
                            <select class="form-control @error('class_id') is-invalid @enderror"
                                    id="class_id" name="class_id">
                                <option value="">Select Class...</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}"
                                            data-fee="{{ $class->monthly_fee }}"
                                            {{ old('class_id') == $class->id ? 'selected' : '' }}>
                                        {{ $class->name }} ({{ $class->subject->name }}) -
                                        RM {{ number_format($class->monthly_fee, 2) }}/month
                                    </option>
                                @endforeach
                            </select>
                            @error('class_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

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
                                        <option value="">Select Day...</option>
                                        @for($i = 1; $i <= 28; $i++)
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

                        <!-- Monthly Fee (for single class) -->
                        <div class="form-group" id="monthly_fee_section" style="display: none;">
                            <label for="monthly_fee">Monthly Fee (RM) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control @error('monthly_fee') is-invalid @enderror"
                                   id="monthly_fee" name="monthly_fee" value="{{ old('monthly_fee') }}"
                                   placeholder="0.00">
                            @error('monthly_fee')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Status -->
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select class="form-control" id="status" name="status">
                                <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="trial" {{ old('status') == 'trial' ? 'selected' : '' }}>Trial</option>
                            </select>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Create Enrollment
                            </button>
                            <a href="{{ route('admin.enrollments.index') }}" class="btn btn-secondary">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Info Panel -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Information</h6>
                </div>
                <div class="card-body">
                    <h6 class="text-primary">Package Enrollment</h6>
                    <p class="small">
                        Select a package to automatically enroll the student in all classes included in that package.
                        The monthly fee will be the package price.
                    </p>

                    <h6 class="text-primary mt-3">Single Class Enrollment</h6>
                    <p class="small">
                        Select individual classes for students who want specific subjects only.
                        The monthly fee will be based on the class fee.
                    </p>

                    <h6 class="text-primary mt-3">Payment Cycle Day</h6>
                    <p class="small">
                        This is the day of each month when the monthly fee is due. Choose a consistent day
                        for easier payment tracking.
                    </p>

                    <h6 class="text-primary mt-3">Automatic Invoice</h6>
                    <p class="small">
                        A registration invoice will be automatically generated upon enrollment and sent to
                        the student's email and WhatsApp.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Toggle enrollment type
    $('input[name="enrollment_type"]').change(function() {
        if ($(this).val() === 'package') {
            $('#package_section').show();
            $('#class_section, #monthly_fee_section').hide();
            $('#package_id').prop('required', true);
            $('#class_id, #monthly_fee').prop('required', false);
        } else {
            $('#class_section, #monthly_fee_section').show();
            $('#package_section').hide();
            $('#class_id, #monthly_fee').prop('required', true);
            $('#package_id').prop('required', false);
        }
    });

    // Load package classes
    $('#package_id').change(function() {
        const packageId = $(this).val();
        if (packageId) {
            $.get(`/admin/enrollments/package/${packageId}`, function(data) {
                let classesText = `This package includes ${data.classes.length} classes: `;
                classesText += data.classes.map(c => c.name).join(', ');
                $('#package_classes').text(classesText);
            });
        } else {
            $('#package_classes').text('');
        }
    });

    // Auto-fill monthly fee when class is selected
    $('#class_id').change(function() {
        const selectedOption = $(this).find(':selected');
        const fee = selectedOption.data('fee');
        if (fee) {
            $('#monthly_fee').val(fee);
        }
    });

    // Trigger initial state
    $('input[name="enrollment_type"]:checked').trigger('change');
});
</script>
@endpush
