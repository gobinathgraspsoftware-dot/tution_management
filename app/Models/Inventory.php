<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;
    protected $table = 'inventory';
    protected $fillable = ['category_id', 'name', 'sku', 'description', 'cost_price', 'selling_price', 'current_stock', 'reorder_level', 'unit', 'image', 'status'];
    protected $casts = ['cost_price' => 'decimal:2', 'selling_price' => 'decimal:2', 'current_stock' => 'integer', 'reorder_level' => 'integer'];
    public function category() { return $this->belongsTo(InventoryCategory::class, 'category_id'); }
    public function logs() { return $this->hasMany(InventoryLog::class); }
    public function transactionItems() { return $this->hasMany(PosTransactionItem::class); }
    public function scopeActive($query) { return $query->where('status', 'active'); }
    public function scopeLowStock($query) { return $query->whereColumn('current_stock', '<=', 'reorder_level'); }
}
