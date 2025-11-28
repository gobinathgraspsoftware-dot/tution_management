@extends('layouts.app')

@section('title', 'Change Password')
@section('page-title', 'Change Password')

@section('content')
<div class="page-header">
    <h1>
        <i class="fas fa-key me-2"></i> Change Password
    </h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('profile.index') }}">Profile</a></li>
            <li class="breadcrumb-item active">Change Password</li>
        </ol>
    </nav>
</div>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-lock me-2"></i>Update Your Password</h5>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form action="{{ route('profile.password.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label for="current_password" class="form-label">Current Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password"
                                   class="form-control @error('current_password') is-invalid @enderror"
                                   id="current_password"
                                   name="current_password"
                                   required>
                            <button class="btn btn-outline-secondary toggle-password" type="button" data-target="current_password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        @error('current_password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label">New Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-key"></i></span>
                            <input type="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   id="password"
                                   name="password"
                                   required>
                            <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <div class="form-text">
                            <i class="fas fa-info-circle me-1"></i>
                            Password must be at least 8 characters long.
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label">Confirm New Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-key"></i></span>
                            <input type="password"
                                   class="form-control"
                                   id="password_confirmation"
                                   name="password_confirmation"
                                   required>
                            <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password_confirmation">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('profile.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Back to Profile
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Update Password
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Password Requirements Card -->
        <div class="card mt-4">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Password Requirements</h6>
            </div>
            <div class="card-body">
                <ul class="mb-0">
                    <li>Minimum 8 characters long</li>
                    <li>Include at least one uppercase letter</li>
                    <li>Include at least one lowercase letter</li>
                    <li>Include at least one number</li>
                    <li>Avoid using easily guessable passwords</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Toggle password visibility
    $('.toggle-password').on('click', function() {
        var targetId = $(this).data('target');
        var input = $('#' + targetId);
        var icon = $(this).find('i');

        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            input.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    // Password strength indicator
    $('#password').on('input', function() {
        var password = $(this).val();
        var strength = 0;

        if (password.length >= 8) strength++;
        if (password.match(/[a-z]/)) strength++;
        if (password.match(/[A-Z]/)) strength++;
        if (password.match(/[0-9]/)) strength++;
        if (password.match(/[^a-zA-Z0-9]/)) strength++;

        var strengthText = '';
        var strengthClass = '';

        switch(strength) {
            case 0:
            case 1:
                strengthText = 'Weak';
                strengthClass = 'text-danger';
                break;
            case 2:
            case 3:
                strengthText = 'Medium';
                strengthClass = 'text-warning';
                break;
            case 4:
            case 5:
                strengthText = 'Strong';
                strengthClass = 'text-success';
                break;
        }

        // You can add a strength indicator element if needed
    });
});
</script>
@endpush
