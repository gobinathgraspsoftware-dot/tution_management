<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory;
    protected $fillable = ['title', 'content', 'type', 'target_audience', 'target_class_id', 'priority', 'attachments', 'publish_at', 'expires_at', 'is_pinned', 'status', 'created_by'];
    protected $casts = ['attachments' => 'array', 'publish_at' => 'datetime', 'expires_at' => 'datetime', 'is_pinned' => 'boolean'];
    public function targetClass() { return $this->belongsTo(ClassModel::class, 'target_class_id'); }
    public function createdBy() { return $this->belongsTo(User::class, 'created_by'); }
    public function reads() { return $this->hasMany(AnnouncementRead::class); }
    public function scopePublished($query) { return $query->where('status', 'published'); }
    public function scopePinned($query) { return $query->where('is_pinned', true); }
}
