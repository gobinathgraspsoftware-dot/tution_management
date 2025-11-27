<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentGatewayConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'gateway_name', 'is_active', 'is_sandbox', 'api_key', 'api_secret',
        'merchant_id', 'webhook_secret', 'configuration', 'supported_currencies',
        'transaction_fee_percentage', 'transaction_fee_fixed',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_sandbox' => 'boolean',
        'configuration' => 'array',
        'supported_currencies' => 'array',
        'transaction_fee_percentage' => 'decimal:2',
        'transaction_fee_fixed' => 'decimal:2',
    ];

    protected $hidden = [
        'api_key', 'api_secret', 'webhook_secret',
    ];

    public function transactions()
    {
        return $this->hasMany(PaymentGatewayTransaction::class, 'gateway_config_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
