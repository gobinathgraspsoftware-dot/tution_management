@extends('layouts.app')

@section('title', $announcement->title)

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Announcement Details</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('parent.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('parent.announcements.index') }}">Announcements</a></li>
                    <li class="breadcrumb-item active">View</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('parent.announcements.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Announcements
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <div class="d-flex align-items-center">
                        {{-- Pinned Icon --}}
                        @if($announcement->is_pinned)
                        <i class="fas fa-thumbtack text-danger me-2 fa-lg" title="Pinned"></i>
                        @endif

                        {{-- Priority Badge --}}
                        @switch($announcement->priority)
                            @case('urgent')
                                <span class="badge bg-danger me-2">Urgent</span>
                                @break
                            @case('high')
                                <span class="badge bg-warning text-dark me-2">High Priority</span>
                                @break
                            @case('normal')
                                <span class="badge bg-info me-2">Normal</span>
                                @break
                            @case('low')
                                <span class="badge bg-secondary me-2">Low Priority</span>
                                @break
                        @endswitch

                        {{-- Type Badge --}}
                        @switch($announcement->type)
                            @case('emergency')
                                <span class="badge bg-danger">Emergency</span>
                                @break
                            @case('event')
                                <span class="badge bg-success">Event</span>
                                @break
                            @case('academic')
                                <span class="badge bg-primary">Academic</span>
                                @break
                            @case('holiday')
                                <span class="badge bg-info">Holiday</span>
                                @break
                            @default
                                <span class="badge bg-secondary">General</span>
                        @endswitch
                    </div>
                </div>
                <div class="card-body">
                    {{-- Urgent/Emergency Alert --}}
                    @if($announcement->priority === 'urgent' || $announcement->type === 'emergency')
                    <div class="alert alert-danger d-flex align-items-center mb-4" role="alert">
                        <i class="fas fa-exclamation-triangle me-2 fa-lg"></i>
                        <div>
                            <strong>Important Notice!</strong> This is a {{ $announcement->priority === 'urgent' ? 'urgent' : 'emergency' }} announcement. Please read carefully.
                        </div>
                    </div>
                    @endif

                    <h2 class="h4 mb-3">{{ $announcement->title }}</h2>

                    <div class="d-flex flex-wrap text-muted mb-4">
                        <span class="me-4">
                            <i class="fas fa-user me-1"></i>
                            Posted by: <strong>{{ $announcement->creator->name ?? 'System' }}</strong>
                        </span>
                        <span class="me-4">
                            <i class="fas fa-calendar me-1"></i>
                            {{ $announcement->created_at->format('d M Y, h:i A') }}
                        </span>
                        @if($announcement->targetClass)
                        <span>
                            <i class="fas fa-users me-1"></i>
                            For: {{ $announcement->targetClass->name }}
                        </span>
                        @else
                        <span>
                            <i class="fas fa-globe me-1"></i>
                            For: {{ ucfirst($announcement->target_audience) }}
                        </span>
                        @endif
                    </div>

                    <hr>

                    {{-- Announcement Content --}}
                    <div class="announcement-content my-4">
                        {!! $announcement->content !!}
                    </div>

                    {{-- Attachments --}}
                    @if($announcement->attachments && count($announcement->attachments) > 0)
                    <hr>
                    <h5 class="mb-3"><i class="fas fa-paperclip me-2"></i>Attachments</h5>
                    <div class="row">
                        @foreach($announcement->attachments as $index => $attachment)
                        <div class="col-md-6 mb-2">
                            <div class="card">
                                <div class="card-body py-2 px-3">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center">
                                            @php
                                                $extension = pathinfo($attachment['original_name'] ?? $attachment, PATHINFO_EXTENSION);
                                                $iconClass = match(strtolower($extension)) {
                                                    'pdf' => 'fa-file-pdf text-danger',
                                                    'doc', 'docx' => 'fa-file-word text-primary',
                                                    'xls', 'xlsx' => 'fa-file-excel text-success',
                                                    'ppt', 'pptx' => 'fa-file-powerpoint text-warning',
                                                    'jpg', 'jpeg', 'png', 'gif' => 'fa-file-image text-info',
                                                    'zip', 'rar' => 'fa-file-archive text-secondary',
                                                    default => 'fa-file text-muted',
                                                };
                                            @endphp
                                            <i class="fas {{ $iconClass }} fa-2x me-3"></i>
                                            <div>
                                                <div class="fw-bold small">{{ $attachment['original_name'] ?? basename($attachment) }}</div>
                                                @if(isset($attachment['size']))
                                                <small class="text-muted">{{ number_format($attachment['size'] / 1024, 1) }} KB</small>
                                                @endif
                                            </div>
                                        </div>
                                        <a href="{{ route('parent.announcements.download-attachment', [$announcement, $index]) }}"
                                           class="btn btn-sm btn-outline-primary" title="Download">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Announcement Info -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Information</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-muted">Posted:</td>
                            <td>{{ $announcement->created_at->format('d M Y') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Time:</td>
                            <td>{{ $announcement->created_at->format('h:i A') }}</td>
                        </tr>
                        @if($announcement->publish_at)
                        <tr>
                            <td class="text-muted">Published:</td>
                            <td>{{ $announcement->publish_at->format('d M Y, h:i A') }}</td>
                        </tr>
                        @endif
                        @if($announcement->expires_at)
                        <tr>
                            <td class="text-muted">Expires:</td>
                            <td>
                                <span class="{{ $announcement->expires_at->isPast() ? 'text-danger' : '' }}">
                                    {{ $announcement->expires_at->format('d M Y, h:i A') }}
                                </span>
                            </td>
                        </tr>
                        @endif
                        <tr>
                            <td class="text-muted">Type:</td>
                            <td>{{ ucfirst($announcement->type) }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Priority:</td>
                            <td>{{ ucfirst($announcement->priority) }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Audience:</td>
                            <td>
                                @if($announcement->targetClass)
                                    {{ $announcement->targetClass->name }}
                                @else
                                    {{ ucfirst(str_replace('_', ' ', $announcement->target_audience)) }}
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h6>
                </div>
                <div class="list-group list-group-flush">
                    <a href="{{ route('parent.announcements.index') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-list me-2 text-primary"></i> All Announcements
                    </a>
                    <a href="{{ route('parent.announcements.index', ['read_status' => 'unread']) }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-envelope me-2 text-warning"></i> Unread Only
                    </a>
                    <a href="{{ route('parent.dashboard') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-home me-2 text-success"></i> Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.announcement-content {
    font-size: 1rem;
    line-height: 1.7;
}
.announcement-content img {
    max-width: 100%;
    height: auto;
}
.announcement-content table {
    width: 100%;
    margin-bottom: 1rem;
    border-collapse: collapse;
}
.announcement-content table td,
.announcement-content table th {
    padding: 0.5rem;
    border: 1px solid #dee2e6;
}
</style>
@endsection
