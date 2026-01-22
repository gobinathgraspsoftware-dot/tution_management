@extends('layouts.app')

@section('title', 'Student Details - ' . ($student->user->name ?? 'N/A'))

@section('styles')
<style>
    .profile-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 10px;
        padding: 30px;
        margin-bottom: 20px;
    }
    .profile-avatar {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background: white;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #667eea;
        font-weight: bold;
        font-size: 36px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    }
    .stat-box {
        background: white;
        border-radius: 10px;
        padding: 20px;
        text-align: center;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        margin-bottom: 15px;
    }
    .stat-box h4 {
        font-size: 1.5rem;
        font-weight: bold;
        margin-bottom: 5px;
    }
    .stat-box p {
        margin: 0;
        color: #6c757d;
        font-size: 0.875rem;
    }
    .stat-box.primary h4 { color: #667eea; }
    .stat-box.success h4 { color: #28a745; }
    .stat-box.warning h4 { color: #ffc107; }
    .stat-box.info h4 { color: #17a2b8; }
    
    .info-card {
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        margin-bottom: 20px;
    }
    .info-card .card-header {
        background: #f8f9fa;
        font-weight: 600;
        border-bottom: 1px solid #eee;
    }
    .info-label {
        color: #6c757d;
        font-size: 0.875rem;
        margin-bottom: 2px;
    }
    .info-value {
        font-weight: 500;
    }
    .enrollment-card {
        border-left: 4px solid #667eea;
        margin-bottom: 10px;
    }
    .enrollment-card.active { border-left-color: #28a745; }
    .enrollment-card.suspended { border-left-color: #ffc107; }
    .enrollment-card.cancelled { border-left-color: #dc3545; }
    
    .attendance-badge {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
        color: white;
    }
    .attendance-badge.present { background: #28a745; }
    .attendance-badge.absent { background: #dc3545; }
    .attendance-badge.late { background: #ffc107; color: #333; }
    .attendance-badge.excused { background: #17a2b8; }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('staff.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('staff.students.index') }}">All Students</a></li>
            <li class="breadcrumb-item active">{{ $student->user->name ?? 'Student Details' }}</li>
        </ol>
    </nav>

    <!-- Profile Header -->
    <div class="profile-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="d-flex align-items-center">
                    <div class="profile-avatar me-4">
                        {{ strtoupper(substr($student->user->name ?? 'S', 0, 1)) }}
                    </div>
                    <div>
                        <h2 class="mb-1">{{ $student->user->name ?? 'N/A' }}</h2>
                        <p class="mb-1 opacity-75">
                            <i class="fas fa-id-card me-2"></i>Student ID: {{ $student->student_id }}
                        </p>
                        <p class="mb-0 opacity-75">
                            <i class="fas fa-graduation-cap me-2"></i>{{ $student->grade_level ?? 'N/A' }}
                            @if($student->school_name)
                                | {{ $student->school_name }}
                            @endif
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                @if($student->user && $student->user->status == 'active')
                    <span class="badge bg-light text-success fs-6 px-3 py-2">
                        <i class="fas fa-check-circle me-1"></i>Active Account
                    </span>
                @else
                    <span class="badge bg-light text-secondary fs-6 px-3 py-2">
                        <i class="fas fa-times-circle me-1"></i>Inactive Account
                    </span>
                @endif
                <br class="d-none d-md-block">
                @if($student->approval_status == 'approved')
                    <span class="badge bg-success mt-2 px-3 py-2">Approved</span>
                @elseif($student->approval_status == 'pending')
                    <span class="badge bg-warning text-dark mt-2 px-3 py-2">Pending Approval</span>
                @else
                    <span class="badge bg-danger mt-2 px-3 py-2">{{ ucfirst($student->approval_status) }}</span>
                @endif
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-3 col-6">
            <div class="stat-box primary">
                <h4>{{ $stats['active_enrollments'] }}</h4>
                <p>Active Enrollments</p>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-box success">
                <h4>{{ number_format($stats['attendance_rate'], 1) }}%</h4>
                <p>Attendance Rate</p>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-box info">
                <h4>RM {{ number_format($stats['total_paid'], 2) }}</h4>
                <p>Total Paid</p>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-box warning">
                <h4>RM {{ number_format($stats['pending_amount'], 2) }}</h4>
                <p>Pending Amount</p>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Left Column -->
        <div class="col-lg-4">
            <!-- Personal Information -->
            <div class="card info-card">
                <div class="card-header">
                    <i class="fas fa-user me-2"></i>Personal Information
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="info-label">Full Name</div>
                        <div class="info-value">{{ $student->user->name ?? 'N/A' }}</div>
                    </div>
                    <div class="mb-3">
                        <div class="info-label">Email</div>
                        <div class="info-value">
                            <a href="mailto:{{ $student->user->email ?? '' }}">{{ $student->user->email ?? 'N/A' }}</a>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="info-label">Phone</div>
                        <div class="info-value">
                            <a href="tel:{{ $student->user->phone ?? '' }}">{{ $student->user->phone ?? 'N/A' }}</a>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="info-label">IC Number</div>
                        <div class="info-value">{{ $student->ic_number ?? 'N/A' }}</div>
                    </div>
                    <div class="mb-3">
                        <div class="info-label">Gender</div>
                        <div class="info-value">{{ ucfirst($student->gender ?? 'N/A') }}</div>
                    </div>
                    <div class="mb-3">
                        <div class="info-label">Date of Birth</div>
                        <div class="info-value">
                            {{ $student->date_of_birth ? $student->date_of_birth->format('d M Y') : 'N/A' }}
                        </div>
                    </div>
                    <div class="mb-0">
                        <div class="info-label">Registration Type</div>
                        <div class="info-value">
                            <span class="badge bg-{{ $student->registration_type == 'online' ? 'info' : 'secondary' }}">
                                {{ ucfirst($student->registration_type ?? 'N/A') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Parent/Guardian Information -->
            <div class="card info-card">
                <div class="card-header">
                    <i class="fas fa-users me-2"></i>Parent/Guardian
                </div>
                <div class="card-body">
                    @if($student->parent && $student->parent->user)
                        <div class="mb-3">
                            <div class="info-label">Name</div>
                            <div class="info-value">{{ $student->parent->user->name }}</div>
                        </div>
                        <div class="mb-3">
                            <div class="info-label">Email</div>
                            <div class="info-value">
                                <a href="mailto:{{ $student->parent->user->email }}">{{ $student->parent->user->email }}</a>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="info-label">Phone</div>
                            <div class="info-value">
                                <a href="tel:{{ $student->parent->user->phone }}">{{ $student->parent->user->phone ?? 'N/A' }}</a>
                            </div>
                        </div>
                        <div class="mb-0">
                            <div class="info-label">Relationship</div>
                            <div class="info-value">{{ ucfirst($student->parent->relationship ?? 'N/A') }}</div>
                        </div>
                    @else
                        <p class="text-muted mb-0">No parent/guardian linked</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-lg-8">
            <!-- Active Enrollments -->
            <div class="card info-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-graduation-cap me-2"></i>Active Enrollments</span>
                    <span class="badge bg-primary">{{ $activeEnrollments->count() }}</span>
                </div>
                <div class="card-body">
                    @forelse($activeEnrollments as $enrollment)
                        <div class="card enrollment-card {{ $enrollment->status }}">
                            <div class="card-body py-3">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <h6 class="mb-1">{{ $enrollment->class->name ?? 'N/A' }}</h6>
                                        <small class="text-muted">
                                            <i class="fas fa-book me-1"></i>{{ $enrollment->class->subject->name ?? 'N/A' }}
                                            @if($enrollment->package)
                                                | <i class="fas fa-box me-1"></i>{{ $enrollment->package->name }}
                                            @endif
                                        </small>
                                    </div>
                                    <div class="col-md-3">
                                        @if($enrollment->class && $enrollment->class->teacher && $enrollment->class->teacher->user)
                                            <small class="text-muted d-block">Teacher</small>
                                            <span>{{ $enrollment->class->teacher->user->name }}</span>
                                        @endif
                                    </div>
                                    <div class="col-md-3 text-md-end">
                                        <small class="text-muted d-block">Period</small>
                                        <span>
                                            {{ $enrollment->start_date?->format('d M Y') ?? 'N/A' }}
                                            @if($enrollment->end_date)
                                                - {{ $enrollment->end_date->format('d M Y') }}
                                            @endif
                                        </span>
                                    </div>
                                </div>
                                @if($enrollment->class && $enrollment->class->schedules->count() > 0)
                                    <hr class="my-2">
                                    <small class="text-muted">
                                        <i class="fas fa-clock me-1"></i>Schedule:
                                        @foreach($enrollment->class->schedules as $schedule)
                                            <span class="badge bg-light text-dark me-1">
                                                {{ ucfirst($schedule->day_of_week) }} 
                                                {{ $schedule->start_time->format('h:i A') }} - {{ $schedule->end_time->format('h:i A') }}
                                            </span>
                                        @endforeach
                                    </small>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-muted text-center py-3 mb-0">No active enrollments</p>
                    @endforelse
                </div>
            </div>

            <!-- Recent Attendance -->
            <div class="card info-card">
                <div class="card-header">
                    <i class="fas fa-calendar-check me-2"></i>Recent Attendance
                </div>
                <div class="card-body">
                    @if($student->attendance && $student->attendance->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Class</th>
                                        <th>Status</th>
                                        <th>Remarks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($student->attendance->take(10) as $attendance)
                                        <tr>
                                            <td>{{ $attendance->classSession && $attendance->classSession->session_date ? $attendance->classSession->session_date->format('d M Y') : ($attendance->created_at ? $attendance->created_at->format('d M Y') : 'N/A') }}</td>
                                            <td>{{ $attendance->classSession->class->name ?? 'N/A' }}</td>
                                            <td>
                                                <span class="attendance-badge {{ $attendance->status }}">
                                                    @switch($attendance->status)
                                                        @case('present')
                                                            <i class="fas fa-check"></i>
                                                            @break
                                                        @case('absent')
                                                            <i class="fas fa-times"></i>
                                                            @break
                                                        @case('late')
                                                            <i class="fas fa-clock"></i>
                                                            @break
                                                        @case('excused')
                                                            <i class="fas fa-info"></i>
                                                            @break
                                                    @endswitch
                                                </span>
                                                <span class="ms-1">{{ ucfirst($attendance->status) }}</span>
                                            </td>
                                            <td>
                                                <small class="text-muted">{{ $attendance->remarks ?? '-' }}</small>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted text-center py-3 mb-0">No attendance records</p>
                    @endif
                </div>
            </div>

            <!-- Recent Payments -->
            <div class="card info-card">
                <div class="card-header">
                    <i class="fas fa-money-bill-wave me-2"></i>Recent Payments
                </div>
                <div class="card-body">
                    @if($student->payments && $student->payments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Receipt No</th>
                                        <th>Amount</th>
                                        <th>Method</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($student->payments->take(10) as $payment)
                                        <tr>
                                            <td>{{ $payment->payment_date ? $payment->payment_date->format('d M Y') : ($payment->created_at ? $payment->created_at->format('d M Y') : 'N/A') }}</td>
                                            <td>
                                                <span class="badge bg-light text-dark">{{ $payment->receipt_number ?? 'N/A' }}</span>
                                            </td>
                                            <td class="fw-semibold">RM {{ number_format($payment->amount, 2) }}</td>
                                            <td>{{ ucfirst(str_replace('_', ' ', $payment->payment_method ?? 'N/A')) }}</td>
                                            <td>
                                                <span class="badge bg-{{ $payment->status == 'completed' ? 'success' : ($payment->status == 'pending' ? 'warning' : 'secondary') }}">
                                                    {{ ucfirst($payment->status) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted text-center py-3 mb-0">No payment records</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Back Button -->
    <div class="mt-4">
        <a href="{{ route('staff.students.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Students
        </a>
    </div>
</div>
@endsection
