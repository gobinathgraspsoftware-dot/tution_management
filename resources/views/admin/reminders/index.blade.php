@extends('layouts.app')

@section('title', 'Payment Reminders')
@section('page-title', 'Payment Reminders')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="fas fa-bell me-2"></i> Payment Reminders</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Reminders</li>
            </ol>
        </nav>
    </div>
    <div>
        <a href="{{ route('admin.reminders.settings') }}" class="btn btn-outline-secondary me-2">
            <i class="fas fa-cog me-1"></i> Settings
        </a>
        <a href="{{ route('admin.reminders.upcoming') }}" class="btn btn-outline-info me-2">
            <i class="fas fa-calendar-alt me-1"></i> Upcoming
        </a>
        <a href="{{ route('admin.reminders.logs') }}" class="btn btn-outline-primary">
            <i class="fas fa-history me-1"></i> Logs
        </a>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-white-50">Scheduled</h6>
                        <h2 class="mb-0">{{ $statistics['total_scheduled'] ?? 0 }}</h2>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-calendar-check fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-white-50">Sent This Month</h6>
                        <h2 class="mb-0">{{ $statistics['total_sent'] ?? 0 }}</h2>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-paper-plane fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-danger text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-white-50">Failed</h6>
                        <h2 class="mb-0">{{ $statistics['total_failed'] ?? 0 }}</h2>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-exclamation-triangle fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-warning text-dark">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="opacity-75">Due Today</h6>
                        <h2 class="mb-0">{{ $todayReminders ?? 0 }}</h2>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-clock fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-8">
                <h6 class="mb-3">Quick Actions</h6>
                <form action="{{ route('admin.reminders.schedule-monthly') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-calendar-plus me-1"></i> Schedule This Month
                    </button>
                </form>
                <form action="{{ route('admin.reminders.send-now') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success me-2">
                        <i class="fas fa-play me-1"></i> Send Due Reminders
                    </button>
                </form>
                <form action="{{ route('admin.reminders.send-overdue') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-danger me-2">
                        <i class="fas fa-bell me-1"></i> Send Overdue Reminders
                    </button>
                </form>
                <form action="{{ route('admin.reminders.retry-failed') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-redo me-1"></i> Retry Failed
                    </button>
                </form>
            </div>
            <div class="col-md-4 text-end">
                <small class="text-muted">Reminder Days: 10th, 18th, 24th of each month</small>
            </div>
        </div>
    </div>
</div>

