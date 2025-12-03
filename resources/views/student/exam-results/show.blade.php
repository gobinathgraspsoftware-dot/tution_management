@extends('layouts.app')

@section('title', 'Result Details')

@section('content')
<div class="container">
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('student.exam-results.index') }}">My Results</a></li>
                <li class="breadcrumb-item active">Details</li>
            </ol>
        </nav>
    </div>

    @include('admin.exam-results.result-card', ['result' => $result])
</div>
@endsection
