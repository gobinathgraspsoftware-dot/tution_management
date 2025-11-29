@extends('layouts.public')

@section('title', 'Student Registration Form')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="registration-card">
            <div class="registration-header">
                <i class="fas fa-user-graduate fa-3x mb-3"></i>
                <h2>Student Registration Form</h2>
                <p>Please fill in all required information accurately</p>
            </div>

            <div class="registration-body">
                <!-- Progress Steps -->
                <div class="progress-steps mb-4">
                    <div class="step active" id="step1-indicator">
                        <span class="step-number">1</span>
                        <span>Parent Info</span>
                    </div>
                    <div class="step-connector"></div>
                    <div class="step" id="step2-indicator">
                        <span class="step-number">2</span>
                        <span>Student Info</span>
                    </div>
                    <div class="step-connector"></div>
                    <div class="step" id="step3-indicator">
                        <span class="step-number">3</span>
                        <span>Additional Info</span>
                    </div>
                </div>

                <form action="{{ route('public.registration.student.submit') }}" method="POST" id="registrationForm">
                    @csrf

                    <!-- Step 1: Parent/Guardian Information -->
                    <div class="form-step" id="step1">
                        <div class="form-section">
                            <h5 class="section-title">
                                <i class="fas fa-user-friends"></i> Parent/Guardian Information
                            </h5>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="parent_name" class="form-label">
                                        Full Name <span class="required-asterisk">*</span>
                                    </label>
                                    <input type="text" class="form-control @error('parent_name') is-invalid @enderror"
                                           id="parent_name" name="parent_name" value="{{ old('parent_name') }}"
                                           placeholder="Enter parent/guardian name" required>
                                    @error('parent_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="relationship" class="form-label">
                                        Relationship <span class="required-asterisk">*</span>
                                    </label>
                                    <select class="form-select @error('relationship') is-invalid @enderror"
                                            id="relationship" name="relationship" required>
                                        <option value="">Select relationship</option>
                                        <option value="father" {{ old('relationship') == 'father' ? 'selected' : '' }}>Father</option>
                                        <option value="mother" {{ old('relationship') == 'mother' ? 'selected' : '' }}>Mother</option>
                                        <option value="guardian" {{ old('relationship') == 'guardian' ? 'selected' : '' }}>Guardian</option>
                                    </select>
                                    @error('relationship')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="parent_email" class="form-label">
                                        Email Address <span class="required-asterisk">*</span>
                                    </label>
                                    <input type="email" class="form-control @error('parent_email') is-invalid @enderror"
                                           id="parent_email" name="parent_email" value="{{ old('parent_email') }}"
                                           placeholder="parent@email.com" required>
                                    <div id="email-feedback" class="form-text"></div>
                                    @error('parent_email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="parent_phone" class="form-label">
                                        Phone Number <span class="required-asterisk">*</span>
                                    </label>
                                    <input type="tel" class="form-control @error('parent_phone') is-invalid @enderror"
                                           id="parent_phone" name="parent_phone" value="{{ old('parent_phone') }}"
                                           placeholder="01X-XXXXXXX" required>
                                    @error('parent_phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="parent_ic" class="form-label">IC Number</label>
                                    <input type="text" class="form-control @error('parent_ic') is-invalid @enderror"
                                           id="parent_ic" name="parent_ic" value="{{ old('parent_ic') }}"
                                           placeholder="XXXXXX-XX-XXXX">
                                    @error('parent_ic')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="parent_whatsapp" class="form-label">WhatsApp Number</label>
                                    <input type="tel" class="form-control @error('parent_whatsapp') is-invalid @enderror"
                                           id="parent_whatsapp" name="parent_whatsapp" value="{{ old('parent_whatsapp') }}"
                                           placeholder="Same as phone if blank">
                                    @error('parent_whatsapp')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="button" class="btn btn-primary" onclick="nextStep(2)">
                                Next <i class="fas fa-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Step 2: Student Information -->
                    <div class="form-step d-none" id="step2">
                        <div class="form-section">
                            <h5 class="section-title">
                                <i class="fas fa-user-graduate"></i> Student Information
                            </h5>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="student_name" class="form-label">
                                        Student Full Name <span class="required-asterisk">*</span>
                                    </label>
                                    <input type="text" class="form-control @error('student_name') is-invalid @enderror"
                                           id="student_name" name="student_name" value="{{ old('student_name') }}"
                                           placeholder="Enter student name" required>
                                    @error('student_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="student_ic" class="form-label">
                                        Student IC Number <span class="required-asterisk">*</span>
                                    </label>
                                    <input type="text" class="form-control @error('student_ic') is-invalid @enderror"
                                           id="student_ic" name="student_ic" value="{{ old('student_ic') }}"
                                           placeholder="XXXXXX-XX-XXXX" required>
                                    @error('student_ic')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="date_of_birth" class="form-label">
                                        Date of Birth <span class="required-asterisk">*</span>
                                    </label>
                                    <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror"
                                           id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth') }}"
                                           max="{{ date('Y-m-d') }}" required>
                                    @error('date_of_birth')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="gender" class="form-label">
                                        Gender <span class="required-asterisk">*</span>
                                    </label>
                                    <select class="form-select @error('gender') is-invalid @enderror"
                                            id="gender" name="gender" required>
                                        <option value="">Select gender</option>
                                        <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                                        <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                                    </select>
                                    @error('gender')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="grade_level" class="form-label">
                                        Grade Level <span class="required-asterisk">*</span>
                                    </label>
                                    <select class="form-select @error('grade_level') is-invalid @enderror"
                                            id="grade_level" name="grade_level" required>
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
                                    <label for="school_name" class="form-label">
                                        School Name <span class="required-asterisk">*</span>
                                    </label>
                                    <input type="text" class="form-control @error('school_name') is-invalid @enderror"
                                           id="school_name" name="school_name" value="{{ old('school_name') }}"
                                           placeholder="Enter school name" required>
                                    @error('school_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="student_phone" class="form-label">Student Phone (Optional)</label>
                                    <input type="tel" class="form-control @error('student_phone') is-invalid @enderror"
                                           id="student_phone" name="student_phone" value="{{ old('student_phone') }}"
                                           placeholder="If student has own phone">
                                    @error('student_phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-secondary" onclick="prevStep(1)">
                                <i class="fas fa-arrow-left me-2"></i> Back
                            </button>
                            <button type="button" class="btn btn-primary" onclick="nextStep(3)">
                                Next <i class="fas fa-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Step 3: Address & Additional Information -->
                    <div class="form-step d-none" id="step3">
                        <div class="form-section">
                            <h5 class="section-title">
                                <i class="fas fa-map-marker-alt"></i> Address Information
                            </h5>

                            <div class="mb-3">
                                <label for="address" class="form-label">
                                    Full Address <span class="required-asterisk">*</span>
                                </label>
                                <textarea class="form-control @error('address') is-invalid @enderror"
                                          id="address" name="address" rows="2"
                                          placeholder="Street address" required>{{ old('address') }}</textarea>
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="city" class="form-label">City</label>
                                    <input type="text" class="form-control @error('city') is-invalid @enderror"
                                           id="city" name="city" value="{{ old('city') }}"
                                           placeholder="City">
                                    @error('city')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="state" class="form-label">State</label>
                                    <select class="form-select @error('state') is-invalid @enderror" id="state" name="state">
                                        <option value="">Select state</option>
                                        <option value="Johor" {{ old('state') == 'Johor' ? 'selected' : '' }}>Johor</option>
                                        <option value="Kedah" {{ old('state') == 'Kedah' ? 'selected' : '' }}>Kedah</option>
                                        <option value="Kelantan" {{ old('state') == 'Kelantan' ? 'selected' : '' }}>Kelantan</option>
                                        <option value="Melaka" {{ old('state') == 'Melaka' ? 'selected' : '' }}>Melaka</option>
                                        <option value="Negeri Sembilan" {{ old('state') == 'Negeri Sembilan' ? 'selected' : '' }}>Negeri Sembilan</option>
                                        <option value="Pahang" {{ old('state') == 'Pahang' ? 'selected' : '' }}>Pahang</option>
                                        <option value="Perak" {{ old('state') == 'Perak' ? 'selected' : '' }}>Perak</option>
                                        <option value="Perlis" {{ old('state') == 'Perlis' ? 'selected' : '' }}>Perlis</option>
                                        <option value="Pulau Pinang" {{ old('state') == 'Pulau Pinang' ? 'selected' : '' }}>Pulau Pinang</option>
                                        <option value="Sabah" {{ old('state') == 'Sabah' ? 'selected' : '' }}>Sabah</option>
                                        <option value="Sarawak" {{ old('state') == 'Sarawak' ? 'selected' : '' }}>Sarawak</option>
                                        <option value="Selangor" {{ old('state') == 'Selangor' ? 'selected' : '' }}>Selangor</option>
                                        <option value="Terengganu" {{ old('state') == 'Terengganu' ? 'selected' : '' }}>Terengganu</option>
                                        <option value="Kuala Lumpur" {{ old('state') == 'Kuala Lumpur' ? 'selected' : '' }}>Kuala Lumpur</option>
                                        <option value="Labuan" {{ old('state') == 'Labuan' ? 'selected' : '' }}>Labuan</option>
                                        <option value="Putrajaya" {{ old('state') == 'Putrajaya' ? 'selected' : '' }}>Putrajaya</option>
                                    </select>
                                    @error('state')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="postcode" class="form-label">Postcode</label>
                                    <input type="text" class="form-control @error('postcode') is-invalid @enderror"
                                           id="postcode" name="postcode" value="{{ old('postcode') }}"
                                           placeholder="XXXXX" maxlength="5">
                                    @error('postcode')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h5 class="section-title">
                                <i class="fas fa-notes-medical"></i> Additional Information
                            </h5>

                            <div class="mb-3">
                                <label for="medical_conditions" class="form-label">Medical Conditions (if any)</label>
                                <textarea class="form-control @error('medical_conditions') is-invalid @enderror"
                                          id="medical_conditions" name="medical_conditions" rows="2"
                                          placeholder="Any allergies, health conditions, or special needs">{{ old('medical_conditions') }}</textarea>
                                @error('medical_conditions')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="referral_code" class="form-label">
                                        <i class="fas fa-gift text-warning me-1"></i> Referral Code (Optional)
                                    </label>
                                    <input type="text" class="form-control @error('referral_code') is-invalid @enderror"
                                           id="referral_code" name="referral_code"
                                           value="{{ old('referral_code', $referralCode ?? '') }}"
                                           placeholder="Enter referral code for RM50 discount">
                                    <div id="referral-feedback" class="referral-validation"></div>
                                    @error('referral_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="notes" class="form-label">Additional Notes</label>
                                    <input type="text" class="form-control @error('notes') is-invalid @enderror"
                                           id="notes" name="notes" value="{{ old('notes') }}"
                                           placeholder="Any additional information">
                                    @error('notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <div class="form-check mb-3">
                                <input class="form-check-input @error('terms_accepted') is-invalid @enderror"
                                       type="checkbox" id="terms_accepted" name="terms_accepted" value="1" required>
                                <label class="form-check-label" for="terms_accepted">
                                    I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Terms and Conditions</a>
                                    and <a href="#" data-bs-toggle="modal" data-bs-target="#privacyModal">Privacy Policy</a>
                                    <span class="required-asterisk">*</span>
                                </label>
                                @error('terms_accepted')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-secondary" onclick="prevStep(2)">
                                <i class="fas fa-arrow-left me-2"></i> Back
                            </button>
                            <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                                <i class="fas fa-paper-plane me-2"></i> Submit Registration
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Terms Modal -->
<div class="modal fade" id="termsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Terms and Conditions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6>1. Registration</h6>
                <p>By registering, you agree to provide accurate and complete information about yourself and your child.</p>

                <h6>2. Payment</h6>
                <p>Monthly fees are due by the 10th of each month. Late payments may incur additional charges.</p>

                <h6>3. Attendance</h6>
                <p>Regular attendance is encouraged. Parents will be notified of absences.</p>

                <h6>4. Cancellation</h6>
                <p>A 30-day notice is required for cancellation of enrollment.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">I Understand</button>
            </div>
        </div>
    </div>
</div>

<!-- Privacy Modal -->
<div class="modal fade" id="privacyModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Privacy Policy</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Your privacy is important to us. We collect and use personal information only for educational purposes.</p>
                <p>We do not share your information with third parties without your consent.</p>
                <p>You may request to view, update, or delete your personal information at any time.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">I Understand</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let currentStep = 1;

    function nextStep(step) {
        // Validate current step before moving
        if (!validateStep(currentStep)) {
            return;
        }

        // Hide current step
        $('#step' + currentStep).addClass('d-none');
        $('#step' + currentStep + '-indicator').removeClass('active').addClass('completed');

        // Show next step
        currentStep = step;
        $('#step' + currentStep).removeClass('d-none');
        $('#step' + currentStep + '-indicator').addClass('active');

        // Scroll to top
        window.scrollTo(0, 0);
    }

    function prevStep(step) {
        // Hide current step
        $('#step' + currentStep).addClass('d-none');
        $('#step' + currentStep + '-indicator').removeClass('active');

        // Show previous step
        currentStep = step;
        $('#step' + currentStep).removeClass('d-none');
        $('#step' + currentStep + '-indicator').removeClass('completed').addClass('active');

        // Scroll to top
        window.scrollTo(0, 0);
    }

    function validateStep(step) {
        let isValid = true;
        $('#step' + step + ' [required]').each(function() {
            if (!$(this).val()) {
                $(this).addClass('is-invalid');
                isValid = false;
            } else {
                $(this).removeClass('is-invalid');
            }
        });

        if (!isValid) {
            alert('Please fill in all required fields.');
        }

        return isValid;
    }

    $(document).ready(function() {
        // Check parent email
        let emailTimeout;
        $('#parent_email').on('blur keyup', function() {
            clearTimeout(emailTimeout);
            let email = $(this).val();

            if (email.length > 5) {
                emailTimeout = setTimeout(function() {
                    $.get('{{ route("public.registration.check-email") }}', { email: email, type: 'parent' })
                        .done(function(response) {
                            if (response.exists) {
                                $('#email-feedback')
                                    .html('<i class="fas fa-info-circle"></i> ' + response.message)
                                    .removeClass('text-danger')
                                    .addClass(response.is_parent ? 'text-info' : 'text-danger');
                            } else {
                                $('#email-feedback').html('');
                            }
                        });
                }, 500);
            }
        });

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
                                    .html('<i class="fas fa-check-circle"></i> ' + response.message)
                                    .removeClass('referral-invalid')
                                    .addClass('referral-valid');
                            } else {
                                $('#referral-feedback')
                                    .html('<i class="fas fa-times-circle"></i> ' + response.message)
                                    .removeClass('referral-valid')
                                    .addClass('referral-invalid');
                            }
                        });
                }, 500);
            } else {
                $('#referral-feedback').html('');
            }
        });

        // Form submission
        $('#registrationForm').on('submit', function(e) {
            $('#submitBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i> Submitting...');
        });

        // Trigger referral validation if pre-filled
        if ($('#referral_code').val()) {
            $('#referral_code').trigger('blur');
        }
    });
</script>
@endpush
