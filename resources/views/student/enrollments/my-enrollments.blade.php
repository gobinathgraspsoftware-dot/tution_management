@extends('layouts.app')

@section('title', 'My Enrollments')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-user-graduate"></i> My Enrollments
        </h1>
        <div>
            <a href="{{ route('student.enrollments.browse-classes') }}" class="btn btn-info">
                <i class="fas fa-search"></i> Browse Classes
            </a>
            <a href="{{ route('student.enrollments.browse-packages') }}" class="btn btn-success">
                <i class="fas fa-box"></i> Browse Packages
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Enrollments</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-book fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Active Classes</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['active'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
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

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Expired</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['expired'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enrollments List -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">My Active Enrollments</h6>
        </div>
        <div class="card-body">
            @forelse($enrollments as $enrollment)
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

                            <div class="mb-2">
                                <strong><i class="fas fa-calendar-alt"></i> Schedule:</strong>
                                @if($enrollment->class->schedules->isNotEmpty())
                                    @foreach($enrollment->class->schedules as $schedule)
                                        <span class="badge badge-secondary">
                                            {{ $schedule->day_of_week }}:
                                            {{ \Carbon\Carbon::parse($schedule->start_time)->format('g:i A') }}
                                        </span>
                                    @endforeach
                                @endif
                            </div>
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
                            @elseif($enrollment->status == 'trial')
                                <span class="badge badge-info badge-pill mb-2">Trial</span>
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

                            <!-- Action Buttons -->
                            <div class="btn-group-vertical" role="group">
                                <a href="{{ route('student.enrollments.show', $enrollment) }}"
                                   class="btn btn-sm btn-primary mb-1">
                                    <i class="fas fa-eye"></i> View Details
                                </a>
                                @if($enrollment->class_id && Route::has('student.materials.index'))
                                <a href="{{ route('student.materials.index', ['class_id' => $enrollment->class_id]) }}"
                                   class="btn btn-sm btn-info mb-1">
                                    <i class="fas fa-book"></i> Materials
                                </a>
                                @endif
                                @if(Route::has('student.invoices.index'))
                                <a href="{{ route('student.invoices.index') }}"
                                   class="btn btn-sm btn-success mb-1">
                                    <i class="fas fa-file-invoice"></i> Payments
                                </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> You are not enrolled in any classes yet.
                <a href="{{ route('student.enrollments.browse-classes') }}" class="alert-link">Browse available classes</a>
                or <a href="{{ route('student.enrollments.browse-packages') }}" class="alert-link">check out our packages</a>!
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
