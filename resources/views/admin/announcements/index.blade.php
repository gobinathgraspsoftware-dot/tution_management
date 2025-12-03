@extends('layouts.app')

@section('title', 'Announcements Management')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Announcements Management</h1>
            <p class="text-muted mb-0">Create and manage announcements</p>
        </div>
        @can('create-announcements')
            <a href="{{ route('admin.announcements.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create Announcement
            </a>
        @endcan
    </div>

    @include('admin.announcements._stats')

    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">All Announcements</h5>
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#filtersCollapse">
                    <i class="fas fa-filter"></i> Filters
                </button>
            </div>
        </div>
        <div class="card-body">
            @include('admin.announcements._filters')

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Type</th>
                            <th>Priority</th>
                            <th>Target</th>
                            <th>Status</th>
                            <th>Published</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($announcements as $announcement)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($announcement->is_pinned)
                                            <i class="fas fa-thumbtack text-danger me-2" title="Pinned"></i>
                                        @endif
                                        <div>
                                            <strong>{{ $announcement->title }}</strong>
                                            <div class="small text-muted">
                                                By {{ $announcement->creator->name }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ ucfirst($announcement->type) }}</span>
                                </td>
                                <td>
                                    <span class="badge
                                        @if($announcement->priority == 'urgent') bg-danger
                                        @elseif($announcement->priority == 'high') bg-warning
                                        @else bg-secondary
                                        @endif">
                                        {{ ucfirst($announcement->priority) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-primary">{{ ucfirst(str_replace('_', ' ', $announcement->target_audience)) }}</span>
                                    @if($announcement->class_id)
                                        <div class="small text-muted">{{ $announcement->targetClass->name }}</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $announcement->status == 'published' ? 'bg-success' : 'bg-secondary' }}">
                                        {{ ucfirst($announcement->status) }}
                                    </span>
                                </td>
                                <td>
                                    @if($announcement->published_at)
                                        <small>{{ $announcement->published_at->format('M j, Y') }}</small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.announcements.show', $announcement) }}" class="btn btn-outline-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @can('edit-announcements')
                                            <a href="{{ route('admin.announcements.edit', $announcement) }}" class="btn btn-outline-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endcan
                                        @can('publish-announcements')
                                            @if($announcement->status == 'draft')
                                                <form action="{{ route('admin.announcements.publish', $announcement) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-outline-success" title="Publish" onclick="return confirm('Publish this announcement?')">
                                                        <i class="fas fa-paper-plane"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        @endcan
                                        @can('delete-announcements')
                                            <form action="{{ route('admin.announcements.destroy', $announcement) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger" title="Delete" onclick="return confirm('Delete this announcement?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <i class="fas fa-inbox"></i> No announcements found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $announcements->links() }}
        </div>
    </div>
</div>
@endsection
