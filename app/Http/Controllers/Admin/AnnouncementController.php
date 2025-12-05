<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AnnouncementRequest;
use App\Models\Announcement;
use App\Models\ClassModel;
use App\Models\ActivityLog;
use App\Services\AnnouncementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AnnouncementController extends Controller
{
    protected $announcementService;

    public function __construct(AnnouncementService $announcementService)
    {
        $this->announcementService = $announcementService;
    }

    /**
     * Display announcements listing.
     */
    public function index(Request $request)
    {
        $query = Announcement::with(['creator', 'targetClass'])->latest();

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
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

        $announcements = $query->paginate(15)->withQueryString();

        // Statistics - Fixed: Now using actual counts from database
        $stats = [
            'total' => Announcement::count(),
            'published' => Announcement::published()->count(),
            'draft' => Announcement::draft()->count(),
            'urgent' => Announcement::urgent()->count(),
            'pinned' => Announcement::pinned()->count(),
            'archived' => Announcement::archived()->count(),
        ];

        return view('admin.announcements.index', compact('announcements', 'stats'));
    }

    /**
     * Show create form.
     */
    public function create()
    {
        $classes = ClassModel::active()->with('subject')->orderBy('name')->get();
        return view('admin.announcements.create', compact('classes'));
    }

    /**
     * Store new announcement.
     */
    public function store(AnnouncementRequest $request)
    {
        try {
            $data = $request->validated();
            $data['created_by'] = auth()->id();

            // Handle file attachments
            if ($request->hasFile('attachment_files')) {
                $attachments = [];
                foreach ($request->file('attachment_files') as $file) {
                    $path = $file->store('announcements', 'public');
                    $attachments[] = [
                        'name' => $file->getClientOriginalName(),
                        'path' => $path,
                        'size' => $file->getSize(),
                        'type' => $file->getMimeType(),
                    ];
                }
                $data['attachments'] = $attachments;
            }

            $announcement = Announcement::create($data);

            // If publish now, send notifications
            if ($data['status'] === 'published' && (!isset($data['publish_at']) || $data['publish_at'] <= now())) {
                $this->announcementService->sendNotifications($announcement);
            }

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'create',
                'model_type' => 'Announcement',
                'model_id' => $announcement->id,
                'description' => "Created announcement: {$announcement->title}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return redirect()->route('admin.announcements.index')
                ->with('success', 'Announcement created successfully!');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Failed to create announcement: ' . $e->getMessage());
        }
    }

    /**
     * Display announcement details.
     */
    public function show(Announcement $announcement)
    {
        $announcement->load(['creator', 'targetClass', 'reads.user']);

        $stats = [
            'read_count' => $announcement->getReadCount(),
            'unread_count' => $this->announcementService->getUnreadCount($announcement),
        ];

        return view('admin.announcements.show', compact('announcement', 'stats'));
    }

    /**
     * Show edit form.
     */
    public function edit(Announcement $announcement)
    {
        $classes = ClassModel::active()->with('subject')->orderBy('name')->get();
        return view('admin.announcements.edit', compact('announcement', 'classes'));
    }

    /**
     * Update announcement.
     */
    public function update(AnnouncementRequest $request, Announcement $announcement)
    {
        try {
            $data = $request->validated();

            // Handle file attachments
            if ($request->hasFile('attachment_files')) {
                $attachments = $announcement->attachments ?? [];
                foreach ($request->file('attachment_files') as $file) {
                    $path = $file->store('announcements', 'public');
                    $attachments[] = [
                        'name' => $file->getClientOriginalName(),
                        'path' => $path,
                        'size' => $file->getSize(),
                        'type' => $file->getMimeType(),
                    ];
                }
                $data['attachments'] = $attachments;
            }

            $announcement->update($data);

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'update',
                'model_type' => 'Announcement',
                'model_id' => $announcement->id,
                'description' => "Updated announcement: {$announcement->title}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return redirect()->route('admin.announcements.index')
                ->with('success', 'Announcement updated successfully!');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Failed to update announcement: ' . $e->getMessage());
        }
    }

    /**
     * Delete announcement.
     */
    public function destroy(Announcement $announcement)
    {
        try {
            $title = $announcement->title;

            // Delete attachments from storage
            if ($announcement->attachments) {
                foreach ($announcement->attachments as $attachment) {
                    if (isset($attachment['path'])) {
                        Storage::disk('public')->delete($attachment['path']);
                    }
                }
            }

            $announcement->delete();

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'delete',
                'model_type' => 'Announcement',
                'model_id' => $announcement->id,
                'description' => "Deleted announcement: {$title}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return redirect()->route('admin.announcements.index')
                ->with('success', 'Announcement deleted successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete announcement: ' . $e->getMessage());
        }
    }

    /**
     * Publish announcement.
     */
    public function publish(Announcement $announcement)
    {
        try {
            $announcement->update([
                'status' => 'published',
                'publish_at' => now(),
            ]);

            // Send notifications
            $this->announcementService->sendNotifications($announcement);

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'publish',
                'model_type' => 'Announcement',
                'model_id' => $announcement->id,
                'description' => "Published announcement: {$announcement->title}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return back()->with('success', 'Announcement published and notifications sent!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to publish announcement: ' . $e->getMessage());
        }
    }

    /**
     * Archive announcement.
     */
    public function archive(Announcement $announcement)
    {
        try {
            $announcement->update(['status' => 'archived']);

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'archive',
                'model_type' => 'Announcement',
                'model_id' => $announcement->id,
                'description' => "Archived announcement: {$announcement->title}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return back()->with('success', 'Announcement archived successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to archive announcement: ' . $e->getMessage());
        }
    }

    /**
     * Toggle pin status.
     */
    public function togglePin(Announcement $announcement)
    {
        try {
            $announcement->update(['is_pinned' => !$announcement->is_pinned]);

            $status = $announcement->is_pinned ? 'pinned' : 'unpinned';

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => $status,
                'model_type' => 'Announcement',
                'model_id' => $announcement->id,
                'description' => ucfirst($status) . " announcement: {$announcement->title}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return back()->with('success', "Announcement {$status} successfully!");
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to toggle pin status: ' . $e->getMessage());
        }
    }

    /**
     * Mark as read (for viewing user).
     */
    public function markAsRead(Announcement $announcement)
    {
        try {
            $announcement->markAsReadBy(auth()->id());
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete attachment.
     */
    public function deleteAttachment(Announcement $announcement, $index)
    {
        try {
            $attachments = $announcement->attachments;

            if (isset($attachments[$index])) {
                // Delete file from storage
                Storage::disk('public')->delete($attachments[$index]['path']);

                // Remove from array
                unset($attachments[$index]);
                $announcement->update(['attachments' => array_values($attachments)]);

                return back()->with('success', 'Attachment deleted successfully!');
            }

            return back()->with('error', 'Attachment not found!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete attachment: ' . $e->getMessage());
        }
    }

    /**
     * Duplicate announcement.
     */
    public function duplicate(Announcement $announcement)
    {
        try {
            $newAnnouncement = $announcement->replicate();
            $newAnnouncement->title = $announcement->title . ' (Copy)';
            $newAnnouncement->status = 'draft';
            $newAnnouncement->is_pinned = false;
            $newAnnouncement->publish_at = null;
            $newAnnouncement->created_by = auth()->id();
            $newAnnouncement->save();

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'duplicate',
                'model_type' => 'Announcement',
                'model_id' => $newAnnouncement->id,
                'description' => "Duplicated announcement: {$announcement->title}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return redirect()->route('admin.announcements.edit', $newAnnouncement)
                ->with('success', 'Announcement duplicated successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to duplicate announcement: ' . $e->getMessage());
        }
    }

    /**
     * Bulk delete announcements.
     */
    public function bulkDelete(Request $request)
    {
        try {
            $ids = $request->input('ids', []);

            if (empty($ids)) {
                return back()->with('error', 'No announcements selected!');
            }

            $announcements = Announcement::whereIn('id', $ids)->get();

            foreach ($announcements as $announcement) {
                // Delete attachments
                if ($announcement->attachments) {
                    foreach ($announcement->attachments as $attachment) {
                        if (isset($attachment['path'])) {
                            Storage::disk('public')->delete($attachment['path']);
                        }
                    }
                }
                $announcement->delete();
            }

            return back()->with('success', count($ids) . ' announcements deleted successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete announcements: ' . $e->getMessage());
        }
    }
}
