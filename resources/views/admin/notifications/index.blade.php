@extends('layouts.app')

@section('title', 'Notification Dashboard')
@section('page-title', 'Notification Dashboard')

@section('content')
<div class="page-header">
    <h1><i class="fas fa-bell me-2"></i> Notification Dashboard</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Notifications</li>
        </ol>
    </nav>
</div>

<!-- Action Buttons -->
<div class="mb-4">
    <a href="{{ route('admin.notifications.create') }}" class="btn btn-primary">
        <i class="fas fa-paper-plane me-1"></i> Send Notification
    </a>
    <a href="{{ route('admin.templates.index') }}" class="btn btn-outline-primary">
        <i class="fas fa-file-alt me-1"></i> Manage Templates
    </a>
    <a href="{{ route('admin.notifications.settings') }}" class="btn btn-outline-secondary">
        <i class="fas fa-cog me-1"></i> Settings
    </a>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                            <i class="fas fa-paper-plane text-primary fa-lg"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h3 class="mb-0">{{ $stats['total'] }}</h3>
                        <small class="text-muted">Total Today</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-success bg-opacity-10 rounded-circle p-3">
                            <i class="fas fa-check text-success fa-lg"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h3 class="mb-0">{{ $stats['sent'] }}</h3>
                        <small class="text-muted">Sent</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                            <i class="fas fa-clock text-warning fa-lg"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h3 class="mb-0">{{ $stats['pending'] }}</h3>
                        <small class="text-muted">Pending</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-danger bg-opacity-10 rounded-circle p-3">
                            <i class="fas fa-times text-danger fa-lg"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h3 class="mb-0">{{ $stats['failed'] }}</h3>
                        <small class="text-muted">Failed</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- WhatsApp Status -->
    <div class="col-md-6 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fab fa-whatsapp me-2"></i> WhatsApp Status</h5>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <span class="badge {{ $whatsappStatus['connected'] ? 'bg-success' : 'bg-danger' }} me-2">
                        {{ $whatsappStatus['connected'] ? 'Connected' : 'Disconnected' }}
                    </span>
                    @if($whatsappStatus['connected'] && isset($whatsappStatus['phone']))
                        <small class="text-muted">{{ $whatsappStatus['phone'] }}</small>
                    @endif
                </div>

                <div class="row text-center">
                    <div class="col-3">
                        <h5 class="mb-0">{{ $whatsappQueue['pending'] }}</h5>
                        <small class="text-muted">Pending</small>
                    </div>
                    <div class="col-3">
                        <h5 class="mb-0 text-success">{{ $whatsappQueue['sent'] }}</h5>
                        <small class="text-muted">Sent</small>
                    </div>
                    <div class="col-3">
                        <h5 class="mb-0 text-info">{{ $whatsappQueue['delivered'] }}</h5>
                        <small class="text-muted">Delivered</small>
                    </div>
                    <div class="col-3">
                        <h5 class="mb-0 text-danger">{{ $whatsappQueue['failed'] }}</h5>
                        <small class="text-muted">Failed</small>
                    </div>
                </div>

                <hr>

                <div class="d-flex gap-2">
                    <a href="{{ route('admin.notifications.whatsapp-queue') }}" class="btn btn-sm btn-outline-success">
                        View Queue
                    </a>
                    <form action="{{ route('admin.notifications.process-whatsapp') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-success">
                            <i class="fas fa-play me-1"></i> Process Now
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Email Status -->
    <div class="col-md-6 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-envelope me-2"></i> Email Status</h5>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <span class="badge {{ config('notification.email.enabled') ? 'bg-success' : 'bg-warning' }}">
                        {{ config('notification.email.enabled') ? 'Enabled' : 'Disabled' }}
                    </span>
                </div>

                <div class="row text-center">
                    <div class="col-4">
                        <h5 class="mb-0">{{ $emailQueue['pending'] }}</h5>
                        <small class="text-muted">Pending</small>
                    </div>
                    <div class="col-4">
                        <h5 class="mb-0 text-success">{{ $emailQueue['sent'] }}</h5>
                        <small class="text-muted">Sent</small>
                    </div>
                    <div class="col-4">
                        <h5 class="mb-0 text-danger">{{ $emailQueue['failed'] }}</h5>
                        <small class="text-muted">Failed</small>
                    </div>
                </div>

                <hr>

                <div class="d-flex gap-2">
                    <a href="{{ route('admin.notifications.email-queue') }}" class="btn btn-sm btn-outline-info">
                        View Queue
                    </a>
                    <form action="{{ route('admin.notifications.process-email') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-info">
                            <i class="fas fa-play me-1"></i> Process Now
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Channel Distribution -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <i class="fab fa-whatsapp fa-2x text-success mb-2"></i>
                <h4>{{ $stats['by_channel']['whatsapp'] ?? 0 }}</h4>
                <small class="text-muted">WhatsApp Today</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <i class="fas fa-envelope fa-2x text-info mb-2"></i>
                <h4>{{ $stats['by_channel']['email'] ?? 0 }}</h4>
                <small class="text-muted">Email Today</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <i class="fas fa-sms fa-2x text-primary mb-2"></i>
                <h4>{{ $stats['by_channel']['sms'] ?? 0 }}</h4>
                <small class="text-muted">SMS Today</small>
            </div>
        </div>
    </div>
</div>

<!-- Recent Notifications -->
<div class="card border-0 shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-history me-2"></i> Recent Notifications</h5>
        <a href="{{ route('admin.notifications.logs') }}" class="btn btn-sm btn-outline-primary">
            View All Logs
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Time</th>
                        <th>Channel</th>
                        <th>Recipient</th>
                        <th>Type</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentLogs as $log)
                    <tr>
                        <td>
                            <small>{{ $log->created_at->format('d M H:i') }}</small>
                        </td>
                        <td>
                            @if($log->channel === 'whatsapp')
                                <span class="badge bg-success"><i class="fab fa-whatsapp"></i> WhatsApp</span>
                            @elseif($log->channel === 'email')
                                <span class="badge bg-info"><i class="fas fa-envelope"></i> Email</span>
                            @elseif($log->channel === 'sms')
                                <span class="badge bg-primary"><i class="fas fa-sms"></i> SMS</span>
                            @endif
                        </td>
                        <td>
                            {{ Str::limit($log->recipient, 25) }}
                            @if($log->user)
                                <br><small class="text-muted">{{ $log->user->name }}</small>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $log->type)) }}</span>
                        </td>
                        <td>
                            @if($log->status === 'sent')
                                <span class="badge bg-success">Sent</span>
                            @elseif($log->status === 'delivered')
                                <span class="badge bg-info">Delivered</span>
                            @elseif($log->status === 'pending')
                                <span class="badge bg-warning">Pending</span>
                            @elseif($log->status === 'failed')
                                <span class="badge bg-danger">Failed</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-4">
                            <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                            <p class="text-muted mb-0">No notifications yet</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
