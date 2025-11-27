<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosTransaction extends Model
{
    use HasFactory;
    protected $fillable = ['transaction_number', 'transaction_date', 'subtotal', 'discount', 'tax', 'total_amount', 'payment_method', 'amount_received', 'change_amount', 'reference_number', 'status', 'cashier_id', 'notes'];
    protected $casts = ['transaction_date' => 'datetime', 'subtotal' => 'decimal:2', 'discount' => 'decimal:2', 'tax' => 'decimal:2', 'total_amount' => 'decimal:2', 'amount_received' => 'decimal:2', 'change_amount' => 'decimal:2'];
    public function items() { return $this->hasMany(PosTransactionItem::class, 'transaction_id'); }
    public function cashier() { return $this->belongsTo(User::class, 'cashier_id'); }
    public function scopeCompleted($query) { return $query->where('status', 'completed'); }
    public function scopeToday($query) { return $query->whereDate('transaction_date', today()); }
}
