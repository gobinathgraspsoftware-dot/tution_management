@extends('layouts.app')

@section('title', 'Seminar Details')
@section('page-title', 'Seminar Details')

@section('content')
<div class="page-header">
    <h1>
        <i class="fas fa-calendar-alt me-2"></i> Seminar Details
    </h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.seminars.index') }}">Seminars</a></li>
            <li class="breadcrumb-item active">{{ $seminar->code }}</li>
        </ol>
    </nav>
</div>

<!-- Action Buttons -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h5 class="mb-0">{{ $seminar->name }}</h5>
        <p class="text-muted mb-0">{{ $seminar->code }}</p>
    </div>
    <div>
        @can('edit-seminars')
        <a href="{{ route('admin.seminars.edit', $seminar) }}" class="btn btn-warning">
            <i class="fas fa-edit"></i> Edit Seminar
        </a>
        @endcan
        @can('export-seminar-participants')
        <a href="{{ route('admin.seminars.export-participants', $seminar) }}" class="btn btn-success">
            <i class="fas fa-download"></i> Export Participants
        </a>
        @endcan
    </div>
</div>

<!-- Seminar Information -->
<div class="row mb-4">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Seminar Information</h5>
            </div>
            <div class="card-body">
                @if($seminar->image)
                <div class="text-center mb-3">
                    <img src="{{ asset('storage/' . $seminar->image) }}" alt="{{ $seminar->name }}" class="img-fluid rounded" style="max-height: 300px;">
                </div>
                @endif

                <table class="table table-borderless">
                    <tr>
                        <th width="200">Type:</th>
                        <td><span class="badge bg-secondary">{{ strtoupper($seminar->type) }}</span></td>
                    </tr>
                    <tr>
                        <th>Status:</th>
                        <td>
                            @php
                                $statusColors = [
                                    'draft' => 'secondary',
                                    'open' => 'success',
                                    'closed' => 'warning',
                                    'completed' => 'info',
                                    'cancelled' => 'danger'
                                ];
                            @endphp
                            <span class="badge bg-{{ $statusColors[$seminar->status] ?? 'secondary' }}">{{ ucfirst($seminar->status) }}</span>
                        </td>
                    </tr>
                    <tr>
                        <th>Date & Time:</th>
                        <td>
                            {{ $seminar->date->format('l, d F Y') }}
                            @if($seminar->start_time)
                            <br><small class="text-muted">{{ \Carbon\Carbon::parse($seminar->start_time)->format('h:i A') }}
                            @if($seminar->end_time)
                             - {{ \Carbon\Carbon::parse($seminar->end_time)->format('h:i A') }}
                            @endif
                            </small>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Venue:</th>
                        <td>
                            @if($seminar->is_online)
                            <span class="badge bg-info"><i class="fas fa-video"></i> Online Seminar</span>
                            @if($seminar->meeting_link)
                            <br><small><a href="{{ $seminar->meeting_link }}" target="_blank">{{ $seminar->meeting_link }}</a></small>
                            @endif
                            @else
                            {{ $seminar->venue }}
                            @endif
                        </td>
                    </tr>
                    @if($seminar->facilitator)
                    <tr>
                        <th>Facilitator:</th>
                        <td>{{ $seminar->facilitator }}</td>
                    </tr>
                    @endif
                    <tr>
                        <th>Capacity:</th>
                        <td>
                            @if($seminar->capacity)
                            {{ $seminar->current_participants }} / {{ $seminar->capacity }} participants
                            <div class="progress mt-2" style="height: 20px;">
                                @php
                                    $percentage = $seminar->capacity > 0 ? ($seminar->current_participants / $seminar->capacity) * 100 : 0;
                                    $colorClass = $percentage >= 90 ? 'bg-danger' : ($percentage >= 70 ? 'bg-warning' : 'bg-success');
                                @endphp
                                <div class="progress-bar {{ $colorClass }}" role="progressbar" style="width: {{ $percentage }}%">
                                    {{ round($percentage, 1) }}%
                                </div>
                            </div>
                            @else
                            Unlimited capacity ({{ $seminar->current_participants }} registered)
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Regular Fee:</th>
                        <td><strong>RM {{ number_format($seminar->regular_fee, 2) }}</strong></td>
                    </tr>
                    @if($seminar->early_bird_fee)
                    <tr>
                        <th>Early Bird Fee:</th>
                        <td>
                            <strong class="text-success">RM {{ number_format($seminar->early_bird_fee, 2) }}</strong>
                            @if($seminar->early_bird_deadline)
                            <br><small class="text-muted">Until {{ $seminar->early_bird_deadline->format('d M Y') }}</small>
                            @endif
                        </td>
                    </tr>
                    @endif
                    @if($seminar->registration_deadline)
                    <tr>
                        <th>Registration Deadline:</th>
                        <td>{{ $seminar->registration_deadline->format('d M Y') }}</td>
                    </tr>
                    @endif
                    @if($seminar->description)
                    <tr>
                        <th>Description:</th>
                        <td>{{ $seminar->description }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Participant Statistics</h5>
            </div>
            <div class="card-body">
                <div class="stat-item mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">Total Participants</span>
                        <strong class="fs-4">{{ $participantStats['total'] }}</strong>
                    </div>
                </div>
                <hr>
                <div class="stat-item mb-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted"><i class="fas fa-check-circle text-success"></i> Paid</span>
                        <strong>{{ $participantStats['paid'] }}</strong>
                    </div>
                </div>
                <div class="stat-item mb-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted"><i class="fas fa-clock text-warning"></i> Pending</span>
                        <strong>{{ $participantStats['pending'] }}</strong>
                    </div>
                </div>
                <div class="stat-item mb-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted"><i class="fas fa-user-check text-info"></i> Attended</span>
                        <strong>{{ $participantStats['attended'] }}</strong>
                    </div>
                </div>
                <hr>
                <div class="stat-item">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">Total Revenue</span>
                        <strong class="text-success">RM {{ number_format($participantStats['total_revenue'], 2) }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                @can('manage-seminar-participants')
                <button type="button" class="btn btn-primary btn-sm w-100 mb-2" data-bs-toggle="modal" data-bs-target="#bulkNotificationModal">
                    <i class="fas fa-paper-plane"></i> Send Bulk Notification
                </button>
                @endcan
                <a href="{{ route('public.seminars.show', $seminar) }}" class="btn btn-info btn-sm w-100 mb-2" target="_blank">
                    <i class="fas fa-external-link-alt"></i> View Public Page
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Participant List -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Participant List</h5>
    </div>
    <div class="card-body">
        @if($seminar->participants->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Name</th>
                        <th>Contact</th>
                        <th>School/Grade</th>
                        <th>Registration Date</th>
                        <th>Fee</th>
                        <th>Payment Status</th>
                        <th>Attendance</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($seminar->participants as $index => $participant)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <div class="fw-semibold">{{ $participant->name }}</div>
                            @if($participant->student_id)
                            <small class="badge bg-info">Registered Student</small>
                            @endif
                        </td>
                        <td>
                            <div><i class="fas fa-envelope"></i> {{ $participant->email }}</div>
                            <div><i class="fas fa-phone"></i> {{ $participant->phone }}</div>
                        </td>
                        <td>
                            @if($participant->school)
                            <div>{{ $participant->school }}</div>
                            @endif
                            @if($participant->grade)
                            <small class="text-muted">Grade: {{ $participant->grade }}</small>
                            @endif
                        </td>
                        <td>{{ $participant->registration_date->format('d/m/Y H:i') }}</td>
                        <td>RM {{ number_format($participant->fee_amount, 2) }}</td>
                        <td>
                            <select class="form-select form-select-sm payment-status-select" 
                                    data-participant-id="{{ $participant->id }}"
                                    @cannot('manage-seminar-participants') disabled @endcannot>
                                <option value="pending" {{ $participant->payment_status == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="paid" {{ $participant->payment_status == 'paid' ? 'selected' : '' }}>Paid</option>
                                <option value="refunded" {{ $participant->payment_status == 'refunded' ? 'selected' : '' }}>Refunded</option>
                            </select>
                            @if($participant->payment_method)
                            <small class="text-muted">via {{ ucfirst($participant->payment_method) }}</small>
                            @endif
                        </td>
                        <td>
                            <select class="form-select form-select-sm attendance-select" 
                                    data-participant-id="{{ $participant->id }}"
                                    @cannot('manage-seminar-participants') disabled @endcannot>
                                <option value="" {{ !$participant->attendance_status ? 'selected' : '' }}>Not Marked</option>
                                <option value="attended" {{ $participant->attendance_status == 'attended' ? 'selected' : '' }}>Attended</option>
                                <option value="absent" {{ $participant->attendance_status == 'absent' ? 'selected' : '' }}>Absent</option>
                                <option value="no_show" {{ $participant->attendance_status == 'no_show' ? 'selected' : '' }}>No Show</option>
                            </select>
                        </td>
                        <td>
                            @if($participant->notes)
                            <button type="button" class="btn btn-sm btn-info" title="View Notes" data-bs-toggle="tooltip">
                                <i class="fas fa-sticky-note"></i>
                            </button>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-5">
            <i class="fas fa-users-slash fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">No participants yet</h5>
            <p class="text-muted">Participants will appear here once they register.</p>
        </div>
        @endif
    </div>
</div>

<!-- Bulk Notification Modal -->
<div class="modal fade" id="bulkNotificationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Send Bulk Notification</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Message</label>
                    <textarea id="bulkMessage" class="form-control" rows="4" placeholder="Enter your message..."></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Send Via:</label>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="channelEmail" value="email" checked>
                        <label class="form-check-label" for="channelEmail">Email</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="channelWhatsapp" value="whatsapp" checked>
                        <label class="form-check-label" for="channelWhatsapp">WhatsApp</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="channelSms" value="sms">
                        <label class="form-check-label" for="channelSms">SMS</label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="sendBulkNotification">
                    <i class="fas fa-paper-plane"></i> Send Notification
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Payment status change
    $('.payment-status-select').change(function() {
        const participantId = $(this).data('participant-id');
        const newStatus = $(this).val();
        
        if(confirm('Update payment status to: ' + newStatus + '?')) {
            $.ajax({
                url: `/admin/seminars/{{ $seminar->id }}/participants/${participantId}/payment-status`,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    payment_status: newStatus,
                    payment_method: 'cash',
                    payment_date: new Date().toISOString().split('T')[0]
                },
                success: function(response) {
                    if(response.success) {
                        showAlert('success', response.message);
                        setTimeout(() => location.reload(), 1000);
                    }
                },
                error: function(xhr) {
                    showAlert('error', 'Failed to update payment status');
                    location.reload();
                }
            });
        } else {
            location.reload();
        }
    });

    // Attendance change
    $('.attendance-select').change(function() {
        const participantId = $(this).data('participant-id');
        const newStatus = $(this).val();
        
        if(confirm('Mark attendance as: ' + (newStatus || 'Not Marked') + '?')) {
            $.ajax({
                url: `/admin/seminars/{{ $seminar->id }}/participants/${participantId}/attendance`,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    attendance_status: newStatus
                },
                success: function(response) {
                    if(response.success) {
                        showAlert('success', response.message);
                    }
                },
                error: function(xhr) {
                    showAlert('error', 'Failed to mark attendance');
                    location.reload();
                }
            });
        } else {
            location.reload();
        }
    });

    // Bulk notification
    $('#sendBulkNotification').click(function() {
        const message = $('#bulkMessage').val();
        const channels = [];
        
        if($('#channelEmail').is(':checked')) channels.push('email');
        if($('#channelWhatsapp').is(':checked')) channels.push('whatsapp');
        if($('#channelSms').is(':checked')) channels.push('sms');
        
        if(!message) {
            alert('Please enter a message');
            return;
        }
        
        if(channels.length === 0) {
            alert('Please select at least one channel');
            return;
        }
        
        if(confirm('Send notification to all participants?')) {
            $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Sending...');
            
            $.ajax({
                url: `/admin/seminars/{{ $seminar->id }}/bulk-notification`,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    message: message,
                    channels: channels
                },
                success: function(response) {
                    if(response.success) {
                        showAlert('success', response.message);
                        $('#bulkNotificationModal').modal('hide');
                        $('#bulkMessage').val('');
                    }
                },
                error: function(xhr) {
                    showAlert('error', 'Failed to send notifications');
                },
                complete: function() {
                    $('#sendBulkNotification').prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Send Notification');
                }
            });
        }
    });

    function showAlert(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const alert = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                <i class="fas fa-${type === 'success' ? 'check' : 'exclamation'}-circle me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        $('.page-header').after(alert);
        setTimeout(() => $('.alert').fadeOut(), 3000);
    }

    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
});
</script>
@endpush
