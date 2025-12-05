<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnnouncementRead extends Model
{
    use HasFactory;

    /**
     * Disable default timestamps.
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'announcement_id',
        'user_id',
        'read_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'read_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-set read_at when creating
        static::creating(function ($model) {
            if (!$model->read_at) {
                $model->read_at = now();
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Get the announcement that was read.
     */
    public function announcement()
    {
        return $this->belongsTo(Announcement::class);
    }

    /**
     * Get the user who read the announcement.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Scope to filter by user.
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to filter by announcement.
     */
    public function scopeByAnnouncement($query, $announcementId)
    {
        return $query->where('announcement_id', $announcementId);
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeReadBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('read_at', [$startDate, $endDate]);
    }

    /**
     * Scope to get reads from today.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('read_at', today());
    }
}
