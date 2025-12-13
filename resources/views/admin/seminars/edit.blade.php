@extends('layouts.app')

@section('title', 'Edit Seminar')
@section('page-title', 'Edit Seminar')

@section('content')
<div class="page-header">
    <h1>
        <i class="fas fa-edit me-2"></i> Edit Seminar
    </h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.seminars.index') }}">Seminars</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.seminars.show', $seminar) }}">{{ $seminar->name }}</a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol>
    </nav>
</div>

<form action="{{ route('admin.seminars.update', $seminar) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    
    @include('admin.seminars._form')

    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between">
                <a href="{{ route('admin.seminars.show', $seminar) }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Seminar
                </button>
            </div>
        </div>
    </div>
</form>
@endsection
