<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiscountUsage extends Model
{
    use HasFactory;

    protected $table = 'discount_usage';
    public $timestamps = false;

    protected $fillable = [
        'discount_rule_id', 'invoice_id', 'student_id', 'discount_amount', 'applied_at',
    ];

    protected $casts = [
        'discount_amount' => 'decimal:2',
        'applied_at' => 'datetime',
    ];

    public function discountRule()
    {
        return $this->belongsTo(DiscountRule::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
