@extends('layouts.app')

@section('title', 'Edit Student')
@section('page-title', 'Edit Student')

@section('content')
<div class="page-header">
    <h1><i class="fas fa-edit me-2"></i> Edit Student</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.students.index') }}">Students</a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol>
    </nav>
</div>

<form action="{{ route('admin.students.update', $student) }}" method="POST" id="studentForm">
    @csrf
    @method('PUT')

    <div class="row">
        <!-- Account Information -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-user me-2"></i> Account Information
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Student ID</label>
                        <input type="text" class="form-control" value="{{ $student->student_id }}" disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="studentName" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $student->user->name) }}" required style="text-transform: uppercase;">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Name will be automatically converted to UPPERCASE</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email Address <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email', $student->user->email) }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Phone Number</label>
                        <div class="row">
                            <div class="col-md-5">
                                <select name="country_code" id="country_code" class="form-select @error('country_code') is-invalid @enderror">
                                    @foreach($countries as $country)
                                        <option value="{{ $country['code'] }}"
                                            {{ old('country_code', $phoneData['country_code']) == $country['code'] ? 'selected' : '' }}>
                                            {{ $country['flag'] }} {{ $country['code'] }} {{ $country['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-7">
                                <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror"
                                       value="{{ old('phone', $phoneData['number']) }}" placeholder="e.g., 123456789">
                            </div>
                        </div>
                        @error('phone')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        @error('country_code')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Current Password</label>
                        <div class="input-group">
                            <input type="text" class="form-control" value="{{ $student->user->password_view ?? '••••••••' }}" disabled>
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordDisplay()" id="toggleBtn">
                                <i class="fas fa-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                        <small class="text-muted">Current password is displayed above. Enter new password below to change it.</small>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Leave blank to keep current</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" name="password_confirmation" class="form-control">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="active" {{ old('status', $student->user->status) == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $student->user->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Personal Information -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-id-card me-2"></i> Personal Information
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">IC Number <span class="text-danger">*</span></label>
                        <input type="text" name="ic_number" id="ic_number" class="form-control @error('ic_number') is-invalid @enderror"
                               value="{{ old('ic_number', substr($student->ic_number, 0, 6) . '-' . substr($student->ic_number, 6, 2) . '-' . substr($student->ic_number, 8, 4)) }}"
                               placeholder="001005-10-1519" maxlength="14" required>
                        @error('ic_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Format: YYMMDD-BP-XXXX (12 digits)</small>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Date of Birth <span class="text-danger">*</span></label>
                            <input type="date" name="date_of_birth" id="date_of_birth" class="form-control @error('date_of_birth') is-invalid @enderror"
                                   value="{{ old('date_of_birth', $student->date_of_birth?->format('Y-m-d')) }}" readonly required style="background-color: #e9ecef;">
                            @error('date_of_birth')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Auto-extracted from IC</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Gender <span class="text-danger">*</span></label>
                            <select name="gender" id="gender" class="form-select @error('gender') is-invalid @enderror" required style="background-color: #e9ecef;">
                                <option value="">Select Gender</option>
                                <option value="male" {{ old('gender', $student->gender) == 'male' ? 'selected' : '' }}>Male</option>
                                <option value="female" {{ old('gender', $student->gender) == 'female' ? 'selected' : '' }}>Female</option>
                            </select>
                            @error('gender')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Auto-detected from IC</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">School Name <span class="text-danger">*</span></label>
                        <input type="text" name="school_name" class="form-control @error('school_name') is-invalid @enderror"
                               value="{{ old('school_name', $student->school_name) }}" required>
                        @error('school_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Grade Level <span class="text-danger">*</span></label>
                        <select name="grade_level" class="form-select @error('grade_level') is-invalid @enderror" required>
                            <option value="">Select Grade</option>
                            @foreach(['Standard 1', 'Standard 2', 'Standard 3', 'Standard 4', 'Standard 5', 'Standard 6', 'Form 1', 'Form 2', 'Form 3', 'Form 4', 'Form 5'] as $grade)
                                <option value="{{ $grade }}" {{ old('grade_level', $student->grade_level) == $grade ? 'selected' : '' }}>{{ $grade }}</option>
                            @endforeach
                        </select>
                        @error('grade_level')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Parent & Registration -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-user-friends me-2"></i> Parent Information
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Parent <span class="text-danger">*</span></label>
                        <select name="parent_id" id="parent_id" class="form-select @error('parent_id') is-invalid @enderror" required>
                            @if($student->parent)
                                @php
                                    $parentIcFormatted = $student->parent->ic_number;
                                    if (strlen($student->parent->ic_number) === 12) {
                                        $parentIcFormatted = substr($student->parent->ic_number, 0, 6) . '-' .
                                                            substr($student->parent->ic_number, 6, 2) . '-' .
                                                            substr($student->parent->ic_number, 8, 4);
                                    }
                                @endphp
                                <option value="{{ $student->parent->id }}" selected>
                                    {{ $student->parent->user->name }} ({{ $parentIcFormatted }})
                                </option>
                            @else
                                <option value="">Search for a parent...</option>
                            @endif
                        </select>
                        @error('parent_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">
                            <i class="fas fa-search me-1"></i> Type to search by name, email, phone, or IC number
                        </small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Registration Type</label>
                        <input type="text" class="form-control" value="{{ ucfirst($student->registration_type) }}" disabled>
                        <small class="text-muted">Registration type cannot be changed</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Referral Code</label>
                        <input type="text" class="form-control" value="{{ $student->referral_code }}" disabled>
                        <small class="text-muted">Auto-generated, cannot be changed</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Information -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-info-circle me-2"></i> Additional Information
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control @error('address') is-invalid @enderror"
                                  rows="2">{{ old('address', $student->address) }}</textarea>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Medical Conditions</label>
                        <textarea name="medical_conditions" class="form-control @error('medical_conditions') is-invalid @enderror"
                                  rows="2">{{ old('medical_conditions', $student->medical_conditions) }}</textarea>
                        @error('medical_conditions')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control @error('notes') is-invalid @enderror"
                                  rows="2" placeholder="Internal notes (visible only on student view page)">{{ old('notes', $student->notes) }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">This information will only be visible on the student view page.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mb-4">
        <a href="{{ route('admin.students.index') }}" class="btn btn-secondary">
            <i class="fas fa-times me-1"></i> Cancel
        </a>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-1"></i> Update Student
        </button>
    </div>
</form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Select2 for Parent dropdown with AJAX
    $('#parent_id').select2({
        theme: 'bootstrap-5',
        placeholder: 'Search for a parent...',
        allowClear: true,
        minimumInputLength: 0,
        ajax: {
            url: '{{ route("admin.students.search-parents") }}',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    q: params.term,
                    page: params.page || 1
                };
            },
            processResults: function (data, params) {
                params.page = params.page || 1;
                return {
                    results: data.results,
                    pagination: {
                        more: data.pagination.more
                    }
                };
            },
            cache: true
        },
        templateResult: formatParentResult,
        templateSelection: formatParentSelection
    });

    // Format parent result in dropdown
    function formatParentResult(parent) {
        if (parent.loading) {
            return parent.text;
        }

        var $container = $(
            '<div class="select2-result-parent">' +
                '<div class="select2-result-parent__name">' + parent.name + '</div>' +
                '<div class="select2-result-parent__meta">' +
                    '<span class="select2-result-parent__ic">IC: ' + parent.ic_number + '</span>' +
                    (parent.phone ? ' | <span class="select2-result-parent__phone">' + parent.phone + '</span>' : '') +
                '</div>' +
            '</div>'
        );

        return $container;
    }

    // Format selected parent
    function formatParentSelection(parent) {
        if (parent.id) {
            return parent.name ? parent.name + ' (' + parent.ic_number + ')' : parent.text;
        }
        return parent.text;
    }

    // IC Number formatting and auto-fill
    const icInput = document.getElementById('ic_number');
    const dobInput = document.getElementById('date_of_birth');
    const genderSelect = document.getElementById('gender');
    const nameInput = document.getElementById('studentName');

    // Format IC number as user types
    icInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/[^0-9]/g, ''); // Remove non-digits

        // Limit to 12 digits
        if (value.length > 12) {
            value = value.substring(0, 12);
        }

        // Format with hyphens: YYMMDD-BP-XXXX
        let formatted = '';
        if (value.length > 0) {
            formatted = value.substring(0, 6);
            if (value.length > 6) {
                formatted += '-' + value.substring(6, 8);
            }
            if (value.length > 8) {
                formatted += '-' + value.substring(8, 12);
            }
        }

        e.target.value = formatted;

        // Auto-extract DOB and Gender when IC is complete
        if (value.length === 12) {
            extractDOBAndGender(value);
        }
    });

    // Extract Date of Birth and Gender from IC Number
    function extractDOBAndGender(icNumber) {
        // Extract YYMMDD from first 6 digits
        const year = icNumber.substring(0, 2);
        const month = icNumber.substring(2, 4);
        const day = icNumber.substring(4, 6);

        // Determine century (00-25 = 2000s, 26-99 = 1900s)
        const fullYear = (parseInt(year) <= 25) ? '20' + year : '19' + year;

        // Set date of birth
        dobInput.value = fullYear + '-' + month + '-' + day;

        // Extract gender from last digit (odd = male, even = female)
        const lastDigit = parseInt(icNumber.substring(11, 12));
        if (lastDigit % 2 === 0) {
            genderSelect.value = 'female';
        } else {
            genderSelect.value = 'male';
        }
    }

    // Auto-uppercase name field
    nameInput.addEventListener('input', function(e) {
        e.target.value = e.target.value.toUpperCase();
    });

    // Prevent manual changes to DOB and Gender
    dobInput.addEventListener('click', function(e) {
        alert('Date of birth will be automatically extracted from IC Number.');
    });

    genderSelect.addEventListener('focus', function(e) {
        alert('Gender will be automatically detected from IC Number.');
    });
});

// Toggle password display
let passwordVisible = false;
const originalPassword = '{{ $student->user->password_view ?? "" }}';

function togglePasswordDisplay() {
    const passwordInput = document.querySelector('input[value="{{ $student->user->password_view ?? '••••••••' }}"]');
    const toggleIcon = document.getElementById('toggleIcon');

    if (!passwordVisible && originalPassword) {
        passwordInput.value = originalPassword;
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
        passwordVisible = true;
    } else {
        passwordInput.value = '••••••••';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
        passwordVisible = false;
    }
}
</script>

<style>
/* Custom Select2 styling for parent results */
.select2-result-parent {
    padding: 5px 0;
}

.select2-result-parent__name {
    font-weight: 600;
    color: #333;
    margin-bottom: 3px;
}

.select2-result-parent__meta {
    font-size: 0.875rem;
    color: #666;
}

.select2-result-parent__ic {
    font-family: monospace;
}

.select2-result-parent__phone {
    color: #667eea;
}
</style>
@endpush
