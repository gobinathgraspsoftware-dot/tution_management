@extends('layouts.admin')

@section('title', 'Low Attendance Alerts')

@section('content')
<div class="container-fluid">
    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Low Attendance Alerts</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.attendance.reports.index') }}">Attendance Reports</a></li>
                    <li class="breadcrumb-item active">Low Attendance</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.attendance.reports.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Reports
            </a>
        </div>
    </div>

    {{-- Alert Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Filters --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.attendance.reports.low-attendance') }}" class="row g-3">
                <div class="col-md-3">
                    <label for="threshold" class="form-label">Attendance Threshold (%)</label>
                    <select name="threshold" id="threshold" class="form-select">
                        <option value="75" {{ $threshold == 75 ? 'selected' : '' }}>Below 75%</option>
                        <option value="80" {{ $threshold == 80 ? 'selected' : '' }}>Below 80%</option>
                        <option value="85" {{ $threshold == 85 ? 'selected' : '' }}>Below 85%</option>
                        <option value="90" {{ $threshold == 90 ? 'selected' : '' }}>Below 90%</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="class_id" class="form-label">Filter by Class</label>
                    <select name="class_id" id="class_id" class="form-select">
                        <option value="">All Classes</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ $classId == $class->id ? 'selected' : '' }}>
                                {{ $class->name }} ({{ $class->subject->name ?? 'N/A' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-filter me-2"></i>Apply Filter
                    </button>
                    <a href="{{ route('admin.attendance.reports.low-attendance') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-2"></i>Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        {{-- Low Attendance Students --}}
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>Students Below {{ $threshold }}% Attendance
                    </h5>
                    @if($lowAttendanceStudents->count() > 0)
                        <form method="POST" action="{{ route('admin.attendance.reports.bulk-alerts') }}" id="bulkAlertForm">
                            @csrf
                            <input type="hidden" name="class_id" value="{{ $classId }}">
                            <button type="submit" class="btn btn-sm btn-light" id="sendBulkAlerts" disabled>
                                <i class="fas fa-bell me-2"></i>Send Bulk Alerts
                            </button>
                        </form>
                    @endif
                </div>
                <div class="card-body p-0">
                    @if($lowAttendanceStudents->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th width="40">
                                            <input type="checkbox" class="form-check-input" id="selectAll">
                                        </th>
                                        <th>Student</th>
                                        <th>Class</th>
                                        <th class="text-center">Attendance %</th>
                                        <th class="text-center">Sessions</th>
                                        <th width="120">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($lowAttendanceStudents as $summary)
                                        <tr>
                                            <td>
                                                <input type="checkbox" class="form-check-input student-checkbox" 
                                                       name="student_ids[]" form="bulkAlertForm"
                                                       value="{{ $summary->student_id }}">
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-sm bg-light rounded-circle me-2">
                                                        <span class="avatar-text text-dark">
                                                            {{ substr($summary->student->user->name ?? 'N', 0, 1) }}
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <div class="fw-semibold">{{ $summary->student->user->name ?? 'N/A' }}</div>
                                                        <small class="text-muted">{{ $summary->student->student_id ?? '' }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div>{{ $summary->class->name ?? 'N/A' }}</div>
                                                <small class="text-muted">{{ $summary->class->subject->name ?? '' }}</small>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge {{ $summary->attendance_percentage < 50 ? 'bg-danger' : 'bg-warning text-dark' }} fs-6">
                                                    {{ number_format($summary->attendance_percentage, 1) }}%
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-success">{{ $summary->present_count }}</span> /
                                                <span class="text-muted">{{ $summary->total_sessions }}</span>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-outline-warning" 
                                                        onclick="openAlertModal({{ $summary->student_id }}, {{ $summary->class_id }}, '{{ $summary->student->user->name ?? 'Student' }}', {{ $summary->attendance_percentage }})">
                                                    <i class="fas fa-bell"></i> Alert
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer">
                            {{ $lowAttendanceStudents->withQueryString()->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                            <h5 class="text-muted">No Students Below {{ $threshold }}% Attendance</h5>
                            <p class="text-muted mb-0">All students are maintaining good attendance.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Alert History --}}
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-history me-2 text-primary"></i>Recent Alerts Sent
                    </h5>
                </div>
                <div class="card-body p-0">
                    @if($alerts->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($alerts as $alert)
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <div class="fw-semibold">{{ $alert->student->user->name ?? 'N/A' }}</div>
                                            <small class="text-muted">
                                                {{ $alert->class->name ?? 'N/A' }} - {{ number_format($alert->attendance_percentage, 1) }}%
                                            </small>
                                        </div>
                                        <span class="badge bg-{{ $alert->status == 'sent' ? 'success' : 'secondary' }}">
                                            {{ ucfirst($alert->status) }}
                                        </span>
                                    </div>
                                    <div class="mt-2">
                                        <small class="text-muted">
                                            <i class="fas fa-clock me-1"></i>{{ $alert->notified_at ? $alert->notified_at->format('d/m/Y H:i') : 'Pending' }}
                                            @if($alert->notifiedBy)
                                                <span class="ms-2">
                                                    <i class="fas fa-user me-1"></i>{{ $alert->notifiedBy->name }}
                                                </span>
                                            @endif
                                        </small>
                                    </div>
                                    @if($alert->alert_message)
                                        <div class="mt-2">
                                            <small class="text-muted fst-italic">"{{ Str::limit($alert->alert_message, 80) }}"</small>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        <div class="card-footer">
                            {{ $alerts->withQueryString()->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">No alerts sent yet.</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Statistics Card --}}
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-pie me-2 text-info"></i>Statistics
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-4">
                            <h3 class="text-danger mb-0">{{ $lowAttendanceStudents->total() }}</h3>
                            <small class="text-muted">At Risk</small>
                        </div>
                        <div class="col-4">
                            <h3 class="text-success mb-0">{{ $alerts->where('status', 'sent')->count() }}</h3>
                            <small class="text-muted">Alerts Sent</small>
                        </div>
                        <div class="col-4">
                            <h3 class="text-warning mb-0">{{ $alerts->where('status', 'pending')->count() }}</h3>
                            <small class="text-muted">Pending</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Send Alert Modal --}}
<div class="modal fade" id="alertModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.attendance.reports.send-alert') }}">
                @csrf
                <input type="hidden" name="student_id" id="alertStudentId">
                <input type="hidden" name="class_id" id="alertClassId">
                
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">
                        <i class="fas fa-bell me-2"></i>Send Low Attendance Alert
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong>Student:</strong> <span id="alertStudentName"></span><br>
                        <strong>Current Attendance:</strong> <span id="alertPercentage"></span>%
                    </div>
                    
                    <div class="mb-3">
                        <label for="alertMessage" class="form-label">Custom Message (Optional)</label>
                        <textarea name="message" id="alertMessage" class="form-control" rows="3" 
                                  placeholder="Enter a custom message for the parent..."></textarea>
                        <small class="text-muted">Leave blank to use the default message.</small>
                    </div>
                    
                    <div class="alert alert-secondary mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        This alert will be sent to the parent via WhatsApp and Email.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-paper-plane me-2"></i>Send Alert
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.avatar {
    width: 35px;
    height: 35px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.avatar-text {
    font-weight: 600;
    font-size: 14px;
}
</style>
@endpush

@push('scripts')
<script>
function openAlertModal(studentId, classId, studentName, percentage) {
    document.getElementById('alertStudentId').value = studentId;
    document.getElementById('alertClassId').value = classId;
    document.getElementById('alertStudentName').textContent = studentName;
    document.getElementById('alertPercentage').textContent = percentage.toFixed(1);
    document.getElementById('alertMessage').value = '';
    
    new bootstrap.Modal(document.getElementById('alertModal')).show();
}

// Select all checkbox functionality
document.getElementById('selectAll')?.addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.student-checkbox');
    checkboxes.forEach(cb => cb.checked = this.checked);
    updateBulkButton();
});

// Individual checkbox change
document.querySelectorAll('.student-checkbox').forEach(cb => {
    cb.addEventListener('change', updateBulkButton);
});

function updateBulkButton() {
    const checked = document.querySelectorAll('.student-checkbox:checked').length;
    const bulkBtn = document.getElementById('sendBulkAlerts');
    if (bulkBtn) {
        bulkBtn.disabled = checked === 0;
        bulkBtn.innerHTML = checked > 0 
            ? `<i class="fas fa-bell me-2"></i>Send Alerts (${checked})`
            : '<i class="fas fa-bell me-2"></i>Send Bulk Alerts';
    }
}

// Confirm bulk alerts
document.getElementById('bulkAlertForm')?.addEventListener('submit', function(e) {
    const checked = document.querySelectorAll('.student-checkbox:checked').length;
    if (!confirm(`Are you sure you want to send alerts to ${checked} parent(s)?`)) {
        e.preventDefault();
    }
});
</script>
@endpush
