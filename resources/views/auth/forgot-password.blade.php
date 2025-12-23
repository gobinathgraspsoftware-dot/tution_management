@extends('layouts.guest')

@section('title', 'Forgot Password')
@section('header-subtitle', 'Reset your password via WhatsApp')

@section('content')
<div class="text-center mb-4">
    <i class="fab fa-whatsapp fa-3x text-success mb-3"></i>
    <p class="text-muted">Enter your WhatsApp number and we'll send you an OTP to reset your password.</p>
</div>

<form action="{{ route('password.email') }}" method="POST" id="forgotPasswordForm">
    @csrf

    <!-- Country Code & Phone Number (Same pattern as ParentController) -->
    <div class="row">
        <div class="col-md-4">
            <div class="mb-3">
                <label for="country_code" class="form-label">Country <span class="text-danger">*</span></label>
                <select class="form-select @error('country_code') is-invalid @enderror"
                        id="country_code" name="country_code" required>
                    @foreach($countries as $country)
                        <option value="{{ $country['code'] }}"
                                data-min="{{ $country['min_length'] }}"
                                data-max="{{ $country['max_length'] }}"
                                {{ old('country_code', $defaultCountryCode) == $country['code'] ? 'selected' : '' }}>
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
                       id="phone" name="phone" value="{{ old('phone') }}"
                       placeholder="e.g., 123456789 (without leading zero)" required>
                <small class="text-muted">Enter phone number without country code</small>
                @error('phone')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <div class="alert alert-info">
        <i class="fas fa-shield-alt me-2"></i>
        <strong>Security Note:</strong> Make sure your WhatsApp is active and accessible. The OTP will expire in 15 minutes.
    </div>

    <button type="submit" class="btn btn-success w-100" id="sendOtpBtn">
        <i class="fab fa-whatsapp me-2"></i> Send OTP via WhatsApp
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

@if(session('otp_demo'))
    <div class="alert alert-warning mt-3">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <strong>Development Mode:</strong> WhatsApp service unavailable. Your OTP is: <strong class="fs-4">{{ session('otp_demo') }}</strong>
        <br>
        <small>(This message only appears in development mode)</small>
    </div>
@endif
@endsection

@push('scripts')
<script>
// Same phone formatting logic as ParentController
$(document).ready(function() {
    // Format phone number - only allow digits, remove leading zero
    $('#phone').on('input', function() {
        let value = $(this).val().replace(/\D/g, '');

        // Remove leading 0 if present
        if (value.startsWith('0')) {
            value = value.substring(1);
        }

        $(this).val(value);
    });

    // Form submission with loading state
    $('#forgotPasswordForm').submit(function() {
        const phone = $('#phone').val().trim();

        if (phone.length < 8) {
            alert('Phone number must be at least 8 digits.');
            return false;
        }

        $('#sendOtpBtn')
            .html('<i class="fas fa-spinner fa-spin me-2"></i> Sending OTP...')
            .prop('disabled', true);
    });
});
</script>
@endpush

@push('styles')
<style>
.alert-info {
    border-left: 4px solid #0dcaf0;
}

.alert-warning {
    border-left: 4px solid #ffc107;
}

#phone {
    font-family: 'Courier New', monospace;
    font-size: 1.1rem;
}

.form-select {
    font-size: 0.9rem;
}
</style>
@endpush
