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
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELLED = 'cancelled';

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
            self::STATUS_FAILED => 'Failed',
            self::STATUS_CANCELLED => 'Cancelled',
        ];
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

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ==========================================
    // SCOPES
    // ==========================================

    public function scopeScheduled($query)
    {
        return $query->where('status', self::STATUS_SCHEDULED);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeSent($query)
    {
        return $query->where('status', self::STATUS_SENT);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    public function scopeDueToSend($query)
    {
        return $query->whereIn('status', [self::STATUS_SCHEDULED, self::STATUS_PENDING])
                     ->where('scheduled_date', '<=', now()->toDateString());
    }

    public function scopeNeedsRetry($query)
    {
        return $query->where('status', self::STATUS_FAILED)
                     ->whereColumn('attempts', '<', 'max_attempts')
                     ->where(function($q) {
                         $q->whereNull('next_retry_at')
                           ->orWhere('next_retry_at', '<=', now());
                     });
    }

    public function scopeForToday($query)
    {
        return $query->whereDate('scheduled_date', today());
    }

    public function scopeForThisMonth($query)
    {
        return $query->whereMonth('scheduled_date', now()->month)
                     ->whereYear('scheduled_date', now()->year);
    }

    public function scopeForChannel($query, $channel)
    {
        return $query->where('channel', $channel);
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('reminder_type', $type);
    }

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
     * Get status badge class
     */
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            self::STATUS_SENT => 'success',
            self::STATUS_SCHEDULED => 'info',
            self::STATUS_PENDING => 'warning',
            self::STATUS_FAILED => 'danger',
            self::STATUS_CANCELLED => 'secondary',
            default => 'secondary',
        };
    }

    /**
     * Get channel icon
     */
    public function getChannelIconAttribute(): string
    {
        return match($this->channel) {
            self::CHANNEL_WHATSAPP => 'fab fa-whatsapp',
            self::CHANNEL_EMAIL => 'fas fa-envelope',
            self::CHANNEL_SMS => 'fas fa-sms',
            default => 'fas fa-bell',
        };
    }

    /**
     * Check if can retry
     */
    public function getCanRetryAttribute(): bool
    {
        return $this->status === self::STATUS_FAILED &&
               $this->attempts < $this->max_attempts;
    }

    // ==========================================
    // HELPER METHODS
    // ==========================================

    /**
     * Mark as sent
     */
    public function markAsSent(?string $response = null): void
    {
        $this->update([
            'status' => self::STATUS_SENT,
            'sent_at' => now(),
            'response' => $response,
            'error_message' => null,
        ]);
    }

    /**
     * Mark as failed
     */
    public function markAsFailed(string $error): void
    {
        $this->increment('attempts');

        $updates = [
            'error_message' => $error,
        ];

        if ($this->attempts >= $this->max_attempts) {
            $updates['status'] = self::STATUS_FAILED;
        } else {
            // Schedule retry
            $retryDelay = config('payment_reminders.retry_delay_hours', 2);
            $updates['next_retry_at'] = now()->addHours($retryDelay);
            $updates['status'] = self::STATUS_PENDING;
        }

        $this->update($updates);
    }

    /**
     * Cancel reminder
     */
    public function cancel(): void
    {
        $this->update(['status' => self::STATUS_CANCELLED]);
    }

    /**
     * Reset for retry
     */
    public function resetForRetry(): void
    {
        $this->update([
            'status' => self::STATUS_PENDING,
            'error_message' => null,
            'next_retry_at' => null,
        ]);
    }

    /**
     * Get reminder statistics
     */
    public static function getStatistics(?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $query = static::query();

        if ($startDate && $endDate) {
            $query->whereBetween('scheduled_date', [$startDate, $endDate]);
        }

        return [
            'total' => (clone $query)->count(),
            'sent' => (clone $query)->sent()->count(),
            'failed' => (clone $query)->failed()->count(),
            'scheduled' => (clone $query)->scheduled()->count(),
            'pending' => (clone $query)->pending()->count(),
            'cancelled' => (clone $query)->cancelled()->count(),
            'by_channel' => [
                'whatsapp' => (clone $query)->forChannel(self::CHANNEL_WHATSAPP)->sent()->count(),
                'email' => (clone $query)->forChannel(self::CHANNEL_EMAIL)->sent()->count(),
                'sms' => (clone $query)->forChannel(self::CHANNEL_SMS)->sent()->count(),
            ],
            'by_type' => [
                'first' => (clone $query)->ofType(self::TYPE_FIRST)->count(),
                'second' => (clone $query)->ofType(self::TYPE_SECOND)->count(),
                'final' => (clone $query)->ofType(self::TYPE_FINAL)->count(),
                'overdue' => (clone $query)->ofType(self::TYPE_OVERDUE)->count(),
            ],
            'success_rate' => static::calculateSuccessRate($query),
        ];
    }

    /**
     * Calculate success rate
     */
    protected static function calculateSuccessRate($query): float
    {
        $total = (clone $query)->whereIn('status', [self::STATUS_SENT, self::STATUS_FAILED])->count();
        $sent = (clone $query)->sent()->count();

        if ($total === 0) {
            return 0;
        }

        return round(($sent / $total) * 100, 1);
    }

    /**
     * Get reminder days configuration
     */
    public static function getReminderDays(): array
    {
        return config('payment_reminders.reminder_days', [10, 18, 24]);
    }
}
