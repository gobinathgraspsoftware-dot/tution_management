@extends('layouts.app')

@section('title', 'View Subject')
@section('page-title', $subject->name)

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="fas fa-book me-2"></i> {{ $subject->name }}</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.subjects.index') }}">Subjects</a></li>
                <li class="breadcrumb-item active">{{ $subject->name }}</li>
            </ol>
        </nav>
    </div>
    <div>
        @can('edit-subjects')
        <a href="{{ route('admin.subjects.edit', $subject) }}" class="btn btn-primary">
            <i class="fas fa-edit me-1"></i> Edit
        </a>
        @endcan
        <a href="{{ route('admin.subjects.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to List
        </a>
    </div>
</div>

<div class="row">
    <!-- Subject Details -->
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i> Subject Details</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th width="35%">Subject Code:</th>
                        <td><span class="badge bg-secondary fs-6">{{ $subject->code }}</span></td>
                    </tr>
                    <tr>
                        <th>Subject Name:</th>
                        <td>{{ $subject->name }}</td>
                    </tr>
                    <tr>
                        <th>Description:</th>
                        <td>{{ $subject->description ?: '-' }}</td>
                    </tr>
                    <tr>
                        <th>Status:</th>
                        <td>
                            @if($subject->status == 'active')
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Inactive</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Grade Levels:</th>
                        <td>
                            @if($subject->grade_levels && count($subject->grade_levels) > 0)
                                @foreach($subject->grade_levels as $grade)
                                    <span class="badge bg-info mb-1">{{ $grade }}</span>
                                @endforeach
                            @else
                                <span class="text-muted">No grade levels assigned</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Created:</th>
                        <td>{{ $subject->created_at->format('d M Y, h:i A') }}</td>
                    </tr>
                    <tr>
                        <th>Last Updated:</th>
                        <td>{{ $subject->updated_at->format('d M Y, h:i A') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i> Statistics</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-4">
                        <div class="border rounded p-3">
                            <h3 class="text-primary mb-0">{{ $subject->packages->count() }}</h3>
                            <small class="text-muted">Packages</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="border rounded p-3">
                            <h3 class="text-success mb-0">{{ $subject->classes->count() }}</h3>
                            <small class="text-muted">Classes</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="border rounded p-3">
                            <h3 class="text-info mb-0">{{ $subject->materials->count() }}</h3>
                            <small class="text-muted">Materials</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Linked Packages -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-box me-2"></i> Linked Packages</h5>
    </div>
    <div class="card-body">
        @if($subject->packages->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Code</th>
                            <th>Package Name</th>
                            <th>Type</th>
                            <th>Price</th>
                            <th>Sessions/Month</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($subject->packages as $package)
                            <tr>
                                <td><span class="badge bg-secondary">{{ $package->code }}</span></td>
                                <td>{{ $package->name }}</td>
                                <td>
                                    @if($package->type == 'online')
                                        <span class="badge bg-info">Online</span>
                                    @elseif($package->type == 'offline')
                                        <span class="badge bg-warning">Offline</span>
                                    @else
                                        <span class="badge bg-primary">Hybrid</span>
                                    @endif
                                </td>
                                <td>RM {{ number_format($package->price, 2) }}</td>
                                <td>{{ $package->pivot->sessions_per_month }}</td>
                                <td>
                                    @if($package->status == 'active')
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.packages.show', $package) }}"
                                       class="btn btn-sm btn-outline-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-4">
                <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                <p class="text-muted">No packages linked to this subject.</p>
            </div>
        @endif
    </div>
</div>

<!-- Classes using this subject -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-school me-2"></i> Classes</h5>
    </div>
    <div class="card-body">
        @if($subject->classes->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Class Name</th>
                            <th>Teacher</th>
                            <th>Type</th>
                            <th>Capacity</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($subject->classes as $class)
                            <tr>
                                <td>{{ $class->name }}</td>
                                <td>{{ $class->teacher?->user?->name ?? '-' }}</td>
                                <td>
                                    @if($class->class_type == 'online')
                                        <span class="badge bg-info">Online</span>
                                    @else
                                        <span class="badge bg-warning">Physical</span>
                                    @endif
                                </td>
                                <td>{{ $class->current_students ?? 0 }}/{{ $class->max_students }}</td>
                                <td>
                                    @if($class->status == 'active')
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Inactive</span>
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
                <p class="text-muted">No classes using this subject.</p>
            </div>
        @endif
    </div>
</div>
@endsection
