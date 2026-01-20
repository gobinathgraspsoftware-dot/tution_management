<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\AnnouncementRead;
use App\Models\ClassModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AnnouncementController extends Controller
{
    /**
     * Constructor with permission middleware.
     * Teacher can only VIEW announcements - no create/edit/delete
     */
    public function __construct()
    {
        // $this->middleware('permission:view-announcements');
    }

    /**
     * Display a listing of announcements for teacher.
     * Shows announcements for teacher's classes + general announcements
     */
    public function index(Request $request)
    {
        $teacher = Auth::user()->teacher;

        if (!$teacher) {
            abort(403, 'Teacher profile not found.');
        }

        // Get teacher's class IDs
        $classIds = ClassModel::where('teacher_id', $teacher->id)->pluck('id')->toArray();

        $query = Announcement::where(function ($q) use ($classIds) {
                // Announcements for teacher's classes
                $q->whereIn('target_class_id', $classIds)
                  // OR general announcements for teachers
                  ->orWhere(function ($sq) {
                      $sq->whereNull('target_class_id')
                         ->where(function ($tq) {
                             $tq->where('target_audience', 'all')
                                ->orWhere('target_audience', 'teachers');
                         });
                  });
            })
            ->where('status', 'published')
            ->with(['targetClass', 'creator']);

        // Filter by type
        if ($request->filled('type')) {
            if ($request->type === 'class') {
                $query->whereIn('target_class_id', $classIds);
            } elseif ($request->type === 'general') {
                $query->whereNull('target_class_id');
            }
        }

        // Filter by priority
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // Filter by read status
        if ($request->filled('read_status')) {
            if ($request->read_status === 'unread') {
                $query->whereDoesntHave('reads', function ($q) {
                    $q->where('user_id', Auth::id());
                });
            } elseif ($request->read_status === 'read') {
                $query->whereHas('reads', function ($q) {
                    $q->where('user_id', Auth::id());
                });
            }
        }

        $announcements = $query->orderBy('is_pinned', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        // Mark which ones are read
        $readIds = AnnouncementRead::where('user_id', Auth::id())
            ->whereIn('announcement_id', $announcements->pluck('id'))
            ->pluck('announcement_id')
            ->toArray();

        // Get unread count
        $unreadCount = Announcement::where(function ($q) use ($classIds) {
                $q->whereIn('target_class_id', $classIds)
                  ->orWhere(function ($sq) {
                      $sq->whereNull('target_class_id')
                         ->where(function ($tq) {
                             $tq->where('target_audience', 'all')
                                ->orWhere('target_audience', 'teachers');
                         });
                  });
            })
            ->where('status', 'published')
            ->whereDoesntHave('reads', function ($q) {
                $q->where('user_id', Auth::id());
            })
            ->count();

        return view('teacher.announcements.index', compact('announcements', 'readIds', 'unreadCount'));
    }

    /**
     * Display a specific announcement.
     */
    public function show(Announcement $announcement)
    {
        $teacher = Auth::user()->teacher;

        if (!$teacher) {
            abort(403, 'Teacher profile not found.');
        }

        // Get teacher's class IDs
        $classIds = ClassModel::where('teacher_id', $teacher->id)->pluck('id')->toArray();

        // Verify teacher can view this announcement
        $canView = false;
        if ($announcement->target_class_id && in_array($announcement->target_class_id, $classIds)) {
            $canView = true;
        } elseif (!$announcement->target_class_id && in_array($announcement->target_audience, ['all', 'teachers'])) {
            $canView = true;
        }

        if (!$canView) {
            abort(403, 'Unauthorized access.');
        }

        $announcement->load(['targetClass', 'creator']);

        // Mark as read
        AnnouncementRead::firstOrCreate([
            'announcement_id' => $announcement->id,
            'user_id' => Auth::id(),
        ], [
            'read_at' => now(),
        ]);

        return view('teacher.announcements.show', compact('announcement'));
    }

    /**
     * Mark announcement as read (AJAX).
     */
    public function markAsRead(Announcement $announcement)
    {
        AnnouncementRead::firstOrCreate([
            'announcement_id' => $announcement->id,
            'user_id' => Auth::id(),
        ], [
            'read_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Mark all announcements as read.
     */
    public function markAllAsRead()
    {
        $teacher = Auth::user()->teacher;

        if (!$teacher) {
            return redirect()->route('teacher.announcements.index')
                ->with('error', 'Teacher profile not found.');
        }

        // Get teacher's class IDs
        $classIds = ClassModel::where('teacher_id', $teacher->id)->pluck('id')->toArray();

        $unreadAnnouncements = Announcement::where(function ($q) use ($classIds) {
                $q->whereIn('target_class_id', $classIds)
                  ->orWhere(function ($sq) {
                      $sq->whereNull('target_class_id')
                         ->where(function ($tq) {
                             $tq->where('target_audience', 'all')
                                ->orWhere('target_audience', 'teachers');
                         });
                  });
            })
            ->where('status', 'published')
            ->whereDoesntHave('reads', function ($q) {
                $q->where('user_id', Auth::id());
            })
            ->pluck('id');

        foreach ($unreadAnnouncements as $announcementId) {
            AnnouncementRead::firstOrCreate([
                'announcement_id' => $announcementId,
                'user_id' => Auth::id(),
            ], [
                'read_at' => now(),
            ]);
        }

        return redirect()->route('teacher.announcements.index')
            ->with('success', 'All announcements marked as read.');
    }

    /**
     * Download attachment.
     */
    public function downloadAttachment(Announcement $announcement, $index)
    {
        $teacher = Auth::user()->teacher;

        if (!$teacher) {
            abort(403, 'Teacher profile not found.');
        }

        // Verify teacher can access this announcement
        $classIds = ClassModel::where('teacher_id', $teacher->id)->pluck('id')->toArray();

        $canAccess = false;
        if ($announcement->target_class_id && in_array($announcement->target_class_id, $classIds)) {
            $canAccess = true;
        } elseif (!$announcement->target_class_id && in_array($announcement->target_audience, ['all', 'teachers'])) {
            $canAccess = true;
        }

        if (!$canAccess) {
            abort(403, 'Unauthorized access.');
        }

        $attachments = $announcement->attachments;

        if (!$attachments || !isset($attachments[$index])) {
            abort(404, 'Attachment not found.');
        }

        $attachment = $attachments[$index];
        $path = $attachment['path'];
        $name = $attachment['name'] ?? 'attachment';

        if (!Storage::disk('public')->exists($path)) {
            abort(404, 'File not found.');
        }

        return Storage::disk('public')->download($path, $name);
    }
}
