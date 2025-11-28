@extends('layouts.app')

@section('title', 'Register Parent')
@section('page-title', 'Register Parent')

@section('content')
<div class="page-header">
    <h1><i class="fas fa-user-friends me-2"></i> Register New Parent</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('staff.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Register Parent</li>
        </ol>
    </nav>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fas fa-check-circle me-2"></i>
    {!! session('success') !!}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<form action="{{ route('staff.registration.store-parent') }}" method="POST">
    @csrf

    <div class="row">
        <!-- Account Information -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-user me-2"></i> Account Information
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}" placeholder="Parent's full name" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email Address <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email') }}" placeholder="parent@example.com" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                        <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                               value="{{ old('phone') }}" placeholder="e.g., 0123456789" required>
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">IC Number <span class="text-danger">*</span></label>
                        <input type="text" name="ic_number" class="form-control @error('ic_number') is-invalid @enderror"
                               value="{{ old('ic_number') }}" placeholder="e.g., 880101-01-1234" required>
                        @error('ic_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Relationship <span class="text-danger">*</span></label>
                            <select name="relationship" class="form-select @error('relationship') is-invalid @enderror" required>
                                <option value="">Select</option>
                                <option value="father" {{ old('relationship') == 'father' ? 'selected' : '' }}>Father</option>
                                <option value="mother" {{ old('relationship') == 'mother' ? 'selected' : '' }}>Mother</option>
                                <option value="guardian" {{ old('relationship') == 'guardian' ? 'selected' : '' }}>Guardian</option>
                            </select>
                            @error('relationship')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Occupation</label>
                            <input type="text" name="occupation" class="form-control @error('occupation') is-invalid @enderror"
                                   value="{{ old('occupation') }}">
                            @error('occupation')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">WhatsApp Number</label>
                        <input type="text" name="whatsapp_number" class="form-control @error('whatsapp_number') is-invalid @enderror"
                               value="{{ old('whatsapp_number') }}" placeholder="e.g., 60123456789">
                        @error('whatsapp_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Include country code without + sign</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Address Information -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <i class="fas fa-map-marker-alt me-2"></i> Address Information
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Address <span class="text-danger">*</span></label>
                        <textarea name="address" class="form-control @error('address') is-invalid @enderror"
                                  rows="2" required>{{ old('address') }}</textarea>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">City <span class="text-danger">*</span></label>
                            <input type="text" name="city" class="form-control @error('city') is-invalid @enderror"
                                   value="{{ old('city') }}" required>
                            @error('city')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Postcode <span class="text-danger">*</span></label>
                            <input type="text" name="postcode" class="form-control @error('postcode') is-invalid @enderror"
                                   value="{{ old('postcode') }}" required>
                            @error('postcode')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">State <span class="text-danger">*</span></label>
                        <select name="state" class="form-select @error('state') is-invalid @enderror" required>
                            <option value="">Select State</option>
                            @foreach(['Johor', 'Kedah', 'Kelantan', 'Melaka', 'Negeri Sembilan', 'Pahang', 'Perak', 'Perlis', 'Pulau Pinang', 'Sabah', 'Sarawak', 'Selangor', 'Terengganu', 'Kuala Lumpur', 'Labuan', 'Putrajaya'] as $state)
                                <option value="{{ $state }}" {{ old('state') == $state ? 'selected' : '' }}>{{ $state }}</option>
                            @endforeach
                        </select>
                        @error('state')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-warning">
                    <i class="fas fa-phone-alt me-2"></i> Emergency Contact
                </div>
                <div class="card-body">
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
                        <input type="text" name="emergency_phone" class="form-control @error('emergency_phone') is-invalid @enderror"
                               value="{{ old('emergency_phone') }}">
                        @error('emergency_phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="alert alert-info">
        <i class="fas fa-info-circle me-2"></i>
        <strong>Note:</strong> A temporary password will be generated automatically. Please provide the Parent ID and password to the parent after registration.
    </div>

    <div class="d-flex justify-content-end gap-2 mb-4">
        <a href="{{ route('staff.dashboard') }}" class="btn btn-secondary">
            <i class="fas fa-times me-1"></i> Cancel
        </a>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-1"></i> Register Parent
        </button>
    </div>
</form>
@endsection
