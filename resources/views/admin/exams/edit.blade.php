@extends('layouts.app')

@section('title', 'Edit Exam')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.exams.index') }}">Exams</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </nav>
        <h1 class="h3">Edit Exam</h1>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.exams.update', $exam) }}" method="POST">
                        @csrf
                        @method('PUT')
                        @include('admin.exams._form')

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Exam
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
                    <h6 class="mb-0"><i class="fas fa-info-circle"></i> Exam Info</h6>
                </div>
                <div class="card-body">
                    <dl class="row small mb-0">
                        <dt class="col-sm-5">Created at:</dt>
                        <dd class="col-sm-7">{{ $exam->created_at->format('M j, Y h:i A') }}</dd>

                        <dt class="col-sm-5">Last updated:</dt>
                        <dd class="col-sm-7">{{ $exam->updated_at->format('M j, Y h:i A') }}</dd>

                        <dt class="col-sm-5">Total Students:</dt>
                        <dd class="col-sm-7">{{ $exam->class->enrollments()->count() }}</dd>

                        <dt class="col-sm-5">Results Entered:</dt>
                        <dd class="col-sm-7">{{ $exam->results()->count() }}</dd>
                    </dl>
                </div>
            </div>

            @if($exam->results()->count() == 0)
                <div class="card mt-3">
                    <div class="card-body">
                        <h6 class="text-danger"><i class="fas fa-exclamation-triangle"></i> Danger Zone</h6>
                        <p class="small text-muted">Delete this exam permanently.</p>
                        <form action="{{ route('admin.exams.destroy', $exam) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm w-100" onclick="return confirm('Are you sure you want to delete this exam?')">
                                <i class="fas fa-trash"></i> Delete Exam
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
