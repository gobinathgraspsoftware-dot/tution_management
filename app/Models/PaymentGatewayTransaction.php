<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentGatewayTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'gateway_config_id', 'payment_id', 'invoice_id', 'transaction_id',
        'amount', 'currency', 'status', 'gateway_status', 'gateway_response',
        'customer_email', 'customer_phone', 'ip_address', 'callback_url',
        'return_url', 'webhook_received_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'gateway_response' => 'array',
        'webhook_received_at' => 'datetime',
    ];

    public function gatewayConfig()
    {
        return $this->belongsTo(PaymentGatewayConfig::class, 'gateway_config_id');
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
