@extends('layouts.app')

@section('title', 'Exam Management')

@section('content')
<div class="container-fluid">
    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="fas fa-file-signature me-2"></i>Exam Management</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Exams</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('admin.exams.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Create Exam
        </a>
    </div>

    {{-- Statistics --}}
    @include('admin.exams._stats')

    {{-- Filters --}}
    @include('admin.exams._filters')

    {{-- Exams Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="fas fa-list me-2"></i>Exams List</h6>
            <span class="badge bg-secondary">{{ $exams->total() }} records</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 5%;">#</th>
                            <th style="width: 20%;">Exam Name</th>
                            <th style="width: 15%;">Class</th>
                            <th style="width: 12%;">Subject</th>
                            <th style="width: 12%;">Date</th>
                            <th style="width: 8%;">Max Marks</th>
                            <th style="width: 8%;">Pass Marks</th>
                            <th style="width: 10%;">Status</th>
                            <th style="width: 10%;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($exams as $exam)
                            <tr>
                                <td>{{ $loop->iteration + ($exams->currentPage() - 1) * $exams->perPage() }}</td>
                                <td>
                                    <a href="{{ route('admin.exams.show', $exam) }}" class="text-decoration-none fw-semibold">
                                        {{ $exam->name }}
                                    </a>
                                    @if($exam->results_count ?? $exam->results->count())
                                        <br><small class="text-muted">
                                            <i class="fas fa-clipboard-check me-1"></i>{{ $exam->results->count() }} results
                                        </small>
                                    @endif
                                </td>
                                <td>{{ $exam->class->name ?? 'N/A' }}</td>
                                <td>{{ $exam->subject->name ?? ($exam->class->subject->name ?? 'N/A') }}</td>
                                <td>
                                    <i class="fas fa-calendar me-1 text-muted"></i>
                                    {{ $exam->exam_date ? $exam->exam_date->format('d M Y') : 'N/A' }}
                                </td>
                                <td><span class="badge bg-primary">{{ number_format($exam->max_marks, 0) }}</span></td>
                                <td><span class="badge bg-warning text-dark">{{ number_format($exam->passing_marks, 0) }}</span></td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'scheduled' => 'warning',
                                            'ongoing' => 'primary',
                                            'completed' => 'success',
                                            'cancelled' => 'danger',
                                        ];
                                    @endphp
                                    <span class="badge bg-{{ $statusColors[$exam->status] ?? 'secondary' }}">
                                        {{ ucfirst($exam->status) }}
                                    </span>
                                </td>
                                <td>
                                    @include('admin.exams._actions', ['exam' => $exam])
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-file-alt fa-3x mb-3 d-block"></i>
                                        <p class="mb-0">No exams found.</p>
                                        <a href="{{ route('admin.exams.create') }}" class="btn btn-sm btn-primary mt-2">
                                            <i class="fas fa-plus me-1"></i> Create First Exam
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($exams->hasPages())
            <div class="card-footer bg-white">
                {{ $exams->links() }}
            </div>
        @endif
    </div>
</div>

{{-- Delete Modal --}}
@include('components.delete-modal', [
    'id' => 'deleteExamModal',
    'title' => 'Delete Exam',
    'message' => 'Are you sure you want to delete this exam? This action cannot be undone.',
    'route' => 'admin.exams.destroy',
])
@endsection
