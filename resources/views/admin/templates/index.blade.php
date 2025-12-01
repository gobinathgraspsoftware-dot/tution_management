@extends('layouts.app')

@section('title', 'Message Templates')
@section('page-title', 'Message Templates')

@section('content')
<div class="page-header">
    <h1><i class="fas fa-file-alt me-2"></i> Message Templates</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.notifications.index') }}">Notifications</a></li>
            <li class="breadcrumb-item active">Templates</li>
        </ol>
    </nav>
</div>

<!-- Action Buttons -->
<div class="mb-4">
    <a href="{{ route('admin.templates.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i> Create Template
    </a>
    <a href="{{ route('admin.notifications.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back to Notifications
    </a>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form action="{{ route('admin.templates.index') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <select name="category" class="form-select">
                    <option value="">All Categories</option>
                    @foreach($categories as $key => $label)
                        <option value="{{ $key }}" {{ request('category') == $key ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="channel" class="form-select">
                    <option value="">All Channels</option>
                    @foreach($channels as $key => $label)
                        <option value="{{ $key }}" {{ request('channel') == $key ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" placeholder="Search templates..." value="{{ request('search') }}">
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Templates Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Channel</th>
                        <th>Preview</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($templates as $template)
                    <tr>
                        <td>
                            <strong>{{ $template->name }}</strong>
                        </td>
                        <td>
                            <span class="badge bg-secondary">
                                {{ $categories[$template->category] ?? ucfirst($template->category) }}
                            </span>
                        </td>
                        <td>
                            @if($template->channel === 'whatsapp')
                                <span class="badge bg-success"><i class="fab fa-whatsapp me-1"></i>WhatsApp</span>
                            @elseif($template->channel === 'email')
                                <span class="badge bg-info"><i class="fas fa-envelope me-1"></i>Email</span>
                            @elseif($template->channel === 'sms')
                                <span class="badge bg-primary"><i class="fas fa-sms me-1"></i>SMS</span>
                            @else
                                <span class="badge bg-dark">All Channels</span>
                            @endif
                        </td>
                        <td>
                            <small class="text-muted">{{ Str::limit($template->message_body, 50) }}</small>
                        </td>
                        <td>
                            @if($template->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.templates.preview', $template) }}" class="btn btn-outline-primary" title="Preview">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.templates.edit', $template) }}" class="btn btn-outline-warning" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.templates.toggle-status', $template) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-{{ $template->is_active ? 'secondary' : 'success' }}" title="{{ $template->is_active ? 'Deactivate' : 'Activate' }}">
                                        <i class="fas fa-{{ $template->is_active ? 'ban' : 'check' }}"></i>
                                    </button>
                                </form>
                                <form action="{{ route('admin.templates.duplicate', $template) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-info" title="Duplicate">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </form>
                                <form action="{{ route('admin.templates.destroy', $template) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this template?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No templates found</p>
                            <a href="{{ route('admin.templates.create') }}" class="btn btn-primary">
                                Create First Template
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($templates->hasPages())
    <div class="card-footer">
        {{ $templates->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection
