@extends('layouts.app')

@section('title', 'Parent Management')
@section('page-title', 'Parent Management')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="fas fa-user-friends me-2"></i> Parent Management</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Parents</li>
            </ol>
        </nav>
    </div>
    <div>
        @can('export-parents')
        <a href="{{ route('admin.parents.export') }}" class="btn btn-outline-success me-2">
            <i class="fas fa-file-export me-1"></i> Export
        </a>
        @endcan
        @can('create-parents')
        <a href="{{ route('admin.parents.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Add Parent
        </a>
        @endcan
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('admin.parents.index') }}" method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Name, email, IC, phone..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">City</label>
                <select name="city" class="form-select">
                    <option value="">All Cities</option>
                    @foreach($cities as $city)
                        <option value="{{ $city }}" {{ request('city') == $city ? 'selected' : '' }}>{{ $city }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search me-1"></i> Filter
                </button>
                <a href="{{ route('admin.parents.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-1"></i> Clear
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Parents List -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Parent ID</th>
                        <th>Name</th>
                        <th>Contact</th>
                        <th>Children</th>
                        <th>City</th>
                        <th>Status</th>
                        <th width="150">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($parents as $parent)
                        <tr>
                            <td><span class="badge bg-info">{{ $parent->parent_id }}</span></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="user-avatar me-2" style="width:35px;height:35px;font-size:0.9rem;background:linear-gradient(135deg, #ff9800 0%, #f57c00 100%);">
                                        {{ substr($parent->user->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <strong>{{ $parent->user->name }}</strong>
                                        <br><small class="text-muted">{{ ucfirst($parent->relationship) }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <small>
                                    <i class="fas fa-envelope me-1 text-muted"></i> {{ $parent->user->email }}<br>
                                    <i class="fas fa-phone me-1 text-muted"></i> {{ $parent->user->phone }}
                                </small>
                            </td>
                            <td>
                                <span class="badge bg-primary">{{ $parent->students->count() }} children</span>
                                @if($parent->students->count() > 0)
                                <br>
                                <small class="text-muted">
                                    {{ $parent->students->take(2)->pluck('user.name')->implode(', ') }}
                                    @if($parent->students->count() > 2)
                                        +{{ $parent->students->count() - 2 }} more
                                    @endif
                                </small>
                                @endif
                            </td>
                            <td>{{ $parent->city ?? '-' }}</td>
                            <td>
                                @if($parent->user->status == 'active')
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    @can('view-parents')
                                    <a href="{{ route('admin.parents.show', $parent) }}" class="btn btn-outline-info" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @endcan
                                    @can('edit-parents')
                                    <a href="{{ route('admin.parents.edit', $parent) }}" class="btn btn-outline-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endcan
                                    @can('delete-parents')
                                    <button type="button" class="btn btn-outline-danger" title="Delete"
                                            onclick="confirmDelete({{ $parent->id }}, '{{ $parent->user->name }}')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endcan
                                </div>

                                @can('delete-parents')
                                <form id="delete-form-{{ $parent->id }}" action="{{ route('admin.parents.destroy', $parent) }}" method="POST" class="d-none">
                                    @csrf
                                    @method('DELETE')
                                </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-user-friends fa-3x mb-3"></i>
                                    <p>No parents found.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center mt-4">
            <div class="text-muted">
                Showing {{ $parents->firstItem() ?? 0 }} to {{ $parents->lastItem() ?? 0 }} of {{ $parents->total() }} entries
            </div>
            {{ $parents->links() }}
        </div>
    </div>
</div>

@include('components.delete-modal', [
    'id' => 'deleteModal',
    'title' => 'Delete Parent',
    'message' => 'Are you sure you want to delete this parent?',
    'route' => 'admin.parents.destroy'
])
@endsection
