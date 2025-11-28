@extends('layouts.app')

@section('title', 'Add New Parent')
@section('page-title', 'Add New Parent')

@section('content')
<div class="page-header">
    <h1>
        <i class="fas fa-user-plus me-2"></i> Add New Parent
    </h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.parents.index') }}">Parents</a></li>
            <li class="breadcrumb-item active">Add New</li>
        </ol>
    </nav>
</div>

<!-- Alert Messages -->
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<form action="{{ route('admin.parents.store') }}" method="POST" id="parentForm">
    @csrf

    <div class="row">
        <!-- Account Information -->
        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-user me-2"></i>Account Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                               id="name" name="name" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                               id="email" name="email" value="{{ old('email') }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                       id="phone" name="phone" value="{{ old('phone') }}"
                                       placeholder="e.g., 0123456789" required>
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="ic_number" class="form-label">IC Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('ic_number') is-invalid @enderror"
                                       id="ic_number" name="ic_number" value="{{ old('ic_number') }}"
                                       placeholder="e.g., 880101145678" required>
                                @error('ic_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" class="form-control @error('password') is-invalid @enderror"
                                           id="password" name="password" required>
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password')">
                                        <i class="fas fa-eye" id="password-icon"></i>
                                    </button>
                                </div>
                                @error('password')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" class="form-control"
                                           id="password_confirmation" name="password_confirmation" required>
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password_confirmation')">
                                        <i class="fas fa-eye" id="password_confirmation-icon"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label">Account Status <span class="text-danger">*</span></label>
                        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                            <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Emergency Contact -->
            <div class="card mb-4">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="fas fa-ambulance me-2"></i>Emergency Contact</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="emergency_contact" class="form-label">Emergency Contact Name</label>
                        <input type="text" class="form-control @error('emergency_contact') is-invalid @enderror"
                               id="emergency_contact" name="emergency_contact" value="{{ old('emergency_contact') }}"
                               placeholder="e.g., Spouse or Family Member">
                        @error('emergency_contact')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="emergency_phone" class="form-label">Emergency Contact Phone</label>
                        <input type="text" class="form-control @error('emergency_phone') is-invalid @enderror"
                               id="emergency_phone" name="emergency_phone" value="{{ old('emergency_phone') }}"
                               placeholder="e.g., 0123456789">
                        @error('emergency_phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Personal & Address Information -->
        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-id-card me-2"></i>Personal Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="relationship" class="form-label">Relationship <span class="text-danger">*</span></label>
                                <select class="form-select @error('relationship') is-invalid @enderror"
                                        id="relationship" name="relationship" required>
                                    <option value="">-- Select --</option>
                                    <option value="father" {{ old('relationship') == 'father' ? 'selected' : '' }}>Father</option>
                                    <option value="mother" {{ old('relationship') == 'mother' ? 'selected' : '' }}>Mother</option>
                                    <option value="guardian" {{ old('relationship') == 'guardian' ? 'selected' : '' }}>Guardian</option>
                                </select>
                                @error('relationship')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="occupation" class="form-label">Occupation</label>
                                <input type="text" class="form-control @error('occupation') is-invalid @enderror"
                                       id="occupation" name="occupation" value="{{ old('occupation') }}"
                                       placeholder="e.g., Engineer, Teacher">
                                @error('occupation')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="whatsapp_number" class="form-label">WhatsApp Number</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fab fa-whatsapp text-success"></i></span>
                            <input type="text" class="form-control @error('whatsapp_number') is-invalid @enderror"
                                   id="whatsapp_number" name="whatsapp_number" value="{{ old('whatsapp_number') }}"
                                   placeholder="e.g., 60123456789">
                        </div>
                        <small class="text-muted">Include country code without '+' (e.g., 60123456789)</small>
                        @error('whatsapp_number')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="address" class="form-label">Address <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('address') is-invalid @enderror"
                                  id="address" name="address" rows="3" required
                                  placeholder="Full address including street and area">{{ old('address') }}</textarea>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="city" class="form-label">City <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('city') is-invalid @enderror"
                                       id="city" name="city" value="{{ old('city') }}" required
                                       placeholder="e.g., Shah Alam">
                                @error('city')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="state" class="form-label">State <span class="text-danger">*</span></label>
                                <select class="form-select @error('state') is-invalid @enderror"
                                        id="state" name="state" required>
                                    <option value="">-- Select --</option>
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
                                    <option value="W.P. Kuala Lumpur" {{ old('state') == 'W.P. Kuala Lumpur' ? 'selected' : '' }}>W.P. Kuala Lumpur</option>
                                    <option value="W.P. Labuan" {{ old('state') == 'W.P. Labuan' ? 'selected' : '' }}>W.P. Labuan</option>
                                    <option value="W.P. Putrajaya" {{ old('state') == 'W.P. Putrajaya' ? 'selected' : '' }}>W.P. Putrajaya</option>
                                </select>
                                @error('state')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="postcode" class="form-label">Postcode <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('postcode') is-invalid @enderror"
                                       id="postcode" name="postcode" value="{{ old('postcode') }}" required
                                       placeholder="e.g., 40000" maxlength="5">
                                @error('postcode')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notification Preferences -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-bell me-2"></i>Notification Preferences</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">Select how the parent would like to receive notifications:</p>

                    <div class="form-check mb-2">
                        <input type="checkbox" class="form-check-input" id="notif_whatsapp"
                               name="notification_preference[]" value="whatsapp"
                               {{ in_array('whatsapp', old('notification_preference', ['whatsapp', 'email'])) ? 'checked' : '' }}>
                        <label class="form-check-label" for="notif_whatsapp">
                            <i class="fab fa-whatsapp text-success me-1"></i> WhatsApp Notifications
                        </label>
                    </div>

                    <div class="form-check mb-2">
                        <input type="checkbox" class="form-check-input" id="notif_email"
                               name="notification_preference[]" value="email"
                               {{ in_array('email', old('notification_preference', ['whatsapp', 'email'])) ? 'checked' : '' }}>
                        <label class="form-check-label" for="notif_email">
                            <i class="fas fa-envelope text-primary me-1"></i> Email Notifications
                        </label>
                    </div>

                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="notif_sms"
                               name="notification_preference[]" value="sms"
                               {{ in_array('sms', old('notification_preference', [])) ? 'checked' : '' }}>
                        <label class="form-check-label" for="notif_sms">
                            <i class="fas fa-sms text-warning me-1"></i> SMS Notifications
                        </label>
                    </div>
                </div>
            </div>

            <!-- Link Students -->
            @if($unlinkedStudents->count() > 0)
            <div class="card mb-4">
                <div class="card-header bg-warning">
                    <h5 class="mb-0"><i class="fas fa-link me-2"></i>Link Existing Students (Optional)</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">Select students to link to this parent:</p>

                    <div class="student-list" style="max-height: 200px; overflow-y: auto;">
                        @foreach($unlinkedStudents as $student)
                            <div class="form-check mb-2">
                                <input type="checkbox" class="form-check-input"
                                       id="student_{{ $student->id }}"
                                       name="link_students[]"
                                       value="{{ $student->id }}"
                                       {{ in_array($student->id, old('link_students', [])) ? 'checked' : '' }}>
                                <label class="form-check-label" for="student_{{ $student->id }}">
                                    <strong>{{ $student->user->name }}</strong>
                                    <small class="text-muted">({{ $student->student_id }} - {{ $student->school_name }})</small>
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Form Actions -->
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between">
                <a href="{{ route('admin.parents.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Cancel
                </a>
                <div>
                    <button type="reset" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-redo me-1"></i> Reset
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Create Parent
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
function togglePassword(fieldId) {
    var field = document.getElementById(fieldId);
    var icon = document.getElementById(fieldId + '-icon');

    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

$(document).ready(function() {
    // Auto-fill WhatsApp number from phone if empty
    $('#phone').on('blur', function() {
        var phone = $(this).val();
        var whatsapp = $('#whatsapp_number');

        if (phone && !whatsapp.val()) {
            // Remove leading 0 and add 60 country code
            if (phone.startsWith('0')) {
                phone = '60' + phone.substring(1);
            }
            whatsapp.val(phone);
        }
    });

    // Form validation
    $('#parentForm').on('submit', function(e) {
        var password = $('#password').val();
        var confirmPassword = $('#password_confirmation').val();

        if (password !== confirmPassword) {
            e.preventDefault();
            alert('Password and Confirm Password do not match!');
            $('#password_confirmation').focus();
            return false;
        }

        if (password.length < 8) {
            e.preventDefault();
            alert('Password must be at least 8 characters long!');
            $('#password').focus();
            return false;
        }
    });
});
</script>
@endpush

@push('styles')
<style>
    .student-list {
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        padding: 1rem;
        background-color: #f8f9fa;
    }

    .student-list .form-check {
        padding: 0.5rem;
        background: white;
        border-radius: 0.25rem;
        border: 1px solid #e9ecef;
    }

    .student-list .form-check:hover {
        background-color: #e9ecef;
    }
</style>
@endpush
