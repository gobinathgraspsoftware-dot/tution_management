@extends('layouts.app')

@section('title', 'Create Announcement')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.announcements.index') }}">Announcements</a></li>
                <li class="breadcrumb-item active">Create</li>
            </ol>
        </nav>
        <h1 class="h3">Create New Announcement</h1>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.announcements.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @include('admin.announcements._form')

                        <div class="d-flex gap-2">
                            <button type="submit" name="status" value="draft" class="btn btn-secondary">
                                <i class="fas fa-save"></i> Save as Draft
                            </button>
                            <button type="submit" name="status" value="published" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Publish Now
                            </button>
                            <a href="{{ route('admin.announcements.index') }}" class="btn btn-outline-secondary">
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
                    <h6 class="mb-0"><i class="fas fa-info-circle"></i> Tips</h6>
                </div>
                <div class="card-body">
                    <ul class="small">
                        <li>Keep titles clear and concise</li>
                        <li>Use priority levels appropriately</li>
                        <li>Target specific audiences for better engagement</li>
                        <li>Pin important announcements to keep them visible</li>
                        <li>Schedule announcements for future dates if needed</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
