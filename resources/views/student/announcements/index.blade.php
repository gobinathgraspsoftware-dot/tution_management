@extends('layouts.app')

@section('title', 'Announcements')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="fas fa-bullhorn text-primary me-2"></i> Announcements</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Announcements</li>
                </ol>
            </nav>
        </div>
        @if($stats['unread'] > 0)
        <div>
            <form action="{{ route('student.announcements.mark-all-read') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-primary">
                    <i class="fas fa-check-double me-1"></i> Mark All as Read
                </button>
            </form>
        </div>
        @endif
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-opacity-10 rounded p-3">
                                <i class="fas fa-bullhorn text-primary fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Total</h6>
                            <h4 class="mb-0">{{ $stats['total'] }}</h4>
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
                            <div class="bg-warning bg-opacity-10 rounded p-3">
                                <i class="fas fa-envelope text-warning fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Unread</h6>
                            <h4 class="mb-0">{{ $stats['unread'] }}</h4>
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
                            <div class="bg-success bg-opacity-10 rounded p-3">
                                <i class="fas fa-envelope-open text-success fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Read</h6>
                            <h4 class="mb-0">{{ $stats['read'] }}</h4>
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
                            <div class="bg-danger bg-opacity-10 rounded p-3">
                                <i class="fas fa-exclamation-triangle text-danger fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Urgent</h6>
                            <h4 class="mb-0">{{ $stats['urgent'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('student.announcements.index') }}" method="GET" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Search announcements..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="priority" class="form-select">
                        <option value="">All Priorities</option>
                        <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                        <option value="normal" {{ request('priority') == 'normal' ? 'selected' : '' }}>Normal</option>
                        <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                        <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="type" class="form-select">
                        <option value="">All Types</option>
                        <option value="general" {{ request('type') == 'general' ? 'selected' : '' }}>General</option>
                        <option value="academic" {{ request('type') == 'academic' ? 'selected' : '' }}>Academic</option>
                        <option value="event" {{ request('type') == 'event' ? 'selected' : '' }}>Event</option>
                        <option value="holiday" {{ request('type') == 'holiday' ? 'selected' : '' }}>Holiday</option>
                        <option value="exam" {{ request('type') == 'exam' ? 'selected' : '' }}>Exam</option>
                        <option value="fee" {{ request('type') == 'fee' ? 'selected' : '' }}>Fee</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="read_status" class="form-select">
                        <option value="">All Status</option>
                        <option value="unread" {{ request('read_status') == 'unread' ? 'selected' : '' }}>Unread</option>
                        <option value="read" {{ request('read_status') == 'read' ? 'selected' : '' }}>Read</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <div class="btn-group w-100">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-1"></i> Filter
                        </button>
                        <a href="{{ route('student.announcements.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-redo me-1"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Announcements List -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            @if($announcements->count() > 0)
                <div class="list-group list-group-flush">
                    @foreach($announcements as $announcement)
                        <a href="{{ route('student.announcements.show', $announcement) }}"
                           class="list-group-item list-group-item-action {{ in_array($announcement->id, $readAnnouncementIds) ? '' : 'bg-light' }}">
                            <div class="d-flex w-100 align-items-start">
                                <!-- Icon -->
                                <div class="flex-shrink-0 me-3">
                                    <div class="rounded-circle p-2 {{ $announcement->priority === 'urgent' ? 'bg-danger' : ($announcement->priority === 'high' ? 'bg-warning' : 'bg-primary') }} bg-opacity-10">
                                        <i class="fas {{ $announcement->type === 'event' ? 'fa-calendar-alt' : ($announcement->type === 'exam' ? 'fa-file-alt' : 'fa-bullhorn') }} {{ $announcement->priority === 'urgent' ? 'text-danger' : ($announcement->priority === 'high' ? 'text-warning' : 'text-primary') }}"></i>
                                    </div>
                                </div>

                                <!-- Content -->
                                <div class="flex-grow-1">
                                    <div class="d-flex w-100 justify-content-between mb-2">
                                        <h6 class="mb-0">
                                            {{ $announcement->title }}
                                            @if(!in_array($announcement->id, $readAnnouncementIds))
                                                <span class="badge bg-primary ms-2">New</span>
                                            @endif
                                            @if($announcement->is_pinned)
                                                <i class="fas fa-thumbtack text-warning ms-2"></i>
                                            @endif
                                        </h6>
                                        <small class="text-muted">{{ $announcement->publish_at ? $announcement->publish_at->diffForHumans() : $announcement->created_at->diffForHumans() }}</small>
                                    </div>

                                    <p class="mb-2 text-muted">{{ \Str::limit(strip_tags($announcement->content), 150) }}</p>

                                    <div class="d-flex align-items-center gap-3">
                                        <small class="text-muted">
                                            <i class="fas fa-user me-1"></i> {{ $announcement->creator->name }}
                                        </small>

                                        @if($announcement->targetClass)
                                            <small class="text-muted">
                                                <i class="fas fa-chalkboard me-1"></i> {{ $announcement->targetClass->name }}
                                            </small>
                                        @endif

                                        <span class="badge bg-{{ $announcement->priority === 'urgent' ? 'danger' : ($announcement->priority === 'high' ? 'warning' : 'secondary') }}">
                                            {{ ucfirst($announcement->priority) }}
                                        </span>

                                        <span class="badge bg-info">
                                            {{ ucfirst($announcement->type) }}
                                        </span>

                                        @if($announcement->attachments && count($announcement->attachments) > 0)
                                            <small class="text-muted">
                                                <i class="fas fa-paperclip me-1"></i> {{ count($announcement->attachments) }} attachment(s)
                                            </small>
                                        @endif
                                    </div>
                                </div>

                                <!-- Arrow -->
                                <div class="flex-shrink-0 ms-3">
                                    <i class="fas fa-chevron-right text-muted"></i>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="p-3">
                    {{ $announcements->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-bullhorn fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No announcements found</h5>
                    <p class="text-muted">Check back later for new announcements</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
