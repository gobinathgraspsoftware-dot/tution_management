{{-- Exam Statistics Cards --}}
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle p-3 me-3" style="background-color: #e3f2fd;">
                    <i class="fas fa-file-alt text-primary fa-lg"></i>
                </div>
                <div>
                    <h4 class="mb-0">{{ $stats['total'] ?? 0 }}</h4>
                    <small class="text-muted">Total Exams</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle p-3 me-3" style="background-color: #fff3e0;">
                    <i class="fas fa-calendar-check text-warning fa-lg"></i>
                </div>
                <div>
                    <h4 class="mb-0">{{ $stats['scheduled'] ?? 0 }}</h4>
                    <small class="text-muted">Scheduled</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle p-3 me-3" style="background-color: #e8f5e9;">
                    <i class="fas fa-check-circle text-success fa-lg"></i>
                </div>
                <div>
                    <h4 class="mb-0">{{ $stats['completed'] ?? 0 }}</h4>
                    <small class="text-muted">Completed</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle p-3 me-3" style="background-color: #fce4ec;">
                    <i class="fas fa-clock text-info fa-lg"></i>
                </div>
                <div>
                    <h4 class="mb-0">{{ $stats['upcoming'] ?? 0 }}</h4>
                    <small class="text-muted">Upcoming</small>
                </div>
            </div>
        </div>
    </div>
</div>
