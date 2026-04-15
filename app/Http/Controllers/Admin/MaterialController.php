<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\ClassModel;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\GradeLevel;
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

    public function index(Request $request)
    {
        $query = Material::with([
            'class' => fn($q) => $q->withTrashed(),
            'class.subject' => fn($q) => $q->withTrashed(),
            'class.gradeLevel',
            'subject' => fn($q) => $q->withTrashed(),
            'gradeLevel',
            'teacher' => fn($q) => $q->withTrashed(),
            'teacher.user',
            'approvedBy',
        ]);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('class', fn($q2) => $q2->withTrashed()->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('subject', fn($q2) => $q2->withTrashed()->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('teacher', fn($q2) => $q2->withTrashed()->whereHas('user', fn($q3) => $q3->where('name', 'like', "%{$search}%")));
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('is_approved')) {
            $query->where('is_approved', $request->is_approved);
        }

        // ADDED: Filter by grade level
        if ($request->filled('grade_level_id')) {
            $query->where('grade_level_id', (int) $request->grade_level_id);
        }

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        if ($request->filled('teacher_id')) {
            $query->where('teacher_id', $request->teacher_id);
        }

        $materials = $query->latest()->paginate(15)->withQueryString();

        $stats = [
            'total' => Material::count(),
            'published' => Material::published()->count(),
            'pending_approval' => Material::where('is_approved', false)->count(),
            'draft' => Material::where('status', 'draft')->count(),
        ];

        $gradeLevels = GradeLevel::ordered()->get();
        $classes = ClassModel::active()->with(['subject', 'gradeLevel'])->orderBy('name')->get();
        $subjects = Subject::active()->orderBy('name')->get();
        $teachers = Teacher::active()->with('user')->get();

        return view('admin.materials.index', compact('materials', 'stats', 'gradeLevels', 'classes', 'subjects', 'teachers'));
    }

    public function create()
    {
        $gradeLevels = GradeLevel::ordered()->get();
        $classes = ClassModel::active()->with(['subject', 'gradeLevel'])->orderBy('name')->get();
        $subjects = Subject::active()->orderBy('name')->get();
        $teachers = Teacher::active()->with('user')->get();

        return view('admin.materials.create', compact('gradeLevels', 'classes', 'subjects', 'teachers'));
    }

    public function store(StoreMaterialRequest $request)
    {
        try {
            $material = $this->materialService->createMaterial($request->validated());
            return redirect()->route('admin.materials.show', $material)->with('success', 'Material created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Failed to create material: ' . $e->getMessage());
        }
    }

    public function show(Material $material)
    {
        $material->load([
            'class' => fn($q) => $q->withTrashed(),
            'class.subject' => fn($q) => $q->withTrashed(),
            'class.gradeLevel',
            'subject' => fn($q) => $q->withTrashed(),
            'gradeLevel',
            'teacher' => fn($q) => $q->withTrashed(),
            'teacher.user',
            'approvedBy',
            'materialAccess',
            'views',
        ]);

        $accessStats = [
            'total_students' => $material->materialAccess()->count(),
            'total_views' => $material->views()->count(),
            'unique_viewers' => $material->views()->distinct('student_id')->count(),
            'average_duration' => $material->views()->avg('duration_seconds'),
        ];

        $recentViewers = $material->views()->with('student.user')->latest('viewed_at')->take(10)->get();

        return view('admin.materials.show', compact('material', 'accessStats', 'recentViewers'));
    }

    public function edit(Material $material)
    {
        $material->load(['gradeLevel', 'class.gradeLevel']);
        $gradeLevels = GradeLevel::ordered()->get();
        $classes = ClassModel::active()->with(['subject', 'gradeLevel'])->orderBy('name')->get();
        $subjects = Subject::active()->orderBy('name')->get();
        $teachers = Teacher::active()->with('user')->get();

        return view('admin.materials.edit', compact('material', 'gradeLevels', 'classes', 'subjects', 'teachers'));
    }

    public function update(UpdateMaterialRequest $request, Material $material)
    {
        try {
            $this->materialService->updateMaterial($material, $request->validated());
            return redirect()->route('admin.materials.show', $material)->with('success', 'Material updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Failed to update material: ' . $e->getMessage());
        }
    }

    public function destroy(Material $material)
    {
        try {
            if ($material->file_path && Storage::disk('public')->exists($material->file_path)) {
                Storage::disk('public')->delete($material->file_path);
            }
            $material->delete();
            return redirect()->route('admin.materials.index')->with('success', 'Material deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete material: ' . $e->getMessage());
        }
    }

    public function approve(Material $material)
    {
        try {
            $material->update([
                'is_approved' => true,
                'approved_by' => auth()->id(),
                'status' => 'published',
            ]);
            return redirect()->back()->with('success', 'Material approved successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to approve material: ' . $e->getMessage());
        }
    }

    public function download(Material $material)
    {
        if (!$material->file_path || !Storage::disk('public')->exists($material->file_path)) {
            abort(404, 'File not found');
        }
        return Storage::disk('public')->download($material->file_path, $material->title . '.' . pathinfo($material->file_path, PATHINFO_EXTENSION));
    }

    // ==========================================
    // AJAX ENDPOINTS (Cascading Dropdowns)
    // ==========================================

    public function getSubjectsByGradeLevel(Request $request)
    {
        $request->validate(['grade_level_id' => 'required|exists:grade_levels,id']);

        $subjects = Subject::active()
            ->whereJsonContains('grade_levels', (int) $request->grade_level_id)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return response()->json($subjects);
    }

    public function getClassesByFilters(Request $request)
    {
        $query = ClassModel::active()->with(['subject', 'gradeLevel', 'teacher.user']);

        if ($request->filled('grade_level_id')) {
            $query->where('grade_level_id', (int) $request->grade_level_id);
        }
        if ($request->filled('subject_id')) {
            $query->where('subject_id', (int) $request->subject_id);
        }

        $classes = $query->orderBy('name')->get()->map(function ($class) {
            return [
                'id'              => $class->id,
                'name'            => $class->name,
                'subject_id'      => $class->subject_id,
                'grade_level_id'  => $class->grade_level_id,
                'teacher_name'    => $class->teacher?->user?->name ?? 'N/A',
                'grade_level_name'=> $class->gradeLevel?->name ?? 'N/A',
                'subject_name'    => $class->subject?->name ?? 'N/A',
            ];
        });

        return response()->json($classes);
    }
}
