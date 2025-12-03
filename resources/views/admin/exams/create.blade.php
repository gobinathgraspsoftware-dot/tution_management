@extends('layouts.app')

@section('title', 'Create Exam')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.exams.index') }}">Exams</a></li>
                <li class="breadcrumb-item active">Create</li>
            </ol>
        </nav>
        <h1 class="h3">Create New Exam</h1>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.exams.store') }}" method="POST">
                        @csrf
                        @include('admin.exams._form')

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Create Exam
                            </button>
                            <a href="{{ route('admin.exams.index') }}" class="btn btn-outline-secondary">
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
                    <h6 class="mb-0"><i class="fas fa-info-circle"></i> Guidelines</h6>
                </div>
                <div class="card-body">
                    <ul class="small mb-0">
                        <li>Set appropriate exam date and time</li>
                        <li>Ensure max marks and passing marks are correct</li>
                        <li>Duration should reflect actual exam time</li>
                        <li>Select the correct class and subject</li>
                        <li>Add clear instructions for students</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
