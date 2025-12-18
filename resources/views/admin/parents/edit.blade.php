@extends('layouts.app')

@section('title', 'Edit Parent')
@section('page-title', 'Edit Parent')

@section('content')
<div class="page-header">
    <h1>
        <i class="fas fa-user-edit me-2"></i> Edit Parent
    </h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.parents.index') }}">Parents</a></li>
            <li class="breadcrumb-item active">Edit {{ $parent->user->name }}</li>
        </ol>
    </nav>
</div>

<!-- Alert Messages -->
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<form action="{{ route('admin.parents.update', $parent) }}" method="POST" id="parentForm">
    @csrf
    @method('PUT')

    <div class="row">
        <!-- Account Information -->
        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-user me-2"></i>Account Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control text-uppercase @error('name') is-invalid @enderror"
                               id="name" name="name" value="{{ old('name', $parent->user->name) }}" required>
                        <small class="text-muted">Name will be automatically converted to uppercase</small>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                               id="email" name="email" value="{{ old('email', $parent->user->email) }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="country_code" class="form-label">Country <span class="text-danger">*</span></label>
                                <select class="form-select @error('country_code') is-invalid @enderror"
                                        id="country_code" name="country_code" required>
                                    @foreach($countries as $country)
                                        <option value="{{ $country['code'] }}"
                                                {{ old('country_code', $phoneData['country_code']) == $country['code'] ? 'selected' : '' }}>
                                            {{ $country['flag'] }} {{ $country['code'] }} ({{ $country['name'] }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('country_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                       id="phone" name="phone" value="{{ old('phone', $phoneData['number']) }}"
                                       placeholder="e.g., 123456789 (without leading zero)" required>
                                <small class="text-muted">Enter phone number without country code</small>
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="ic_number" class="form-label">IC Number <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('ic_number') is-invalid @enderror"
                               id="ic_number" name="ic_number" value="{{ old('ic_number', $parent->ic_number) }}"
                               placeholder="e.g., 001005-10-1519" maxlength="14" required>
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i> Format: XXXXXX-XX-XXXX (12 digits)
                        </small>
                        @error('ic_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control @error('password') is-invalid @enderror"
                                           id="password" name="password">
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password')">
                                        <i class="fas fa-eye" id="password-icon"></i>
                                    </button>
                                </div>
                                <small class="text-muted">
                                    <i class="fas fa-info-circle"></i> Leave blank to keep current. Minimum 8 characters required
                                </small>
                                @error('password')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label">Confirm Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control"
                                           id="password_confirmation" name="password_confirmation">
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password_confirmation')">
                                        <i class="fas fa-eye" id="password_confirmation-icon"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label">Account Status <span class="text-danger">*</span></label>
                        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                            <option value="active" {{ old('status', $parent->user->status) == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $parent->user->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Emergency Contact -->
            <div class="card mb-4">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="fas fa-ambulance me-2"></i>Emergency Contact</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="emergency_contact" class="form-label">Emergency Contact Name</label>
                        <input type="text" class="form-control text-uppercase @error('emergency_contact') is-invalid @enderror"
                               id="emergency_contact" name="emergency_contact" value="{{ old('emergency_contact', $parent->emergency_contact) }}"
                               placeholder="e.g., SPOUSE OR FAMILY MEMBER">
                        <small class="text-muted">Name will be automatically converted to uppercase</small>
                        @error('emergency_contact')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="emergency_country_code" class="form-label">Country</label>
                                <select class="form-select @error('emergency_country_code') is-invalid @enderror"
                                        id="emergency_country_code" name="emergency_country_code">
                                    @foreach($countries as $country)
                                        <option value="{{ $country['code'] }}"
                                                {{ old('emergency_country_code', $emergencyData['country_code']) == $country['code'] ? 'selected' : '' }}>
                                            {{ $country['flag'] }} {{ $country['code'] }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('emergency_country_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="emergency_phone" class="form-label">Emergency Contact Phone</label>
                                <input type="text" class="form-control @error('emergency_phone') is-invalid @enderror"
                                       id="emergency_phone" name="emergency_phone" value="{{ old('emergency_phone', $emergencyData['number']) }}"
                                       placeholder="e.g., 123456789">
                                @error('emergency_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Personal & Address Information -->
        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-id-card me-2"></i>Personal Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="relationship" class="form-label">Relationship <span class="text-danger">*</span></label>
                                <select class="form-select @error('relationship') is-invalid @enderror"
                                        id="relationship" name="relationship" required>
                                    <option value="">-- Select --</option>
                                    <option value="father" {{ old('relationship', $parent->relationship) == 'father' ? 'selected' : '' }}>Father</option>
                                    <option value="mother" {{ old('relationship', $parent->relationship) == 'mother' ? 'selected' : '' }}>Mother</option>
                                    <option value="guardian" {{ old('relationship', $parent->relationship) == 'guardian' ? 'selected' : '' }}>Guardian</option>
                                    <option value="other" {{ old('relationship', $parent->relationship) == 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('relationship')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="relationship_description" class="form-label">Description (if Other)</label>
                                <input type="text" class="form-control @error('relationship_description') is-invalid @enderror"
                                       id="relationship_description" name="relationship_description"
                                       value="{{ old('relationship_description', $parent->relationship_description) }}"
                                       placeholder="e.g., Uncle, Aunt, Grandparent">
                                <small class="text-muted">Optional: Specify relationship type</small>
                                @error('relationship_description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="occupation" class="form-label">Occupation</label>
                        <input type="text" class="form-control text-uppercase @error('occupation') is-invalid @enderror"
                               id="occupation" name="occupation" value="{{ old('occupation', $parent->occupation) }}"
                               placeholder="e.g., ENGINEER">
                        @error('occupation')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Address Information -->
            <div class="card mb-4">
                <div class="card-header bg-warning">
                    <h5 class="mb-0"><i class="fas fa-map-marker-alt me-2"></i>Address Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="postcode" class="form-label">Postcode <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('postcode') is-invalid @enderror"
                               id="postcode" name="postcode" value="{{ old('postcode', $parent->postcode) }}"
                               placeholder="e.g., 50000" required>
                        <small class="text-muted">City and state will be auto-filled</small>
                        @error('postcode')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="city" class="form-label">City <span class="text-danger">*</span></label>
                                <select class="form-select text-uppercase @error('city') is-invalid @enderror"
                                       id="city" name="city" required>
                                    <option value="{{ old('city', $parent->city) }}">{{ old('city', $parent->city) }}</option>
                                </select>
                                @error('city')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="state" class="form-label">State <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('state') is-invalid @enderror"
                                       id="state" name="state" value="{{ old('state', $parent->state) }}" readonly required>
                                @error('state')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="address" class="form-label">Address <span class="text-danger">*</span></label>
                        <textarea class="form-control text-uppercase @error('address') is-invalid @enderror"
                                  id="address" name="address" rows="2" required>{{ old('address', $parent->address) }}</textarea>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Notification Preferences -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-bell me-2"></i>Notification Preferences</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">Configure how the parent receives notifications:</p>

                    @php
                        $emailEnabled = old('email_notifications', $parent->notification_preference['email'] ?? true);
                    @endphp

                    <div class="form-check">
                                            <!-- WhatsApp Notifications -->
                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input" id="whatsapp_notifications"
                               name="whatsapp_notifications" value="1"
                               {{ old('whatsapp_notifications', $parent->whatsapp_notification_enabled) ? 'checked' : '' }}>
                        <label class="form-check-label" for="whatsapp_notifications">
                            <i class="fab fa-whatsapp text-success me-1"></i> WhatsApp Notifications
                        </label>
                        <br><small class="text-muted">Receive updates and notifications via WhatsApp</small>
                    </div>

<input type="checkbox" class="form-check-input" id="email_notifications"
                               name="email_notifications" value="1"
                               {{ $emailEnabled ? 'checked' : '' }}>
                        <label class="form-check-label" for="email_notifications">
                            <i class="fas fa-envelope text-primary me-1"></i> Email Notifications
                        </label>
                        <br><small class="text-muted">Receive updates and notifications via email</small>
                    </div>
                </div>
            </div>

            <!-- Link Students -->
            @if($availableStudents->count() > 0)
            <div class="card mb-4">
                <div class="card-header" style="background-color: #6f42c1; color: white;">
                    <h5 class="mb-0"><i class="fas fa-link me-2"></i>Linked Students</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">Select students to link to this parent:</p>

                    <div class="student-list" style="max-height: 200px; overflow-y: auto;">
                        @foreach($availableStudents as $student)
                            @php
                                $isLinked = $parent->students->contains($student->id);
                            @endphp
                            <div class="form-check mb-2">
                                <input type="checkbox" class="form-check-input"
                                       id="student_{{ $student->id }}"
                                       name="link_students[]"
                                       value="{{ $student->id }}"
                                       {{ in_array($student->id, old('link_students', $parent->students->pluck('id')->toArray())) ? 'checked' : '' }}>
                                <label class="form-check-label" for="student_{{ $student->id }}">
                                    <strong>{{ $student->user->name }}</strong>
                                    <small class="text-muted">({{ $student->student_id }} - {{ $student->school_name }})</small>
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Form Actions -->
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between">
                <a href="{{ route('admin.parents.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Update Parent
                </button>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
function togglePassword(fieldId) {
    var field = document.getElementById(fieldId);
    var icon = document.getElementById(fieldId + '-icon');

    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

$(document).ready(function() {
    // Convert name fields to uppercase automatically
    $('#name, #emergency_contact, #occupation, #address').on('input', function() {
        var start = this.selectionStart;
        var end = this.selectionEnd;
        this.value = this.value.toUpperCase();
        this.setSelectionRange(start, end);
    });

    // Postcode auto-fill functionality
    $('#postcode').on('blur', function() {
        var postcode = $(this).val();

        if (postcode.length >= 5) {
            $.ajax({
                url: '{{ route("admin.parents.postcode-data") }}',
                method: 'GET',
                data: { postcode: postcode },
                success: function(response) {
                    $('#state').val(response.state);

                    // Populate city dropdown
                    var citySelect = $('#city');
                    var currentCity = citySelect.val();
                    citySelect.empty();
                    citySelect.append('<option value="">-- Select City --</option>');

                    response.cities.forEach(function(city) {
                        citySelect.append('<option value="' + city.toUpperCase() + '">' + city + '</option>');
                    });

                    // Reselect current city or select first city
                    if (currentCity && citySelect.find('option[value="' + currentCity + '"]').length) {
                        citySelect.val(currentCity);
                    } else if (response.city) {
                        citySelect.val(response.city.toUpperCase());
                    }
                },
                error: function() {
                    // If postcode not found, keep current values
                }
            });
        }
    });

    // Show/hide relationship description based on selection
    $('#relationship').on('change', function() {
        if ($(this).val() === 'other') {
            $('#relationship_description').prop('required', true);
            $('#relationship_description').closest('.mb-3').find('label').html('Description (if Other) <span class="text-danger">*</span>');
        } else {
            $('#relationship_description').prop('required', false);
            $('#relationship_description').closest('.mb-3').find('label').html('Description (if Other)');
        }
    }).trigger('change');

    // Phone number validation - only allow numbers
    $('#phone, #emergency_phone').on('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    // Postcode validation - only allow numbers
    $('#postcode').on('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    // IC Number validation - only allow numbers and dashes
    $('#ic_number').on('input', function() {
        // IC Number Auto-Formatting: XXXXXX-XX-XXXX
        let value = $(this).val().replace(/[^0-9]/g, ''); // Remove non-digits
        let formatted = '';

        if (value.length > 0) {
            formatted = value.substring(0, 6); // First 6 digits
        }
        if (value.length > 6) {
            formatted += '-' + value.substring(6, 8); // Next 2 digits
        }
        if (value.length > 8) {
            formatted += '-' + value.substring(8, 12); // Last 4 digits
        }

        $(this).val(formatted);

        // Real-time validation feedback
        let cleaned = value;
        if (cleaned.length === 0) {
            $(this).removeClass('is-valid is-invalid');
        } else if (cleaned.length === 12) {
            $(this).removeClass('is-invalid').addClass('is-valid');
        } else {
            $(this).removeClass('is-valid').addClass('is-invalid');
        }
    });

    // IC Number - paste handler
    $('#ic_number').on('paste', function(e) {
        e.preventDefault();
        let pastedData = e.originalEvent.clipboardData.getData('text');
        let cleaned = pastedData.replace(/[^0-9]/g, '');
        $(this).val(cleaned).trigger('input');
    });

    // Password strength indicator (for new password)
    $('#password').on('input', function() {
        let password = $(this).val();
        let strength = 0;
        let $indicator = $(this).closest('.mb-3').find('.password-strength');

        if ($indicator.length === 0 && password.length > 0) {
            $(this).closest('.input-group').after('<div class="password-strength mt-1"></div>');
            $indicator = $(this).closest('.mb-3').find('.password-strength');
        }

        if (password.length === 0) {
            $indicator.remove();
            return;
        }

        // Calculate strength
        if (password.length >= 8) strength++;
        if (password.length >= 12) strength++;
        if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
        if (/[0-9]/.test(password)) strength++;
        if (/[^a-zA-Z0-9]/.test(password)) strength++;

        // Display strength
        let strengthText = '';
        let strengthClass = '';

        if (strength <= 2) {
            strengthText = 'Weak';
            strengthClass = 'text-danger';
        } else if (strength <= 3) {
            strengthText = 'Medium';
            strengthClass = 'text-warning';
        } else {
            strengthText = 'Strong';
            strengthClass = 'text-success';
        }

        $indicator.html('<small class="' + strengthClass + '"><i class="fas fa-shield-alt me-1"></i>Password strength: ' + strengthText + '</small>');
    });

    // Password confirmation matching
    $('#password_confirmation').on('input', function() {
        let password = $('#password').val();
        let confirmation = $(this).val();

        if (confirmation.length === 0) {
            $(this).removeClass('is-valid is-invalid');
            return;
        }

        if (password === confirmation) {
            $(this).removeClass('is-invalid').addClass('is-valid');
        } else {
            $(this).removeClass('is-valid').addClass('is-invalid');
        }
    });

    // Initialize IC formatting on page load
    $(document).ready(function() {
        let icValue = $('#ic_number').val();
        if (icValue) {
            $('#ic_number').trigger('input');
        }
    });

    // Form validation

    // Auto-fill WhatsApp number from phone if empty
    $('#phone').on('blur', function() {
        var phone = $(this).val();
        var whatsapp = $('#whatsapp_number');
        var countryCode = $('#country_code').val();
        var whatsappCountryCode = $('#whatsapp_country_code');

        if (phone && !whatsapp.val()) {
            whatsapp.val(phone);
            whatsappCountryCode.val(countryCode);
        }
    });

    // Phone number validation - only allow numbers
    $('#phone, #whatsapp_number, #emergency_phone').on('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    // Form submission - combine country codes with numbers
    $('#parentForm').on('submit', function(e) {
        // Existing validation...

        // Combine WhatsApp country code with number
        var whatsappCountryCode = $('#whatsapp_country_code').val();
        var whatsappNumber = $('#whatsapp_number').val();
        if (whatsappNumber) {
            $('#whatsapp_number').val(whatsappCountryCode + whatsappNumber);
        }

    });
});
</script>
@endpush

@push('styles')
<style>
    .text-uppercase {
        text-transform: uppercase !important;
    }

    .student-list {
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        padding: 1rem;
        background-color: #f8f9fa;
    }

    .student-list .form-check {
        padding: 0.5rem;
        background: white;
        border-radius: 0.25rem;
        border: 1px solid #e9ecef;
    }

    .student-list .form-check:hover {
        background-color: #e9ecef;
    }

    /* Password strength indicator */
    #password {
        font-family: monospace;
    }
</style>
@endpush
