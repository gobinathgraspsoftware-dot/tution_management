@extends('layouts.app')

@section('title', 'Student History - ' . $student->user->name)
@section('page-title', 'Student History')

@section('content')
<div class="page-header">
    <div>
        <h1><i class="fas fa-history me-2"></i> Student History</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.students.index') }}">Students</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.students.profile', $student) }}">{{ $student->user->name }}</a></li>
                <li class="breadcrumb-item active">History</li>
            </ol>
        </nav>
    </div>
    <div>
        <a href="{{ route('admin.students.profile', $student) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Profile
        </a>
    </div>
</div>

<!-- Student Info Card -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-1">
                <div class="avatar-circle" style="width: 60px; height: 60px; background: linear-gradient(135deg, #fda530 0%, #4c4c4c 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                    <span style="font-size: 24px; color: white; font-weight: bold;">
                        {{ strtoupper(substr($student->user->name, 0, 2)) }}
                    </span>
                </div>
            </div>
            <div class="col-md-11">
                <h4 class="mb-1">{{ $student->user->name }}</h4>
                <p class="mb-0 text-muted">
                    <span class="badge bg-primary">{{ $student->student_id }}</span>
                    {{ $student->user->email }} | {{ $student->user->phone ?? 'No phone' }}
                </p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Main Timeline -->
    <div class="col-md-8">
        <!-- Attendance Summary -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-chart-pie me-2"></i> Attendance Summary
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-3">
                        <h4 class="text-success">{{ $attendanceSummary['present'] ?? 0 }}</h4>
                        <small class="text-muted">Present</small>
                    </div>
                    <div class="col-3">
                        <h4 class="text-danger">{{ $attendanceSummary['absent'] ?? 0 }}</h4>
                        <small class="text-muted">Absent</small>
                    </div>
                    <div class="col-3">
                        <h4 class="text-warning">{{ $attendanceSummary['late'] ?? 0 }}</h4>
                        <small class="text-muted">Late</small>
                    </div>
                    <div class="col-3">
                        <h4 class="text-info">{{ $attendanceSummary['excused'] ?? 0 }}</h4>
                        <small class="text-muted">Excused</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Activity Timeline -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-stream me-2"></i> Activity Timeline
            </div>
            <div class="card-body">
                @forelse($activities as $activity)
                <div class="timeline-item mb-3 pb-3 border-bottom">
                    <div class="d-flex">
                        <div class="timeline-icon me-3">
                            @if($activity->action === 'create')
                                <span class="badge bg-success rounded-pill p-2"><i class="fas fa-plus"></i></span>
                            @elseif($activity->action === 'update')
                                <span class="badge bg-warning rounded-pill p-2"><i class="fas fa-edit"></i></span>
                            @elseif($activity->action === 'delete')
                                <span class="badge bg-danger rounded-pill p-2"><i class="fas fa-trash"></i></span>
                            @else
                                <span class="badge bg-secondary rounded-pill p-2"><i class="fas fa-info"></i></span>
                            @endif
                        </div>
                        <div class="timeline-content flex-grow-1">
                            <div class="d-flex justify-content-between">
                                <strong>{{ ucfirst($activity->action) }} - {{ $activity->model_type }}</strong>
                                <small class="text-muted">{{ $activity->created_at->format('d M Y, h:i A') }}</small>
                            </div>
                            <p class="mb-1">{{ $activity->description }}</p>
                            <small class="text-muted">
                                @if($activity->user)
                                    By: {{ $activity->user->name }}
                                @endif
                                | IP: {{ $activity->ip_address ?? 'N/A' }}
                            </small>
                        </div>
                    </div>
                </div>
                @empty
                <p class="text-muted text-center">No activity logs found</p>
                @endforelse

                {{ $activities->links() }}
            </div>
        </div>
    </div>

    <!-- Right Column -->
    <div class="col-md-4">
        <!-- Enrollment History -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-graduation-cap me-2"></i> Enrollment History
            </div>
            <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                @forelse($enrollmentHistory as $enrollment)
                <div class="mb-3 pb-3 border-bottom">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <strong>{{ $enrollment->class->name ?? 'N/A' }}</strong>
                            <br><small class="text-muted">{{ $enrollment->class->subject->name ?? '' }}</small>
                        </div>
                        @if($enrollment->status === 'active')
                            <span class="badge bg-success">Active</span>
                        @elseif($enrollment->status === 'expired')
                            <span class="badge bg-secondary">Expired</span>
                        @elseif($enrollment->status === 'cancelled')
                            <span class="badge bg-danger">Cancelled</span>
                        @else
                            <span class="badge bg-warning">{{ ucfirst($enrollment->status) }}</span>
                        @endif
                    </div>
                    <small class="text-muted">
                        {{ $enrollment->start_date?->format('d M Y') }} - {{ $enrollment->end_date?->format('d M Y') ?? 'Ongoing' }}
                    </small>
                </div>
                @empty
                <p class="text-muted text-center mb-0">No enrollment history</p>
                @endforelse
            </div>
        </div>

        <!-- Payment History -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-money-bill-wave me-2"></i> Payment History
            </div>
            <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                @forelse($paymentHistory as $payment)
                <div class="mb-3 pb-3 border-bottom">
                    <div class="d-flex justify-content-between">
                        <strong>RM {{ number_format($payment->amount, 2) }}</strong>
                        @if($payment->status === 'completed')
                            <span class="badge bg-success">Completed</span>
                        @elseif($payment->status === 'pending')
                            <span class="badge bg-warning">Pending</span>
                        @else
                            <span class="badge bg-secondary">{{ ucfirst($payment->status) }}</span>
                        @endif
                    </div>
                    <small class="text-muted">
                        {{ $payment->payment_date?->format('d M Y') ?? $payment->created_at->format('d M Y') }}
                        | {{ ucfirst($payment->payment_method ?? 'N/A') }}
                    </small>
                    @if($payment->invoice)
                    <br><small class="text-muted">Invoice: {{ $payment->invoice->invoice_number }}</small>
                    @endif
                </div>
                @empty
                <p class="text-muted text-center mb-0">No payment history</p>
                @endforelse
            </div>
        </div>

        <!-- Trial Class History -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-chalkboard me-2"></i> Trial Class History
            </div>
            <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                @forelse($trialHistory as $trial)
                <div class="mb-3 pb-3 border-bottom">
                    <div class="d-flex justify-content-between">
                        <strong>{{ $trial->class->name ?? 'N/A' }}</strong>
                        @if($trial->status === 'attended')
                            <span class="badge bg-success">Attended</span>
                        @elseif($trial->status === 'no_show')
                            <span class="badge bg-danger">No Show</span>
                        @elseif($trial->status === 'converted')
                            <span class="badge bg-primary">Converted</span>
                        @else
                            <span class="badge bg-secondary">{{ ucfirst($trial->status) }}</span>
                        @endif
                    </div>
                    <small class="text-muted">{{ $trial->scheduled_date->format('d M Y') }}</small>
                </div>
                @empty
                <p class="text-muted text-center mb-0">No trial class history</p>
                @endforelse
            </div>
        </div>

        <!-- Referral History -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-users me-2"></i> Referral History
            </div>
            <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                @forelse($referralHistory as $referral)
                <div class="mb-3 pb-3 border-bottom">
                    <div class="d-flex justify-content-between">
                        <strong>{{ $referral->referred->user->name ?? 'N/A' }}</strong>
                        @if($referral->status === 'completed')
                            <span class="badge bg-success">Completed</span>
                        @elseif($referral->status === 'pending')
                            <span class="badge bg-warning">Pending</span>
                        @else
                            <span class="badge bg-secondary">{{ ucfirst($referral->status) }}</span>
                        @endif
                    </div>
                    <small class="text-muted">
                        Code: {{ $referral->referral_code }}
                        @if($referral->completed_at)
                        <br>Completed: {{ $referral->completed_at->format('d M Y') }}
                        @endif
                    </small>
                </div>
                @empty
                <p class="text-muted text-center mb-0">No referral history</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
