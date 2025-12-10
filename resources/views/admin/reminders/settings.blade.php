@extends('layouts.app')

@section('title', 'Reminder Settings')
@section('page-title', 'Reminder Settings')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="fas fa-cog me-2"></i> Reminder Settings</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.reminders.index') }}">Reminders</a></li>
                <li class="breadcrumb-item active">Settings</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('admin.reminders.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back
    </a>
</div>

<form action="{{ route('admin.reminders.update-settings') }}" method="POST">
    @csrf
    @method('PUT')

    <div class="row">
        <div class="col-lg-8">
            <!-- General Settings -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-sliders-h me-2"></i> General Settings</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="reminderEnabled"
                                       name="reminder_enabled" value="1"
                                       {{ ($settings['reminder_enabled'] ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="reminderEnabled">
                                    <strong>Enable Payment Reminders</strong>
                                </label>
                            </div>
                            <p class="text-muted small">When enabled, automatic reminders will be sent on scheduled days.</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Default Channel</label>
                            <select name="default_channel" class="form-select">
                                <option value="whatsapp" {{ ($settings['default_channel'] ?? '') == 'whatsapp' ? 'selected' : '' }}>
                                    WhatsApp
                                </option>
                                <option value="email" {{ ($settings['default_channel'] ?? '') == 'email' ? 'selected' : '' }}>
                                    Email
                                </option>
                                <option value="sms" {{ ($settings['default_channel'] ?? '') == 'sms' ? 'selected' : '' }}>
                                    SMS
                                </option>
                            </select>
                        </div>
                    </div>

                    <hr>

                    <h6 class="mb-3"><i class="fas fa-calendar-alt me-2"></i> Reminder Schedule</h6>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Reminders are automatically scheduled on the <strong>10th</strong> (first), <strong>18th</strong> (second), and <strong>24th</strong> (final) of each month.
                    </div>
                    <p class="text-muted small">These dates can be modified in the config/payment_reminders.php configuration file.</p>
                </div>
            </div>

            <!-- Overdue Settings -->
            <div class="card mb-4">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i> Overdue Reminder Settings</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="autoOverdue"
                                       name="auto_overdue_reminder" value="1"
                                       {{ ($settings['auto_overdue_reminder'] ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="autoOverdue">
                                    <strong>Auto Send Overdue Reminders</strong>
                                </label>
                            </div>
                            <p class="text-muted small">Automatically send reminders for overdue payments.</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Overdue Reminder Interval (Days)</label>
                            <input type="number" name="overdue_reminder_interval" class="form-control"
                                   value="{{ $settings['overdue_reminder_interval'] ?? 7 }}" min="1" max="30">
                            <small class="text-muted">Days between overdue reminders for the same invoice.</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Retry Settings -->
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-redo me-2"></i> Retry Settings</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Max Retry Attempts</label>
                            <input type="number" class="form-control" value="{{ $settings['max_retry_attempts'] ?? 3 }}" disabled>
                            <small class="text-muted">Maximum times to retry a failed reminder.</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Retry Delay (Hours)</label>
                            <input type="number" class="form-control" value="{{ $settings['retry_delay_hours'] ?? 2 }}" disabled>
                            <small class="text-muted">Hours to wait before retrying.</small>
                        </div>
                    </div>
                    <p class="text-muted small mt-2">
                        <i class="fas fa-lock me-1"></i> These settings are configured in the system config file.
                    </p>
                </div>
            </div>

            <!-- Message Templates -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-envelope-open-text me-2"></i> Message Templates</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-4">Customize the reminder messages. Use placeholders like {student_name}, {amount}, {due_date}, {invoice_number}.</p>

                    <div class="mb-4">
                        <label class="form-label"><strong>First Reminder (10th)</strong></label>
                        <textarea name="template_first" class="form-control" rows="4"
                                  placeholder="Enter first reminder template...">{{ $templates['first'] ?? '' }}</textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label"><strong>Second Reminder (18th)</strong></label>
                        <textarea name="template_second" class="form-control" rows="4"
                                  placeholder="Enter second reminder template...">{{ $templates['second'] ?? '' }}</textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label"><strong>Final Reminder (24th)</strong></label>
                        <textarea name="template_final" class="form-control" rows="4"
                                  placeholder="Enter final reminder template...">{{ $templates['final'] ?? '' }}</textarea>
                    </div>

                    <div class="mb-0">
                        <label class="form-label"><strong>Overdue Notice</strong></label>
                        <textarea name="template_overdue" class="form-control" rows="4"
                                  placeholder="Enter overdue notice template...">{{ $templates['overdue'] ?? '' }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Placeholders Reference -->
            <div class="card mb-4">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0"><i class="fas fa-code me-2"></i> Available Placeholders</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tbody>
                            <tr>
                                <td><code>{parent_name}</code></td>
                                <td>Parent/Guardian name</td>
                            </tr>
                            <tr>
                                <td><code>{student_name}</code></td>
                                <td>Student name</td>
                            </tr>
                            <tr>
                                <td><code>{amount}</code></td>
                                <td>Outstanding amount</td>
                            </tr>
                            <tr>
                                <td><code>{due_date}</code></td>
                                <td>Payment due date</td>
                            </tr>
                            <tr>
                                <td><code>{invoice_number}</code></td>
                                <td>Invoice number</td>
                            </tr>
                            <tr>
                                <td><code>{payment_link}</code></td>
                                <td>Online payment URL</td>
                            </tr>
                            <tr>
                                <td><code>{days_overdue}</code></td>
                                <td>Days past due</td>
                            </tr>
                            <tr>
                                <td><code>{centre_name}</code></td>
                                <td>Centre name</td>
                            </tr>
                            <tr>
                                <td><code>{centre_phone}</code></td>
                                <td>Centre phone</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Current Schedule -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="fas fa-calendar-check me-2"></i> Current Schedule</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span><span class="badge bg-primary">10th</span> First Reminder</span>
                        <i class="fas fa-check-circle text-success"></i>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span><span class="badge bg-warning text-dark">18th</span> Second Reminder</span>
                        <i class="fas fa-check-circle text-success"></i>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span><span class="badge bg-danger">24th</span> Final Reminder</span>
                        <i class="fas fa-check-circle text-success"></i>
                    </div>
                </div>
            </div>

            <!-- Tips -->
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-lightbulb me-2"></i> Tips</h6>
                </div>
                <div class="card-body">
                    <ul class="mb-0 ps-3">
                        <li class="mb-2">Keep messages concise for WhatsApp</li>
                        <li class="mb-2">Include payment link for easy access</li>
                        <li class="mb-2">Use friendly tone for early reminders</li>
                        <li class="mb-2">Be more urgent in final notices</li>
                        <li>Always include contact information</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between">
                <a href="{{ route('admin.reminders.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-1"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Save Settings
                </button>
            </div>
        </div>
    </div>
</form>
@endsection
