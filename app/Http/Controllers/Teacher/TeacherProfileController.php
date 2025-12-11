<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\TeacherDocument;
use App\Models\ActivityLog;
use App\Services\TeacherProfileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class TeacherProfileController extends Controller
{
    protected $profileService;

    public function __construct(TeacherProfileService $profileService)
    {
        $this->profileService = $profileService;
    }

    /**
     * Display teacher's own profile.
     */
    public function index()
    {
        $user = auth()->user();
        $teacher = $user->teacher;

        if (!$teacher) {
            return redirect()->route('teacher.dashboard')
                ->with('error', 'Teacher profile not found.');
        }

        $teacher->load(['user', 'classes.subject', 'classes.schedules', 'documents']);

        // Get statistics
        $stats = $this->profileService->getTeacherStatistics($teacher);

        // Get recent activity
        $recentActivity = ActivityLog::where('user_id', $user->id)
            ->latest()
            ->take(10)
            ->get();

        return view('teacher.profile.index', compact('teacher', 'stats', 'recentActivity'));
    }

    /**
     * Show the form for editing teacher's profile.
     */
    public function edit()
    {
        $user = auth()->user();
        $teacher = $user->teacher;

        if (!$teacher) {
            return redirect()->route('teacher.dashboard')
                ->with('error', 'Teacher profile not found.');
        }

        $teacher->load('user');

        return view('teacher.profile.edit', compact('teacher'));
    }

    /**
     * Update teacher's profile.
     */
    public function update(Request $request)
    {
        $user = auth()->user();
        $teacher = $user->teacher;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string|max:500',
            'bio' => 'nullable|string|max:1000',
            'qualification' => 'nullable|string|max:500',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        try {
            // Update user info
            $user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
            ]);

            // Handle profile photo upload
            if ($request->hasFile('profile_photo')) {
                $path = $request->file('profile_photo')->store('profile-photos', 'public');

                // Delete old photo if exists
                if ($user->profile_photo) {
                    Storage::disk('public')->delete($user->profile_photo);
                }

                $user->update(['profile_photo' => $path]);
            }

            // Update teacher profile
            $teacher->update([
                'address' => $validated['address'],
                'bio' => $validated['bio'],
                'qualification' => $validated['qualification'],
            ]);

            // Log activity
            ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'update',
                'model_type' => 'Teacher',
                'model_id' => $teacher->id,
                'description' => 'Updated own profile',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return redirect()->route('teacher.profile.index')
                ->with('success', 'Profile updated successfully.');

        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Failed to update profile: ' . $e->getMessage());
        }
    }

    /**
     * Update teacher's password.
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = auth()->user();

        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()->with('error', 'Current password is incorrect.');
        }

        try {
            $user->update([
                'password' => Hash::make($validated['password']),
            ]);

            // Log activity
            ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'update',
                'model_type' => 'User',
                'model_id' => $user->id,
                'description' => 'Changed password',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return redirect()->route('teacher.profile.index')
                ->with('success', 'Password changed successfully.');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to change password: ' . $e->getMessage());
        }
    }

    /**
     * Upload teacher document.
     */
    public function uploadDocument(Request $request)
    {
        $validated = $request->validate([
            'document_type' => 'required|string|in:' . implode(',', array_keys(TeacherDocument::DOCUMENT_TYPES)),
            'title' => 'required|string|max:255',
            'document' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
            'description' => 'nullable|string|max:500',
            'expiry_date' => 'nullable|date|after:today',
        ]);

        $teacher = auth()->user()->teacher;

        try {
            $file = $request->file('document');
            $path = $file->store('teacher-documents/' . $teacher->id, 'private');

            TeacherDocument::create([
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

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'create',
                'model_type' => 'TeacherDocument',
                'model_id' => $teacher->id,
                'description' => 'Uploaded document: ' . $validated['title'],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return back()->with('success', 'Document uploaded successfully.');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to upload document: ' . $e->getMessage());
        }
    }

    /**
     * Download teacher document.
     */
    public function downloadDocument(TeacherDocument $document)
    {
        $teacher = auth()->user()->teacher;

        // Ensure teacher can only access their own documents
        if ($document->teacher_id !== $teacher->id) {
            abort(403, 'Unauthorized access.');
        }

        if (!Storage::disk('private')->exists($document->file_path)) {
            return back()->with('error', 'Document file not found.');
        }

        return Storage::disk('private')->download($document->file_path, $document->file_name);
    }

    /**
     * Delete teacher document.
     */
    public function deleteDocument(TeacherDocument $document)
    {
        $teacher = auth()->user()->teacher;

        // Ensure teacher can only delete their own documents
        if ($document->teacher_id !== $teacher->id) {
            abort(403, 'Unauthorized access.');
        }

        try {
            // Delete file from storage
            if (Storage::disk('private')->exists($document->file_path)) {
                Storage::disk('private')->delete($document->file_path);
            }

            $document->delete();

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'delete',
                'model_type' => 'TeacherDocument',
                'model_id' => $document->id,
                'description' => 'Deleted document: ' . $document->title,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return back()->with('success', 'Document deleted successfully.');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete document: ' . $e->getMessage());
        }
    }
}
