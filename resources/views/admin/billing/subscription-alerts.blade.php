@extends('layouts.app')

@section('title', 'Subscription Alerts')
@section('page-title', 'Subscription Alerts')

@push('styles')
<style>
    .alert-stats {
        background: linear-gradient(135deg, #ff9800 0%, #f57c00 100%);
        color: white;
        border-radius: 15px;
        padding: 25px;
    }
    .urgency-critical {
        border-left: 4px solid #c62828;
        background: #ffebee;
    }
    .urgency-high {
        border-left: 4px solid #e53935;
        background: #fff3e0;
    }
    .urgency-medium {
        border-left: 4px solid #ff9800;
        background: #fffde7;
    }
    .urgency-low {
        border-left: 4px solid #4caf50;
        background: #e8f5e9;
    }
    .expiry-card {
        transition: all 0.3s ease;
        margin-bottom: 15px;
    }
    .expiry-card:hover {
        box-shadow: 0 4px 15px rgba(0,0,0,0.15);
    }
    .days-badge {
        font-size: 1.2rem;
        font-weight: bold;
        padding: 8px 15px;
    }
    .stat-box {
        background: white;
        border-radius: 10px;
        padding: 15px;
        text-align: center;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .renewal-form {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 10px;
    }
    .attention-badge {
        animation: pulse 2s infinite;
    }
    @keyframes pulse {
        0% { opacity: 1; }
        50% { opacity: 0.6; }
        100% { opacity: 1; }
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1>
        <i class="fas fa-bell text-warning me-2"></i> Subscription Alerts
    </h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.invoices.index') }}">Billing</a></li>
            <li class="breadcrumb-item active">Subscription Alerts</li>
        </ol>
    </nav>
</div>

<!-- Summary Stats -->
<div class="row mb-4">
    <div class="col-lg-12">
        <div class="alert-stats">
            <h4 class="mb-4"><i class="fas fa-chart-line me-2"></i> Subscription Overview</h4>
            <div class="row text-center">
                <div class="col-md-2">
                    <div class="stat-box">
                        <h3 class="mb-0 text-primary">{{ $summary['total_active'] }}</h3>
                        <small class="text-muted">Active Enrollments</small>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="stat-box">
                        <h3 class="mb-0 text-danger">{{ $summary['expiring_today'] }}</h3>
                        <small class="text-muted">Expiring Today</small>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="stat-box">
                        <h3 class="mb-0 text-warning">{{ $summary['expiring_this_week'] }}</h3>
                        <small class="text-muted">Expiring This Week</small>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="stat-box">
                        <h3 class="mb-0 text-info">{{ $summary['expiring_this_month'] }}</h3>
                        <small class="text-muted">Expiring This Month</small>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="stat-box">
                        <h3 class="mb-0 text-dark">{{ $summary['expired_not_renewed'] }}</h3>
                        <small class="text-muted">Expired (Not Renewed)</small>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="stat-box">
                        <h3 class="mb-0 text-secondary">{{ $summary['without_end_date'] }}</h3>
                        <small class="text-muted">No End Date</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabs for different alert categories -->
<ul class="nav nav-tabs mb-4" id="alertTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="expiring-tab" data-bs-toggle="tab" data-bs-target="#expiring" type="button">
            <i class="fas fa-clock me-2"></i> Expiring Soon
            <span class="badge bg-warning ms-2">{{ $expiringEnrollments->count() }}</span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="expired-tab" data-bs-toggle="tab" data-bs-target="#expired" type="button">
            <i class="fas fa-times-circle me-2"></i> Expired
            <span class="badge bg-danger ms-2">{{ $expiredEnrollments->count() }}</span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="attention-tab" data-bs-toggle="tab" data-bs-target="#attention" type="button">
            <i class="fas fa-exclamation-triangle me-2"></i> Needs Attention
            <span class="badge bg-info ms-2 attention-badge">{{ $needingAttention->count() }}</span>
        </button>
    </li>
</ul>

<div class="tab-content" id="alertTabsContent">
    <!-- Expiring Soon Tab -->
    <div class="tab-pane fade show active" id="expiring" role="tabpanel">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-hourglass-half me-2"></i> Enrollments Expiring Within 30 Days</span>
                <button class="btn btn-warning btn-sm" onclick="sendBulkExpiryNotifications()">
                    <i class="fas fa-bell me-2"></i> Send Bulk Notifications
                </button>
            </div>
            <div class="card-body">
                @if($expiringEnrollments->isEmpty())
                    <div class="text-center py-5">
                        <i class="fas fa-check-circle text-success fa-4x mb-3"></i>
                        <h4>No Expiring Enrollments!</h4>
                        <p class="text-muted">All enrollments are healthy with more than 30 days remaining.</p>
                    </div>
                @else
                    <div class="row">
                        @foreach($expiringEnrollments as $enrollment)
                            @php
                                $urgencyClass = match($enrollment['urgency']) {
                                    'critical' => 'urgency-critical',
                                    'high' => 'urgency-high',
                                    'medium' => 'urgency-medium',
                                    default => 'urgency-low'
                                };
                                $badgeClass = match($enrollment['urgency']) {
                                    'critical' => 'bg-danger',
                                    'high' => 'bg-warning text-dark',
                                    'medium' => 'bg-info',
                                    default => 'bg-success'
                                };
                            @endphp
                            <div class="col-lg-6">
                                <div class="card expiry-card {{ $urgencyClass }}">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div>
                                                <h5 class="mb-1">{{ $enrollment['student_name'] }}</h5>
                                                <small class="text-muted">
                                                    <i class="fas fa-id-card me-1"></i> {{ $enrollment['student_code'] }}
                                                </small>
                                            </div>
                                            <span class="badge {{ $badgeClass }} days-badge">
                                                {{ $enrollment['days_until_expiry'] }} days
                                            </span>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-6">
                                                <small class="text-muted">Package</small>
                                                <div class="fw-bold">{{ $enrollment['package_name'] }}</div>
                                            </div>
                                            <div class="col-6">
                                                <small class="text-muted">End Date</small>
                                                <div class="fw-bold">{{ \Carbon\Carbon::parse($enrollment['end_date'])->format('d M Y') }}</div>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-6">
                                                <small class="text-muted">Monthly Fee</small>
                                                <div class="fw-bold">RM {{ number_format($enrollment['monthly_fee'], 2) }}</div>
                                            </div>
                                            <div class="col-6">
                                                <small class="text-muted">Parent</small>
                                                <div>{{ $enrollment['parent_name'] }}</div>
                                                @if($enrollment['parent_phone'] !== 'N/A')
                                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $enrollment['parent_phone']) }}"
                                                       target="_blank" class="text-success small">
                                                        <i class="fab fa-whatsapp"></i> {{ $enrollment['parent_phone'] }}
                                                    </a>
                                                @endif
                                            </div>
                                        </div>

                                        <hr>

                                        <form action="{{ route('admin.invoices.renew-enrollment', $enrollment['enrollment_id']) }}"
                                              method="POST" class="renewal-form">
                                            @csrf
                                            <div class="row g-2 align-items-end">
                                                <div class="col-4">
                                                    <label class="form-label small">Renew For</label>
                                                    <select name="months" class="form-select form-select-sm">
                                                        <option value="1">1 Month</option>
                                                        <option value="3">3 Months</option>
                                                        <option value="6">6 Months</option>
                                                        <option value="12" selected>12 Months</option>
                                                    </select>
                                                </div>
                                                <div class="col-4">
                                                    <div class="form-check">
                                                        <input type="checkbox" class="form-check-input" name="generate_invoice"
                                                               value="1" checked id="invoice_{{ $enrollment['enrollment_id'] }}">
                                                        <label class="form-check-label small" for="invoice_{{ $enrollment['enrollment_id'] }}">
                                                            Generate Invoice
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-4">
                                                    <button type="submit" class="btn btn-success btn-sm w-100">
                                                        <i class="fas fa-sync me-1"></i> Renew
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Expired Tab -->
    <div class="tab-pane fade" id="expired" role="tabpanel">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-times-circle text-danger me-2"></i> Expired Enrollments
            </div>
            <div class="card-body p-0">
                @if($expiredEnrollments->isEmpty())
                    <div class="text-center py-5">
                        <i class="fas fa-smile text-success fa-4x mb-3"></i>
                        <h4>No Expired Enrollments!</h4>
                        <p class="text-muted">All expired enrollments have been renewed or handled.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Student</th>
                                    <th>Package</th>
                                    <th>End Date</th>
                                    <th>Days Expired</th>
                                    <th>Parent</th>
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
                                            @if($enrollment['end_date'])
                                                {{ \Carbon\Carbon::parse($enrollment['end_date'])->format('d M Y') }}
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-danger">{{ $enrollment['days_expired'] }} days</span>
                                        </td>
                                        <td>
                                            {{ $enrollment['parent_name'] }}
                                            @if($enrollment['parent_phone'] !== 'N/A')
                                                <br>
                                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $enrollment['parent_phone']) }}"
                                                   target="_blank" class="text-success small">
                                                    <i class="fab fa-whatsapp"></i> Contact
                                                </a>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $enrollment['status'] === 'expired' ? 'danger' : 'secondary' }}">
                                                {{ ucfirst($enrollment['status']) }}
                                            </span>
                                        </td>
                                        <td>
                                            <form action="{{ route('admin.invoices.renew-enrollment', $enrollment['enrollment_id']) }}"
                                                  method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="months" value="12">
                                                <input type="hidden" name="generate_invoice" value="1">
                                                <button type="submit" class="btn btn-success btn-sm">
                                                    <i class="fas fa-sync me-1"></i> Renew
                                                </button>
                                            </form>
                                            <a href="{{ route('admin.students.show', $enrollment['student_id']) }}"
                                               class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-user"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Needs Attention Tab -->
    <div class="tab-pane fade" id="attention" role="tabpanel">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-exclamation-triangle text-warning me-2"></i> Students Needing Immediate Attention
            </div>
            <div class="card-body">
                @if($needingAttention->isEmpty())
                    <div class="text-center py-5">
                        <i class="fas fa-thumbs-up text-success fa-4x mb-3"></i>
                        <h4>All Clear!</h4>
                        <p class="text-muted">No students need immediate attention regarding subscriptions.</p>
                    </div>
                @else
                    <div class="row">
                        @foreach($needingAttention as $student)
                            <div class="col-lg-6 mb-4">
                                <div class="card border-warning">
                                    <div class="card-header bg-warning text-dark d-flex justify-content-between">
                                        <span>
                                            <i class="fas fa-user me-2"></i> {{ $student['student_name'] }}
                                        </span>
                                        <span class="badge bg-danger">
                                            {{ $student['soonest_expiry'] }} days until first expiry
                                        </span>
                                    </div>
                                    <div class="card-body">
                                        <div class="row mb-3">
                                            <div class="col-6">
                                                <small class="text-muted">Student ID</small>
                                                <div>{{ $student['student_code'] }}</div>
                                            </div>
                                            <div class="col-6">
                                                <small class="text-muted">Parent</small>
                                                <div>{{ $student['parent_name'] }}</div>
                                                @if($student['parent_phone'] !== 'N/A')
                                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $student['parent_phone']) }}"
                                                       target="_blank" class="text-success small">
                                                        <i class="fab fa-whatsapp"></i> {{ $student['parent_phone'] }}
                                                    </a>
                                                @endif
                                            </div>
                                        </div>

                                        <h6 class="border-bottom pb-2">Expiring Enrollments:</h6>
                                        @foreach($student['expiring_enrollments'] as $exp)
                                            <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-light rounded">
                                                <span>{{ $exp['package'] }}</span>
                                                <span class="badge bg-{{ $exp['days_remaining'] <= 3 ? 'danger' : 'warning' }}">
                                                    {{ $exp['days_remaining'] }} days left
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="card-footer bg-transparent">
                                        <a href="{{ route('admin.students.show', $student['student_id']) }}"
                                           class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-user me-1"></i> View Student
                                        </a>
                                        <button class="btn btn-outline-warning btn-sm"
                                                onclick="sendRenewalReminder({{ $student['student_id'] }})">
                                            <i class="fas fa-bell me-1"></i> Send Reminder
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function sendBulkExpiryNotifications() {
    if (confirm('Send expiry notifications to all parents with enrollments expiring within 30 days?')) {
        $.ajax({
            url: '{{ route("admin.notifications.send-bulk") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                type: 'subscription_expiry'
            },
            success: function(response) {
                alert('Notifications sent successfully!');
                location.reload();
            },
            error: function() {
                alert('Failed to send notifications. Please try again.');
            }
        });
    }
}

function sendRenewalReminder(studentId) {
    if (confirm('Send renewal reminder to this student\'s parent?')) {
        $.ajax({
            url: '/admin/students/' + studentId + '/send-renewal-reminder',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                alert('Reminder sent successfully!');
            },
            error: function() {
                alert('Failed to send reminder. Please try again.');
            }
        });
    }
}
</script>
@endpush
