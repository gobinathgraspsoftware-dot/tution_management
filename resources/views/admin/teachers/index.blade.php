@extends('layouts.app')

@section('title', 'Teacher Management')
@section('page-title', 'Teacher Management')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="fas fa-chalkboard-teacher me-2"></i> Teacher Management</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Teachers</li>
            </ol>
        </nav>
    </div>
    <div>
        @can('export-teachers')
        <a href="{{ route('admin.teachers.export') }}" class="btn btn-outline-success me-2">
            <i class="fas fa-file-export me-1"></i> Export
        </a>
        @endcan
        @can('create-teachers')
        <a href="{{ route('admin.teachers.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Add Teacher
        </a>
        @endcan
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('admin.teachers.index') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Name, email, IC..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="on_leave" {{ request('status') == 'on_leave' ? 'selected' : '' }}>On Leave</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Employment</label>
                <select name="employment_type" class="form-select">
                    <option value="">All Types</option>
                    <option value="full_time" {{ request('employment_type') == 'full_time' ? 'selected' : '' }}>Full Time</option>
                    <option value="part_time" {{ request('employment_type') == 'part_time' ? 'selected' : '' }}>Part Time</option>
                    <option value="contract" {{ request('employment_type') == 'contract' ? 'selected' : '' }}>Contract</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Pay Type</label>
                <select name="pay_type" class="form-select">
                    <option value="">All Pay Types</option>
                    <option value="hourly" {{ request('pay_type') == 'hourly' ? 'selected' : '' }}>Hourly</option>
                    <option value="monthly" {{ request('pay_type') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                    <option value="per_class" {{ request('pay_type') == 'per_class' ? 'selected' : '' }}>Per Class</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search me-1"></i> Filter
                </button>
                <a href="{{ route('admin.teachers.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-1"></i> Clear
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Teachers List -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Teacher ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>IC Number</th>
                        <th>Specialization</th>
                        <th>Employment</th>
                        <th>Pay Rate</th>
                        <th>Status</th>
                        <th width="150">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($teachers as $teacher)
                        <tr>
                            <td><span class="badge bg-info">{{ $teacher->teacher_id }}</span></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="user-avatar me-2" style="width:35px;height:35px;font-size:0.9rem;background:linear-gradient(135deg, #4caf50 0%, #2e7d32 100%);">
                                        {{ substr($teacher->user->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <strong>{{ $teacher->user->name }}</strong>
                                        <br><small class="text-muted">{{ $teacher->experience_years }} years exp.</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $teacher->user->email }}</td>
                            <td><span class="badge bg-light text-dark">{{ $teacher->formatted_ic_number ?? 'N/A' }}</span></td>
                            <td>
                                @if(!empty($teacher->specialization_names))
                                    @foreach(array_slice($teacher->specialization_names, 0, 2) as $subject)
                                        <span class="badge bg-primary me-1 mb-1">{{ $subject }}</span>
                                    @endforeach
                                    @if(count($teacher->specialization_names) > 2)
                                        <span class="badge bg-secondary mb-1">+{{ count($teacher->specialization_names) - 2 }}</span>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $empBadgeClass = [
                                        'full_time' => 'bg-primary',
                                        'part_time' => 'bg-warning text-dark',
                                        'contract' => 'bg-secondary'
                                    ];
                                @endphp
                                <span class="badge {{ $empBadgeClass[$teacher->employment_type] ?? 'bg-secondary' }}">
                                    {{ str_replace('_', ' ', ucfirst($teacher->employment_type)) }}
                                </span>
                            </td>
                            <td>
                                @if($teacher->pay_type == 'hourly')
                                    RM {{ number_format($teacher->hourly_rate, 2) }}/hr
                                @elseif($teacher->pay_type == 'monthly')
                                    RM {{ number_format($teacher->monthly_salary, 2) }}/mo
                                @else
                                    RM {{ number_format($teacher->per_class_rate, 2) }}/class
                                @endif
                            </td>
                            <td>
                                @if($teacher->status == 'active')
                                    <span class="badge bg-success">Active</span>
                                @elseif($teacher->status == 'on_leave')
                                    <span class="badge bg-warning text-dark">On Leave</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    @can('view-teachers')
                                    <a href="{{ route('admin.teachers.show', $teacher) }}" class="btn btn-outline-info" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @endcan
                                    @can('edit-teachers')
                                    <a href="{{ route('admin.teachers.edit', $teacher) }}" class="btn btn-outline-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endcan
                                    @can('delete-teachers')
                                    <button type="button" class="btn btn-outline-danger" title="Delete"
                                            onclick="confirmDelete('{{ $teacher->id }}', '{{ $teacher->user->name }}')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No teachers found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center mt-4">
            <div class="text-muted">
                Showing {{ $teachers->firstItem() ?? 0 }} to {{ $teachers->lastItem() ?? 0 }} of {{ $teachers->total() }} entries
            </div>
            {{ $teachers->links() }}
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete <strong id="deleteName"></strong>?</p>
                <p class="text-danger"><small>This action cannot be undone. Teachers with active classes cannot be deleted.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function confirmDelete(id, name) {
    document.getElementById('deleteName').textContent = name;
    document.getElementById('deleteForm').action = '{{ route("admin.teachers.index") }}/' + id;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
@endpush
