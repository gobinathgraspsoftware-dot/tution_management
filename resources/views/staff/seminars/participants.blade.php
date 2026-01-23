@extends('layouts.app')

@section('title', 'Seminar Participants')
@section('page-title', 'Seminar Participants')

@section('content')
<div class="container-fluid">
    <!-- Back Button & Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <a href="{{ route('staff.seminars.show', $seminar) }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Seminar
            </a>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('staff.seminars.index') }}">Seminars</a></li>
                <li class="breadcrumb-item"><a href="{{ route('staff.seminars.show', $seminar) }}">{{ $seminar->code }}</a></li>
                <li class="breadcrumb-item active">Participants</li>
            </ol>
        </nav>
    </div>

    <!-- Seminar Info Card -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex align-items-center">
                        @if($seminar->image)
                        <img src="{{ Storage::url($seminar->image) }}" alt="{{ $seminar->name }}" class="rounded me-3" style="width: 60px; height: 60px; object-fit: cover;">
                        @else
                        <div class="bg-light rounded me-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                            <i class="fas fa-calendar-alt fa-2x text-muted"></i>
                        </div>
                        @endif
                        <div>
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
                            <h4 class="mb-0 mt-1">{{ $seminar->name }}</h4>
                            <p class="text-muted mb-0">
                                <i class="fas fa-calendar me-1"></i>{{ $seminar->date->format('d M Y') }}
                                @if($seminar->start_time)
                                    | <i class="fas fa-clock ms-1 me-1"></i>{{ \Carbon\Carbon::parse($seminar->start_time)->format('h:i A') }}
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="row g-2 text-center">
                        <div class="col-4">
                            <div class="bg-light rounded p-2">
                                <h5 class="mb-0 text-primary">{{ $stats['total'] }}</h5>
                                <small class="text-muted">Total</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="bg-light rounded p-2">
                                <h5 class="mb-0 text-success">{{ $stats['paid'] }}</h5>
                                <small class="text-muted">Paid</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="bg-light rounded p-2">
                                <h5 class="mb-0 text-warning">{{ $stats['pending'] }}</h5>
                                <small class="text-muted">Pending</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Row -->
    <div class="row mb-4">
        <div class="col-md-2 col-sm-4 col-6 mb-2">
            <div class="stat-card text-center h-100">
                <div class="stat-icon bg-primary bg-opacity-10 text-primary mx-auto mb-2">
                    <i class="fas fa-users"></i>
                </div>
                <h4 class="mb-0">{{ $stats['total'] }}</h4>
                <small class="text-muted">Total Registered</small>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 col-6 mb-2">
            <div class="stat-card text-center h-100">
                <div class="stat-icon bg-success bg-opacity-10 text-success mx-auto mb-2">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h4 class="mb-0">{{ $stats['paid'] }}</h4>
                <small class="text-muted">Paid</small>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 col-6 mb-2">
            <div class="stat-card text-center h-100">
                <div class="stat-icon bg-warning bg-opacity-10 text-warning mx-auto mb-2">
                    <i class="fas fa-clock"></i>
                </div>
                <h4 class="mb-0">{{ $stats['pending'] }}</h4>
                <small class="text-muted">Pending Payment</small>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 col-6 mb-2">
            <div class="stat-card text-center h-100">
                <div class="stat-icon bg-info bg-opacity-10 text-info mx-auto mb-2">
                    <i class="fas fa-user-check"></i>
                </div>
                <h4 class="mb-0">{{ $stats['attended'] }}</h4>
                <small class="text-muted">Attended</small>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 col-6 mb-2">
            <div class="stat-card text-center h-100">
                <div class="stat-icon bg-danger bg-opacity-10 text-danger mx-auto mb-2">
                    <i class="fas fa-user-times"></i>
                </div>
                <h4 class="mb-0">{{ $stats['absent'] }}</h4>
                <small class="text-muted">Absent</small>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 col-6 mb-2">
            <div class="stat-card text-center h-100">
                <div class="stat-icon bg-secondary bg-opacity-10 text-secondary mx-auto mb-2">
                    <i class="fas fa-user-slash"></i>
                </div>
                <h4 class="mb-0">{{ $stats['no_show'] }}</h4>
                <small class="text-muted">No Show</small>
            </div>
        </div>
    </div>

    <!-- Participants Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-users me-2"></i>Participant List</h5>
            <span class="badge bg-primary">{{ $participants->total() }} participants</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">#</th>
                            <th width="20%">Participant</th>
                            <th width="15%">Contact</th>
                            <th width="12%">School/Grade</th>
                            <th class="text-end" width="10%">Fee</th>
                            <th class="text-center" width="10%">Payment</th>
                            <th class="text-center" width="10%">Attendance</th>
                            <th width="10%">Registered</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($participants as $index => $participant)
                        <tr>
                            <td>{{ $participants->firstItem() + $index }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle bg-{{ ['primary', 'success', 'info', 'warning', 'danger'][($participants->firstItem() + $index) % 5] }} text-white me-2">
                                        {{ strtoupper(substr($participant->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <strong>{{ $participant->name }}</strong>
                                        @if($participant->student)
                                        <br><small class="text-muted">
                                            <i class="fas fa-link me-1"></i>Linked Student
                                        </small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <small>
                                    <i class="fas fa-envelope text-muted me-1"></i>{{ $participant->email }}
                                </small>
                                <br>
                                <small>
                                    <i class="fas fa-phone text-muted me-1"></i>{{ $participant->phone }}
                                </small>
                            </td>
                            <td>
                                @if($participant->school)
                                <small>{{ $participant->school }}</small>
                                @endif
                                @if($participant->grade)
                                <br><span class="badge bg-light text-dark">{{ $participant->grade }}</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <strong>RM {{ number_format($participant->fee_amount, 2) }}</strong>
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
                                @if($participant->payment_method && $participant->payment_status == 'paid')
                                <br><small class="text-muted">{{ ucfirst($participant->payment_method) }}</small>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($participant->attendance_status)
                                    @php
                                        $attendanceColors = [
                                            'attended' => 'success',
                                            'absent' => 'danger',
                                            'no_show' => 'warning'
                                        ];
                                        $attendanceIcons = [
                                            'attended' => 'check-circle',
                                            'absent' => 'times-circle',
                                            'no_show' => 'minus-circle'
                                        ];
                                    @endphp
                                    <span class="badge bg-{{ $attendanceColors[$participant->attendance_status] ?? 'secondary' }}">
                                        <i class="fas fa-{{ $attendanceIcons[$participant->attendance_status] ?? 'question-circle' }} me-1"></i>
                                        {{ ucfirst(str_replace('_', ' ', $participant->attendance_status)) }}
                                    </span>
                                @else
                                    <span class="badge bg-secondary">Not Marked</span>
                                @endif
                            </td>
                            <td>
                                @if($participant->registration_date)
                                <small>{{ $participant->registration_date->format('d M Y') }}</small>
                                <br><small class="text-muted">{{ $participant->registration_date->format('h:i A') }}</small>
                                @else
                                <small class="text-muted">-</small>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-users-slash fa-3x mb-3"></i>
                                    <p>No participants registered yet.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($participants->hasPages())
        <div class="card-footer">
            {{ $participants->links() }}
        </div>
        @endif
    </div>
</div>

<style>
.avatar-circle {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 14px;
}

.stat-card .stat-icon {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>
@endsection
