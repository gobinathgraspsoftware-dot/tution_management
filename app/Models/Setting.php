<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;
    protected $fillable = ['group', 'key', 'value', 'type', 'description'];
    public function scopeByGroup($query, $group) { return $query->where('group', $group); }
    public function getValueAttribute($value) {
        return match($this->type) {
            'integer' => (int) $value,
            'boolean' => (bool) $value,
            'json' => json_decode($value, true),
            default => $value,
        };
    }
}
