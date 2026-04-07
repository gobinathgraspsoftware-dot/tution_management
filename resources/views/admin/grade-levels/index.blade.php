@extends('layouts.app')

@section('title', 'Grade Levels')
@section('page-title', 'Grade Levels')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="fas fa-layer-group me-2 text-warning"></i>Grade Levels</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item active">Grade Levels</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('admin.grade-levels.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Add Grade Level
        </a>
    </div>
</div>

{{-- Search / Filter Bar --}}
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('admin.grade-levels.index') }}" class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text"
                           name="search"
                           class="form-control"
                           placeholder="Search grade level..."
                           value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">Search</button>
                @if(request('search'))
                    <a href="{{ route('admin.grade-levels.index') }}" class="btn btn-outline-secondary ms-1">Clear</a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- Grade Levels Table --}}
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-list me-1"></i> All Grade Levels</span>
        <span class="badge bg-secondary">{{ $gradeLevels->total() }} total</span>
    </div>
    <div class="card-body p-0">
        @if($gradeLevels->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="fas fa-layer-group fa-3x mb-3 opacity-50"></i>
                <p class="mb-0">No grade levels found.</p>
                @if(request('search'))
                    <small>Try clearing your search filter.</small>
                @else
                    <a href="{{ route('admin.grade-levels.create') }}" class="btn btn-sm btn-primary mt-2">
                        <i class="fas fa-plus me-1"></i> Add First Grade Level
                    </a>
                @endif
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Grade Level Name</th>
                            {{-- <th width="180">Created At</th> --}}
                            {{-- <th width="180">Last Updated</th> --}}
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($gradeLevels as $index => $level)
                        <tr>
                            <td class="text-muted">{{ $gradeLevels->firstItem() + $loop->index }}</td>
                            <td>
                                <i class="fas fa-graduation-cap me-2 text-warning"></i>
                                <strong>{{ $level->name }}</strong>
                            </td>
                            {{-- <td class="text-muted small">
                                {{ $level->created_at->format('d M Y, h:i A') }}
                            </td> --}}
                            {{-- <td class="text-muted small">
                                {{ $level->updated_at->format('d M Y, h:i A') }}
                            </td> --}}
                            <td class="text-center">
                                <a href="{{ route('admin.grade-levels.edit', $level) }}"
                                   class="btn btn-sm btn-outline-primary"
                                   title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                {{-- No delete button — intentionally omitted --}}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    @if($gradeLevels->hasPages())
    <div class="card-footer">
        {{ $gradeLevels->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection
