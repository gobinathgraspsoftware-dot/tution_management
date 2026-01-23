@extends('layouts.app')

@section('title', 'Seminars')
@section('page-title', 'Seminar Management')

@section('content')
<div class="container-fluid">
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="stat-card h-100">
                <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <h3 class="mb-1">{{ number_format($stats['total']) }}</h3>
                <p class="text-muted mb-0">Total Seminars</p>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="stat-card h-100">
                <div class="stat-icon bg-info bg-opacity-10 text-info">
                    <i class="fas fa-clock"></i>
                </div>
                <h3 class="mb-1">{{ number_format($stats['upcoming']) }}</h3>
                <p class="text-muted mb-0">Upcoming</p>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="stat-card h-100">
                <div class="stat-icon bg-success bg-opacity-10 text-success">
                    <i class="fas fa-door-open"></i>
                </div>
                <h3 class="mb-1">{{ number_format($stats['open']) }}</h3>
                <p class="text-muted mb-0">Open Registration</p>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="stat-card h-100">
                <div class="stat-icon bg-secondary bg-opacity-10 text-secondary">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h3 class="mb-1">{{ number_format($stats['completed']) }}</h3>
                <p class="text-muted mb-0">Completed</p>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="stat-card h-100">
                <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                    <i class="fas fa-users"></i>
                </div>
                <h3 class="mb-1">{{ number_format($stats['total_participants']) }}</h3>
                <p class="text-muted mb-0">Total Participants</p>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="stat-card h-100">
                <div class="stat-icon bg-success bg-opacity-10 text-success">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <h3 class="mb-1">RM {{ number_format($stats['total_revenue'], 2) }}</h3>
                <p class="text-muted mb-0">Total Revenue</p>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('staff.seminars.index') }}" method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Search</label>
                    <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="Name, Code, Facilitator...">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Type</label>
                    <select class="form-select" name="type">
                        <option value="">All Types</option>
                        <option value="spm" {{ request('type') == 'spm' ? 'selected' : '' }}>SPM Program</option>
                        <option value="workshop" {{ request('type') == 'workshop' ? 'selected' : '' }}>Workshop</option>
                        <option value="seminar" {{ request('type') == 'seminar' ? 'selected' : '' }}>Seminar</option>
                        <option value="camp" {{ request('type') == 'camp' ? 'selected' : '' }}>Camp</option>
                        <option value="other" {{ request('type') == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="status">
                        <option value="">All Status</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Open</option>
                        <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">From Date</label>
                    <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">To Date</label>
                    <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Seminars Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>All Seminars</h5>
            <span class="badge bg-primary">{{ $seminars->total() }} seminars</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Code</th>
                            <th>Seminar</th>
                            <th>Type</th>
                            <th>Date</th>
                            <th>Venue</th>
                            <th class="text-center">Participants</th>
                            <th class="text-end">Fee</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($seminars as $seminar)
                        <tr>
                            <td>
                                <span class="badge bg-secondary">{{ $seminar->code }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($seminar->image)
                                    <img src="{{ Storage::url($seminar->image) }}" alt="{{ $seminar->name }}" class="rounded me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                    @else
                                    <div class="bg-light rounded me-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                        <i class="fas fa-calendar-alt text-muted"></i>
                                    </div>
                                    @endif
                                    <div>
                                        <h6 class="mb-0">{{ $seminar->name }}</h6>
                                        @if($seminar->facilitator)
                                        <small class="text-muted">By: {{ $seminar->facilitator }}</small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                @php
                                    $typeColors = [
                                        'spm' => 'primary',
                                        'workshop' => 'info',
                                        'seminar' => 'success',
                                        'camp' => 'warning',
                                        'other' => 'secondary'
                                    ];
                                @endphp
                                <span class="badge bg-{{ $typeColors[$seminar->type] ?? 'secondary' }}">
                                    {{ ucfirst($seminar->type) }}
                                </span>
                            </td>
                            <td>
                                <div>
                                    <strong>{{ $seminar->date->format('d M Y') }}</strong>
                                </div>
                                @if($seminar->start_time)
                                <small class="text-muted">
                                    {{ \Carbon\Carbon::parse($seminar->start_time)->format('h:i A') }}
                                    @if($seminar->end_time)
                                    - {{ \Carbon\Carbon::parse($seminar->end_time)->format('h:i A') }}
                                    @endif
                                </small>
                                @endif
                            </td>
                            <td>
                                @if($seminar->is_online)
                                    <span class="badge bg-info"><i class="fas fa-video me-1"></i>Online</span>
                                @else
                                    <small>{{ Str::limit($seminar->venue, 30) }}</small>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge bg-{{ $seminar->participants->count() >= ($seminar->capacity ?? 999) ? 'danger' : 'success' }}">
                                    {{ $seminar->participants->count() }}
                                    @if($seminar->capacity)
                                    / {{ $seminar->capacity }}
                                    @endif
                                </span>
                            </td>
                            <td class="text-end">
                                <div>RM {{ number_format($seminar->regular_fee, 2) }}</div>
                                @if($seminar->early_bird_fee && $seminar->early_bird_deadline && $seminar->early_bird_deadline->isFuture())
                                <small class="text-success">
                                    Early: RM {{ number_format($seminar->early_bird_fee, 2) }}
                                </small>
                                @endif
                            </td>
                            <td class="text-center">
                                @php
                                    $statusColors = [
                                        'draft' => 'secondary',
                                        'open' => 'success',
                                        'closed' => 'warning',
                                        'completed' => 'info',
                                        'cancelled' => 'danger'
                                    ];
                                @endphp
                                <span class="badge bg-{{ $statusColors[$seminar->status] ?? 'secondary' }}">
                                    {{ ucfirst($seminar->status) }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    @can('view-seminar-participants')
                                    <a href="{{ route('staff.seminars.show', $seminar) }}" class="btn btn-outline-primary" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('staff.seminars.participants', $seminar) }}" class="btn btn-outline-info" title="View Participants">
                                        <i class="fas fa-users"></i>
                                    </a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-calendar-times fa-3x mb-3"></i>
                                    <p>No seminars found.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($seminars->hasPages())
        <div class="card-footer">
            {{ $seminars->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
