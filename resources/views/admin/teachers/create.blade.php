@extends('layouts.app')

@section('title', 'Add Teacher')
@section('page-title', 'Add Teacher')

@section('content')
<div class="page-header">
    <h1><i class="fas fa-user-plus me-2"></i> Add New Teacher</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.teachers.index') }}">Teachers</a></li>
            <li class="breadcrumb-item active">Add New</li>
        </ol>
    </nav>
</div>

<form action="{{ route('admin.teachers.store') }}" method="POST">
    @csrf

    <div class="row">
        <!-- Account Information -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-user me-2"></i> Account Information
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text"
                               id="name"
                               name="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}"
                               style="text-transform: uppercase;"
                               required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email Address <span class="text-danger">*</span></label>
                        <input type="email"
                               name="email"
                               class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email') }}"
                               required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <select name="country_code" class="form-select" style="max-width: 120px;">
                                @foreach(config('country_codes.countries', []) as $country)
                                    <option value="{{ $country['code'] }}"
                                            {{ old('country_code', config('country_codes.default')) == $country['code'] ? 'selected' : '' }}>
                                        {{ $country['flag'] }} {{ $country['code'] }}
                                    </option>
                                @endforeach
                            </select>
                            <input type="tel"
                                   name="phone"
                                   class="form-control @error('phone') is-invalid @enderror"
                                   value="{{ old('phone') }}"
                                   placeholder="e.g., 123456789"
                                   required>
                        </div>
                        @error('phone')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password"
                                   name="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Minimum 8 characters</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                            <input type="password"
                                   name="password_confirmation"
                                   class="form-control"
                                   required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="on_leave" {{ old('status') == 'on_leave' ? 'selected' : '' }}>On Leave</option>
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
                        <input type="text"
                               id="ic_number"
                               name="ic_number"
                               class="form-control @error('ic_number') is-invalid @enderror"
                               value="{{ old('ic_number') }}"
                               placeholder="XXXXXX-XX-XXXX"
                               maxlength="14"
                               required>
                        <small class="text-muted">Format: 001005-10-1519 (12 digits)</small>
                        @error('ic_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Qualification</label>
                        <input type="text"
                               name="qualification"
                               class="form-control @error('qualification') is-invalid @enderror"
                               value="{{ old('qualification') }}"
                               placeholder="e.g., B.Ed Mathematics, USM">
                        @error('qualification')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Specialization <span class="text-danger">*</span></label>
                            <select class="form-select @error('specialization') is-invalid @enderror"
                                    id="specialization"
                                    name="specialization[]"
                                    multiple="multiple"
                                    required>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}"
                                            {{ in_array($subject->id, old('specialization', [])) ? 'selected' : '' }}>
                                        {{ $subject->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Select one or more subjects</small>
                            @error('specialization')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Experience (Years) <span class="text-danger">*</span></label>
                            <input type="number"
                                   name="experience_years"
                                   min="0"
                                   max="50"
                                   class="form-control @error('experience_years') is-invalid @enderror"
                                   value="{{ old('experience_years', 0) }}"
                                   required>
                            @error('experience_years')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea name="address"
                                  class="form-control @error('address') is-invalid @enderror"
                                  rows="2">{{ old('address') }}</textarea>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Bio</label>
                        <textarea name="bio"
                                  class="form-control @error('bio') is-invalid @enderror"
                                  rows="2">{{ old('bio') }}</textarea>
                        @error('bio')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Employment & Payment Details -->
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-briefcase me-2"></i> Employment & Payment Details
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Join Date <span class="text-danger">*</span></label>
                            <input type="date"
                                   name="join_date"
                                   class="form-control @error('join_date') is-invalid @enderror"
                                   value="{{ old('join_date', date('Y-m-d')) }}"
                                   required>
                            @error('join_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Employment Type <span class="text-danger">*</span></label>
                            <select name="employment_type" class="form-select @error('employment_type') is-invalid @enderror" required>
                                <option value="full_time" {{ old('employment_type') == 'full_time' ? 'selected' : '' }}>Full Time</option>
                                <option value="part_time" {{ old('employment_type', 'part_time') == 'part_time' ? 'selected' : '' }}>Part Time</option>
                                <option value="contract" {{ old('employment_type') == 'contract' ? 'selected' : '' }}>Contract</option>
                            </select>
                            @error('employment_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Pay Type <span class="text-danger">*</span></label>
                            <select name="pay_type" id="payType" class="form-select @error('pay_type') is-invalid @enderror" required onchange="togglePayFields()">
                                <option value="hourly" {{ old('pay_type') == 'hourly' ? 'selected' : '' }}>Hourly Rate</option>
                                <option value="monthly" {{ old('pay_type') == 'monthly' ? 'selected' : '' }}>Monthly Salary</option>
                                <option value="per_class" {{ old('pay_type', 'per_class') == 'per_class' ? 'selected' : '' }}>Per Class</option>
                            </select>
                            @error('pay_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3 mb-3" id="hourlyRateField">
                            <label class="form-label">Hourly Rate (RM)</label>
                            <input type="number"
                                   name="hourly_rate"
                                   step="0.01"
                                   min="0"
                                   class="form-control @error('hourly_rate') is-invalid @enderror"
                                   value="{{ old('hourly_rate') }}"
                                   placeholder="e.g., 50.00">
                            @error('hourly_rate')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3 mb-3 d-none" id="monthlySalaryField">
                            <label class="form-label">Monthly Salary (RM)</label>
                            <input type="number"
                                   name="monthly_salary"
                                   step="0.01"
                                   min="0"
                                   class="form-control @error('monthly_salary') is-invalid @enderror"
                                   value="{{ old('monthly_salary') }}"
                                   placeholder="e.g., 3000.00">
                            @error('monthly_salary')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3 mb-3 d-none" id="perClassRateField">
                            <label class="form-label">Per Class Rate (RM)</label>
                            <input type="number"
                                   name="per_class_rate"
                                   step="0.01"
                                   min="0"
                                   class="form-control @error('per_class_rate') is-invalid @enderror"
                                   value="{{ old('per_class_rate') }}"
                                   placeholder="e.g., 150.00">
                            @error('per_class_rate')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bank & Statutory Information -->
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-university me-2"></i> Bank & Statutory Information
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Bank Name</label>
                            <input type="text"
                                   name="bank_name"
                                   class="form-control @error('bank_name') is-invalid @enderror"
                                   value="{{ old('bank_name') }}"
                                   placeholder="e.g., Maybank">
                            @error('bank_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Bank Account</label>
                            <input type="text"
                                   name="bank_account"
                                   class="form-control @error('bank_account') is-invalid @enderror"
                                   value="{{ old('bank_account') }}"
                                   placeholder="e.g., 1234567890">
                            @error('bank_account')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">EPF Number</label>
                            <input type="text"
                                   name="epf_number"
                                   class="form-control @error('epf_number') is-invalid @enderror"
                                   value="{{ old('epf_number') }}">
                            @error('epf_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">SOCSO Number</label>
                            <input type="text"
                                   name="socso_number"
                                   class="form-control @error('socso_number') is-invalid @enderror"
                                   value="{{ old('socso_number') }}">
                            @error('socso_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="d-flex justify-content-end gap-2 mb-4">
        <a href="{{ route('admin.teachers.index') }}" class="btn btn-secondary">
            <i class="fas fa-times me-1"></i> Cancel
        </a>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-1"></i> Create Teacher
        </button>
    </div>
</form>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize Select2 for specialization
    $('#specialization').select2({
        theme: 'bootstrap-5',
        placeholder: 'Select subjects',
        allowClear: true,
        width: '100%'
    });

    // Auto-format IC number while typing
    $('#ic_number').on('input', function() {
        let value = $(this).val().replace(/[^0-9]/g, ''); // Remove non-numeric

        if (value.length > 12) {
            value = value.substr(0, 12);
        }

        // Format: XXXXXX-XX-XXXX
        let formatted = '';
        if (value.length > 0) {
            formatted = value.substr(0, 6);
            if (value.length >= 7) {
                formatted += '-' + value.substr(6, 2);
            }
            if (value.length >= 9) {
                formatted += '-' + value.substr(8, 4);
            }
        }

        $(this).val(formatted);
    });

    // Auto-convert name to uppercase
    $('#name').on('input', function() {
        $(this).val($(this).val().toUpperCase());
    });
});

function togglePayFields() {
    const payType = document.getElementById('payType').value;

    document.getElementById('hourlyRateField').classList.add('d-none');
    document.getElementById('monthlySalaryField').classList.add('d-none');
    document.getElementById('perClassRateField').classList.add('d-none');

    if (payType === 'hourly') {
        document.getElementById('hourlyRateField').classList.remove('d-none');
    } else if (payType === 'monthly') {
        document.getElementById('monthlySalaryField').classList.remove('d-none');
    } else if (payType === 'per_class') {
        document.getElementById('perClassRateField').classList.remove('d-none');
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    togglePayFields();
});
</script>
@endpush
