@extends('layouts.app')

@section('title', 'Edit Staff')
@section('page-title', 'Edit Staff')

@section('content')
<div class="page-header">
    <h1><i class="fas fa-edit me-2"></i> Edit Staff</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.staff.index') }}">Staff</a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol>
    </nav>
</div>

<form action="{{ route('admin.staff.update', $staff) }}" method="POST">
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
                        <label class="form-label">Staff ID</label>
                        <input type="text" class="form-control" value="{{ $staff->staff_id }}" disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                               value="{{ old('name', $staff->user->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email Address <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                               value="{{ old('email', $staff->user->email) }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                        <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" 
                               value="{{ old('phone', $staff->user->phone) }}" required>
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
                            <small class="text-muted">Leave blank to keep current password</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" name="password_confirmation" class="form-control">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="active" {{ old('status', $staff->user->status) == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $staff->user->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
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
                               value="{{ old('ic_number', $staff->ic_number) }}" required>
                        @error('ic_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control @error('address') is-invalid @enderror" 
                                  rows="3">{{ old('address', $staff->address) }}</textarea>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Emergency Contact</label>
                            <input type="text" name="emergency_contact" class="form-control @error('emergency_contact') is-invalid @enderror" 
                                   value="{{ old('emergency_contact', $staff->emergency_contact) }}">
                            @error('emergency_contact')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Emergency Phone</label>
                            <input type="text" name="emergency_phone" class="form-control @error('emergency_phone') is-invalid @enderror" 
                                   value="{{ old('emergency_phone', $staff->emergency_phone) }}">
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
                                   value="{{ old('position', $staff->position) }}" required>
                            @error('position')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Department <span class="text-danger">*</span></label>
                            <input type="text" name="department" class="form-control @error('department') is-invalid @enderror" 
                                   value="{{ old('department', $staff->department) }}" required>
                            @error('department')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Join Date <span class="text-danger">*</span></label>
                            <input type="date" name="join_date" class="form-control @error('join_date') is-invalid @enderror" 
                                   value="{{ old('join_date', $staff->join_date?->format('Y-m-d')) }}" required>
                            @error('join_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Salary (RM)</label>
                            <input type="number" name="salary" step="0.01" min="0" class="form-control @error('salary') is-invalid @enderror" 
                                   value="{{ old('salary', $staff->salary) }}">
                            @error('salary')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" 
                                  rows="2">{{ old('notes', $staff->notes) }}</textarea>
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
            <i class="fas fa-save me-1"></i> Update Staff
        </button>
    </div>
</form>
@endsection
