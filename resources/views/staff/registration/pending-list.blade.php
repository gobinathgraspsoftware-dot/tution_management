@extends('layouts.app')

@section('title', 'Pending Registrations')
@section('page-title', 'Pending Registrations')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="fas fa-clock me-2"></i> Pending Registrations</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('staff.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Pending Registrations</li>
            </ol>
        </nav>
    </div>
    <div>
        <a href="{{ route('staff.registration.create-student') }}" class="btn btn-primary">
            <i class="fas fa-user-plus me-1"></i> New Registration
        </a>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('staff.registration.pending') }}" method="GET" class="row g-3">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control" name="search"
                           value="{{ request('search') }}" placeholder="Search by name, ID, IC...">
                </div>
            </div>
            <div class="col-md-3">
                <select class="form-select" name="registration_type">
                    <option value="">All Registration Types</option>
                    <option value="online" {{ request('registration_type') == 'online' ? 'selected' : '' }}>Online</option>
                    <option value="offline" {{ request('registration_type') == 'offline' ? 'selected' : '' }}>Offline (Staff)</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter me-1"></i> Filter
                </button>
            </div>
            @if(request()->hasAny(['search', 'registration_type']))
            <div class="col-md-2">
                <a href="{{ route('staff.registration.pending') }}" class="btn btn-secondary w-100">
                    <i class="fas fa-times me-1"></i> Clear
                </a>
            </div>
            @endif
        </form>
    </div>
</div>

<!-- Pending List -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-list me-2"></i> Pending Approvals</span>
        <span class="badge bg-warning">{{ $pendingStudents->total() }} pending</span>
    </div>
    <div class="card-body p-0">
        @if($pendingStudents->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Student</th>
                        <th>Parent</th>
                        <th>School / Grade</th>
                        <th>Registration</th>
                        <th>Date</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pendingStudents as $student)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="user-avatar me-2" style="width: 36px; height: 36px; font-size: 0.9rem;">
                                    {{ substr($student->user->name, 0, 1) }}
                                </div>
                                <div>
                                    <strong>{{ $student->user->name }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $student->student_id }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($student->parent)
                                {{ $student->parent->user->name }}
                                <br>
                                <small class="text-muted">{{ $student->parent->user->phone }}</small>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            {{ $student->school_name }}
                            <br>
                            <span class="badge bg-secondary">{{ $student->grade_level }}</span>
                        </td>
                        <td>
                            @if($student->registration_type == 'online')
                                <span class="badge bg-info">Online</span>
                            @else
                                <span class="badge bg-primary">Offline</span>
                            @endif
                            @if($student->referred_by)
                                <br>
                                <small class="text-success">
                                    <i class="fas fa-gift me-1"></i> Referred
                                </small>
                            @endif
                        </td>
                        <td>
                            {{ $student->registration_date ? $student->registration_date->format('d M Y') : $student->created_at->format('d M Y') }}
                            <br>
                            <small class="text-muted">{{ $student->created_at->diffForHumans() }}</small>
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.students.show', $student) }}" class="btn btn-outline-primary" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <button type="button" class="btn btn-outline-success"
                                        onclick="approveStudent({{ $student->id }})" title="Approve">
                                    <i class="fas fa-check"></i>
                                </button>
                                <button type="button" class="btn btn-outline-danger"
                                        onclick="showRejectModal({{ $student->id }}, '{{ $student->user->name }}')" title="Reject">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($pendingStudents->hasPages())
        <div class="card-footer">
            {{ $pendingStudents->links() }}
        </div>
        @endif
        @else
        <div class="text-center py-5">
            <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
            <h5>No Pending Registrations</h5>
            <p class="text-muted">All registrations have been processed.</p>
        </div>
        @endif
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="rejectForm" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-header">
                    <h5 class="modal-title">Reject Registration</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to reject <strong id="rejectStudentName"></strong>'s registration?</p>
                    <div class="mb-3">
                        <label for="rejection_reason" class="form-label">Reason for Rejection <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="3"
                                  placeholder="Please provide a reason..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times me-1"></i> Reject Registration
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function approveStudent(studentId) {
        if (confirm('Are you sure you want to approve this registration?')) {
            $.ajax({
                url: '/admin/students/' + studentId + '/approve',
                type: 'PATCH',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    location.reload();
                },
                error: function(xhr) {
                    alert('Error: ' + xhr.responseJSON.message);
                }
            });
        }
    }

    function showRejectModal(studentId, studentName) {
        $('#rejectStudentName').text(studentName);
        $('#rejectForm').attr('action', '/admin/students/' + studentId + '/reject');
        $('#rejectModal').modal('show');
    }
</script>
@endpush