<!-- By Type Stats -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="fas fa-chart-pie me-2"></i> By Reminder Type</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-3">
                        <div class="p-2 bg-light rounded">
                            <h4 class="mb-0 text-primary">{{ $statistics['by_type']['first'] ?? 0 }}</h4>
                            <small class="text-muted">First (10th)</small>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="p-2 bg-light rounded">
                            <h4 class="mb-0 text-warning">{{ $statistics['by_type']['second'] ?? 0 }}</h4>
                            <small class="text-muted">Second (18th)</small>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="p-2 bg-light rounded">
                            <h4 class="mb-0 text-danger">{{ $statistics['by_type']['final'] ?? 0 }}</h4>
                            <small class="text-muted">Final (24th)</small>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="p-2 bg-light rounded">
                            <h4 class="mb-0 text-dark">{{ $statistics['by_type']['overdue'] ?? 0 }}</h4>
                            <small class="text-muted">Overdue</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="fas fa-broadcast-tower me-2"></i> By Channel</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-4">
                        <div class="p-2 bg-light rounded">
                            <h4 class="mb-0 text-success">{{ $statistics['by_channel']['whatsapp'] ?? 0 }}</h4>
                            <small class="text-muted"><i class="fab fa-whatsapp me-1"></i> WhatsApp</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2 bg-light rounded">
                            <h4 class="mb-0 text-primary">{{ $statistics['by_channel']['email'] ?? 0 }}</h4>
                            <small class="text-muted"><i class="fas fa-envelope me-1"></i> Email</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2 bg-light rounded">
                            <h4 class="mb-0 text-info">{{ $statistics['by_channel']['sms'] ?? 0 }}</h4>
                            <small class="text-muted"><i class="fas fa-sms me-1"></i> SMS</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('admin.reminders.index') }}" method="GET" class="row g-3">
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Sent</option>
                    <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Delivered</option>
                    <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Type</label>
                <select name="type" class="form-select">
                    <option value="">All Types</option>
                    <option value="first" {{ request('type') == 'first' ? 'selected' : '' }}>First (10th)</option>
                    <option value="second" {{ request('type') == 'second' ? 'selected' : '' }}>Second (18th)</option>
                    <option value="final" {{ request('type') == 'final' ? 'selected' : '' }}>Final (24th)</option>
                    <option value="overdue" {{ request('type') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                    <option value="follow_up" {{ request('type') == 'follow_up' ? 'selected' : '' }}>Follow-up</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Channel</label>
                <select name="channel" class="form-select">
                    <option value="">All Channels</option>
                    <option value="whatsapp" {{ request('channel') == 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                    <option value="email" {{ request('channel') == 'email' ? 'selected' : '' }}>Email</option>
                    <option value="sms" {{ request('channel') == 'sms' ? 'selected' : '' }}>SMS</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">From Date</label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">To Date</label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-2 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i>
                </button>
                <a href="{{ route('admin.reminders.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Reminders Table -->
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-list me-2"></i> Reminders</h5>
        <form action="{{ route('admin.reminders.bulk-cancel') }}" method="POST" id="bulkCancelForm">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-danger" id="cancelBtn" disabled>
                <i class="fas fa-ban me-1"></i> Cancel Selected (<span id="selectedCount">0</span>)
            </button>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="40">
                            <input type="checkbox" class="form-check-input" id="checkAll" onchange="toggleAll()">
                        </th>
                        <th>Invoice</th>
                        <th>Student</th>
                        <th>Type</th>
                        <th>Channel</th>
                        <th>Scheduled</th>
                        <th>Sent At</th>
                        <th>Status</th>
                        <th>Attempts</th>
                        <th width="120">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reminders as $reminder)
                        <tr>
                            <td>
                                @if(in_array($reminder->status, ['scheduled', 'pending']))
                                <input type="checkbox" class="form-check-input reminder-check"
                                       name="reminder_ids[]"
                                       value="{{ $reminder->id }}"
                                       form="bulkCancelForm"
                                       onchange="updateCount()">
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.invoices.show', $reminder->invoice) }}" class="fw-bold">
                                    {{ $reminder->invoice->invoice_number ?? 'N/A' }}
                                </a>
                            </td>
                            <td>
                                {{ $reminder->invoice->student->user->name ?? $reminder->student->user->name ?? 'N/A' }}
                            </td>
                            <td>
                                @switch($reminder->reminder_type)
                                    @case('first')
                                        <span class="badge bg-primary">1st (10th)</span>
                                        @break
                                    @case('second')
                                        <span class="badge bg-warning text-dark">2nd (18th)</span>
                                        @break
                                    @case('final')
                                        <span class="badge bg-danger">Final (24th)</span>
                                        @break
                                    @case('overdue')
                                        <span class="badge bg-dark">Overdue</span>
                                        @break
                                    @default
                                        <span class="badge bg-secondary">{{ ucfirst($reminder->reminder_type) }}</span>
                                @endswitch
                            </td>
                            <td>
                                @if($reminder->channel == 'whatsapp')
                                    <i class="fab fa-whatsapp text-success"></i>
                                @elseif($reminder->channel == 'email')
                                    <i class="fas fa-envelope text-primary"></i>
                                @else
                                    <i class="fas fa-sms text-info"></i>
                                @endif
                                {{ ucfirst($reminder->channel) }}
                            </td>
                            <td>{{ $reminder->scheduled_date->format('d M Y') }}</td>
                            <td>{{ $reminder->sent_at ? $reminder->sent_at->format('d M Y H:i') : '-' }}</td>
                            <td>
                                @switch($reminder->status)
                                    @case('scheduled')
                                        <span class="badge bg-info">Scheduled</span>
                                        @break
                                    @case('pending')
                                        <span class="badge bg-warning text-dark">Pending</span>
                                        @break
                                    @case('sent')
                                    @case('delivered')
                                        <span class="badge bg-success">{{ ucfirst($reminder->status) }}</span>
                                        @break
                                    @case('failed')
                                        <span class="badge bg-danger">Failed</span>
                                        @if($reminder->error_message)
                                            <br><small class="text-danger">{{ Str::limit($reminder->error_message, 30) }}</small>
                                        @endif
                                        @break
                                    @case('cancelled')
                                        <span class="badge bg-secondary">Cancelled</span>
                                        @break
                                    @default
                                        <span class="badge bg-secondary">{{ ucfirst($reminder->status) }}</span>
                                @endswitch
                            </td>
                            <td>{{ $reminder->attempts ?? 0 }}/{{ $reminder->max_attempts ?? 3 }}</td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.reminders.show', $reminder) }}" class="btn btn-outline-info" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if(in_array($reminder->status, ['scheduled', 'pending']))
                                        <form action="{{ route('admin.reminders.cancel', $reminder) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Cancel"
                                                    onclick="return confirm('Cancel this reminder?')">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        </form>
                                    @elseif($reminder->status == 'failed')
                                        <form action="{{ route('admin.reminders.resend', $reminder) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-warning" title="Resend">
                                                <i class="fas fa-redo"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-4">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No reminders found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($reminders->hasPages())
        <div class="p-3 border-top">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted">
                    Showing {{ $reminders->firstItem() ?? 0 }} to {{ $reminders->lastItem() ?? 0 }} of {{ $reminders->total() }} reminders
                </div>
                {{ $reminders->links() }}
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleAll() {
    const checkboxes = document.querySelectorAll('.reminder-check');
    const checkAll = document.getElementById('checkAll');
    checkboxes.forEach(cb => cb.checked = checkAll.checked);
    updateCount();
}

function updateCount() {
    const checked = document.querySelectorAll('.reminder-check:checked').length;
    document.getElementById('selectedCount').textContent = checked;
    document.getElementById('cancelBtn').disabled = checked === 0;
}
</script>
@endpush
