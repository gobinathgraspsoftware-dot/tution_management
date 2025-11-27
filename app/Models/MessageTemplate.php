<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageTemplate extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'category', 'channel', 'subject', 'message_body', 'variables', 'is_active'];
    protected $casts = ['variables' => 'array', 'is_active' => 'boolean'];
    public function whatsappQueue() { return $this->hasMany(WhatsappQueue::class, 'template_id'); }
    public function emailQueue() { return $this->hasMany(EmailQueue::class, 'template_id'); }
    public function scopeActive($query) { return $query->where('is_active', true); }
    public function scopeByCategory($query, $category) { return $query->where('category', $category); }
}
