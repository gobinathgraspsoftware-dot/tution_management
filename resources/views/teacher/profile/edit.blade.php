@extends('layouts.app')

@section('title', 'Edit Profile')
@section('page-title', 'Edit Profile')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="fas fa-user-edit me-2"></i> Edit Profile</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('teacher.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('teacher.profile.index') }}">My Profile</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Personal Information Form -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-user me-2"></i> Personal Information
            </div>
            <div class="card-body">
                <form action="{{ route('teacher.profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $teacher->user->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email', $teacher->user->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                                   value="{{ old('phone', $teacher->user->phone) }}" required>
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Profile Photo</label>
                            <input type="file" name="profile_photo" class="form-control @error('profile_photo') is-invalid @enderror"
                                   accept="image/jpeg,image/png,image/jpg">
                            <small class="text-muted">Max 2MB. Allowed: JPG, JPEG, PNG</small>
                            @error('profile_photo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 mb-3">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control @error('address') is-invalid @enderror"
                                      rows="2">{{ old('address', $teacher->address) }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 mb-3">
                            <label class="form-label">Qualification</label>
                            <input type="text" name="qualification" class="form-control @error('qualification') is-invalid @enderror"
                                   value="{{ old('qualification', $teacher->qualification) }}">
                            @error('qualification')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 mb-3">
                            <label class="form-label">Bio</label>
                            <textarea name="bio" class="form-control @error('bio') is-invalid @enderror"
                                      rows="4" maxlength="1000">{{ old('bio', $teacher->bio) }}</textarea>
                            <small class="text-muted">Max 1000 characters</small>
                            @error('bio')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="text-end">
                        <a href="{{ route('teacher.profile.index') }}" class="btn btn-secondary me-2">
                            <i class="fas fa-times me-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Change Password Form -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-lock me-2"></i> Change Password
            </div>
            <div class="card-body">
                <form action="{{ route('teacher.profile.password') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Current Password <span class="text-danger">*</span></label>
                            <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" required>
                            @error('current_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">New Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required minlength="8">
                            <small class="text-muted">Minimum 8 characters</small>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Confirm New Password <span class="text-danger">*</span></label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-key me-1"></i> Change Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Current Profile Photo -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-image me-2"></i> Current Photo
            </div>
            <div class="card-body text-center">
                @if($teacher->user->profile_photo)
                    <img src="{{ Storage::url($teacher->user->profile_photo) }}"
                         alt="Profile Photo"
                         class="rounded-circle mb-3"
                         style="width: 150px; height: 150px; object-fit: cover;">
                @else
                    <div class="user-avatar mx-auto mb-3"
                         style="width: 150px; height: 150px; font-size: 4rem; background: linear-gradient(135deg, #4caf50 0%, #2e7d32 100%);">
                        {{ substr($teacher->user->name, 0, 1) }}
                    </div>
                @endif
                <p class="text-muted mb-0">{{ $teacher->user->name }}</p>
                <small class="text-muted">{{ $teacher->teacher_id }}</small>
            </div>
        </div>

        <!-- Profile Completion -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-tasks me-2"></i> Profile Completion
            </div>
            <div class="card-body">
                @php
                    $completionItems = [
                        'name' => !empty($teacher->user->name),
                        'email' => !empty($teacher->user->email),
                        'phone' => !empty($teacher->user->phone),
                        'address' => !empty($teacher->address),
                        'qualification' => !empty($teacher->qualification),
                        'bio' => !empty($teacher->bio),
                        'photo' => !empty($teacher->user->profile_photo),
                    ];
                    $completedCount = count(array_filter($completionItems));
                    $totalCount = count($completionItems);
                    $completionPercentage = round(($completedCount / $totalCount) * 100);
                @endphp

                <div class="progress mb-3" style="height: 20px;">
                    <div class="progress-bar bg-{{ $completionPercentage >= 80 ? 'success' : ($completionPercentage >= 50 ? 'warning' : 'danger') }}"
                         role="progressbar"
                         style="width: {{ $completionPercentage }}%">
                        {{ $completionPercentage }}%
                    </div>
                </div>

                <ul class="list-group list-group-flush">
                    @foreach($completionItems as $item => $isComplete)
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            {{ ucfirst($item) }}
                            @if($isComplete)
                                <i class="fas fa-check-circle text-success"></i>
                            @else
                                <i class="fas fa-times-circle text-danger"></i>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <!-- Read-Only Information -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-info-circle me-2"></i> Account Information
            </div>
            <div class="card-body">
                <p class="mb-2"><strong>Teacher ID:</strong> {{ $teacher->teacher_id }}</p>
                <p class="mb-2"><strong>Join Date:</strong> {{ $teacher->join_date ? $teacher->join_date->format('d M Y') : 'N/A' }}</p>
                <p class="mb-2"><strong>Employment:</strong> {{ ucfirst(str_replace('_', ' ', $teacher->employment_type)) }}</p>
                <p class="mb-0"><strong>Status:</strong>
                    <span class="badge bg-{{ $teacher->status == 'active' ? 'success' : 'secondary' }}">
                        {{ ucfirst($teacher->status) }}
                    </span>
                </p>
                <hr>
                <small class="text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    Contact admin to update account information.
                </small>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.user-avatar {
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    color: white;
    font-weight: bold;
}
</style>
@endpush
