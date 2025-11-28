@extends('layouts.app')

@section('title', 'My Profile')
@section('page-title', 'My Profile')

@section('content')
<div class="page-header">
    <h1><i class="fas fa-user-circle me-2"></i> My Profile</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Profile</li>
        </ol>
    </nav>
</div>

<div class="row">
    <!-- Profile Card -->
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-body text-center">
                <div class="user-avatar mx-auto mb-3" style="width: 120px; height: 120px; font-size: 3rem;">
                    {{ substr($user->name, 0, 1) }}
                </div>
                <h4>{{ $user->name }}</h4>
                <p class="text-muted mb-2">
                    @foreach($user->roles as $role)
                        <span class="badge bg-primary">{{ ucfirst(str_replace('-', ' ', $role->name)) }}</span>
                    @endforeach
                </p>

                <hr>

                @if($user->status == 'active')
                    <span class="badge bg-success fs-6">Active Account</span>
                @else
                    <span class="badge bg-danger fs-6">Inactive Account</span>
                @endif

                <div class="mt-4">
                    <a href="{{ route('profile.edit') }}" class="btn btn-primary w-100 mb-2">
                        <i class="fas fa-edit me-1"></i> Edit Profile
                    </a>
                    <a href="{{ route('profile.change-password') }}" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-key me-1"></i> Change Password
                    </a>
                </div>
            </div>
        </div>

        <!-- Contact Information -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-address-book me-2"></i> Contact Information
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label text-muted small mb-1">Email</label>
                    <p class="mb-0">
                        {{ $user->email }}
                        @if($user->email_verified_at)
                            <span class="badge bg-success ms-1">Verified</span>
                        @else
                            <span class="badge bg-warning ms-1">Not Verified</span>
                        @endif
                    </p>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small mb-1">Phone</label>
                    <p class="mb-0">{{ $user->phone ?? 'Not provided' }}</p>
                </div>
                @if($profile && isset($profile->address))
                <div class="mb-0">
                    <label class="form-label text-muted small mb-1">Address</label>
                    <p class="mb-0">{{ $profile->address ?? 'Not provided' }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <!-- Account Information -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-user-shield me-2"></i> Account Information
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small mb-1">Account Created</label>
                        <p class="mb-0">{{ $user->created_at->format('d M Y, h:i A') }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small mb-1">Last Login</label>
                        <p class="mb-0">{{ $user->last_login_at?->format('d M Y, h:i A') ?? 'Never' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small mb-1">Last IP Address</label>
                        <p class="mb-0">{{ $user->last_login_ip ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small mb-1">Account Status</label>
                        <p class="mb-0">
                            @if($user->status == 'active')
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Inactive</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Role-Specific Information -->
        @if($profileType == 'staff' && $profile)
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-user-tie me-2"></i> Staff Information
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small mb-1">Staff ID</label>
                        <p class="mb-0"><strong>{{ $profile->staff_id }}</strong></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small mb-1">IC Number</label>
                        <p class="mb-0">{{ $profile->ic_number ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small mb-1">Position</label>
                        <p class="mb-0">{{ $profile->position ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small mb-1">Department</label>
                        <p class="mb-0">{{ $profile->department ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small mb-1">Join Date</label>
                        <p class="mb-0">{{ $profile->join_date?->format('d M Y') ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if($profileType == 'teacher' && $profile)
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-chalkboard-teacher me-2"></i> Teacher Information
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small mb-1">Teacher ID</label>
                        <p class="mb-0"><strong>{{ $profile->teacher_id }}</strong></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small mb-1">IC Number</label>
                        <p class="mb-0">{{ $profile->ic_number ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small mb-1">Specialization</label>
                        <p class="mb-0">{{ $profile->specialization ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small mb-1">Experience</label>
                        <p class="mb-0">{{ $profile->experience_years }} years</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small mb-1">Qualification</label>
                        <p class="mb-0">{{ $profile->qualification ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small mb-1">Employment Type</label>
                        <p class="mb-0">{{ ucfirst(str_replace('_', ' ', $profile->employment_type)) }}</p>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if($profileType == 'parent' && $profile)
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-user-friends me-2"></i> Parent Information
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small mb-1">Parent ID</label>
                        <p class="mb-0"><strong>{{ $profile->parent_id }}</strong></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small mb-1">IC Number</label>
                        <p class="mb-0">{{ $profile->ic_number ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small mb-1">Relationship</label>
                        <p class="mb-0">{{ ucfirst($profile->relationship) }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small mb-1">Occupation</label>
                        <p class="mb-0">{{ $profile->occupation ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if($profileType == 'student' && $profile)
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-user-graduate me-2"></i> Student Information
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small mb-1">Student ID</label>
                        <p class="mb-0"><strong>{{ $profile->student_id }}</strong></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small mb-1">IC Number</label>
                        <p class="mb-0">{{ $profile->ic_number ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small mb-1">School</label>
                        <p class="mb-0">{{ $profile->school_name ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small mb-1">Grade Level</label>
                        <p class="mb-0">{{ $profile->grade_level ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small mb-1">Date of Birth</label>
                        <p class="mb-0">{{ $profile->date_of_birth?->format('d M Y') ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small mb-1">Referral Code</label>
                        <p class="mb-0"><code>{{ $profile->referral_code }}</code></p>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
