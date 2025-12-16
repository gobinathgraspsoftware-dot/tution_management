@extends('layouts.app')

@section('title', 'My Children\'s Enrollments')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-user-graduate"></i> My Children's Enrollments
        </h1>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Enrollments</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Active Enrollments</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['active'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Expiring Soon</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['expiring_soon'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter by Child -->
    @if($children->count() > 1)
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('parent.enrollments.index') }}">
                <div class="row align-items-end">
                    <div class="col-md-10">
                        <label for="student_id">Filter by Child</label>
                        <select class="form-control" id="student_id" name="student_id">
                            <option value="">All Children</option>
                            @foreach($children as $child)
                                <option value="{{ $child->id }}" {{ request('student_id') == $child->id ? 'selected' : '' }}>
                                    {{ $child->user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Children's Enrollments -->
    @forelse($children as $child)
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-gradient-primary text-white">
            <h5 class="mb-0">
                <i class="fas fa-user"></i> {{ $child->user->name }}
                <span class="badge badge-light text-primary ml-2">{{ $child->enrollments->count() }} Enrollments</span>
            </h5>
        </div>
        <div class="card-body">
            @if($child->enrollments->count() > 0)
                @foreach($child->enrollments as $enrollment)
                <div class="card mb-3 border-left-primary">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <h5 class="text-primary">
                                    @if($enrollment->package)
                                        <span class="badge badge-info">Package</span> {{ $enrollment->package->name }}
                                    @else
                                        {{ $enrollment->class->name }}
                                    @endif
                                </h5>

                                @if($enrollment->class)
                                <div class="mb-2">
                                    <strong>Subject:</strong> {{ $enrollment->class->subject->name }}<br>
                                    <strong>Teacher:</strong> {{ $enrollment->class->teacher->user->name }}
                                </div>

                                @if($enrollment->class->schedules->isNotEmpty())
                                <div class="mb-2">
                                    <strong><i class="fas fa-calendar-alt"></i> Schedule:</strong>
                                    @foreach($enrollment->class->schedules as $schedule)
                                        <span class="badge badge-secondary">
                                            {{ $schedule->day_of_week }}:
                                            {{ \Carbon\Carbon::parse($schedule->start_time)->format('g:i A') }}
                                        </span>
                                    @endforeach
                                </div>
                                @endif
                                @endif

                                <div class="mb-2">
                                    <strong>Duration:</strong>
                                    {{ $enrollment->start_date->format('d M Y') }} -
                                    @if($enrollment->end_date)
                                        {{ $enrollment->end_date->format('d M Y') }}
                                    @else
                                        Ongoing
                                    @endif
                                </div>

                                <div class="mb-2">
                                    <strong>Monthly Fee:</strong>
                                    <span class="text-success font-weight-bold">RM {{ number_format($enrollment->monthly_fee, 2) }}</span>
                                    <br>
                                    <small class="text-muted">Payment due on day {{ $enrollment->payment_cycle_day }} of each month</small>
                                </div>
                            </div>

                            <div class="col-md-4 text-right">
                                <!-- Status Badge -->
                                @if($enrollment->status == 'active')
                                    <span class="badge badge-success badge-pill mb-2">Active</span>
                                @elseif($enrollment->status == 'suspended')
                                    <span class="badge badge-warning badge-pill mb-2">Suspended</span>
                                @elseif($enrollment->status == 'expired')
                                    <span class="badge badge-danger badge-pill mb-2">Expired</span>
                                @elseif($enrollment->status == 'cancelled')
                                    <span class="badge badge-dark badge-pill mb-2">Cancelled</span>
                                @endif

                                <!-- Days Remaining -->
                                @if($enrollment->end_date && $enrollment->status == 'active')
                                    <div class="mb-3">
                                        @if($enrollment->days_remaining > 30)
                                            <div class="text-success">
                                                <i class="fas fa-clock"></i> {{ $enrollment->days_remaining }} days left
                                            </div>
                                        @elseif($enrollment->days_remaining > 0)
                                            <div class="text-warning">
                                                <i class="fas fa-exclamation-triangle"></i> {{ $enrollment->days_remaining }} days left
                                            </div>
                                        @else
                                            <div class="text-danger">
                                                <i class="fas fa-times-circle"></i> Expired
                                            </div>
                                        @endif
                                    </div>
                                @endif

                                <!-- Action Button -->
                                <a href="{{ route('parent.enrollments.show', $enrollment) }}"
                                   class="btn btn-primary btn-sm">
                                    <i class="fas fa-eye"></i> View Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            @else
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> {{ $child->user->name }} is not enrolled in any classes yet.
                </div>
            @endif
        </div>
    </div>
    @empty
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i> No children found. Please contact the administration to link your children to your account.
            </div>
        </div>
    </div>
    @endforelse
</div>
@endsection
