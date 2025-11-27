@extends('layouts.guest')

@section('title', 'Register')
@section('header-subtitle', 'Create your parent account')

@section('content')
<form action="{{ route('register') }}" method="POST" id="registerForm">
    @csrf

    <h5 class="mb-3 text-center">Personal Information</h5>

    <div class="mb-3">
        <label for="name" class="form-label">Full Name *</label>
        <div class="input-group">
            <span class="input-group-text">
                <i class="fas fa-user text-muted"></i>
            </span>
            <input
                type="text"
                class="form-control @error('name') is-invalid @enderror"
                id="name"
                name="name"
                value="{{ old('name') }}"
                placeholder="Enter your full name"
                required
                autofocus
            >
        </div>
        @error('name')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="email" class="form-label">Email Address *</label>
            <div class="input-group">
                <span class="input-group-text">
                    <i class="fas fa-envelope text-muted"></i>
                </span>
                <input
                    type="email"
                    class="form-control @error('email') is-invalid @enderror"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="your@email.com"
                    required
                >
            </div>
            @error('email')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6 mb-3">
            <label for="phone" class="form-label">Phone Number *</label>
            <div class="input-group">
                <span class="input-group-text">
                    <i class="fas fa-phone text-muted"></i>
                </span>
                <input
                    type="tel"
                    class="form-control @error('phone') is-invalid @enderror"
                    id="phone"
                    name="phone"
                    value="{{ old('phone') }}"
                    placeholder="01X-XXXXXXX"
                    required
                >
            </div>
            @error('phone')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="ic_number" class="form-label">IC Number</label>
            <input
                type="text"
                class="form-control @error('ic_number') is-invalid @enderror"
                id="ic_number"
                name="ic_number"
                value="{{ old('ic_number') }}"
                placeholder="XXXXXX-XX-XXXX"
            >
            @error('ic_number')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6 mb-3">
            <label for="relationship" class="form-label">Relationship *</label>
            <select
                class="form-select @error('relationship') is-invalid @enderror"
                id="relationship"
                name="relationship"
                required
            >
                <option value="">Select...</option>
                <option value="father" {{ old('relationship') == 'father' ? 'selected' : '' }}>Father</option>
                <option value="mother" {{ old('relationship') == 'mother' ? 'selected' : '' }}>Mother</option>
                <option value="guardian" {{ old('relationship') == 'guardian' ? 'selected' : '' }}>Guardian</option>
                <option value="other" {{ old('relationship') == 'other' ? 'selected' : '' }}>Other</option>
            </select>
            @error('relationship')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="mb-3">
        <label for="occupation" class="form-label">Occupation</label>
        <input
            type="text"
            class="form-control @error('occupation') is-invalid @enderror"
            id="occupation"
            name="occupation"
            value="{{ old('occupation') }}"
            placeholder="Your occupation"
        >
        @error('occupation')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="address" class="form-label">Address</label>
        <textarea
            class="form-control @error('address') is-invalid @enderror"
            id="address"
            name="address"
            rows="2"
            placeholder="Street address"
        >{{ old('address') }}</textarea>
        @error('address')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>

    <div class="row">
        <div class="col-md-4 mb-3">
            <label for="city" class="form-label">City</label>
            <input
                type="text"
                class="form-control @error('city') is-invalid @enderror"
                id="city"
                name="city"
                value="{{ old('city') }}"
            >
            @error('city')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4 mb-3">
            <label for="state" class="form-label">State</label>
            <input
                type="text"
                class="form-control @error('state') is-invalid @enderror"
                id="state"
                name="state"
                value="{{ old('state') }}"
            >
            @error('state')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4 mb-3">
            <label for="postcode" class="form-label">Postcode</label>
            <input
                type="text"
                class="form-control @error('postcode') is-invalid @enderror"
                id="postcode"
                name="postcode"
                value="{{ old('postcode') }}"
            >
            @error('postcode')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="whatsapp_number" class="form-label">WhatsApp Number</label>
            <input
                type="tel"
                class="form-control @error('whatsapp_number') is-invalid @enderror"
                id="whatsapp_number"
                name="whatsapp_number"
                value="{{ old('whatsapp_number') }}"
                placeholder="60XXXXXXXXX"
            >
            @error('whatsapp_number')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6 mb-3">
            <label for="emergency_phone" class="form-label">Emergency Phone</label>
            <input
                type="tel"
                class="form-control @error('emergency_phone') is-invalid @enderror"
                id="emergency_phone"
                name="emergency_phone"
                value="{{ old('emergency_phone') }}"
            >
            @error('emergency_phone')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="mb-3">
        <label for="emergency_contact" class="form-label">Emergency Contact Name</label>
        <input
            type="text"
            class="form-control @error('emergency_contact') is-invalid @enderror"
            id="emergency_contact"
            name="emergency_contact"
            value="{{ old('emergency_contact') }}"
        >
        @error('emergency_contact')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>

    <h5 class="mb-3 text-center mt-4">Account Security</h5>

    <div class="mb-3">
        <label for="password" class="form-label">Password *</label>
        <div class="input-group">
            <span class="input-group-text">
                <i class="fas fa-lock text-muted"></i>
            </span>
            <input
                type="password"
                class="form-control @error('password') is-invalid @enderror"
                id="password"
                name="password"
                placeholder="Minimum 8 characters"
                required
            >
            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                <i class="fas fa-eye" id="toggleIcon"></i>
            </button>
        </div>
        @error('password')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="password_confirmation" class="form-label">Confirm Password *</label>
        <div class="input-group">
            <span class="input-group-text">
                <i class="fas fa-lock text-muted"></i>
            </span>
            <input
                type="password"
                class="form-control"
                id="password_confirmation"
                name="password_confirmation"
                placeholder="Re-enter password"
                required
            >
            <button class="btn btn-outline-secondary" type="button" id="togglePasswordConfirm">
                <i class="fas fa-eye" id="toggleIconConfirm"></i>
            </button>
        </div>
    </div>

    <button type="submit" class="btn btn-primary w-100 mt-3" id="registerBtn">
        <i class="fas fa-user-plus me-2"></i> Register
    </button>

    <div class="auth-links">
        <div>
            Already have an account?
            <a href="{{ route('login') }}">
                <i class="fas fa-sign-in-alt me-1"></i> Login
            </a>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Toggle password visibility
    $('#togglePassword').click(function() {
        const passwordField = $('#password');
        const toggleIcon = $('#toggleIcon');

        if (passwordField.attr('type') === 'password') {
            passwordField.attr('type', 'text');
            toggleIcon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            passwordField.attr('type', 'password');
            toggleIcon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    $('#togglePasswordConfirm').click(function() {
        const passwordField = $('#password_confirmation');
        const toggleIcon = $('#toggleIconConfirm');

        if (passwordField.attr('type') === 'password') {
            passwordField.attr('type', 'text');
            toggleIcon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            passwordField.attr('type', 'password');
            toggleIcon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    // Copy phone to WhatsApp if empty
    $('#phone').blur(function() {
        if ($('#whatsapp_number').val() === '') {
            $('#whatsapp_number').val($(this).val());
        }
    });

    // Form submission
    $('#registerForm').submit(function() {
        $('#registerBtn').html('<i class="fas fa-spinner fa-spin me-2"></i> Registering...').prop('disabled', true);
    });
});
</script>
@endpush
