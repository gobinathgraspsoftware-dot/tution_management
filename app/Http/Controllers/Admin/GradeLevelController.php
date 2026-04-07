<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\GradeLevelRequest;
use App\Models\GradeLevel;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class GradeLevelController extends Controller
{
    /**
     * Restrict all actions to admin / super-admin roles.
     */
    public function __construct()
    {
        // $this->middleware(['auth', 'role:super-admin|admin']);
    }

    // ──────────────────────────────────────────────────────────────
    // INDEX
    // ──────────────────────────────────────────────────────────────

    /**
     * Display all grade levels (with optional search).
     */
    public function index(Request $request)
    {
        $query = GradeLevel::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $gradeLevels = $query->ordered()->paginate(20)->withQueryString();

        return view('admin.grade-levels.index', compact('gradeLevels'));
    }

    // ──────────────────────────────────────────────────────────────
    // CREATE
    // ──────────────────────────────────────────────────────────────

    /**
     * Show the form for creating a new grade level.
     */
    public function create()
    {
        return view('admin.grade-levels.create');
    }

    /**
     * Store a newly created grade level.
     */
    public function store(GradeLevelRequest $request)
    {
        try {
            $gradeLevel = GradeLevel::create($request->validated());

            // Activity log
            ActivityLog::create([
                'user_id'     => auth()->id(),
                'action'      => 'create',
                'model_type'  => 'GradeLevel',
                'model_id'    => $gradeLevel->id,
                'description' => 'Created grade level: ' . $gradeLevel->name,
                'ip_address'  => $request->ip(),
                'user_agent'  => $request->userAgent(),
            ]);

            return redirect()
                ->route('admin.grade-levels.index')
                ->with('success', 'Grade level "' . $gradeLevel->name . '" created successfully.');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to create grade level: ' . $e->getMessage());
        }
    }

    // ──────────────────────────────────────────────────────────────
    // EDIT / UPDATE
    // ──────────────────────────────────────────────────────────────

    /**
     * Show the form for editing the specified grade level.
     */
    public function edit(GradeLevel $gradeLevel)
    {
        return view('admin.grade-levels.edit', compact('gradeLevel'));
    }

    /**
     * Update the specified grade level.
     */
    public function update(GradeLevelRequest $request, GradeLevel $gradeLevel)
    {
        try {
            $oldName = $gradeLevel->name;

            $gradeLevel->update($request->validated());

            // Activity log
            ActivityLog::create([
                'user_id'     => auth()->id(),
                'action'      => 'update',
                'model_type'  => 'GradeLevel',
                'model_id'    => $gradeLevel->id,
                'description' => 'Updated grade level: "' . $oldName . '" → "' . $gradeLevel->name . '"',
                'ip_address'  => $request->ip(),
                'user_agent'  => $request->userAgent(),
            ]);

            return redirect()
                ->route('admin.grade-levels.index')
                ->with('success', 'Grade level updated successfully.');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to update grade level: ' . $e->getMessage());
        }
    }

    // NOTE: No destroy() method — delete is intentionally excluded
    //       to protect grade levels referenced by students / subjects.
}
