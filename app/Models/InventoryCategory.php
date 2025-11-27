<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryCategory extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'description', 'status'];
    public function inventoryItems() { return $this->hasMany(Inventory::class, 'category_id'); }
    public function scopeActive($query) { return $query->where('status', 'active'); }
}
