@extends('layouts.app')

@section('title', 'View Template')
@section('page-title', 'View Template')

@section('content')
<div class="page-header">
    <h1><i class="fas fa-file-alt me-2"></i> View Template</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.templates.index') }}">Templates</a></li>
            <li class="breadcrumb-item active">View</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">{{ $template->name }}</h5>
                <div>
                    @if($template->is_active)
                        <span class="badge bg-success">Active</span>
                    @else
                        <span class="badge bg-secondary">Inactive</span>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <!-- Template Details -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label text-muted small">CATEGORY</label>
                        <p>
                            <span class="badge bg-secondary">
                                {{ ucfirst(str_replace('_', ' ', $template->category)) }}
                            </span>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">CHANNEL</label>
                        <p>
                            @if($template->channel === 'whatsapp')
                                <span class="badge bg-success"><i class="fab fa-whatsapp me-1"></i>WhatsApp</span>
                            @elseif($template->channel === 'email')
                                <span class="badge bg-info"><i class="fas fa-envelope me-1"></i>Email</span>
                            @elseif($template->channel === 'sms')
                                <span class="badge bg-primary"><i class="fas fa-sms me-1"></i>SMS</span>
                            @else
                                <span class="badge bg-dark">All Channels</span>
                            @endif
                        </p>
                    </div>
                </div>

                @if($template->subject)
                <div class="mb-4">
                    <label class="form-label text-muted small">EMAIL SUBJECT</label>
                    <div class="bg-light p-3 rounded">
                        {{ $template->subject }}
                    </div>
                </div>
                @endif

                <div class="mb-4">
                    <label class="form-label text-muted small">MESSAGE BODY</label>
                    <div class="bg-light p-3 rounded" style="white-space: pre-wrap; font-family: monospace;">{{ $template->message_body }}</div>
                </div>

                @if($template->variables && count($template->variables) > 0)
                <div class="mb-4">
                    <label class="form-label text-muted small">VARIABLES USED</label>
                    <div>
                        @foreach($template->variables as $variable)
                            <span class="badge bg-primary me-1 mb-1">
                                <code class="text-white">{!! '{' . $variable . '}' !!}</code>
                            </span>
                        @endforeach
                    </div>
                </div>
                @endif

                <hr>

                <div class="row text-muted small">
                    <div class="col-md-6">
                        <i class="fas fa-calendar me-1"></i> Created: {{ $template->created_at->format('d M Y, H:i') }}
                    </div>
                    <div class="col-md-6">
                        <i class="fas fa-edit me-1"></i> Updated: {{ $template->updated_at->format('d M Y, H:i') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Actions Card -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-cog me-2"></i> Actions</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.templates.preview', $template) }}" class="btn btn-primary">
                        <i class="fas fa-eye me-1"></i> Preview with Sample Data
                    </a>
                    <a href="{{ route('admin.templates.edit', $template) }}" class="btn btn-warning">
                        <i class="fas fa-edit me-1"></i> Edit Template
                    </a>
                    <form action="{{ route('admin.templates.toggle-status', $template) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-outline-{{ $template->is_active ? 'secondary' : 'success' }} w-100">
                            <i class="fas fa-{{ $template->is_active ? 'ban' : 'check' }} me-1"></i>
                            {{ $template->is_active ? 'Deactivate' : 'Activate' }}
                        </button>
                    </form>
                    <form action="{{ route('admin.templates.duplicate', $template) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-outline-info w-100">
                            <i class="fas fa-copy me-1"></i> Duplicate
                        </button>
                    </form>
                    <hr>
                    <form action="{{ route('admin.templates.destroy', $template) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this template?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger w-100">
                            <i class="fas fa-trash me-1"></i> Delete Template
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Statistics Card -->
        <div class="card border-0 shadow-sm">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i> Usage Statistics</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr>
                        <td class="text-muted">WhatsApp Messages:</td>
                        <td class="text-end">{{ $template->whatsappQueue()->count() }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Emails Sent:</td>
                        <td class="text-end">{{ $template->emailQueue()->count() }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Character Count:</td>
                        <td class="text-end">{{ strlen($template->message_body) }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Back Button -->
        <div class="mt-3">
            <a href="{{ route('admin.templates.index') }}" class="btn btn-outline-secondary w-100">
                <i class="fas fa-arrow-left me-1"></i> Back to Templates
            </a>
        </div>
    </div>
</div>
@endsection
