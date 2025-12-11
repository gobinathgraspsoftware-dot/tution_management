@extends('layouts.app')

@section('title', 'My Documents')
@section('page-title', 'My Documents')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1><i class="fas fa-folder-open me-2"></i> My Documents</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('teacher.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">My Documents</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('teacher.documents.create') }}" class="btn btn-primary">
        <i class="fas fa-upload me-1"></i> Upload Document
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- Document Statistics -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <h3 class="text-primary mb-0">{{ $stats['total'] }}</h3>
                <small class="text-muted">Total Documents</small>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <h3 class="text-success mb-0">{{ $stats['verified'] }}</h3>
                <small class="text-muted">Verified</small>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <h3 class="text-warning mb-0">{{ $stats['pending'] }}</h3>
                <small class="text-muted">Pending Verification</small>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <h3 class="text-danger mb-0">{{ $stats['expired'] }}</h3>
                <small class="text-muted">Expired</small>
            </div>
        </div>
    </div>
</div>

<!-- Alerts -->
@if($stats['expiring_soon'] > 0)
<div class="alert alert-warning">
    <i class="fas fa-exclamation-triangle me-2"></i>
    <strong>Reminder:</strong> {{ $stats['expiring_soon'] }} document(s) will expire within 30 days. Please renew them.
</div>
@endif

<!-- Documents by Type -->
@if($documents->count() > 0)
    @foreach($documents as $type => $docs)
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>
                <i class="fas fa-file me-2"></i>
                {{ \App\Models\TeacherDocument::DOCUMENT_TYPES[$type] ?? ucfirst($type) }}
            </span>
            <span class="badge bg-secondary">{{ $docs->count() }} file(s)</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Title</th>
                            <th>File Info</th>
                            <th>Expiry Date</th>
                            <th>Status</th>
                            <th>Uploaded</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($docs as $document)
                        <tr class="{{ $document->is_expired ? 'table-danger' : ($document->is_expiring_soon ? 'table-warning' : '') }}">
                            <td>
                                <strong>{{ $document->title }}</strong>
                                @if($document->description)
                                    <br><small class="text-muted">{{ Str::limit($document->description, 50) }}</small>
                                @endif
                            </td>
                            <td>
                                <small class="text-muted">
                                    <i class="fas fa-file me-1"></i> {{ $document->file_name }}<br>
                                    <i class="fas fa-hdd me-1"></i> {{ $document->file_size_formatted }}
                                </small>
                            </td>
                            <td>
                                @if($document->expiry_date)
                                    {{ $document->expiry_date->format('d M Y') }}
                                    @if($document->is_expired)
                                        <br><span class="badge bg-danger">Expired</span>
                                    @elseif($document->is_expiring_soon)
                                        <br><span class="badge bg-warning text-dark">Expiring Soon</span>
                                    @endif
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>{!! $document->status_badge !!}</td>
                            <td>{{ $document->created_at->format('d M Y') }}</td>
                            <td class="text-center">
                                <div class="btn-group">
                                    <a href="{{ route('teacher.documents.download', $document) }}"
                                       class="btn btn-sm btn-outline-primary" title="Download">
                                        <i class="fas fa-download"></i>
                                    </a>
                                    @if(!$document->is_verified)
                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                            onclick="confirmDelete({{ $document->id }}, '{{ $document->title }}')"
                                            title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @else
                                    <button type="button" class="btn btn-sm btn-outline-secondary" disabled
                                            title="Cannot delete verified document">
                                        <i class="fas fa-lock"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endforeach
@else
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">No Documents Found</h5>
            <p class="text-muted mb-3">You haven't uploaded any documents yet.</p>
            <a href="{{ route('teacher.documents.create') }}" class="btn btn-primary">
                <i class="fas fa-upload me-1"></i> Upload Your First Document
            </a>
        </div>
    </div>
@endif

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="deleteForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-trash me-2 text-danger"></i> Delete Document</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this document?</p>
                    <p><strong id="deleteDocumentTitle"></strong></p>
                    <p class="text-danger mb-0">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        This action cannot be undone.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i> Delete
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function confirmDelete(id, title) {
    document.getElementById('deleteDocumentTitle').textContent = title;
    document.getElementById('deleteForm').action = `/teacher/documents/${id}`;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
@endpush
