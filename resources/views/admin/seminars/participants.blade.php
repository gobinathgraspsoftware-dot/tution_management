@extends('layouts.app')

@section('title', 'Participant Management')
@section('page-title', 'Participant Management')

@section('content')
<div class="page-header">
    <h1>
        <i class="fas fa-users me-2"></i> Participant Management
    </h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.seminars.index') }}">Seminars</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.seminars.show', $seminar) }}">{{ $seminar->name }}</a></li>
            <li class="breadcrumb-item active">Participants</li>
        </ol>
    </nav>
</div>

<!-- Seminar Info Card -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h4 class="mb-2">{{ $seminar->name }}</h4>
                <p class="mb-1"><i class="fas fa-calendar"></i> {{ $seminar->date->format('l, d F Y') }}</p>
                <p class="mb-1"><i class="fas fa-clock"></i> 
                    @if($seminar->start_time)
                    {{ \Carbon\Carbon::parse($seminar->start_time)->format('h:i A') }}
                    @else
                    Time TBA
                    @endif
                </p>
                <p class="mb-0">
                    <i class="fas fa-{{ $seminar->is_online ? 'video' : 'map-marker-alt' }}"></i> 
                    {{ $seminar->is_online ? 'Online Seminar' : $seminar->venue }}
                </p>
            </div>
            <div class="col-md-4 text-end">
                <div class="mb-2">
                    <span class="badge bg-primary fs-5">{{ $participants->total() }} Participants</span>
                </div>
                @can('export-seminar-participants')
                <a href="{{ route('admin.seminars.export-participants', $seminar) }}" class="btn btn-success">
                    <i class="fas fa-download"></i> Export to Excel
                </a>
                @endcan
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-users fa-2x text-primary mb-2"></i>
                <h3 class="mb-0">{{ $participants->total() }}</h3>
                <p class="text-muted mb-0">Total Participants</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                <h3 class="mb-0">{{ $participants->where('payment_status', 'paid')->count() }}</h3>
                <p class="text-muted mb-0">Paid</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-clock fa-2x text-warning mb-2"></i>
                <h3 class="mb-0">{{ $participants->where('payment_status', 'pending')->count() }}</h3>
                <p class="text-muted mb-0">Pending Payment</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-user-check fa-2x text-info mb-2"></i>
                <h3 class="mb-0">{{ $participants->where('attendance_status', 'attended')->count() }}</h3>
                <p class="text-muted mb-0">Attended</p>
            </div>
        </div>
    </div>
</div>

<!-- Action Buttons -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <a href="{{ route('admin.seminars.show', $seminar) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Seminar Details
        </a>
    </div>
    <div>
        @can('manage-seminar-participants')
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#bulkActionModal">
            <i class="fas fa-tasks"></i> Bulk Actions
        </button>
        <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#bulkNotificationModal">
            <i class="fas fa-paper-plane"></i> Send Notification
        </button>
        @endcan
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.seminars.participants', $seminar) }}" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Search by name, email, phone..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Payment Status</label>
                <select name="payment_status" class="form-select">
                    <option value="">All</option>
                    <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="refunded" {{ request('payment_status') == 'refunded' ? 'selected' : '' }}>Refunded</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Attendance</label>
                <select name="attendance_status" class="form-select">
                    <option value="">All</option>
                    <option value="attended" {{ request('attendance_status') == 'attended' ? 'selected' : '' }}>Attended</option>
                    <option value="absent" {{ request('attendance_status') == 'absent' ? 'selected' : '' }}>Absent</option>
                    <option value="no_show" {{ request('attendance_status') == 'no_show' ? 'selected' : '' }}>No Show</option>
                    <option value="not_marked" {{ request('attendance_status') == 'not_marked' ? 'selected' : '' }}>Not Marked</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Student Type</label>
                <select name="student_type" class="form-select">
                    <option value="">All</option>
                    <option value="registered" {{ request('student_type') == 'registered' ? 'selected' : '' }}>Registered Students</option>
                    <option value="public" {{ request('student_type') == 'public' ? 'selected' : '' }}>Public</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Sort By</label>
                <select name="sort" class="form-select">
                    <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Latest First</option>
                    <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Oldest First</option>
                    <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>Name A-Z</option>
                </select>
            </div>
            <div class="col-md-1">
                <label class="form-label">&nbsp;</label>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Participants Table -->
