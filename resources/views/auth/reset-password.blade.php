@extends('layouts.guest')

@section('title', 'Reset Password')
@section('header-subtitle', 'Create new password')

@section('content')
<div class="text-center mb-4">
    <i class="fas fa-lock-open fa-3x text-primary mb-3"></i>
    <p class="text-muted">Enter the OTP sent to your WhatsApp and create a new password.</p>
</div>

<form action="{{ route('password.update') }}" method="POST" id="resetPasswordForm">
    @csrf

    <input type="hidden" name="phone" value="{{ request('phone') }}">

    <div class="mb-3">
        <label for="phone_display" class="form-label">
            <i class="fab fa-whatsapp text-success"></i> WhatsApp Number
        </label>
        <div class="input-group">
            <span class="input-group-text">
                <i class="fas fa-mobile-alt text-muted"></i>
            </span>
            <input
                type="text"
                class="form-control"
                id="phone_display"
                value="+{{ request('phone') }}"
                readonly
            >
        </div>
        <small class="text-muted">Check your WhatsApp for the OTP message</small>
    </div>

    <div class="mb-3">
        <label for="otp" class="form-label">
            OTP Code 
            <span class="text-danger">*</span>
        </label>
        <div class="input-group">
            <span class="input-group-text">
                <i class="fas fa-key text-muted"></i>
            </span>
            <input
                type="text"
                class="form-control form-control-lg text-center @error('otp') is-invalid @enderror"
                id="otp"
                name="otp"
                value="{{ old('otp') }}"
                placeholder="Enter 6-digit OTP"
                maxlength="6"
                required
                autofocus
                style="letter-spacing: 8px; font-size: 1.5rem; font-weight: bold;"
            >
        </div>
        @error('otp')
            <div class="text-danger small mt-1">
                <i class="fas fa-exclamation-circle"></i> {{ $message }}
            </div>
        @enderror
        <div class="d-flex justify-content-between align-items-center mt-2">
            <small class="text-muted">
                <i class="fas fa-clock"></i> OTP valid for 15 minutes
            </small>
            <small>
                <a href="{{ route('password.request') }}" class="text-decoration-none">
                    <i class="fas fa-redo"></i> Resend OTP
                </a>
            </small>
        </div>
    </div>

    <div class="mb-3">
        <label for="password" class="form-label">
            New Password 
            <span class="text-danger">*</span>
        </label>
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
            <button class="btn btn-outline-secondary" type="button" id="togglePassword" tabindex="-1">
                <i class="fas fa-eye" id="toggleIcon"></i>
            </button>
        </div>
        @error('password')
            <div class="text-danger small mt-1">
                <i class="fas fa-exclamation-circle"></i> {{ $message }}
            </div>
        @enderror
        <div id="passwordStrength" class="mt-2"></div>
    </div>

    <div class="mb-3">
        <label for="password_confirmation" class="form-label">
            Confirm New Password 
            <span class="text-danger">*</span>
        </label>
        <div class="input-group">
            <span class="input-group-text">
                <i class="fas fa-lock text-muted"></i>
            </span>
            <input
                type="password"
                class="form-control"
                id="password_confirmation"
                name="password_confirmation"
                placeholder="Re-enter new password"
                required
            >
            <button class="btn btn-outline-secondary" type="button" id="togglePasswordConfirm" tabindex="-1">
                <i class="fas fa-eye" id="toggleIconConfirm"></i>
            </button>
        </div>
        <small id="passwordMatch" class="mt-1"></small>
    </div>

    <div class="alert alert-info mb-3">
        <strong><i class="fas fa-info-circle"></i> Password Requirements:</strong>
        <ul class="mb-0 mt-2">
            <li>At least 8 characters long</li>
            <li>Mix of uppercase and lowercase letters recommended</li>
            <li>Include numbers for better security</li>
        </ul>
    </div>

    <button type="submit" class="btn btn-primary w-100 btn-lg" id="resetBtn">
        <i class="fas fa-check me-2"></i> Reset Password
    </button>

    <div class="auth-links">
        <div class="mt-3 text-center">
            Remember your password?
            <a href="{{ route('login') }}">
                <i class="fas fa-sign-in-alt me-1"></i> Back to Login
            </a>
        </div>
    </div>
</form>

