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
     * Teachers can view ALL published announcements from admin
     */
    public function index(Request $request)
    {
        $teacher = Auth::user()->teacher;

        if (!$teacher) {
            abort(403, 'Teacher profile not found.');
        }

        // Build query - Teachers can see ALL published announcements
        $query = Announcement::where('status', 'published')
            // Check publish_at date
            ->where(function ($q) {
                $q->whereNull('publish_at')
                  ->orWhere('publish_at', '<=', now());
            })
            // Check expires_at date
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>=', now());
            })
            ->with(['targetClass', 'creator']);

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by target audience
        if ($request->filled('target')) {
            $query->where('target_audience', $request->target);
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

        // Get unread count - ALL published announcements
        $unreadCount = Announcement::where('status', 'published')
            ->where(function ($q) {
                $q->whereNull('publish_at')
                  ->orWhere('publish_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>=', now());
            })
            ->whereDoesntHave('reads', function ($q) {
                $q->where('user_id', Auth::id());
            })
            ->count();

        return view('teacher.announcements.index', compact('announcements', 'readIds', 'unreadCount'));
    }

    /**
     * Display a specific announcement.
     * Teachers can view any published announcement
     */
    public function show(Announcement $announcement)
    {
        $teacher = Auth::user()->teacher;

        if (!$teacher) {
            abort(403, 'Teacher profile not found.');
        }

        // Teachers can view any published announcement
        if ($announcement->status !== 'published') {
            abort(403, 'This announcement is not published.');
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

        // Mark ALL published announcements as read
        $unreadAnnouncements = Announcement::where('status', 'published')
            ->where(function ($q) {
                $q->whereNull('publish_at')
                  ->orWhere('publish_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>=', now());
            })
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

        // Teachers can download attachments from any published announcement
        if ($announcement->status !== 'published') {
            abort(403, 'This announcement is not published.');
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
