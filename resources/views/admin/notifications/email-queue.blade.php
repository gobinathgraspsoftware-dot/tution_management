@extends('layouts.app')

@section('title', 'Email Queue')
@section('page-title', 'Email Queue')

@section('content')
<div class="page-header">
    <h1><i class="fas fa-envelope me-2"></i> Email Queue</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.notifications.index') }}">Notifications</a></li>
            <li class="breadcrumb-item active">Email Queue</li>
        </ol>
    </nav>
</div>

<!-- Stats -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h3 class="mb-0 text-warning">{{ $stats['pending'] }}</h3>
                <small class="text-muted">Pending</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h3 class="mb-0 text-success">{{ $stats['sent'] }}</h3>
                <small class="text-muted">Sent Today</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h3 class="mb-0 text-danger">{{ $stats['failed'] }}</h3>
                <small class="text-muted">Failed Today</small>
            </div>
        </div>
    </div>
</div>

<!-- Action Buttons -->
<div class="mb-4">
    <form action="{{ route('admin.notifications.process-email') }}" method="POST" class="d-inline">
        @csrf
        <button type="submit" class="btn btn-info text-white">
            <i class="fas fa-play me-1"></i> Process Queue Now
        </button>
    </form>
    <form action="{{ route('admin.notifications.retry-email') }}" method="POST" class="d-inline">
        @csrf
        <button type="submit" class="btn btn-warning">
            <i class="fas fa-redo me-1"></i> Retry Failed
        </button>
    </form>
    <a href="{{ route('admin.notifications.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back
    </a>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form action="{{ route('admin.notifications.email-queue') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Sent</option>
                    <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="priority" class="form-select">
                    <option value="">All Priorities</option>
                    <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                    <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                    <option value="normal" {{ request('priority') == 'normal' ? 'selected' : '' }}>Normal</option>
                    <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
        </form>
    </div>
</div>

<!-- Queue Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Recipient</th>
                        <th>Subject</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Attempts</th>
                        <th>Scheduled</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($queue as $item)
                    <tr>
                        <td>#{{ $item->id }}</td>
                        <td>
                            <div>{{ Str::limit($item->recipient_email, 25) }}</div>
                            <small class="text-muted">{{ $item->recipient_name }}</small>
                        </td>
                        <td>{{ Str::limit($item->subject, 40) }}</td>
                        <td>
                            <span class="badge bg-{{ $item->priority == 'urgent' ? 'danger' : ($item->priority == 'high' ? 'warning' : 'secondary') }}">
                                {{ ucfirst($item->priority) }}
                            </span>
                        </td>
                        <td>
                            @if($item->status == 'pending')
                                <span class="badge bg-warning">Pending</span>
                            @elseif($item->status == 'sent')
                                <span class="badge bg-success">Sent</span>
                            @elseif($item->status == 'failed')
                                <span class="badge bg-danger">Failed</span>
                            @endif
                        </td>
                        <td>{{ $item->attempts }}/{{ $item->max_attempts }}</td>
                        <td>{{ $item->scheduled_at ? $item->scheduled_at->format('d M H:i') : 'Immediate' }}</td>
                        <td>
                            @if($item->status == 'pending')
                            <form action="{{ route('admin.notifications.cancel', ['type' => 'email', 'id' => $item->id]) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Cancel this email?')">
                                    <i class="fas fa-times"></i>
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <i class="fas fa-envelope fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No emails in queue</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($queue->hasPages())
    <div class="card-footer">{{ $queue->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
