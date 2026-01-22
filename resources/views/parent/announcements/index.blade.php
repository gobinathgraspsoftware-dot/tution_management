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
                    <li class="breadcrumb-item"><a href="{{ route('parent.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Announcements</li>
                </ol>
            </nav>
        </div>
        <div>
            @if($unreadCount > 0)
            <form action="{{ route('parent.announcements.mark-all-read') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-primary">
                    <i class="fas fa-check-double me-1"></i> Mark All as Read
                </button>
            </form>
            @endif
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 text-white-50">Total Announcements</h6>
                            <h3 class="mb-0">{{ $announcements->total() }}</h3>
                        </div>
                        <i class="fas fa-bullhorn fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-warning text-dark">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 opacity-75">Unread</h6>
                            <h3 class="mb-0">{{ $unreadCount }}</h3>
                        </div>
                        <i class="fas fa-envelope fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 text-white-50">Read</h6>
                            <h3 class="mb-0">{{ $announcements->total() - $unreadCount }}</h3>
                        </div>
                        <i class="fas fa-envelope-open fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card shadow-sm mb-4">
        <div class="card-body py-3">
            <form action="{{ route('parent.announcements.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Type</label>
                    <select name="type" class="form-select">
                        <option value="">All Types</option>
                        <option value="general" {{ request('type') == 'general' ? 'selected' : '' }}>General</option>
                        <option value="academic" {{ request('type') == 'academic' ? 'selected' : '' }}>Academic</option>
                        <option value="event" {{ request('type') == 'event' ? 'selected' : '' }}>Event</option>
                        <option value="holiday" {{ request('type') == 'holiday' ? 'selected' : '' }}>Holiday</option>
                        <option value="emergency" {{ request('type') == 'emergency' ? 'selected' : '' }}>Emergency</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Priority</label>
                    <select name="priority" class="form-select">
                        <option value="">All Priorities</option>
                        <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                        <option value="normal" {{ request('priority') == 'normal' ? 'selected' : '' }}>Normal</option>
                        <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                        <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="read_status" class="form-select">
                        <option value="">All</option>
                        <option value="unread" {{ request('read_status') == 'unread' ? 'selected' : '' }}>Unread Only</option>
                        <option value="read" {{ request('read_status') == 'read' ? 'selected' : '' }}>Read Only</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-1"></i> Filter
                    </button>
                    <a href="{{ route('parent.announcements.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-redo me-1"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Announcements List -->
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-bullhorn me-2"></i>All Announcements</h5>
        </div>
        <div class="card-body p-0">
            @forelse($announcements as $announcement)
            <div class="announcement-item border-bottom p-3 {{ !in_array($announcement->id, $readIds) ? 'bg-light' : '' }}">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center mb-2">
                            {{-- Pinned Icon --}}
                            @if($announcement->is_pinned)
                            <i class="fas fa-thumbtack text-danger me-2" title="Pinned"></i>
                            @endif

                            {{-- Unread Indicator --}}
                            @if(!in_array($announcement->id, $readIds))
                            <span class="badge bg-primary me-2">NEW</span>
                            @endif

                            {{-- Priority Badge --}}
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

                            {{-- Type Badge --}}
                            @switch($announcement->type)
                                @case('emergency')
                                    <span class="badge bg-danger me-2">Emergency</span>
                                    @break
                                @case('event')
                                    <span class="badge bg-success me-2">Event</span>
                                    @break
                                @case('academic')
                                    <span class="badge bg-primary me-2">Academic</span>
                                    @break
                                @case('holiday')
                                    <span class="badge bg-info me-2">Holiday</span>
                                    @break
                                @default
                                    <span class="badge bg-secondary me-2">General</span>
                            @endswitch
                        </div>

                        <h5 class="mb-1">
                            <a href="{{ route('parent.announcements.show', $announcement) }}" class="text-decoration-none text-dark">
                                {{ $announcement->title }}
                            </a>
                        </h5>

                        <p class="text-muted mb-2">
                            {{ Str::limit(strip_tags($announcement->content), 150) }}
                        </p>

                        <div class="d-flex align-items-center text-muted small">
                            <span class="me-3">
                                <i class="fas fa-user me-1"></i>
                                {{ $announcement->creator->name ?? 'System' }}
                            </span>
                            <span class="me-3">
                                <i class="fas fa-calendar me-1"></i>
                                {{ $announcement->created_at->format('d M Y, h:i A') }}
                            </span>
                            @if($announcement->targetClass)
                            <span class="me-3">
                                <i class="fas fa-users me-1"></i>
                                {{ $announcement->targetClass->name }}
                            </span>
                            @endif
                            @if($announcement->attachments && count($announcement->attachments) > 0)
                            <span>
                                <i class="fas fa-paperclip me-1"></i>
                                {{ count($announcement->attachments) }} file(s)
                            </span>
                            @endif
                        </div>
                    </div>

                    <div class="ms-3">
                        <a href="{{ route('parent.announcements.show', $announcement) }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-eye me-1"></i> View
                        </a>
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center py-5">
                <i class="fas fa-bullhorn fa-4x text-muted mb-3"></i>
                <h5>No Announcements</h5>
                <p class="text-muted">There are no announcements at the moment.</p>
            </div>
            @endforelse
        </div>

        @if($announcements->hasPages())
        <div class="card-footer bg-white">
            {{ $announcements->links() }}
        </div>
        @endif
    </div>
</div>

<style>
.announcement-item:hover {
    background-color: #f8f9fa !important;
}
.announcement-item.bg-light {
    border-left: 4px solid #0d6efd !important;
}
</style>
@endsection
