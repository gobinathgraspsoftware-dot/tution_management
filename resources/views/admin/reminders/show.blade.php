@extends('layouts.app')

@section('title', 'Reminder Details')
@section('page-title', 'Reminder Details')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.reminders.index') }}">Reminders</a></li>
            <li class="breadcrumb-item active">Details</li>
        </ol>
    </nav>

    <div class="row">
        <!-- Reminder Information -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-bell me-2"></i> Reminder Information
                    </h5>
                    <div>
                        @if(in_array($reminder->status, ['scheduled', 'pending']))
                            <form action="{{ route('admin.reminders.cancel', $reminder) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('Cancel this reminder?')">
                                    <i class="fas fa-times me-1"></i> Cancel
                                </button>
                            </form>
                        @endif
                        @if($reminder->status === 'failed')
                            <form action="{{ route('admin.reminders.resend', $reminder) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-redo me-1"></i> Resend
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">Reminder ID:</th>
                                    <td>#{{ $reminder->id }}</td>
                                </tr>
                                <tr>
                                    <th>Type:</th>
                                    <td>
                                        @switch($reminder->reminder_type)
                                            @case('first')
                                                <span class="badge bg-info">1st Reminder (10th)</span>
                                                @break
                                            @case('second')
                                                <span class="badge bg-warning">2nd Reminder (18th)</span>
                                                @break
                                            @case('final')
                                                <span class="badge bg-danger">Final Reminder (24th)</span>
                                                @break
                                            @case('overdue')
                                                <span class="badge bg-dark">Overdue</span>
                                                @break
                                            @case('follow_up')
                                                <span class="badge bg-secondary">Follow-up</span>
                                                @break
                                            @default
                                                <span class="badge bg-secondary">{{ ucfirst($reminder->reminder_type) }}</span>
                                        @endswitch
                                    </td>
                                </tr>
                                <tr>
                                    <th>Channel:</th>
                                    <td>
                                        @if($reminder->channel === 'whatsapp')
                                            <i class="fab fa-whatsapp text-success me-1"></i> WhatsApp
                                        @elseif($reminder->channel === 'email')
                                            <i class="fas fa-envelope text-primary me-1"></i> Email
                                        @else
                                            <i class="fas fa-sms text-info me-1"></i> SMS
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td>
                                        @switch($reminder->status)
                                            @case('scheduled')
                                                <span class="badge bg-info">Scheduled</span>
                                                @break
                                            @case('pending')
                                                <span class="badge bg-warning">Pending</span>
                                                @break
                                            @case('sent')
                                                <span class="badge bg-success">Sent</span>
                                                @break
                                            @case('delivered')
                                                <span class="badge bg-success">Delivered</span>
                                                @break
                                            @case('failed')
                                                <span class="badge bg-danger">Failed</span>
                                                @break
                                            @case('cancelled')
                                                <span class="badge bg-secondary">Cancelled</span>
                                                @break
                                            @default
                                                <span class="badge bg-secondary">{{ ucfirst($reminder->status) }}</span>
                                        @endswitch
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">Scheduled Date:</th>
                                    <td>{{ $reminder->scheduled_date?->format('d M Y') ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Sent At:</th>
                                    <td>{{ $reminder->sent_at?->format('d M Y, h:i A') ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Attempts:</th>
                                    <td>{{ $reminder->attempts ?? 0 }}</td>
                                </tr>
                                <tr>
                                    <th>Created By:</th>
                                    <td>{{ $reminder->createdBy?->name ?? 'System' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($reminder->channel === 'whatsapp' && $reminder->recipient_phone)
                        <div class="alert alert-light border">
                            <strong>Recipient Phone:</strong> {{ $reminder->recipient_phone }}
                        </div>
                    @endif

                    @if($reminder->channel === 'email' && $reminder->recipient_email)
                        <div class="alert alert-light border">
                            <strong>Recipient Email:</strong> {{ $reminder->recipient_email }}
                        </div>
                    @endif

                    @if($reminder->error_message)
                        <div class="alert alert-danger">
                            <strong><i class="fas fa-exclamation-triangle me-1"></i> Error:</strong>
                            {{ $reminder->error_message }}
                        </div>
                    @endif

                    @if($reminder->message_content)
                        <div class="card bg-light mt-3">
                            <div class="card-header">
                                <strong>Message Content</strong>
                            </div>
                            <div class="card-body">
                                <pre class="mb-0" style="white-space: pre-wrap;">{{ $reminder->message_content }}</pre>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Invoice Details -->
            @if($reminder->invoice)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-file-invoice me-2"></i> Invoice Details
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">Invoice Number:</th>
                                    <td>
                                        <a href="{{ route('admin.invoices.show', $reminder->invoice) }}">
                                            {{ $reminder->invoice->invoice_number }}
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Package:</th>
                                    <td>{{ $reminder->invoice->enrollment?->package?->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Due Date:</th>
                                    <td>
                                        {{ $reminder->invoice->due_date?->format('d M Y') ?? 'N/A' }}
                                        @if($reminder->invoice->due_date && $reminder->invoice->due_date->isPast())
                                            <span class="badge bg-danger ms-1">Overdue</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">Total Amount:</th>
                                    <td><strong>RM {{ number_format($reminder->invoice->total_amount, 2) }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Paid Amount:</th>
                                    <td class="text-success">RM {{ number_format($reminder->invoice->paid_amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>Balance:</th>
                                    <td class="text-danger"><strong>RM {{ number_format($reminder->invoice->balance, 2) }}</strong></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Installment Details (if applicable) -->
            @if($reminder->installment)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-check me-2"></i> Installment Details
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">Installment #:</th>
                                    <td>{{ $reminder->installment->installment_number }}</td>
                                </tr>
                                <tr>
                                    <th>Due Date:</th>
                                    <td>{{ $reminder->installment->due_date?->format('d M Y') ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">Amount:</th>
                                    <td>RM {{ number_format($reminder->installment->amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td>
                                        @if($reminder->installment->status === 'paid')
                                            <span class="badge bg-success">Paid</span>
                                        @elseif($reminder->installment->status === 'overdue')
                                            <span class="badge bg-danger">Overdue</span>
                                        @else
                                            <span class="badge bg-warning">{{ ucfirst($reminder->installment->status) }}</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Student & Parent Information -->
        <div class="col-md-4">
            @if($reminder->invoice?->student)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-user-graduate me-2"></i> Student Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" style="width: 60px; height: 60px; font-size: 24px;">
                            {{ strtoupper(substr($reminder->invoice->student->user->name ?? 'S', 0, 1)) }}
                        </div>
                        <h6 class="mt-2 mb-0">{{ $reminder->invoice->student->user->name ?? 'N/A' }}</h6>
                        <small class="text-muted">{{ $reminder->invoice->student->student_id ?? '' }}</small>
                    </div>
                    <hr>
                    <p class="mb-2">
                        <i class="fas fa-envelope me-2 text-muted"></i>
                        {{ $reminder->invoice->student->user->email ?? 'N/A' }}
                    </p>
                    <p class="mb-2">
                        <i class="fas fa-phone me-2 text-muted"></i>
                        {{ $reminder->invoice->student->user->phone ?? 'N/A' }}
                    </p>
                    <a href="{{ route('admin.students.show', $reminder->invoice->student) }}" class="btn btn-outline-primary btn-sm w-100 mt-2">
                        View Student Profile
                    </a>
                </div>
            </div>

            @if($reminder->invoice->student->parent)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-user-friends me-2"></i> Parent Information
                    </h5>
                </div>
                <div class="card-body">
                    <p class="mb-2">
                        <strong>{{ $reminder->invoice->student->parent->user->name ?? 'N/A' }}</strong>
                    </p>
                    <p class="mb-2">
                        <i class="fab fa-whatsapp me-2 text-success"></i>
                        {{ $reminder->invoice->student->parent->user->phone ?? 'N/A' }}
                    </p>
                    <p class="mb-0">
                        <i class="fas fa-envelope me-2 text-muted"></i>
                        {{ $reminder->invoice->student->parent->user->email ?? 'N/A' }}
                    </p>
                </div>
            </div>
            @endif
            @endif

            <!-- Quick Actions -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-bolt me-2"></i> Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($reminder->invoice && !$reminder->invoice->isPaid())
                            <a href="{{ route('admin.payments.create', ['invoice_id' => $reminder->invoice->id]) }}" class="btn btn-success">
                                <i class="fas fa-money-bill me-1"></i> Record Payment
                            </a>
                            <form action="{{ route('admin.reminders.send-follow-up', $reminder->invoice) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-outline-primary w-100">
                                    <i class="fas fa-paper-plane me-1"></i> Send Follow-up
                                </button>
                            </form>
                        @endif
                        <a href="{{ route('admin.reminders.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Back to Reminders
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
