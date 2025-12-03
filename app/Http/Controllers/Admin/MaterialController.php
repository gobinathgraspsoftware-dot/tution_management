<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\ClassModel;
use App\Models\Subject;
use App\Models\Teacher;
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
     * Display a listing of materials.
     */
    public function index(Request $request)
    {
        $query = Material::with(['class', 'subject', 'teacher.user', 'approvedBy']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('class', fn($q2) => $q2->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('subject', fn($q2) => $q2->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('teacher.user', fn($q2) => $q2->where('name', 'like', "%{$search}%"));
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

        // Filter by approval
        if ($request->filled('is_approved')) {
            $query->where('is_approved', $request->is_approved);
        }

        // Filter by class
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        // Filter by subject
        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        // Filter by teacher
        if ($request->filled('teacher_id')) {
            $query->where('teacher_id', $request->teacher_id);
        }

        $materials = $query->latest()->paginate(15)->withQueryString();

        // Statistics
        $stats = [
            'total' => Material::count(),
            'published' => Material::published()->count(),
            'pending_approval' => Material::where('is_approved', false)->count(),
            'draft' => Material::where('status', 'draft')->count(),
        ];

        // Get filter options
        $classes = ClassModel::active()->with('subject')->orderBy('name')->get();
        $subjects = Subject::active()->orderBy('name')->get();
        $teachers = Teacher::active()->with('user')->get();

        return view('admin.materials.index', compact('materials', 'stats', 'classes', 'subjects', 'teachers'));
    }

    /**
     * Show the form for creating a new material.
     */
    public function create()
    {
        $classes = ClassModel::active()->with('subject')->orderBy('name')->get();
        $subjects = Subject::active()->orderBy('name')->get();
        $teachers = Teacher::active()->with('user')->get();

        return view('admin.materials.create', compact('classes', 'subjects', 'teachers'));
    }

    /**
     * Store a newly created material.
     */
    public function store(StoreMaterialRequest $request)
    {
        try {
            $material = $this->materialService->createMaterial($request->validated());

            return redirect()
                ->route('admin.materials.show', $material)
                ->with('success', 'Material created successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to create material: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified material.
     */
    public function show(Material $material)
    {
        $material->load(['class', 'subject', 'teacher.user', 'approvedBy', 'materialAccess', 'views']);

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

        return view('admin.materials.show', compact('material', 'accessStats', 'recentViewers'));
    }

    /**
     * Show the form for editing the specified material.
     */
    public function edit(Material $material)
    {
        $classes = ClassModel::active()->with('subject')->orderBy('name')->get();
        $subjects = Subject::active()->orderBy('name')->get();
        $teachers = Teacher::active()->with('user')->get();

        return view('admin.materials.edit', compact('material', 'classes', 'subjects', 'teachers'));
    }

    /**
     * Update the specified material.
     */
    public function update(UpdateMaterialRequest $request, Material $material)
    {
        try {
            $this->materialService->updateMaterial($material, $request->validated());

            return redirect()
                ->route('admin.materials.show', $material)
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
        try {
            // Delete file from storage
            if ($material->file_path && Storage::disk('public')->exists($material->file_path)) {
                Storage::disk('public')->delete($material->file_path);
            }

            $material->delete();

            return redirect()
                ->route('admin.materials.index')
                ->with('success', 'Material deleted successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to delete material: ' . $e->getMessage());
        }
    }

    /**
     * Approve material.
     */
    public function approve(Material $material)
    {
        try {
            $material->update([
                'is_approved' => true,
                'approved_by' => auth()->id(),
                'status' => 'published',
            ]);

            return redirect()
                ->back()
                ->with('success', 'Material approved successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to approve material: ' . $e->getMessage());
        }
    }

    /**
     * Download material file.
     */
    public function download(Material $material)
    {
        if (!$material->file_path || !Storage::disk('public')->exists($material->file_path)) {
            abort(404, 'File not found');
        }

        return Storage::disk('public')->download($material->file_path, $material->title . '.' . pathinfo($material->file_path, PATHINFO_EXTENSION));
    }
}
