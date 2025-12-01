@extends('layouts.app')

@section('title', 'Send Notification')
@section('page-title', 'Send Notification')

@section('content')
<div class="page-header">
    <h1><i class="fas fa-paper-plane me-2"></i> Send Notification</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.notifications.index') }}">Notifications</a></li>
            <li class="breadcrumb-item active">Send</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form action="{{ route('admin.notifications.send') }}" method="POST" id="notificationForm">
                    @csrf

                    <!-- Recipients -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">Recipients</label>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="recipient_type" id="recipientIndividual" value="individual" checked>
                                    <label class="form-check-label" for="recipientIndividual">
                                        Individual Users
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="recipient_type" id="recipientGroup" value="group">
                                    <label class="form-check-label" for="recipientGroup">
                                        User Group
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="recipient_type" id="recipientAll" value="all">
                                    <label class="form-check-label" for="recipientAll">
                                        All Users
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Individual Selection -->
                        <div id="individualSection" class="mt-3">
                            <label class="form-label">Select Users</label>
                            <select name="user_ids[]" class="form-select" id="userSelect" multiple>
                                <optgroup label="Students">
                                    @foreach($students as $student)
                                        <option value="{{ $student->user_id }}">
                                            {{ $student->user->name }} ({{ $student->student_id }})
                                        </option>
                                    @endforeach
                                </optgroup>
                                <optgroup label="Parents">
                                    @foreach($parents as $parent)
                                        <option value="{{ $parent->user_id }}">
                                            {{ $parent->user->name }} ({{ $parent->parent_id }})
                                        </option>
                                    @endforeach
                                </optgroup>
                            </select>
                            <small class="text-muted">Hold Ctrl/Cmd to select multiple users</small>
                        </div>

                        <!-- Group Selection -->
                        <div id="groupSection" class="mt-3" style="display: none;">
                            <label class="form-label">Select Group</label>
                            <select name="group" class="form-select">
                                <option value="">-- Select Group --</option>
                                <option value="students">All Active Students</option>
                                <option value="parents">All Parents</option>
                                <option value="teachers">All Teachers</option>
                            </select>
                        </div>

                        @error('recipient_type')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Channels -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">Notification Channels</label>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="channel[]" value="whatsapp" id="channelWhatsapp" checked>
                                    <label class="form-check-label" for="channelWhatsapp">
                                        <i class="fab fa-whatsapp text-success me-1"></i> WhatsApp
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="channel[]" value="email" id="channelEmail">
                                    <label class="form-check-label" for="channelEmail">
                                        <i class="fas fa-envelope text-info me-1"></i> Email
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="channel[]" value="sms" id="channelSms">
                                    <label class="form-check-label" for="channelSms">
                                        <i class="fas fa-sms text-primary me-1"></i> SMS
                                    </label>
                                </div>
                            </div>
                        </div>
                        @error('channel')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Template Selection -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">Message Template (Optional)</label>
                        <select name="template_id" class="form-select" id="templateSelect">
                            <option value="">-- Custom Message --</option>
                            @foreach($templates as $category => $categoryTemplates)
                                <optgroup label="{{ ucfirst(str_replace('_', ' ', $category)) }}">
                                    @foreach($categoryTemplates as $template)
                                        <option value="{{ $template->id }}" data-message="{{ $template->message_body }}" data-subject="{{ $template->subject }}">
                                            {{ $template->name }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>

                    <!-- Subject (for Email) -->
                    <div class="mb-4" id="subjectSection" style="display: none;">
                        <label class="form-label fw-bold">Email Subject</label>
                        <input type="text" name="subject" class="form-control" placeholder="Enter email subject" value="{{ old('subject') }}">
                        @error('subject')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Message -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">Message</label>
                        <textarea name="message" class="form-control" rows="6" placeholder="Enter your message here..." required>{{ old('message') }}</textarea>
                        <small class="text-muted">
                            Available variables: {student_name}, {parent_name}, {class_name}, {amount}, {due_date}
                        </small>
                        @error('message')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Priority -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">Priority</label>
                        <select name="priority" class="form-select">
                            <option value="normal" selected>Normal</option>
                            <option value="low">Low</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>

                    <!-- Schedule -->
                    <div class="mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="scheduleCheck">
                            <label class="form-check-label" for="scheduleCheck">
                                Schedule for later
                            </label>
                        </div>
                        <div id="scheduleSection" class="mt-2" style="display: none;">
                            <input type="datetime-local" name="schedule_at" class="form-control">
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.notifications.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-1"></i> Send Notification
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Preview Card -->
        <div class="card border-0 shadow-sm">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-eye me-2"></i> Preview</h6>
            </div>
            <div class="card-body">
                <div id="previewContent">
                    <p class="text-muted text-center">Message preview will appear here</p>
                </div>
            </div>
        </div>

        <!-- Help Card -->
        <div class="card border-0 shadow-sm mt-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i> Tips</h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0 small">
                    <li class="mb-2">
                        <i class="fas fa-check text-success me-1"></i>
                        Use templates for consistent messaging
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check text-success me-1"></i>
                        WhatsApp messages support emojis
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check text-success me-1"></i>
                        SMS has 160 character limit
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check text-success me-1"></i>
                        High priority skips the queue
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Recipient type toggle
    $('input[name="recipient_type"]').change(function() {
        var val = $(this).val();
        $('#individualSection').toggle(val === 'individual');
        $('#groupSection').toggle(val === 'group');
    });

    // Email channel toggle
    $('#channelEmail').change(function() {
        $('#subjectSection').toggle($(this).is(':checked'));
    });

    // Schedule toggle
    $('#scheduleCheck').change(function() {
        $('#scheduleSection').toggle($(this).is(':checked'));
    });

    // Template selection
    $('#templateSelect').change(function() {
        var option = $(this).find('option:selected');
        if (option.val()) {
            $('textarea[name="message"]').val(option.data('message'));
            $('input[name="subject"]').val(option.data('subject'));
        }
    });

    // Live preview
    $('textarea[name="message"]').on('input', function() {
        var message = $(this).val();
        if (message) {
            $('#previewContent').html('<div class="bg-light p-3 rounded" style="white-space: pre-wrap;">' + message + '</div>');
        } else {
            $('#previewContent').html('<p class="text-muted text-center">Message preview will appear here</p>');
        }
    });
});
</script>
@endpush
@endsection
