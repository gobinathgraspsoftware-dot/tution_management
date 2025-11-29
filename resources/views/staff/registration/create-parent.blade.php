@extends('layouts.app')

@section('title', 'Register Parent')
@section('page-title', 'Register Parent')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="fas fa-user-friends me-2"></i> Register New Parent</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('staff.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Register Parent</li>
            </ol>
        </nav>
    </div>
    <div>
        <a href="{{ route('staff.registration.create-student') }}" class="btn btn-outline-primary">
            <i class="fas fa-user-graduate me-1"></i> Register Student
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-user-plus me-2"></i> Parent Registration Form (Offline)
            </div>
            <div class="card-body">
                <form action="{{ route('staff.registration.store-parent') }}" method="POST" id="parentRegistrationForm">
                    @csrf

                    <!-- Personal Information -->
                    <h5 class="mb-3 text-primary"><i class="fas fa-user me-2"></i> Personal Information</h5>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name') }}"
                                   placeholder="Enter parent's full name" required>
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
                        <div class="col-md-6 mb-3">
                            <label for="relationship" class="form-label">Relationship <span class="text-danger">*</span></label>
                            <select class="form-select @error('relationship') is-invalid @enderror"
                                    id="relationship" name="relationship" required>
                                <option value="">Select relationship</option>
                                <option value="father" {{ old('relationship') == 'father' ? 'selected' : '' }}>Father</option>
                                <option value="mother" {{ old('relationship') == 'mother' ? 'selected' : '' }}>Mother</option>
                                <option value="guardian" {{ old('relationship') == 'guardian' ? 'selected' : '' }}>Guardian</option>
                            </select>
                            @error('relationship')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="occupation" class="form-label">Occupation</label>
                            <input type="text" class="form-control @error('occupation') is-invalid @enderror"
                                   id="occupation" name="occupation" value="{{ old('occupation') }}"
                                   placeholder="Job/Profession">
                            @error('occupation')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Contact Information -->
                    <h5 class="mb-3 mt-4 text-primary"><i class="fas fa-phone me-2"></i> Contact Information</h5>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                   id="email" name="email" value="{{ old('email') }}"
                                   placeholder="parent@email.com" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control @error('phone') is-invalid @enderror"
                                   id="phone" name="phone" value="{{ old('phone') }}"
                                   placeholder="01X-XXXXXXX" required>
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="whatsapp_number" class="form-label">
                            <i class="fab fa-whatsapp text-success me-1"></i> WhatsApp Number
                        </label>
                        <input type="tel" class="form-control @error('whatsapp_number') is-invalid @enderror"
                               id="whatsapp_number" name="whatsapp_number" value="{{ old('whatsapp_number') }}"
                               placeholder="Same as phone if blank">
                        <small class="text-muted">Leave blank to use the same phone number</small>
                        @error('whatsapp_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Address -->
                    <h5 class="mb-3 mt-4 text-primary"><i class="fas fa-map-marker-alt me-2"></i> Address</h5>

                    <div class="mb-3">
                        <label for="address" class="form-label">Street Address <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('address') is-invalid @enderror"
                                  id="address" name="address" rows="2"
                                  placeholder="Full street address" required>{{ old('address') }}</textarea>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="city" class="form-label">City <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('city') is-invalid @enderror"
                                   id="city" name="city" value="{{ old('city') }}"
                                   placeholder="City" required>
                            @error('city')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="state" class="form-label">State <span class="text-danger">*</span></label>
                            <select class="form-select @error('state') is-invalid @enderror" id="state" name="state" required>
                                <option value="">Select state</option>
                                <option value="Johor" {{ old('state') == 'Johor' ? 'selected' : '' }}>Johor</option>
                                <option value="Kedah" {{ old('state') == 'Kedah' ? 'selected' : '' }}>Kedah</option>
                                <option value="Kelantan" {{ old('state') == 'Kelantan' ? 'selected' : '' }}>Kelantan</option>
                                <option value="Melaka" {{ old('state') == 'Melaka' ? 'selected' : '' }}>Melaka</option>
                                <option value="Negeri Sembilan" {{ old('state') == 'Negeri Sembilan' ? 'selected' : '' }}>Negeri Sembilan</option>
                                <option value="Pahang" {{ old('state') == 'Pahang' ? 'selected' : '' }}>Pahang</option>
                                <option value="Perak" {{ old('state') == 'Perak' ? 'selected' : '' }}>Perak</option>
                                <option value="Perlis" {{ old('state') == 'Perlis' ? 'selected' : '' }}>Perlis</option>
                                <option value="Pulau Pinang" {{ old('state') == 'Pulau Pinang' ? 'selected' : '' }}>Pulau Pinang</option>
                                <option value="Sabah" {{ old('state') == 'Sabah' ? 'selected' : '' }}>Sabah</option>
                                <option value="Sarawak" {{ old('state') == 'Sarawak' ? 'selected' : '' }}>Sarawak</option>
                                <option value="Selangor" {{ old('state') == 'Selangor' ? 'selected' : '' }}>Selangor</option>
                                <option value="Terengganu" {{ old('state') == 'Terengganu' ? 'selected' : '' }}>Terengganu</option>
                                <option value="Kuala Lumpur" {{ old('state') == 'Kuala Lumpur' ? 'selected' : '' }}>Kuala Lumpur</option>
                                <option value="Labuan" {{ old('state') == 'Labuan' ? 'selected' : '' }}>Labuan</option>
                                <option value="Putrajaya" {{ old('state') == 'Putrajaya' ? 'selected' : '' }}>Putrajaya</option>
                            </select>
                            @error('state')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="postcode" class="form-label">Postcode <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('postcode') is-invalid @enderror"
                                   id="postcode" name="postcode" value="{{ old('postcode') }}"
                                   placeholder="XXXXX" maxlength="5" required>
                            @error('postcode')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Emergency Contact -->
                    <h5 class="mb-3 mt-4 text-primary"><i class="fas fa-first-aid me-2"></i> Emergency Contact</h5>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="emergency_contact" class="form-label">Emergency Contact Name</label>
                            <input type="text" class="form-control @error('emergency_contact') is-invalid @enderror"
                                   id="emergency_contact" name="emergency_contact" value="{{ old('emergency_contact') }}"
                                   placeholder="Alternative contact person">
                            @error('emergency_contact')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="emergency_phone" class="form-label">Emergency Contact Phone</label>
                            <input type="tel" class="form-control @error('emergency_phone') is-invalid @enderror"
                                   id="emergency_phone" name="emergency_phone" value="{{ old('emergency_phone') }}"
                                   placeholder="01X-XXXXXXX">
                            @error('emergency_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="d-flex justify-content-between mt-4">
                        <a href="{{ route('staff.dashboard') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Back
                        </a>
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <i class="fas fa-save me-1"></i> Register Parent
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Info Card -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-info-circle me-2"></i> Registration Info
            </div>
            <div class="card-body">
                <p class="small text-muted mb-3">
                    Parent accounts are activated immediately upon registration.
                </p>
                <div class="alert alert-info small mb-0">
                    <i class="fas fa-key me-1"></i>
                    A temporary password will be generated. Make sure to provide this to the parent securely.
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-chart-bar me-2"></i> Quick Stats
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span>Total Parents:</span>
                    <span class="badge bg-primary">{{ \App\Models\Parents::count() }}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Total Students:</span>
                    <span class="badge bg-success">{{ \App\Models\Student::count() }}</span>
                </div>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-link me-2"></i> Quick Links
            </div>
            <div class="card-body">
                <a href="{{ route('staff.registration.create-student') }}" class="btn btn-outline-primary btn-sm w-100 mb-2">
                    <i class="fas fa-user-graduate me-1"></i> Register Student
                </a>
                <a href="{{ route('staff.registration.pending') }}" class="btn btn-outline-warning btn-sm w-100">
                    <i class="fas fa-clock me-1"></i> Pending Approvals
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Form submission
        $('#parentRegistrationForm').on('submit', function() {
            $('#submitBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Registering...');
        });
    });
</script>
@endpush
