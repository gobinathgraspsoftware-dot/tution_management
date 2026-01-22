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
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total'] ?? 0 }}</div>
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
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['active'] ?? 0 }}</div>
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
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['expiring_soon'] ?? 0 }}</div>
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
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['expired'] ?? 0 }}</div>
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
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Filters</h6>
            <a href="{{ route('staff.enrollments.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-sync"></i> Reset
            </a>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('staff.enrollments.index') }}">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="search">Search Student</label>
                        <input type="text" class="form-control" id="search" name="search"
                               value="{{ request('search') }}" placeholder="Name, ID, or email...">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="status">Status</label>
                        <select class="form-control" id="status" name="status">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="trial" {{ request('status') == 'trial' ? 'selected' : '' }}>Trial</option>
                            <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                            <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="package_id">Package</label>
                        <select class="form-control" id="package_id" name="package_id">
                            <option value="">All Packages</option>
                            @foreach($packages as $package)
                                <option value="{{ $package->id }}" {{ request('package_id') == $package->id ? 'selected' : '' }}>
                                    {{ $package->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
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
                    <div class="col-md-3 mb-3">
                        <label>&nbsp;</label>
                        <div>
                            <button type="submit" class="btn btn-primary">
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
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">All Enrollments</h6>
            <span class="badge badge-primary">{{ $enrollments->total() }} records</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th>Student</th>
                            <th>Package/Class</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Monthly Fee</th>
                            <th>Status</th>
                            <th width="150">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($enrollments as $enrollment)
                        <tr>
                            <td>
                                <strong>{{ $enrollment->student->user->name ?? 'N/A' }}</strong><br>
                                <small class="text-muted">{{ $enrollment->student->student_id ?? '-' }}</small>
                            </td>
                            <td>
                                @if($enrollment->package)
                                    <span class="badge badge-info">Package</span>
                                    {{ $enrollment->package->name }}
                                @elseif($enrollment->class)
                                    <span class="badge badge-secondary">Class</span>
                                    {{ $enrollment->class->name }}
                                    <br><small class="text-muted">{{ $enrollment->class->subject->name ?? '' }}</small>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>{{ $enrollment->start_date ? $enrollment->start_date->format('d M Y') : '-' }}</td>
                            <td>
                                @if($enrollment->end_date)
                                    {{ $enrollment->end_date->format('d M Y') }}
                                    @if($enrollment->end_date->isPast() && $enrollment->status == 'active')
                                        <span class="badge badge-danger">Expired</span>
                                    @elseif($enrollment->days_remaining !== null && $enrollment->days_remaining <= 30 && $enrollment->status == 'active')
                                        <span class="badge badge-warning">{{ $enrollment->days_remaining }} days left</span>
                                    @endif
                                @else
                                    <span class="text-muted">Ongoing</span>
                                @endif
                            </td>
                            <td>RM {{ number_format($enrollment->monthly_fee, 2) }}</td>
                            <td>
                                @switch($enrollment->status)
                                    @case('active')
                                        <span class="badge badge-success">Active</span>
                                        @break
                                    @case('suspended')
                                        <span class="badge badge-warning">Suspended</span>
                                        @break
                                    @case('expired')
                                        <span class="badge badge-danger">Expired</span>
                                        @break
                                    @case('cancelled')
                                        <span class="badge badge-dark">Cancelled</span>
                                        @break
                                    @case('trial')
                                        <span class="badge badge-info">Trial</span>
                                        @break
                                    @default
                                        <span class="badge badge-secondary">{{ ucfirst($enrollment->status) }}</span>
                                @endswitch
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('staff.enrollments.show', $enrollment) }}"
                                       class="btn btn-sm btn-info" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @can('edit-enrollments')
                                    <a href="{{ route('staff.enrollments.edit', $enrollment) }}"
                                       class="btn btn-sm btn-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endcan
                                    @can('delete-enrollments')
                                    @if(!$enrollment->invoices()->where('paid_amount', '>', 0)->exists())
                                    <button type="button" class="btn btn-sm btn-danger" title="Delete"
                                            onclick="confirmDelete({{ $enrollment->id }})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No enrollments found</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-3 d-flex justify-content-between align-items-center">
                <div class="text-muted small">
                    Showing {{ $enrollments->firstItem() ?? 0 }} to {{ $enrollments->lastItem() ?? 0 }} of {{ $enrollments->total() }} entries
                </div>
                {{ $enrollments->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Delete Enrollment</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this enrollment? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function confirmDelete(enrollmentId) {
    $('#deleteForm').attr('action', '/staff/enrollments/' + enrollmentId);
    $('#deleteModal').modal('show');
}
</script>
@endpush
