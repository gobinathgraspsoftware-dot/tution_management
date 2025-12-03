@extends('layouts.app')

@section('title', 'Edit Announcement')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.announcements.index') }}">Announcements</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </nav>
        <h1 class="h3">Edit Announcement</h1>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.announcements.update', $announcement) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        @include('admin.announcements._form')

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Announcement
                            </button>
                            @if($announcement->status == 'draft')
                                <button type="submit" name="status" value="published" class="btn btn-success">
                                    <i class="fas fa-paper-plane"></i> Publish Now
                                </button>
                            @endif
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
                    <h6 class="mb-0"><i class="fas fa-info-circle"></i> Announcement Info</h6>
                </div>
                <div class="card-body">
                    <dl class="row small mb-0">
                        <dt class="col-sm-5">Created by:</dt>
                        <dd class="col-sm-7">{{ $announcement->creator->name }}</dd>

                        <dt class="col-sm-5">Created at:</dt>
                        <dd class="col-sm-7">{{ $announcement->created_at->format('M j, Y h:i A') }}</dd>

                        <dt class="col-sm-5">Last updated:</dt>
                        <dd class="col-sm-7">{{ $announcement->updated_at->format('M j, Y h:i A') }}</dd>

                        @if($announcement->published_at)
                            <dt class="col-sm-5">Published at:</dt>
                            <dd class="col-sm-7">{{ $announcement->published_at->format('M j, Y h:i A') }}</dd>
                        @endif
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
