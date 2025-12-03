@extends('layouts.app')

@section('title', 'Announcements')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h1 class="h3">Announcements</h1>
        <p class="text-muted">Stay updated with the latest news and information</p>
    </div>

    <!-- Pinned Announcements -->
    @php
        $pinnedAnnouncements = App\Models\Announcement::published()->where('is_pinned', true)->latest()->get();
    @endphp

    @if($pinnedAnnouncements->count() > 0)
        <div class="mb-4">
            <h5 class="mb-3"><i class="fas fa-thumbtack text-danger"></i> Pinned Announcements</h5>
            <div class="row">
                @foreach($pinnedAnnouncements as $announcement)
                    <div class="col-md-6 mb-3">
                        @include('components._announcement_card', ['announcement' => $announcement])
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Recent Announcements -->
    <h5 class="mb-3">Recent Announcements</h5>
    @php
        $announcements = App\Models\Announcement::published()
            ->where('is_pinned', false)
            ->when(auth()->user()->hasRole('student'), function($q) {
                $q->where(function($query) {
                    $query->whereIn('target_audience', ['all', 'students'])
                        ->orWhere(function($q2) {
                            $q2->where('target_audience', 'specific_class')
                               ->whereIn('class_id', auth()->user()->student->enrollments()->pluck('class_id'));
                        });
                });
            })
            ->when(auth()->user()->hasRole('parent'), function($q) {
                $q->whereIn('target_audience', ['all', 'parents']);
            })
            ->when(auth()->user()->hasRole('teacher'), function($q) {
                $q->whereIn('target_audience', ['all', 'teachers']);
            })
            ->latest('published_at')
            ->paginate(12);
    @endphp

    <div class="row">
        @forelse($announcements as $announcement)
            <div class="col-md-6 col-lg-4 mb-4">
                @include('components._announcement_card', ['announcement' => $announcement])
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle"></i> No announcements available at this time.
                </div>
            </div>
        @endforelse
    </div>

    {{ $announcements->links() }}
</div>
@endsection
