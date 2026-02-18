<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\AnnouncementRead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnnouncementController extends Controller
{
    /**
     * Display announcements for students.
     */
    public function index(Request $request)
    {
        $student = auth()->user()->student;
        $user = auth()->user();

        // Get student's enrolled class IDs
        $enrolledClassIds = $student->enrollments()->pluck('class_id')->toArray();

        // Build query for announcements visible to student
        $query = Announcement::where('status', 'published')
            ->where(function($q) use ($enrolledClassIds, $user) {
                $q->where('target_audience', 'all')
                  ->orWhere(function($q2) use ($enrolledClassIds) {
                      $q2->where('target_audience', 'students')
                         ->where(function($q3) use ($enrolledClassIds) {
                             $q3->whereNull('target_class_id')
                                ->orWhereIn('target_class_id', $enrolledClassIds);
                         });
                  });
            })
            ->with(['creator', 'targetClass']);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        // Priority filter
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // Type filter
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Status filter (read/unread)
        if ($request->filled('read_status')) {
            $readAnnouncementIds = AnnouncementRead::where('user_id', $user->id)
                ->pluck('announcement_id')
                ->toArray();

            if ($request->read_status === 'read') {
                $query->whereIn('id', $readAnnouncementIds);
            } else {
                $query->whereNotIn('id', $readAnnouncementIds);
            }
        }

        // Get announcements with pagination
        $announcements = $query->latest('publish_at')
            ->latest('created_at')
            ->paginate(15)
            ->withQueryString();

        // Get read status for each announcement
        $readAnnouncementIds = AnnouncementRead::where('user_id', $user->id)
            ->pluck('announcement_id')
            ->toArray();

        // Statistics
        $totalCount = Announcement::where('status', 'published')
            ->where(function($q) use ($enrolledClassIds) {
                $q->where('target_audience', 'all')
                  ->orWhere(function($q2) use ($enrolledClassIds) {
                      $q2->where('target_audience', 'students')
                         ->where(function($q3) use ($enrolledClassIds) {
                             $q3->whereNull('target_class_id')
                                ->orWhereIn('target_class_id', $enrolledClassIds);
                         });
                  });
            })
            ->count();

        $unreadCount = Announcement::where('status', 'published')
            ->where(function($q) use ($enrolledClassIds) {
                $q->where('target_audience', 'all')
                  ->orWhere(function($q2) use ($enrolledClassIds) {
                      $q2->where('target_audience', 'students')
                         ->where(function($q3) use ($enrolledClassIds) {
                             $q3->whereNull('target_class_id')
                                ->orWhereIn('target_class_id', $enrolledClassIds);
                         });
                  });
            })
            ->whereNotIn('id', $readAnnouncementIds)
            ->count();

        $stats = [
            'total' => $totalCount,
            'unread' => $unreadCount,
            'read' => $totalCount - $unreadCount,
            'urgent' => Announcement::where('status', 'published')
                ->where('priority', 'urgent')
                ->where(function($q) use ($enrolledClassIds) {
                    $q->where('target_audience', 'all')
                      ->orWhere(function($q2) use ($enrolledClassIds) {
                          $q2->where('target_audience', 'students')
                             ->where(function($q3) use ($enrolledClassIds) {
                                 $q3->whereNull('target_class_id')
                                    ->orWhereIn('target_class_id', $enrolledClassIds);
                             });
                      });
                })
                ->count(),
        ];

        return view('student.announcements.index', compact('announcements', 'stats', 'readAnnouncementIds'));
    }

    /**
     * Display announcement details.
     */
    public function show(Announcement $announcement)
    {
        $student = auth()->user()->student;
        $user = auth()->user();

        // Get student's enrolled class IDs
        $enrolledClassIds = $student->enrollments()->pluck('class_id')->toArray();

        // Check if student has access to this announcement
        $hasAccess = $announcement->status === 'published' && (
            $announcement->target_audience === 'all' ||
            (
                $announcement->target_audience === 'students' &&
                (
                    is_null($announcement->target_class_id) ||
                    in_array($announcement->target_class_id, $enrolledClassIds)
                )
            )
        );

        if (!$hasAccess) {
            abort(403, 'You do not have access to this announcement.');
        }

        // Load relationships
        $announcement->load(['creator', 'targetClass']);

        // Mark as read
        AnnouncementRead::firstOrCreate([
            'announcement_id' => $announcement->id,
            'user_id' => $user->id,
        ], [
            'read_at' => now(),
        ]);

        return view('student.announcements.show', compact('announcement'));
    }

    /**
     * Mark announcement as read via AJAX.
     */
    public function markAsRead(Announcement $announcement)
    {
        try {
            $user = auth()->user();

            AnnouncementRead::firstOrCreate([
                'announcement_id' => $announcement->id,
                'user_id' => $user->id,
            ], [
                'read_at' => now(),
            ]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Mark all announcements as read.
     */
    public function markAllAsRead()
    {
        try {
            $student = auth()->user()->student;
            $user = auth()->user();

            // Get student's enrolled class IDs
            $enrolledClassIds = $student->enrollments()->pluck('class_id')->toArray();

            // Get all announcement IDs visible to student
            $announcementIds = Announcement::where('status', 'published')
                ->where(function($q) use ($enrolledClassIds) {
                    $q->where('target_audience', 'all')
                      ->orWhere(function($q2) use ($enrolledClassIds) {
                          $q2->where('target_audience', 'students')
                             ->where(function($q3) use ($enrolledClassIds) {
                                 $q3->whereNull('target_class_id')
                                    ->orWhereIn('target_class_id', $enrolledClassIds);
                             });
                      });
                })
                ->pluck('id')
                ->toArray();

            // Mark all as read
            foreach ($announcementIds as $announcementId) {
                AnnouncementRead::firstOrCreate([
                    'announcement_id' => $announcementId,
                    'user_id' => $user->id,
                ], [
                    'read_at' => now(),
                ]);
            }

            return back()->with('success', 'All announcements marked as read!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to mark announcements as read: ' . $e->getMessage());
        }
    }
}
