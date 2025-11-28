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

<form action="{{ route('admin.students.update', $student) }}" method="POST">
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
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $student->user->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
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
                        <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                               value="{{ old('phone', $student->user->phone) }}">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
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
                        <input type="text" name="ic_number" class="form-control @error('ic_number') is-invalid @enderror"
                               value="{{ old('ic_number', $student->ic_number) }}" required>
                        @error('ic_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Date of Birth <span class="text-danger">*</span></label>
                            <input type="date" name="date_of_birth" class="form-control @error('date_of_birth') is-invalid @enderror"
                                   value="{{ old('date_of_birth', $student->date_of_birth?->format('Y-m-d')) }}" required>
                            @error('date_of_birth')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Gender <span class="text-danger">*</span></label>
                            <select name="gender" class="form-select @error('gender') is-invalid @enderror" required>
                                <option value="">Select Gender</option>
                                <option value="male" {{ old('gender', $student->gender) == 'male' ? 'selected' : '' }}>Male</option>
                                <option value="female" {{ old('gender', $student->gender) == 'female' ? 'selected' : '' }}>Female</option>
                            </select>
                            @error('gender')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
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
                        <select name="parent_id" class="form-select @error('parent_id') is-invalid @enderror" required>
                            <option value="">Select Parent</option>
                            @foreach($parents as $parent)
                                <option value="{{ $parent->id }}" {{ old('parent_id', $student->parent_id) == $parent->id ? 'selected' : '' }}>
                                    {{ $parent->user->name }} ({{ $parent->ic_number }})
                                </option>
                            @endforeach
                        </select>
                        @error('parent_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Registration Type <span class="text-danger">*</span></label>
                        <select name="registration_type" class="form-select @error('registration_type') is-invalid @enderror" required>
                            <option value="offline" {{ old('registration_type', $student->registration_type) == 'offline' ? 'selected' : '' }}>Offline</option>
                            <option value="online" {{ old('registration_type', $student->registration_type) == 'online' ? 'selected' : '' }}>Online</option>
                        </select>
                        @error('registration_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
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
                                  rows="2">{{ old('notes', $student->notes) }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
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
