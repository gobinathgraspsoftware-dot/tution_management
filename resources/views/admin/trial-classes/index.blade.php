@extends('layouts.app')

@section('title', 'Trial Class Management')
@section('page-title', 'Trial Class Management')

@section('content')
<div class="page-header">
    <div>
        <h1><i class="fas fa-chalkboard me-2"></i> Trial Class Management</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Trial Classes</li>
            </ol>
        </nav>
    </div>
    <div>
        <a href="{{ route('admin.trial-classes.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Schedule Trial
        </a>
        <a href="{{ route('admin.trial-classes.export') }}" class="btn btn-success">
            <i class="fas fa-download me-1"></i> Export
        </a>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-2">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #e3f2fd; color: #2196f3;">
                <i class="fas fa-list"></i>
            </div>
            <div class="stat-details">
                <h3 class="mb-0">{{ $stats['total'] }}</h3>
                <p class="text-muted mb-0">Total Trials</p>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #fff3e0; color: #ff9800;">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-details">
                <h3 class="mb-0">{{ $stats['pending'] }}</h3>
                <p class="text-muted mb-0">Pending</p>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #e8f5e9; color: #4caf50;">
                <i class="fas fa-check"></i>
            </div>
            <div class="stat-details">
                <h3 class="mb-0">{{ $stats['attended'] }}</h3>
                <p class="text-muted mb-0">Attended</p>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #f3e5f5; color: #9c27b0;">
                <i class="fas fa-user-plus"></i>
            </div>
            <div class="stat-details">
                <h3 class="mb-0">{{ $stats['converted'] }}</h3>
                <p class="text-muted mb-0">Converted</p>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #ffebee; color: #f44336;">
                <i class="fas fa-user-times"></i>
            </div>
            <div class="stat-details">
                <h3 class="mb-0">{{ $stats['no_show'] }}</h3>
                <p class="text-muted mb-0">No Show</p>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #e8f5e9; color: #4caf50;">
                <i class="fas fa-percentage"></i>
            </div>
            <div class="stat-details">
                <h3 class="mb-0">{{ $stats['conversion_rate'] }}%</h3>
                <p class="text-muted mb-0">Conversion Rate</p>
            </div>
        </div>
    </div>
</div>

