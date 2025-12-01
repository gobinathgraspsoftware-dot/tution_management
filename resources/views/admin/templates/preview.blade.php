@extends('layouts.app')

@section('title', 'Preview Template')
@section('page-title', 'Preview Template')

@section('content')
<div class="page-header">
    <h1><i class="fas fa-eye me-2"></i> Preview Template</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.templates.index') }}">Templates</a></li>
            <li class="breadcrumb-item active">Preview</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Preview Card -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    @if($template->channel === 'whatsapp')
                        <i class="fab fa-whatsapp text-success me-2"></i>
                    @elseif($template->channel === 'email')
                        <i class="fas fa-envelope text-info me-2"></i>
                    @elseif($template->channel === 'sms')
                        <i class="fas fa-sms text-primary me-2"></i>
                    @else
                        <i class="fas fa-bell me-2"></i>
                    @endif
                    {{ $template->name }}
                </h5>
                <span class="badge {{ $template->is_active ? 'bg-success' : 'bg-secondary' }}">
                    {{ $template->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>
            <div class="card-body">
                @if($template->channel === 'email' && $subject)
                <div class="mb-4">
                    <label class="form-label text-muted small">EMAIL SUBJECT</label>
                    <div class="bg-light p-3 rounded border">
                        <strong>{{ $subject }}</strong>
                    </div>
                </div>
                @endif

                <div class="mb-4">
                    <label class="form-label text-muted small">MESSAGE PREVIEW (WITH SAMPLE DATA)</label>
                    @if($template->channel === 'whatsapp')
                        <!-- WhatsApp Style Preview -->
                        <div class="whatsapp-preview">
                            <div class="whatsapp-bubble">
                                {!! nl2br(e($message)) !!}
                            </div>
                            <div class="whatsapp-time">
                                {{ now()->format('H:i') }} <i class="fas fa-check-double text-info"></i>
                            </div>
                        </div>
                    @elseif($template->channel === 'email')
                        <!-- Email Style Preview -->
                        <div class="email-preview border rounded">
                            <div class="email-preview-header bg-light p-3 border-bottom">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <strong>From:</strong> Arena Matriks Edu Group &lt;noreply@arenamatriks.edu.my&gt;
                                    </div>
                                </div>
                                <div class="mt-1">
                                    <strong>Subject:</strong> {{ $subject }}
                                </div>
                            </div>
                            <div class="email-preview-body p-4">
                                {!! nl2br(e($message)) !!}
                            </div>
                        </div>
                    @else
                        <!-- Generic Preview -->
                        <div class="bg-light p-4 rounded border" style="white-space: pre-wrap;">{{ $message }}</div>
                    @endif
                </div>

                <hr>

                <div class="mb-3">
                    <label class="form-label text-muted small">ORIGINAL TEMPLATE (WITH VARIABLES)</label>
                    <div class="bg-dark text-light p-3 rounded" style="white-space: pre-wrap; font-family: monospace; font-size: 0.9rem;">{{ $template->message_body }}</div>
                </div>
            </div>
        </div>

        <!-- Sample Data Used -->
        <div class="card border-0 shadow-sm">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-database me-2"></i> Sample Data Used for Preview</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Variable</th>
                                <th>Sample Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td><code>{student_name}</code></td><td>Ahmad bin Ali</td></tr>
                            <tr><td><code>{parent_name}</code></td><td>Ali bin Abu</td></tr>
                            <tr><td><code>{teacher_name}</code></td><td>Cikgu Siti</td></tr>
                            <tr><td><code>{class_name}</code></td><td>SPM Mathematics</td></tr>
                            <tr><td><code>{subject_name}</code></td><td>Additional Mathematics</td></tr>
                            <tr><td><code>{amount}</code></td><td>250.00</td></tr>
                            <tr><td><code>{due_date}</code></td><td>{{ now()->addDays(7)->format('d M Y') }}</td></tr>
                            <tr><td><code>{invoice_number}</code></td><td>INV-2024-001234</td></tr>
                            <tr><td><code>{attendance_date}</code></td><td>{{ now()->format('d M Y') }}</td></tr>
                            <tr><td><code>{exam_name}</code></td><td>Mid-Year Examination</td></tr>
                            <tr><td><code>{score}</code></td><td>85</td></tr>
                            <tr><td><code>{grade}</code></td><td>A</td></tr>
                            <tr><td><code>{trial_date}</code></td><td>{{ now()->addDays(3)->format('d M Y') }}</td></tr>
                            <tr><td><code>{trial_time}</code></td><td>10:00 AM</td></tr>
                            <tr><td><code>{center_name}</code></td><td>Arena Matriks Edu Group</td></tr>
                            <tr><td><code>{center_phone}</code></td><td>03-1234 5678</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Template Info -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i> Template Information</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr>
                        <td class="text-muted">Name:</td>
                        <td><strong>{{ $template->name }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Category:</td>
                        <td>
                            <span class="badge bg-secondary">
                                {{ ucfirst(str_replace('_', ' ', $template->category)) }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Channel:</td>
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
                    </tr>
                    <tr>
                        <td class="text-muted">Status:</td>
                        <td>
                            @if($template->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Characters:</td>
                        <td>{{ strlen($template->message_body) }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Created:</td>
                        <td>{{ $template->created_at->format('d M Y') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Updated:</td>
                        <td>{{ $template->updated_at->format('d M Y') }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Variables Used -->
        @if($template->variables && count($template->variables) > 0)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-code me-2"></i> Variables Used</h6>
            </div>
            <div class="card-body">
                @foreach($template->variables as $variable)
                    <span class="badge bg-light text-dark border me-1 mb-1">
                        <code>{!! '{' . $variable . '}' !!}</code>
                    </span>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Actions -->
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.templates.edit', $template) }}" class="btn btn-warning">
                        <i class="fas fa-edit me-1"></i> Edit Template
                    </a>
                    <form action="{{ route('admin.templates.duplicate', $template) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-outline-info w-100">
                            <i class="fas fa-copy me-1"></i> Duplicate Template
                        </button>
                    </form>
                    <a href="{{ route('admin.templates.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Templates
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* WhatsApp Preview Styles */
.whatsapp-preview {
    background: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAyCAMAAAAp4XiDAAAAUVBMVEWFhYWDg4N3d3dtbW17e3t1dXWBgYGHh4d5. eXlycnJubm5qamoAAAeli4ueli4vLy8reli4uLeli4uDgeli4uLn5+NjsrK19RETR0teleport+fn7W1teleport+dnZ2nZubm6teleport+BgYHa2tqeli4uLS0tLS0tzMzMz'); /* WhatsApp background pattern */
    background-color: #e5ddd5;
    padding: 20px;
    border-radius: 8px;
    min-height: 200px;
}
.whatsapp-bubble {
    background: #dcf8c6;
    padding: 10px 15px;
    border-radius: 8px;
    max-width: 85%;
    margin-left: auto;
    box-shadow: 0 1px 1px rgba(0,0,0,0.1);
    white-space: pre-wrap;
    word-wrap: break-word;
}
.whatsapp-time {
    text-align: right;
    font-size: 11px;
    color: #999;
    margin-top: 5px;
}

/* Email Preview Styles */
.email-preview {
    background: #fff;
    max-height: 500px;
    overflow-y: auto;
}
.email-preview-header {
    font-size: 0.9rem;
}
.email-preview-body {
    white-space: pre-wrap;
    word-wrap: break-word;
    line-height: 1.6;
}
</style>
@endsection
