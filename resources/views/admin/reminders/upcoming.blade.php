@extends('layouts.app')

@section('title', 'Upcoming Reminders')
@section('page-title', 'Upcoming Reminders')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.reminders.index') }}">Reminders</a></li>
            <li class="breadcrumb-item active">Upcoming</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="fas fa-calendar-alt me-2"></i> Upcoming Reminders</h4>
            <p class="text-muted mb-0">Payment reminders scheduled for the next 7 days</p>
        </div>
        <div>
            <a href="{{ route('admin.reminders.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-list me-1"></i> All Reminders
            </a>
            <form action="{{ route('admin.reminders.send-now') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-primary" onclick="return confirm('Send all due reminders now?')">
                    <i class="fas fa-paper-plane me-1"></i> Send Due Now
                </button>
            </form>
        </div>
    </div>

    @if($upcomingReminders->isEmpty())
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            No reminders scheduled for the next 7 days.
        </div>
    @else
        <!-- Summary Stats -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-calendar-check fa-2x opacity-75"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h3 class="mb-0">{{ $upcomingReminders->flatten()->count() }}</h3>
                                <small>Total Scheduled</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm bg-warning text-dark">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-clock fa-2x opacity-75"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h3 class="mb-0">{{ $upcomingReminders->has(today()->format('Y-m-d')) ? $upcomingReminders[today()->format('Y-m-d')]->count() : 0 }}</h3>
                                <small>Due Today</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="fab fa-whatsapp fa-2x opacity-75"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h3 class="mb-0">{{ $upcomingReminders->flatten()->where('channel', 'whatsapp')->count() }}</h3>
                                <small>WhatsApp</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-envelope fa-2x opacity-75"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h3 class="mb-0">{{ $upcomingReminders->flatten()->where('channel', 'email')->count() }}</h3>
                                <small>Email</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grouped by Date -->
        @foreach($upcomingReminders as $date => $reminders)
            @php
                $dateObj = \Carbon\Carbon::parse($date);
                $isToday = $dateObj->isToday();
                $isTomorrow = $dateObj->isTomorrow();
            @endphp
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">
                            @if($isToday)
                                <span class="badge bg-warning me-2">Today</span>
                            @elseif($isTomorrow)
                                <span class="badge bg-info me-2">Tomorrow</span>
                            @endif
                            {{ $dateObj->format('l, d M Y') }}
                        </h5>
                    </div>
                    <span class="badge bg-primary rounded-pill">{{ $reminders->count() }} reminders</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Student</th>
                                    <th>Invoice</th>
                                    <th>Amount Due</th>
                                    <th>Type</th>
                                    <th>Channel</th>
                                    <th>Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($reminders as $reminder)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm me-2">
                                                    <span class="avatar-title rounded-circle bg-primary text-white">
                                                        {{ strtoupper(substr($reminder->invoice?->student?->user?->name ?? 'S', 0, 1)) }}
                                                    </span>
                                                </div>
                                                <div>
                                                    <strong>{{ $reminder->invoice?->student?->user?->name ?? 'N/A' }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $reminder->invoice?->student?->parent?->user?->phone ?? 'No phone' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <a href="{{ $reminder->invoice ? route('admin.invoices.show', $reminder->invoice) : '#' }}">
                                                {{ $reminder->invoice?->invoice_number ?? 'N/A' }}
                                            </a>
                                            <br>
                                            <small class="text-muted">Due: {{ $reminder->invoice?->due_date?->format('d M Y') ?? 'N/A' }}</small>
                                        </td>
                                        <td>
                                            <strong class="text-danger">RM {{ number_format($reminder->invoice?->balance ?? 0, 2) }}</strong>
                                        </td>
                                        <td>
                                            @switch($reminder->reminder_type)
                                                @case('first')
                                                    <span class="badge bg-info">1st (10th)</span>
                                                    @break
                                                @case('second')
                                                    <span class="badge bg-warning">2nd (18th)</span>
                                                    @break
                                                @case('final')
                                                    <span class="badge bg-danger">Final (24th)</span>
                                                    @break
                                                @default
                                                    <span class="badge bg-secondary">{{ ucfirst($reminder->reminder_type) }}</span>
                                            @endswitch
                                        </td>
                                        <td>
                                            @if($reminder->channel === 'whatsapp')
                                                <i class="fab fa-whatsapp text-success"></i> WhatsApp
                                            @elseif($reminder->channel === 'email')
                                                <i class="fas fa-envelope text-primary"></i> Email
                                            @else
                                                <i class="fas fa-sms text-info"></i> SMS
                                            @endif
                                        </td>
                                        <td>
                                            @switch($reminder->status)
                                                @case('scheduled')
                                                    <span class="badge bg-info">Scheduled</span>
                                                    @break
                                                @case('pending')
                                                    <span class="badge bg-warning">Pending</span>
                                                    @break
                                                @default
                                                    <span class="badge bg-secondary">{{ ucfirst($reminder->status) }}</span>
                                            @endswitch
                                        </td>
                                        <td class="text-end">
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('admin.reminders.show', $reminder) }}" class="btn btn-outline-primary" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <form action="{{ route('admin.reminders.cancel', $reminder) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger" title="Cancel" onclick="return confirm('Cancel this reminder?')">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endforeach
    @endif
</div>

<style>
.avatar-sm {
    width: 36px;
    height: 36px;
}
.avatar-title {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
    font-size: 14px;
    font-weight: 600;
}
</style>
@endsection
