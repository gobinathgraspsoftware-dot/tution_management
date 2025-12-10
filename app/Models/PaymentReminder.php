<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PaymentReminder extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'installment_id',
        'student_id',
        'reminder_type',
        'reminder_day',
        'scheduled_date',
        'sent_at',
        'channel',
        'status',
        'response',
        'recipient_phone',
        'recipient_email',
        'message_content',
        'attempts',
        'max_attempts',
        'next_retry_at',
        'error_message',
        'created_by',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'sent_at' => 'datetime',
        'next_retry_at' => 'datetime',
        'attempts' => 'integer',
        'max_attempts' => 'integer',
        'reminder_day' => 'integer',
        'created_by' => 'integer',
    ];

    /**
     * Reminder types
     */
    const TYPE_FIRST = 'first';
    const TYPE_SECOND = 'second';
    const TYPE_FINAL = 'final';
    const TYPE_OVERDUE = 'overdue';
    const TYPE_FOLLOW_UP = 'follow_up';
    const TYPE_INSTALLMENT = 'installment';

    /**
     * Reminder channels
     */
    const CHANNEL_WHATSAPP = 'whatsapp';
    const CHANNEL_EMAIL = 'email';
    const CHANNEL_SMS = 'sms';

    /**
     * Reminder statuses
     */
    const STATUS_SCHEDULED = 'scheduled';
    const STATUS_PENDING = 'pending';
    const STATUS_SENT = 'sent';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Default reminder days (10th, 18th, 24th of each month)
     */
    const DEFAULT_REMINDER_DAYS = [10, 18, 24];

    /**
     * Get all reminder types
     */
    public static function getTypes(): array
    {
        return [
            self::TYPE_FIRST => '1st Reminder (10th)',
            self::TYPE_SECOND => '2nd Reminder (18th)',
            self::TYPE_FINAL => 'Final Reminder (24th)',
            self::TYPE_OVERDUE => 'Overdue Reminder',
            self::TYPE_FOLLOW_UP => 'Follow-up Reminder',
            self::TYPE_INSTALLMENT => 'Installment Reminder',
        ];
    }

    /**
     * Get all channels
     */
    public static function getChannels(): array
    {
        return [
            self::CHANNEL_WHATSAPP => 'WhatsApp',
            self::CHANNEL_EMAIL => 'Email',
            self::CHANNEL_SMS => 'SMS',
        ];
    }

    /**
     * Get all statuses
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_SCHEDULED => 'Scheduled',
            self::STATUS_PENDING => 'Pending',
            self::STATUS_SENT => 'Sent',
            self::STATUS_DELIVERED => 'Delivered',
            self::STATUS_FAILED => 'Failed',
            self::STATUS_CANCELLED => 'Cancelled',
        ];
    }

    /**
     * Get reminder days from config or default
     */
    public static function getReminderDays(): array
    {
        return config('payment_reminders.reminder_days', self::DEFAULT_REMINDER_DAYS);
    }

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function installment()
    {
        return $this->belongsTo(Installment::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Alias for createdBy
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ==========================================
    // SCOPES
    // ==========================================

    /**
     * Scope: scheduled reminders
     */
    public function scopeScheduled($query)
    {
        return $query->where('status', self::STATUS_SCHEDULED);
    }

    /**
     * Scope: pending reminders
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope: sent reminders
     */
    public function scopeSent($query)
    {
        return $query->whereIn('status', [self::STATUS_SENT, self::STATUS_DELIVERED]);
    }

    /**
     * Scope: failed reminders
     */
    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    /**
     * Scope: cancelled reminders
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    /**
     * Scope: by type (REQUIRED BY SERVICE)
     */
    public function scopeByType($query, $type)
    {
        return $query->where('reminder_type', $type);
    }

    /**
     * Scope: by channel (REQUIRED BY SERVICE)
     */
    public function scopeByChannel($query, $channel)
    {
        return $query->where('channel', $channel);
    }

    /**
     * Scope: alias for byType
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('reminder_type', $type);
    }

    /**
     * Scope: alias for byChannel
     */
    public function scopeForChannel($query, $channel)
    {
        return $query->where('channel', $channel);
    }

    /**
     * Scope: reminders due to send
     */
    public function scopeDueToSend($query)
    {
        return $query->whereIn('status', [self::STATUS_SCHEDULED, self::STATUS_PENDING])
                     ->where('scheduled_date', '<=', now()->toDateString());
    }

    /**
     * Scope: reminders that need retry
     */
    public function scopeNeedsRetry($query)
    {
        return $query->where('status', self::STATUS_FAILED)
                     ->whereColumn('attempts', '<', 'max_attempts')
                     ->where(function($q) {
                         $q->whereNull('next_retry_at')
                           ->orWhere('next_retry_at', '<=', now());
                     });
    }

    /**
     * Scope: reminders for today (REQUIRED BY CONTROLLER)
     */
    public function scopeForToday($query)
    {
        return $query->whereDate('scheduled_date', today());
    }

    /**
     * Scope: reminders for this month
     */
    public function scopeForThisMonth($query)
    {
        return $query->whereMonth('scheduled_date', now()->month)
                     ->whereYear('scheduled_date', now()->year);
    }

    /**
     * Scope: upcoming reminders
     */
    public function scopeUpcoming($query, $days = 7)
    {
        return $query->where('status', self::STATUS_SCHEDULED)
                     ->whereBetween('scheduled_date', [now(), now()->addDays($days)]);
    }

    // ==========================================
    // ACCESSORS
    // ==========================================

    /**
     * Get type label
     */
    public function getTypeLabelAttribute(): string
    {
        return self::getTypes()[$this->reminder_type] ?? ucfirst($this->reminder_type);
    }

    /**
     * Get channel label
     */
    public function getChannelLabelAttribute(): string
    {
        return self::getChannels()[$this->channel] ?? ucfirst($this->channel);
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return self::getStatuses()[$this->status] ?? ucfirst($this->status);
    }

    /**
     * Check if reminder can be cancelled
     */
    public function getCanCancelAttribute(): bool
    {
        return in_array($this->status, [self::STATUS_SCHEDULED, self::STATUS_PENDING]);
    }

    /**
     * Check if reminder can be resent
     */
    public function getCanResendAttribute(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * Check if reminder is overdue
     */
    public function getIsOverdueAttribute(): bool
    {
        return $this->scheduled_date &&
               $this->scheduled_date->isPast() &&
               in_array($this->status, [self::STATUS_SCHEDULED, self::STATUS_PENDING]);
    }

    // ==========================================
    // METHODS
    // ==========================================

    /**
     * Cancel the reminder (REQUIRED BY CONTROLLER)
     */
    public function cancel(): bool
    {
        if (!$this->can_cancel) {
            return false;
        }

        $this->status = self::STATUS_CANCELLED;
        return $this->save();
    }

    /**
     * Reset for retry (REQUIRED BY CONTROLLER)
     */
    public function resetForRetry(): bool
    {
        $this->status = self::STATUS_PENDING;
        $this->error_message = null;
        $this->next_retry_at = null;
        return $this->save();
    }

    /**
     * Mark as sent
     */
    public function markAsSent(?string $response = null): bool
    {
        $this->status = self::STATUS_SENT;
        $this->sent_at = now();
        $this->response = $response;
        $this->attempts = ($this->attempts ?? 0) + 1;
        return $this->save();
    }

    /**
     * Mark as delivered
     */
    public function markAsDelivered(?string $response = null): bool
    {
        $this->status = self::STATUS_DELIVERED;
        $this->sent_at = $this->sent_at ?? now();
        $this->response = $response;
        return $this->save();
    }

    /**
     * Mark as failed
     */
    public function markAsFailed(string $errorMessage, ?int $retryDelayHours = null): bool
    {
        $this->status = self::STATUS_FAILED;
        $this->error_message = $errorMessage;
        $this->attempts = ($this->attempts ?? 0) + 1;

        // Set next retry time if not exceeded max attempts
        $maxAttempts = $this->max_attempts ?? config('payment_reminders.max_retry_attempts', 3);
        if ($this->attempts < $maxAttempts && $retryDelayHours) {
            $this->next_retry_at = now()->addHours($retryDelayHours);
        }

        return $this->save();
    }

    /**
     * Get recipient (phone or email based on channel)
     */
    public function getRecipient(): ?string
    {
        if ($this->channel === self::CHANNEL_EMAIL) {
            return $this->recipient_email;
        }
        return $this->recipient_phone;
    }

    /**
     * Set recipient based on channel
     */
    public function setRecipient(string $value): self
    {
        if ($this->channel === self::CHANNEL_EMAIL) {
            $this->recipient_email = $value;
        } else {
            $this->recipient_phone = $value;
        }
        return $this;
    }

    /**
     * Check if invoice is paid
     */
    public function isInvoicePaid(): bool
    {
        return $this->invoice && $this->invoice->isPaid();
    }
}
