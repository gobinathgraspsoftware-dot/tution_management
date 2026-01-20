@extends('layouts.app')

@section('title', $announcement->title)

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">View Announcement</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('teacher.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('teacher.announcements.index') }}">Announcements</a></li>
                    <li class="breadcrumb-item active">View</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('teacher.announcements.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Announcements
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Announcement Content -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <div class="d-flex align-items-center">
                        @if($announcement->is_pinned)
                            <span class="badge bg-warning text-dark me-2">
                                <i class="fas fa-thumbtack"></i> Pinned
                            </span>
                        @endif
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
                        @if($announcement->target_class_id)
                            <span class="badge bg-primary">
                                <i class="fas fa-chalkboard me-1"></i>{{ $announcement->targetClass->name ?? 'Class' }}
                            </span>
                        @else
                            <span class="badge bg-success">
                                <i class="fas fa-globe me-1"></i>General Announcement
                            </span>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <h3 class="card-title mb-3">{{ $announcement->title }}</h3>
                    
                    <div class="mb-4 text-muted small">
                        <span class="me-3">
                            <i class="fas fa-user me-1"></i>
                            Posted by: <strong>{{ $announcement->creator->name ?? 'System' }}</strong>
                        </span>
                        <span class="me-3">
                            <i class="fas fa-calendar me-1"></i>
                            {{ $announcement->created_at->format('d M Y, h:i A') }}
                        </span>
                        @if($announcement->expires_at)
                            <span class="{{ $announcement->expires_at->isPast() ? 'text-danger' : '' }}">
                                <i class="fas fa-clock me-1"></i>
                                Expires: {{ $announcement->expires_at->format('d M Y') }}
                            </span>
                        @endif
                    </div>

                    <hr>

                    <div class="announcement-content">
                        {!! nl2br(e($announcement->content)) !!}
                    </div>
                </div>
            </div>

            <!-- Attachments -->
            @if($announcement->attachments && count($announcement->attachments) > 0)
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="fas fa-paperclip me-2"></i>Attachments ({{ count($announcement->attachments) }})
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            @foreach($announcement->attachments as $index => $attachment)
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        @php
                                            $ext = pathinfo($attachment['name'] ?? '', PATHINFO_EXTENSION);
                                            $icon = match(strtolower($ext)) {
                                                'pdf' => 'fa-file-pdf text-danger',
                                                'doc', 'docx' => 'fa-file-word text-primary',
                                                'xls', 'xlsx' => 'fa-file-excel text-success',
                                                'ppt', 'pptx' => 'fa-file-powerpoint text-warning',
                                                'jpg', 'jpeg', 'png', 'gif' => 'fa-file-image text-info',
                                                'zip', 'rar' => 'fa-file-archive text-secondary',
                                                default => 'fa-file text-muted'
                                            };
                                        @endphp
                                        <i class="fas {{ $icon }} me-2"></i>
                                        <span>{{ $attachment['name'] ?? 'Attachment ' . ($index + 1) }}</span>
                                        @if(isset($attachment['size']))
                                            <small class="text-muted ms-2">
                                                ({{ number_format($attachment['size'] / 1024, 2) }} KB)
                                            </small>
                                        @endif
                                    </div>
                                    <a href="{{ route('teacher.announcements.download-attachment', [$announcement, $index]) }}" 
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-download me-1"></i> Download
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-lg-4">
            <!-- Announcement Info -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Details</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <th class="ps-0" style="width: 40%;">Type</th>
                            <td>
                                @if($announcement->target_class_id)
                                    <span class="badge bg-primary">Class</span>
                                @else
                                    <span class="badge bg-success">General</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="ps-0">Priority</th>
                            <td>
                                @switch($announcement->priority)
                                    @case('urgent')
                                        <span class="badge bg-danger">Urgent</span>
                                        @break
                                    @case('high')
                                        <span class="badge bg-warning text-dark">High</span>
                                        @break
                                    @case('normal')
                                        <span class="badge bg-info">Normal</span>
                                        @break
                                    @case('low')
                                        <span class="badge bg-secondary">Low</span>
                                        @break
                                @endswitch
                            </td>
                        </tr>
                        @if($announcement->target_class_id && $announcement->targetClass)
                            <tr>
                                <th class="ps-0">Target Class</th>
                                <td>{{ $announcement->targetClass->name }}</td>
                            </tr>
                        @endif
                        <tr>
                            <th class="ps-0">Target Audience</th>
                            <td>{{ ucfirst(str_replace('_', ' ', $announcement->target_audience)) }}</td>
                        </tr>
                        <tr>
                            <th class="ps-0">Posted By</th>
                            <td>{{ $announcement->creator->name ?? 'System' }}</td>
                        </tr>
                        <tr>
                            <th class="ps-0">Posted On</th>
                            <td>{{ $announcement->created_at->format('d M Y') }}</td>
                        </tr>
                        @if($announcement->expires_at)
                            <tr>
                                <th class="ps-0">Expires On</th>
                                <td class="{{ $announcement->expires_at->isPast() ? 'text-danger' : '' }}">
                                    {{ $announcement->expires_at->format('d M Y') }}
                                    @if($announcement->expires_at->isPast())
                                        <span class="badge bg-danger">Expired</span>
                                    @endif
                                </td>
                            </tr>
                        @endif
                    </table>
                </div>
            </div>

            <!-- Read Status -->
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-check-circle me-2"></i>Status</h5>
                </div>
                <div class="card-body text-center">
                    <i class="fas fa-eye fa-3x text-success mb-2"></i>
                    <h5 class="text-success">Read</h5>
                    <p class="text-muted mb-0">You have viewed this announcement.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
