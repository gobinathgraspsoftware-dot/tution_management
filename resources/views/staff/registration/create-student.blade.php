@extends('layouts.app')

@section('title', 'Register Student')
@section('page-title', 'Register Student')

@section('content')
<div class="page-header">
    <h1><i class="fas fa-user-graduate me-2"></i> Register New Student</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('staff.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Register Student</li>
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

<form action="{{ route('staff.registration.store-student') }}" method="POST">
    @csrf

    <div class="row">
        <!-- Student Information -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-user me-2"></i> Student Information
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}" placeholder="Student's full name" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email Address <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email') }}" placeholder="student@example.com" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                               value="{{ old('phone') }}" placeholder="Optional">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">IC Number <span class="text-danger">*</span></label>
                        <input type="text" name="ic_number" class="form-control @error('ic_number') is-invalid @enderror"
                               value="{{ old('ic_number') }}" placeholder="e.g., 100101-01-1234" required>
                        @error('ic_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Date of Birth <span class="text-danger">*</span></label>
                            <input type="date" name="date_of_birth" class="form-control @error('date_of_birth') is-invalid @enderror"
                                   value="{{ old('date_of_birth') }}" required>
                            @error('date_of_birth')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Gender <span class="text-danger">*</span></label>
                            <select name="gender" class="form-select @error('gender') is-invalid @enderror" required>
                                <option value="">Select Gender</option>
                                <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                                <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                            </select>
                            @error('gender')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- School & Parent Information -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <i class="fas fa-school me-2"></i> School Information
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">School Name <span class="text-danger">*</span></label>
                        <input type="text" name="school_name" class="form-control @error('school_name') is-invalid @enderror"
                               value="{{ old('school_name') }}" required>
                        @error('school_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Grade Level <span class="text-danger">*</span></label>
                        <select name="grade_level" class="form-select @error('grade_level') is-invalid @enderror" required>
                            <option value="">Select Grade</option>
                            @foreach(['Standard 1', 'Standard 2', 'Standard 3', 'Standard 4', 'Standard 5', 'Standard 6', 'Form 1', 'Form 2', 'Form 3', 'Form 4', 'Form 5'] as $grade)
                                <option value="{{ $grade }}" {{ old('grade_level') == $grade ? 'selected' : '' }}>{{ $grade }}</option>
                            @endforeach
                        </select>
                        @error('grade_level')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-warning">
                    <i class="fas fa-user-friends me-2"></i> Parent Selection
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Select Parent <span class="text-danger">*</span></label>
                        <select name="parent_id" class="form-select @error('parent_id') is-invalid @enderror" required>
                            <option value="">Select Parent</option>
                            @foreach($parents as $parent)
                                <option value="{{ $parent->id }}" {{ old('parent_id') == $parent->id ? 'selected' : '' }}>
                                    {{ $parent->user->name }} - {{ $parent->user->phone }} ({{ $parent->ic_number }})
                                </option>
                            @endforeach
                        </select>
                        @error('parent_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="text-end">
                        <a href="{{ route('staff.registration.create-parent') }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-plus me-1"></i> Register New Parent First
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Information -->
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-info-circle me-2"></i> Additional Information
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control @error('address') is-invalid @enderror"
                                      rows="2">{{ old('address') }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Medical Conditions</label>
                            <textarea name="medical_conditions" class="form-control @error('medical_conditions') is-invalid @enderror"
                                      rows="2" placeholder="Any allergies or medical conditions...">{{ old('medical_conditions') }}</textarea>
                            @error('medical_conditions')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control @error('notes') is-invalid @enderror"
                                  rows="2" placeholder="Any additional notes...">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="alert alert-info">
        <i class="fas fa-info-circle me-2"></i>
        <strong>Note:</strong> A temporary password will be generated automatically. Please provide the password to the parent after registration.
        The student will require admin approval before becoming fully active.
    </div>

    <div class="d-flex justify-content-end gap-2 mb-4">
        <a href="{{ route('staff.dashboard') }}" class="btn btn-secondary">
            <i class="fas fa-times me-1"></i> Cancel
        </a>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-1"></i> Register Student
        </button>
    </div>
</form>
@endsection
