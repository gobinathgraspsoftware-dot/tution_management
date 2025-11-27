<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosTransactionItem extends Model
{
    use HasFactory;
    public $timestamps = false;
    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;
    protected $fillable = ['transaction_id', 'inventory_id', 'quantity', 'unit_price', 'total_price'];
    protected $casts = ['quantity' => 'integer', 'unit_price' => 'decimal:2', 'total_price' => 'decimal:2', 'created_at' => 'datetime'];
    public function transaction() { return $this->belongsTo(PosTransaction::class); }
    public function inventory() { return $this->belongsTo(Inventory::class); }
}
