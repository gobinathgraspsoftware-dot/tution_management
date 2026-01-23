@extends('layouts.app')

@section('title', 'Seminar Details')
@section('page-title', 'Seminar Details')

@section('content')
<div class="container-fluid">
    <!-- Back Button -->
    <div class="mb-3">
        <a href="{{ route('staff.seminars.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Seminars
        </a>
    </div>

    <!-- Seminar Header -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <div class="d-flex align-items-start">
                        @if($seminar->image)
                        <img src="{{ Storage::url($seminar->image) }}" alt="{{ $seminar->name }}" class="rounded me-3" style="width: 120px; height: 120px; object-fit: cover;">
                        @else
                        <div class="bg-light rounded me-3 d-flex align-items-center justify-content-center" style="width: 120px; height: 120px;">
                            <i class="fas fa-calendar-alt fa-3x text-muted"></i>
                        </div>
                        @endif
                        <div>
                            <div class="d-flex align-items-center mb-2">
                                <span class="badge bg-secondary me-2">{{ $seminar->code }}</span>
                                @php
                                    $statusColors = [
                                        'draft' => 'secondary',
                                        'open' => 'success',
                                        'closed' => 'warning',
                                        'completed' => 'info',
                                        'cancelled' => 'danger'
                                    ];
                                @endphp
                                <span class="badge bg-{{ $statusColors[$seminar->status] ?? 'secondary' }}">
                                    {{ ucfirst($seminar->status) }}
                                </span>
                            </div>
                            <h3 class="mb-1">{{ $seminar->name }}</h3>
                            @if($seminar->facilitator)
                            <p class="text-muted mb-2">
                                <i class="fas fa-user me-1"></i> Facilitator: {{ $seminar->facilitator }}
                            </p>
                            @endif
                            @php
                                $typeColors = [
                                    'spm' => 'primary',
                                    'workshop' => 'info',
                                    'seminar' => 'success',
                                    'camp' => 'warning',
                                    'other' => 'secondary'
                                ];
                            @endphp
                            <span class="badge bg-{{ $typeColors[$seminar->type] ?? 'secondary' }}">
                                {{ ucfirst($seminar->type) }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-md-end">
                    @can('view-seminar-participants')
                    <a href="{{ route('staff.seminars.participants', $seminar) }}" class="btn btn-primary">
                        <i class="fas fa-users me-1"></i> View Participants
                    </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Left Column -->
        <div class="col-lg-8">
            <!-- Seminar Details -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Seminar Information</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Date</label>
                            <p class="mb-0">
                                <i class="fas fa-calendar-day me-2 text-primary"></i>
                                <strong>{{ $seminar->date->format('l, d F Y') }}</strong>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Time</label>
                            <p class="mb-0">
                                <i class="fas fa-clock me-2 text-primary"></i>
                                @if($seminar->start_time)
                                    {{ \Carbon\Carbon::parse($seminar->start_time)->format('h:i A') }}
                                    @if($seminar->end_time)
                                    - {{ \Carbon\Carbon::parse($seminar->end_time)->format('h:i A') }}
                                    @endif
                                @else
                                    <span class="text-muted">Not specified</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Venue</label>
                            <p class="mb-0">
                                @if($seminar->is_online)
                                    <i class="fas fa-video me-2 text-info"></i>
                                    <span class="badge bg-info">Online</span>
                                    @if($seminar->meeting_link)
                                    <br>
                                    <a href="{{ $seminar->meeting_link }}" target="_blank" class="small">
                                        <i class="fas fa-external-link-alt me-1"></i> Join Meeting
                                    </a>
                                    @endif
                                @else
                                    <i class="fas fa-map-marker-alt me-2 text-danger"></i>
                                    {{ $seminar->venue ?? 'Not specified' }}
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Capacity</label>
                            <p class="mb-0">
                                <i class="fas fa-users me-2 text-success"></i>
                                @if($seminar->capacity)
                                    {{ $seminar->current_participants ?? 0 }} / {{ $seminar->capacity }} participants
                                @else
                                    Unlimited
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Regular Fee</label>
                            <p class="mb-0">
                                <i class="fas fa-money-bill-wave me-2 text-success"></i>
                                <strong>RM {{ number_format($seminar->regular_fee, 2) }}</strong>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Early Bird Fee</label>
                            <p class="mb-0">
                                <i class="fas fa-tags me-2 text-warning"></i>
                                @if($seminar->early_bird_fee)
                                    <strong>RM {{ number_format($seminar->early_bird_fee, 2) }}</strong>
                                    @if($seminar->early_bird_deadline)
                                        <br>
                                        <small class="text-muted">
                                            Until {{ $seminar->early_bird_deadline->format('d M Y') }}
                                            @if($seminar->early_bird_deadline->isPast())
                                                <span class="badge bg-danger">Expired</span>
                                            @endif
                                        </small>
                                    @endif
                                @else
                                    <span class="text-muted">Not available</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Registration Deadline</label>
                            <p class="mb-0">
                                <i class="fas fa-hourglass-half me-2 text-warning"></i>
                                @if($seminar->registration_deadline)
                                    {{ $seminar->registration_deadline->format('d M Y') }}
                                    @if($seminar->registration_deadline->isPast())
                                        <span class="badge bg-danger">Closed</span>
                                    @endif
                                @else
                                    <span class="text-muted">Not specified</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    @if($seminar->description)
                    <hr>
                    <label class="form-label text-muted small">Description</label>
                    <p class="mb-0">{!! nl2br(e($seminar->description)) !!}</p>
                    @endif
                </div>
            </div>

            <!-- Recent Participants -->
            @can('view-seminar-participants')
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-users me-2"></i>Recent Participants</h5>
                    <a href="{{ route('staff.seminars.participants', $seminar) }}" class="btn btn-sm btn-outline-primary">
                        View All
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Contact</th>
                                    <th class="text-center">Payment</th>
                                    <th class="text-center">Attendance</th>
                                    <th>Registered</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($seminar->participants->take(5) as $participant)
                                <tr>
                                    <td>
                                        <strong>{{ $participant->name }}</strong>
                                        @if($participant->school)
                                        <br><small class="text-muted">{{ $participant->school }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <small>
                                            <i class="fas fa-envelope me-1"></i>{{ $participant->email }}<br>
                                            <i class="fas fa-phone me-1"></i>{{ $participant->phone }}
                                        </small>
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $paymentColors = [
                                                'paid' => 'success',
                                                'pending' => 'warning',
                                                'refunded' => 'danger'
                                            ];
                                        @endphp
                                        <span class="badge bg-{{ $paymentColors[$participant->payment_status] ?? 'secondary' }}">
                                            {{ ucfirst($participant->payment_status) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if($participant->attendance_status)
                                            @php
                                                $attendanceColors = [
                                                    'attended' => 'success',
                                                    'absent' => 'danger',
                                                    'no_show' => 'warning'
                                                ];
                                            @endphp
                                            <span class="badge bg-{{ $attendanceColors[$participant->attendance_status] ?? 'secondary' }}">
                                                {{ ucfirst(str_replace('_', ' ', $participant->attendance_status)) }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">Not Marked</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small>{{ $participant->registration_date ? $participant->registration_date->format('d M Y') : '-' }}</small>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-3 text-muted">
                                        No participants registered yet.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endcan
        </div>

        <!-- Right Column - Statistics -->
        <div class="col-lg-4">
            <!-- Participant Statistics -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Participant Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="border rounded p-3 text-center">
                                <h3 class="mb-0 text-primary">{{ $participantStats['total'] }}</h3>
                                <small class="text-muted">Total Registered</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-3 text-center">
                                <h3 class="mb-0 text-success">{{ $participantStats['paid'] }}</h3>
                                <small class="text-muted">Paid</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-3 text-center">
                                <h3 class="mb-0 text-warning">{{ $participantStats['pending'] }}</h3>
                                <small class="text-muted">Pending Payment</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-3 text-center">
                                <h3 class="mb-0 text-info">{{ $participantStats['attended'] }}</h3>
                                <small class="text-muted">Attended</small>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Total Revenue:</span>
                        <strong class="text-success">RM {{ number_format($participantStats['total_revenue'], 2) }}</strong>
                    </div>

                    @if($seminar->capacity)
                    <div class="progress" style="height: 25px;">
                        @php
                            $percentage = min(100, ($participantStats['total'] / $seminar->capacity) * 100);
                        @endphp
                        <div class="progress-bar bg-{{ $percentage >= 90 ? 'danger' : ($percentage >= 70 ? 'warning' : 'success') }}"
                             role="progressbar"
                             style="width: {{ $percentage }}%"
                             aria-valuenow="{{ $percentage }}"
                             aria-valuemin="0"
                             aria-valuemax="100">
                            {{ round($percentage) }}% Full
                        </div>
                    </div>
                    <small class="text-muted">
                        {{ $seminar->capacity - $participantStats['total'] }} spots remaining
                    </small>
                    @endif
                </div>
            </div>

            <!-- Quick Info -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Quick Info</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="fas fa-hashtag text-muted me-2"></i>
                            <strong>Code:</strong> {{ $seminar->code }}
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-tag text-muted me-2"></i>
                            <strong>Type:</strong> {{ ucfirst($seminar->type) }}
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-calendar text-muted me-2"></i>
                            <strong>Date:</strong> {{ $seminar->date->format('d M Y') }}
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-flag text-muted me-2"></i>
                            <strong>Status:</strong>
                            <span class="badge bg-{{ $statusColors[$seminar->status] ?? 'secondary' }}">
                                {{ ucfirst($seminar->status) }}
                            </span>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-clock text-muted me-2"></i>
                            <strong>Created:</strong> {{ $seminar->created_at->format('d M Y') }}
                        </li>
                        @if($seminar->updated_at != $seminar->created_at)
                        <li>
                            <i class="fas fa-edit text-muted me-2"></i>
                            <strong>Updated:</strong> {{ $seminar->updated_at->format('d M Y') }}
                        </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