<!-- Quick Stats -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="alert alert-info mb-0">
            <i class="fas fa-calendar-day me-2"></i>
            <strong>Today:</strong> {{ $stats['today'] }} trial class(es) scheduled
        </div>
    </div>
    <div class="col-md-6">
        <div class="alert alert-warning mb-0">
            <i class="fas fa-calendar-week me-2"></i>
            <strong>Upcoming:</strong> {{ $stats['upcoming'] }} trial class(es) in queue
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('admin.trial-classes.index') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" placeholder="Search student, parent, phone..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="attended" {{ request('status') === 'attended' ? 'selected' : '' }}>Attended</option>
                    <option value="no_show" {{ request('status') === 'no_show' ? 'selected' : '' }}>No Show</option>
                    <option value="converted" {{ request('status') === 'converted' ? 'selected' : '' }}>Converted</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="conversion_status" class="form-select">
                    <option value="">All Conversion</option>
                    <option value="pending" {{ request('conversion_status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="converted" {{ request('conversion_status') === 'converted' ? 'selected' : '' }}>Converted</option>
                    <option value="declined" {{ request('conversion_status') === 'declined' ? 'selected' : '' }}>Declined</option>
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary"><i class="fas fa-search me-1"></i> Filter</button>
                <a href="{{ route('admin.trial-classes.index') }}" class="btn btn-secondary"><i class="fas fa-redo me-1"></i> Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Trial Classes Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Student</th>
                        <th>Parent</th>
                        <th>Class</th>
                        <th>Scheduled</th>
                        <th>Status</th>
                        <th>Conversion</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($trialClasses as $trial)
                    <tr>
                        <td>{{ $trial->id }}</td>
                        <td>
                            @if($trial->student)
                                <a href="{{ route('admin.students.profile', $trial->student) }}">
                                    {{ $trial->student->user->name }}
                                </a>
                            @else
                                {{ $trial->student_name ?? 'N/A' }}
                            @endif
                        </td>
                        <td>
                            {{ $trial->parent_name ?? 'N/A' }}
                            @if($trial->parent_phone)
                                <br><small class="text-muted">{{ $trial->parent_phone }}</small>
                            @endif
                        </td>
                        <td>
                            {{ $trial->class->name ?? 'N/A' }}
                            <br><small class="text-muted">{{ $trial->class->subject->name ?? '' }}</small>
                        </td>
                        <td>
                            {{ $trial->scheduled_date->format('d M Y') }}
                            <br><small class="text-muted">{{ $trial->scheduled_time ? $trial->scheduled_time->format('h:i A') : 'TBD' }}</small>
                        </td>
                        <td>
                            @if($trial->status === 'pending')
                                <span class="badge bg-warning">Pending</span>
                            @elseif($trial->status === 'approved')
                                <span class="badge bg-info">Approved</span>
                            @elseif($trial->status === 'attended')
                                <span class="badge bg-success">Attended</span>
                            @elseif($trial->status === 'no_show')
                                <span class="badge bg-danger">No Show</span>
                            @elseif($trial->status === 'converted')
                                <span class="badge bg-primary">Converted</span>
                            @elseif($trial->status === 'cancelled')
                                <span class="badge bg-secondary">Cancelled</span>
                            @endif
                        </td>
                        <td>
                            @if($trial->conversion_status === 'converted')
                                <span class="badge bg-success">Converted</span>
                            @elseif($trial->conversion_status === 'declined')
                                <span class="badge bg-danger">Declined</span>
                            @else
                                <span class="badge bg-secondary">Pending</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group">
                                <a href="{{ route('admin.trial-classes.show', $trial) }}" class="btn btn-sm btn-info" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if(in_array($trial->status, ['pending', 'approved']))
                                    <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#markAttendanceModal{{ $trial->id }}" title="Mark Attendance">
                                        <i class="fas fa-check"></i>
                                    </button>
                                @endif
                                @if($trial->status === 'attended' && $trial->conversion_status === 'pending')
                                    <form action="{{ route('admin.trial-classes.convert', $trial) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-primary" title="Convert to Enrollment" onclick="return confirm('Convert this trial to full enrollment?')">
                                            <i class="fas fa-user-plus"></i>
                                        </button>
                                    </form>
                                @endif
                                <form action="{{ route('admin.trial-classes.destroy', $trial) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this trial class?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>

                    <!-- Mark Attendance Modal -->
                    <div class="modal fade" id="markAttendanceModal{{ $trial->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form action="{{ route('admin.trial-classes.mark-attendance', $trial) }}" method="POST">
                                    @csrf
                                    <div class="modal-header">
                                        <h5 class="modal-title">Mark Attendance</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p><strong>Student:</strong> {{ $trial->student_name ?? $trial->student->user->name ?? 'N/A' }}</p>
                                        <p><strong>Class:</strong> {{ $trial->class->name ?? 'N/A' }}</p>
                                        <p><strong>Scheduled:</strong> {{ $trial->scheduled_date->format('d M Y') }} {{ $trial->scheduled_time ? $trial->scheduled_time->format('h:i A') : '' }}</p>
                                        <hr>
                                        <div class="mb-3">
                                            <label class="form-label">Attendance Status <span class="text-danger">*</span></label>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="attended" id="attended{{ $trial->id }}" value="1" checked>
                                                <label class="form-check-label" for="attended{{ $trial->id }}">Attended</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="attended" id="noshow{{ $trial->id }}" value="0">
                                                <label class="form-check-label" for="noshow{{ $trial->id }}">No Show</label>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Feedback</label>
                                            <textarea name="feedback" class="form-control" rows="3" placeholder="Enter feedback about the trial class..."></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary">Save</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">No trial classes found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $trialClasses->links() }}
    </div>
</div>
@endsection
