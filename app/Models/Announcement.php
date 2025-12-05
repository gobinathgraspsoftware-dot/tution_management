<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'type',
        'target_audience',
        'target_class_id',
        'priority',
        'attachments',
        'publish_at',
        'expires_at',
        'is_pinned',
        'status',
        'created_by',
    ];

    protected $casts = [
        'attachments' => 'array',
        'publish_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_pinned' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Get the creator of the announcement.
     * NOTE: Using 'creator' to match controller usage
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Alias for creator relationship (backward compatibility)
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the target class for the announcement.
     */
    public function targetClass()
    {
        return $this->belongsTo(ClassModel::class, 'target_class_id');
    }

    /**
     * Get the read records for this announcement.
     */
    public function reads()
    {
        return $this->hasMany(AnnouncementRead::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Scope to get published announcements.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->where(function ($q) {
                $q->whereNull('publish_at')
                    ->orWhere('publish_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', now());
            });
    }

    /**
     * Scope to get draft announcements.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope to get archived announcements.
     */
    public function scopeArchived($query)
    {
        return $query->where('status', 'archived');
    }

    /**
     * Scope to get pinned announcements.
     */
    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }

    /**
     * Scope to get urgent announcements.
     */
    public function scopeUrgent($query)
    {
        return $query->where('priority', 'urgent');
    }

    /**
     * Scope to get announcements for a specific user based on their role.
     */
    public function scopeForUser($query, $user)
    {
        return $query->where(function ($q) use ($user) {
            // All users can see 'all' target audience
            $q->where('target_audience', 'all');

            // Check role-based targeting
            if ($user->hasRole('student')) {
                $q->orWhere('target_audience', 'students')
                    ->orWhere(function ($q2) use ($user) {
                        $q2->where('target_audience', 'specific_class')
                            ->whereHas('targetClass.enrollments', function ($q3) use ($user) {
                                $studentId = $user->student->id ?? 0;
                                $q3->where('student_id', $studentId)
                                    ->where('status', 'active');
                            });
                    });
            } elseif ($user->hasRole('parent')) {
                $q->orWhere('target_audience', 'parents')
                    ->orWhere(function ($q2) use ($user) {
                        // Parents can see announcements for classes their children are in
                        $q2->where('target_audience', 'specific_class')
                            ->whereHas('targetClass.enrollments', function ($q3) use ($user) {
                                $parent = $user->parent;
                                if ($parent) {
                                    $studentIds = $parent->students()->pluck('id');
                                    $q3->whereIn('student_id', $studentIds)
                                        ->where('status', 'active');
                                }
                            });
                    });
            } elseif ($user->hasRole('teacher')) {
                $q->orWhere('target_audience', 'teachers')
                    ->orWhere(function ($q2) use ($user) {
                        // Teachers can see announcements for classes they teach
                        $q2->where('target_audience', 'specific_class')
                            ->whereHas('targetClass', function ($q3) use ($user) {
                                $teacherId = $user->teacher->id ?? 0;
                                $q3->where('teacher_id', $teacherId);
                            });
                    });
            } elseif ($user->hasRole('staff')) {
                $q->orWhere('target_audience', 'staff');
            }

            // Admins and Super Admins can see all announcements
            if ($user->hasRole(['admin', 'super-admin'])) {
                $q->orWhereNotNull('id'); // See all
            }
        });
    }

    /**
     * Scope to filter by type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to filter by priority.
     */
    public function scopeOfPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope for active announcements (published and not expired).
     */
    public function scopeActive($query)
    {
        return $query->published();
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Check if announcement has been read by a user.
     */
    public function isReadBy($userId)
    {
        return $this->reads()->where('user_id', $userId)->exists();
    }

    /**
     * Mark announcement as read by a user.
     */
    public function markAsReadBy($userId)
    {
        return $this->reads()->firstOrCreate(
            ['user_id' => $userId],
            ['read_at' => now()]
        );
    }

    /**
     * Get the count of users who have read this announcement.
     */
    public function getReadCount()
    {
        return $this->reads()->count();
    }

    /**
     * Check if the announcement is currently active.
     */
    public function isActive()
    {
        if ($this->status !== 'published') {
            return false;
        }

        if ($this->publish_at && $this->publish_at->isFuture()) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Check if announcement is expired.
     */
    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if announcement is scheduled for future.
     */
    public function isScheduled()
    {
        return $this->status === 'published'
            && $this->publish_at
            && $this->publish_at->isFuture();
    }

    /**
     * Get priority badge class.
     */
    public function getPriorityBadgeClass()
    {
        return match ($this->priority) {
            'urgent' => 'bg-danger',
            'high' => 'bg-warning',
            'normal' => 'bg-info',
            'low' => 'bg-secondary',
            default => 'bg-secondary',
        };
    }

    /**
     * Get status badge class.
     */
    public function getStatusBadgeClass()
    {
        return match ($this->status) {
            'published' => 'bg-success',
            'draft' => 'bg-warning',
            'archived' => 'bg-secondary',
            default => 'bg-secondary',
        };
    }

    /**
     * Get type badge class.
     */
    public function getTypeBadgeClass()
    {
        return match ($this->type) {
            'urgent' => 'bg-danger',
            'event' => 'bg-primary',
            'class' => 'bg-info',
            'general' => 'bg-secondary',
            default => 'bg-secondary',
        };
    }

    /**
     * Get formatted target audience.
     */
    public function getTargetAudienceLabel()
    {
        return match ($this->target_audience) {
            'all' => 'Everyone',
            'students' => 'Students',
            'parents' => 'Parents',
            'teachers' => 'Teachers',
            'staff' => 'Staff',
            'specific_class' => $this->targetClass ? $this->targetClass->name : 'Specific Class',
            default => ucfirst($this->target_audience),
        };
    }
}
