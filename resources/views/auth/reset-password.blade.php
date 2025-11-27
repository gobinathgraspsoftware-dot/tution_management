@extends('layouts.guest')

@section('title', 'Reset Password')
@section('header-subtitle', 'Create new password')

@section('content')
<div class="text-center mb-4">
    <i class="fas fa-lock-open fa-3x text-primary mb-3"></i>
    <p class="text-muted">Enter the OTP sent to your email and create a new password.</p>
</div>

<form action="{{ route('password.update') }}" method="POST" id="resetPasswordForm">
    @csrf

    <input type="hidden" name="email" value="{{ request('email') }}">

    <div class="mb-3">
        <label for="email" class="form-label">Email Address</label>
        <div class="input-group">
            <span class="input-group-text">
                <i class="fas fa-envelope text-muted"></i>
            </span>
            <input
                type="email"
                class="form-control"
                id="email"
                value="{{ request('email') }}"
                readonly
            >
        </div>
    </div>

    <div class="mb-3">
        <label for="otp" class="form-label">OTP Code</label>
        <div class="input-group">
            <span class="input-group-text">
                <i class="fas fa-key text-muted"></i>
            </span>
            <input
                type="text"
                class="form-control @error('otp') is-invalid @enderror"
                id="otp"
                name="otp"
                value="{{ old('otp') }}"
                placeholder="Enter 6-digit OTP"
                maxlength="6"
                required
                autofocus
            >
        </div>
        @error('otp')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
        <small class="text-muted">OTP is valid for 15 minutes</small>
    </div>

    <div class="mb-3">
        <label for="password" class="form-label">New Password</label>
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
        <label for="password_confirmation" class="form-label">Confirm New Password</label>
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
            <button class="btn btn-outline-secondary" type="button" id="togglePasswordConfirm">
                <i class="fas fa-eye" id="toggleIconConfirm"></i>
            </button>
        </div>
    </div>

    <button type="submit" class="btn btn-primary w-100" id="resetBtn">
        <i class="fas fa-check me-2"></i> Reset Password
    </button>

    <div class="auth-links">
        <div>
            <a href="{{ route('password.request') }}">
                <i class="fas fa-redo me-1"></i> Resend OTP
            </a>
        </div>
        <div class="mt-2">
            Remember your password?
            <a href="{{ route('login') }}">
                <i class="fas fa-sign-in-alt me-1"></i> Login
            </a>
        </div>
    </div>
</form>

@if(session('otp_sent'))
    <div class="alert alert-info mt-3">
        <i class="fas fa-info-circle me-2"></i>
        <strong>Demo Mode:</strong> Your OTP is: <strong>{{ session('otp_sent') }}</strong>
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

    // Auto-format OTP input (numbers only)
    $('#otp').on('input', function() {
        $(this).val($(this).val().replace(/\D/g, ''));
    });

    // Form submission
    $('#resetPasswordForm').submit(function() {
        $('#resetBtn').html('<i class="fas fa-spinner fa-spin me-2"></i> Resetting...').prop('disabled', true);
    });
});
</script>
@endpush
