@extends('layouts.app')

@section('title', 'View Document')
@section('page-title', 'Document Details')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1"><i class="fas fa-file-alt me-2"></i> Document Details</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('teacher.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('teacher.documents.index') }}">My Documents</a></li>
                    <li class="breadcrumb-item active">{{ $document->title }}</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('teacher.documents.download', $document) }}" class="btn btn-primary me-2">
                <i class="fas fa-download me-1"></i> Download
            </a>
            <a href="{{ route('teacher.documents.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Document Preview -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-eye me-2"></i> Document Preview</h5>
                </div>
                <div class="card-body text-center">
                    @php
                        $extension = strtolower(pathinfo($document->file_name, PATHINFO_EXTENSION));
                        $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif']);
                        $isPdf = $extension === 'pdf';
                    @endphp

                    @if($isImage)
                        <img src="{{ route('teacher.documents.download', $document) }}" 
                             alt="{{ $document->title }}" 
                             class="img-fluid rounded shadow" 
                             style="max-height: 500px;">
                    @elseif($isPdf)
                        <div class="ratio ratio-16x9 mb-3">
                            <iframe src="{{ route('teacher.documents.download', $document) }}#toolbar=0" 
                                    title="{{ $document->title }}"
                                    class="border rounded"></iframe>
                        </div>
                        <a href="{{ route('teacher.documents.download', $document) }}" 
                           class="btn btn-primary" target="_blank">
                            <i class="fas fa-external-link-alt me-1"></i> Open in New Tab
                        </a>
                    @else
                        <div class="py-5">
                            @if(in_array($extension, ['doc', 'docx']))
                                <i class="fas fa-file-word fa-5x text-primary mb-3"></i>
                            @else
                                <i class="fas fa-file-alt fa-5x text-muted mb-3"></i>
                            @endif
                            <h5 class="text-muted">Preview not available for this file type</h5>
                            <p class="text-muted">Please download the file to view its contents.</p>
                            <a href="{{ route('teacher.documents.download', $document) }}" class="btn btn-primary">
                                <i class="fas fa-download me-1"></i> Download File
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Description -->
            @if($document->description)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i> Description</h5>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $document->description }}</p>
                </div>
            </div>
            @endif
        </div>

        <div class="col-lg-4">
            <!-- Document Information -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-info me-2"></i> Document Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <th class="ps-0" style="width: 40%;">Title</th>
                            <td>{{ $document->title }}</td>
                        </tr>
                        <tr>
                            <th class="ps-0">Type</th>
                            <td>
                                <span class="badge bg-secondary">
                                    {{ $document->document_type_name ?? $document->document_type }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th class="ps-0">File Name</th>
                            <td><small>{{ $document->file_name }}</small></td>
                        </tr>
                        <tr>
                            <th class="ps-0">File Size</th>
                            <td>
                                @if($document->file_size)
                                    @if($document->file_size >= 1048576)
                                        {{ number_format($document->file_size / 1048576, 2) }} MB
                                    @else
                                        {{ number_format($document->file_size / 1024, 2) }} KB
                                    @endif
                                @else
                                    N/A
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="ps-0">File Type</th>
                            <td>{{ strtoupper($extension) }}</td>
                        </tr>
                        <tr>
                            <th class="ps-0">Uploaded</th>
                            <td>{{ $document->created_at->format('d M Y, h:i A') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Status Card -->
            <div class="card mb-4">
                <div class="card-header {{ $document->is_verified ? 'bg-success' : 'bg-warning' }} {{ $document->is_verified ? 'text-white' : 'text-dark' }}">
                    <h5 class="mb-0">
                        <i class="fas {{ $document->is_verified ? 'fa-check-circle' : 'fa-clock' }} me-2"></i> 
                        Status
                    </h5>
                </div>
                <div class="card-body">
                    @if($document->is_verified)
                        <div class="text-center">
                            <i class="fas fa-check-circle fa-3x text-success mb-2"></i>
                            <h5 class="text-success">Verified</h5>
                            @if($document->verified_at)
                            <p class="text-muted mb-0">
                                <small>Verified on {{ $document->verified_at->format('d M Y') }}</small>
                            </p>
                            @endif
                        </div>
                    @else
                        <div class="text-center">
                            <i class="fas fa-clock fa-3x text-warning mb-2"></i>
                            <h5 class="text-warning">Pending Verification</h5>
                            <p class="text-muted mb-0">
                                <small>Your document is awaiting review by admin.</small>
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Expiry Information -->
            <div class="card mb-4">
                <div class="card-header 
                    @if($document->expiry_date && $document->expiry_date->isPast())
                        bg-danger text-white
                    @elseif($document->expiry_date && $document->expiry_date->diffInDays(now()) <= 30)
                        bg-warning text-dark
                    @else
                        bg-light
                    @endif">
                    <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i> Expiry</h5>
                </div>
                <div class="card-body">
                    @if($document->expiry_date)
                        @if($document->expiry_date->isPast())
                            <div class="text-center">
                                <i class="fas fa-exclamation-triangle fa-3x text-danger mb-2"></i>
                                <h5 class="text-danger">Expired</h5>
                                <p class="mb-0">This document expired on <strong>{{ $document->expiry_date->format('d M Y') }}</strong></p>
                                <p class="text-muted small">Please upload a new version.</p>
                            </div>
                        @elseif($document->expiry_date->diffInDays(now()) <= 30)
                            <div class="text-center">
                                <i class="fas fa-exclamation-circle fa-3x text-warning mb-2"></i>
                                <h5 class="text-warning">Expiring Soon</h5>
                                <p class="mb-0">Expires on <strong>{{ $document->expiry_date->format('d M Y') }}</strong></p>
                                <p class="text-muted small">{{ $document->expiry_date->diffForHumans() }}</p>
                            </div>
                        @else
                            <div class="text-center">
                                <i class="fas fa-calendar-check fa-3x text-success mb-2"></i>
                                <h5 class="text-success">Valid</h5>
                                <p class="mb-0">Expires on <strong>{{ $document->expiry_date->format('d M Y') }}</strong></p>
                            </div>
                        @endif
                    @else
                        <div class="text-center text-muted">
                            <i class="fas fa-infinity fa-3x mb-2"></i>
                            <h5>No Expiry</h5>
                            <p class="mb-0">This document does not expire.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Actions -->
            @if(!$document->is_verified)
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="fas fa-trash me-2"></i> Delete Document</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small">Unverified documents can be deleted. Once verified, please contact admin to make changes.</p>
                    <form action="{{ route('teacher.documents.destroy', $document) }}" method="POST" 
                          onsubmit="return confirm('Are you sure you want to delete this document? This action cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="fas fa-trash me-1"></i> Delete Document
                        </button>
                    </form>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
