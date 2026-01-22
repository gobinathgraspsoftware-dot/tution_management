@extends('layouts.app')

@section('title', 'Trial Class Details')

@section('styles')
<style>
    .trial-header {
        border-radius: 10px;
        padding: 25px;
        margin-bottom: 20px;
        color: white;
    }
    .trial-header.status-pending { background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%); }
    .trial-header.status-approved { background: linear-gradient(135deg, #17a2b8 0%, #138496 100%); }
    .trial-header.status-attended { background: linear-gradient(135deg, #28a745 0%, #218838 100%); }
    .trial-header.status-no_show { background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); }
    .trial-header.status-converted { background: linear-gradient(135deg, #6f42c1 0%, #5a32a3 100%); }
    .trial-header.status-cancelled { background: linear-gradient(135deg, #6c757d 0%, #545b62 100%); }
    
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
    
    .timeline {
        position: relative;
        padding-left: 30px;
    }
    .timeline::before {
        content: '';
        position: absolute;
        left: 10px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e9ecef;
    }
    .timeline-item {
        position: relative;
        margin-bottom: 20px;
    }
    .timeline-item::before {
        content: '';
        position: absolute;
        left: -24px;
        top: 5px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #667eea;
        border: 2px solid white;
        box-shadow: 0 0 0 2px #667eea;
    }
    .schedule-badge {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 10px 15px;
        margin-bottom: 10px;
        display: inline-flex;
        align-items: center;
        gap: 10px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('staff.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('staff.trial-classes.index') }}">Trial Classes</a></li>
            <li class="breadcrumb-item active">Trial Details</li>
        </ol>
    </nav>

    <!-- Trial Header -->
    <div class="trial-header status-{{ $trialClass->status }}">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="mb-1">
                    @if($trialClass->student && $trialClass->student->user)
                        {{ $trialClass->student->user->name }}
                    @else
                        {{ $trialClass->student_name ?? 'N/A' }}
                    @endif
                </h2>
                <p class="mb-1 opacity-75">
                    <i class="fas fa-book me-2"></i>{{ $trialClass->class->name ?? 'N/A' }} - {{ $trialClass->class->subject->name ?? 'N/A' }}
                </p>
                <p class="mb-0 opacity-75">
                    <i class="fas fa-calendar me-2"></i>{{ $trialClass->scheduled_date ? $trialClass->scheduled_date->format('l, d M Y') : 'N/A' }}
                    @if($trialClass->scheduled_time)
                        at {{ $trialClass->scheduled_time->format('h:i A') }}
                    @endif
                </p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <span class="badge bg-light text-dark fs-6 px-3 py-2">
                    @switch($trialClass->status)
                        @case('pending')
                            <i class="fas fa-clock me-1"></i>Pending
                            @break
                        @case('approved')
                            <i class="fas fa-check me-1"></i>Approved
                            @break
                        @case('attended')
                            <i class="fas fa-user-check me-1"></i>Attended
                            @break
                        @case('no_show')
                            <i class="fas fa-user-times me-1"></i>No Show
                            @break
                        @case('converted')
                            <i class="fas fa-exchange-alt me-1"></i>Converted
                            @break
                        @case('cancelled')
                            <i class="fas fa-times me-1"></i>Cancelled
                            @break
                        @default
                            {{ ucfirst($trialClass->status) }}
                    @endswitch
                </span>
                @if($trialClass->scheduled_date && $trialClass->scheduled_date->isToday())
                    <span class="badge bg-danger ms-2 fs-6 px-3 py-2">TODAY</span>
                @endif
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Left Column -->
        <div class="col-lg-4">
            <!-- Student/Parent Information -->
            <div class="card info-card">
                <div class="card-header">
                    <i class="fas fa-user me-2"></i>Contact Information
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="info-label">Student Name</div>
                        <div class="info-value">
                            @if($trialClass->student && $trialClass->student->user)
                                {{ $trialClass->student->user->name }}
                                <span class="badge bg-success ms-1">Registered</span>
                            @else
                                {{ $trialClass->student_name ?? 'N/A' }}
                                <span class="badge bg-secondary ms-1">New</span>
                            @endif
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="info-label">Parent/Guardian Name</div>
                        <div class="info-value">{{ $trialClass->parent_name ?? 'N/A' }}</div>
                    </div>
                    <div class="mb-3">
                        <div class="info-label">Phone</div>
                        <div class="info-value">
                            @if($trialClass->parent_phone)
                                <a href="tel:{{ $trialClass->parent_phone }}">
                                    <i class="fas fa-phone me-1"></i>{{ $trialClass->parent_phone }}
                                </a>
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $trialClass->parent_phone) }}" 
                                   target="_blank" class="btn btn-sm btn-success ms-2">
                                    <i class="fab fa-whatsapp"></i>
                                </a>
                            @else
                                N/A
                            @endif
                        </div>
                    </div>
                    <div class="mb-0">
                        <div class="info-label">Email</div>
                        <div class="info-value">
                            @if($trialClass->parent_email)
                                <a href="mailto:{{ $trialClass->parent_email }}">
                                    <i class="fas fa-envelope me-1"></i>{{ $trialClass->parent_email }}
                                </a>
                            @else
                                N/A
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Trial Status -->
            <div class="card info-card">
                <div class="card-header">
                    <i class="fas fa-info-circle me-2"></i>Trial Status
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="info-label">Current Status</div>
                        <div class="info-value">
                            @switch($trialClass->status)
                                @case('pending')
                                    <span class="badge bg-warning text-dark">Pending Approval</span>
                                    @break
                                @case('approved')
                                    <span class="badge bg-info">Approved</span>
                                    @break
                                @case('attended')
                                    <span class="badge bg-success">Attended</span>
                                    @break
                                @case('no_show')
                                    <span class="badge bg-danger">No Show</span>
                                    @break
                                @case('converted')
                                    <span class="badge bg-primary">Converted to Full Enrollment</span>
                                    @break
                                @case('cancelled')
                                    <span class="badge bg-secondary">Cancelled</span>
                                    @break
                            @endswitch
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="info-label">Conversion Status</div>
                        <div class="info-value">
                            @switch($trialClass->conversion_status)
                                @case('converted')
                                    <span class="badge bg-success">✓ Converted</span>
                                    @break
                                @case('declined')
                                    <span class="badge bg-danger">✗ Declined</span>
                                    @break
                                @default
                                    <span class="badge bg-secondary">Pending</span>
                            @endswitch
                        </div>
                    </div>
                    <div class="mb-0">
                        <div class="info-label">Created</div>
                        <div class="info-value">
                            {{ $trialClass->created_at ? $trialClass->created_at->format('d M Y, h:i A') : 'N/A' }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mark Attendance (if applicable) -->
            @if(in_array($trialClass->status, ['pending', 'approved']))
            <div class="card info-card border-primary">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-check-square me-2"></i>Mark Attendance
                </div>
                <div class="card-body">
                    <form action="{{ route('staff.trial-classes.mark-attendance', $trialClass) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Did the student attend?</label>
                            <div class="d-grid gap-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="attended" value="1" id="attended" required>
                                    <label class="form-check-label" for="attended">
                                        <i class="fas fa-check text-success me-1"></i>Yes, Student Attended
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="attended" value="0" id="noShow">
                                    <label class="form-check-label" for="noShow">
                                        <i class="fas fa-times text-danger me-1"></i>No Show
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Feedback (Optional)</label>
                            <textarea name="feedback" class="form-control" rows="3" 
                                      placeholder="Any notes about the trial class...">{{ $trialClass->feedback }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-save me-1"></i>Save Attendance
                        </button>
                    </form>
                </div>
            </div>
            @endif
        </div>

        <!-- Right Column -->
        <div class="col-lg-8">
            <!-- Class Information -->
            <div class="card info-card">
                <div class="card-header">
                    <i class="fas fa-chalkboard me-2"></i>Class Information
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="info-label">Class Name</div>
                                <div class="info-value">{{ $trialClass->class->name ?? 'N/A' }}</div>
                            </div>
                            <div class="mb-3">
                                <div class="info-label">Subject</div>
                                <div class="info-value">{{ $trialClass->class->subject->name ?? 'N/A' }}</div>
                            </div>
                            <div class="mb-3">
                                <div class="info-label">Teacher</div>
                                <div class="info-value">
                                    @if($trialClass->class && $trialClass->class->teacher && $trialClass->class->teacher->user)
                                        {{ $trialClass->class->teacher->user->name }}
                                    @else
                                        N/A
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="info-label">Class Type</div>
                                <div class="info-value">
                                    @if($trialClass->class)
                                        <span class="badge bg-{{ $trialClass->class->type == 'online' ? 'info' : 'secondary' }}">
                                            {{ ucfirst($trialClass->class->type ?? 'N/A') }}
                                        </span>
                                    @else
                                        N/A
                                    @endif
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="info-label">Grade Level</div>
                                <div class="info-value">{{ $trialClass->class->grade_level ?? 'N/A' }}</div>
                            </div>
                            @if($trialClass->class && $trialClass->class->type == 'online' && $trialClass->class->meeting_link)
                            <div class="mb-3">
                                <div class="info-label">Meeting Link</div>
                                <div class="info-value">
                                    <a href="{{ $trialClass->class->meeting_link }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-video me-1"></i>Join Meeting
                                    </a>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Class Schedule -->
                    @if($scheduleInfo && count($scheduleInfo) > 0)
                    <hr>
                    <div class="info-label mb-2">Regular Class Schedule</div>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($scheduleInfo as $schedule)
                            <div class="schedule-badge">
                                <i class="fas fa-calendar-day text-primary"></i>
                                <span>{{ $schedule['day'] }}</span>
                                <span class="text-muted">{{ $schedule['time'] }}</span>
                            </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>

            <!-- Trial Schedule -->
            <div class="card info-card">
                <div class="card-header">
                    <i class="fas fa-calendar-alt me-2"></i>Trial Schedule
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <div class="info-label">Scheduled Date</div>
                                <div class="info-value fs-5">
                                    {{ $trialClass->scheduled_date ? $trialClass->scheduled_date->format('l') : 'N/A' }}
                                    <br>
                                    <span class="text-muted">
                                        {{ $trialClass->scheduled_date ? $trialClass->scheduled_date->format('d M Y') : '' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <div class="info-label">Scheduled Time</div>
                                <div class="info-value fs-5">
                                    @if($trialClass->scheduled_time)
                                        {{ $trialClass->scheduled_time->format('h:i A') }}
                                    @else
                                        <span class="text-muted">Time TBD</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <div class="info-label">Days Until Trial</div>
                                <div class="info-value fs-5">
                                    @if($trialClass->scheduled_date)
                                        @php
                                            $daysUntil = now()->startOfDay()->diffInDays($trialClass->scheduled_date->startOfDay(), false);
                                        @endphp
                                        @if($daysUntil > 0)
                                            <span class="text-info">{{ $daysUntil }} day{{ $daysUntil != 1 ? 's' : '' }}</span>
                                        @elseif($daysUntil == 0)
                                            <span class="text-success fw-bold">Today!</span>
                                        @else
                                            <span class="text-muted">{{ abs($daysUntil) }} day{{ abs($daysUntil) != 1 ? 's' : '' }} ago</span>
                                        @endif
                                    @else
                                        N/A
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notes & Feedback -->
            <div class="card info-card">
                <div class="card-header">
                    <i class="fas fa-comment-alt me-2"></i>Notes & Feedback
                </div>
                <div class="card-body">
                    @if($trialClass->feedback)
                        <div class="mb-3">
                            <div class="info-label">Feedback</div>
                            <div class="info-value">{{ $trialClass->feedback }}</div>
                        </div>
                    @endif
                    @if($trialClass->notes)
                        <div class="mb-0">
                            <div class="info-label">Internal Notes</div>
                            <div class="info-value">{{ $trialClass->notes }}</div>
                        </div>
                    @endif
                    @if(!$trialClass->feedback && !$trialClass->notes)
                        <p class="text-muted mb-0 text-center py-3">No notes or feedback recorded yet.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="mt-4">
        <a href="{{ route('staff.trial-classes.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Trial Classes
        </a>
    </div>
</div>
@endsection
