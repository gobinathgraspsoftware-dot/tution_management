<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailQueue extends Model
{
    use HasFactory;
    protected $fillable = ['recipient_email', 'recipient_name', 'subject', 'body', 'template_id', 'priority', 'status', 'attempts', 'max_attempts', 'scheduled_at', 'sent_at', 'failed_at', 'error_message'];
    protected $casts = ['attempts' => 'integer', 'max_attempts' => 'integer', 'scheduled_at' => 'datetime', 'sent_at' => 'datetime', 'failed_at' => 'datetime'];
    public function template() { return $this->belongsTo(MessageTemplate::class, 'template_id'); }
    public function scopePending($query) { return $query->where('status', 'pending'); }
    public function scopeSent($query) { return $query->where('status', 'sent'); }
}
