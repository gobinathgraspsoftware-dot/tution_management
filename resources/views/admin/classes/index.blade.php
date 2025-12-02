@extends('layouts.app')

@section('title', 'Classes Management')
@section('page-title', 'Classes Management')

@section('content')
<div class="container-fluid">
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="mb-0">{{ $stats['total'] }}</h3>
                    <small class="text-muted">Total Classes</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center border-success">
                <div class="card-body">
                    <h3 class="mb-0 text-success">{{ $stats['active'] }}</h3>
                    <small class="text-muted">Active</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center border-info">
                <div class="card-body">
                    <h3 class="mb-0 text-info">{{ $stats['online'] }}</h3>
                    <small class="text-muted">Online</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center border-primary">
                <div class="card-body">
                    <h3 class="mb-0 text-primary">{{ $stats['offline'] }}</h3>
                    <small class="text-muted">Offline</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center border-danger">
                <div class="card-body">
                    <h3 class="mb-0 text-danger">{{ $stats['full'] }}</h3>
                    <small class="text-muted">Full</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center border-warning">
                <div class="card-body">
                    <h3 class="mb-0 text-warning">{{ $stats['available_seats'] }}</h3>
                    <small class="text-muted">Available Seats</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters & Actions -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.classes.index') }}" id="filterForm">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" class="form-control" placeholder="Search classes..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-select">
                            <option value="">All Types</option>
                            <option value="online" {{ request('type') == 'online' ? 'selected' : '' }}>Online</option>
                            <option value="offline" {{ request('type') == 'offline' ? 'selected' : '' }}>Offline</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="full" {{ request('status') == 'full' ? 'selected' : '' }}>Full</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Subject</label>
                        <select name="subject_id" class="form-select">
                            <option value="">All Subjects</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                                    {{ $subject->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Teacher</label>
                        <select name="teacher_id" class="form-select">
                            <option value="">All Teachers</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}" {{ request('teacher_id') == $teacher->id ? 'selected' : '' }}>
                                    {{ $teacher->user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter me-2"></i>Filter
                        </button>
                        <a href="{{ route('admin.classes.index') }}" class="btn btn-secondary">
                            <i class="fas fa-redo me-2"></i>Reset
                        </a>
                        @can('create-classes')
                            <a href="{{ route('admin.classes.create') }}" class="btn btn-success">
                                <i class="fas fa-plus me-2"></i>Add New Class
                            </a>
                        @endcan
                        <a href="{{ route('admin.classes.timetable') }}" class="btn btn-info">
                            <i class="fas fa-calendar-alt me-2"></i>View Timetable
                        </a>
                        <a href="{{ route('admin.classes.export', request()->query()) }}" class="btn btn-outline-primary">
                            <i class="fas fa-download me-2"></i>Export CSV
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Classes Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Class Name</th>
                            <th>Subject</th>
                            <th>Teacher</th>
                            <th>Type</th>
                            <th>Grade</th>
                            <th>Capacity</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($classes as $class)
                            <tr>
                                <td><strong>{{ $class->code }}</strong></td>
                                <td>{{ $class->name }}</td>
                                <td>
                                    <span class="badge bg-info">{{ $class->subject->name ?? 'N/A' }}</span>
                                </td>
                                <td>{{ $class->teacher->user->name ?? 'Not Assigned' }}</td>
                                <td>
                                    @if($class->type === 'online')
                                        <span class="badge bg-primary">
                                            <i class="fas fa-video me-1"></i>Online
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">
                                            <i class="fas fa-chalkboard-teacher me-1"></i>Offline
                                        </span>
                                    @endif
                                </td>
                                <td>{{ $class->grade_level ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge {{ $class->isFull() ? 'bg-danger' : 'bg-success' }}">
                                        {{ $class->current_enrollment }}/{{ $class->capacity }}
                                    </span>
                                </td>
                                <td>
                                    @if($class->status === 'active')
                                        <span class="badge bg-success">Active</span>
                                    @elseif($class->status === 'full')
                                        <span class="badge bg-danger">Full</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        @can('view-class-details')
                                            <a href="{{ route('admin.classes.show', $class) }}" class="btn btn-info" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        @endcan
                                        @can('edit-classes')
                                            <a href="{{ route('admin.classes.edit', $class) }}" class="btn btn-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endcan
                                        @can('manage-class-schedule')
                                            <a href="{{ route('admin.classes.schedule.index', $class) }}" class="btn btn-primary" title="Manage Schedule">
                                                <i class="fas fa-calendar"></i>
                                            </a>
                                        @endcan
                                        @can('edit-classes')
                                            <form action="{{ route('admin.classes.toggle-status', $class) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-secondary" title="Toggle Status">
                                                    <i class="fas fa-power-off"></i>
                                                </button>
                                            </form>
                                        @endcan
                                        @can('delete-classes')
                                            <button type="button" class="btn btn-danger" onclick="confirmDelete({{ $class->id }})" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <form id="delete-form-{{ $class->id }}" action="{{ route('admin.classes.destroy', $class) }}" method="POST" style="display: none;">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                    No classes found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    Showing {{ $classes->firstItem() ?? 0 }} to {{ $classes->lastItem() ?? 0 }} of {{ $classes->total() }} classes
                </div>
                <div>
                    {{ $classes->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function confirmDelete(id) {
    if (confirm('Are you sure you want to delete this class? This action cannot be undone.')) {
        document.getElementById('delete-form-' + id).submit();
    }
}
</script>
@endpush
