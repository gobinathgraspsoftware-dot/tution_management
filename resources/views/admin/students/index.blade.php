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
                <label class="form-label">Registration Type</label>
                <select name="registration_type" class="form-select">
                    <option value="">All Types</option>
                    <option value="online" {{ request('registration_type') == 'online' ? 'selected' : '' }}>Online</option>
                    <option value="offline" {{ request('registration_type') == 'offline' ? 'selected' : '' }}>Offline</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">&nbsp;</label>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-1"></i> Filter
                    </button>
                    <a href="{{ route('admin.students.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i> Clear
                    </a>
                </div>
            </div>

            <!-- Second Row Filters -->
            <div class="col-md-2">
                {{-- ═══ GRADE LEVEL filter — changed from distinct varchar to DB objects with id ═══ --}}
                <label class="form-label">Grade Level</label>
                <select name="grade_level" class="form-select">
                    <option value="">All Grades</option>
                    @foreach($gradeLevels as $grade)
                        <option value="{{ $grade->id }}"
                            {{ request('grade_level') == $grade->id ? 'selected' : '' }}>
                            {{ $grade->name }}
                        </option>
                    @endforeach
                </select>
                {{-- ══════════════════════════════════════════════════════════════════════════════ --}}
            </div>
            <div class="col-md-2">
                <label class="form-label">Gender</label>
                <select name="gender" class="form-select">
                    <option value="">All Gender</option>
                    <option value="male" {{ request('gender') == 'male' ? 'selected' : '' }}>Male</option>
                    <option value="female" {{ request('gender') == 'female' ? 'selected' : '' }}>Female</option>
                </select>
            </div>
        </form>
    </div>
</div>

<!-- Active Filters Summary -->
@if(request()->hasAny(['search', 'status', 'approval_status', 'registration_type', 'grade_level', 'gender']))
<div class="mb-3">
    <div class="d-flex flex-wrap align-items-center gap-2">
        <span class="text-muted"><i class="fas fa-filter me-1"></i> Active Filters:</span>
        @if(request('search'))
            <span class="badge bg-primary">Search: {{ request('search') }}
                <a href="{{ request()->fullUrlWithQuery(['search' => null]) }}" class="text-white ms-1"><i class="fas fa-times"></i></a>
            </span>
        @endif
        @if(request('status'))
            <span class="badge bg-info">Status: {{ ucfirst(request('status')) }}
                <a href="{{ request()->fullUrlWithQuery(['status' => null]) }}" class="text-white ms-1"><i class="fas fa-times"></i></a>
            </span>
        @endif
        @if(request('approval_status'))
            <span class="badge bg-warning text-dark">Approval: {{ ucfirst(request('approval_status')) }}
                <a href="{{ request()->fullUrlWithQuery(['approval_status' => null]) }}" class="text-dark ms-1"><i class="fas fa-times"></i></a>
            </span>
        @endif
        @if(request('registration_type'))
            <span class="badge bg-{{ request('registration_type') == 'online' ? 'info' : 'secondary' }}">
                Type: {{ ucfirst(request('registration_type')) }}
                <a href="{{ request()->fullUrlWithQuery(['registration_type' => null]) }}" class="text-white ms-1"><i class="fas fa-times"></i></a>
            </span>
        @endif
        @if(request('grade_level'))
            {{-- ← CHANGED: look up name from collection by id --}}
            @php $selectedGrade = $gradeLevels->firstWhere('id', request('grade_level')); @endphp
            <span class="badge bg-dark">Grade: {{ $selectedGrade?->name ?? request('grade_level') }}
                <a href="{{ request()->fullUrlWithQuery(['grade_level' => null]) }}" class="text-white ms-1"><i class="fas fa-times"></i></a>
            </span>
        @endif
        @if(request('gender'))
            <span class="badge bg-secondary">Gender: {{ ucfirst(request('gender')) }}
                <a href="{{ request()->fullUrlWithQuery(['gender' => null]) }}" class="text-white ms-1"><i class="fas fa-times"></i></a>
            </span>
        @endif
        <a href="{{ route('admin.students.index') }}" class="btn btn-sm btn-outline-danger">
            <i class="fas fa-times me-1"></i> Clear All
        </a>
    </div>
