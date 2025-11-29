@extends('layouts.app')

@section('title', 'Subject Management')
@section('page-title', 'Subject Management')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="fas fa-book me-2"></i> Subject Management</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Subjects</li>
            </ol>
        </nav>
    </div>
    <div>
        @can('create-subjects')
        <a href="{{ route('admin.subjects.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Add Subject
        </a>
        @endcan
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="mb-0">{{ \App\Models\Subject::count() }}</h4>
                        <small>Total Subjects</small>
                    </div>
                    <i class="fas fa-book fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="mb-0">{{ \App\Models\Subject::active()->count() }}</h4>
                        <small>Active Subjects</small>
                    </div>
                    <i class="fas fa-check-circle fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="mb-0">{{ \App\Models\Subject::withCount('packages')->get()->sum('packages_count') }}</h4>
                        <small>Package Links</small>
                    </div>
                    <i class="fas fa-link fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="mb-0">{{ \App\Models\Subject::where('status', 'inactive')->count() }}</h4>
                        <small>Inactive Subjects</small>
                    </div>
                    <i class="fas fa-pause-circle fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('admin.subjects.index') }}" method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control"
                       placeholder="Name, code, description..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Grade Level</label>
                <select name="grade_level" class="form-select">
                    <option value="">All Grades</option>
                    @foreach($allGradeLevels as $grade)
                        <option value="{{ $grade }}" {{ request('grade_level') == $grade ? 'selected' : '' }}>{{ $grade }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search me-1"></i> Filter
                </button>
                <a href="{{ route('admin.subjects.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-1"></i> Clear
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Subjects List -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Code</th>
                        <th>Subject Name</th>
                        <th>Grade Levels</th>
                        <th>Packages</th>
                        <th>Classes</th>
                        <th>Status</th>
                        <th width="150">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subjects as $subject)
                        <tr>
                            <td><span class="badge bg-secondary">{{ $subject->code }}</span></td>
                            <td>
                                <strong>{{ $subject->name }}</strong>
                                @if($subject->description)
                                    <br><small class="text-muted">{{ Str::limit($subject->description, 50) }}</small>
                                @endif
                            </td>
                            <td>
                                @if($subject->grade_levels && count($subject->grade_levels) > 0)
                                    @foreach(array_slice($subject->grade_levels, 0, 3) as $grade)
                                        <span class="badge bg-info">{{ $grade }}</span>
                                    @endforeach
                                    @if(count($subject->grade_levels) > 3)
                                        <span class="badge bg-secondary">+{{ count($subject->grade_levels) - 3 }}</span>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-primary">{{ $subject->packages_count }}</span>
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $subject->classes_count }}</span>
                            </td>
                            <td>
                                @if($subject->status == 'active')
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    @can('view-subjects')
                                    <a href="{{ route('admin.subjects.show', $subject) }}"
                                       class="btn btn-outline-info" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @endcan
                                    @can('edit-subjects')
                                    <a href="{{ route('admin.subjects.edit', $subject) }}"
                                       class="btn btn-outline-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-warning" title="Toggle Status"
                                            onclick="confirmToggle('{{ $subject->id }}', '{{ $subject->name }}', '{{ $subject->status }}')">
                                        <i class="fas fa-power-off"></i>
                                    </button>
                                    @endcan
                                    @can('delete-subjects')
                                    <button type="button" class="btn btn-outline-danger" title="Delete"
                                            onclick="confirmDelete('{{ $subject->id }}', '{{ $subject->name }}')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <i class="fas fa-book fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No subjects found.</p>
                                @can('create-subjects')
                                <a href="{{ route('admin.subjects.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-1"></i> Add First Subject
                                </a>
                                @endcan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($subjects->hasPages())
        <div class="d-flex justify-content-between align-items-center mt-4">
            <div class="text-muted">
                Showing {{ $subjects->firstItem() ?? 0 }} to {{ $subjects->lastItem() ?? 0 }}
                of {{ $subjects->total() }} subjects
            </div>
            {{ $subjects->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete subject <strong id="deleteSubjectName"></strong>?</p>
                <p class="text-danger mb-0">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    This action cannot be undone.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i> Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Toggle Status Modal -->
<div class="modal fade" id="toggleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Status Change</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to <span id="toggleAction"></span> subject <strong id="toggleSubjectName"></strong>?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="toggleForm" method="POST" class="d-inline">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-power-off me-1"></i> Confirm
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function confirmDelete(id, name) {
    document.getElementById('deleteSubjectName').textContent = name;
    document.getElementById('deleteForm').action = `/admin/subjects/${id}`;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

function confirmToggle(id, name, currentStatus) {
    const action = currentStatus === 'active' ? 'deactivate' : 'activate';
    document.getElementById('toggleSubjectName').textContent = name;
    document.getElementById('toggleAction').textContent = action;
    document.getElementById('toggleForm').action = `/admin/subjects/${id}/toggle-status`;
    new bootstrap.Modal(document.getElementById('toggleModal')).show();
}
</script>
@endsection
