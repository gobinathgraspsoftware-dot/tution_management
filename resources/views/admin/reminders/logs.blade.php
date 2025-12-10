@extends('layouts.app')

@section('title', 'Reminder Logs')
@section('page-title', 'Reminder Logs')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="fas fa-history me-2"></i> Reminder Logs</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.reminders.index') }}">Reminders</a></li>
                <li class="breadcrumb-item active">Logs</li>
            </ol>
        </nav>
    </div>
    <div>
        <a href="{{ route('admin.reminders.index') }}" class="btn btn-outline-secondary me-2">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
        <a href="{{ route('admin.reminders.export') }}?{{ http_build_query(request()->all()) }}" class="btn btn-outline-success">
            <i class="fas fa-download me-1"></i> Export
        </a>
    </div>
</div>

<!-- Statistics -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-success text-white">
            <div class="card-body text-center">
                <h3 class="mb-0">{{ $stats['total_sent'] ?? 0 }}</h3>
                <small>Total Sent</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-info text-white">
            <div class="card-body text-center">
                <h3 class="mb-0">{{ $stats['total_delivered'] ?? 0 }}</h3>
                <small>Delivered</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-danger text-white">
            <div class="card-body text-center">
                <h3 class="mb-0">{{ $stats['total_failed'] ?? 0 }}</h3>
                <small>Failed</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-primary text-white">
            <div class="card-body text-center">
                <h3 class="mb-0">{{ $stats['success_rate'] ?? 0 }}%</h3>
                <small>Success Rate</small>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('admin.reminders.logs') }}" method="GET" class="row g-3">
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Sent</option>
                    <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Delivered</option>
                    <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
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
            <div class="col-md-4 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search me-1"></i> Filter
                </button>
                <a href="{{ route('admin.reminders.logs') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-1"></i> Clear
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Logs Table -->
<div class="card">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="fas fa-list me-2"></i> Delivery Logs</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Sent At</th>
                        <th>Invoice</th>
                        <th>Student</th>
                        <th>Type</th>
                        <th>Channel</th>
                        <th>Recipient</th>
                        <th>Status</th>
                        <th>Attempts</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr class="{{ $log->status == 'failed' ? 'table-danger' : '' }}">
                            <td>
                                {{ $log->sent_at ? $log->sent_at->format('d M Y') : '-' }}
                                @if($log->sent_at)
                                    <br><small class="text-muted">{{ $log->sent_at->format('H:i:s') }}</small>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.invoices.show', $log->invoice) }}" class="fw-bold">
                                    {{ $log->invoice->invoice_number ?? 'N/A' }}
                                </a>
                            </td>
                            <td>{{ $log->invoice->student->user->name ?? 'N/A' }}</td>
                            <td>
                                <span class="badge bg-secondary">{{ ucfirst($log->reminder_type) }}</span>
                            </td>
                            <td>
                                @if($log->channel == 'whatsapp')
                                    <i class="fab fa-whatsapp text-success me-1"></i>
                                @elseif($log->channel == 'email')
                                    <i class="fas fa-envelope text-primary me-1"></i>
                                @else
                                    <i class="fas fa-sms text-info me-1"></i>
                                @endif
                                {{ ucfirst($log->channel) }}
                            </td>
                            <td>
                                @if($log->channel == 'whatsapp' || $log->channel == 'sms')
                                    {{ $log->recipient_phone ?? '-' }}
                                @else
                                    {{ $log->recipient_email ?? '-' }}
                                @endif
                            </td>
                            <td>
                                @switch($log->status)
                                    @case('sent')
                                        <span class="badge bg-success">Sent</span>
                                        @break
                                    @case('delivered')
                                        <span class="badge bg-info">Delivered</span>
                                        @break
                                    @case('failed')
                                        <span class="badge bg-danger">Failed</span>
                                        @break
                                    @default
                                        <span class="badge bg-secondary">{{ ucfirst($log->status) }}</span>
                                @endswitch
                            </td>
                            <td>{{ $log->attempts ?? 1 }}</td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-info"
                                        data-bs-toggle="modal"
                                        data-bs-target="#detailModal"
                                        data-message="{{ $log->message_content ?? 'No message content' }}"
                                        data-response="{{ $log->response ?? 'No response' }}"
                                        data-error="{{ $log->error_message ?? '' }}">
                                    <i class="fas fa-info-circle"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No logs found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
        <div class="p-3 border-top">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted">
                    Showing {{ $logs->firstItem() ?? 0 }} to {{ $logs->lastItem() ?? 0 }} of {{ $logs->total() }} logs
                </div>
                {{ $logs->links() }}
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Detail Modal -->
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-info-circle me-2"></i> Reminder Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">Message Content</label>
                    <pre class="bg-light p-3 rounded" id="modalMessage" style="white-space: pre-wrap;"></pre>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">API Response</label>
                    <pre class="bg-light p-3 rounded" id="modalResponse" style="white-space: pre-wrap;"></pre>
                </div>
                <div id="errorSection" style="display: none;">
                    <label class="form-label fw-bold text-danger">Error Message</label>
                    <pre class="bg-danger text-white p-3 rounded" id="modalError" style="white-space: pre-wrap;"></pre>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('detailModal').addEventListener('show.bs.modal', function(event) {
    const button = event.relatedTarget;
    const message = button.getAttribute('data-message');
    const response = button.getAttribute('data-response');
    const error = button.getAttribute('data-error');

    document.getElementById('modalMessage').textContent = message;
    document.getElementById('modalResponse').textContent = response;

    const errorSection = document.getElementById('errorSection');
    if (error) {
        errorSection.style.display = 'block';
        document.getElementById('modalError').textContent = error;
    } else {
        errorSection.style.display = 'none';
    }
});
</script>
@endpush
