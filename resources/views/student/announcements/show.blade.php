@extends('layouts.app')

@section('title', 'Announcement Details')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('student.announcements.index') }}">Announcements</a></li>
                <li class="breadcrumb-item active">{{ $announcement->title }}</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Announcement Details -->
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <!-- Priority Badge -->
                    @if($announcement->priority === 'urgent' || $announcement->priority === 'high')
                        <div class="alert alert-{{ $announcement->priority === 'urgent' ? 'danger' : 'warning' }} mb-3">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>{{ ucfirst($announcement->priority) }} Priority</strong>
                        </div>
                    @endif

                    <!-- Title -->
                    <h3 class="mb-3">
                        {{ $announcement->title }}
                        @if($announcement->is_pinned)
                            <i class="fas fa-thumbtack text-warning ms-2"></i>
                        @endif
                    </h3>

                    <!-- Meta Information -->
                    <div class="d-flex flex-wrap gap-3 mb-4 pb-3 border-bottom">
                        <div class="d-flex align-items-center text-muted">
                            <i class="fas fa-user me-2"></i>
                            <span>{{ $announcement->creator->name }}</span>
                        </div>
                        <div class="d-flex align-items-center text-muted">
                            <i class="fas fa-calendar me-2"></i>
                            <span>{{ $announcement->publish_at ? $announcement->publish_at->format('d M Y, h:i A') : $announcement->created_at->format('d M Y, h:i A') }}</span>
                        </div>
                        @if($announcement->targetClass)
                            <div class="d-flex align-items-center text-muted">
                                <i class="fas fa-chalkboard me-2"></i>
                                <span>{{ $announcement->targetClass->name }}</span>
                            </div>
                        @endif
                        <div>
                            <span class="badge bg-{{ $announcement->priority === 'urgent' ? 'danger' : ($announcement->priority === 'high' ? 'warning' : 'secondary') }}">
                                {{ ucfirst($announcement->priority) }}
                            </span>
                            <span class="badge bg-info ms-1">
                                {{ ucfirst($announcement->type) }}
                            </span>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="announcement-content mb-4">
                        {!! nl2br(e($announcement->content)) !!}
                    </div>

                    <!-- Attachments -->
                    @if($announcement->attachments && count($announcement->attachments) > 0)
                        <div class="border-top pt-4">
                            <h5 class="mb-3"><i class="fas fa-paperclip me-2"></i> Attachments</h5>
                            <div class="list-group">
                                @foreach($announcement->attachments as $index => $attachment)
                                    <a href="{{ Storage::url($attachment['path']) }}"
                                       target="_blank"
                                       class="list-group-item list-group-item-action">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0 me-3">
                                                <i class="fas fa-file-{{ $this->getFileIcon($attachment['type']) }} fa-2x text-primary"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1">{{ $attachment['name'] }}</h6>
                                                <small class="text-muted">{{ $this->formatBytes($attachment['size']) }}</small>
                                            </div>
                                            <div class="flex-shrink-0">
                                                <i class="fas fa-download text-muted"></i>
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Actions Card -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="fas fa-cog me-2"></i> Actions</h6>
                </div>
                <div class="card-body">
                    <a href="{{ route('student.announcements.index') }}" class="btn btn-outline-primary w-100 mb-2">
                        <i class="fas fa-arrow-left me-2"></i> Back to Announcements
                    </a>
                </div>
            </div>

            <!-- Information Card -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i> Information</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-muted" width="40%">Type:</td>
                            <td><strong>{{ ucfirst($announcement->type) }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Priority:</td>
                            <td>
                                <span class="badge bg-{{ $announcement->priority === 'urgent' ? 'danger' : ($announcement->priority === 'high' ? 'warning text-dark' : 'secondary') }}">
                                    {{ ucfirst($announcement->priority) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Target:</td>
                            <td><strong>{{ ucfirst($announcement->target_audience) }}</strong></td>
                        </tr>
                        @if($announcement->targetClass)
                            <tr>
                                <td class="text-muted">Class:</td>
                                <td><strong>{{ $announcement->targetClass->name }}</strong></td>
                            </tr>
                        @endif
                        <tr>
                            <td class="text-muted">Published:</td>
                            <td>{{ $announcement->publish_at ? $announcement->publish_at->format('d M Y') : $announcement->created_at->format('d M Y') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Posted by:</td>
                            <td>{{ $announcement->creator->name }}</td>
                        </tr>
                        @if($announcement->attachments && count($announcement->attachments) > 0)
                            <tr>
                                <td class="text-muted">Attachments:</td>
                                <td><strong>{{ count($announcement->attachments) }}</strong></td>
                            </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@php
// Helper functions for file display
function getFileIcon($mimeType) {
    if (str_contains($mimeType, 'pdf')) return 'pdf';
    if (str_contains($mimeType, 'word') || str_contains($mimeType, 'document')) return 'word';
    if (str_contains($mimeType, 'excel') || str_contains($mimeType, 'spreadsheet')) return 'excel';
    if (str_contains($mimeType, 'powerpoint') || str_contains($mimeType, 'presentation')) return 'powerpoint';
    if (str_contains($mimeType, 'image')) return 'image';
    if (str_contains($mimeType, 'video')) return 'video';
    if (str_contains($mimeType, 'audio')) return 'audio';
    if (str_contains($mimeType, 'zip') || str_contains($mimeType, 'rar')) return 'archive';
    return 'alt';
}

function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, $precision) . ' ' . $units[$pow];
}
@endphp
@endsection
