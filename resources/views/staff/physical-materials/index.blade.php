@extends('layouts.app')

@section('title', 'Physical Materials')
@section('page-title', 'Physical Materials')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="mb-0">
            <i class="fas fa-book me-2"></i> Physical Materials
        </h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('staff.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Physical Materials</li>
            </ol>
        </nav>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="mb-0">{{ $stats['total'] }}</h4>
                        <small>Total Materials</small>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-book fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="mb-0">{{ $stats['available'] }}</h4>
                        <small>Available</small>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-check-circle fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="mb-0">{{ $stats['out_of_stock'] }}</h4>
                        <small>Out of Stock</small>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-times-circle fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="mb-0">{{ $stats['total_quantity'] }}</h4>
                        <small>Total Quantity</small>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-boxes fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('staff.physical-materials.index') }}" method="GET" class="row g-3">
            <div class="col-md-2">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Search materials..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Grade Level</label>
                <select name="grade_level_id" class="form-select">
                    <option value="">All Grade Levels</option>
                    @foreach($gradeLevels as $gradeLevel)
                        <option value="{{ $gradeLevel->id }}" {{ request('grade_level_id') == $gradeLevel->id ? 'selected' : '' }}>
                            {{ $gradeLevel->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Subject</label>
                <select name="subject_id" class="form-select">
                    <option value="">All Subjects</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                            {{ $subject->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Month</label>
                <select name="month" class="form-select">
                    <option value="">All Months</option>
                    @foreach($months as $month)
                        <option value="{{ $month }}" {{ request('month') == $month ? 'selected' : '' }}>
                            {{ $month }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All</option>
                    <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>Available</option>
                    <option value="out_of_stock" {{ request('status') == 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="fas fa-search me-1"></i> Filter
                </button>
                <a href="{{ route('staff.physical-materials.index') }}" class="btn btn-secondary">
                    <i class="fas fa-redo me-1"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Materials List -->
<div class="card">
    <div class="card-header">
        <i class="fas fa-list me-2"></i> Materials List
    </div>
    <div class="card-body">
        @if($physicalMaterials->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Material</th>
                        <th>Grade Level</th>
                        <th>Subject</th>
                        <th>Class</th>
                        <th>Month/Year</th>
                        <th>Quantity</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($physicalMaterials as $material)
                    <tr>
                        <td>
                            <strong>{{ $material->name }}</strong>
                            @if($material->description)
                                <br><small class="text-muted">{{ Str::limit($material->description, 50) }}</small>
                            @endif
                        </td>
                        <td>{{ $material->gradeLevel->name ?? 'N/A' }}</td>
                        <td>{{ $material->subject->name ?? 'N/A' }}</td>
                        <td>{{ $material->classModel->name ?? '-' }}</td>
                        <td>{{ $material->month }} {{ $material->year }}</td>
                        <td>
                            <span class="badge bg-{{ $material->quantity_available > 10 ? 'success' : ($material->quantity_available > 0 ? 'warning' : 'danger') }}">
                                {{ $material->quantity_available }}
                            </span>
                        </td>
                        <td>
                            @if($material->status == 'available')
                                <span class="badge bg-success">Available</span>
                            @elseif($material->status == 'out_of_stock')
                                <span class="badge bg-danger">Out of Stock</span>
                            @else
                                <span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $material->status)) }}</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('staff.physical-materials.collections', $material) }}" class="btn btn-sm btn-primary" title="Manage Collections">
                                <i class="fas fa-hands"></i> Collections
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-center mt-4">
            {{ $physicalMaterials->links() }}
        </div>
        @else
        <div class="text-center py-5">
            <i class="fas fa-book fa-3x text-muted mb-3"></i>
            <p class="text-muted">No physical materials found.</p>
        </div>
        @endif
    </div>
</div>
@endsection
