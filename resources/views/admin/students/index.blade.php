@extends('layouts.app')

@section('title', 'Student Management')
@section('page-title', 'Student Management')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="fas fa-users me-2"></i> Student Management</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Students</li>
            </ol>
        </nav>
    </div>
    <div>
        @can('export-students')
        <a href="{{ route('admin.students.export') }}" class="btn btn-outline-success me-2">
            <i class="fas fa-file-export me-1"></i> Export
        </a>
        @endcan
        @can('create-students')
        <a href="{{ route('admin.students.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Add Student
        </a>
        @endcan
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('admin.students.index') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Name, email, ID, IC, school..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Approval</label>
                <select name="approval_status" class="form-select">
                    <option value="">All Approval</option>
                    <option value="pending" {{ request('approval_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('approval_status') == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('approval_status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Grade Level</label>
                <select name="grade_level" class="form-select">
                    <option value="">All Grades</option>
                    @foreach($gradeLevels as $grade)
                        <option value="{{ $grade }}" {{ request('grade_level') == $grade ? 'selected' : '' }}>{{ $grade }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search me-1"></i> Filter
                </button>
                <a href="{{ route('admin.students.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-1"></i> Clear
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Students List -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Student ID</th>
                        <th>Name</th>
                        <th>IC Number</th>
                        <th>Parent</th>
                        <th>School</th>
                        <th>Grade</th>
                        <th>Approval</th>
                        <th>Status</th>
                        <th width="150">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $student)
                        <tr>
                            <td><span class="badge bg-primary">{{ $student->student_id }}</span></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="user-avatar me-2" style="width:35px;height:35px;font-size:0.9rem;background:linear-gradient(135deg, #2196f3 0%, #1976d2 100%);">
                                        {{ substr($student->user->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <strong>{{ $student->user->name }}</strong>
                                        <br><small class="text-muted">{{ $student->user->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <code class="text-dark">{{ $student->formatted_ic_number ?? '-' }}</code>
                            </td>
                            <td>
                                @if($student->parent)
                                    {{ $student->parent->user->name }}
                                    <br><small class="text-muted">{{ $student->parent->user->phone }}</small>
                                @else
                                    <span class="text-muted">Not linked</span>
                                @endif
                            </td>
                            <td>{{ Str::limit($student->school_name, 20) ?? '-' }}</td>
                            <td>{{ $student->grade_level ?? '-' }}</td>
                            <td>
                                @if($student->approval_status == 'approved')
                                    <span class="badge bg-success">Approved</span>
                                @elseif($student->approval_status == 'pending')
                                    <span class="badge bg-warning text-dark">Pending</span>
                                @else
                                    <span class="badge bg-danger">Rejected</span>
                                @endif
                            </td>
                            <td>
                                @if($student->user->status == 'active')
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    @can('view-students')
                                    <a href="{{ route('admin.students.show', $student) }}" class="btn btn-outline-info" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @endcan
                                    @can('edit-students')
                                    <a href="{{ route('admin.students.edit', $student) }}" class="btn btn-outline-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endcan
                                    @can('delete-students')
                                    <button type="button" class="btn btn-outline-danger" title="Delete"
                                            onclick="confirmDelete({{ $student->id }}, '{{ $student->user->name }}')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endcan
                                </div>

                                @can('delete-students')
                                <form id="delete-form-{{ $student->id }}" action="{{ route('admin.students.destroy', $student) }}" method="POST" class="d-none">
                                    @csrf
                                    @method('DELETE')
                                </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-users fa-3x mb-3"></i>
                                    <p>No students found.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center mt-4">
            <div class="text-muted">
                Showing {{ $students->firstItem() ?? 0 }} to {{ $students->lastItem() ?? 0 }} of {{ $students->total() }} entries
            </div>
            {{ $students->links() }}
        </div>
    </div>
</div>

<!-- Delete Modal -->
@include('components.delete-modal', [
    'id' => 'deleteModal',
    'title' => 'Delete Student',
    'message' => 'Are you sure you want to delete this student?',
    'route' => 'admin.students.destroy'
])
@endsection
