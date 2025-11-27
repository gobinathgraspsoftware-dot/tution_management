<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentReminder extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'invoice_id', 'reminder_type', 'scheduled_date', 'sent_at',
        'channel', 'status', 'response',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'sent_at' => 'datetime',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }
}
