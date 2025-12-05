<?php

namespace App\Services;

use App\Models\Announcement;
use App\Models\User;
use App\Models\Student;
use App\Models\Parents;
use App\Models\Teacher;
use App\Models\Staff;
use App\Models\Enrollment;
use Illuminate\Support\Collection;

class AnnouncementService
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Send notifications for announcement.
     */
    public function sendNotifications(Announcement $announcement): int
    {
        $recipients = $this->getRecipients($announcement);

        foreach ($recipients as $user) {
            $this->notificationService->sendNotification(
                $user,
                'announcement',
                [
                    'title' => $announcement->title,
                    'message' => strip_tags(substr($announcement->content, 0, 200)),
                    'priority' => $announcement->priority,
                    'announcement_id' => $announcement->id,
                    'url' => route('announcements.show', $announcement->id),
                ]
            );
        }

        return $recipients->count();
    }

    /**
     * Get recipients based on target audience.
     */
    public function getRecipients(Announcement $announcement): Collection
    {
        $recipients = collect();

        switch ($announcement->target_audience) {
            case 'all':
                $recipients = User::where('status', 'active')->get();
                break;

            case 'students':
                $recipients = User::where('status', 'active')
                    ->whereHas('student', function ($q) {
                        $q->where('approval_status', 'approved');
                    })
                    ->get();
                break;

            case 'parents':
                $recipients = User::where('status', 'active')
                    ->whereHas('parent')
                    ->get();
                break;

            case 'teachers':
                $recipients = User::where('status', 'active')
                    ->whereHas('teacher', function ($q) {
                        $q->where('status', 'active');
                    })
                    ->get();
                break;

            case 'staff':
                $recipients = User::where('status', 'active')
                    ->whereHas('staff', function ($q) {
                        $q->where('status', 'active');
                    })
                    ->get();
                break;

            case 'specific_class':
                if ($announcement->target_class_id) {
                    $recipients = $this->getClassRecipients($announcement->target_class_id);
                }
                break;
        }

        return $recipients->unique('id');
    }

    /**
     * Get recipients for a specific class (students and their parents).
     */
    protected function getClassRecipients(int $classId): Collection
    {
        $recipients = collect();

        // Get active enrollments for the class
        $enrollments = Enrollment::where('class_id', $classId)
            ->where('status', 'active')
            ->with(['student.user', 'student.parent.user'])
            ->get();

        foreach ($enrollments as $enrollment) {
            $student = $enrollment->student;

            // Add student user
            if ($student && $student->user && $student->user->status === 'active') {
                $recipients->push($student->user);
            }

            // Add parent user
            if ($student && $student->parent && $student->parent->user && $student->parent->user->status === 'active') {
                $recipients->push($student->parent->user);
            }
        }

        // Also add the class teacher
        $class = \App\Models\ClassModel::find($classId);
        if ($class && $class->teacher && $class->teacher->user && $class->teacher->user->status === 'active') {
            $recipients->push($class->teacher->user);
        }

        return $recipients;
    }

    /**
     * Get unread count for announcement.
     */
    public function getUnreadCount(Announcement $announcement): int
    {
        $recipients = $this->getRecipients($announcement);
        $readCount = $announcement->getReadCount();

        return max(0, $recipients->count() - $readCount);
    }

    /**
     * Get active announcements for user.
     */
    public function getActiveAnnouncementsForUser(User $user, ?int $limit = null): Collection
    {
        $query = Announcement::published()
            ->forUser($user)
            ->orderBy('is_pinned', 'desc')
            ->orderBy('priority', 'desc')
            ->orderBy('publish_at', 'desc');

        if ($limit) {
            return $query->take($limit)->get();
        }

        return $query->get();
    }

    /**
     * Get unread announcements for user.
     */
    public function getUnreadAnnouncementsForUser(User $user): Collection
    {
        return Announcement::published()
            ->forUser($user)
            ->whereDoesntHave('reads', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->orderBy('is_pinned', 'desc')
            ->orderBy('priority', 'desc')
            ->orderBy('publish_at', 'desc')
            ->get();
    }

    /**
     * Get unread count for user.
     */
    public function getUnreadCountForUser(User $user): int
    {
        return Announcement::published()
            ->forUser($user)
            ->whereDoesntHave('reads', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->count();
    }

    /**
     * Mark all announcements as read for user.
     */
    public function markAllAsReadForUser(User $user): int
    {
        $announcements = $this->getUnreadAnnouncementsForUser($user);

        foreach ($announcements as $announcement) {
            $announcement->markAsReadBy($user->id);
        }

        return $announcements->count();
    }

    /**
     * Get announcement statistics.
     */
    public function getStatistics(string $period = 'all'): array
    {
        $query = Announcement::query();

        if ($period === 'today') {
            $query->whereDate('created_at', today());
        } elseif ($period === 'week') {
            $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
        } elseif ($period === 'month') {
            $query->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year);
        }

        return [
            'total' => (clone $query)->count(),
            'published' => (clone $query)->published()->count(),
            'draft' => (clone $query)->draft()->count(),
            'archived' => (clone $query)->archived()->count(),
            'urgent' => (clone $query)->urgent()->count(),
            'pinned' => (clone $query)->pinned()->count(),
            'by_type' => [
                'general' => (clone $query)->where('type', 'general')->count(),
                'class' => (clone $query)->where('type', 'class')->count(),
                'urgent' => (clone $query)->where('type', 'urgent')->count(),
                'event' => (clone $query)->where('type', 'event')->count(),
            ],
            'by_audience' => [
                'all' => (clone $query)->where('target_audience', 'all')->count(),
                'students' => (clone $query)->where('target_audience', 'students')->count(),
                'parents' => (clone $query)->where('target_audience', 'parents')->count(),
                'teachers' => (clone $query)->where('target_audience', 'teachers')->count(),
                'staff' => (clone $query)->where('target_audience', 'staff')->count(),
                'specific_class' => (clone $query)->where('target_audience', 'specific_class')->count(),
            ],
        ];
    }

    /**
     * Get recent announcements.
     */
    public function getRecentAnnouncements(int $limit = 5): Collection
    {
        return Announcement::with(['creator', 'targetClass'])
            ->published()
            ->orderBy('publish_at', 'desc')
            ->take($limit)
            ->get();
    }

    /**
     * Get pinned announcements.
     */
    public function getPinnedAnnouncements(): Collection
    {
        return Announcement::with(['creator', 'targetClass'])
            ->published()
            ->pinned()
            ->orderBy('priority', 'desc')
            ->orderBy('publish_at', 'desc')
            ->get();
    }

    /**
     * Get urgent announcements.
     */
    public function getUrgentAnnouncements(): Collection
    {
        return Announcement::with(['creator', 'targetClass'])
            ->published()
            ->urgent()
            ->orderBy('publish_at', 'desc')
            ->get();
    }

    /**
     * Schedule announcement for publishing.
     */
    public function scheduleAnnouncement(Announcement $announcement, \DateTime $publishAt): bool
    {
        return $announcement->update([
            'status' => 'published',
            'publish_at' => $publishAt,
        ]);
    }

    /**
     * Process scheduled announcements (for scheduled task).
     */
    public function processScheduledAnnouncements(): int
    {
        $announcements = Announcement::where('status', 'published')
            ->whereNotNull('publish_at')
            ->where('publish_at', '<=', now())
            ->whereDoesntHave('reads') // Not yet notified
            ->get();

        $count = 0;
        foreach ($announcements as $announcement) {
            $this->sendNotifications($announcement);
            $count++;
        }

        return $count;
    }

    /**
     * Archive expired announcements.
     */
    public function archiveExpiredAnnouncements(): int
    {
        return Announcement::where('status', 'published')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->update(['status' => 'archived']);
    }

    /**
     * Get announcements by class.
     */
    public function getAnnouncementsByClass(int $classId, ?int $limit = null): Collection
    {
        $query = Announcement::with(['creator'])
            ->published()
            ->where(function ($q) use ($classId) {
                $q->where('target_audience', 'all')
                    ->orWhere(function ($q2) use ($classId) {
                        $q2->where('target_audience', 'specific_class')
                            ->where('target_class_id', $classId);
                    });
            })
            ->orderBy('is_pinned', 'desc')
            ->orderBy('publish_at', 'desc');

        if ($limit) {
            return $query->take($limit)->get();
        }

        return $query->get();
    }
}
