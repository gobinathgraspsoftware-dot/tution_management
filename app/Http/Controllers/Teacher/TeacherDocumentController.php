<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\TeacherDocument;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TeacherDocumentController extends Controller
{
    /**
     * Display teacher's own documents.
     */
    public function index()
    {
        $teacher = auth()->user()->teacher;

        if (!$teacher) {
            return redirect()->route('teacher.dashboard')
                ->with('error', 'Teacher profile not found.');
        }

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

        return view('teacher.documents.index', compact('teacher', 'documents', 'stats'));
    }

    /**
     * Show upload form.
     */
    public function create()
    {
        $documentTypes = TeacherDocument::DOCUMENT_TYPES;

        return view('teacher.documents.create', compact('documentTypes'));
    }

    /**
     * Upload a new document.
     */
    public function store(Request $request)
    {
        $teacher = auth()->user()->teacher;

        if (!$teacher) {
            return redirect()->route('teacher.dashboard')
                ->with('error', 'Teacher profile not found.');
        }

        $validated = $request->validate([
            'document_type' => 'required|string|in:' . implode(',', array_keys(TeacherDocument::DOCUMENT_TYPES)),
            'title' => 'required|string|max:255',
            'document' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
            'description' => 'nullable|string|max:500',
            'expiry_date' => 'nullable|date|after:today',
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
                'description' => 'Uploaded document: ' . $document->title,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return redirect()->route('teacher.documents.index')
                ->with('success', 'Document uploaded successfully! It will be reviewed by admin.');

        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Failed to upload document: ' . $e->getMessage());
        }
    }

    /**
     * View document details.
     */
    public function show(TeacherDocument $document)
    {
        $teacher = auth()->user()->teacher;

        // Ensure the document belongs to this teacher
        if ($document->teacher_id !== $teacher->id) {
            abort(403, 'Unauthorized access.');
        }

        return view('teacher.documents.show', compact('document'));
    }

    /**
     * Download document.
     */
    public function download(TeacherDocument $document)
    {
        $teacher = auth()->user()->teacher;

        // Ensure the document belongs to this teacher
        if ($document->teacher_id !== $teacher->id) {
            abort(403, 'Unauthorized access.');
        }

        if (!Storage::disk('private')->exists($document->file_path)) {
            return back()->with('error', 'Document file not found.');
        }

        return Storage::disk('private')->download($document->file_path, $document->file_name);
    }

    /**
     * Delete document.
     */
    public function destroy(TeacherDocument $document)
    {
        $teacher = auth()->user()->teacher;

        // Ensure the document belongs to this teacher
        if ($document->teacher_id !== $teacher->id) {
            abort(403, 'Unauthorized access.');
        }

        // Only allow deletion of unverified documents
        if ($document->is_verified) {
            return back()->with('error', 'Cannot delete verified documents. Please contact admin.');
        }

        try {
            // Delete file from storage
            if (Storage::disk('private')->exists($document->file_path)) {
                Storage::disk('private')->delete($document->file_path);
            }

            $documentTitle = $document->title;
            $document->delete();

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'delete',
                'model_type' => 'TeacherDocument',
                'model_id' => $document->id,
                'description' => 'Deleted own document: ' . $documentTitle,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return redirect()->route('teacher.documents.index')
                ->with('success', 'Document deleted successfully.');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete document: ' . $e->getMessage());
        }
    }
}
