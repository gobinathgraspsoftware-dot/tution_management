@extends('layouts.app')

@section('title', 'Approval History')
@section('page-title', 'Student Approval History')

@section('content')
<div class="container-fluid">
    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-check-circle text-success fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0">{{ $stats['approved_total'] }}</h3>
                            <p class="text-muted mb-0">Total Approved</p>
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
                            <div class="bg-danger bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-times-circle text-danger fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0">{{ $stats['rejected_total'] }}</h3>
                            <p class="text-muted mb-0">Total Rejected</p>
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
                                <i class="fas fa-calendar-check text-primary fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0">{{ $stats['approved_this_month'] }}</h3>
                            <p class="text-muted mb-0">Approved This Month</p>
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
                            <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-calendar-times text-warning fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0">{{ $stats['rejected_this_month'] }}</h3>
                            <p class="text-muted mb-0">Rejected This Month</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.approvals.history') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Name, Student ID..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
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
                    <a href="{{ route('admin.approvals.history') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-redo me-1"></i> Reset
                    </a>
                    <a href="{{ route('admin.approvals.index') }}" class="btn btn-outline-warning">
                        <i class="fas fa-clock me-1"></i> Pending
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- History Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">
                <i class="fas fa-history me-2 text-primary"></i>
                Processed Registrations ({{ $processedStudents->total() }})
            </h5>
        </div>
        <div class="card-body p-0">
            @if($processedStudents->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Student</th>
                            <th>Student ID</th>
                            <th>Status</th>
                            <th>Processed By</th>
                            <th>Processed At</th>
                            <th>Reason/Notes</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($processedStudents as $student)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm bg-{{ $student->approval_status == 'approved' ? 'success' : 'danger' }} bg-opacity-10 rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                        <i class="fas fa-{{ $student->approval_status == 'approved' ? 'check' : 'times' }} text-{{ $student->approval_status == 'approved' ? 'success' : 'danger' }}"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">{{ $student->user->name }}</h6>
                                        <small class="text-muted">{{ $student->user->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td><code>{{ $student->student_id }}</code></td>
                            <td>
                                @if($student->approval_status == 'approved')
                                <span class="badge bg-success">
                                    <i class="fas fa-check-circle me-1"></i> Approved
                                </span>
                                @else
                                <span class="badge bg-danger">
                                    <i class="fas fa-times-circle me-1"></i> Rejected
                                </span>
                                @endif
                            </td>
                            <td>
                                @if($student->approver)
                                {{ $student->approver->name }}
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($student->approved_at)
                                {{ \Carbon\Carbon::parse($student->approved_at)->format('d M Y') }}
                                <br>
                                <small class="text-muted">{{ \Carbon\Carbon::parse($student->approved_at)->format('h:i A') }}</small>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td style="max-width: 200px;">
                                @if($student->approval_status == 'rejected' && $student->rejection_reason)
                                <span class="text-danger" title="{{ $student->rejection_reason }}">
                                    {{ Str::limit($student->rejection_reason, 50) }}
                                </span>
                                @elseif($student->notes)
                                <span class="text-muted" title="{{ $student->notes }}">
                                    {{ Str::limit($student->notes, 50) }}
                                </span>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.students.show', $student) }}" class="btn btn-outline-primary" title="View Profile">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($student->approval_status == 'approved')
                                    <button type="button" class="btn btn-outline-success" title="Resend Welcome"
                                            onclick="resendWelcome({{ $student->id }}, '{{ $student->user->name }}')">
                                        <i class="fas fa-paper-plane"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-3 py-3">
                {{ $processedStudents->withQueryString()->links() }}
            </div>
            @else
            <div class="text-center py-5">
                <i class="fas fa-folder-open fa-4x text-muted mb-3"></i>
                <h5>No Records Found</h5>
                <p class="text-muted">No processed registrations match your criteria.</p>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Resend Welcome Modal -->
<div class="modal fade" id="resendWelcomeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="resendWelcomeForm">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fas fa-paper-plane me-2"></i>Resend Welcome</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Resend welcome notification to <strong id="resendStudentName"></strong>?</p>

                    <div class="form-check mb-2">
                        <input type="checkbox" class="form-check-input" name="whatsapp" value="1" checked id="resendWhatsapp">
                        <label class="form-check-label" for="resendWhatsapp">
                            <i class="fab fa-whatsapp text-success me-1"></i> WhatsApp
                        </label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="email" value="1" checked id="resendEmail">
                        <label class="form-check-label" for="resendEmail">
                            <i class="fas fa-envelope text-primary me-1"></i> Email
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-paper-plane me-1"></i> Send
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function resendWelcome(studentId, studentName) {
    document.getElementById('resendStudentName').textContent = studentName;
    document.getElementById('resendWelcomeForm').action = `/admin/approvals/${studentId}/resend-welcome`;
    new bootstrap.Modal(document.getElementById('resendWelcomeModal')).show();
}
</script>
@endpush
