@extends('layouts.app')

@section('title', 'Review Registration')
@section('page-title', 'Review Student Registration')

@section('content')
<div class="container-fluid">
    <!-- Back Button -->
    <div class="mb-4">
        <a href="{{ route('admin.approvals.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Queue
        </a>
    </div>

    <div class="row">
        <!-- Student Details -->
        <div class="col-lg-8">
            <!-- Basic Info Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-user-graduate me-2 text-primary"></i>
                            Student Information
                        </h5>
                        <span class="badge bg-warning fs-6">
                            <i class="fas fa-clock me-1"></i> Pending Approval
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="text-muted" width="140">Full Name</td>
                                    <td><strong>{{ $student->user->name }}</strong></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Student ID</td>
                                    <td><code>{{ $student->student_id }}</code></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">IC Number</td>
                                    <td>{{ $student->ic_number }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Date of Birth</td>
                                    <td>{{ \Carbon\Carbon::parse($student->date_of_birth)->format('d M Y') }}
                                        <span class="text-muted">({{ \Carbon\Carbon::parse($student->date_of_birth)->age }} years old)</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Gender</td>
                                    <td>{{ ucfirst($student->gender) }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="text-muted" width="140">Email</td>
                                    <td>{{ $student->user->email }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Phone</td>
                                    <td>{{ $student->user->phone ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">School</td>
                                    <td>{{ $student->school_name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Grade Level</td>
                                    <td>{{ $student->grade_level ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Address</td>
                                    <td>{{ $student->address ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($student->medical_conditions)
                    <div class="alert alert-warning mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Medical Conditions:</strong> {{ $student->medical_conditions }}
                    </div>
                    @endif
                </div>
            </div>

            <!-- Registration Details Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">
                        <i class="fas fa-file-alt me-2 text-info"></i>
                        Registration Details
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="text-muted" width="150">Registration Type</td>
                                    <td>
                                        @if($student->registration_type == 'online')
                                        <span class="badge bg-info">
                                            <i class="fas fa-globe me-1"></i> Online
                                        </span>
                                        @else
                                        <span class="badge bg-secondary">
                                            <i class="fas fa-building me-1"></i> Offline
                                        </span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Registration Date</td>
                                    <td>{{ $student->registration_date ? \Carbon\Carbon::parse($student->registration_date)->format('d M Y') : $student->created_at->format('d M Y') }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Submitted</td>
                                    <td>{{ $student->created_at->format('d M Y h:i A') }} <span class="text-muted">({{ $student->created_at->diffForHumans() }})</span></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="text-muted" width="150">Referred By</td>
                                    <td>
                                        @if($student->referredBy)
                                        {{ $student->referredBy->user->name }} ({{ $student->referredBy->student_id }})
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Notes</td>
                                    <td>{{ $student->notes ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Parent Information Card -->
            @if($student->parent)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">
                        <i class="fas fa-user-friends me-2 text-success"></i>
                        Parent/Guardian Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="text-muted" width="140">Name</td>
                                    <td><strong>{{ $student->parent->user->name }}</strong></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Email</td>
                                    <td>{{ $student->parent->user->email }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Phone</td>
                                    <td>{{ $student->parent->user->phone ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="text-muted" width="140">Relationship</td>
                                    <td>{{ ucfirst($student->parent->relationship ?? 'Parent') }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Occupation</td>
                                    <td>{{ $student->parent->occupation ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Address</td>
                                    <td>{{ $student->parent->address ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Potential Duplicates Warning -->
            @if($similarStudents->count() > 0)
            <div class="card border-danger mb-4">
                <div class="card-header bg-danger text-white">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Potential Duplicate Records Found</strong>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">The following existing students have similar information:</p>
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Student ID</th>
                                    <th>Name</th>
                                    <th>IC Number</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($similarStudents as $similar)
                                <tr>
                                    <td><code>{{ $similar->student_id }}</code></td>
                                    <td>{{ $similar->user->name }}</td>
                                    <td>{{ $similar->ic_number }}</td>
                                    <td>{{ $similar->user->email }}</td>
                                    <td>
                                        <span class="badge bg-{{ $similar->approval_status == 'approved' ? 'success' : 'danger' }}">
                                            {{ ucfirst($similar->approval_status) }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Action Panel -->
        <div class="col-lg-4">
            <!-- Approve Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-check-circle me-2"></i>
                        Approve Registration
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.approvals.approve', $student) }}">
                        @csrf
                        @method('PATCH')

                        <div class="mb-3">
                            <label class="form-label">Notes (Optional)</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Add any approval notes..."></textarea>
                        </div>

                        <div class="form-check mb-2">
                            <input type="checkbox" class="form-check-input" name="send_welcome" value="1" checked id="approveWelcome">
                            <label class="form-check-label" for="approveWelcome">Send Welcome Notification</label>
                        </div>

                        <div class="ps-4 mb-3">
                            <div class="form-check mb-1">
                                <input type="checkbox" class="form-check-input" name="send_whatsapp" value="1" checked id="approveWhatsapp">
                                <label class="form-check-label" for="approveWhatsapp">
                                    <i class="fab fa-whatsapp text-success me-1"></i> WhatsApp
                                </label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="send_email" value="1" checked id="approveEmail">
                                <label class="form-check-label" for="approveEmail">
                                    <i class="fas fa-envelope text-primary me-1"></i> Email
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success w-100">
                            <i class="fas fa-check me-1"></i> Approve Student
                        </button>
                    </form>
                </div>
            </div>

            <!-- Reject Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-times-circle me-2"></i>
                        Reject Registration
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.approvals.reject', $student) }}">
                        @csrf
                        @method('PATCH')

                        <div class="mb-3">
                            <label class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                            <select class="form-select mb-2" id="rejectTemplate">
                                <option value="">Select a template...</option>
                                <option value="Incomplete documentation. Please submit all required documents.">Incomplete Documentation</option>
                                <option value="Invalid IC number or personal details provided.">Invalid Personal Details</option>
                                <option value="Duplicate registration found in the system.">Duplicate Registration</option>
                                <option value="Age requirement not met for the selected program.">Age Requirement Not Met</option>
                                <option value="other">Other (Custom Reason)</option>
                            </select>
                            <textarea name="rejection_reason" class="form-control" rows="3" required
                                      placeholder="Enter rejection reason..." id="rejectReason"></textarea>
                        </div>

                        <div class="form-check mb-3">
                            <input type="checkbox" class="form-check-input" name="send_notification" value="1" checked id="rejectNotify">
                            <label class="form-check-label" for="rejectNotify">Notify parent</label>
                        </div>

                        <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Are you sure you want to reject this registration?')">
                            <i class="fas fa-times me-1"></i> Reject Registration
                        </button>
                    </form>
                </div>
            </div>

            <!-- Request Info Card -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-warning">
                    <h5 class="mb-0">
                        <i class="fas fa-question-circle me-2"></i>
                        Request More Info
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.approvals.request-info', $student) }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Information Required</label>
                            <textarea name="info_request" class="form-control" rows="3" required
                                      placeholder="What additional information do you need?"></textarea>
                        </div>

                        <button type="submit" class="btn btn-warning w-100">
                            <i class="fas fa-paper-plane me-1"></i> Send Request
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Rejection Template
document.getElementById('rejectTemplate')?.addEventListener('change', function() {
    if (this.value && this.value !== 'other') {
        document.getElementById('rejectReason').value = this.value;
    } else {
        document.getElementById('rejectReason').value = '';
    }
});
</script>
@endpush
