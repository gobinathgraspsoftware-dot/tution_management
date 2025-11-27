<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationLog extends Model
{
    use HasFactory;
    public $timestamps = false;
    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;
    protected $fillable = ['user_id', 'channel', 'recipient', 'type', 'subject', 'message', 'template_id', 'status', 'sent_at', 'delivered_at', 'response', 'error_message'];
    protected $casts = ['sent_at' => 'datetime', 'delivered_at' => 'datetime', 'created_at' => 'datetime'];
    public function user() { return $this->belongsTo(User::class); }
    public function scopeSent($query) { return $query->where('status', 'sent'); }
    public function scopeFailed($query) { return $query->where('status', 'failed'); }
}
