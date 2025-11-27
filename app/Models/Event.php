<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['title', 'description', 'event_type', 'start_datetime', 'end_datetime', 'location', 'meeting_link', 'is_recurring', 'recurrence_pattern', 'related_model_type', 'related_model_id', 'created_by', 'status'];
    protected $casts = ['start_datetime' => 'datetime', 'end_datetime' => 'datetime', 'is_recurring' => 'boolean', 'recurrence_pattern' => 'array'];
    public function createdBy() { return $this->belongsTo(User::class, 'created_by'); }
    public function related() { return $this->morphTo(); }
    public function scopeUpcoming($query) { return $query->where('start_datetime', '>=', now()); }
    public function scopeScheduled($query) { return $query->where('status', 'scheduled'); }
}
