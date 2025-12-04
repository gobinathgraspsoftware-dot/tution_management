@extends('layouts.app')

@section('title', 'Student Profile - ' . $student->user->name)
@section('page-title', 'Student Profile')

@section('content')
<div class="page-header">
    <div>
        <h1><i class="fas fa-user-graduate me-2"></i> Student Profile</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.students.index') }}">Students</a></li>
                <li class="breadcrumb-item active">{{ $student->user->name }}</li>
            </ol>
        </nav>
    </div>
    <div>
        <a href="{{ route('admin.students.history', $student) }}" class="btn btn-info">
            <i class="fas fa-history me-1"></i> View History
        </a>
        <a href="{{ route('admin.students.edit', $student) }}" class="btn btn-warning">
            <i class="fas fa-edit me-1"></i> Edit
        </a>
    </div>
</div>

<!-- Profile Header Card -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-2 text-center">
                <div class="avatar-circle" style="width: 120px; height: 120px; background: linear-gradient(135deg, #fda530 0%, #4c4c4c 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                    <span style="font-size: 48px; color: white; font-weight: bold;">
                        {{ strtoupper(substr($student->user->name, 0, 2)) }}
                    </span>
                </div>
            </div>
            <div class="col-md-6">
                <h2 class="mb-1">{{ $student->user->name }}</h2>
                <p class="text-muted mb-2">
                    <span class="badge bg-primary">{{ $student->student_id }}</span>
                    @if($student->approval_status === 'approved')
                        <span class="badge bg-success">Approved</span>
                    @elseif($student->approval_status === 'pending')
                        <span class="badge bg-warning">Pending</span>
                    @else
                        <span class="badge bg-danger">Rejected</span>
                    @endif
                    <span class="badge bg-info">{{ ucfirst($student->registration_type) }}</span>
                </p>
                <p class="mb-1"><i class="fas fa-envelope me-2"></i> {{ $student->user->email }}</p>
                <p class="mb-1"><i class="fas fa-phone me-2"></i> {{ $student->user->phone ?? 'N/A' }}</p>
                <p class="mb-0"><i class="fas fa-school me-2"></i> {{ $student->school_name ?? 'N/A' }} - {{ $student->grade_level ?? 'N/A' }}</p>
            </div>
            <div class="col-md-4">
                <div class="referral-card p-3 rounded" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white;">
                    <h6 class="mb-2"><i class="fas fa-gift me-1"></i> Referral Code</h6>
                    <h3 class="mb-2">{{ $student->referral_code ?? 'Not Generated' }}</h3>
                    @if($student->referral_code)
                        <small>Share this code to earn RM50 voucher!</small>
                        <form action="{{ route('admin.students.regenerate-referral', $student) }}" method="POST" class="mt-2">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-light" onclick="return confirm('Are you sure you want to regenerate the referral code?')">
                                <i class="fas fa-sync-alt me-1"></i> Regenerate
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #e3f2fd; color: #2196f3;">
                <i class="fas fa-book"></i>
            </div>
            <div class="stat-details">
                <h3 class="mb-0">{{ $stats['active_enrollments'] }}/{{ $stats['total_enrollments'] }}</h3>
                <p class="text-muted mb-0">Active Enrollments</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #e8f5e9; color: #4caf50;">
                <i class="fas fa-money-bill-wave"></i>
            </div>
            <div class="stat-details">
                <h3 class="mb-0">RM {{ number_format($stats['total_paid'], 2) }}</h3>
                <p class="text-muted mb-0">Total Paid</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #fff3e0; color: #ff9800;">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stat-details">
                <h3 class="mb-0">{{ $stats['attendance_rate'] }}%</h3>
                <p class="text-muted mb-0">Attendance Rate</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #f3e5f5; color: #9c27b0;">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-details">
                <h3 class="mb-0">{{ $stats['total_referrals'] }}</h3>
                <p class="text-muted mb-0">Successful Referrals</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Personal Information -->
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-info-circle me-2"></i> Personal Information
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <td class="text-muted" width="40%">IC Number</td>
                        <td><strong>{{ $student->ic_number ?? 'N/A' }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Date of Birth</td>
                        <td><strong>{{ $student->date_of_birth?->format('d M Y') ?? 'N/A' }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Gender</td>
                        <td><strong>{{ ucfirst($student->gender ?? 'N/A') }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Address</td>
                        <td><strong>{{ $student->address ?? 'N/A' }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Medical Conditions</td>
                        <td><strong>{{ $student->medical_conditions ?? 'None' }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Registration Date</td>
                        <td><strong>{{ $student->registration_date?->format('d M Y') ?? 'N/A' }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Enrollment Date</td>
                        <td><strong>{{ $student->enrollment_date?->format('d M Y') ?? 'N/A' }}</strong></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Parent Information -->
        @if($student->parent)
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-user-friends me-2"></i> Parent/Guardian Information
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <td class="text-muted" width="40%">Name</td>
                        <td><strong>{{ $student->parent->user->name ?? 'N/A' }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Email</td>
                        <td><strong>{{ $student->parent->user->email ?? 'N/A' }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Phone</td>
                        <td><strong>{{ $student->parent->user->phone ?? 'N/A' }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Relationship</td>
                        <td><strong>{{ ucfirst($student->parent->relationship ?? 'N/A') }}</strong></td>
                    </tr>
                </table>
            </div>
        </div>
        @endif
    </div>

    <!-- Right Column -->
    <div class="col-md-6">
        <!-- Referral & Voucher Info -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-gift me-2"></i> Referral & Voucher Summary
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-4">
                        <h4 class="text-primary">{{ $stats['total_referrals'] }}</h4>
                        <small class="text-muted">Referrals</small>
                    </div>
                    <div class="col-4">
                        <h4 class="text-success">RM {{ number_format($stats['voucher_balance'], 2) }}</h4>
                        <small class="text-muted">Voucher Balance</small>
                    </div>
                    <div class="col-4">
                        <h4 class="text-info">{{ $student->referralVouchers->where('status', 'active')->count() }}</h4>
                        <small class="text-muted">Active Vouchers</small>
                    </div>
                </div>

                @if($student->referrer)
                <hr>
                <p class="mb-1"><small class="text-muted">Referred by:</small></p>
                <p><strong>{{ $student->referrer->user->name }}</strong> ({{ $student->referrer->student_id }})</p>
                @endif

                @if($referredStudents->count() > 0)
                <hr>
                <p class="mb-2"><small class="text-muted">Students Referred:</small></p>
                @foreach($referredStudents as $referred)
                <span class="badge bg-secondary me-1">{{ $referred->user->name }}</span>
                @endforeach
                @endif
            </div>
        </div>

        <!-- Active Enrollments -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-graduation-cap me-2"></i> Active Enrollments
            </div>
            <div class="card-body">
                @forelse($student->enrollments->where('status', 'active') as $enrollment)
                <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-light rounded">
                    <div>
                        <strong>{{ $enrollment->class->name ?? 'N/A' }}</strong>
                        <br><small class="text-muted">{{ $enrollment->class->subject->name ?? 'N/A' }}</small>
                    </div>
                    <span class="badge bg-success">Active</span>
                </div>
                @empty
                <p class="text-muted text-center mb-0">No active enrollments</p>
                @endforelse
            </div>
        </div>

        <!-- Reviews -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-star me-2"></i> Reviews ({{ $stats['reviews_count'] }})
                @if($stats['average_rating'] > 0)
                <span class="float-end">
                    @for($i = 1; $i <= 5; $i++)
                        @if($i <= round($stats['average_rating']))
                            <i class="fas fa-star text-warning"></i>
                        @else
                            <i class="far fa-star text-warning"></i>
                        @endif
                    @endfor
                    ({{ number_format($stats['average_rating'], 1) }})
                </span>
                @endif
            </div>
            <div class="card-body">
                @forelse($student->reviews->take(3) as $review)
                <div class="mb-3 pb-3 border-bottom">
                    <div class="d-flex justify-content-between">
                        <div>
                            @for($i = 1; $i <= 5; $i++)
                                @if($i <= $review->rating)
                                    <i class="fas fa-star text-warning"></i>
                                @else
                                    <i class="far fa-star text-warning"></i>
                                @endif
                            @endfor
                        </div>
                        <small class="text-muted">{{ $review->created_at->format('d M Y') }}</small>
                    </div>
                    <p class="mb-1 mt-2">{{ Str::limit($review->review, 100) }}</p>
                    <small class="text-muted">
                        {{ $review->class->name ?? 'General' }}
                        @if($review->teacher) - {{ $review->teacher->user->name }} @endif
                    </small>
                </div>
                @empty
                <p class="text-muted text-center mb-0">No reviews yet</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Recent Invoices -->
<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-file-invoice me-2"></i> Recent Invoices
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Date</th>
                        <th>Description</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($student->invoices as $invoice)
                    <tr>
                        <td>{{ $invoice->invoice_number }}</td>
                        <td>{{ $invoice->invoice_date?->format('d M Y') }}</td>
                        <td>{{ Str::limit($invoice->description, 50) }}</td>
                        <td>RM {{ number_format($invoice->total_amount, 2) }}</td>
                        <td>
                            @if($invoice->status === 'paid')
                                <span class="badge bg-success">Paid</span>
                            @elseif($invoice->status === 'pending')
                                <span class="badge bg-warning">Pending</span>
                            @elseif($invoice->status === 'overdue')
                                <span class="badge bg-danger">Overdue</span>
                            @else
                                <span class="badge bg-secondary">{{ ucfirst($invoice->status) }}</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">No invoices found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Trial Classes -->
@if($student->trialClasses->count() > 0)
<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-chalkboard me-2"></i> Trial Classes
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Class</th>
                        <th>Scheduled Date</th>
                        <th>Status</th>
                        <th>Conversion</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($student->trialClasses as $trial)
                    <tr>
                        <td>{{ $trial->class->name ?? 'N/A' }}</td>
                        <td>{{ $trial->scheduled_date->format('d M Y') }} {{ $trial->scheduled_time?->format('h:i A') }}</td>
                        <td>
                            @if($trial->status === 'attended')
                                <span class="badge bg-success">Attended</span>
                            @elseif($trial->status === 'no_show')
                                <span class="badge bg-danger">No Show</span>
                            @elseif($trial->status === 'converted')
                                <span class="badge bg-primary">Converted</span>
                            @else
                                <span class="badge bg-secondary">{{ ucfirst($trial->status) }}</span>
                            @endif
                        </td>
                        <td>
                            @if($trial->conversion_status === 'converted')
                                <span class="badge bg-success">Converted</span>
                            @elseif($trial->conversion_status === 'declined')
                                <span class="badge bg-danger">Declined</span>
                            @else
                                <span class="badge bg-warning">Pending</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
@endsection
