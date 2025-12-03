<div class="btn-group btn-group-sm">
    <a href="{{ route('admin.exams.show', $exam) }}" class="btn btn-outline-info" title="View Details">
        <i class="fas fa-eye"></i>
    </a>

    @can('create-exam-results')
        <a href="{{ route('admin.exam-results.create', $exam) }}" class="btn btn-outline-success" title="Enter Results">
            <i class="fas fa-pen-square"></i>
        </a>
    @endcan

    @can('view-exam-results')
        <a href="{{ route('admin.exam-results.index', $exam) }}" class="btn btn-outline-primary" title="View Results">
            <i class="fas fa-list"></i>
        </a>
    @endcan

    @can('edit-exams')
        <a href="{{ route('admin.exams.edit', $exam) }}" class="btn btn-outline-warning" title="Edit">
            <i class="fas fa-edit"></i>
        </a>
    @endcan

    @can('delete-exams')
        @if($exam->results()->count() == 0)
            <form action="{{ route('admin.exams.destroy', $exam) }}" method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger" title="Delete"
                        onclick="return confirm('Are you sure you want to delete this exam?')">
                    <i class="fas fa-trash"></i>
                </button>
            </form>
        @endif
    @endcan
</div>
