<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\AnnouncementRead;
use App\Models\ClassModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class AnnouncementController extends Controller
{
    /**
     * Display a listing of announcements for teacher.
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
                $q->whereIn('class_id', $classIds)
                  // OR general announcements for teachers
                  ->orWhere(function ($sq) {
                      $sq->whereNull('class_id')
                         ->where(function ($tq) {
                             $tq->where('target_audience', 'all')
                                ->orWhere('target_audience', 'teachers');
                         });
                  });
            })
            ->where('status', 'published')
            ->with(['class', 'createdBy']);

        // Filter by type
        if ($request->filled('type')) {
            if ($request->type === 'class') {
                $query->whereIn('class_id', $classIds);
            } elseif ($request->type === 'general') {
                $query->whereNull('class_id');
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
            ->orderBy('published_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        // Mark which ones are read
        $readIds = AnnouncementRead::where('user_id', Auth::id())
            ->whereIn('announcement_id', $announcements->pluck('id'))
            ->pluck('announcement_id')
            ->toArray();

        // Get unread count
        $unreadCount = Announcement::where(function ($q) use ($classIds) {
                $q->whereIn('class_id', $classIds)
                  ->orWhere(function ($sq) {
                      $sq->whereNull('class_id')
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

        // Get teacher's classes for creating announcements
        $classes = ClassModel::where('teacher_id', $teacher->id)
            ->where('status', 'active')
            ->get();

        return view('teacher.announcements.index', compact('announcements', 'readIds', 'unreadCount', 'classes'));
    }

    /**
     * Display a specific announcement.
     */
    public function show(Announcement $announcement)
    {
        $teacher = Auth::user()->teacher;

        // Get teacher's class IDs
        $classIds = ClassModel::where('teacher_id', $teacher->id)->pluck('id')->toArray();

        // Verify teacher can view this announcement
        $canView = false;
        if ($announcement->class_id && in_array($announcement->class_id, $classIds)) {
            $canView = true;
        } elseif (!$announcement->class_id && in_array($announcement->target_audience, ['all', 'teachers'])) {
            $canView = true;
        }

        if (!$canView) {
            abort(403, 'Unauthorized access.');
        }

        $announcement->load(['class', 'createdBy']);

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
     * Show form for creating announcement for teacher's class.
     */
    public function create()
    {
        $teacher = Auth::user()->teacher;

        $classes = ClassModel::where('teacher_id', $teacher->id)
            ->where('status', 'active')
            ->with('subject')
            ->get();

        if ($classes->isEmpty()) {
            return redirect()->route('teacher.announcements.index')
                ->with('error', 'You need active classes to create announcements.');
        }

        return view('teacher.announcements.create', compact('classes'));
    }

    /**
     * Store a new announcement for teacher's class.
     */
    public function store(Request $request)
    {
        $teacher = Auth::user()->teacher;

        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string|max:5000',
            'class_id' => 'required|exists:classes,id',
            'priority' => 'required|in:low,normal,high,urgent',
            'publish_now' => 'nullable|boolean',
            'publish_date' => 'required_without:publish_now|nullable|date|after_or_equal:today',
            'expires_at' => 'nullable|date|after:publish_date',
            'attachments.*' => 'nullable|file|max:10240', // 10MB max
        ]);

        // Verify teacher owns this class
        $class = ClassModel::findOrFail($request->class_id);
        if ($class->teacher_id !== $teacher->id) {
            abort(403, 'Unauthorized access.');
        }

        // Handle attachments
        $attachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('announcements', 'public');
                $attachments[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'type' => $file->getMimeType(),
                ];
            }
        }

        $announcement = Announcement::create([
            'title' => $request->title,
            'content' => $request->content,
            'class_id' => $request->class_id,
            'priority' => $request->priority,
            'target_audience' => 'class', // Teacher can only announce to their class
            'attachments' => !empty($attachments) ? json_encode($attachments) : null,
            'status' => $request->publish_now ? 'published' : 'scheduled',
            'published_at' => $request->publish_now ? now() : $request->publish_date,
            'expires_at' => $request->expires_at,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('teacher.announcements.index')
            ->with('success', 'Announcement created successfully.');
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

        // Get teacher's class IDs
        $classIds = ClassModel::where('teacher_id', $teacher->id)->pluck('id')->toArray();

        $unreadAnnouncements = Announcement::where(function ($q) use ($classIds) {
                $q->whereIn('class_id', $classIds)
                  ->orWhere(function ($sq) {
                      $sq->whereNull('class_id')
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
}
