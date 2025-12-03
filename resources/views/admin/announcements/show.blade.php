@extends('layouts.app')

@section('title', 'View Announcement')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.announcements.index') }}">Announcements</a></li>
                <li class="breadcrumb-item active">View</li>
            </ol>
        </nav>
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-0">Announcement Details</h1>
            <div class="btn-group">
                @can('edit-announcements')
                    <a href="{{ route('admin.announcements.edit', $announcement) }}" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                @endcan
                @can('delete-announcements')
                    <form action="{{ route('admin.announcements.destroy', $announcement) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Delete this announcement?')">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </form>
                @endcan
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <div>
                            @if($announcement->is_pinned)
                                <i class="fas fa-thumbtack text-danger me-2"></i>
                            @endif
                            <h2>{{ $announcement->title }}</h2>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-{{ $announcement->priority == 'urgent' ? 'danger' : ($announcement->priority == 'high' ? 'warning' : 'secondary') }} mb-2">
                                {{ ucfirst($announcement->priority) }} Priority
                            </span>
                            <br>
                            <span class="badge bg-{{ $announcement->status == 'published' ? 'success' : 'secondary' }}">
                                {{ ucfirst($announcement->status) }}
                            </span>
                        </div>
                    </div>

                    <!-- Meta Information -->
                    <div class="border-bottom pb-3 mb-4">
                        <div class="row small text-muted">
                            <div class="col-md-6">
                                <i class="fas fa-user"></i> <strong>Posted by:</strong> {{ $announcement->creator->name }}
                            </div>
                            <div class="col-md-6">
                                <i class="fas fa-calendar"></i> <strong>Date:</strong>
                                {{ $announcement->published_at ? $announcement->published_at->format('M j, Y h:i A') : $announcement->created_at->format('M j, Y h:i A') }}
                            </div>
                        </div>
                        <div class="row small text-muted mt-2">
                            <div class="col-md-6">
                                <i class="fas fa-tag"></i> <strong>Type:</strong> {{ ucfirst($announcement->type) }}
                            </div>
                            <div class="col-md-6">
                                <i class="fas fa-users"></i> <strong>Target:</strong> {{ ucfirst(str_replace('_', ' ', $announcement->target_audience)) }}
                                @if($announcement->class_id)
                                    - {{ $announcement->targetClass->name }}
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="announcement-content">
                        {!! nl2br(e($announcement->content)) !!}
                    </div>

                    <!-- Attachment -->
                    @if($announcement->attachment_path)
                        <div class="mt-4 p-3 bg-light rounded">
                            <h6><i class="fas fa-paperclip"></i> Attachment</h6>
                            <a href="{{ Storage::url($announcement->attachment_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-download"></i> Download Attachment
                            </a>
                        </div>
                    @endif

                    <!-- Schedule Info -->
                    @if($announcement->scheduled_at && !$announcement->published_at)
                        <div class="alert alert-info mt-4">
                            <i class="fas fa-clock"></i> This announcement is scheduled to be published on
                            <strong>{{ $announcement->scheduled_at->format('F j, Y \a\t h:i A') }}</strong>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Actions -->
            @can('publish-announcements')
                @if($announcement->status == 'draft')
                    <div class="card mb-3">
                        <div class="card-body">
                            <h6 class="card-title">Publish Announcement</h6>
                            <p class="small text-muted">This announcement is currently in draft mode.</p>
                            <form action="{{ route('admin.announcements.publish', $announcement) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-success w-100" onclick="return confirm('Publish this announcement?')">
                                    <i class="fas fa-paper-plane"></i> Publish Now
                                </button>
                            </form>
                        </div>
                    </div>
                @endif
            @endcan

            <!-- Statistics -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-chart-bar"></i> Statistics</h6>
                </div>
                <div class="card-body">
                    <dl class="row mb-0 small">
                        <dt class="col-sm-6">Views:</dt>
                        <dd class="col-sm-6 text-end">{{ $announcement->view_count ?? 0 }}</dd>

                        <dt class="col-sm-6">Status:</dt>
                        <dd class="col-sm-6 text-end">
                            <span class="badge bg-{{ $announcement->status == 'published' ? 'success' : 'secondary' }}">
                                {{ ucfirst($announcement->status) }}
                            </span>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.announcement-content {
    font-size: 1.1rem;
    line-height: 1.8;
}
</style>
@endpush
