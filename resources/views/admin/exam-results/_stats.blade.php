{{-- Exam Result Statistics Cards --}}
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle p-3 me-3" style="background-color: #e3f2fd;">
                    <i class="fas fa-users text-primary fa-lg"></i>
                </div>
                <div>
                    <h4 class="mb-0">{{ $stats['total_students'] ?? 0 }}</h4>
                    <small class="text-muted">Total Students</small>
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
                    <h4 class="mb-0">{{ $stats['results_entered'] ?? 0 }}</h4>
                    <small class="text-muted">Results Entered</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle p-3 me-3" style="background-color: #fff3e0;">
                    <i class="fas fa-chart-line text-warning fa-lg"></i>
                </div>
                <div>
                    <h4 class="mb-0">{{ $stats['average_marks'] ? number_format($stats['average_marks'], 1) : '0' }}</h4>
                    <small class="text-muted">Average Marks</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle p-3 me-3" style="background-color: #fce4ec;">
                    <i class="fas fa-percentage text-danger fa-lg"></i>
                </div>
                <div>
                    @php
                        $passRate = ($stats['results_entered'] ?? 0) > 0 
                            ? round((($stats['pass_count'] ?? 0) / $stats['results_entered']) * 100, 1) 
                            : 0;
                    @endphp
                    <h4 class="mb-0">{{ $passRate }}%</h4>
                    <small class="text-muted">Pass Rate</small>
                </div>
            </div>
        </div>
    </div>
</div>
