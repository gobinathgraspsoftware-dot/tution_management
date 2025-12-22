@extends('layouts.app')

@section('title', 'Staff Details')
@section('page-title', 'Staff Details')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="fas fa-user-tie me-2"></i> Staff Details</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.staff.index') }}">Staff</a></li>
                <li class="breadcrumb-item active">{{ $staff->user->name }}</li>
            </ol>
        </nav>
    </div>
    <div>
        @can('edit-staff')
        <a href="{{ route('admin.staff.edit', $staff) }}" class="btn btn-primary">
            <i class="fas fa-edit me-1"></i> Edit
        </a>
        @endcan
    </div>
</div>

<div class="row">
    <!-- Profile Card -->
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-body text-center">
                <div class="user-avatar mx-auto mb-3" style="width: 100px; height: 100px; font-size: 2.5rem;">
                    {{ substr($staff->user->name, 0, 1) }}
                </div>
                <h4>{{ $staff->user->name }}</h4>
                <p class="text-muted mb-2">{{ $staff->position }}</p>
                <span class="badge bg-secondary">{{ $staff->staff_id }}</span>
                
                <hr>
                
                @if($staff->user->status == 'active')
                    <span class="badge bg-success fs-6">Active</span>
                @else
                    <span class="badge bg-danger fs-6">Inactive</span>
                @endif
                
                @if($staff->user->roles->isNotEmpty())
                    <div class="mt-2">
                        <small class="text-muted d-block mb-1">Role</small>
                        <span class="badge bg-primary fs-6">
                            {{ ucwords(str_replace('-', ' ', $staff->user->roles->first()->name)) }}
                        </span>
                    </div>
                @endif
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
                        <a href="mailto:{{ $staff->user->email }}">{{ $staff->user->email }}</a>
                    </p>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small mb-1">Phone</label>
                    <p class="mb-0">
                        <a href="tel:{{ $staff->user->phone }}">{{ $staff->user->phone }}</a>
                    </p>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small mb-1">Address</label>
                    <p class="mb-0">{{ $staff->address ?? 'N/A' }}</p>
                </div>
                @if($staff->emergency_contact)
                <div class="mb-0">
                    <label class="form-label text-muted small mb-1">Emergency Contact</label>
                    <p class="mb-0">
                        {{ $staff->emergency_contact }}
                        @if($staff->emergency_phone)
                            <br><a href="tel:{{ $staff->emergency_phone }}">{{ $staff->emergency_phone }}</a>
                        @endif
                    </p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Details -->
    <div class="col-md-8">
        <!-- Personal Information -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-id-card me-2"></i> Personal Information
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small mb-1">IC Number</label>
                        <p class="mb-0">{{ $staff->formatted_ic_number }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small mb-1">Join Date</label>
                        <p class="mb-0">{{ $staff->join_date?->format('d M Y') ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Employment Information -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-briefcase me-2"></i> Employment Information
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label text-muted small mb-1">Position</label>
                        <p class="mb-0"><strong>{{ $staff->position ?? 'N/A' }}</strong></p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label text-muted small mb-1">Department</label>
                        <p class="mb-0">{{ $staff->department ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label text-muted small mb-1">Salary</label>
                        <p class="mb-0">{{ $staff->salary ? 'RM ' . number_format($staff->salary, 2) : 'N/A' }}</p>
                    </div>
                </div>
                @if($staff->notes)
                <div class="mt-3">
                    <label class="form-label text-muted small mb-1">Notes</label>
                    <p class="mb-0">{{ $staff->notes }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Account Information -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-user-shield me-2"></i> Account Information
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label text-muted small mb-1">Assigned Role</label>
                        <p class="mb-0">
                            @if($staff->user->roles->isNotEmpty())
                                <span class="badge bg-primary">
                                    {{ ucwords(str_replace('-', ' ', $staff->user->roles->first()->name)) }}
                                </span>
                            @else
                                <span class="text-muted">No role assigned</span>
                            @endif
                        </p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label text-muted small mb-1">Password</label>
                        <p class="mb-0">
                            @if($staff->user->password_view)
                                <span class="badge bg-info">{{ $staff->user->password_view }}</span>
                            @else
                                <span class="text-muted">Not available</span>
                            @endif
                        </p>
                        <small class="text-muted">Current password for viewing only</small>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label text-muted small mb-1">Last Login</label>
                        <p class="mb-0">{{ $staff->user->last_login_at?->format('d M Y, h:i A') ?? 'Never' }}</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label text-muted small mb-1">Email Verified</label>
                        <p class="mb-0">
                            @if($staff->user->email_verified_at)
                                <span class="badge bg-success">Verified</span>
                            @else
                                <span class="badge bg-warning">Not Verified</span>
                            @endif
                        </p>
                    </div>
                    <div class="col-md-4 mb-0">
                        <label class="form-label text-muted small mb-1">Account Created</label>
                        <p class="mb-0">{{ $staff->user->created_at->format('d M Y, h:i A') }}</p>
                    </div>
                    <div class="col-md-4 mb-0">
                        <label class="form-label text-muted small mb-1">Last Updated</label>
                        <p class="mb-0">{{ $staff->user->updated_at->format('d M Y, h:i A') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-start gap-2 mb-4">
    <a href="{{ route('admin.staff.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back to List
    </a>
</div>
@endsection
