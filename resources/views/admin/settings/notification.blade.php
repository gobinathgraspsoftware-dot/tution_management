@extends('layouts.app')

@section('title', 'Notification Settings')
@section('page-title', 'Notification Settings')

@section('content')
<div class="page-header">
    <h1><i class="fas fa-cog me-2"></i> Notification Settings</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.notifications.index') }}">Notifications</a></li>
            <li class="breadcrumb-item active">Settings</li>
        </ol>
    </nav>
</div>

<div class="row">
    <!-- WhatsApp Settings -->
    <div class="col-md-6 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fab fa-whatsapp me-2"></i> WhatsApp (Ultra Messenger)</h5>
            </div>
            <div class="card-body">
                <!-- Status -->
                <div class="alert {{ $whatsappStatus['connected'] ? 'alert-success' : 'alert-warning' }} mb-4">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-{{ $whatsappStatus['connected'] ? 'check-circle' : 'exclamation-triangle' }} fa-2x me-3"></i>
                        <div>
                            <strong>{{ $whatsappStatus['connected'] ? 'Connected' : 'Not Connected' }}</strong>
                            @if($whatsappStatus['connected'] && isset($whatsappStatus['phone']))
                                <br><small>Phone: {{ $whatsappStatus['phone'] }}</small>
                            @elseif(!$whatsappStatus['connected'] && isset($whatsappStatus['error']))
                                <br><small>{{ $whatsappStatus['error'] }}</small>
                            @endif
                        </div>
                    </div>
                </div>

                <form action="{{ route('admin.notifications.settings.update') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="whatsapp_enabled" id="whatsappEnabled"
                                   {{ config('notification.whatsapp.enabled') ? 'checked' : '' }}>
                            <label class="form-check-label" for="whatsappEnabled">Enable WhatsApp Notifications</label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Instance ID</label>
                        <input type="text" name="whatsapp_instance_id" class="form-control"
                               value="{{ config('notification.whatsapp.instance_id') }}" placeholder="Your Ultra Messenger Instance ID">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">API Token</label>
                        <input type="password" name="whatsapp_token" class="form-control"
                               value="{{ config('notification.whatsapp.token') ? '********' : '' }}" placeholder="Your Ultra Messenger Token">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Default Country Code</label>
                        <input type="text" name="whatsapp_country_code" class="form-control"
                               value="{{ config('notification.whatsapp.default_country_code', '60') }}" placeholder="60">
                        <small class="text-muted">Country code without + (e.g., 60 for Malaysia)</small>
                    </div>

                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-1"></i> Save WhatsApp Settings
                    </button>
                </form>

                <hr>

                <!-- Test WhatsApp -->
                <h6>Test Connection</h6>
                <form action="{{ route('admin.notifications.test-whatsapp') }}" method="POST" class="row g-2">
                    @csrf
                    <div class="col-8">
                        <input type="text" name="phone" class="form-control" placeholder="Phone number (e.g., 60123456789)" required>
                    </div>
                    <div class="col-4">
                        <button type="submit" class="btn btn-outline-success w-100">
                            <i class="fas fa-paper-plane me-1"></i> Test
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Email Settings -->
    <div class="col-md-6 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-envelope me-2"></i> Email (SMTP)</h5>
            </div>
            <div class="card-body">
                <div class="alert {{ config('notification.email.enabled') ? 'alert-success' : 'alert-warning' }} mb-4">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-{{ config('notification.email.enabled') ? 'check-circle' : 'exclamation-triangle' }} fa-2x me-3"></i>
                        <div>
                            <strong>{{ config('notification.email.enabled') ? 'Enabled' : 'Disabled' }}</strong>
                            <br><small>From: {{ config('notification.email.from_address') }}</small>
                        </div>
                    </div>
                </div>

                <form action="{{ route('admin.notifications.settings.update') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="email_enabled" id="emailEnabled"
                                   {{ config('notification.email.enabled') ? 'checked' : '' }}>
                            <label class="form-check-label" for="emailEnabled">Enable Email Notifications</label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">From Address</label>
                        <input type="email" name="email_from_address" class="form-control"
                               value="{{ config('notification.email.from_address') }}" placeholder="noreply@example.com">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">From Name</label>
                        <input type="text" name="email_from_name" class="form-control"
                               value="{{ config('notification.email.from_name') }}" placeholder="Arena Matriks">
                    </div>

                    <div class="alert alert-info small">
                        <i class="fas fa-info-circle me-1"></i>
                        SMTP settings are configured in .env file (MAIL_HOST, MAIL_PORT, MAIL_USERNAME, MAIL_PASSWORD)
                    </div>

                    <button type="submit" class="btn btn-info text-white">
                        <i class="fas fa-save me-1"></i> Save Email Settings
                    </button>
                </form>

                <hr>

                <!-- Test Email -->
                <h6>Test Email</h6>
                <form action="{{ route('admin.notifications.test-email') }}" method="POST" class="row g-2">
                    @csrf
                    <div class="col-8">
                        <input type="email" name="email" class="form-control" placeholder="test@example.com" required>
                    </div>
                    <div class="col-4">
                        <button type="submit" class="btn btn-outline-info w-100">
                            <i class="fas fa-paper-plane me-1"></i> Test
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- SMS Settings -->
    <div class="col-md-6 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-sms me-2"></i> SMS Gateway</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-secondary mb-4">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-info-circle fa-2x me-3"></i>
                        <div>
                            <strong>{{ config('notification.sms.enabled') ? 'Enabled' : 'Disabled' }}</strong>
                            <br><small>Provider: {{ ucfirst(config('notification.sms.provider', 'Not configured')) }}</small>
                        </div>
                    </div>
                </div>

                <form action="{{ route('admin.notifications.settings.update') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="sms_enabled" id="smsEnabled"
                                   {{ config('notification.sms.enabled') ? 'checked' : '' }}>
                            <label class="form-check-label" for="smsEnabled">Enable SMS Notifications</label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">SMS Provider</label>
                        <select name="sms_provider" class="form-select">
                            <option value="twilio" {{ config('notification.sms.provider') == 'twilio' ? 'selected' : '' }}>Twilio</option>
                            <option value="nexmo" {{ config('notification.sms.provider') == 'nexmo' ? 'selected' : '' }}>Nexmo/Vonage</option>
                            <option value="custom" {{ config('notification.sms.provider') == 'custom' ? 'selected' : '' }}>Custom Gateway</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Sender ID</label>
                        <input type="text" name="sms_sender_id" class="form-control"
                               value="{{ config('notification.sms.sender_id') }}" placeholder="ArenaMatriks">
                    </div>

                    <div class="alert alert-info small">
                        <i class="fas fa-info-circle me-1"></i>
                        SMS API credentials are configured in .env file (SMS_API_KEY, SMS_API_SECRET)
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Save SMS Settings
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Queue Settings -->
    <div class="col-md-6 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0"><i class="fas fa-tasks me-2"></i> Queue Settings</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.notifications.settings.update') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Process Limit (per batch)</label>
                        <input type="number" name="queue_process_limit" class="form-control"
                               value="{{ config('notification.queue.process_limit', 50) }}" min="10" max="200">
                        <small class="text-muted">Maximum messages to process per scheduled run</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Schedule Interval (minutes)</label>
                        <input type="number" name="queue_schedule_interval" class="form-control"
                               value="{{ config('notification.queue.schedule_interval', 1) }}" min="1" max="60">
                        <small class="text-muted">How often the queue processor runs</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Failed Message Retention (days)</label>
                        <input type="number" name="queue_failed_retention" class="form-control"
                               value="{{ config('notification.queue.failed_retention_days', 30) }}" min="1" max="365">
                    </div>

                    <button type="submit" class="btn btn-secondary">
                        <i class="fas fa-save me-1"></i> Save Queue Settings
                    </button>
                </form>

                <hr>

                <h6>Cron Job Setup</h6>
                <div class="bg-light p-3 rounded">
                    <code class="small">* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1</code>
                </div>
                <small class="text-muted mt-2 d-block">Add this to your server's crontab to enable automatic queue processing</small>
            </div>
        </div>
    </div>
</div>
@endsection
