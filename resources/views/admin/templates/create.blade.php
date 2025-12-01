@extends('layouts.app')

@section('title', 'Create Template')
@section('page-title', 'Create Message Template')

@section('content')
<div class="page-header">
    <h1><i class="fas fa-plus me-2"></i> Create Message Template</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.templates.index') }}">Templates</a></li>
            <li class="breadcrumb-item active">Create</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form action="{{ route('admin.templates.store') }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <label class="form-label fw-bold">Template Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}" placeholder="e.g., Payment Reminder - First Notice" required>
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
                                    <option value="{{ $key }}" {{ old('category') == $key ? 'selected' : '' }}>
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
                                    <option value="{{ $key }}" {{ old('channel') == $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('channel')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-4" id="subjectField" style="display: none;">
                        <label class="form-label fw-bold">Email Subject</label>
                        <input type="text" name="subject" class="form-control @error('subject') is-invalid @enderror"
                               value="{{ old('subject') }}" placeholder="Enter email subject line">
                        @error('subject')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Message Body <span class="text-danger">*</span></label>
                        <textarea name="message_body" class="form-control @error('message_body') is-invalid @enderror"
                                  rows="8" id="messageBody" placeholder="Enter your message template here..." required>{{ old('message_body') }}</textarea>
                        @error('message_body')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Character count: <span id="charCount">0</span></small>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Variables Used</label>
                        <div class="row">
                            @foreach($variables as $variable)
                            <div class="col-md-4 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="variables[]"
                                           value="{{ $variable }}" id="var_{{ $variable }}"
                                           {{ in_array($variable, old('variables', [])) ? 'checked' : '' }}>
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
                            <input class="form-check-input" type="checkbox" name="is_active" id="isActive" value="1" checked>
                            <label class="form-check-label" for="isActive">Active Template</label>
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.templates.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Save Template
                        </button>
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
                <div id="previewContent" class="bg-light p-3 rounded" style="min-height: 150px; white-space: pre-wrap;">
                    <span class="text-muted">Preview will appear here...</span>
                </div>
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
                        <tr>
                            <td><code>{student_name}</code></td>
                            <td>Student's full name</td>
                        </tr>
                        <tr>
                            <td><code>{parent_name}</code></td>
                            <td>Parent's full name</td>
                        </tr>
                        <tr>
                            <td><code>{amount}</code></td>
                            <td>Payment amount</td>
                        </tr>
                        <tr>
                            <td><code>{due_date}</code></td>
                            <td>Payment due date</td>
                        </tr>
                        <tr>
                            <td><code>{class_name}</code></td>
                            <td>Class name</td>
                        </tr>
                        <tr>
                            <td><code>{attendance_date}</code></td>
                            <td>Attendance date</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Show/hide subject field based on channel
    $('#channelSelect').change(function() {
        var isEmail = $(this).val() === 'email' || $(this).val() === 'all';
        $('#subjectField').toggle(isEmail);
    }).trigger('change');

    // Character count
    $('#messageBody').on('input', function() {
        var count = $(this).val().length;
        $('#charCount').text(count);

        // Update preview
        var preview = $(this).val() || '<span class="text-muted">Preview will appear here...</span>';
        $('#previewContent').html(preview);
    });

    // Click variable to insert
    $('.variable-tag').click(function() {
        var variable = '{' + $(this).data('var') + '}';
        var textarea = $('#messageBody');
        var cursorPos = textarea[0].selectionStart;
        var textBefore = textarea.val().substring(0, cursorPos);
        var textAfter = textarea.val().substring(cursorPos);

        textarea.val(textBefore + variable + textAfter);
        textarea.trigger('input');

        // Check the corresponding checkbox
        $('#var_' + $(this).data('var')).prop('checked', true);
    });
});
</script>
@endpush
@endsection
