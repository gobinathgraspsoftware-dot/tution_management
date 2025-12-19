@extends('layouts.app')

@section('title', 'Pending Approvals')
@section('page-title', 'Student Approval Queue')

@section('content')
<div class="container-fluid">
    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-clock text-warning fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0">{{ $counts['pending'] }}</h3>
                            <p class="text-muted mb-0">Total Pending</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-info bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-globe text-info fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0">{{ $counts['online'] }}</h3>
                            <p class="text-muted mb-0">Online Registration</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-building text-primary fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0">{{ $counts['offline'] }}</h3>
                            <p class="text-muted mb-0">Offline Registration</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-calendar-day text-success fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0">{{ $counts['today'] }}</h3>
                            <p class="text-muted mb-0">Today's Submissions</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters & Actions -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.approvals.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Name, Email, IC..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Registration Type</label>
                    <select name="registration_type" class="form-select">
                        <option value="">All Types</option>
                        <option value="online" {{ request('registration_type') == 'online' ? 'selected' : '' }}>Online</option>
                        <option value="offline" {{ request('registration_type') == 'offline' ? 'selected' : '' }}>Offline</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Date From</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Date To</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-1"></i> Filter
                    </button>
                    <a href="{{ route('admin.approvals.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-redo me-1"></i> Reset
                    </a>
                    <a href="{{ route('admin.approvals.history') }}" class="btn btn-outline-info">
                        <i class="fas fa-history me-1"></i> History
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Pending Students Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-user-clock me-2 text-warning"></i>
                    Pending Approvals ({{ $pendingStudents->total() }})
                </h5>
                @if($pendingStudents->count() > 0)
                <form method="POST" action="{{ route('admin.approvals.bulk-approve') }}" id="bulkApproveForm">
                    @csrf
                    <button type="submit" class="btn btn-success btn-sm" id="bulkApproveBtn" disabled>
                        <i class="fas fa-check-double me-1"></i> Approve Selected (<span id="selectedCount">0</span>)
                    </button>
                </form>
                @endif
            </div>
        </div>
        <div class="card-body p-0">
            @if($pendingStudents->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="40">
                                <input type="checkbox" class="form-check-input" id="selectAll">
                            </th>
                            <th>Student</th>
                            <th>Parent</th>
                            <th>Type</th>
                            <th>Registration Date</th>
                            <th>Waiting Time</th>
                            <th width="200">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingStudents as $student)
                        <tr>
                            <td>
                                <input type="checkbox" class="form-check-input student-checkbox"
                                       name="student_ids[]" value="{{ $student->id }}" form="bulkApproveForm">
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm bg-primary bg-opacity-10 rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                        <span class="text-primary fw-bold">{{ substr($student->user->name, 0, 1) }}</span>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">{{ $student->user->name }}</h6>
                                        <small class="text-muted">{{ $student->student_id }}</small>
                                        <br>
                                        <small class="text-muted">{{ $student->user->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($student->parent)
                                <span>{{ $student->parent->user->name }}</span>
                                <br>
                                <small class="text-muted">{{ $student->parent->user->phone }}</small>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($student->registration_type == 'online')
                                <span class="badge bg-info">Online</span>
                                @else
                                <span class="badge bg-secondary">Offline</span>
                                @endif
                            </td>
                            <td>
                                {{ $student->registration_date ? \Carbon\Carbon::parse($student->registration_date)->format('d M Y') : $student->created_at->format('d M Y') }}
                            </td>
                            <td>
                                <span class="badge {{ $student->waiting_status['badge'] }}">
                                    {{ $student->waiting_status['value'] }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.approvals.show', $student) }}" class="btn btn-outline-primary" title="Review">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <button type="button" class="btn btn-success" title="Quick Approve"
                                            onclick="quickApprove({{ $student->id }}, '{{ $student->user->name }}')">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button type="button" class="btn btn-danger" title="Quick Reject"
                                            onclick="quickReject({{ $student->id }}, '{{ $student->user->name }}')">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-3 py-3">
                {{ $pendingStudents->withQueryString()->links() }}
            </div>
            @else
            <div class="text-center py-5">
                <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                <h5>No Pending Approvals</h5>
                <p class="text-muted">All student registrations have been processed.</p>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Quick Approve Modal -->
<div class="modal fade" id="quickApproveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="quickApproveForm">
                @csrf
                @method('PATCH')
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fas fa-check-circle me-2"></i>Approve Student</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>You are about to approve <strong id="approveStudentName"></strong>.</p>

                    <div class="mb-3">
                        <label class="form-label">Notes (Optional)</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Add any notes..."></textarea>
                    </div>

                    <div class="form-check mb-2">
                        <input type="checkbox" class="form-check-input" name="send_welcome" value="1" checked id="sendWelcome">
                        <label class="form-check-label" for="sendWelcome">Send Welcome Notification</label>
                    </div>
                    <div class="ps-4">
                        <div class="form-check mb-1">
                            <input type="checkbox" class="form-check-input" name="send_whatsapp" value="1" checked id="sendWhatsapp">
                            <label class="form-check-label" for="sendWhatsapp">
                                <i class="fab fa-whatsapp text-success me-1"></i> WhatsApp
                            </label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="send_email" value="1" checked id="sendEmail">
                            <label class="form-check-label" for="sendEmail">
                                <i class="fas fa-envelope text-primary me-1"></i> Email
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check me-1"></i> Approve
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Quick Reject Modal -->
<div class="modal fade" id="quickRejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="quickRejectForm">
                @csrf
                @method('PATCH')
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="fas fa-times-circle me-2"></i>Reject Registration</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>You are about to reject <strong id="rejectStudentName"></strong>'s registration.</p>

                    <div class="mb-3">
                        <label class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                        <select class="form-select mb-2" id="rejectionTemplate">
                            <option value="">Select a template...</option>
                            <option value="Incomplete documentation. Please submit all required documents.">Incomplete Documentation</option>
                            <option value="Invalid IC number or personal details provided.">Invalid Personal Details</option>
                            <option value="Duplicate registration found in the system.">Duplicate Registration</option>
                            <option value="Age requirement not met for the selected program.">Age Requirement Not Met</option>
                            <option value="other">Other (Custom Reason)</option>
                        </select>
                        <textarea name="rejection_reason" class="form-control" rows="3" required
                                  placeholder="Enter rejection reason..." id="rejectionReason"></textarea>
                    </div>

                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="send_notification" value="1" checked id="sendRejectionNotif">
                        <label class="form-check-label" for="sendRejectionNotif">Send notification to parent</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times me-1"></i> Reject
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Select All Checkbox
document.getElementById('selectAll')?.addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.student-checkbox');
    checkboxes.forEach(cb => cb.checked = this.checked);
    updateBulkButton();
});

// Individual checkboxes
document.querySelectorAll('.student-checkbox').forEach(cb => {
    cb.addEventListener('change', updateBulkButton);
});

function updateBulkButton() {
    const checked = document.querySelectorAll('.student-checkbox:checked').length;
    document.getElementById('selectedCount').textContent = checked;
    document.getElementById('bulkApproveBtn').disabled = checked === 0;
}

// Quick Approve
function quickApprove(studentId, studentName) {
    document.getElementById('approveStudentName').textContent = studentName;
    document.getElementById('quickApproveForm').action = `/admin/approvals/${studentId}/approve`;
    new bootstrap.Modal(document.getElementById('quickApproveModal')).show();
}

// Quick Reject
function quickReject(studentId, studentName) {
    document.getElementById('rejectStudentName').textContent = studentName;
    document.getElementById('quickRejectForm').action = `/admin/approvals/${studentId}/reject`;
    document.getElementById('rejectionReason').value = '';
    document.getElementById('rejectionTemplate').value = '';
    new bootstrap.Modal(document.getElementById('quickRejectModal')).show();
}

// Rejection Template
document.getElementById('rejectionTemplate')?.addEventListener('change', function() {
    if (this.value && this.value !== 'other') {
        document.getElementById('rejectionReason').value = this.value;
    } else {
        document.getElementById('rejectionReason').value = '';
    }
});

// Bulk Approve Confirmation
document.getElementById('bulkApproveForm')?.addEventListener('submit', function(e) {
    const count = document.querySelectorAll('.student-checkbox:checked').length;
    if (!confirm(`Are you sure you want to approve ${count} student(s)?`)) {
        e.preventDefault();
    }
});
</script>
@endpush
