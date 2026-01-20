@extends('layouts.app')

@section('title', 'Announcements')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Announcements</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('teacher.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Announcements</li>
                </ol>
            </nav>
        </div>
        <div>
            @if($unreadCount > 0)
                <form action="{{ route('teacher.announcements.mark-all-read') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary">
                        <i class="fas fa-check-double me-1"></i> Mark All Read
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if($unreadCount > 0)
        <div class="alert alert-info mb-4">
            <i class="fas fa-bell me-2"></i>
            You have <strong>{{ $unreadCount }}</strong> unread announcement(s).
        </div>
    @endif

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('teacher.announcements.index') }}" method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Type</label>
                    <select name="type" class="form-select">
                        <option value="">All Types</option>
                        <option value="class" {{ request('type') == 'class' ? 'selected' : '' }}>Class Announcements</option>
                        <option value="general" {{ request('type') == 'general' ? 'selected' : '' }}>General Announcements</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Priority</label>
                    <select name="priority" class="form-select">
                        <option value="">All Priorities</option>
                        <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                        <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                        <option value="normal" {{ request('priority') == 'normal' ? 'selected' : '' }}>Normal</option>
                        <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="read_status" class="form-select">
                        <option value="">All</option>
                        <option value="unread" {{ request('read_status') == 'unread' ? 'selected' : '' }}>Unread</option>
                        <option value="read" {{ request('read_status') == 'read' ? 'selected' : '' }}>Read</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search"></i> Filter
                    </button>
                    <a href="{{ route('teacher.announcements.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-undo"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Announcements List -->
    <div class="card">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-bullhorn me-2"></i>All Announcements</h5>
        </div>
        <div class="card-body">
            @if($announcements->isEmpty())
                <div class="text-center py-5">
                    <i class="fas fa-bullhorn fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted">No Announcements Found</h5>
                    <p class="text-muted">There are no announcements at the moment.</p>
                </div>
            @else
                <div class="list-group list-group-flush">
                    @foreach($announcements as $announcement)
                        @php
                            $isRead = in_array($announcement->id, $readIds);
                        @endphp
                        <a href="{{ route('teacher.announcements.show', $announcement) }}" 
                           class="list-group-item list-group-item-action {{ !$isRead ? 'bg-light' : '' }}">
                            <div class="d-flex w-100 justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center mb-2">
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
                                                <span class="badge bg-warning text-dark me-2">High</span>
                                                @break
                                            @case('normal')
                                                <span class="badge bg-info me-2">Normal</span>
                                                @break
                                            @case('low')
                                                <span class="badge bg-secondary me-2">Low</span>
                                                @break
                                        @endswitch
                                        @if($announcement->target_class_id)
                                            <span class="badge bg-primary me-2">
                                                <i class="fas fa-chalkboard me-1"></i>{{ $announcement->targetClass->name ?? 'Class' }}
                                            </span>
                                        @else
                                            <span class="badge bg-success me-2">
                                                <i class="fas fa-globe me-1"></i>General
                                            </span>
                                        @endif
                                        @if(!$isRead)
                                            <span class="badge bg-danger">New</span>
                                        @endif
                                    </div>
                                    <h5 class="mb-1 {{ !$isRead ? 'fw-bold' : '' }}">{{ $announcement->title }}</h5>
                                    <p class="mb-1 text-muted">
                                        {{ Str::limit(strip_tags($announcement->content), 150) }}
                                    </p>
                                    <small class="text-muted">
                                        <i class="fas fa-user me-1"></i>{{ $announcement->creator->name ?? 'System' }} |
                                        <i class="fas fa-clock me-1"></i>{{ $announcement->created_at->diffForHumans() }}
                                        @if($announcement->attachments)
                                            | <i class="fas fa-paperclip me-1"></i>Has Attachments
                                        @endif
                                    </small>
                                </div>
                                <div class="text-end">
                                    <i class="fas fa-chevron-right text-muted"></i>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>

                <div class="mt-3">
                    {{ $announcements->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
