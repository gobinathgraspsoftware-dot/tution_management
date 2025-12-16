@extends('layouts.app')

@section('title', 'Parent Details')
@section('page-title', 'Parent Details')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="fas fa-user-friends me-2"></i> Parent Details</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.parents.index') }}">Parents</a></li>
                <li class="breadcrumb-item active">{{ $parent->user->name }}</li>
            </ol>
        </nav>
    </div>
    <div>
        @can('edit-parents')
        <a href="{{ route('admin.parents.edit', $parent) }}" class="btn btn-primary">
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
                <div class="user-avatar mx-auto mb-3" style="width: 100px; height: 100px; font-size: 2.5rem; background: linear-gradient(135deg, #ff9800 0%, #f57c00 100%);">
                    {{ substr($parent->user->name, 0, 1) }}
                </div>
                <h4>{{ $parent->user->name }}</h4>
                <p class="text-muted mb-2">
                    {{ ucfirst($parent->relationship) }}
                    @if($parent->relationship == 'other' && $parent->relationship_description)
                        <br><small>({{ $parent->relationship_description }})</small>
                    @endif
                </p>
                <span class="badge bg-info">{{ $parent->parent_id }}</span>

                <hr>

                @if($parent->user->status == 'active')
                    <span class="badge bg-success fs-6">Active</span>
                @else
                    <span class="badge bg-danger fs-6">Inactive</span>
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
                        <a href="mailto:{{ $parent->user->email }}">{{ $parent->user->email }}</a>
                    </p>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small mb-1">Phone</label>
                    <p class="mb-0">
                        <a href="tel:{{ $parent->user->phone }}">{{ $parent->user->phone }}</a>
                    </p>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small mb-1">IC Number</label>
                    <p class="mb-0">{{ $parent->ic_number }}</p>
                </div>
                @if($parent->occupation)
                <div class="mb-3">
                    <label class="form-label text-muted small mb-1">Occupation</label>
                    <p class="mb-0">{{ $parent->occupation }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Emergency Contact -->
        @if($parent->emergency_contact || $parent->emergency_phone)
        <div class="card mb-4">
            <div class="card-header bg-danger text-white">
                <i class="fas fa-ambulance me-2"></i> Emergency Contact
            </div>
            <div class="card-body">
                @if($parent->emergency_contact)
                <div class="mb-3">
                    <label class="form-label text-muted small mb-1">Name</label>
                    <p class="mb-0">{{ $parent->emergency_contact }}</p>
                </div>
                @endif
                @if($parent->emergency_phone)
                <div class="mb-3">
                    <label class="form-label text-muted small mb-1">Phone</label>
                    <p class="mb-0">
                        <a href="tel:{{ $parent->emergency_phone }}">{{ $parent->emergency_phone }}</a>
                    </p>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Payment Summary -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-wallet me-2"></i> Payment Summary
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <h5 class="text-success mb-0">RM {{ number_format($paymentStats['total_paid'], 2) }}</h5>
                        <small class="text-muted">Total Paid</small>
                    </div>
                    <div class="col-6">
                        <h5 class="text-danger mb-0">RM {{ number_format($paymentStats['pending_invoices'], 2) }}</h5>
                        <small class="text-muted">Pending</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notification Preferences -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-bell me-2"></i> Notification Preferences
            </div>
            <div class="card-body">
                @php
                    $emailEnabled = $parent->notification_preference['email'] ?? true;
                @endphp
                <div class="d-flex align-items-center">
                    <i class="fas fa-envelope text-primary me-2"></i>
                    <span>Email Notifications: </span>
                    @if($emailEnabled)
                        <span class="badge bg-success ms-2">Enabled</span>
                    @else
                        <span class="badge bg-secondary ms-2">Disabled</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="col-md-8">
        <!-- Address Information -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-map-marker-alt me-2"></i> Address Information
            </div>
            <div class="card-body">
                <address class="mb-0">
                    {{ $parent->address }}<br>
                    {{ $parent->postcode }} {{ $parent->city }}<br>
                    {{ $parent->state }}
                </address>
            </div>
        </div>

        <!-- Linked Students -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-users me-2"></i> Linked Students
                <span class="badge bg-primary ms-2">{{ $parent->students->count() }}</span>
            </div>
            <div class="card-body">
                @if($parent->students->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Student ID</th>
                                    <th>Name</th>
                                    <th>School</th>
                                    <th>Enrollments</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($parent->students as $student)
                                <tr>
                                    <td>{{ $student->student_id }}</td>
                                    <td>
                                        <strong>{{ $student->user->name }}</strong><br>
                                        <small class="text-muted">{{ $student->user->email }}</small>
                                    </td>
                                    <td>{{ $student->school_name }}</td>
                                    <td>
                                        <span class="badge bg-info">{{ $student->enrollments->count() }} Classes</span>
                                    </td>
                                    <td>
                                        @if($student->user->status == 'active')
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-danger">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        @can('view-students')
                                        <a href="{{ route('admin.students.show', $student) }}"
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        @endcan
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        No students linked to this parent yet.
                    </div>
                @endif
            </div>
        </div>

        <!-- Active Enrollments -->
        @if($parent->students->count() > 0)
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-graduation-cap me-2"></i> Active Enrollments
            </div>
            <div class="card-body">
                @php
                    $activeEnrollments = [];
                    foreach($parent->students as $student) {
                        foreach($student->enrollments->where('status', 'active') as $enrollment) {
                            $activeEnrollments[] = $enrollment;
                        }
                    }
                @endphp

                @if(count($activeEnrollments) > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Package</th>
                                    <th>Fee</th>
                                    <th>Start Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($activeEnrollments as $enrollment)
                                <tr>
                                    <td>{{ $enrollment->student->user->name }}</td>
                                    <td>{{ $enrollment->package->name }}</td>
                                    <td>RM {{ number_format($enrollment->fee_amount, 2) }}</td>
                                    <td>{{ $enrollment->enrollment_date->format('d M Y') }}</td>
                                    <td>
                                        <span class="badge bg-success">{{ ucfirst($enrollment->status) }}</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        No active enrollments at the moment.
                    </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Activity Log -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-history me-2"></i> Recent Activity
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-badge bg-primary">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <div class="timeline-content">
                            <h6 class="mb-1">Parent Account Created</h6>
                            <p class="text-muted small mb-0">
                                {{ $parent->created_at->format('d M Y, h:i A') }}
                                <br><small>{{ $parent->created_at->diffForHumans() }}</small>
                            </p>
                        </div>
                    </div>

                    @if($parent->updated_at != $parent->created_at)
                    <div class="timeline-item">
                        <div class="timeline-badge bg-info">
                            <i class="fas fa-edit"></i>
                        </div>
                        <div class="timeline-content">
                            <h6 class="mb-1">Profile Updated</h6>
                            <p class="text-muted small mb-0">
                                {{ $parent->updated_at->format('d M Y, h:i A') }}
                                <br><small>{{ $parent->updated_at->diffForHumans() }}</small>
                            </p>
                        </div>
                    </div>
                    @endif
                </div>
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

    .timeline {
        position: relative;
        padding-left: 30px;
    }

    .timeline:before {
        content: '';
        position: absolute;
        left: 10px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #dee2e6;
    }

    .timeline-item {
        position: relative;
        margin-bottom: 20px;
    }

    .timeline-item:last-child {
        margin-bottom: 0;
    }

    .timeline-badge {
        position: absolute;
        left: -20px;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
        color: white;
    }

    .timeline-content {
        padding-left: 20px;
    }
</style>
@endpush
