@extends('layouts.app')

@section('title', 'Edit Exam')

@section('content')
<div class="container-fluid">
    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="fas fa-edit me-2"></i>Edit Exam</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.exams.index') }}">Exams</a></li>
                    <li class="breadcrumb-item active">Edit: {{ $exam->name }}</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.exams.show', $exam) }}" class="btn btn-outline-info">
                <i class="fas fa-eye me-1"></i> View
            </a>
            <a href="{{ route('admin.exams.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <h6 class="mb-0"><i class="fas fa-edit me-2"></i>Update Exam Details</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.exams.update', $exam) }}" method="POST">
                @csrf
                @method('PUT')
                @include('admin.exams._form')

                <hr>
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.exams.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Update Exam
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
