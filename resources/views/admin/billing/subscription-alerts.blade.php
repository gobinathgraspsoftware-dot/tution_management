@extends('layouts.app')

@section('title', 'Subscription Alerts')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Subscription Alerts</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.invoices.index') }}">Billing</a></li>
                    <li class="breadcrumb-item active">Subscription Alerts</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="card bg-success text-white h-100">
                <div class="card-body text-center">
                    <i class="fas fa-check-circle fa-2x mb-2"></i>
                    <h3 class="mb-0">{{ $summary['total_active'] }}</h3>
                    <small>Active Enrollments</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card bg-danger text-white h-100">
                <div class="card-body text-center">
                    <i class="fas fa-exclamation-circle fa-2x mb-2"></i>
                    <h3 class="mb-0">{{ $summary['expiring_today'] }}</h3>
                    <small>Expiring Today</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card bg-warning text-dark h-100">
                <div class="card-body text-center">
                    <i class="fas fa-clock fa-2x mb-2"></i>
                    <h3 class="mb-0">{{ $summary['expiring_this_week'] }}</h3>
                    <small>Expiring This Week</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card bg-info text-white h-100">
                <div class="card-body text-center">
                    <i class="fas fa-calendar-alt fa-2x mb-2"></i>
                    <h3 class="mb-0">{{ $summary['expiring_this_month'] }}</h3>
                    <small>Expiring This Month</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-danger h-100">
                <div class="card-body text-center">
                    <h4 class="text-danger">{{ $summary['expired_not_renewed'] }}</h4>
                    <small class="text-muted">Expired (Not Renewed)</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-secondary h-100">
                <div class="card-body text-center">
                    <h4>{{ $summary['suspended'] }}</h4>
                    <small class="text-muted">Suspended</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-warning h-100">
                <div class="card-body text-center">
                    <h4 class="text-warning">{{ $summary['without_end_date'] }}</h4>
                    <small class="text-muted">Without End Date</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Students Needing Attention -->
    @if($needingAttention->count() > 0)
    <div class="card border-danger mb-4">
        <div class="card-header bg-danger text-white">
            <h5 class="mb-0"><i class="fas fa-bell me-2"></i>Students Needing Immediate Attention (Expiring Within 7 Days)</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Student</th>
                            <th>Parent Contact</th>
                            <th>Expiring Package(s)</th>
                            <th>Soonest Expiry</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($needingAttention as $student)
                            <tr>
                                <td>
                                    <strong>{{ $student['student_name'] }}</strong>
                                    <br><small class="text-muted">{{ $student['student_code'] }}</small>
                                </td>
                                <td>
                                    {{ $student['parent_name'] }}
                                    @if($student['parent_phone'])
                                        <br>
                                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $student['parent_phone']) }}" target="_blank" class="text-success">
                                            <i class="fab fa-whatsapp"></i> {{ $student['parent_phone'] }}
                                        </a>
                                    @endif
                                </td>
                                <td>
                                    @foreach($student['expiring_enrollments'] as $enrollment)
                                        <span class="badge bg-warning text-dark mb-1">
                                            {{ $enrollment['package'] }}
                                            ({{ $enrollment['days_remaining'] }} days)
                                        </span>
                                    @endforeach
                                </td>
                                <td>
                                    @if($student['soonest_expiry'] <= 0)
                                        <span class="badge bg-danger">Expired</span>
                                    @elseif($student['soonest_expiry'] <= 3)
                                        <span class="badge bg-danger">{{ $student['soonest_expiry'] }} day(s)</span>
                                    @else
                                        <span class="badge bg-warning text-dark">{{ $student['soonest_expiry'] }} days</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.students.show', $student['student_id']) }}"
                                           class="btn btn-outline-primary" title="View Student">
                                            <i class="fas fa-user"></i>
                                        </a>
                                        @foreach($student['expiring_enrollments'] as $enrollment)
                                            <button type="button" class="btn btn-outline-success renew-btn"
                                                    data-enrollment-id="{{ $enrollment['enrollment_id'] }}"
                                                    data-student-name="{{ $student['student_name'] }}"
                                                    data-package-name="{{ $enrollment['package'] }}"
                                                    title="Renew {{ $enrollment['package'] }}">
                                                <i class="fas fa-sync-alt"></i>
                                            </button>
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Expiring Enrollments -->
    <div class="card mb-4">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0"><i class="fas fa-hourglass-half me-2"></i>Expiring Enrollments (Next 30 Days)</h5>
        </div>
        <div class="card-body p-0">
            @if($expiringEnrollments->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Student</th>
                                <th>Package</th>
                                <th>Parent Contact</th>
                                <th>End Date</th>
                                <th>Days Left</th>
                                <th>Monthly Fee</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($expiringEnrollments as $enrollment)
                                <tr class="{{ $enrollment['urgency'] === 'critical' ? 'table-danger' : ($enrollment['urgency'] === 'high' ? 'table-warning' : '') }}">
                                    <td>
                                        <strong>{{ $enrollment['student_name'] }}</strong>
                                        <br><small class="text-muted">{{ $enrollment['student_code'] }}</small>
                                    </td>
                                    <td>
                                        {{ $enrollment['package_name'] }}
                                        <br><small class="text-muted">{{ ucfirst($enrollment['package_type']) }}</small>
                                    </td>
                                    <td>
                                        {{ $enrollment['parent_name'] }}
                                        @if($enrollment['parent_phone'])
                                            <br>
                                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $enrollment['parent_phone']) }}" target="_blank" class="text-success">
                                                <i class="fab fa-whatsapp"></i> {{ $enrollment['parent_phone'] }}
                                            </a>
                                        @endif
                                    </td>
                                    <td>{{ $enrollment['end_date']->format('d M Y') }}</td>
                                    <td>
                                        @if($enrollment['days_until_expiry'] <= 0)
                                            <span class="badge bg-danger">Expired</span>
                                        @elseif($enrollment['days_until_expiry'] <= 3)
                                            <span class="badge bg-danger">{{ $enrollment['days_until_expiry'] }} days</span>
                                        @elseif($enrollment['days_until_expiry'] <= 7)
                                            <span class="badge bg-warning text-dark">{{ $enrollment['days_until_expiry'] }} days</span>
                                        @else
                                            <span class="badge bg-info">{{ $enrollment['days_until_expiry'] }} days</span>
                                        @endif
                                    </td>
                                    <td>RM {{ number_format($enrollment['monthly_fee'], 2) }}</td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-success renew-btn"
                                                    data-enrollment-id="{{ $enrollment['enrollment_id'] }}"
                                                    data-student-name="{{ $enrollment['student_name'] }}"
                                                    data-package-name="{{ $enrollment['package_name'] }}"
                                                    title="Renew">
                                                <i class="fas fa-sync-alt"></i> Renew
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-check-circle fa-3x mb-3 text-success"></i>
                    <p>No enrollments expiring in the next 30 days</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Expired Enrollments -->
    <div class="card">
        <div class="card-header bg-danger text-white">
            <h5 class="mb-0"><i class="fas fa-times-circle me-2"></i>Expired Enrollments (Not Renewed)</h5>
        </div>
        <div class="card-body p-0">
            @if($expiredEnrollments->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Student</th>
                                <th>Package</th>
                                <th>Parent Contact</th>
                                <th>Expired On</th>
                                <th>Days Expired</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($expiredEnrollments as $enrollment)
                                <tr>
                                    <td>
                                        <strong>{{ $enrollment['student_name'] }}</strong>
                                        <br><small class="text-muted">{{ $enrollment['student_code'] }}</small>
                                    </td>
                                    <td>{{ $enrollment['package_name'] }}</td>
                                    <td>
                                        {{ $enrollment['parent_name'] }}
                                        @if($enrollment['parent_phone'])
                                            <br>
                                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $enrollment['parent_phone']) }}" target="_blank" class="text-success">
                                                <i class="fab fa-whatsapp"></i> {{ $enrollment['parent_phone'] }}
                                            </a>
                                        @endif
                                    </td>
                                    <td>{{ $enrollment['end_date'] ? $enrollment['end_date']->format('d M Y') : 'N/A' }}</td>
                                    <td><span class="badge bg-danger">{{ $enrollment['days_expired'] }} days</span></td>
                                    <td><span class="badge bg-secondary">{{ ucfirst($enrollment['status']) }}</span></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-success renew-btn"
                                                    data-enrollment-id="{{ $enrollment['enrollment_id'] }}"
                                                    data-student-name="{{ $enrollment['student_name'] }}"
                                                    data-package-name="{{ $enrollment['package_name'] }}"
                                                    title="Renew">
                                                <i class="fas fa-sync-alt"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-smile fa-3x mb-3 text-success"></i>
                    <p>No expired enrollments pending renewal</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Renewal Modal -->
