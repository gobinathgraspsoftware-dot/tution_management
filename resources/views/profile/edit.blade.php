@extends('layouts.app')

@section('title', 'Edit Profile')
@section('page-title', 'Edit Profile')

@section('content')
<div class="page-header">
    <h1><i class="fas fa-edit me-2"></i> Edit Profile</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('profile.index') }}">Profile</a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol>
    </nav>
</div>

<form action="{{ route('profile.update') }}" method="POST">
    @csrf
    @method('PUT')

    <div class="row">
        <!-- Basic Information -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-user me-2"></i> Basic Information
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $user->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email Address <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email', $user->email) }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                               value="{{ old('phone', $user->phone) }}">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Role-Specific Fields -->
        @if($profileType == 'staff' && $profile)
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-user-tie me-2"></i> Staff Details
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Staff ID</label>
                        <input type="text" class="form-control" value="{{ $profile->staff_id }}" disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control @error('address') is-invalid @enderror"
                                  rows="3">{{ old('address', $profile->address) }}</textarea>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if($profileType == 'teacher' && $profile)
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chalkboard-teacher me-2"></i> Teacher Details
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Teacher ID</label>
                        <input type="text" class="form-control" value="{{ $profile->teacher_id }}" disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control @error('address') is-invalid @enderror"
                                  rows="2">{{ old('address', $profile->address) }}</textarea>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Bio</label>
                        <textarea name="bio" class="form-control @error('bio') is-invalid @enderror"
                                  rows="3">{{ old('bio', $profile->bio) }}</textarea>
                        @error('bio')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if($profileType == 'parent' && $profile)
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-user-friends me-2"></i> Parent Details
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Parent ID</label>
                        <input type="text" class="form-control" value="{{ $profile->parent_id }}" disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Occupation</label>
                        <input type="text" name="occupation" class="form-control @error('occupation') is-invalid @enderror"
                               value="{{ old('occupation', $profile->occupation) }}">
                        @error('occupation')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">WhatsApp Number</label>
                        <input type="text" name="whatsapp_number" class="form-control @error('whatsapp_number') is-invalid @enderror"
                               value="{{ old('whatsapp_number', $profile->whatsapp_number) }}">
                        @error('whatsapp_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control @error('address') is-invalid @enderror"
                                  rows="2">{{ old('address', $profile->address) }}</textarea>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-bell me-2"></i> Notification Preferences
                </div>
                <div class="card-body">
                    @php
                        $notifPrefs = $profile->notification_preference ?? [];
                    @endphp
                    <div class="form-check mb-2">
                        <input type="checkbox" name="notification_preference[whatsapp]" class="form-check-input"
                               id="notif_whatsapp" value="1" {{ old('notification_preference.whatsapp', $notifPrefs['whatsapp'] ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label" for="notif_whatsapp">WhatsApp Notifications</label>
                    </div>
                    <div class="form-check mb-2">
                        <input type="checkbox" name="notification_preference[email]" class="form-check-input"
                               id="notif_email" value="1" {{ old('notification_preference.email', $notifPrefs['email'] ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label" for="notif_email">Email Notifications</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="notification_preference[sms]" class="form-check-input"
                               id="notif_sms" value="1" {{ old('notification_preference.sms', $notifPrefs['sms'] ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label" for="notif_sms">SMS Notifications</label>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if($profileType == 'student' && $profile)
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-user-graduate me-2"></i> Student Details
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Student ID</label>
                        <input type="text" class="form-control" value="{{ $profile->student_id }}" disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control @error('address') is-invalid @enderror"
                                  rows="3">{{ old('address', $profile->address) }}</textarea>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if($profileType == 'admin')
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-user-shield me-2"></i> Admin Account
                </div>
                <div class="card-body">
                    <p class="text-muted">You are logged in as an administrator. Your profile is managed through the admin settings.</p>
                </div>
            </div>
        </div>
        @endif
    </div>

    <div class="d-flex justify-content-end gap-2 mb-4">
        <a href="{{ route('profile.index') }}" class="btn btn-secondary">
            <i class="fas fa-times me-1"></i> Cancel
        </a>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-1"></i> Save Changes
        </button>
    </div>
</form>
@endsection
