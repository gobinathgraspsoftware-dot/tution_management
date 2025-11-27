<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Seminar extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['name', 'code', 'description', 'type', 'date', 'start_time', 'end_time', 'venue', 'is_online', 'meeting_link', 'capacity', 'current_participants', 'regular_fee', 'early_bird_fee', 'early_bird_deadline', 'registration_deadline', 'facilitator', 'image', 'status'];
    protected $casts = ['date' => 'date', 'start_time' => 'datetime:H:i:s', 'end_time' => 'datetime:H:i:s', 'is_online' => 'boolean', 'capacity' => 'integer', 'current_participants' => 'integer', 'regular_fee' => 'decimal:2', 'early_bird_fee' => 'decimal:2', 'early_bird_deadline' => 'date', 'registration_deadline' => 'date'];
    public function participants() { return $this->hasMany(SeminarParticipant::class); }
    public function expenses() { return $this->hasMany(SeminarExpense::class); }
    public function scopeOpen($query) { return $query->where('status', 'open'); }
    public function scopeUpcoming($query) { return $query->where('date', '>=', today()); }
}
