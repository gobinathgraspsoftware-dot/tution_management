<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'description', 'start_date', 'end_date', 'holiday_type', 'affects_billing', 'is_recurring', 'recurrence_pattern'];
    protected $casts = ['start_date' => 'date', 'end_date' => 'date', 'affects_billing' => 'boolean', 'is_recurring' => 'boolean', 'recurrence_pattern' => 'array'];
    public function scopeUpcoming($query) { return $query->where('start_date', '>=', today()); }
}
