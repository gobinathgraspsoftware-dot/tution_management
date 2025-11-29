@extends('layouts.public')

@section('title', 'Student Registration')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="registration-card">
            <div class="registration-header">
                <i class="fas fa-user-graduate fa-3x mb-3"></i>
                <h2>Student Registration</h2>
                <p>Join Arena Matriks Edu Group and unlock your potential!</p>
            </div>

            <div class="registration-body">
                <div class="row">
                    <!-- New Registration -->
                    <div class="col-md-6 mb-4 mb-md-0">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body text-center p-4">
                                <div class="mb-3">
                                    <i class="fas fa-user-plus fa-3x text-primary"></i>
                                </div>
                                <h5 class="card-title">New Student Registration</h5>
                                <p class="card-text text-muted">
                                    Register your child for tuition classes. Create both parent and student accounts.
                                </p>
                                <a href="{{ route('public.registration.student') }}" class="btn btn-primary">
                                    <i class="fas fa-arrow-right me-2"></i> Register Now
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Existing Parent -->
                    <div class="col-md-6">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body text-center p-4">
                                <div class="mb-3">
                                    <i class="fas fa-sign-in-alt fa-3x text-success"></i>
                                </div>
                                <h5 class="card-title">Already a Parent?</h5>
                                <p class="card-text text-muted">
                                    Login to your parent account to register additional children.
                                </p>
                                <a href="{{ route('login') }}" class="btn btn-success">
                                    <i class="fas fa-sign-in-alt me-2"></i> Login
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Info Section -->
                <div class="mt-4">
                    <div class="info-box">
                        <h6><i class="fas fa-info-circle me-2"></i> Registration Process</h6>
                        <ol class="mb-0 ps-3">
                            <li>Fill in parent and student details</li>
                            <li>Submit registration for approval</li>
                            <li>Receive confirmation via email/WhatsApp</li>
                            <li>Login and select your preferred package</li>
                        </ol>
                    </div>
                </div>

                <!-- Packages Preview -->
                @if($packages->count() > 0)
                <div class="mt-4">
                    <h5 class="mb-3"><i class="fas fa-box me-2"></i> Available Packages</h5>
                    <div class="row">
                        @foreach($packages->take(3) as $package)
                        <div class="col-md-4 mb-3">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body">
                                    <h6 class="card-title">{{ $package->name }}</h6>
                                    <p class="text-muted small mb-2">
                                        <i class="fas fa-tag me-1"></i>
                                        {{ ucfirst($package->type) }} Class
                                    </p>
                                    <p class="h5 text-primary mb-2">
                                        RM {{ number_format($package->monthly_fee, 2) }}
                                        <small class="text-muted">/month</small>
                                    </p>
                                    @if($package->subjects->count() > 0)
                                    <p class="small text-muted mb-0">
                                        <i class="fas fa-book me-1"></i>
                                        {{ $package->subjects->pluck('name')->take(2)->join(', ') }}
                                        @if($package->subjects->count() > 2)
                                            +{{ $package->subjects->count() - 2 }} more
                                        @endif
                                    </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Referral Info -->
                <div class="mt-4 p-3 bg-light rounded">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas fa-gift fa-2x text-warning"></i>
                        </div>
                        <div>
                            <h6 class="mb-1">Have a Referral Code?</h6>
                            <p class="mb-0 text-muted small">
                                Get <strong>RM50 discount</strong> when you register with a valid referral code from an existing student!
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
