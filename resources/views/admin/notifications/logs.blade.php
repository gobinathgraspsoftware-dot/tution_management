@extends('layouts.app')

@section('title', 'Notification Logs')
@section('page-title', 'Notification Logs')

@section('content')
<div class="page-header">
    <h1><i class="fas fa-list me-2"></i> Notification Logs</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.notifications.index') }}">Notifications</a></li>
            <li class="breadcrumb-item active">Logs</li>
        </ol>
    </nav>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form action="{{ route('admin.notifications.logs') }}" method="GET">
            <div class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">Channel</label>
                    <select name="channel" class="form-select">
                        <option value="">All Channels</option>
                        @foreach($channels as $channel)
                            <option value="{{ $channel }}" {{ request('channel') == $channel ? 'selected' : '' }}>
                                {{ ucfirst($channel) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                                {{ ucfirst($status) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Type</label>
                    <select name="type" class="form-select">
                        <option value="">All Types</option>
                        @foreach($types as $type)
                            <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $type)) }}
                            </option>
                        @endforeach
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
                <div class="col-md-2">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Search..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="mt-3">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search me-1"></i> Filter
                </button>
                <a href="{{ route('admin.notifications.logs') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-1"></i> Clear
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Logs Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Date/Time</th>
                        <th>Channel</th>
                        <th>Recipient</th>
                        <th>Type</th>
                        <th>Subject/Message</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr>
                        <td>#{{ $log->id }}</td>
                        <td>
                            <div>{{ $log->created_at->format('d M Y') }}</div>
                            <small class="text-muted">{{ $log->created_at->format('H:i:s') }}</small>
                        </td>
                        <td>
                            @if($log->channel === 'whatsapp')
                                <span class="badge bg-success"><i class="fab fa-whatsapp me-1"></i>WhatsApp</span>
                            @elseif($log->channel === 'email')
                                <span class="badge bg-info"><i class="fas fa-envelope me-1"></i>Email</span>
                            @elseif($log->channel === 'sms')
                                <span class="badge bg-primary"><i class="fas fa-sms me-1"></i>SMS</span>
                            @else
                                <span class="badge bg-secondary">{{ $log->channel }}</span>
                            @endif
                        </td>
                        <td>
                            <div>{{ Str::limit($log->recipient, 30) }}</div>
                            @if($log->user)
                                <small class="text-muted">{{ $log->user->name }}</small>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $log->type)) }}</span>
                        </td>
                        <td>
                            @if($log->subject)
                                <strong>{{ Str::limit($log->subject, 30) }}</strong><br>
                            @endif
                            <small class="text-muted">{{ Str::limit($log->message, 50) }}</small>
                        </td>
                        <td>
                            @if($log->status === 'sent')
                                <span class="badge bg-success">Sent</span>
                                @if($log->sent_at)
                                    <br><small class="text-muted">{{ $log->sent_at->format('H:i') }}</small>
                                @endif
                            @elseif($log->status === 'delivered')
                                <span class="badge bg-info">Delivered</span>
                                @if($log->delivered_at)
                                    <br><small class="text-muted">{{ $log->delivered_at->format('H:i') }}</small>
                                @endif
                            @elseif($log->status === 'pending')
                                <span class="badge bg-warning">Pending</span>
                            @elseif($log->status === 'failed')
                                <span class="badge bg-danger">Failed</span>
                                @if($log->error_message)
                                    <br><small class="text-danger" title="{{ $log->error_message }}">{{ Str::limit($log->error_message, 20) }}</small>
                                @endif
                            @endif
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#logModal{{ $log->id }}">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>

                    <!-- Detail Modal -->
                    <div class="modal fade" id="logModal{{ $log->id }}" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Notification Details #{{ $log->id }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>Channel:</strong> {{ ucfirst($log->channel) }}</p>
                                            <p><strong>Recipient:</strong> {{ $log->recipient }}</p>
                                            <p><strong>User:</strong> {{ $log->user?->name ?? 'N/A' }}</p>
                                            <p><strong>Type:</strong> {{ ucfirst(str_replace('_', ' ', $log->type)) }}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Status:</strong> {{ ucfirst($log->status) }}</p>
                                            <p><strong>Created:</strong> {{ $log->created_at->format('d M Y H:i:s') }}</p>
                                            @if($log->sent_at)
                                                <p><strong>Sent:</strong> {{ $log->sent_at->format('d M Y H:i:s') }}</p>
                                            @endif
                                            @if($log->delivered_at)
                                                <p><strong>Delivered:</strong> {{ $log->delivered_at->format('d M Y H:i:s') }}</p>
                                            @endif
                                        </div>
                                    </div>

                                    @if($log->subject)
                                        <hr>
                                        <p><strong>Subject:</strong></p>
                                        <p>{{ $log->subject }}</p>
                                    @endif

                                    <hr>
                                    <p><strong>Message:</strong></p>
                                    <div class="bg-light p-3 rounded" style="white-space: pre-wrap;">{{ $log->message }}</div>

                                    @if($log->error_message)
                                        <hr>
                                        <p><strong>Error:</strong></p>
                                        <div class="alert alert-danger">{{ $log->error_message }}</div>
                                    @endif

                                    @if($log->response)
                                        <hr>
                                        <p><strong>Response:</strong></p>
                                        <pre class="bg-light p-3 rounded">{{ $log->response }}</pre>
                                    @endif
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No notification logs found</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($logs->hasPages())
    <div class="card-footer">
        {{ $logs->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection
