<div class="card h-100 announcement-card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <span class="badge bg-{{ $announcement->priority == 'urgent' ? 'danger' : ($announcement->priority == 'high' ? 'warning' : 'primary') }}">
                {{ ucfirst($announcement->type) }}
            </span>
            @if($announcement->is_pinned)
                <i class="fas fa-thumbtack text-danger"></i>
            @endif
        </div>

        <h5 class="card-title">
            <a href="{{ route('announcements.show', $announcement) }}" class="text-decoration-none text-dark">
                {{ Str::limit($announcement->title, 60) }}
            </a>
        </h5>

        <p class="card-text text-muted small">
            {{ Str::limit(strip_tags($announcement->content), 120) }}
        </p>

        <div class="d-flex justify-content-between align-items-center mt-3">
            <small class="text-muted">
                <i class="fas fa-calendar"></i>
                {{ $announcement->published_at->diffForHumans() }}
            </small>
            <a href="{{ route('announcements.show', $announcement) }}" class="btn btn-sm btn-outline-primary">
                Read More <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</div>

<style>
.announcement-card {
    transition: transform 0.2s, box-shadow 0.2s;
}
.announcement-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
</style>
