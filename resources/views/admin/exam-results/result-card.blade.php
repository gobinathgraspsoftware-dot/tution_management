@extends('layouts.app')

@section('title', 'Result Card')

@section('content')
<div class="container">
    <div class="mb-4 text-center">
        <a href="{{ route('admin.exam-results.download-result-card', $result) }}" class="btn btn-primary">
            <i class="fas fa-download"></i> Download PDF
        </a>
        <button onclick="window.print()" class="btn btn-outline-primary">
            <i class="fas fa-print"></i> Print
        </button>
    </div>

    <div class="card result-card">
        <div class="card-body p-5">
            <!-- Header -->
            <div class="text-center mb-4">
                <h2 class="mb-2">ARENA MATRIKS EDU GROUP</h2>
                <h4 class="text-muted">EXAMINATION RESULT CARD</h4>
                <hr class="my-4">
            </div>

            <!-- Student & Exam Info -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <th style="width: 40%;">Student Name:</th>
                            <td><strong>{{ $result->student->user->name }}</strong></td>
                        </tr>
                        <tr>
                            <th>Student ID:</th>
                            <td>{{ $result->student->student_id }}</td>
                        </tr>
                        <tr>
                            <th>Class:</th>
                            <td>{{ $result->exam->class->name }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <th style="width: 40%;">Exam Name:</th>
                            <td>{{ $result->exam->name }}</td>
                        </tr>
                        <tr>
                            <th>Subject:</th>
                            <td>{{ $result->exam->subject->name }}</td>
                        </tr>
                        <tr>
                            <th>Exam Date:</th>
                            <td>{{ \Carbon\Carbon::parse($result->exam->exam_date)->format('F j, Y') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Results -->
            <div class="card bg-light mb-4">
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <h6 class="text-muted mb-2">Marks Obtained</h6>
                            <h3 class="mb-0 text-primary">{{ $result->marks_obtained }}/{{ $result->exam->max_marks }}</h3>
                        </div>
                        <div class="col-md-3">
                            <h6 class="text-muted mb-2">Percentage</h6>
                            <h3 class="mb-0 text-success">{{ number_format($result->percentage, 2) }}%</h3>
                        </div>
                        <div class="col-md-3">
                            <h6 class="text-muted mb-2">Grade</h6>
                            <h3 class="mb-0 text-info">{{ $result->grade }}</h3>
                        </div>
                        <div class="col-md-3">
                            <h6 class="text-muted mb-2">Rank</h6>
                            <h3 class="mb-0 text-warning">{{ $result->rank }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status Badge -->
            <div class="text-center mb-4">
                @if($result->marks_obtained >= $result->exam->passing_marks)
                    <span class="badge bg-success p-3" style="font-size: 1.5rem;">
                        <i class="fas fa-check-circle"></i> PASSED
                    </span>
                @else
                    <span class="badge bg-danger p-3" style="font-size: 1.5rem;">
                        <i class="fas fa-times-circle"></i> NEEDS IMPROVEMENT
                    </span>
                @endif
            </div>

            <!-- Remarks -->
            @if($result->remarks)
                <div class="mb-4">
                    <h6>Remarks:</h6>
                    <p class="text-muted">{{ $result->remarks }}</p>
                </div>
            @endif

            <!-- Footer -->
            <div class="row mt-5 pt-4 border-top">
                <div class="col-md-6">
                    <p class="mb-0"><strong>Published Date:</strong> {{ $result->published_at->format('F j, Y') }}</p>
                </div>
                <div class="col-md-6 text-end">
                    <p class="mb-0"><strong>Signature:</strong> ___________________</p>
                    <small class="text-muted">Authorized Signatory</small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
@media print {
    .btn, .breadcrumb { display: none !important; }
    .result-card { border: none !important; box-shadow: none !important; }
}
</style>
@endpush
