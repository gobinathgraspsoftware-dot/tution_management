@extends('layouts.app')

@section('title', 'Announcements')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Announcements</h1>
            <p class="text-muted mb-0">View all announcements</p>
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

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Total</p>
                            <h3 class="mb-0">{{ $announcements->total() }}</h3>
                        </div>
                        <div class="bg-primary bg-opacity-10 rounded p-3">
                            <i class="fas fa-bullhorn fa-lg text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Unread</p>
                            <h3 class="mb-0">{{ $unreadCount }}</h3>
                        </div>
                        <div class="bg-danger bg-opacity-10 rounded p-3">
                            <i class="fas fa-envelope fa-lg text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Read</p>
                            <h3 class="mb-0">{{ $announcements->total() - $unreadCount }}</h3>
                        </div>
                        <div class="bg-success bg-opacity-10 rounded p-3">
                            <i class="fas fa-envelope-open fa-lg text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Pinned</p>
                            <h3 class="mb-0">{{ $announcements->where('is_pinned', true)->count() }}</h3>
                        </div>
                        <div class="bg-warning bg-opacity-10 rounded p-3">
                            <i class="fas fa-thumbtack fa-lg text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($unreadCount > 0)
        <div class="alert alert-info mb-4">
            <i class="fas fa-bell me-2"></i>
            You have <strong>{{ $unreadCount }}</strong> unread announcement(s).
        </div>
    @endif

    <!-- Announcements Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-bullhorn me-2"></i>All Announcements</h5>
            <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#filterSection">
                <i class="fas fa-filter me-1"></i> Filters
            </button>
        </div>
        
        <!-- Collapsible Filters -->
        <div class="collapse {{ request()->hasAny(['type', 'target', 'priority', 'read_status']) ? 'show' : '' }}" id="filterSection">
            <div class="card-body border-bottom bg-light">
                <form action="{{ route('teacher.announcements.index') }}" method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-select">
                            <option value="">All Types</option>
                            <option value="general" {{ request('type') == 'general' ? 'selected' : '' }}>General</option>
                            <option value="urgent" {{ request('type') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                            <option value="event" {{ request('type') == 'event' ? 'selected' : '' }}>Event</option>
                            <option value="class" {{ request('type') == 'class' ? 'selected' : '' }}>Class</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Target Audience</label>
                        <select name="target" class="form-select">
                            <option value="">All Targets</option>
                            <option value="all" {{ request('target') == 'all' ? 'selected' : '' }}>Everyone</option>
                            <option value="students" {{ request('target') == 'students' ? 'selected' : '' }}>Students</option>
                            <option value="parents" {{ request('target') == 'parents' ? 'selected' : '' }}>Parents</option>
                            <option value="teachers" {{ request('target') == 'teachers' ? 'selected' : '' }}>Teachers</option>
                            <option value="staff" {{ request('target') == 'staff' ? 'selected' : '' }}>Staff</option>
                            <option value="specific_class" {{ request('target') == 'specific_class' ? 'selected' : '' }}>Specific Class</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Priority</label>
                        <select name="priority" class="form-select">
                            <option value="">All</option>
                            <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                            <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                            <option value="normal" {{ request('priority') == 'normal' ? 'selected' : '' }}>Normal</option>
                            <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Read Status</label>
                        <select name="read_status" class="form-select">
                            <option value="">All</option>
                            <option value="unread" {{ request('read_status') == 'unread' ? 'selected' : '' }}>Unread</option>
                            <option value="read" {{ request('read_status') == 'read' ? 'selected' : '' }}>Read</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-search"></i>
                        </button>
                        <a href="{{ route('teacher.announcements.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-undo"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card-body p-0">
            @if($announcements->isEmpty())
                <div class="text-center py-5">
                    <i class="fas fa-bullhorn fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted">No Announcements Found</h5>
                    <p class="text-muted">There are no announcements at the moment.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Title</th>
                                <th>Type</th>
                                <th>Priority</th>
                                <th>Target</th>
                                <th>Posted</th>
                                <th>Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($announcements as $announcement)
                                @php
                                    $isRead = in_array($announcement->id, $readIds);
                                @endphp
                                <tr class="{{ !$isRead ? 'table-warning' : '' }}">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($announcement->is_pinned)
                                                <i class="fas fa-thumbtack text-warning me-2" title="Pinned"></i>
                                            @endif
                                            @if(!$isRead)
                                                <span class="badge bg-danger me-2">New</span>
                                            @endif
                                            <div>
                                                <strong class="{{ !$isRead ? 'fw-bold' : '' }}">{{ $announcement->title }}</strong>
                                                <br>
                                                <small class="text-muted">By {{ $announcement->creator->name ?? 'System' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $typeBadge = match($announcement->type) {
                                                'urgent' => 'bg-danger',
                                                'event' => 'bg-primary',
                                                'class' => 'bg-info',
                                                'general' => 'bg-success',
                                                default => 'bg-secondary'
                                            };
                                        @endphp
                                        <span class="badge {{ $typeBadge }}">{{ ucfirst($announcement->type ?? 'General') }}</span>
                                    </td>
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
                                            @default
                                                <span class="badge bg-secondary">{{ ucfirst($announcement->priority ?? 'Normal') }}</span>
                                        @endswitch
                                    </td>
                                    <td>
                                        @php
                                            $targetBadge = match($announcement->target_audience) {
                                                'all' => 'bg-success',
                                                'students' => 'bg-info',
                                                'parents' => 'bg-primary',
                                                'teachers' => 'bg-warning text-dark',
                                                'staff' => 'bg-secondary',
                                                'specific_class' => 'bg-dark',
                                                default => 'bg-secondary'
                                            };
                                            $targetLabel = match($announcement->target_audience) {
                                                'all' => 'Everyone',
                                                'students' => 'Students',
                                                'parents' => 'Parents',
                                                'teachers' => 'Teachers',
                                                'staff' => 'Staff',
                                                'specific_class' => $announcement->targetClass->name ?? 'Specific Class',
                                                default => ucfirst($announcement->target_audience ?? 'All')
                                            };
                                        @endphp
                                        <span class="badge {{ $targetBadge }}">{{ $targetLabel }}</span>
                                    </td>
                                    <td>
                                        <small>{{ $announcement->created_at->format('d M Y') }}</small>
                                        <br>
                                        <small class="text-muted">{{ $announcement->created_at->diffForHumans() }}</small>
                                    </td>
                                    <td>
                                        @if($isRead)
                                            <span class="badge bg-success">Read</span>
                                        @else
                                            <span class="badge bg-warning text-dark">Unread</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('teacher.announcements.show', $announcement) }}" 
                                           class="btn btn-sm btn-outline-primary" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="card-footer bg-white">
                    {{ $announcements->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
