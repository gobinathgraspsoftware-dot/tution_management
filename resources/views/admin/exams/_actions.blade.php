{{-- Exam Row Actions Dropdown --}}
<div class="dropdown">
    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="fas fa-ellipsis-v"></i>
    </button>
    <ul class="dropdown-menu dropdown-menu-end">
        <li>
            <a class="dropdown-item" href="{{ route('admin.exams.show', $exam) }}">
                <i class="fas fa-eye me-2 text-info"></i> View Details
            </a>
        </li>
        <li>
            <a class="dropdown-item" href="{{ route('admin.exams.edit', $exam) }}">
                <i class="fas fa-edit me-2 text-primary"></i> Edit Exam
            </a>
        </li>
        <li><hr class="dropdown-divider"></li>
        @if(Route::has('admin.exam-results.index'))
        <li>
            <a class="dropdown-item" href="{{ route('admin.exam-results.index', $exam) }}">
                <i class="fas fa-clipboard-list me-2 text-success"></i> View Results
            </a>
        </li>
        @endif
        @if(Route::has('admin.exam-results.create'))
        <li>
            <a class="dropdown-item" href="{{ route('admin.exam-results.create', $exam) }}">
                <i class="fas fa-plus-circle me-2 text-warning"></i> Enter Results
            </a>
        </li>
        @endif
        @if(Route::has('admin.exam-results.statistics'))
        <li>
            <a class="dropdown-item" href="{{ route('admin.exam-results.statistics', $exam) }}">
                <i class="fas fa-chart-bar me-2 text-info"></i> Statistics
            </a>
        </li>
        @endif
        <li><hr class="dropdown-divider"></li>
        @if(Route::has('admin.exams.duplicate'))
        <li>
            <form action="{{ route('admin.exams.duplicate', $exam) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="dropdown-item">
                    <i class="fas fa-copy me-2 text-secondary"></i> Duplicate
                </button>
            </form>
        </li>
        @endif
        @if(Route::has('admin.exam-results.export'))
        <li>
            <a class="dropdown-item" href="{{ route('admin.exam-results.export', $exam) }}">
                <i class="fas fa-download me-2 text-success"></i> Export Results
            </a>
        </li>
        @endif
        <li><hr class="dropdown-divider"></li>
        <li>
            <button type="button" class="dropdown-item text-danger" 
                    data-bs-toggle="modal" 
                    data-bs-target="#deleteExamModal"
                    data-id="{{ $exam->id }}"
                    data-name="{{ $exam->name }}">
                <i class="fas fa-trash-alt me-2"></i> Delete
            </button>
        </li>
    </ul>
</div>
