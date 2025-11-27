<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryLog extends Model
{
    use HasFactory;
    public $timestamps = false;
    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;
    protected $fillable = ['inventory_id', 'type', 'quantity', 'previous_stock', 'new_stock', 'reference', 'notes', 'created_by'];
    protected $casts = ['quantity' => 'integer', 'previous_stock' => 'integer', 'new_stock' => 'integer', 'created_at' => 'datetime'];
    public function inventory() { return $this->belongsTo(Inventory::class); }
    public function createdBy() { return $this->belongsTo(User::class, 'created_by'); }
}
