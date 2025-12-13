@extends('layouts.app')

@section('title', 'Seminar Management')
@section('page-title', 'Seminar Management')

@section('content')
<div class="page-header">
    <h1>
        <i class="fas fa-calendar-alt me-2"></i> Seminar Management
    </h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Seminars</li>
        </ol>
    </nav>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #e3f2fd; color: #2196f3;">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div class="stat-details">
                <h3 class="mb-0">{{ $stats['total'] }}</h3>
                <p class="text-muted mb-0">Total Seminars</p>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #e8f5e9; color: #4caf50;">
                <i class="fas fa-door-open"></i>
            </div>
            <div class="stat-details">
                <h3 class="mb-0">{{ $stats['open'] }}</h3>
                <p class="text-muted mb-0">Open for Registration</p>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #fff3e0; color: #ff9800;">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-details">
                <h3 class="mb-0">{{ $stats['upcoming'] }}</h3>
                <p class="text-muted mb-0">Upcoming Seminars</p>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #f3e5f5; color: #9c27b0;">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-details">
                <h3 class="mb-0">{{ $stats['total_participants'] }}</h3>
                <p class="text-muted mb-0">Total Participants</p>
            </div>
        </div>
    </div>
</div>

<!-- Filters Card -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.seminars.index') }}" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Search seminars..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Type</label>
                <select name="type" class="form-select">
                    <option value="">All Types</option>
                    <option value="spm" {{ request('type') == 'spm' ? 'selected' : '' }}>SPM</option>
                    <option value="workshop" {{ request('type') == 'workshop' ? 'selected' : '' }}>Workshop</option>
                    <option value="bootcamp" {{ request('type') == 'bootcamp' ? 'selected' : '' }}>Bootcamp</option>
                    <option value="other" {{ request('type') == 'other' ? 'selected' : '' }}>Other</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Open</option>
                    <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Date From</label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Date To</label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-1">
                <label class="form-label">&nbsp;</label>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search"></i> Filter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Action Buttons -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h5 class="mb-0">Seminar List</h5>
        <p class="text-muted mb-0">{{ $seminars->total() }} seminars found</p>
    </div>
    <div>
        @can('create-seminars')
        <a href="{{ route('admin.seminars.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Create Seminar
        </a>
        @endcan
    </div>
</div>

<!-- Seminars List -->
<div class="card">
    <div class="card-body">
        @if($seminars->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Code</th>
                        <th>Seminar Name</th>
                        <th>Type</th>
                        <th>Date & Time</th>
                        <th>Venue</th>
                        <th>Capacity</th>
                        <th>Fee</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($seminars as $seminar)
                    <tr>
                        <td><strong>{{ $seminar->code }}</strong></td>
                        <td>
                            <div class="fw-semibold">{{ $seminar->name }}</div>
                            @if($seminar->facilitator)
                            <small class="text-muted">By: {{ $seminar->facilitator }}</small>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-secondary">{{ strtoupper($seminar->type) }}</span>
                        </td>
                        <td>
                            <div>{{ $seminar->date->format('d M Y') }}</div>
                            @if($seminar->start_time)
                            <small class="text-muted">{{ \Carbon\Carbon::parse($seminar->start_time)->format('h:i A') }}</small>
                            @endif
                        </td>
                        <td>
                            @if($seminar->is_online)
                            <span class="badge bg-info">
                                <i class="fas fa-video"></i> Online
                            </span>
                            @else
                            <small>{{ Str::limit($seminar->venue, 30) }}</small>
                            @endif
                        </td>
                        <td>
                            @if($seminar->capacity)
                            <div class="progress" style="height: 20px;">
                                @php
                                    $percentage = $seminar->capacity > 0 ? ($seminar->current_participants / $seminar->capacity) * 100 : 0;
                                    $colorClass = $percentage >= 90 ? 'bg-danger' : ($percentage >= 70 ? 'bg-warning' : 'bg-success');
                                @endphp
                                <div class="progress-bar {{ $colorClass }}" role="progressbar" style="width: {{ $percentage }}%">
                                    {{ $seminar->current_participants }}/{{ $seminar->capacity }}
                                </div>
                            </div>
                            @else
                            <span class="badge bg-secondary">Unlimited</span>
                            @endif
                        </td>
                        <td>
                            <div>RM {{ number_format($seminar->regular_fee, 2) }}</div>
                            @if($seminar->early_bird_fee)
                            <small class="text-success">EB: RM {{ number_format($seminar->early_bird_fee, 2) }}</small>
                            @endif
                        </td>
                        <td>
                            <select class="form-select form-select-sm status-select" data-seminar-id="{{ $seminar->id }}" 
                                    @cannot('edit-seminars') disabled @endcannot>
                                <option value="draft" {{ $seminar->status == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="open" {{ $seminar->status == 'open' ? 'selected' : '' }}>Open</option>
                                <option value="closed" {{ $seminar->status == 'closed' ? 'selected' : '' }}>Closed</option>
                                <option value="completed" {{ $seminar->status == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="cancelled" {{ $seminar->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                @can('view-seminar-participants')
                                <a href="{{ route('admin.seminars.show', $seminar) }}" class="btn btn-sm btn-info" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @endcan
                                @can('edit-seminars')
                                <a href="{{ route('admin.seminars.edit', $seminar) }}" class="btn btn-sm btn-warning" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcan
                                @can('delete-seminars')
                                <button type="button" class="btn btn-sm btn-danger delete-btn" 
                                        data-seminar-id="{{ $seminar->id }}"
                                        data-seminar-name="{{ $seminar->name }}" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-3">
            {{ $seminars->links() }}
        </div>
        @else
        <div class="text-center py-5">
            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">No seminars found</h5>
            <p class="text-muted">Create your first seminar to get started.</p>
            @can('create-seminars')
            <a href="{{ route('admin.seminars.create') }}" class="btn btn-primary mt-3">
                <i class="fas fa-plus"></i> Create Seminar
            </a>
            @endcan
        </div>
        @endif
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete seminar: <strong id="deleteSeminarName"></strong>?</p>
                <p class="text-danger"><i class="fas fa-exclamation-triangle"></i> This action cannot be undone!</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Seminar</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Status change handler
    $('.status-select').change(function() {
        const seminarId = $(this).data('seminar-id');
        const newStatus = $(this).val();
        const selectElement = $(this);
        
        if(confirm('Are you sure you want to change the status to: ' + newStatus + '?')) {
            $.ajax({
                url: `/admin/seminars/${seminarId}/update-status`,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    status: newStatus
                },
                success: function(response) {
                    if(response.success) {
                        showAlert('success', response.message);
                    }
                },
                error: function(xhr) {
                    showAlert('error', 'Failed to update status');
                    // Revert select
                    location.reload();
                }
            });
        } else {
            // Revert select
            location.reload();
        }
    });

    // Delete button handler
    $('.delete-btn').click(function() {
        const seminarId = $(this).data('seminar-id');
        const seminarName = $(this).data('seminar-name');
        
        $('#deleteSeminarName').text(seminarName);
        $('#deleteForm').attr('action', `/admin/seminars/${seminarId}`);
        $('#deleteModal').modal('show');
    });

    function showAlert(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const alert = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                <i class="fas fa-${type === 'success' ? 'check' : 'exclamation'}-circle me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        $('.page-header').after(alert);
        setTimeout(() => $('.alert').fadeOut(), 3000);
    }
});
</script>
@endpush
