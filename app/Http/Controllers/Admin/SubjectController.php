<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubjectRequest;
use App\Models\GradeLevel;
use App\Models\Subject;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    /**
     * Display a listing of subjects.
     */
    public function index(Request $request)
    {
        $query = Subject::withCount(['packages', 'classes']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // CHANGED: Filter by grade level ID (integer) instead of name (string)
        if ($request->filled('grade_level')) {
            $query->whereJsonContains('grade_levels', (int) $request->grade_level);
        }

        $subjects = $query->latest()->paginate(15)->withQueryString();

        // CHANGED: Get grade levels as objects (id + name) for filter dropdown
        $allGradeLevels = GradeLevel::ordered()->get();

        return view('admin.subjects.index', compact('subjects', 'allGradeLevels'));
    }

    /**
     * Show the form for creating a new subject.
     */
    public function create()
    {
        $gradeLevels = $this->getGradeLevelOptions();
        return view('admin.subjects.create', compact('gradeLevels'));
    }

    /**
     * Store a newly created subject.
     */
    public function store(SubjectRequest $request)
    {
        $validated = $request->validated();

        // CHANGED: Cast grade level values to integers before saving
        $validated['grade_levels'] = array_map('intval', $request->grade_levels ?? []);

        Subject::create($validated);

        return redirect()->route('admin.subjects.index')
            ->with('success', 'Subject created successfully.');
    }

    /**
     * Display the specified subject.
     */
    public function show(Subject $subject)
    {
        $subject->load(['packages', 'classes.teacher.user', 'materials']);

        return view('admin.subjects.show', compact('subject'));
    }

    /**
     * Show the form for editing the specified subject.
     */
    public function edit(Subject $subject)
    {
        $gradeLevels = $this->getGradeLevelOptions();
        return view('admin.subjects.edit', compact('subject', 'gradeLevels'));
    }

    /**
     * Update the specified subject.
     */
    public function update(SubjectRequest $request, Subject $subject)
    {
        $validated = $request->validated();

        // CHANGED: Cast grade level values to integers before saving
        $validated['grade_levels'] = array_map('intval', $request->grade_levels ?? []);

        $subject->update($validated);

        return redirect()->route('admin.subjects.index')
            ->with('success', 'Subject updated successfully.');
    }

    /**
     * Remove the specified subject.
     */
    public function destroy(Subject $subject)
    {
        // Check if subject has related records
        if ($subject->classes()->exists()) {
            return back()->with('error', 'Cannot delete subject with associated classes.');
        }

        if ($subject->packages()->exists()) {
            return back()->with('error', 'Cannot delete subject associated with packages.');
        }

        $subject->delete();

        return redirect()->route('admin.subjects.index')
            ->with('success', 'Subject deleted successfully.');
    }

    /**
     * Toggle subject status.
     */
    public function toggleStatus(Subject $subject)
    {
        $newStatus = $subject->status === 'active' ? 'inactive' : 'active';
        $subject->update(['status' => $newStatus]);

        $statusText = $newStatus === 'active' ? 'activated' : 'deactivated';
        return back()->with('success', "Subject {$statusText} successfully.");
    }

    /**
     * Restore a soft-deleted subject.
     */
    public function restore($id)
    {
        $subject = Subject::withTrashed()->findOrFail($id);
        $subject->restore();

        return redirect()->route('admin.subjects.index')
            ->with('success', 'Subject restored successfully.');
    }

    /**
     * CHANGED: Get grade level options dynamically — returns [id => name].
     *
     * BEFORE: Static array ['Standard 1' => 'Standard 1', ...]
     * AFTER:  Dynamic DB query [1 => 'Standard 1', 2 => 'Standard 2', ...]
     *
     * Blade views iterate as @foreach($gradeLevels as $key => $label)
     * where $key = grade_level ID, $label = grade_level name.
     */
    private function getGradeLevelOptions(): array
    {
        return GradeLevel::ordered()
            ->pluck('name', 'id')
            ->toArray();
    }
}
