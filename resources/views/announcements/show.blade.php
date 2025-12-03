@extends('layouts.app')

@section('title', $announcement->title)

@section('content')
<div class="container">
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('announcements.index') }}">Announcements</a></li>
                <li class="breadcrumb-item active">{{ Str::limit($announcement->title, 50) }}</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-body p-4">
                    <!-- Header -->
                    <div class="mb-4">
                        @if($announcement->is_pinned)
                            <i class="fas fa-thumbtack text-danger me-2"></i>
                        @endif
                        <span class="badge bg-{{ $announcement->priority == 'urgent' ? 'danger' : ($announcement->priority == 'high' ? 'warning' : 'primary') }} mb-2">
                            {{ ucfirst($announcement->priority) }} Priority
                        </span>
                        <h1 class="h2 mb-3">{{ $announcement->title }}</h1>
                        <div class="d-flex align-items-center text-muted small">
                            <i class="fas fa-user me-2"></i>
                            <span class="me-3">{{ $announcement->creator->name }}</span>
                            <i class="fas fa-calendar me-2"></i>
                            <span>{{ $announcement->published_at->format('F j, Y \a\t h:i A') }}</span>
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
                            <a href="{{ Storage::url($announcement->attachment_path) }}" target="_blank" class="btn btn-sm btn-primary">
                                <i class="fas fa-download"></i> Download
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <div class="mt-3 text-center">
                <a href="{{ route('announcements.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Announcements
                </a>
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