</div>
@endif

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
                        <th>Type</th>
                        <th>Approval</th>
                        <th>Status</th>
                        <th width="180">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $student)
                        <tr>
                            <td><span class="badge bg-primary">{{ $student->student_id }}</span></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="user-avatar me-2" style="width:35px;height:35px;font-size:0.9rem;background:linear-gradient(135deg, #2196f3 0%, #1976d2 100%);">
                                        {{ substr($student->user->name ?? 'N', 0, 1) }}
                                    </div>
                                    <div>
                                        <strong>{{ $student->user->name ?? 'Unknown' }}</strong>
                                        <br><small class="text-muted">{{ $student->user->email ?? '' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($student->ic_number)
                                    @php
                                        $ic = $student->ic_number;
                                        $formattedIc = (strlen($ic) === 12)
                                            ? substr($ic, 0, 6) . '-' . substr($ic, 6, 2) . '-' . substr($ic, 8, 4)
                                            : $ic;
                                    @endphp
                                    <code class="text-dark">{{ $formattedIc }}</code>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($student->parent && $student->parent->user)
                                    {{ $student->parent->user->name }}
                                    <br>
                                    <small class="text-muted">{{ $student->parent->user->phone }}</small>
                                @else
                                    <span class="text-muted">Not linked</span>
                                @endif
                            </td>
                            <td>{{ $student->school_name ? Str::limit($student->school_name, 20) : '-' }}</td>

                            {{-- ═══ GRADE LEVEL display — changed from $student->grade_level to relationship ═══ --}}
                            <td>{{ $student->gradeLevel->name ?? '-' }}</td>
                            {{-- ════════════════════════════════════════════════════════════════════════════════ --}}

                            <td>
                                @if($student->registration_type == 'online')
                                    <span class="badge bg-info"><i class="fas fa-globe me-1"></i>Online</span>
                                @else
                                    <span class="badge bg-secondary"><i class="fas fa-building me-1"></i>Offline</span>
                                @endif
                            </td>
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
                                @if($student->user && $student->user->status == 'active')
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
                                    @if(config('notification.whatsapp.enabled', false) && $student->user && $student->user->phone)
                                    <a href="{{ route('admin.students.resend-whatsapp', $student) }}"
                                       class="btn btn-outline-success"
                                       title="Send WhatsApp to Student"
                                       onclick="return confirm('Send registration details via WhatsApp to student?');">
                                        <i class="fab fa-whatsapp"></i>
                                    </a>
                                    @endif
                                    @can('delete-students')
                                    <button type="button" class="btn btn-outline-danger" title="Delete"
                                            onclick="confirmDelete({{ $student->id }}, '{{ addslashes($student->user->name ?? 'Unknown') }}')">
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
                            <td colspan="10" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-users fa-3x mb-3"></i>
                                    <p>No students found.</p>
                                    @if(request()->hasAny(['search', 'status', 'approval_status', 'registration_type', 'grade_level', 'gender']))
                                        <a href="{{ route('admin.students.index') }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-times me-1"></i> Clear Filters
                                        </a>
                                    @endif
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

@push('scripts')
<script>
function confirmDelete(studentId, studentName) {
    if (confirm('Are you sure you want to delete student "' + studentName + '"? This action cannot be undone.')) {
        document.getElementById('delete-form-' + studentId).submit();
    }
}
</script>
@endpush

@push('styles')
<style>
/* User avatar styling */
.user-avatar {
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
}

/* WhatsApp button styling */
.btn-outline-success {
    color: #25D366;
    border-color: #25D366;
}

.btn-outline-success:hover {
    background-color: #25D366;
    border-color: #25D366;
    color: white;
}

.text-success {
    color: #25D366 !important;
}

/* Action buttons group */
.btn-group-sm > .btn {
    padding: 0.25rem 0.5rem;
}
</style>
@endpush
