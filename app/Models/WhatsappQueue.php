<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappQueue extends Model
{
    use HasFactory;
    protected $table = 'whatsapp_queue';
    protected $fillable = ['recipient_phone', 'recipient_name', 'message', 'template_id', 'media_url', 'priority', 'status', 'whatsapp_message_id', 'attempts', 'max_attempts', 'scheduled_at', 'sent_at', 'delivered_at', 'read_at', 'failed_at', 'error_message'];
    protected $casts = ['attempts' => 'integer', 'max_attempts' => 'integer', 'scheduled_at' => 'datetime', 'sent_at' => 'datetime', 'delivered_at' => 'datetime', 'read_at' => 'datetime', 'failed_at' => 'datetime'];
    public function template() { return $this->belongsTo(MessageTemplate::class, 'template_id'); }
    public function scopePending($query) { return $query->where('status', 'pending'); }
    public function scopeSent($query) { return $query->where('status', 'sent'); }
}
