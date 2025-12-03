@extends('layouts.app')

@section('title', 'Enter Exam Results')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.exams.index') }}">Exams</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.exam-results.index', $exam) }}">Results</a></li>
                <li class="breadcrumb-item active">Enter Results</li>
            </ol>
        </nav>
        <h1 class="h3">Enter Results - {{ $exam->name }}</h1>
        <p class="text-muted">{{ $exam->class->name }} | {{ $exam->subject->name }} | Max Marks: {{ $exam->max_marks }}</p>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.exam-results.bulk-store', $exam) }}" method="POST" id="resultsForm">
                @csrf
                @include('admin.exam-results._bulk-entry-form')

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save All Results
                    </button>
                    <button type="button" class="btn btn-success" onclick="document.getElementById('publishResults').value='1'; document.getElementById('resultsForm').submit();">
                        <i class="fas fa-paper-plane"></i> Save & Publish
                    </button>
                    <a href="{{ route('admin.exam-results.index', $exam) }}" class="btn btn-outline-secondary">
                        Cancel
                    </a>
                </div>
                <input type="hidden" name="publish_results" id="publishResults" value="0">
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Auto-calculate percentage and grade
function calculateResult(index) {
    const marksInput = document.getElementById('marks_' + index);
    const marks = parseFloat(marksInput.value) || 0;
    const maxMarks = {{ $exam->max_marks }};

    if (marks > maxMarks) {
        alert('Marks cannot exceed maximum marks!');
        marksInput.value = '';
        return;
    }

    // Calculate using AJAX
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
        document.getElementById('percentage_' + index).textContent = data.percentage + '%';
        document.getElementById('grade_' + index).textContent = data.grade;
    })
    .catch(error => console.error('Error:', error));
}

// Quick fill for all students
function quickFillAll() {
    const marks = prompt('Enter marks for all students:');
    if (marks !== null && marks !== '') {
        const inputs = document.querySelectorAll('input[name^="results"][name$="[marks_obtained]"]');
        inputs.forEach((input, index) => {
            input.value = marks;
            calculateResult(index);
        });
    }
}
</script>
@endpush
