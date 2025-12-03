<?php

namespace App\Services;

use App\Models\Announcement;
use App\Models\User;
use App\Models\Student;
use App\Models\Parents;
use App\Models\Teacher;
use App\Models\Staff;

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
    public function sendNotifications(Announcement $announcement)
    {
        $recipients = $this->getRecipients($announcement);

        foreach ($recipients as $user) {
            $this->notificationService->sendNotification(
                $user,
                'announcement',
                [
                    'title' => $announcement->title,
                    'message' => $announcement->content,
                    'priority' => $announcement->priority,
                    'announcement_id' => $announcement->id,
                    'url' => route('announcements.show', $announcement->id),
                ]
            );
        }

        return count($recipients);
    }

    /**
     * Get recipients based on target audience.
     */
    protected function getRecipients(Announcement $announcement)
    {
        $recipients = [];

        switch ($announcement->target_audience) {
            case 'all':
                $recipients = User::where('status', 'active')->get();
                break;

            case 'students':
                $recipients = User::whereHas('student', function($q) {
                    $q->whereHas('user', fn($q2) => $q2->where('status', 'active'));
                })->where('status', 'active')->get();
                break;

            case 'parents':
                $recipients = User::whereHas('parent', function($q) {
                    $q->whereHas('user', fn($q2) => $q2->where('status', 'active'));
                })->where('status', 'active')->get();
                break;

            case 'teachers':
                $recipients = User::whereHas('teacher', function($q) {
                    $q->whereHas('user', fn($q2) => $q2->where('status', 'active'));
                })->where('status', 'active')->get();
                break;

            case 'staff':
                $recipients = User::whereHas('staff', function($q) {
                    $q->whereHas('user', fn($q2) => $q2->where('status', 'active'));
                })->where('status', 'active')->get();
                break;

            case 'specific_class':
                if ($announcement->target_class_id) {
                    // Get students enrolled in the class
                    $studentIds = \App\Models\Enrollment::where('class_id', $announcement->target_class_id)
                        ->where('status', 'active')
                        ->pluck('student_id');

                    // Get students and their parents
                    $students = Student::whereIn('id', $studentIds)
                        ->where('approval_status', 'approved')
                        ->with('user', 'parent.user')
                        ->get();

                    foreach ($students as $student) {
                        if ($student->user && $student->user->status === 'active') {
                            $recipients[] = $student->user;
                        }
                        if ($student->parent && $student->parent->user && $student->parent->user->status === 'active') {
                            $recipients[] = $student->parent->user;
                        }
                    }

                    $recipients = collect($recipients)->unique('id');
                }
                break;
        }

        return collect($recipients)->unique('id');
    }

    /**
     * Get unread count for announcement.
     */
    public function getUnreadCount(Announcement $announcement)
    {
        $recipients = $this->getRecipients($announcement);
        $readCount = $announcement->getReadCount();

        return max(0, $recipients->count() - $readCount);
    }

    /**
     * Get active announcements for user.
     */
    public function getActiveAnnouncementsForUser(User $user, $limit = null)
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
    public function getUnreadAnnouncementsForUser(User $user)
    {
        return Announcement::published()
            ->forUser($user)
            ->whereDoesntHave('reads', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->orderBy('is_pinned', 'desc')
            ->orderBy('priority', 'desc')
            ->orderBy('publish_at', 'desc')
            ->get();
    }

    /**
     * Mark all announcements as read for user.
     */
    public function markAllAsReadForUser(User $user)
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
    public function getStatistics($period = 'all')
    {
        $query = Announcement::query();

        if ($period === 'today') {
            $query->whereDate('created_at', today());
        } elseif ($period === 'week') {
            $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
        } elseif ($period === 'month') {
            $query->whereMonth('created_at', now()->month);
        }

        return [
            'total' => $query->count(),
            'published' => (clone $query)->published()->count(),
            'draft' => (clone $query)->draft()->count(),
            'urgent' => (clone $query)->urgent()->count(),
            'pinned' => (clone $query)->pinned()->count(),
            'by_type' => [
                'general' => (clone $query)->where('type', 'general')->count(),
                'class' => (clone $query)->where('type', 'class')->count(),
                'urgent' => (clone $query)->where('type', 'urgent')->count(),
                'event' => (clone $query)->where('type', 'event')->count(),
            ],
        ];
    }
}