@if(session('otp_demo'))
    <div class="alert alert-warning mt-3">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <strong>Development Mode:</strong> Your OTP is: <strong class="fs-4">{{ session('otp_demo') }}</strong>
    </div>
@endif
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

    // Auto-format OTP input (numbers only with spacing)
    $('#otp').on('input', function() {
        let value = $(this).val().replace(/\D/g, '');
        $(this).val(value);
        
        // Visual feedback when 6 digits entered
        if (value.length === 6) {
            $(this).removeClass('is-invalid').addClass('is-valid');
        } else {
            $(this).removeClass('is-valid');
        }
    });

    // Password strength indicator
    $('#password').on('input', function() {
        const password = $(this).val();
        const strength = calculatePasswordStrength(password);
        updatePasswordStrength(strength);
        checkPasswordMatch();
    });

    $('#password_confirmation').on('input', function() {
        checkPasswordMatch();
    });

    function calculatePasswordStrength(password) {
        let strength = 0;
        
        if (password.length >= 8) strength += 25;
        if (password.length >= 12) strength += 25;
        if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength += 25;
        if (/\d/.test(password)) strength += 15;
        if (/[^a-zA-Z0-9]/.test(password)) strength += 10;
        
        return Math.min(strength, 100);
    }

    function updatePasswordStrength(strength) {
        let color, text, bgColor;
        
        if (strength < 30) {
            color = 'danger';
            text = 'Weak';
            bgColor = '#dc3545';
        } else if (strength < 60) {
            color = 'warning';
            text = 'Fair';
            bgColor = '#ffc107';
        } else if (strength < 80) {
            color = 'info';
            text = 'Good';
            bgColor = '#0dcaf0';
        } else {
            color = 'success';
            text = 'Strong';
            bgColor = '#198754';
        }
        
        $('#passwordStrength').html(`
            <div class="d-flex justify-content-between align-items-center">
                <small class="text-${color}">
                    <i class="fas fa-shield-alt"></i> Password Strength: <strong>${text}</strong>
                </small>
                <small class="text-muted">${strength}%</small>
            </div>
            <div class="progress" style="height: 5px;">
                <div class="progress-bar bg-${color}" role="progressbar" 
                     style="width: ${strength}%; background-color: ${bgColor} !important;" 
                     aria-valuenow="${strength}" aria-valuemin="0" aria-valuemax="100">
                </div>
            </div>
        `);
    }

    function checkPasswordMatch() {
        const password = $('#password').val();
        const confirmation = $('#password_confirmation').val();
        
        if (confirmation.length > 0) {
            if (password === confirmation) {
                $('#passwordMatch').html('<span class="text-success"><i class="fas fa-check-circle"></i> Passwords match</span>');
                $('#password_confirmation').removeClass('is-invalid').addClass('is-valid');
            } else {
                $('#passwordMatch').html('<span class="text-danger"><i class="fas fa-times-circle"></i> Passwords do not match</span>');
                $('#password_confirmation').removeClass('is-valid').addClass('is-invalid');
            }
        } else {
            $('#passwordMatch').html('');
            $('#password_confirmation').removeClass('is-valid is-invalid');
        }
    }

    // Form submission
    $('#resetPasswordForm').submit(function(e) {
        const password = $('#password').val();
        const confirmation = $('#password_confirmation').val();
        
        if (password !== confirmation) {
            e.preventDefault();
            alert('Passwords do not match!');
            return false;
        }
        
        if (password.length < 8) {
            e.preventDefault();
            alert('Password must be at least 8 characters long!');
            return false;
        }
        
        $('#resetBtn').html('<i class="fas fa-spinner fa-spin me-2"></i> Resetting Password...').prop('disabled', true);
    });

    // Auto-focus on OTP if it's empty
    if ($('#otp').val() === '') {
        $('#otp').focus();
    }
});
</script>
@endpush

@push('styles')
<style>
.form-control-lg {
    padding: 0.75rem 1rem;
}

#otp::-webkit-inner-spin-button,
#otp::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

#otp {
    -moz-appearance: textfield;
}

.progress {
    border-radius: 10px;
    overflow: hidden;
}

.progress-bar {
    transition: width 0.3s ease;
}

.btn-outline-secondary:hover {
    background-color: #f8f9fa;
}

.alert-info {
    border-left: 4px solid #0dcaf0;
}

.alert-warning {
    border-left: 4px solid #ffc107;
}
</style>
@endpush
