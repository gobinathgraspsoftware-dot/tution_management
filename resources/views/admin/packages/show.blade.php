@extends('layouts.app')

@section('title', 'View Package')
@section('page-title', $package->name)

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="fas fa-box me-2"></i> {{ $package->name }}</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.packages.index') }}">Packages</a></li>
                <li class="breadcrumb-item active">{{ $package->name }}</li>
            </ol>
        </nav>
    </div>
    <div>
        @can('edit-packages')
        <a href="{{ route('admin.packages.edit', $package) }}" class="btn btn-primary">
            <i class="fas fa-edit me-1"></i> Edit
        </a>
        @endcan
        <a href="{{ route('admin.packages.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to List
        </a>
    </div>
</div>

<div class="row">
    <!-- Package Details -->
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i> Package Details</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th width="40%">Package Code:</th>
                                <td><span class="badge bg-secondary fs-6">{{ $package->code }}</span></td>
                            </tr>
                            <tr>
                                <th>Package Name:</th>
                                <td>{{ $package->name }}</td>
                            </tr>
                            <tr>
                                <th>Type:</th>
                                <td>
                                    @if($package->type == 'online')
                                        <span class="badge bg-info fs-6">Online</span>
                                    @elseif($package->type == 'offline')
                                        <span class="badge bg-warning fs-6">Offline</span>
                                    @else
                                        <span class="badge bg-primary fs-6">Hybrid</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Duration:</th>
                                <td>{{ $package->duration_months }} month(s)</td>
                            </tr>
                            <tr>
                                <th>Max Students:</th>
                                <td>{{ $package->max_students ?? 'Unlimited' }}</td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td>
                                    @if($package->status == 'active')
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Inactive</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th width="40%">Materials:</th>
                                <td>
                                    @if($package->includes_materials)
                                        <span class="badge bg-success"><i class="fas fa-check me-1"></i> Included</span>
                                    @else
                                        <span class="badge bg-secondary">Not Included</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Created:</th>
                                <td>{{ $package->created_at->format('d M Y, h:i A') }}</td>
                            </tr>
                            <tr>
                                <th>Last Updated:</th>
                                <td>{{ $package->updated_at->format('d M Y, h:i A') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                @if($package->description)
                <hr>
                <h6>Description:</h6>
                <p class="text-muted">{{ $package->description }}</p>
                @endif

                @if($package->features && count($package->features) > 0)
                <hr>
                <h6>Features:</h6>
                <ul class="mb-0">
                    @foreach($package->features as $feature)
                        @if($feature)
                        <li><i class="fas fa-check text-success me-2"></i>{{ $feature }}</li>
                        @endif
                    @endforeach
                </ul>
                @endif
            </div>
        </div>

        <!-- Included Subjects -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-book me-2"></i> Included Subjects</h5>
            </div>
            <div class="card-body">
                @if($package->subjects->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Code</th>
                                    <th>Subject Name</th>
                                    <th>Sessions/Month</th>
                                    <th>Grade Levels</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($package->subjects as $subject)
                                    <tr>
                                        <td><span class="badge bg-secondary">{{ $subject->code }}</span></td>
                                        <td>{{ $subject->name }}</td>
                                        <td>
                                            <span class="badge bg-primary">{{ $subject->pivot->sessions_per_month }}</span> sessions
                                        </td>
                                        <td>
                                            @if($subject->grade_levels && count($subject->grade_levels) > 0)
                                                @foreach(array_slice($subject->grade_levels, 0, 2) as $grade)
                                                    <span class="badge bg-info">{{ $grade }}</span>
                                                @endforeach
                                                @if(count($subject->grade_levels) > 2)
                                                    <span class="badge bg-secondary">+{{ count($subject->grade_levels) - 2 }}</span>
                                                @endif
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.subjects.show', $subject) }}"
                                               class="btn btn-sm btn-outline-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-book-open fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No subjects assigned to this package.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Recent Enrollments -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-users me-2"></i> Recent Enrollments</h5>
            </div>
            <div class="card-body">
                @if($package->enrollments->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Student</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Monthly Fee</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($package->enrollments as $enrollment)
                                    <tr>
                                        <td>{{ $enrollment->student?->user?->name ?? 'N/A' }}</td>
                                        <td>{{ $enrollment->start_date?->format('d M Y') ?? '-' }}</td>
                                        <td>{{ $enrollment->end_date?->format('d M Y') ?? '-' }}</td>
                                        <td>RM {{ number_format($enrollment->monthly_fee, 2) }}</td>
                                        <td>
                                            @if($enrollment->status == 'active')
                                                <span class="badge bg-success">Active</span>
                                            @elseif($enrollment->status == 'expired')
                                                <span class="badge bg-secondary">Expired</span>
                                            @elseif($enrollment->status == 'cancelled')
                                                <span class="badge bg-danger">Cancelled</span>
                                            @else
                                                <span class="badge bg-warning">{{ ucfirst($enrollment->status) }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-user-graduate fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No enrollments for this package yet.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Pricing & Statistics -->
    <div class="col-md-4">
        <!-- Pricing Card -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-tag me-2"></i> Pricing</h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <h2 class="text-primary mb-0">RM {{ number_format($package->total_price, 2) }}</h2>
                    <small class="text-muted">per {{ $package->duration_months }} month(s)</small>
                </div>

                <hr>

                <table class="table table-borderless table-sm">
                    <tr>
                        <td>Base Price:</td>
                        <td class="text-end">RM {{ number_format($package->price, 2) }}</td>
                    </tr>
                    @if($package->online_fee)
                    <tr>
                        <td>Online Fee:</td>
                        <td class="text-end">RM {{ number_format($package->online_fee, 2) }}</td>
                    </tr>
                    @endif
                    <tr class="border-top">
                        <th>Total:</th>
                        <th class="text-end text-primary">RM {{ number_format($package->total_price, 2) }}</th>
                    </tr>
                </table>

                @if($package->type == 'online' || $package->type == 'hybrid')
                <div class="alert alert-info mb-0 small">
                    <i class="fas fa-info-circle me-1"></i>
                    Online fee of RM {{ number_format($package->online_fee ?? 130, 2) }} applies for online access.
                </div>
                @endif
            </div>
        </div>

        <!-- Statistics Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i> Enrollment Statistics</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6 mb-3">
                        <div class="border rounded p-3">
                            <h3 class="text-success mb-0">{{ $enrollmentStats['active'] }}</h3>
                            <small class="text-muted">Active</small>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="border rounded p-3">
                            <h3 class="text-secondary mb-0">{{ $enrollmentStats['expired'] }}</h3>
                            <small class="text-muted">Expired</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="border rounded p-3">
                            <h3 class="text-danger mb-0">{{ $enrollmentStats['cancelled'] }}</h3>
                            <small class="text-muted">Cancelled</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="border rounded p-3">
                            <h3 class="text-primary mb-0">{{ $enrollmentStats['total'] }}</h3>
                            <small class="text-muted">Total</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-bolt me-2"></i> Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    @can('edit-packages')
                    <a href="{{ route('admin.packages.edit', $package) }}" class="btn btn-primary">
                        <i class="fas fa-edit me-1"></i> Edit Package
                    </a>
                    <form action="{{ route('admin.packages.duplicate', $package) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success w-100">
                            <i class="fas fa-copy me-1"></i> Duplicate Package
                        </button>
                    </form>
                    @endcan
                    @can('delete-packages')
                    <form action="{{ route('admin.packages.destroy', $package) }}" method="POST"
                          onsubmit="return confirm('Are you sure you want to delete this package?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger w-100"
                                {{ $enrollmentStats['active'] > 0 ? 'disabled' : '' }}>
                            <i class="fas fa-trash me-1"></i> Delete Package
                        </button>
                    </form>
                    @if($enrollmentStats['active'] > 0)
                        <small class="text-danger text-center">Cannot delete: has active enrollments</small>
                    @endif
                    @endcan
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
