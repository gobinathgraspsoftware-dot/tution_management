<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\ClassModel;
use App\Models\Subject;
use App\Services\MaterialService;
use App\Http\Requests\StoreMaterialRequest;
use App\Http\Requests\UpdateMaterialRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MaterialController extends Controller
{
    protected $materialService;

    public function __construct(MaterialService $materialService)
    {
        $this->materialService = $materialService;
    }

    /**
     * Display a listing of teacher's materials.
     */
    public function index(Request $request)
    {
        $teacher = auth()->user()->teacher;

        $query = Material::where('teacher_id', $teacher->id)
            ->with(['class', 'subject', 'approvedBy']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('class', fn($q2) => $q2->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('subject', fn($q2) => $q2->where('name', 'like', "%{$search}%"));
            });
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by class
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        $materials = $query->latest()->paginate(15)->withQueryString();

        // Statistics
        $stats = [
            'total' => Material::where('teacher_id', $teacher->id)->count(),
            'published' => Material::where('teacher_id', $teacher->id)->published()->count(),
            'pending_approval' => Material::where('teacher_id', $teacher->id)->where('is_approved', false)->count(),
            'draft' => Material::where('teacher_id', $teacher->id)->where('status', 'draft')->count(),
        ];

        // Get teacher's classes
        $classes = $teacher->classes()->active()->with('subject')->orderBy('name')->get();

        return view('teacher.materials.index', compact('materials', 'stats', 'classes'));
    }

    /**
     * Show the form for creating a new material.
     */
    public function create()
    {
        $teacher = auth()->user()->teacher;
        $classes = $teacher->classes()->active()->with('subject')->orderBy('name')->get();

        return view('teacher.materials.create', compact('classes'));
    }

    /**
     * Store a newly created material.
     */
    public function store(StoreMaterialRequest $request)
    {
        try {
            $teacher = auth()->user()->teacher;

            $data = $request->validated();
            $data['teacher_id'] = $teacher->id;

            $material = $this->materialService->createMaterial($data);

            return redirect()
                ->route('teacher.materials.show', $material)
                ->with('success', 'Material uploaded successfully. Waiting for admin approval.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to upload material: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified material.
     */
    public function show(Material $material)
    {
        // Ensure teacher can only view their own materials
        if ($material->teacher_id !== auth()->user()->teacher->id) {
            abort(403, 'Unauthorized access');
        }

        $material->load(['class', 'subject', 'approvedBy', 'materialAccess', 'views']);

        // Get access statistics
        $accessStats = [
            'total_students' => $material->materialAccess()->count(),
            'total_views' => $material->views()->count(),
            'unique_viewers' => $material->views()->distinct('student_id')->count(),
            'average_duration' => $material->views()->avg('duration_seconds'),
        ];

        // Get recent viewers
        $recentViewers = $material->views()
            ->with('student.user')
            ->latest('viewed_at')
            ->take(10)
            ->get();

        return view('teacher.materials.show', compact('material', 'accessStats', 'recentViewers'));
    }

    /**
     * Show the form for editing the specified material.
     */
    public function edit(Material $material)
    {
        // Ensure teacher can only edit their own materials
        if ($material->teacher_id !== auth()->user()->teacher->id) {
            abort(403, 'Unauthorized access');
        }

        $teacher = auth()->user()->teacher;
        $classes = $teacher->classes()->active()->with('subject')->orderBy('name')->get();

        return view('teacher.materials.edit', compact('material', 'classes'));
    }

    /**
     * Update the specified material.
     */
    public function update(UpdateMaterialRequest $request, Material $material)
    {
        // Ensure teacher can only update their own materials
        if ($material->teacher_id !== auth()->user()->teacher->id) {
            abort(403, 'Unauthorized access');
        }

        try {
            $this->materialService->updateMaterial($material, $request->validated());

            return redirect()
                ->route('teacher.materials.show', $material)
                ->with('success', 'Material updated successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update material: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified material.
     */
    public function destroy(Material $material)
    {
        // Ensure teacher can only delete their own materials
        if ($material->teacher_id !== auth()->user()->teacher->id) {
            abort(403, 'Unauthorized access');
        }

        try {
            // Delete file from storage
            if ($material->file_path && Storage::disk('public')->exists($material->file_path)) {
                Storage::disk('public')->delete($material->file_path);
            }

            $material->delete();

            return redirect()
                ->route('teacher.materials.index')
                ->with('success', 'Material deleted successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to delete material: ' . $e->getMessage());
        }
    }
}