<div class="card">
    <div class="card-body">
        @if($participants->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th width="40">
                            <input type="checkbox" id="selectAll" class="form-check-input">
                        </th>
                        <th>No</th>
                        <th>Participant Info</th>
                        <th>Contact</th>
                        <th>School/Grade</th>
                        <th>Registration Date</th>
                        <th>Fee Amount</th>
                        <th>Payment Status</th>
                        <th>Payment Method</th>
                        <th>Attendance</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($participants as $index => $participant)
                    <tr>
                        <td>
                            <input type="checkbox" class="form-check-input participant-checkbox" value="{{ $participant->id }}">
                        </td>
                        <td>{{ $participants->firstItem() + $index }}</td>
                        <td>
                            <div class="fw-semibold">{{ $participant->name }}</div>
                            @if($participant->student_id)
                            <span class="badge bg-info">
                                <i class="fas fa-user-graduate"></i> Registered Student
                            </span>
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
                            @if(!$participant->school && !$participant->grade)
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <div>{{ $participant->registration_date->format('d M Y') }}</div>
                            <small class="text-muted">{{ $participant->registration_date->format('h:i A') }}</small>
                        </td>
                        <td>
                            <strong>RM {{ number_format($participant->fee_amount, 2) }}</strong>
                        </td>
                        <td>
                            <select class="form-select form-select-sm payment-status-select" 
                                    data-participant-id="{{ $participant->id }}"
                                    @cannot('manage-seminar-participants') disabled @endcannot>
                                <option value="pending" {{ $participant->payment_status == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="paid" {{ $participant->payment_status == 'paid' ? 'selected' : '' }}>Paid</option>
                                <option value="refunded" {{ $participant->payment_status == 'refunded' ? 'selected' : '' }}>Refunded</option>
                            </select>
                            @if($participant->payment_date)
                            <small class="text-muted">{{ $participant->payment_date->format('d M Y') }}</small>
                            @endif
                        </td>
                        <td>
                            @if($participant->payment_method)
                            <span class="badge bg-secondary">{{ ucfirst($participant->payment_method) }}</span>
                            @else
                            <span class="text-muted">-</span>
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
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-sm btn-info view-details-btn" 
                                        data-participant-id="{{ $participant->id }}"
                                        data-bs-toggle="modal" data-bs-target="#detailsModal"
                                        title="View Details">
                                    <i class="fas fa-eye"></i>
                                </button>
                                @if($participant->notes)
                                <button type="button" class="btn btn-sm btn-warning view-notes-btn" 
                                        data-notes="{{ $participant->notes }}"
                                        title="View Notes">
                                    <i class="fas fa-sticky-note"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <td colspan="6" class="text-end"><strong>Total:</strong></td>
                        <td><strong>RM {{ number_format($participants->sum('fee_amount'), 2) }}</strong></td>
                        <td colspan="4"></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-3">
            {{ $participants->links() }}
        </div>
        @else
        <div class="text-center py-5">
            <i class="fas fa-users-slash fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">No participants found</h5>
            @if(request()->hasAny(['search', 'payment_status', 'attendance_status', 'student_type']))
            <p class="text-muted">Try adjusting your filters.</p>
            @else
            <p class="text-muted">Participants will appear here once they register.</p>
            @endif
        </div>
        @endif
    </div>
</div>

<!-- Bulk Action Modal -->
<div class="modal fade" id="bulkActionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bulk Actions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="selectedCount">0 participants selected</p>
                <div class="mb-3">
                    <label class="form-label">Action</label>
                    <select id="bulkAction" class="form-select">
                        <option value="">Select Action</option>
                        <option value="mark_attended">Mark as Attended</option>
                        <option value="mark_absent">Mark as Absent</option>
                        <option value="mark_paid">Mark as Paid</option>
                        <option value="mark_pending">Mark Payment as Pending</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="executeBulkAction">
                    <i class="fas fa-check"></i> Execute Action
                </button>
            </div>
        </div>
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
                    <label class="form-label">Send to:</label>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="sendTo" id="sendToAll" value="all" checked>
                        <label class="form-check-label" for="sendToAll">
                            All Participants ({{ $participants->total() }})
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="sendTo" id="sendToSelected" value="selected">
                        <label class="form-check-label" for="sendToSelected">
                            Selected Only (<span id="selectedCountNotif">0</span>)
                        </label>
                    </div>
                </div>
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

<!-- Participant Details Modal -->
<div class="modal fade" id="detailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Participant Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="participantDetails">
                <!-- Details will be loaded here -->
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Select All checkbox
    $('#selectAll').change(function() {
        $('.participant-checkbox').prop('checked', $(this).is(':checked'));
        updateSelectedCount();
    });

    // Individual checkbox
    $('.participant-checkbox').change(function() {
        updateSelectedCount();
    });

    function updateSelectedCount() {
        const count = $('.participant-checkbox:checked').length;
        $('#selectedCount').text(count + ' participants selected');
        $('#selectedCountNotif').text(count);
    }

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

    // View notes
    $('.view-notes-btn').click(function() {
        const notes = $(this).data('notes');
        alert('Notes: ' + notes);
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
});
</script>
@endpush
