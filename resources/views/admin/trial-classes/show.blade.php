@extends('layouts.app')

@section('title', 'Trial Class Details')
@section('page-title', 'Trial Class Details')

@section('content')
<div class="page-header">
    <div>
        <h1><i class="fas fa-chalkboard me-2"></i> Trial Class Details</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.trial-classes.index') }}">Trial Classes</a></li>
                <li class="breadcrumb-item active">Details</li>
            </ol>
        </nav>
    </div>
    <div>
        <a href="{{ route('admin.trial-classes.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
        @if($trialClass->status === 'attended' && $trialClass->conversion_status === 'pending')
            <form action="{{ route('admin.trial-classes.convert', $trialClass) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-primary" onclick="return confirm('Convert this trial to full enrollment?')">
                    <i class="fas fa-user-plus me-1"></i> Convert to Enrollment
                </button>
            </form>
        @endif
    </div>
</div>

<div class="row">
    <!-- Main Info -->
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-info-circle me-2"></i> Trial Class Information</span>
                <div>
                    @if($trialClass->status === 'pending')
                        <span class="badge bg-warning fs-6">Pending</span>
                    @elseif($trialClass->status === 'approved')
                        <span class="badge bg-info fs-6">Approved</span>
                    @elseif($trialClass->status === 'attended')
                        <span class="badge bg-success fs-6">Attended</span>
                    @elseif($trialClass->status === 'no_show')
                        <span class="badge bg-danger fs-6">No Show</span>
                    @elseif($trialClass->status === 'converted')
                        <span class="badge bg-primary fs-6">Converted</span>
                    @elseif($trialClass->status === 'cancelled')
                        <span class="badge bg-secondary fs-6">Cancelled</span>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted">Student Information</h6>
                        <table class="table table-borderless">
                            <tr>
                                <td width="40%">Name</td>
                                <td>
                                    <strong>
                                        @if($trialClass->student)
                                            <a href="{{ route('admin.students.profile', $trialClass->student) }}">
                                                {{ $trialClass->student->user->name }}
                                            </a>
                                        @else
                                            {{ $trialClass->student_name ?? 'N/A' }}
                                        @endif
                                    </strong>
                                </td>
                            </tr>
                            @if($trialClass->student)
                            <tr>
                                <td>Student ID</td>
                                <td><strong>{{ $trialClass->student->student_id }}</strong></td>
                            </tr>
                            @endif
                            <tr>
                                <td>Parent Name</td>
                                <td><strong>{{ $trialClass->parent_name ?? 'N/A' }}</strong></td>
                            </tr>
                            <tr>
                                <td>Parent Phone</td>
                                <td><strong>{{ $trialClass->parent_phone ?? 'N/A' }}</strong></td>
                            </tr>
                            <tr>
                                <td>Parent Email</td>
                                <td><strong>{{ $trialClass->parent_email ?? 'N/A' }}</strong></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted">Class Information</h6>
                        <table class="table table-borderless">
                            <tr>
                                <td width="40%">Class</td>
                                <td><strong>{{ $trialClass->class->name ?? 'N/A' }}</strong></td>
                            </tr>
                            <tr>
                                <td>Subject</td>
                                <td><strong>{{ $trialClass->class->subject->name ?? 'N/A' }}</strong></td>
                            </tr>
                            <tr>
                                <td>Teacher</td>
                                <td><strong>{{ $trialClass->class->teacher->user->name ?? 'N/A' }}</strong></td>
                            </tr>
                            <tr>
                                <td>Type</td>
                                <td><strong>{{ ucfirst($trialClass->class->type ?? 'N/A') }}</strong></td>
                            </tr>
                            <tr>
                                <td>Location</td>
                                <td><strong>{{ $trialClass->class->location ?? 'Arena Matriks Centre' }}</strong></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted">Schedule</h6>
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-calendar-day fa-2x text-primary me-3"></i>
                            <div>
                                <strong>{{ $trialClass->scheduled_date->format('l, d F Y') }}</strong>
                                <br>
                                <span class="text-muted">
                                    {{ $trialClass->scheduled_time ? $trialClass->scheduled_time->format('h:i A') : 'Time TBD' }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted">Conversion Status</h6>
                        @if($trialClass->conversion_status === 'converted')
                            <span class="badge bg-success fs-6">Converted to Enrollment</span>
                        @elseif($trialClass->conversion_status === 'declined')
                            <span class="badge bg-danger fs-6">Declined</span>
                        @else
                            <span class="badge bg-secondary fs-6">Pending Decision</span>
                        @endif
                    </div>
                </div>

                @if($trialClass->feedback)
                <hr>
                <h6 class="text-muted">Feedback</h6>
                <p class="mb-0">{{ $trialClass->feedback }}</p>
                @endif

                @if($trialClass->notes)
                <hr>
                <h6 class="text-muted">Notes</h6>
                <p class="mb-0">{{ $trialClass->notes }}</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Actions Sidebar -->
    <div class="col-md-4">
        <!-- Update Status -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-edit me-2"></i> Update Status
            </div>
            <div class="card-body">
                <form action="{{ route('admin.trial-classes.update-status', $trialClass) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="pending" {{ $trialClass->status === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ $trialClass->status === 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="attended" {{ $trialClass->status === 'attended' ? 'selected' : '' }}>Attended</option>
                            <option value="no_show" {{ $trialClass->status === 'no_show' ? 'selected' : '' }}>No Show</option>
                            <option value="converted" {{ $trialClass->status === 'converted' ? 'selected' : '' }}>Converted</option>
                            <option value="cancelled" {{ $trialClass->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Feedback</label>
                        <textarea name="feedback" class="form-control" rows="3">{{ $trialClass->feedback }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2">{{ $trialClass->notes }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-save me-1"></i> Update
                    </button>
                </form>
            </div>
        </div>

        <!-- Mark Declined -->
        @if($trialClass->status === 'attended' && $trialClass->conversion_status === 'pending')
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-times-circle me-2"></i> Mark as Declined
            </div>
            <div class="card-body">
                <form action="{{ route('admin.trial-classes.decline', $trialClass) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Reason for Declining</label>
                        <textarea name="decline_reason" class="form-control" rows="3" required placeholder="Enter reason..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Mark this trial as declined?')">
                        <i class="fas fa-times me-1"></i> Mark Declined
                    </button>
                </form>
            </div>
        </div>
        @endif

        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-bolt me-2"></i> Quick Actions
            </div>
            <div class="card-body">
                @if($trialClass->parent_phone)
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $trialClass->parent_phone) }}" target="_blank" class="btn btn-success w-100 mb-2">
                    <i class="fab fa-whatsapp me-1"></i> WhatsApp Parent
                </a>
                @endif
                @if($trialClass->parent_email)
                <a href="mailto:{{ $trialClass->parent_email }}" class="btn btn-info w-100 mb-2">
                    <i class="fas fa-envelope me-1"></i> Email Parent
                </a>
                @endif
                @if($trialClass->student)
                <a href="{{ route('admin.students.profile', $trialClass->student) }}" class="btn btn-secondary w-100">
                    <i class="fas fa-user me-1"></i> View Student Profile
                </a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
