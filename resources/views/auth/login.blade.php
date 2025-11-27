@extends('layouts.guest')

@section('title', 'Login')
@section('header-subtitle', 'Login to your account')

@section('content')
<form action="{{ route('login') }}" method="POST" id="loginForm">
    @csrf

    <div class="mb-3">
        <label for="email" class="form-label">
            <i class="fas fa-envelope me-1"></i> Email Address
        </label>
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
                placeholder="Enter your email"
                required
                autofocus
            >
        </div>
        @error('email')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="password" class="form-label">
            <i class="fas fa-lock me-1"></i> Password
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
                placeholder="Enter your password"
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

    <div class="mb-3 form-check">
        <input
            type="checkbox"
            class="form-check-input"
            id="remember"
            name="remember"
            {{ old('remember') ? 'checked' : '' }}
        >
        <label class="form-check-label" for="remember">
            Remember me
        </label>
    </div>

    <button type="submit" class="btn btn-primary w-100" id="loginBtn">
        <i class="fas fa-sign-in-alt me-2"></i> Login
    </button>

    <div class="auth-links">
        <div class="mb-2">
            <a href="{{ route('password.request') }}">
                <i class="fas fa-key me-1"></i> Forgot your password?
            </a>
        </div>
        <div>
            Don't have an account?
            <a href="{{ route('register') }}">
                <i class="fas fa-user-plus me-1"></i> Register as Parent
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

    // Form validation and loading state
    $('#loginForm').submit(function() {
        $('#loginBtn').html('<i class="fas fa-spinner fa-spin me-2"></i> Logging in...').prop('disabled', true);
    });
});
</script>
@endpush
