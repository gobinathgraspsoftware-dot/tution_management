@extends('layouts.app')

@section('title', 'Announcements')
@section('page-title', 'Announcements')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="fas fa-bullhorn me-2"></i> Announcements</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('staff.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Announcements</li>
            </ol>
        </nav>
    </div>
    <div>
        @if($stats['unread'] > 0)
        <form action="{{ route('staff.announcements.mark-all-read') }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-outline-primary">
                <i class="fas fa-check-double me-1"></i> Mark All as Read
            </button>
        </form>
        @endif
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3 col-6">
        <div class="card border-0 bg-primary bg-opacity-10">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">Total</div>
                        <h4 class="mb-0 text-primary">{{ $stats['total'] }}</h4>
                    </div>
                    <i class="fas fa-bullhorn fa-2x text-primary opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card border-0 bg-warning bg-opacity-10">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">Unread</div>
                        <h4 class="mb-0 text-warning">{{ $stats['unread'] }}</h4>
                    </div>
                    <i class="fas fa-envelope fa-2x text-warning opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card border-0 bg-danger bg-opacity-10">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">Urgent</div>
                        <h4 class="mb-0 text-danger">{{ $stats['urgent'] }}</h4>
                    </div>
                    <i class="fas fa-exclamation-triangle fa-2x text-danger opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card border-0 bg-info bg-opacity-10">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">Pinned</div>
                        <h4 class="mb-0 text-info">{{ $stats['pinned'] }}</h4>
                    </div>
                    <i class="fas fa-thumbtack fa-2x text-info opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('staff.announcements.index') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Search title..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Type</label>
                <select name="type" class="form-select">
                    <option value="">All Types</option>
                    <option value="general" {{ request('type') == 'general' ? 'selected' : '' }}>General</option>
                    <option value="academic" {{ request('type') == 'academic' ? 'selected' : '' }}>Academic</option>
                    <option value="event" {{ request('type') == 'event' ? 'selected' : '' }}>Event</option>
                    <option value="holiday" {{ request('type') == 'holiday' ? 'selected' : '' }}>Holiday</option>
                    <option value="payment" {{ request('type') == 'payment' ? 'selected' : '' }}>Payment</option>
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
                <label class="form-label">Status</label>
                <select name="read_status" class="form-select">
                    <option value="">All</option>
                    <option value="unread" {{ request('read_status') == 'unread' ? 'selected' : '' }}>Unread</option>
                    <option value="read" {{ request('read_status') == 'read' ? 'selected' : '' }}>Read</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search me-1"></i> Filter
                </button>
                <a href="{{ route('staff.announcements.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-1"></i> Clear
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Announcements Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th width="5%"></th>
                        <th width="35%">Title</th>
                        <th width="12%">Type</th>
                        <th width="12%">Priority</th>
                        <th width="15%">Target</th>
                        <th width="15%">Date</th>
                        <th width="6%" class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($announcements as $announcement)
                        <tr class="{{ in_array($announcement->id, $readAnnouncementIds) ? '' : 'table-warning' }}">
                            <!-- Status Indicators -->
                            <td class="text-center">
                                @if(!in_array($announcement->id, $readAnnouncementIds))
                                    <span class="badge bg-primary rounded-pill" title="Unread" style="width: 8px; height: 8px; padding: 0;"></span>
                                @endif
                                @if($announcement->is_pinned)
                                    <i class="fas fa-thumbtack text-info" title="Pinned"></i>
                                @endif
                            </td>

                            <!-- Title -->
                            <td>
                                <a href="{{ route('staff.announcements.show', $announcement) }}" class="text-decoration-none text-dark">
                                    <strong>{{ Str::limit($announcement->title, 50) }}</strong>
                                </a>
                                @if($announcement->attachments && count($announcement->attachments) > 0)
                                    <i class="fas fa-paperclip text-muted ms-1" title="{{ count($announcement->attachments) }} attachment(s)"></i>
                                @endif
                                <div class="text-muted small">
                                    {{ Str::limit(strip_tags($announcement->content), 60) }}
                                </div>
                            </td>

                            <!-- Type -->
                            <td>
                                <span class="badge
                                    @switch($announcement->type)
                                        @case('general') bg-primary @break
                                        @case('academic') bg-success @break
                                        @case('event') bg-info @break
                                        @case('holiday') bg-warning text-dark @break
                                        @case('payment') bg-danger @break
                                        @default bg-secondary
                                    @endswitch">
                                    {{ ucfirst($announcement->type) }}
                                </span>
                            </td>

                            <!-- Priority -->
                            <td>
                                @switch($announcement->priority)
                                    @case('urgent')
                                        <span class="badge bg-danger">
                                            <i class="fas fa-exclamation-circle me-1"></i>Urgent
                                        </span>
                                        @break
                                    @case('high')
                                        <span class="badge bg-warning text-dark">High</span>
                                        @break
                                    @case('normal')
                                        <span class="badge bg-secondary">Normal</span>
                                        @break
                                    @default
                                        <span class="badge bg-light text-dark">Low</span>
                                @endswitch
                            </td>

                            <!-- Target -->
                            <td>
                                @if($announcement->targetClass)
                                    <small><i class="fas fa-users me-1"></i>{{ $announcement->targetClass->name }}</small>
                                @else
                                    <small><i class="fas fa-globe me-1"></i>{{ ucfirst(str_replace('_', ' ', $announcement->target_audience)) }}</small>
                                @endif
                            </td>

                            <!-- Date -->
                            <td>
                                <small>
                                    {{ $announcement->created_at->format('d M Y') }}<br>
                                    <span class="text-muted">{{ $announcement->created_at->format('h:i A') }}</span>
                                </small>
                            </td>

                            <!-- Action -->
                            <td class="text-center">
                                <a href="{{ route('staff.announcements.show', $announcement) }}"
                                   class="btn btn-sm btn-outline-primary" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <i class="fas fa-bullhorn fa-3x text-muted mb-3"></i>
                                <p class="text-muted mb-0">No announcements found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="text-muted small">
                Showing {{ $announcements->firstItem() ?? 0 }} to {{ $announcements->lastItem() ?? 0 }} of {{ $announcements->total() }} announcements
            </div>
            {{ $announcements->links() }}
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.table tbody tr {
    cursor: pointer;
}
.table tbody tr:hover {
    background-color: #f8f9fa;
}
.badge.rounded-pill {
    display: inline-block;
}
</style>
@endpush

@push('scripts')
<script>
// Make entire row clickable
document.querySelectorAll('tbody tr').forEach(row => {
    row.addEventListener('click', function(e) {
        // Don't navigate if clicking on action button
        if (e.target.closest('a') || e.target.closest('button')) return;

        const link = this.querySelector('td:nth-child(2) a');
        if (link) {
            window.location.href = link.href;
        }
    });
});
</script>
@endpush
