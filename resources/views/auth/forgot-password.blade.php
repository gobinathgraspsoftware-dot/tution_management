@extends('layouts.guest')

@section('title', 'Forgot Password')
@section('header-subtitle', 'Reset your password')

@section('content')
<div class="text-center mb-4">
    <i class="fas fa-key fa-3x text-primary mb-3"></i>
    <p class="text-muted">Enter your email address and we'll send you an OTP to reset your password.</p>
</div>

<form action="{{ route('password.email') }}" method="POST" id="forgotPasswordForm">
    @csrf

    <div class="mb-3">
        <label for="email" class="form-label">Email Address</label>
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
                placeholder="Enter your registered email"
                required
                autofocus
            >
        </div>
        @error('email')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>

    <button type="submit" class="btn btn-primary w-100" id="sendOtpBtn">
        <i class="fas fa-paper-plane me-2"></i> Send OTP
    </button>

    <div class="auth-links">
        <div>
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
        <br>
        <small>(In production, this will be sent via email/SMS/WhatsApp)</small>
    </div>
@endif
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#forgotPasswordForm').submit(function() {
        $('#sendOtpBtn').html('<i class="fas fa-spinner fa-spin me-2"></i> Sending OTP...').prop('disabled', true);
    });
});
</script>
@endpush
