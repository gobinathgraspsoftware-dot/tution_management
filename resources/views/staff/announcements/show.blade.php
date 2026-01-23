@extends('layouts.app')

@section('title', $announcement->title)
@section('page-title', 'Announcement Details')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="fas fa-bullhorn me-2"></i> Announcement Details</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('staff.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('staff.announcements.index') }}">Announcements</a></li>
                <li class="breadcrumb-item active">{{ Str::limit($announcement->title, 30) }}</li>
            </ol>
        </nav>
    </div>
    <div>
        <a href="{{ route('staff.announcements.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to List
        </a>
    </div>
</div>

<div class="row">
    <!-- Main Content -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h4 class="mb-1">
                            @if($announcement->is_pinned)
                                <i class="fas fa-thumbtack text-info me-2" title="Pinned"></i>
                            @endif
                            {{ $announcement->title }}
                        </h4>
                        <div class="text-muted small">
                            <span class="me-3">
                                <i class="fas fa-user me-1"></i>
                                {{ $announcement->creator->name ?? 'System' }}
                            </span>
                            <span>
                                <i class="fas fa-calendar me-1"></i>
                                {{ $announcement->created_at->format('d M Y, h:i A') }}
                            </span>
                        </div>
                    </div>
                    <div>
                        @if($announcement->priority == 'urgent')
                            <span class="badge bg-danger">Urgent</span>
                        @elseif($announcement->priority == 'high')
                            <span class="badge bg-warning">High Priority</span>
                        @elseif($announcement->priority == 'normal')
                            <span class="badge bg-info">Normal</span>
                        @else
                            <span class="badge bg-secondary">Low Priority</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="card-body">
                <!-- Announcement Content -->
                <div class="announcement-content mb-4">
                    {!! nl2br(e($announcement->content)) !!}
                </div>

                <!-- Attachments -->
                @if($announcement->attachments && count($announcement->attachments) > 0)
                    <hr>
                    <h6><i class="fas fa-paperclip me-2"></i>Attachments</h6>
                    <div class="list-group">
                        @foreach($announcement->attachments as $index => $attachment)
                            <a href="{{ route('staff.announcements.download-attachment', [$announcement, $index]) }}" 
                               class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas {{ getFileIcon($attachment['type'] ?? 'application/octet-stream') }} me-2"></i>
                                    {{ $attachment['name'] }}
                                </div>
                                <span class="badge bg-secondary">
                                    {{ formatFileSize($attachment['size'] ?? 0) }}
                                </span>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-md-4">
        <!-- Info Card -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-info-circle me-2"></i> Information
            </div>
            <div class="card-body">
                <table class="table table-borderless table-sm mb-0">
                    <tr>
                        <td class="text-muted">Type</td>
                        <td>
                            <span class="badge 
                                @switch($announcement->type)
                                    @case('general') bg-primary @break
                                    @case('academic') bg-success @break
                                    @case('event') bg-info @break
                                    @case('holiday') bg-warning @break
                                    @case('payment') bg-danger @break
                                    @default bg-secondary
                                @endswitch">
                                {{ ucfirst($announcement->type) }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Target</td>
                        <td>
                            @if($announcement->targetClass)
                                <span class="badge bg-info">
                                    <i class="fas fa-users me-1"></i>
                                    {{ $announcement->targetClass->name }}
                                </span>
                            @else
                                <span class="badge bg-secondary">
                                    <i class="fas fa-globe me-1"></i>
                                    {{ ucfirst(str_replace('_', ' ', $announcement->target_audience)) }}
                                </span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Created</td>
                        <td>{{ $announcement->created_at->format('d M Y, h:i A') }}</td>
                    </tr>
                    @if($announcement->publish_at)
                    <tr>
                        <td class="text-muted">Published</td>
                        <td>{{ $announcement->publish_at->format('d M Y, h:i A') }}</td>
                    </tr>
                    @endif
                    @if($announcement->expires_at)
                    <tr>
                        <td class="text-muted">Expires</td>
                        <td>
                            {{ $announcement->expires_at->format('d M Y, h:i A') }}
                            @if($announcement->expires_at < now())
                                <span class="badge bg-danger">Expired</span>
                            @elseif($announcement->expires_at < now()->addDays(7))
                                <span class="badge bg-warning">Expiring Soon</span>
                            @endif
                        </td>
                    </tr>
                    @endif
                    @if($announcement->is_pinned)
                    <tr>
                        <td class="text-muted">Status</td>
                        <td>
                            <span class="badge bg-info">
                                <i class="fas fa-thumbtack me-1"></i> Pinned
                            </span>
                        </td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-bolt me-2"></i> Quick Actions
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('staff.announcements.index') }}" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Announcements
                    </a>
                    @if($announcement->attachments && count($announcement->attachments) > 0)
                        <button type="button" class="btn btn-outline-success" onclick="downloadAllAttachments()">
                            <i class="fas fa-download me-1"></i> Download All Attachments
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function downloadAllAttachments() {
    @if($announcement->attachments && count($announcement->attachments) > 0)
        @foreach($announcement->attachments as $index => $attachment)
            setTimeout(function() {
                window.open('{{ route('staff.announcements.download-attachment', [$announcement, $index]) }}', '_blank');
            }, {{ $index * 500 }});
        @endforeach
    @endif
}
</script>
@endpush

@php
function getFileIcon($mimeType) {
    $icons = [
        'application/pdf' => 'fa-file-pdf text-danger',
        'application/msword' => 'fa-file-word text-primary',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'fa-file-word text-primary',
        'application/vnd.ms-excel' => 'fa-file-excel text-success',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'fa-file-excel text-success',
        'application/vnd.ms-powerpoint' => 'fa-file-powerpoint text-warning',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'fa-file-powerpoint text-warning',
        'image/jpeg' => 'fa-file-image text-info',
        'image/png' => 'fa-file-image text-info',
        'image/gif' => 'fa-file-image text-info',
        'text/plain' => 'fa-file-alt text-secondary',
        'application/zip' => 'fa-file-archive text-warning',
        'application/x-rar-compressed' => 'fa-file-archive text-warning',
    ];

    return $icons[$mimeType] ?? 'fa-file text-secondary';
}

function formatFileSize($bytes) {
    if ($bytes == 0) return '0 Bytes';
    $k = 1024;
    $sizes = ['Bytes', 'KB', 'MB', 'GB'];
    $i = floor(log($bytes) / log($k));
    return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
}
@endphp

@push('styles')
<style>
.announcement-content {
    white-space: pre-wrap;
    line-height: 1.8;
}
</style>
@endpush
