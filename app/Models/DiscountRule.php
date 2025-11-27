<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DiscountRule extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code', 'name', 'description', 'discount_type', 'discount_value',
        'min_purchase_amount', 'max_discount_amount', 'applicable_to',
        'valid_from', 'valid_until', 'usage_limit', 'times_used',
        'is_active', 'auto_apply',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'min_purchase_amount' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'valid_from' => 'date',
        'valid_until' => 'date',
        'usage_limit' => 'integer',
        'times_used' => 'integer',
        'is_active' => 'boolean',
        'auto_apply' => 'boolean',
    ];

    public function discountUsage()
    {
        return $this->hasMany(DiscountUsage::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeValid($query)
    {
        return $query->where('is_active', true)
                     ->where(function($q) {
                         $q->whereNull('valid_from')
                           ->orWhere('valid_from', '<=', now());
                     })
                     ->where(function($q) {
                         $q->whereNull('valid_until')
                           ->orWhere('valid_until', '>=', now());
                     })
                     ->where(function($q) {
                         $q->whereNull('usage_limit')
                           ->orWhereColumn('times_used', '<', 'usage_limit');
                     });
    }
}
