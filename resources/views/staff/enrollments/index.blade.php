@extends('layouts.app')

@section('title', 'Enrollment Management')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-user-graduate"></i> Enrollment Management
        </h1>
        @can('create-enrollments')
        <a href="{{ route('staff.enrollments.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> New Enrollment
        </a>
        @endcan
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
                            <i class="fas fa-users fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Active</div>
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

    <!-- Filters Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filters</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('staff.enrollments.index') }}">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="search">Search Student</label>
                        <input type="text" class="form-control" id="search" name="search"
                               value="{{ request('search') }}" placeholder="Student name...">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="status">Status</label>
                        <select class="form-control" id="status" name="status">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                            <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="class_id">Class</label>
                        <select class="form-control" id="class_id" name="class_id">
                            <option value="">All Classes</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                    {{ $class->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label>&nbsp;</label>
                        <div>
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-search"></i> Filter
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Enrollments Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">All Enrollments</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Package/Class</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Monthly Fee</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($enrollments as $enrollment)
                        <tr>
                            <td>
                                <strong>{{ $enrollment->student->user->name }}</strong><br>
                                <small class="text-muted">{{ $enrollment->student->student_id }}</small>
                            </td>
                            <td>
                                @if($enrollment->package)
                                    <span class="badge badge-info">Package</span>
                                    {{ $enrollment->package->name }}
                                @else
                                    <span class="badge badge-secondary">Class</span>
                                    {{ $enrollment->class->name }}
                                    <br><small class="text-muted">{{ $enrollment->class->subject->name }}</small>
                                @endif
                            </td>
                            <td>{{ $enrollment->start_date->format('d M Y') }}</td>
                            <td>
                                @if($enrollment->end_date)
                                    {{ $enrollment->end_date->format('d M Y') }}
                                    @if($enrollment->end_date->isPast() && $enrollment->status == 'active')
                                        <span class="badge badge-danger">Expired</span>
                                    @elseif($enrollment->days_remaining <= 30 && $enrollment->status == 'active')
                                        <span class="badge badge-warning">{{ $enrollment->days_remaining }} days left</span>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>RM {{ number_format($enrollment->monthly_fee, 2) }}</td>
                            <td>
                                @if($enrollment->status == 'active')
                                    <span class="badge badge-success">Active</span>
                                @elseif($enrollment->status == 'suspended')
                                    <span class="badge badge-warning">Suspended</span>
                                @elseif($enrollment->status == 'expired')
                                    <span class="badge badge-danger">Expired</span>
                                @elseif($enrollment->status == 'cancelled')
                                    <span class="badge badge-dark">Cancelled</span>
                                @elseif($enrollment->status == 'trial')
                                    <span class="badge badge-info">Trial</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('staff.enrollments.show', $enrollment) }}"
                                   class="btn btn-sm btn-info" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">No enrollments found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-3">
                {{ $enrollments->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
