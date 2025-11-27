<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;
    public $timestamps = false;
    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;
    protected $fillable = ['user_id', 'action', 'model_type', 'model_id', 'description', 'old_values', 'new_values', 'ip_address', 'user_agent'];
    protected $casts = ['old_values' => 'array', 'new_values' => 'array', 'created_at' => 'datetime'];
    public function user() { return $this->belongsTo(User::class); }
    public function subject() { return $this->morphTo('model'); }
}
