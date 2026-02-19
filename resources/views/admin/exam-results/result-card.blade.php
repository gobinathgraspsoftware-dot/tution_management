@extends('layouts.app')

@section('title', 'Result Card - ' . ($result->student->user->name ?? 'Student'))

@section('content')
<div class="container-fluid">
    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="fas fa-id-card me-2"></i>Result Card</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.exams.index') }}">Exams</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.exam-results.index', $result->exam) }}">{{ $result->exam->name }}</a></li>
                    <li class="breadcrumb-item active">Result Card</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            @if(Route::has('admin.exam-results.download'))
                <a href="{{ route('admin.exam-results.download', $result) }}" class="btn btn-success" target="_blank">
                    <i class="fas fa-download me-1"></i> Download PDF
                </a>
            @endif
            <button onclick="window.print()" class="btn btn-outline-primary">
                <i class="fas fa-print me-1"></i> Print
            </button>
            <a href="{{ route('admin.exam-results.index', $result->exam) }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    {{-- Result Card --}}
    <div class="card border-0 shadow-sm" id="resultCard">
        <div class="card-body p-4">
            {{-- Header --}}
            <div class="text-center mb-4 border-bottom pb-3">
                <h3 class="mb-1">Arena Matriks Edu Group</h3>
                <h5 class="text-muted">Exam Result Card</h5>
            </div>

            <div class="row mb-4">
                {{-- Student Info --}}
                <div class="col-md-6">
                    <table class="table table-borderless table-sm">
                        <tr>
                            <td class="text-muted fw-semibold" style="width:40%;">Student Name</td>
                            <td>{{ $result->student->user->name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold">Student ID</td>
                            <td>{{ $result->student->student_id ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold">Parent/Guardian</td>
                            <td>{{ $result->student->parent->user->name ?? 'N/A' }}</td>
                        </tr>
                    </table>
                </div>
                {{-- Exam Info --}}
                <div class="col-md-6">
                    <table class="table table-borderless table-sm">
                        <tr>
                            <td class="text-muted fw-semibold" style="width:40%;">Exam</td>
                            <td>{{ $result->exam->name }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold">Class</td>
                            <td>{{ $result->exam->class->name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold">Subject</td>
                            <td>{{ $result->exam->subject->name ?? ($result->exam->class->subject->name ?? 'N/A') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold">Date</td>
                            <td>{{ $result->exam->exam_date ? $result->exam->exam_date->format('d M Y') : 'N/A' }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            {{-- Results --}}
            <div class="row mb-4">
                <div class="col-12">
                    <table class="table table-bordered text-center">
                        <thead class="table-light">
                            <tr>
                                <th>Maximum Marks</th>
                                <th>Passing Marks</th>
                                <th>Marks Obtained</th>
                                <th>Percentage</th>
                                <th>Grade</th>
                                <th>Rank</th>
                                <th>Result</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="fw-semibold">{{ number_format($result->exam->max_marks, 0) }}</td>
                                <td>{{ number_format($result->exam->passing_marks, 0) }}</td>
                                <td class="fw-bold fs-5">{{ number_format($result->marks_obtained, 0) }}</td>
                                <td class="fw-semibold">{{ number_format($result->percentage, 1) }}%</td>
                                <td>
                                    @php
                                        $gradeColors = [
                                            'A+' => 'success', 'A' => 'success',
                                            'B+' => 'info', 'B' => 'info',
                                            'C' => 'warning', 'D' => 'warning',
                                            'F' => 'danger',
                                        ];
                                    @endphp
                                    <span class="badge bg-{{ $gradeColors[$result->grade] ?? 'secondary' }} fs-6">
                                        {{ $result->grade }}
                                    </span>
                                </td>
                                <td>
                                    @if($result->rank)
                                        <span class="fw-semibold">#{{ $result->rank }}</span>
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>
                                    @if($result->marks_obtained >= $result->exam->passing_marks)
                                        <span class="badge bg-success fs-6">PASS</span>
                                    @else
                                        <span class="badge bg-danger fs-6">FAIL</span>
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            @if($result->remarks)
                <div class="mb-4">
                    <strong>Remarks:</strong> {{ $result->remarks }}
                </div>
            @endif

            {{-- Footer --}}
            <div class="row mt-5 pt-4 border-top">
                <div class="col-md-4 text-center">
                    <div class="border-top border-dark pt-2 mx-4">
                        <small class="text-muted">Teacher's Signature</small>
                    </div>
                </div>
                <div class="col-md-4 text-center">
                    <div class="border-top border-dark pt-2 mx-4">
                        <small class="text-muted">Date</small>
                    </div>
                </div>
                <div class="col-md-4 text-center">
                    <div class="border-top border-dark pt-2 mx-4">
                        <small class="text-muted">Principal's Signature</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
@media print {
    .sidebar, .header, .breadcrumb, .btn, nav {
        display: none !important;
    }
    .main-content {
        margin-left: 0 !important;
        padding: 0 !important;
    }
    #resultCard {
        border: none !important;
        box-shadow: none !important;
    }
}
</style>
@endpush
@endsection
