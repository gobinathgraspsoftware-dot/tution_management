@extends('layouts.app')

@section('title', 'Create Seminar')
@section('page-title', 'Create Seminar')

@section('content')
<div class="page-header">
    <h1>
        <i class="fas fa-plus-circle me-2"></i> Create New Seminar
    </h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.seminars.index') }}">Seminars</a></li>
            <li class="breadcrumb-item active">Create</li>
        </ol>
    </nav>
</div>

<form action="{{ route('admin.seminars.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    
    @include('admin.seminars._form')

    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between">
                <a href="{{ route('admin.seminars.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Create Seminar
                </button>
            </div>
        </div>
    </div>
</form>
@endsection
