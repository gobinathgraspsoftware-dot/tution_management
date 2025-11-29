@extends('layouts.app')

@section('title', 'Register New Child')
@section('page-title', 'Register New Child')

@section('content')
<div class="page-header">
    <h1><i class="fas fa-user-plus me-2"></i> Register New Child</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('parent.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('parent.children.index') }}">My Children</a></li>
            <li class="breadcrumb-item active">Register New Child</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-user-graduate me-2"></i> Child Information
            </div>
            <div class="card-body">
                <form action="{{ route('parent.children.store') }}" method="POST" id="childRegistrationForm">
                    @csrf

                    <!-- Parent Info Display -->
                    <div class="alert alert-info mb-4">
                        <i class="fas fa-info-circle me-2"></i>
                        Registering as: <strong>{{ $parent->user->name }}</strong> ({{ ucfirst($parent->relationship) }})
                    </div>

                    <!-- Basic Information -->
                    <h5 class="mb-3 text-primary"><i class="fas fa-user me-2"></i> Basic Information</h5>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Child's Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name') }}"
                                   placeholder="Enter child's full name" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="ic_number" class="form-label">IC Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('ic_number') is-invalid @enderror"
                                   id="ic_number" name="ic_number" value="{{ old('ic_number') }}"
                                   placeholder="XXXXXX-XX-XXXX" required>
                            @error('ic_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="date_of_birth" class="form-label">Date of Birth <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror"
                                   id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth') }}"
                                   max="{{ date('Y-m-d') }}" required>
                            @error('date_of_birth')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="gender" class="form-label">Gender <span class="text-danger">*</span></label>
                            <select class="form-select @error('gender') is-invalid @enderror" id="gender" name="gender" required>
                                <option value="">Select gender</option>
                                <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                                <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                            </select>
                            @error('gender')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="grade_level" class="form-label">Grade Level <span class="text-danger">*</span></label>
                            <select class="form-select @error('grade_level') is-invalid @enderror" id="grade_level" name="grade_level" required>
                                <option value="">Select grade</option>
                                @foreach($gradeLevels as $value => $label)
                                    <option value="{{ $value }}" {{ old('grade_level') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('grade_level')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- School & Contact -->
                    <h5 class="mb-3 mt-4 text-primary"><i class="fas fa-school me-2"></i> School & Contact</h5>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="school_name" class="form-label">School Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('school_name') is-invalid @enderror"
                                   id="school_name" name="school_name" value="{{ old('school_name') }}"
                                   placeholder="Enter school name" required>
                            @error('school_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Child's Phone (Optional)</label>
                            <input type="tel" class="form-control @error('phone') is-invalid @enderror"
                                   id="phone" name="phone" value="{{ old('phone') }}"
                                   placeholder="If child has own phone">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Child's Email (Optional)</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                               id="email" name="email" value="{{ old('email') }}"
                               placeholder="Will be auto-generated if left blank">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="address" class="form-label">Address (Optional)</label>
                        <textarea class="form-control @error('address') is-invalid @enderror"
                                  id="address" name="address" rows="2"
                                  placeholder="Leave blank to use your registered address">{{ old('address') }}</textarea>
                        <small class="text-muted">Default: {{ $parent->address }}</small>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Additional Information -->
                    <h5 class="mb-3 mt-4 text-primary"><i class="fas fa-info-circle me-2"></i> Additional Information</h5>

                    <div class="mb-3">
                        <label for="medical_conditions" class="form-label">Medical Conditions (if any)</label>
                        <textarea class="form-control @error('medical_conditions') is-invalid @enderror"
                                  id="medical_conditions" name="medical_conditions" rows="2"
                                  placeholder="Any allergies, health conditions, or special needs">{{ old('medical_conditions') }}</textarea>
                        @error('medical_conditions')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="referral_code" class="form-label">
                                <i class="fas fa-gift text-warning me-1"></i> Referral Code (Optional)
                            </label>
                            <input type="text" class="form-control @error('referral_code') is-invalid @enderror"
                                   id="referral_code" name="referral_code" value="{{ old('referral_code') }}"
                                   placeholder="Enter referral code for RM50 discount">
                            <div id="referral-feedback" class="form-text"></div>
                            @error('referral_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="notes" class="form-label">Additional Notes</label>
                            <input type="text" class="form-control @error('notes') is-invalid @enderror"
                                   id="notes" name="notes" value="{{ old('notes') }}"
                                   placeholder="Any additional information">
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="d-flex justify-content-between mt-4">
                        <a href="{{ route('parent.children.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Back
                        </a>
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <i class="fas fa-paper-plane me-1"></i> Submit Registration
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Sidebar Info -->
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-info-circle me-2"></i> Registration Info
            </div>
            <div class="card-body">
                <p class="small text-muted mb-3">
                    Registration is subject to approval. You will receive a notification once approved.
                </p>
                <ul class="small mb-0">
                    <li>Fill in all required fields accurately</li>
                    <li>IC number must be unique</li>
                    <li>Approval usually takes 1-2 business days</li>
                    <li>After approval, you can enroll in packages</li>
                </ul>
            </div>
        </div>

        @if($packages->count() > 0)
        <div class="card">
            <div class="card-header">
                <i class="fas fa-box me-2"></i> Available Packages
            </div>
            <div class="card-body">
                @foreach($packages->take(3) as $package)
                <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                    <div>
                        <strong>{{ $package->name }}</strong>
                        <br>
                        <small class="text-muted">{{ ucfirst($package->type) }}</small>
                    </div>
                    <span class="badge bg-primary">RM {{ number_format($package->monthly_fee, 2) }}</span>
                </div>
                @endforeach
                <small class="text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    Package enrollment available after registration approval.
                </small>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Validate referral code
        let referralTimeout;
        $('#referral_code').on('blur keyup', function() {
            clearTimeout(referralTimeout);
            let code = $(this).val();

            if (code.length >= 6) {
                referralTimeout = setTimeout(function() {
                    $.get('{{ route("public.registration.validate-referral") }}', { code: code })
                        .done(function(response) {
                            if (response.valid) {
                                $('#referral-feedback')
                                    .html('<i class="fas fa-check-circle text-success"></i> ' + response.message)
                                    .removeClass('text-danger').addClass('text-success');
                            } else {
                                $('#referral-feedback')
                                    .html('<i class="fas fa-times-circle text-danger"></i> ' + response.message)
                                    .removeClass('text-success').addClass('text-danger');
                            }
                        });
                }, 500);
            } else {
                $('#referral-feedback').html('');
            }
        });

        // Form submission
        $('#childRegistrationForm').on('submit', function() {
            $('#submitBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Submitting...');
        });
    });
</script>
@endpush
