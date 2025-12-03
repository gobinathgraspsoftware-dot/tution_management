@extends('layouts.app')

@section('title', 'View Material')
@section('page-title', 'View Material')

@section('content')
<div class="page-header">
    <h1><i class="fas fa-file-alt me-2"></i> {{ $material->title }}</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('student.materials.index') }}">Study Materials</a></li>
            <li class="breadcrumb-item active">{{ Str::limit($material->title, 30) }}</li>
        </ol>
    </nav>
</div>

<div class="row">
    <!-- Material Viewer -->
    <div class="col-lg-9">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-eye me-2"></i> Material Viewer</span>
                <div class="btn-group btn-group-sm">
                    @if($material->is_downloadable)
                    <a href="{{ route('admin.materials.download', $material) }}" class="btn btn-success" target="_blank">
                        <i class="fas fa-download me-1"></i> Download
                    </a>
                    @endif
                    <button type="button" class="btn btn-secondary" onclick="window.print()">
                        <i class="fas fa-print me-1"></i> Print
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                @if($material->file_type == 'pdf')
                    <!-- PDF Viewer -->
                    <div id="pdfViewer" style="min-height: 800px; width: 100%;">
                        <iframe
                            src="data:application/pdf;base64,{{ $base64 }}"
                            style="width: 100%; height: 800px; border: none;"
                            type="application/pdf">
                            <p>Your browser does not support embedded PDFs.
                               <a href="data:application/pdf;base64,{{ $base64 }}" download="{{ $material->title }}.pdf">
                                   Download the PDF
                               </a> instead.
                            </p>
                        </iframe>
                    </div>
                @elseif(in_array($material->file_type, ['doc', 'docx']))
                    <!-- Document Viewer -->
                    <div class="alert alert-info m-3">
                        <i class="fas fa-file-word fa-2x mb-3"></i>
                        <h5>Microsoft Word Document</h5>
                        <p>This document cannot be previewed in the browser.</p>
                        @if($material->is_downloadable)
                            <a href="{{ route('admin.materials.download', $material) }}" class="btn btn-primary">
                                <i class="fas fa-download me-1"></i> Download Document
                            </a>
                        @else
                            <p class="text-muted mb-0">This material is view-only. Please contact your teacher if you need a copy.</p>
                        @endif
                    </div>
                @elseif(in_array($material->file_type, ['ppt', 'pptx']))
                    <!-- Presentation Viewer -->
                    <div class="alert alert-info m-3">
                        <i class="fas fa-file-powerpoint fa-2x mb-3"></i>
                        <h5>Microsoft PowerPoint Presentation</h5>
                        <p>This presentation cannot be previewed in the browser.</p>
                        @if($material->is_downloadable)
                            <a href="{{ route('admin.materials.download', $material) }}" class="btn btn-primary">
                                <i class="fas fa-download me-1"></i> Download Presentation
                            </a>
                        @else
                            <p class="text-muted mb-0">This material is view-only. Please contact your teacher if you need a copy.</p>
                        @endif
                    </div>
                @else
                    <!-- Other File Types -->
                    <div class="alert alert-info m-3">
                        <i class="fas fa-file fa-2x mb-3"></i>
                        <h5>{{ strtoupper($material->file_type) }} File</h5>
                        <p>This file type cannot be previewed in the browser.</p>
                        @if($material->is_downloadable)
                            <a href="{{ route('admin.materials.download', $material) }}" class="btn btn-primary">
                                <i class="fas fa-download me-1"></i> Download File
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Material Information Sidebar -->
    <div class="col-lg-3">
        <!-- Material Info -->
        <div class="card mb-3">
            <div class="card-header">
                <i class="fas fa-info-circle me-2"></i> Material Info
            </div>
            <div class="card-body">
                <h6 class="mb-3">{{ $material->title }}</h6>

                <div class="mb-2">
                    <small class="text-muted">Type:</small>
                    <p class="mb-0">
                        <span class="badge bg-info">{{ ucfirst($material->type) }}</span>
                    </p>
                </div>

                <div class="mb-2">
                    <small class="text-muted">Class:</small>
                    <p class="mb-0">{{ $material->class->name ?? 'N/A' }}</p>
                </div>

                <div class="mb-2">
                    <small class="text-muted">Subject:</small>
                    <p class="mb-0">{{ $material->subject->name ?? 'N/A' }}</p>
                </div>

                <div class="mb-2">
                    <small class="text-muted">Teacher:</small>
                    <p class="mb-0">{{ $material->teacher->user->name ?? 'N/A' }}</p>
                </div>

                <div class="mb-2">
                    <small class="text-muted">Published:</small>
                    <p class="mb-0">{{ $material->publish_date?->format('d M Y') ?? $material->created_at->format('d M Y') }}</p>
                </div>

                <div class="mb-0">
                    <small class="text-muted">File Size:</small>
                    <p class="mb-0">{{ number_format($material->file_size / 1024, 2) }} KB</p>
                </div>
            </div>
        </div>

        <!-- Description -->
        @if($material->description)
        <div class="card mb-3">
            <div class="card-header">
                <i class="fas fa-align-left me-2"></i> Description
            </div>
            <div class="card-body">
                <p class="mb-0 small">{{ $material->description }}</p>
            </div>
        </div>
        @endif

        <!-- Actions -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-cog me-2"></i> Actions
            </div>
            <div class="card-body">
                <a href="{{ route('student.materials.index') }}" class="btn btn-outline-secondary w-100 mb-2">
                    <i class="fas fa-arrow-left me-1"></i> Back to Materials
                </a>
                @if($material->is_downloadable)
                <a href="{{ route('admin.materials.download', $material) }}" class="btn btn-success w-100 mb-2" target="_blank">
                    <i class="fas fa-download me-1"></i> Download File
                </a>
                @endif
                <button type="button" class="btn btn-outline-primary w-100" onclick="window.print()">
                    <i class="fas fa-print me-1"></i> Print
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Note for non-downloadable materials -->
@if(!$material->is_downloadable)
<div class="alert alert-warning mt-3">
    <i class="fas fa-info-circle me-2"></i>
    <strong>Note:</strong> This material is view-only and cannot be downloaded. You can view it online or print it using the print button.
</div>
@endif
@endsection

@push('scripts')
<script>
// Track viewing time
let startTime = Date.now();

window.addEventListener('beforeunload', function() {
    let duration = Math.floor((Date.now() - startTime) / 1000);

    // Send duration to server (using beacon API for reliability)
    navigator.sendBeacon(
        '{{ route("student.materials.track-view", $material) }}',
        JSON.stringify({ duration: duration })
    );
});
</script>
@endpush
