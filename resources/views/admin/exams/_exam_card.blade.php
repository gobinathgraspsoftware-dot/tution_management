<div class="card h-100 exam-card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <span class="badge
                @if($exam->status == 'completed') bg-success
                @elseif($exam->status == 'ongoing') bg-warning
                @elseif($exam->status == 'cancelled') bg-danger
                @else bg-primary
                @endif">
                {{ ucfirst($exam->status) }}
            </span>
            <span class="badge bg-info">{{ $exam->subject->name }}</span>
        </div>

        <h5 class="card-title mb-2">{{ $exam->name }}</h5>
        <p class="text-muted small mb-3">{{ $exam->class->name }}</p>

        <div class="exam-details mb-3">
            <div class="d-flex align-items-center mb-2">
                <i class="fas fa-calendar text-muted me-2"></i>
                <span class="small">{{ \Carbon\Carbon::parse($exam->exam_date)->format('M j, Y') }}</span>
            </div>
            <div class="d-flex align-items-center mb-2">
                <i class="fas fa-clock text-muted me-2"></i>
                <span class="small">
                    {{ \Carbon\Carbon::parse($exam->start_time)->format('h:i A') }} -
                    {{ \Carbon\Carbon::parse($exam->end_time)->format('h:i A') }}
                </span>
            </div>
            <div class="d-flex align-items-center">
                <i class="fas fa-star text-muted me-2"></i>
                <span class="small">Max Marks: {{ $exam->max_marks }} | Pass: {{ $exam->passing_marks }}</span>
            </div>
        </div>

        <div class="d-flex gap-2">
            @if(auth()->user()->hasRole(['super-admin', 'admin', 'staff']))
                <a href="{{ route('admin.exams.show', $exam) }}" class="btn btn-sm btn-outline-primary flex-grow-1">
                    <i class="fas fa-eye"></i> View
                </a>
                @can('create-exam-results')
                    <a href="{{ route('admin.exam-results.create', $exam) }}" class="btn btn-sm btn-outline-success">
                        <i class="fas fa-pen-square"></i>
                    </a>
                @endcan
            @elseif(auth()->user()->hasRole('student'))
                @php
                    $result = $exam->results()->where('student_id', auth()->user()->student->id)->first();
                @endphp
                @if($result && $result->is_published)
                    <a href="{{ route('student.exam-results.show', $result) }}" class="btn btn-sm btn-primary w-100">
                        <i class="fas fa-chart-line"></i> View Result
                    </a>
                @else
                    <button class="btn btn-sm btn-secondary w-100" disabled>
                        <i class="fas fa-hourglass-half"></i> Result Pending
                    </button>
                @endif
            @endif
        </div>
    </div>
</div>

<style>
.exam-card {
    transition: transform 0.2s, box-shadow 0.2s;
}
.exam-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
</style>
