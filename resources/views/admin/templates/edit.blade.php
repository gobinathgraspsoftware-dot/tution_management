@extends('layouts.app')

@section('title', 'Edit Template')
@section('page-title', 'Edit Message Template')

@section('content')
<div class="page-header">
    <h1><i class="fas fa-edit me-2"></i> Edit Message Template</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.templates.index') }}">Templates</a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form action="{{ route('admin.templates.update', $template) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label class="form-label fw-bold">Template Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $template->name) }}" placeholder="e.g., Payment Reminder - First Notice" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Category <span class="text-danger">*</span></label>
                            <select name="category" class="form-select @error('category') is-invalid @enderror" required>
                                <option value="">-- Select Category --</option>
                                @foreach($categories as $key => $label)
                                    <option value="{{ $key }}" {{ old('category', $template->category) == $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Channel <span class="text-danger">*</span></label>
                            <select name="channel" class="form-select @error('channel') is-invalid @enderror" id="channelSelect" required>
                                <option value="">-- Select Channel --</option>
                                @foreach($channels as $key => $label)
                                    <option value="{{ $key }}" {{ old('channel', $template->channel) == $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('channel')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-4" id="subjectField" style="{{ in_array($template->channel, ['email', 'all']) ? '' : 'display: none;' }}">
                        <label class="form-label fw-bold">Email Subject</label>
                        <input type="text" name="subject" class="form-control @error('subject') is-invalid @enderror"
                               value="{{ old('subject', $template->subject) }}" placeholder="Enter email subject line">
                        @error('subject')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Message Body <span class="text-danger">*</span></label>
                        <textarea name="message_body" class="form-control @error('message_body') is-invalid @enderror"
                                  rows="8" id="messageBody" placeholder="Enter your message template here..." required>{{ old('message_body', $template->message_body) }}</textarea>
                        @error('message_body')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Character count: <span id="charCount">{{ strlen($template->message_body) }}</span></small>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Variables Used</label>
                        <div class="row">
                            @php $templateVars = $template->variables ?? []; @endphp
                            @foreach($variables as $variable)
                            <div class="col-md-4 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="variables[]"
                                           value="{{ $variable }}" id="var_{{ $variable }}"
                                           {{ in_array($variable, old('variables', $templateVars)) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="var_{{ $variable }}">
                                        <code class="text-primary variable-tag" data-var="{{ $variable }}">{!! '{' . $variable . '}' !!}</code>
                                    </label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <small class="text-muted">Click on variables to insert them into the message</small>
                    </div>

                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" id="isActive" value="1"
                                   {{ old('is_active', $template->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="isActive">Active Template</label>
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.templates.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Cancel
                        </a>
                        <div>
                            <a href="{{ route('admin.templates.preview', $template) }}" class="btn btn-outline-primary me-2">
                                <i class="fas fa-eye me-1"></i> Preview
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Update Template
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Preview Card -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-eye me-2"></i> Live Preview</h6>
            </div>
            <div class="card-body">
                <div id="previewContent" class="bg-light p-3 rounded" style="min-height: 150px; white-space: pre-wrap;">{{ $template->message_body }}</div>
            </div>
        </div>

        <!-- Template Info -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i> Template Info</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm small mb-0">
                    <tbody>
                        <tr>
                            <td class="text-muted">Created:</td>
                            <td>{{ $template->created_at->format('d M Y H:i') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Updated:</td>
                            <td>{{ $template->updated_at->format('d M Y H:i') }}</td>
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
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Variables Reference -->
        <div class="card border-0 shadow-sm">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-code me-2"></i> Variable Reference</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm small mb-0">
                    <tbody>
                        <tr><td><code>{student_name}</code></td><td>Student's name</td></tr>
                        <tr><td><code>{parent_name}</code></td><td>Parent's name</td></tr>
                        <tr><td><code>{amount}</code></td><td>Payment amount</td></tr>
                        <tr><td><code>{due_date}</code></td><td>Due date</td></tr>
                        <tr><td><code>{class_name}</code></td><td>Class name</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $('#channelSelect').change(function() {
        var isEmail = $(this).val() === 'email' || $(this).val() === 'all';
        $('#subjectField').toggle(isEmail);
    });

    $('#messageBody').on('input', function() {
        var count = $(this).val().length;
        $('#charCount').text(count);
        var preview = $(this).val() || '<span class="text-muted">Preview will appear here...</span>';
        $('#previewContent').html(preview);
    });

    $('.variable-tag').click(function() {
        var variable = '{' + $(this).data('var') + '}';
        var textarea = $('#messageBody');
        var cursorPos = textarea[0].selectionStart;
        var textBefore = textarea.val().substring(0, cursorPos);
        var textAfter = textarea.val().substring(cursorPos);
        textarea.val(textBefore + variable + textAfter);
        textarea.trigger('input');
        $('#var_' + $(this).data('var')).prop('checked', true);
    });
});
</script>
@endpush
@endsection
