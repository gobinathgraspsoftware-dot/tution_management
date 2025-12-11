@extends('layouts.app')

@section('title', 'My Profile')
@section('page-title', 'My Profile')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="fas fa-user-circle me-2"></i> My Profile</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('teacher.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">My Profile</li>
            </ol>
        </nav>
    </div>
    <div>
        <a href="{{ route('teacher.profile.edit') }}" class="btn btn-primary">
            <i class="fas fa-edit me-1"></i> Edit Profile
        </a>
    </div>
</div>

<div class="row">
    <!-- Profile Card -->
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-body text-center">
                @if($teacher->user->profile_photo)
                    <img src="{{ Storage::url($teacher->user->profile_photo) }}"
                         alt="Profile Photo"
                         class="rounded-circle mb-3"
                         style="width: 120px; height: 120px; object-fit: cover;">
                @else
                    <div class="user-avatar mx-auto mb-3"
                         style="width: 120px; height: 120px; font-size: 3rem; background: linear-gradient(135deg, #4caf50 0%, #2e7d32 100%);">
                        {{ substr($teacher->user->name, 0, 1) }}
                    </div>
                @endif

                <h4 class="mb-1">{{ $teacher->user->name }}</h4>
                <p class="text-muted mb-2">{{ $teacher->specialization ?? 'Teacher' }}</p>
                <span class="badge bg-info fs-6">{{ $teacher->teacher_id }}</span>

                <hr>

                @if($teacher->status == 'active')
                    <span class="badge bg-success fs-6"><i class="fas fa-check-circle me-1"></i> Active</span>
                @elseif($teacher->status == 'on_leave')
                    <span class="badge bg-warning text-dark fs-6"><i class="fas fa-pause-circle me-1"></i> On Leave</span>
                @else
                    <span class="badge bg-danger fs-6"><i class="fas fa-times-circle me-1"></i> Inactive</span>
                @endif

                <div class="mt-3">
                    <span class="badge bg-secondary">
                        {{ ucfirst(str_replace('_', ' ', $teacher->employment_type)) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Contact Information -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-address-book me-2"></i> Contact Information
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label text-muted small mb-1">Email</label>
                    <p class="mb-0">
                        <i class="fas fa-envelope text-muted me-2"></i>
                        <a href="mailto:{{ $teacher->user->email }}">{{ $teacher->user->email }}</a>
                    </p>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small mb-1">Phone</label>
                    <p class="mb-0">
                        <i class="fas fa-phone text-muted me-2"></i>
                        <a href="tel:{{ $teacher->user->phone }}">{{ $teacher->user->phone ?? 'Not provided' }}</a>
                    </p>
                </div>
                <div class="mb-0">
                    <label class="form-label text-muted small mb-1">Address</label>
                    <p class="mb-0">
                        <i class="fas fa-map-marker-alt text-muted me-2"></i>
                        {{ $teacher->address ?? 'Not provided' }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-chart-bar me-2"></i> Quick Statistics
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6 mb-3">
                        <h3 class="text-primary mb-0">{{ $stats['total_classes'] }}</h3>
                        <small class="text-muted">Total Classes</small>
                    </div>
                    <div class="col-6 mb-3">
                        <h3 class="text-success mb-0">{{ $stats['active_classes'] }}</h3>
                        <small class="text-muted">Active Classes</small>
                    </div>
                    <div class="col-6 mb-3">
                        <h3 class="text-info mb-0">{{ $stats['total_students'] }}</h3>
                        <small class="text-muted">Total Students</small>
                    </div>
                    <div class="col-6 mb-3">
                        <h3 class="text-warning mb-0">{{ $stats['materials_uploaded'] }}</h3>
                        <small class="text-muted">Materials</small>
                    </div>
                    <div class="col-6">
                        <h3 class="text-secondary mb-0">{{ $stats['sessions_conducted'] }}</h3>
                        <small class="text-muted">Sessions</small>
                    </div>
                    <div class="col-6">
                        <h3 class="text-danger mb-0">
                            {{ $stats['average_rating'] > 0 ? $stats['average_rating'] : 'N/A' }}
                            @if($stats['average_rating'] > 0)
                                <i class="fas fa-star text-warning" style="font-size: 0.7em;"></i>
                            @endif
                        </h3>
                        <small class="text-muted">Rating</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="col-lg-8">
        <!-- Professional Information -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-briefcase me-2"></i> Professional Information
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small mb-1">IC Number</label>
                        <p class="mb-0 fw-medium">{{ $teacher->ic_number ?? 'Not provided' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small mb-1">Join Date</label>
                        <p class="mb-0 fw-medium">{{ $teacher->join_date ? $teacher->join_date->format('d M Y') : 'Not provided' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small mb-1">Qualification</label>
                        <p class="mb-0 fw-medium">{{ $teacher->qualification ?? 'Not provided' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small mb-1">Experience</label>
                        <p class="mb-0 fw-medium">{{ $teacher->experience_years ?? 0 }} years</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small mb-1">Employment Type</label>
                        <p class="mb-0 fw-medium">{{ ucfirst(str_replace('_', ' ', $teacher->employment_type)) }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small mb-1">Pay Type</label>
                        <p class="mb-0 fw-medium">{{ ucfirst(str_replace('_', ' ', $teacher->pay_type)) }}</p>
                    </div>
                    <div class="col-12">
                        <label class="form-label text-muted small mb-1">Bio</label>
                        <p class="mb-0">{{ $teacher->bio ?? 'No bio provided' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- My Classes -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-school me-2"></i> My Classes</span>
                <a href="{{ route('teacher.classes.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                @if($teacher->classes->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Class</th>
                                    <th>Subject</th>
                                    <th>Students</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($teacher->classes->take(5) as $class)
                                    <tr>
                                        <td>
                                            <a href="{{ route('teacher.classes.show', $class) }}">
                                                {{ $class->name }}
                                            </a>
                                            <br>
                                            <small class="text-muted">{{ $class->code }}</small>
                                        </td>
                                        <td>{{ $class->subject->name ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge bg-info">
                                                {{ $class->enrollments->where('status', 'active')->count() }} / {{ $class->capacity }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($class->status == 'active')
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-secondary">{{ ucfirst($class->status) }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-school fa-3x text-muted mb-3"></i>
                        <p class="text-muted mb-0">No classes assigned yet.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Documents Section -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-file-alt me-2"></i> My Documents</span>
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#uploadDocumentModal">
                    <i class="fas fa-upload me-1"></i> Upload
                </button>
            </div>
            <div class="card-body">
                @if($teacher->documents && $teacher->documents->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Document</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Expiry</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($teacher->documents as $document)
                                    <tr>
                                        <td>
                                            <i class="fas fa-file-pdf text-danger me-2"></i>
                                            {{ $document->title }}
                                            <br>
                                            <small class="text-muted">{{ $document->file_size_formatted }}</small>
                                        </td>
                                        <td>{{ $document->document_type_name }}</td>
                                        <td>{!! $document->status_badge !!}</td>
                                        <td>
                                            @if($document->expiry_date)
                                                {{ $document->expiry_date->format('d M Y') }}
                                            @else
                                                <span class="text-muted">No expiry</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('teacher.profile.document.download', $document) }}"
                                               class="btn btn-sm btn-outline-primary" title="Download">
                                                <i class="fas fa-download"></i>
                                            </a>
                                            <form action="{{ route('teacher.profile.document.delete', $document) }}"
                                                  method="POST" class="d-inline"
                                                  onsubmit="return confirm('Are you sure you want to delete this document?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                        <p class="text-muted mb-0">No documents uploaded yet.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-history me-2"></i> Recent Activity
            </div>
            <div class="card-body">
                @if($recentActivity->count() > 0)
                    <ul class="list-group list-group-flush">
                        @foreach($recentActivity as $activity)
                            <li class="list-group-item d-flex justify-content-between align-items-start px-0">
                                <div>
                                    <span class="badge bg-{{ $activity->action == 'create' ? 'success' : ($activity->action == 'update' ? 'primary' : 'danger') }} me-2">
                                        {{ ucfirst($activity->action) }}
                                    </span>
                                    {{ $activity->description }}
                                </div>
                                <small class="text-muted">{{ $activity->created_at->diffForHumans() }}</small>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-clock fa-3x text-muted mb-3"></i>
                        <p class="text-muted mb-0">No recent activity.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Upload Document Modal -->
<div class="modal fade" id="uploadDocumentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('teacher.profile.document.upload') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-upload me-2"></i> Upload Document</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Document Type <span class="text-danger">*</span></label>
                        <select name="document_type" class="form-select" required>
                            <option value="">Select Type</option>
                            @foreach(\App\Models\TeacherDocument::DOCUMENT_TYPES as $key => $value)
                                <option value="{{ $key }}">{{ $value }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" required maxlength="255">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Document File <span class="text-danger">*</span></label>
                        <input type="file" name="document" class="form-control" required accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                        <small class="text-muted">Max 5MB. Allowed: PDF, JPG, PNG, DOC, DOCX</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Expiry Date</label>
                        <input type="date" name="expiry_date" class="form-control" min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2" maxlength="500"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload me-1"></i> Upload
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.user-avatar {
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    color: white;
    font-weight: bold;
}
</style>
@endpush
