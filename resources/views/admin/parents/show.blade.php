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
                <p class="text-muted mb-2">{{ ucfirst($parent->relationship) }}</p>
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
                    <label class="form-label text-muted small mb-1">WhatsApp</label>
                    <p class="mb-0">
                        @if($parent->whatsapp_number)
                        <a href="https://wa.me/{{ $parent->whatsapp_number }}" target="_blank">
                            <i class="fab fa-whatsapp text-success me-1"></i> {{ $parent->whatsapp_number }}
                        </a>
                        @else
                        N/A
                        @endif
                    </p>
                </div>
            </div>
        </div>

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
    </div>

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
                        <p class="mb-0"><strong>{{ $parent->ic_number }}</strong></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small mb-1">Occupation</label>
                        <p class="mb-0">{{ $parent->occupation ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Address Information -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-map-marker-alt me-2"></i> Address
            </div>
            <div class="card-body">
                <p class="mb-1">{{ $parent->address }}</p>
                <p class="mb-0 text-muted">{{ $parent->postcode }} {{ $parent->city }}, {{ $parent->state }}</p>
            </div>
        </div>

        <!-- Emergency Contact -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-phone-alt me-2"></i> Emergency Contact
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label text-muted small mb-1">Contact Name</label>
                        <p class="mb-0">{{ $parent->emergency_contact ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small mb-1">Contact Phone</label>
                        <p class="mb-0">{{ $parent->emergency_phone ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Children -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-child me-2"></i> Children</span>
                <span class="badge bg-primary">{{ $parent->students->count() }} children</span>
            </div>
            <div class="card-body">
                @if($parent->students->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Student ID</th>
                                <th>Name</th>
                                <th>School</th>
                                <th>Grade</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($parent->students as $student)
                            <tr>
                                <td><span class="badge bg-info">{{ $student->student_id }}</span></td>
                                <td>{{ $student->user->name }}</td>
                                <td>{{ $student->school_name ?? 'N/A' }}</td>
                                <td>{{ $student->grade_level ?? 'N/A' }}</td>
                                <td>
                                    @if($student->approval_status == 'approved')
                                        <span class="badge bg-success">Approved</span>
                                    @elseif($student->approval_status == 'pending')
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    @else
                                        <span class="badge bg-danger">Rejected</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.students.show', $student) }}" class="btn btn-sm btn-outline-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Enrollments Summary -->
                @if($parent->students->flatMap->enrollments->count() > 0)
                <hr>
                <h6 class="mb-3"><i class="fas fa-graduation-cap me-2"></i> Active Enrollments</h6>
                @foreach($parent->students as $student)
                    @if($student->enrollments->count() > 0)
                    <div class="mb-2">
                        <strong>{{ $student->user->name }}:</strong>
                        @foreach($student->enrollments->where('status', 'active') as $enrollment)
                            <span class="badge bg-secondary">{{ $enrollment->package->name ?? 'N/A' }}</span>
                        @endforeach
                    </div>
                    @endif
                @endforeach
                @endif
                @else
                <p class="text-muted mb-0 text-center py-3">No children linked yet.</p>
                @endif
            </div>
        </div>

        <!-- Notification Preferences -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-bell me-2"></i> Notification Preferences
            </div>
            <div class="card-body">
                @php
                    $notifPrefs = $parent->notification_preference ?? [];
                @endphp
                <div class="d-flex gap-3">
                    <span class="badge {{ ($notifPrefs['whatsapp'] ?? false) ? 'bg-success' : 'bg-secondary' }} p-2">
                        <i class="fab fa-whatsapp me-1"></i> WhatsApp
                    </span>
                    <span class="badge {{ ($notifPrefs['email'] ?? false) ? 'bg-success' : 'bg-secondary' }} p-2">
                        <i class="fas fa-envelope me-1"></i> Email
                    </span>
                    <span class="badge {{ ($notifPrefs['sms'] ?? false) ? 'bg-success' : 'bg-secondary' }} p-2">
                        <i class="fas fa-sms me-1"></i> SMS
                    </span>
                </div>
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
                        <label class="form-label text-muted small mb-1">Last Login</label>
                        <p class="mb-0">{{ $parent->user->last_login_at?->format('d M Y, h:i A') ?? 'Never' }}</p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label text-muted small mb-1">Account Created</label>
                        <p class="mb-0">{{ $parent->user->created_at->format('d M Y') }}</p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label text-muted small mb-1">Email Verified</label>
                        <p class="mb-0">
                            @if($parent->user->email_verified_at)
                                <span class="badge bg-success">Verified</span>
                            @else
                                <span class="badge bg-warning">Not Verified</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-start gap-2 mb-4">
    <a href="{{ route('admin.parents.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back to List
    </a>
</div>
@endsection
