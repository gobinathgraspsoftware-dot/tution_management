@extends('layouts.app')

@section('title', 'Register Student')
@section('page-title', 'Register Student')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="fas fa-user-plus me-2"></i> Register New Student</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('staff.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Register Student</li>
            </ol>
        </nav>
    </div>
    <div>
        <a href="{{ route('staff.registration.pending') }}" class="btn btn-outline-primary">
            <i class="fas fa-clock me-1"></i> View Pending
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-user-graduate me-2"></i> Student Registration Form (Offline)
            </div>
            <div class="card-body">
                <form action="{{ route('staff.registration.store-student') }}" method="POST" id="studentRegistrationForm">
                    @csrf

                    <!-- Parent Selection -->
                    <h5 class="mb-3 text-primary"><i class="fas fa-user-friends me-2"></i> Parent/Guardian</h5>

                    <div class="mb-3">
                        <label for="parent_id" class="form-label">Select Parent/Guardian <span class="text-danger">*</span></label>
                        <select class="form-select @error('parent_id') is-invalid @enderror"
                                id="parent_id" name="parent_id" required>
                            <option value="">-- Search or select parent --</option>
                            @foreach($parents as $parent)
                                <option value="{{ $parent->id }}"
                                        data-email="{{ $parent->user->email }}"
                                        data-phone="{{ $parent->user->phone }}"
                                        data-address="{{ $parent->address }}"
                                        {{ old('parent_id') == $parent->id ? 'selected' : '' }}>
                                    {{ $parent->user->name }} ({{ $parent->ic_number }}) - {{ $parent->user->phone }}
                                </option>
                            @endforeach
                        </select>
                        @error('parent_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="mt-2">
                            <a href="{{ route('staff.registration.create-parent') }}" class="btn btn-sm btn-outline-success">
                                <i class="fas fa-plus me-1"></i> Register New Parent
                            </a>
                        </div>
                    </div>

                    <!-- Parent Info Display -->
                    <div id="parentInfo" class="alert alert-info d-none mb-4">
                        <div class="row">
                            <div class="col-md-4">
                                <small class="text-muted">Email:</small>
                                <div id="parentEmail">-</div>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted">Phone:</small>
                                <div id="parentPhone">-</div>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted">Address:</small>
                                <div id="parentAddress">-</div>
                            </div>
                        </div>
                    </div>

                    <!-- Student Information -->
                    <h5 class="mb-3 text-primary"><i class="fas fa-user-graduate me-2"></i> Student Information</h5>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Student Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name') }}"
                                   placeholder="Enter student name" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="ic_number" class="form-label">IC Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('ic_number') is-invalid @enderror"
                                   id="ic_number" name="ic_number" value="{{ old('ic_number') }}"
                                   placeholder="XXXXXX-XX-XXXX" required>
                            @error('ic_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                   id="email" name="email" value="{{ old('email') }}"
                                   placeholder="student@email.com" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control @error('phone') is-invalid @enderror"
                                   id="phone" name="phone" value="{{ old('phone') }}"
                                   placeholder="Student's phone (optional)">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="date_of_birth" class="form-label">Date of Birth <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror"
                                   id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth') }}"
                                   max="{{ date('Y-m-d') }}" required>
                            @error('date_of_birth')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="gender" class="form-label">Gender <span class="text-danger">*</span></label>
                            <select class="form-select @error('gender') is-invalid @enderror" id="gender" name="gender" required>
                                <option value="">Select gender</option>
                                <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                                <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                            </select>
                            @error('gender')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="grade_level" class="form-label">Grade Level <span class="text-danger">*</span></label>
                            <select class="form-select @error('grade_level') is-invalid @enderror" id="grade_level" name="grade_level" required>
                                <option value="">Select grade</option>
                                @foreach($gradeLevels as $value => $label)
                                    <option value="{{ $value }}" {{ old('grade_level') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('grade_level')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="school_name" class="form-label">School Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('school_name') is-invalid @enderror"
                                   id="school_name" name="school_name" value="{{ old('school_name') }}"
                                   placeholder="Enter school name" required>
                            @error('school_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="referral_code" class="form-label">
                                <i class="fas fa-gift text-warning me-1"></i> Referral Code
                            </label>
                            <input type="text" class="form-control @error('referral_code') is-invalid @enderror"
                                   id="referral_code" name="referral_code" value="{{ old('referral_code') }}"
                                   placeholder="Optional - RM50 discount">
                            <div id="referral-feedback" class="form-text"></div>
                            @error('referral_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control @error('address') is-invalid @enderror"
                                  id="address" name="address" rows="2"
                                  placeholder="Student's address (uses parent's address if blank)">{{ old('address') }}</textarea>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="medical_conditions" class="form-label">Medical Conditions</label>
                        <textarea class="form-control @error('medical_conditions') is-invalid @enderror"
                                  id="medical_conditions" name="medical_conditions" rows="2"
                                  placeholder="Any allergies, health conditions, or special needs">{{ old('medical_conditions') }}</textarea>
                        @error('medical_conditions')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Internal Notes</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror"
                                  id="notes" name="notes" rows="2"
                                  placeholder="Notes for admin (not visible to parent)">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Staff Options -->
                    <div class="card bg-light mb-3">
                        <div class="card-body">
                            <h6><i class="fas fa-cog me-2"></i> Staff Options</h6>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="auto_approve" name="auto_approve" value="1"
                                       {{ old('auto_approve') ? 'checked' : '' }}>
                                <label class="form-check-label" for="auto_approve">
                                    <strong>Auto-approve and activate student</strong>
                                    <br>
                                    <small class="text-muted">Skip pending approval queue and activate account immediately</small>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('staff.dashboard') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Back
                        </a>
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <i class="fas fa-save me-1"></i> Register Student
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Quick Stats -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-chart-bar me-2"></i> Today's Stats
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span>Pending Approvals:</span>
                    <span class="badge bg-warning">{{ \App\Models\Student::where('approval_status', 'pending')->count() }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Today's Registrations:</span>
                    <span class="badge bg-primary">{{ \App\Models\Student::whereDate('registration_date', today())->count() }}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Total Students:</span>
                    <span class="badge bg-success">{{ \App\Models\Student::count() }}</span>
                </div>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-link me-2"></i> Quick Links
            </div>
            <div class="card-body">
                <a href="{{ route('staff.registration.create-parent') }}" class="btn btn-outline-primary btn-sm w-100 mb-2">
                    <i class="fas fa-user-plus me-1"></i> Register New Parent
                </a>
                <a href="{{ route('staff.registration.pending') }}" class="btn btn-outline-warning btn-sm w-100 mb-2">
                    <i class="fas fa-clock me-1"></i> View Pending Approvals
                </a>
                <a href="#" class="btn btn-outline-info btn-sm w-100">
                    <i class="fas fa-search me-1"></i> Search Students
                </a>
            </div>
        </div>

        <!-- Help -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-info-circle me-2"></i> Help
            </div>
            <div class="card-body small">
                <p><strong>Registration Process:</strong></p>
                <ol class="ps-3 mb-0">
                    <li>Select or create parent account</li>
                    <li>Fill in student details</li>
                    <li>Choose auto-approve if needed</li>
                    <li>Submit registration</li>
                    <li>Provide temp password to parent</li>
                </ol>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        // Initialize Select2 for parent dropdown
        $('#parent_id').select2({
            theme: 'bootstrap-5',
            placeholder: 'Search parent by name, IC, or phone...',
            allowClear: true
        });

        // Show parent info when selected
        $('#parent_id').on('change', function() {
            var selected = $(this).find(':selected');
            if (selected.val()) {
                $('#parentEmail').text(selected.data('email'));
                $('#parentPhone').text(selected.data('phone'));
                $('#parentAddress').text(selected.data('address') || 'Not provided');
                $('#parentInfo').removeClass('d-none');
            } else {
                $('#parentInfo').addClass('d-none');
            }
        });

        // Trigger change if pre-selected
        if ($('#parent_id').val()) {
            $('#parent_id').trigger('change');
        }

        // Validate referral code
        let referralTimeout;
        $('#referral_code').on('blur keyup', function() {
            clearTimeout(referralTimeout);
            let code = $(this).val();

            if (code.length >= 6) {
                referralTimeout = setTimeout(function() {
                    $.get('{{ route("public.registration.validate-referral") }}', { code: code })
                        .done(function(response) {
                            if (response.valid) {
                                $('#referral-feedback')
                                    .html('<i class="fas fa-check-circle text-success"></i> ' + response.message)
                                    .removeClass('text-danger').addClass('text-success');
                            } else {
                                $('#referral-feedback')
                                    .html('<i class="fas fa-times-circle text-danger"></i> ' + response.message)
                                    .removeClass('text-success').addClass('text-danger');
                            }
                        });
                }, 500);
            } else {
                $('#referral-feedback').html('');
            }
        });

        // Auto-generate email from name
        $('#name').on('blur', function() {
            if (!$('#email').val() && $(this).val()) {
                var name = $(this).val().toLowerCase().replace(/\s+/g, '.');
                var randomStr = Math.random().toString(36).substring(2, 6);
                $('#email').val(name + '.' + randomStr + '@student.arenamatriks.edu.my');
            }
        });

        // Form submission
        $('#studentRegistrationForm').on('submit', function() {
            $('#submitBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Registering...');
        });
    });
</script>
@endpush
