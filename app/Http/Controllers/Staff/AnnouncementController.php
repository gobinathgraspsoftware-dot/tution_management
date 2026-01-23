<?php

namespace App\Http\Controllers\Staff;

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
     * Staff can only VIEW announcements - no create/edit/delete
     */
    public function __construct()
    {
        // $this->middleware('permission:view-announcements')->only(['index', 'show']);
    }

    /**
     * Display a listing of announcements for staff.
     * Staff can view ALL published announcements
     */
    public function index(Request $request)
    {
        // Build query for published announcements
        $query = Announcement::with(['creator', 'targetClass'])
            ->where('status', 'published')
            ->where(function ($q) {
                $q->whereNull('publish_at')
                  ->orWhere('publish_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>=', now());
            });

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by priority
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // Filter by target audience
        if ($request->filled('target_audience')) {
            $query->where('target_audience', $request->target_audience);
        }

        // Filter by read status
        if ($request->filled('read_status')) {
            if ($request->read_status === 'read') {
                $query->whereHas('reads', function ($q) {
                    $q->where('user_id', Auth::id());
                });
            } else {
                $query->whereDoesntHave('reads', function ($q) {
                    $q->where('user_id', Auth::id());
                });
            }
        }

        // Order: pinned first, then by priority (urgent first), then latest
        $announcements = $query->orderByDesc('is_pinned')
            ->orderByRaw("FIELD(priority, 'urgent', 'high', 'normal', 'low')")
            ->latest()
            ->paginate(15)
            ->withQueryString();

        // Get read announcement IDs for current user
        $readAnnouncementIds = AnnouncementRead::where('user_id', Auth::id())
            ->pluck('announcement_id')
            ->toArray();

        // Statistics
        $stats = [
            'total' => Announcement::where('status', 'published')
                ->where(function ($q) {
                    $q->whereNull('publish_at')
                      ->orWhere('publish_at', '<=', now());
                })
                ->where(function ($q) {
                    $q->whereNull('expires_at')
                      ->orWhere('expires_at', '>=', now());
                })
                ->count(),
            'unread' => Announcement::where('status', 'published')
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
                ->count(),
            'urgent' => Announcement::where('status', 'published')
                ->where('priority', 'urgent')
                ->where(function ($q) {
                    $q->whereNull('publish_at')
                      ->orWhere('publish_at', '<=', now());
                })
                ->where(function ($q) {
                    $q->whereNull('expires_at')
                      ->orWhere('expires_at', '>=', now());
                })
                ->count(),
            'pinned' => Announcement::where('status', 'published')
                ->where('is_pinned', true)
                ->where(function ($q) {
                    $q->whereNull('publish_at')
                      ->orWhere('publish_at', '<=', now());
                })
                ->where(function ($q) {
                    $q->whereNull('expires_at')
                      ->orWhere('expires_at', '>=', now());
                })
                ->count(),
        ];

        return view('staff.announcements.index', compact('announcements', 'readAnnouncementIds', 'stats'));
    }

    /**
     * Display the specified announcement.
     * Staff can view any published announcement
     */
    public function show(Announcement $announcement)
    {
        // Staff can view any published announcement
        if ($announcement->status !== 'published') {
            abort(403, 'This announcement is not published.');
        }

        // Check if announcement has started (publish_at)
        if ($announcement->publish_at && $announcement->publish_at > now()) {
            abort(403, 'This announcement is not yet available.');
        }

        // Check if announcement has expired
        if ($announcement->expires_at && $announcement->expires_at < now()) {
            abort(403, 'This announcement has expired.');
        }

        $announcement->load(['targetClass', 'creator']);

        // Mark as read
        AnnouncementRead::firstOrCreate([
            'announcement_id' => $announcement->id,
            'user_id' => Auth::id(),
        ], [
            'read_at' => now(),
        ]);

        return view('staff.announcements.show', compact('announcement'));
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

        return redirect()->route('staff.announcements.index')
            ->with('success', 'All announcements marked as read.');
    }

    /**
     * Download attachment.
     */
    public function downloadAttachment(Announcement $announcement, $index)
    {
        // Staff can download attachments from any published announcement
        if ($announcement->status !== 'published') {
            abort(403, 'This announcement is not published.');
        }

        // Check if announcement has started
        if ($announcement->publish_at && $announcement->publish_at > now()) {
            abort(403, 'This announcement is not yet available.');
        }

        // Check if announcement has expired
        if ($announcement->expires_at && $announcement->expires_at < now()) {
            abort(403, 'This announcement has expired.');
        }

        $attachments = $announcement->attachments ?? [];

        if (!isset($attachments[$index])) {
            abort(404, 'Attachment not found.');
        }

        $attachment = $attachments[$index];

        if (!Storage::disk('public')->exists($attachment['path'])) {
            abort(404, 'File not found.');
        }

        return Storage::disk('public')->download(
            $attachment['path'],
            $attachment['name']
        );
    }
}
