<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\TeacherDocument;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TeacherDocumentController extends Controller
{
    /**
     * Display documents for a teacher.
     */
    public function index(Teacher $teacher)
    {
        $teacher->load('user');

        $documents = $teacher->documents()
            ->orderBy('document_type')
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('document_type');

        // Get document statistics
        $stats = [
            'total' => $teacher->documents()->count(),
            'verified' => $teacher->documents()->verified()->count(),
            'pending' => $teacher->documents()->pending()->count(),
            'expired' => $teacher->documents()->expired()->count(),
            'expiring_soon' => $teacher->documents()->expiringSoon()->count(),
        ];

        return view('admin.teachers.documents.index', compact('teacher', 'documents', 'stats'));
    }

    /**
     * Upload document for teacher.
     */
    public function store(Request $request, Teacher $teacher)
    {
        $validated = $request->validate([
            'document_type' => 'required|string|in:' . implode(',', array_keys(TeacherDocument::DOCUMENT_TYPES)),
            'title' => 'required|string|max:255',
            'document' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
            'description' => 'nullable|string|max:500',
            'expiry_date' => 'nullable|date',
        ]);

        try {
            $file = $request->file('document');
            $path = $file->store('teacher-documents/' . $teacher->id, 'private');

            $document = TeacherDocument::create([
                'teacher_id' => $teacher->id,
                'document_type' => $validated['document_type'],
                'title' => $validated['title'],
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'description' => $validated['description'],
                'expiry_date' => $validated['expiry_date'],
                'status' => 'active',
            ]);

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'create',
                'model_type' => 'TeacherDocument',
                'model_id' => $document->id,
                'description' => 'Uploaded document for teacher: ' . $teacher->user->name,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return back()->with('success', 'Document uploaded successfully.');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to upload document: ' . $e->getMessage());
        }
    }

    /**
     * Download document.
     */
    public function download(TeacherDocument $document)
    {
        if (!Storage::disk('private')->exists($document->file_path)) {
            return back()->with('error', 'Document file not found.');
        }

        return Storage::disk('private')->download($document->file_path, $document->file_name);
    }

    /**
     * View document (inline).
     */
    public function view(TeacherDocument $document)
    {
        if (!Storage::disk('private')->exists($document->file_path)) {
            return back()->with('error', 'Document file not found.');
        }

        $file = Storage::disk('private')->get($document->file_path);
        $mimeType = $document->mime_type;

        return response($file)
            ->header('Content-Type', $mimeType)
            ->header('Content-Disposition', 'inline; filename="' . $document->file_name . '"');
    }

    /**
     * Verify document.
     */
    public function verify(Request $request, TeacherDocument $document)
    {
        try {
            $document->update([
                'is_verified' => true,
                'verified_by' => auth()->id(),
                'verified_at' => now(),
            ]);

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'update',
                'model_type' => 'TeacherDocument',
                'model_id' => $document->id,
                'description' => 'Verified document: ' . $document->title,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return back()->with('success', 'Document verified successfully.');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to verify document: ' . $e->getMessage());
        }
    }

    /**
     * Reject/Unverify document.
     */
    public function reject(Request $request, TeacherDocument $document)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        try {
            $document->update([
                'is_verified' => false,
                'verified_by' => null,
                'verified_at' => null,
                'description' => $document->description . "\n\nRejection Reason: " . $validated['rejection_reason'],
            ]);

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'update',
                'model_type' => 'TeacherDocument',
                'model_id' => $document->id,
                'description' => 'Rejected document: ' . $document->title,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return back()->with('success', 'Document rejected.');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to reject document: ' . $e->getMessage());
        }
    }

    /**
     * Delete document.
     */
    public function destroy(TeacherDocument $document)
    {
        try {
            // Delete file from storage
            if (Storage::disk('private')->exists($document->file_path)) {
                Storage::disk('private')->delete($document->file_path);
            }

            $documentTitle = $document->title;
            $teacherName = $document->teacher->user->name;

            $document->delete();

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'delete',
                'model_type' => 'TeacherDocument',
                'model_id' => $document->id,
                'description' => "Deleted document '{$documentTitle}' for teacher: {$teacherName}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return back()->with('success', 'Document deleted successfully.');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete document: ' . $e->getMessage());
        }
    }

    /**
     * Bulk verify documents.
     */
    public function bulkVerify(Request $request)
    {
        $validated = $request->validate([
            'document_ids' => 'required|array',
            'document_ids.*' => 'exists:teacher_documents,id',
        ]);

        try {
            TeacherDocument::whereIn('id', $validated['document_ids'])
                ->update([
                    'is_verified' => true,
                    'verified_by' => auth()->id(),
                    'verified_at' => now(),
                ]);

            return back()->with('success', count($validated['document_ids']) . ' documents verified successfully.');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to verify documents: ' . $e->getMessage());
        }
    }
}
