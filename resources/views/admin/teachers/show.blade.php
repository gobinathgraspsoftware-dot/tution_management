@extends('layouts.app')

@section('title', 'Teacher Details')
@section('page-title', 'Teacher Details')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="fas fa-chalkboard-teacher me-2"></i> Teacher Details</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.teachers.index') }}">Teachers</a></li>
                <li class="breadcrumb-item active">{{ $teacher->user->name }}</li>
            </ol>
        </nav>
    </div>
    <div>
        @can('edit-teachers')
        <a href="{{ route('admin.teachers.edit', $teacher) }}" class="btn btn-primary">
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
                <div class="user-avatar mx-auto mb-3" style="width: 100px; height: 100px; font-size: 2.5rem; background: linear-gradient(135deg, #4caf50 0%, #2e7d32 100%);">
                    {{ substr($teacher->user->name, 0, 1) }}
                </div>
                <h4>{{ $teacher->user->name }}</h4>
                <p class="text-muted mb-2">
                    @if(!empty($teacher->specialization_names))
                        {{ implode(', ', array_slice($teacher->specialization_names, 0, 2)) }}
                        @if(count($teacher->specialization_names) > 2)
                            <span class="text-muted small">+{{ count($teacher->specialization_names) - 2 }} more</span>
                        @endif
                    @else
                        Teacher
                    @endif
                </p>
                <span class="badge bg-info">{{ $teacher->teacher_id }}</span>

                <hr>

                @if($teacher->status == 'active')
                    <span class="badge bg-success fs-6">Active</span>
                @elseif($teacher->status == 'on_leave')
                    <span class="badge bg-warning text-dark fs-6">On Leave</span>
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
                        <a href="mailto:{{ $teacher->user->email }}">{{ $teacher->user->email }}</a>
                    </p>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small mb-1">Phone</label>
                    <p class="mb-0">
                        @if($teacher->user->phone)
                            <a href="tel:{{ $teacher->user->phone }}">{{ $teacher->user->phone }}</a>
                        @else
                            N/A
                        @endif
                    </p>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small mb-1">Address</label>
                    <p class="mb-0">{{ $teacher->address ?? 'N/A' }}</p>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-chart-bar me-2"></i> Teaching Statistics
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-4">
                        <h4 class="text-primary mb-0">{{ $stats['total_classes'] }}</h4>
                        <small class="text-muted">Total Classes</small>
                    </div>
                    <div class="col-4">
                        <h4 class="text-success mb-0">{{ $stats['active_classes'] }}</h4>
                        <small class="text-muted">Active</small>
                    </div>
                    <div class="col-4">
                        <h4 class="text-info mb-0">{{ $stats['total_students'] }}</h4>
                        <small class="text-muted">Students</small>
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
                        <p class="mb-0"><strong>{{ $teacher->formatted_ic_number ?? 'N/A' }}</strong></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small mb-1">Join Date</label>
                        <p class="mb-0">{{ $teacher->join_date ? $teacher->join_date->format('d M Y') : 'N/A' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small mb-1">Qualification</label>
                        <p class="mb-0">{{ $teacher->qualification ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small mb-1">Experience</label>
                        <p class="mb-0">{{ $teacher->experience_years }} years</p>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label class="form-label text-muted small mb-1">Specialization</label>
                        <div>
                            @if(!empty($teacher->specialization_names))
                                @foreach($teacher->specialization_names as $subject)
                                    <span class="badge bg-primary me-1 mb-1">{{ $subject }}</span>
                                @endforeach
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </div>
                    </div>
                </div>
                @if($teacher->bio)
                <div class="mt-3">
                    <label class="form-label text-muted small mb-1">Bio</label>
                    <p class="mb-0">{{ $teacher->bio }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Account Security -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-lock me-2"></i> Account Security
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small mb-1">Current Password</label>
                        <p class="mb-0">
                            <code>{{ $teacher->user->password_view ?? '********' }}</code>
                        </p>
                        <small class="text-muted">For reference only</small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small mb-1">Account Created</label>
                        <p class="mb-0">{{ $teacher->user->created_at->format('d M Y, h:i A') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Employment Details -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-briefcase me-2"></i> Employment Details
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label text-muted small mb-1">Employment Type</label>
                        <p class="mb-0">
                            @php
                                $empBadge = ['full_time' => 'bg-primary', 'part_time' => 'bg-warning text-dark', 'contract' => 'bg-secondary'];
                            @endphp
                            <span class="badge {{ $empBadge[$teacher->employment_type] ?? 'bg-secondary' }}">
                                {{ str_replace('_', ' ', ucfirst($teacher->employment_type)) }}
                            </span>
                        </p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label text-muted small mb-1">Pay Type</label>
                        <p class="mb-0">{{ ucfirst(str_replace('_', ' ', $teacher->pay_type)) }}</p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label text-muted small mb-1">Pay Rate</label>
                        <p class="mb-0">
                            @if($teacher->pay_type == 'hourly')
                                RM {{ number_format($teacher->hourly_rate, 2) }}/hour
                            @elseif($teacher->pay_type == 'monthly')
                                RM {{ number_format($teacher->monthly_salary, 2) }}/month
                            @else
                                RM {{ number_format($teacher->per_class_rate, 2) }}/class
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bank Details -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-university me-2"></i> Bank & Statutory Details
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small mb-1">Bank Name</label>
                        <p class="mb-0">{{ $teacher->bank_name ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small mb-1">Bank Account</label>
                        <p class="mb-0">{{ $teacher->bank_account ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small mb-1">EPF Number</label>
                        <p class="mb-0">{{ $teacher->epf_number ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small mb-1">SOCSO Number</label>
                        <p class="mb-0">{{ $teacher->socso_number ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assigned Classes -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-school me-2"></i> Assigned Classes</span>
                <span class="badge bg-primary">{{ $teacher->classes->count() }} Classes</span>
            </div>
            <div class="card-body">
                @if($teacher->classes->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Class Name</th>
                                <th>Subject</th>
                                <th>Type</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($teacher->classes as $class)
                            <tr>
                                <td>{{ $class->name }}</td>
                                <td>{{ $class->subject->name ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge {{ $class->class_type == 'online' ? 'bg-info' : 'bg-secondary' }}">
                                        {{ ucfirst($class->class_type) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ $class->status == 'active' ? 'bg-success' : 'bg-danger' }}">
                                        {{ ucfirst($class->status) }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-muted mb-0 text-center py-3">No classes assigned yet.</p>
                @endif
            </div>
        </div>

        <!-- Recent Attendance -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-calendar-check me-2"></i> Recent Attendance
            </div>
            <div class="card-body">
                @if($teacher->attendance->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Time In</th>
                                <th>Time Out</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($teacher->attendance as $att)
                            <tr>
                                <td>{{ $att->date->format('d M Y') }}</td>
                                <td>
                                    @php
                                        $statusBadge = ['present' => 'bg-success', 'absent' => 'bg-danger', 'half_day' => 'bg-warning text-dark', 'leave' => 'bg-info'];
                                    @endphp
                                    <span class="badge {{ $statusBadge[$att->status] ?? 'bg-secondary' }}">
                                        {{ ucfirst(str_replace('_', ' ', $att->status)) }}
                                    </span>
                                </td>
                                <td>{{ $att->time_in ?? '-' }}</td>
                                <td>{{ $att->time_out ?? '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-muted mb-0 text-center py-3">No attendance records yet.</p>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-start gap-2 mb-4">
    <a href="{{ route('admin.teachers.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back to List
    </a>
</div>
@endsection