<div class="modal fade" id="renewModal" tabindex="-1" aria-labelledby="renewModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="renewForm" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="renewModalLabel">Renew Enrollment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Renew enrollment for <strong id="renewStudentName"></strong> - <strong id="renewPackageName"></strong></p>

                    <div class="mb-3">
                        <label for="renewMonths" class="form-label">Duration (Months)</label>
                        <select name="months" id="renewMonths" class="form-select" required>
                            <option value="1">1 Month</option>
                            <option value="3">3 Months</option>
                            <option value="6">6 Months</option>
                            <option value="12" selected>12 Months</option>
                            <option value="24">24 Months</option>
                        </select>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="generate_invoice" id="generateInvoice" value="1" checked>
                        <label class="form-check-label" for="generateInvoice">
                            Generate renewal invoice
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-sync-alt me-1"></i> Renew Enrollment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Handle renewal button click
    $('.renew-btn').on('click', function() {
        var enrollmentId = $(this).data('enrollment-id');
        var studentName = $(this).data('student-name');
        var packageName = $(this).data('package-name');

        $('#renewStudentName').text(studentName);
        $('#renewPackageName').text(packageName);
        $('#renewForm').attr('action', '{{ url("admin/billing/renew-enrollment") }}/' + enrollmentId);

        $('#renewModal').modal('show');
    });
});
</script>
@endpush
