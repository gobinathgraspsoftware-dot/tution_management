@extends('layouts.app')

@section('title', 'Add Staff')
@section('page-title', 'Add Staff')

@section('content')
<div class="page-header">
    <h1><i class="fas fa-user-plus me-2"></i> Add New Staff</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.staff.index') }}">Staff</a></li>
            <li class="breadcrumb-item active">Add New</li>
        </ol>
    </nav>
</div>

<form action="{{ route('admin.staff.store') }}" method="POST" id="staffForm">
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
                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" 
                               value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Name will be automatically converted to UPPERCASE</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email Address <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                               value="{{ old('email') }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <select name="country_code" class="form-select @error('country_code') is-invalid @enderror" 
                                    style="max-width: 120px;" required>
                                @foreach($countries as $country)
                                    <option value="{{ $country['code'] }}" 
                                            {{ old('country_code', $defaultCountryCode) == $country['code'] ? 'selected' : '' }}>
                                        {{ $country['code'] }} {{ $country['name'] }}
                                    </option>
                                @endforeach
                            </select>
                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" 
                                   value="{{ old('phone') }}" placeholder="e.g., 0123456789" required>
                            @error('country_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Minimum 8 characters</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
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
                        <input type="text" name="ic_number" id="ic_number" 
                               class="form-control @error('ic_number') is-invalid @enderror" 
                               value="{{ old('ic_number') }}" 
                               placeholder="e.g., 001005-10-1519" 
                               maxlength="14"
                               required>
                        @error('ic_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Format: YYMMDD-PB-XXXX (12 digits with hyphens)</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control @error('address') is-invalid @enderror" 
                                  rows="3">{{ old('address') }}</textarea>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Emergency Contact Name</label>
                        <input type="text" name="emergency_contact" class="form-control @error('emergency_contact') is-invalid @enderror" 
                               value="{{ old('emergency_contact') }}">
                        @error('emergency_contact')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Emergency Contact Phone</label>
                        <div class="input-group">
                            <select name="emergency_country_code" class="form-select" style="max-width: 120px;">
                                @foreach($countries as $country)
                                    <option value="{{ $country['code'] }}" 
                                            {{ old('emergency_country_code', $defaultCountryCode) == $country['code'] ? 'selected' : '' }}>
                                        {{ $country['code'] }} {{ $country['name'] }}
                                    </option>
                                @endforeach
                            </select>
                            <input type="text" name="emergency_phone" class="form-control @error('emergency_phone') is-invalid @enderror" 
                                   value="{{ old('emergency_phone') }}" placeholder="e.g., 0123456789">
                            @error('emergency_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Employment Information -->
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-briefcase me-2"></i> Employment Information
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Position <span class="text-danger">*</span></label>
                            <input type="text" name="position" class="form-control @error('position') is-invalid @enderror" 
                                   value="{{ old('position') }}" placeholder="e.g., Administrative Staff" required>
                            @error('position')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Department <span class="text-danger">*</span></label>
                            <input type="text" name="department" class="form-control @error('department') is-invalid @enderror" 
                                   value="{{ old('department') }}" placeholder="e.g., Administration" required>
                            @error('department')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Join Date <span class="text-danger">*</span></label>
                            <input type="date" name="join_date" class="form-control @error('join_date') is-invalid @enderror" 
                                   value="{{ old('join_date', date('Y-m-d')) }}" required>
                            @error('join_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Salary (RM)</label>
                            <input type="number" name="salary" step="0.01" min="0" class="form-control @error('salary') is-invalid @enderror" 
                                   value="{{ old('salary') }}" placeholder="e.g., 2500.00">
                            @error('salary')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" 
                                  rows="2" placeholder="Any additional notes about this staff member...">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="d-flex justify-content-end gap-2 mb-4">
        <a href="{{ route('admin.staff.index') }}" class="btn btn-secondary">
            <i class="fas fa-times me-1"></i> Cancel
        </a>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-1"></i> Create Staff
        </button>
    </div>
</form>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-convert name to UPPERCASE
    $('#name').on('input', function() {
        this.value = this.value.toUpperCase();
    });

    // IC Number auto-formatting (12 digits with hyphens: YYMMDD-PB-XXXX)
    $('#ic_number').on('input', function() {
        let value = this.value.replace(/[^0-9]/g, ''); // Remove all non-numeric characters
        let formatted = '';
        
        // Format: YYMMDD-PB-XXXX
        if (value.length > 0) {
            formatted = value.substring(0, 6); // First 6 digits (YYMMDD)
            
            if (value.length > 6) {
                formatted += '-' + value.substring(6, 8); // Next 2 digits (PB)
            }
            
            if (value.length > 8) {
                formatted += '-' + value.substring(8, 12); // Last 4 digits (XXXX)
            }
        }
        
        this.value = formatted;
    });

    // Form validation
    $('#staffForm').on('submit', function(e) {
        // Validate IC number has exactly 12 digits
        let icNumber = $('#ic_number').val().replace(/[^0-9]/g, '');
        if (icNumber.length !== 12) {
            e.preventDefault();
            alert('IC Number must be exactly 12 digits');
            $('#ic_number').focus();
            return false;
        }
    });
});
</script>
@endpush
