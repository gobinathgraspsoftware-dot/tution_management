<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\AnnouncementRead;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AnnouncementController extends Controller
{
    /**
     * Display a listing of announcements for parent.
     * Parents can view announcements targeted to:
     * - 'all' (everyone)
     * - 'parents' (all parents)
     * - 'specific_class' (classes their children are enrolled in)
     */
    public function index(Request $request)
    {
        $parent = Auth::user()->parent;

        if (!$parent) {
            return redirect()->route('parent.dashboard')
                ->with('error', 'Parent profile not found.');
        }

        // Get children's enrolled class IDs
        $childrenClassIds = Student::where('parent_id', $parent->id)
            ->whereHas('user', fn($q) => $q->where('status', 'active'))
            ->with(['enrollments' => function($q) {
                $q->where('status', 'active');
            }])
            ->get()
            ->pluck('enrollments')
            ->flatten()
            ->pluck('class_id')
            ->unique()
            ->toArray();

        // Build query - Parents can see announcements targeted to them
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
            // Target audience filter
            ->where(function ($q) use ($childrenClassIds) {
                $q->where('target_audience', 'all')
                  ->orWhere('target_audience', 'parents')
                  ->orWhere(function ($q2) use ($childrenClassIds) {
                      // Announcements for specific classes their children are in
                      $q2->where('target_audience', 'specific_class')
                         ->whereIn('target_class_id', $childrenClassIds);
                  });
            })
            ->with(['targetClass', 'creator']);

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
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
        $unreadCount = Announcement::where('status', 'published')
            ->where(function ($q) {
                $q->whereNull('publish_at')
                  ->orWhere('publish_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>=', now());
            })
            ->where(function ($q) use ($childrenClassIds) {
                $q->where('target_audience', 'all')
                  ->orWhere('target_audience', 'parents')
                  ->orWhere(function ($q2) use ($childrenClassIds) {
                      $q2->where('target_audience', 'specific_class')
                         ->whereIn('target_class_id', $childrenClassIds);
                  });
            })
            ->whereDoesntHave('reads', function ($q) {
                $q->where('user_id', Auth::id());
            })
            ->count();

        return view('parent.announcements.index', compact('announcements', 'readIds', 'unreadCount'));
    }

    /**
     * Display a specific announcement.
     */
    public function show(Announcement $announcement)
    {
        $parent = Auth::user()->parent;

        if (!$parent) {
            return redirect()->route('parent.dashboard')
                ->with('error', 'Parent profile not found.');
        }

        // Check if announcement is published
        if ($announcement->status !== 'published') {
            abort(403, 'This announcement is not available.');
        }

        // Get children's enrolled class IDs
        $childrenClassIds = Student::where('parent_id', $parent->id)
            ->whereHas('user', fn($q) => $q->where('status', 'active'))
            ->with(['enrollments' => function($q) {
                $q->where('status', 'active');
            }])
            ->get()
            ->pluck('enrollments')
            ->flatten()
            ->pluck('class_id')
            ->unique()
            ->toArray();

        // Verify parent can view this announcement
        $canView = in_array($announcement->target_audience, ['all', 'parents']) ||
                   ($announcement->target_audience === 'specific_class' && 
                    in_array($announcement->target_class_id, $childrenClassIds));

        if (!$canView) {
            abort(403, 'You do not have permission to view this announcement.');
        }

        $announcement->load(['targetClass', 'creator']);

        // Mark as read
        AnnouncementRead::firstOrCreate([
            'announcement_id' => $announcement->id,
            'user_id' => Auth::id(),
        ], [
            'read_at' => now(),
        ]);

        return view('parent.announcements.show', compact('announcement'));
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
        $parent = Auth::user()->parent;

        if (!$parent) {
            return redirect()->route('parent.announcements.index')
                ->with('error', 'Parent profile not found.');
        }

        // Get children's enrolled class IDs
        $childrenClassIds = Student::where('parent_id', $parent->id)
            ->whereHas('user', fn($q) => $q->where('status', 'active'))
            ->with(['enrollments' => function($q) {
                $q->where('status', 'active');
            }])
            ->get()
            ->pluck('enrollments')
            ->flatten()
            ->pluck('class_id')
            ->unique()
            ->toArray();

        // Get all unread announcements for this parent
        $unreadAnnouncements = Announcement::where('status', 'published')
            ->where(function ($q) {
                $q->whereNull('publish_at')
                  ->orWhere('publish_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>=', now());
            })
            ->where(function ($q) use ($childrenClassIds) {
                $q->where('target_audience', 'all')
                  ->orWhere('target_audience', 'parents')
                  ->orWhere(function ($q2) use ($childrenClassIds) {
                      $q2->where('target_audience', 'specific_class')
                         ->whereIn('target_class_id', $childrenClassIds);
                  });
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

        return redirect()->route('parent.announcements.index')
            ->with('success', 'All announcements marked as read.');
    }

    /**
     * Download attachment.
     */
    public function downloadAttachment(Announcement $announcement, $index)
    {
        $parent = Auth::user()->parent;

        if (!$parent) {
            abort(403, 'Parent profile not found.');
        }

        // Check if announcement is published
        if ($announcement->status !== 'published') {
            abort(403, 'This announcement is not available.');
        }

        // Get children's enrolled class IDs
        $childrenClassIds = Student::where('parent_id', $parent->id)
            ->whereHas('user', fn($q) => $q->where('status', 'active'))
            ->with(['enrollments' => function($q) {
                $q->where('status', 'active');
            }])
            ->get()
            ->pluck('enrollments')
            ->flatten()
            ->pluck('class_id')
            ->unique()
            ->toArray();

        // Verify parent can access this announcement
        $canView = in_array($announcement->target_audience, ['all', 'parents']) ||
                   ($announcement->target_audience === 'specific_class' && 
                    in_array($announcement->target_class_id, $childrenClassIds));

        if (!$canView) {
            abort(403, 'You do not have permission to access this attachment.');
        }

        $attachments = $announcement->attachments ?? [];

        if (!isset($attachments[$index])) {
            abort(404, 'Attachment not found.');
        }

        $attachment = $attachments[$index];
        $path = $attachment['path'] ?? $attachment;

        if (!Storage::disk('public')->exists($path)) {
            abort(404, 'File not found.');
        }

        $filename = $attachment['original_name'] ?? basename($path);

        return Storage::disk('public')->download($path, $filename);
    }
}
