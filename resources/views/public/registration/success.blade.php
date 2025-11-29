@extends('layouts.public')

@section('title', 'Registration Successful')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="registration-card">
            <div class="registration-header" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                <i class="fas fa-check-circle fa-4x mb-3"></i>
                <h2>Registration Successful!</h2>
                <p>Thank you for registering with Arena Matriks Edu Group</p>
            </div>

            <div class="registration-body">
                <div class="alert alert-success">
                    <i class="fas fa-info-circle me-2"></i>
                    Your registration has been submitted and is <strong>pending approval</strong>.
                    You will receive a notification once approved.
                </div>

                <!-- Student Information -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <i class="fas fa-user-graduate me-2"></i> Student Account Details
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-2">
                                    <strong>Student Name:</strong><br>
                                    {{ $data['student_name'] }}
                                </p>
                                <p class="mb-2">
                                    <strong>Student ID:</strong><br>
                                    <span class="badge bg-primary">{{ $data['student_id'] }}</span>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-2">
                                    <strong>Email:</strong><br>
                                    {{ $data['student_email'] }}
                                </p>
                                <p class="mb-2">
                                    <strong>Temporary Password:</strong><br>
                                    <code class="bg-light p-1">{{ $data['student_temp_password'] }}</code>
                                    <button class="btn btn-sm btn-outline-secondary ms-2" onclick="copyToClipboard('{{ $data['student_temp_password'] }}')">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Parent Information -->
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <i class="fas fa-user-friends me-2"></i> Parent Account Details
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-2">
                                    <strong>Parent Name:</strong><br>
                                    {{ $data['parent_name'] }}
                                </p>
                                <p class="mb-2">
                                    <strong>Email:</strong><br>
                                    {{ $data['parent_email'] }}
                                </p>
                            </div>
                            <div class="col-md-6">
                                @if($data['parent_is_new'])
                                    <p class="mb-2">
                                        <strong>Temporary Password:</strong><br>
                                        <code class="bg-light p-1">{{ $data['parent_temp_password'] }}</code>
                                        <button class="btn btn-sm btn-outline-secondary ms-2" onclick="copyToClipboard('{{ $data['parent_temp_password'] }}')">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </p>
                                    <p class="text-muted small mb-0">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Please save this password securely. You can change it after logging in.
                                    </p>
                                @else
                                    <p class="mb-2">
                                        <span class="badge bg-info">Existing Account</span>
                                    </p>
                                    <p class="text-muted small mb-0">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Your existing parent account password remains unchanged.
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Important Notes -->
                <div class="info-box">
                    <h6><i class="fas fa-exclamation-triangle text-warning me-2"></i> Important Notes</h6>
                    <ul class="mb-0">
                        <li>Please save these credentials securely.</li>
                        <li>You will receive an email/WhatsApp notification once your registration is approved.</li>
                        <li>After approval, login to complete package enrollment.</li>
                        <li>For any queries, contact us at <strong>support@arenamatriks.edu.my</strong></li>
                    </ul>
                </div>

                <!-- Next Steps -->
                <div class="card mt-4">
                    <div class="card-header">
                        <i class="fas fa-list-ol me-2"></i> What's Next?
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-start mb-3">
                            <span class="badge bg-primary rounded-pill me-3">1</span>
                            <div>
                                <strong>Wait for Approval</strong>
                                <p class="text-muted mb-0 small">Our team will review your registration within 1-2 business days.</p>
                            </div>
                        </div>
                        <div class="d-flex align-items-start mb-3">
                            <span class="badge bg-primary rounded-pill me-3">2</span>
                            <div>
                                <strong>Receive Notification</strong>
                                <p class="text-muted mb-0 small">You'll receive an email and WhatsApp message once approved.</p>
                            </div>
                        </div>
                        <div class="d-flex align-items-start mb-3">
                            <span class="badge bg-primary rounded-pill me-3">3</span>
                            <div>
                                <strong>Login & Enroll</strong>
                                <p class="text-muted mb-0 small">Login to parent portal and select your preferred package.</p>
                            </div>
                        </div>
                        <div class="d-flex align-items-start">
                            <span class="badge bg-primary rounded-pill me-3">4</span>
                            <div>
                                <strong>Start Learning!</strong>
                                <p class="text-muted mb-0 small">Complete payment and begin your tuition journey.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex justify-content-center gap-3 mt-4">
                    <a href="{{ route('public.registration.index') }}" class="btn btn-outline-primary">
                        <i class="fas fa-user-plus me-2"></i> Register Another Student
                    </a>
                    <a href="{{ route('login') }}" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt me-2"></i> Go to Login
                    </a>
                </div>

                <!-- Print Button -->
                <div class="text-center mt-3">
                    <button onclick="window.print()" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-print me-1"></i> Print This Page
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(function() {
            alert('Password copied to clipboard!');
        });
    }
</script>
@endpush

@push('styles')
<style>
    @media print {
        .public-header, .public-footer, .btn {
            display: none !important;
        }
        .registration-card {
            box-shadow: none !important;
        }
    }
</style>
@endpush
