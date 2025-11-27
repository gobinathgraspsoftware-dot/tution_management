<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseCategory extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'description', 'status'];
    public function expenses() { return $this->hasMany(Expense::class, 'category_id'); }
    public function scopeActive($query) { return $query->where('status', 'active'); }
}
