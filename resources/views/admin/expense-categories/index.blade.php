@extends('layouts.app')

@section('title', 'Expense Categories')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="h3 mb-0">Expense Categories</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.expenses.index') }}">Expenses</a></li>
                    <li class="breadcrumb-item active">Categories</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 text-end">
            @can('create-expense-categories')
            <a href="{{ route('admin.expense-categories.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Add Category
            </a>
            @endcan
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('admin.expense-categories.index') }}" method="GET">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" class="form-control" placeholder="Search category name..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-1"></i> Search
                        </button>
                        <a href="{{ route('admin.expense-categories.index') }}" class="btn btn-secondary">
                            <i class="fas fa-redo me-1"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Categories Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-tags me-2"></i>Categories List</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th width="5%">#</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Expenses Count</th>
                            <th>Status</th>
                            <th width="15%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $index => $category)
                        <tr>
                            <td>{{ $categories->firstItem() + $index }}</td>
                            <td class="fw-bold">{{ $category->name }}</td>
                            <td>{{ Str::limit($category->description, 50) ?? '-' }}</td>
                            <td>
                                <span class="badge bg-info">{{ $category->expenses_count ?? 0 }} expenses</span>
                            </td>
                            <td>
                                @if($category->status == 'active')
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    @can('edit-expense-categories')
                                    <a href="{{ route('admin.expense-categories.edit', $category) }}" class="btn btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endcan
                                    @can('delete-expense-categories')
                                    @if($category->expenses_count == 0)
                                    <button type="button" class="btn btn-danger" title="Delete" onclick="deleteCategory({{ $category->id }})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @else
                                    <button type="button" class="btn btn-danger" title="Cannot delete - has expenses" disabled>
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No categories found.</p>
                                @can('create-expense-categories')
                                <a href="{{ route('admin.expense-categories.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-1"></i> Add First Category
                                </a>
                                @endcan
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($categories->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $categories->withQueryString()->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Delete Form -->
<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endsection

@push('scripts')
<script>
function deleteCategory(id) {
    if (confirm('Are you sure you want to delete this category? This action cannot be undone.')) {
        const form = document.getElementById('deleteForm');
        form.action = '{{ url("admin/expense-categories") }}/' + id;
        form.submit();
    }
}
</script>
@endpush
