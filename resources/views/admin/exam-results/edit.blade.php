@extends('layouts.app')

@section('title', 'Edit Result')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.exams.index') }}">Exams</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.exam-results.index', $result->exam) }}">Results</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </nav>
        <h1 class="h3">Edit Result</h1>
        <p class="text-muted">{{ $result->student->user->name }} - {{ $result->exam->name }}</p>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.exam-results.update', $result) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">Student</label>
                            <input type="text" class="form-control" value="{{ $result->student->user->name }}" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Exam</label>
                            <input type="text" class="form-control" value="{{ $result->exam->name }}" readonly>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="marks_obtained" class="form-label">Marks Obtained <span class="text-danger">*</span></label>
                                <input type="number" name="marks_obtained" id="marks_obtained"
                                       class="form-control @error('marks_obtained') is-invalid @enderror"
                                       value="{{ old('marks_obtained', $result->marks_obtained) }}"
                                       min="0" max="{{ $result->exam->max_marks }}" step="0.01" required
                                       onchange="calculateResult()">
                                <small class="text-muted">Max Marks: {{ $result->exam->max_marks }}</small>
                                @error('marks_obtained')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Percentage & Grade</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="percentage_display"
                                           value="{{ number_format($result->percentage, 2) }}%" readonly>
                                    <input type="text" class="form-control" id="grade_display"
                                           value="{{ $result->grade }}" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="remarks" class="form-label">Remarks</label>
                            <textarea name="remarks" id="remarks" rows="3" class="form-control @error('remarks') is-invalid @enderror">{{ old('remarks', $result->remarks) }}</textarea>
                            @error('remarks')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Result
                            </button>
                            <a href="{{ route('admin.exam-results.index', $result->exam) }}" class="btn btn-outline-secondary">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Result Information</h6>
                </div>
                <div class="card-body">
                    <dl class="row mb-0 small">
                        <dt class="col-sm-6">Entered by:</dt>
                        <dd class="col-sm-6">{{ $result->created_at ? 'System' : 'N/A' }}</dd>

                        <dt class="col-sm-6">Entered at:</dt>
                        <dd class="col-sm-6">{{ $result->created_at->format('M j, Y h:i A') }}</dd>

                        <dt class="col-sm-6">Last updated:</dt>
                        <dd class="col-sm-6">{{ $result->updated_at->format('M j, Y h:i A') }}</dd>

                        <dt class="col-sm-6">Published:</dt>
                        <dd class="col-sm-6">
                            @if($result->is_published)
                                <span class="badge bg-success">Yes</span>
                                <br><small>{{ $result->published_at->format('M j, Y') }}</small>
                            @else
                                <span class="badge bg-warning">No</span>
                            @endif
                        </dd>
                    </dl>
                </div>
            </div>

            @can('delete-exam-results')
                <div class="card mt-3">
                    <div class="card-body">
                        <h6 class="text-danger"><i class="fas fa-exclamation-triangle"></i> Danger Zone</h6>
                        <p class="small text-muted">Delete this result permanently.</p>
                        <form action="{{ route('admin.exam-results.destroy', $result) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm w-100"
                                    onclick="return confirm('Are you sure you want to delete this result?')">
                                <i class="fas fa-trash"></i> Delete Result
                            </button>
                        </form>
                    </div>
                </div>
            @endcan
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function calculateResult() {
    const marks = parseFloat(document.getElementById('marks_obtained').value) || 0;
    const maxMarks = {{ $result->exam->max_marks }};

    fetch('{{ route("admin.exam-results.auto-calculate") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            marks_obtained: marks,
            max_marks: maxMarks
        })
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('percentage_display').value = data.percentage + '%';
        document.getElementById('grade_display').value = data.grade;
    });
}
</script>
@endpush
