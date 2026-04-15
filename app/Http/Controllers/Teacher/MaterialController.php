<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\ClassModel;
use App\Models\Subject;
use App\Models\GradeLevel;
use App\Services\MaterialService;
use App\Http\Requests\StoreMaterialRequest;
use App\Http\Requests\UpdateMaterialRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $teacher = auth()->user()->teacher;

        $query = Material::where('teacher_id', $teacher->id)
            ->with(['class', 'class.gradeLevel', 'subject', 'gradeLevel', 'approvedBy']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('class', fn($q2) => $q2->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('subject', fn($q2) => $q2->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // ADDED: Filter by grade level
        if ($request->filled('grade_level_id')) {
            $query->where('grade_level_id', (int) $request->grade_level_id);
        }

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        $materials = $query->latest()->paginate(15)->withQueryString();

        $stats = [
            'total' => Material::where('teacher_id', $teacher->id)->count(),
            'published' => Material::where('teacher_id', $teacher->id)->published()->count(),
            'pending_approval' => Material::where('teacher_id', $teacher->id)->where('is_approved', false)->count(),
            'draft' => Material::where('teacher_id', $teacher->id)->where('status', 'draft')->count(),
        ];

        $classes = $teacher->classes()->active()->with(['subject', 'gradeLevel'])->orderBy('name')->get();

        // ADDED: Grade levels from teacher's active classes
        $gradeLevelIds = $classes->pluck('grade_level_id')->unique()->filter()->toArray();
        $gradeLevels = GradeLevel::whereIn('id', $gradeLevelIds)->ordered()->get();

        return view('teacher.materials.index', compact('materials', 'stats', 'classes', 'gradeLevels'));
    }

    public function create()
    {
        $teacher = auth()->user()->teacher;
        $classes = $teacher->classes()->active()->with(['subject', 'gradeLevel'])->orderBy('name')->get();

        // ADDED: Grade levels from teacher's active classes
        $gradeLevelIds = $classes->pluck('grade_level_id')->unique()->filter()->toArray();
        $gradeLevels = GradeLevel::whereIn('id', $gradeLevelIds)->ordered()->get();

        return view('teacher.materials.create', compact('classes', 'gradeLevels'));
    }

    public function store(StoreMaterialRequest $request)
    {
        try {
            $teacher = auth()->user()->teacher;
            $data = $request->validated();
            $data['teacher_id'] = $teacher->id;

            $material = $this->materialService->createMaterial($data);

            return redirect()->route('teacher.materials.show', $material)
                ->with('success', 'Material uploaded successfully. Waiting for admin approval.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()
                ->with('error', 'Failed to upload material: ' . $e->getMessage());
        }
    }

    public function show(Material $material)
    {
        if ($material->teacher_id !== auth()->user()->teacher->id) {
            abort(403, 'Unauthorized access');
        }

        $material->load(['class', 'class.gradeLevel', 'subject', 'gradeLevel', 'approvedBy', 'materialAccess', 'views']);

        $accessStats = [
            'total_students' => $material->materialAccess()->count(),
            'total_views' => $material->views()->count(),
            'unique_viewers' => $material->views()->distinct('student_id')->count(),
            'average_duration' => $material->views()->avg('duration_seconds'),
        ];

        $recentViewers = $material->views()->with('student.user')->latest('viewed_at')->take(10)->get();

        return view('teacher.materials.show', compact('material', 'accessStats', 'recentViewers'));
    }

    public function edit(Material $material)
    {
        if ($material->teacher_id !== auth()->user()->teacher->id) {
            abort(403, 'Unauthorized access');
        }

        $teacher = auth()->user()->teacher;
        $classes = $teacher->classes()->active()->with(['subject', 'gradeLevel'])->orderBy('name')->get();

        $gradeLevelIds = $classes->pluck('grade_level_id')->unique()->filter()->toArray();
        $gradeLevels = GradeLevel::whereIn('id', $gradeLevelIds)->ordered()->get();

        return view('teacher.materials.edit', compact('material', 'classes', 'gradeLevels'));
    }

    public function update(UpdateMaterialRequest $request, Material $material)
    {
        if ($material->teacher_id !== auth()->user()->teacher->id) {
            abort(403, 'Unauthorized access');
        }

        try {
            $this->materialService->updateMaterial($material, $request->validated());
            return redirect()->route('teacher.materials.show', $material)->with('success', 'Material updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Failed to update material: ' . $e->getMessage());
        }
    }

    public function destroy(Material $material)
    {
        if ($material->teacher_id !== auth()->user()->teacher->id) {
            abort(403, 'Unauthorized access');
        }

        try {
            if ($material->file_path && Storage::disk('public')->exists($material->file_path)) {
                Storage::disk('public')->delete($material->file_path);
            }
            $material->delete();
            return redirect()->route('teacher.materials.index')->with('success', 'Material deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete material: ' . $e->getMessage());
        }
    }

    // ==========================================
    // AJAX ENDPOINTS (Teacher-scoped)
    // ==========================================

    public function getSubjectsByGradeLevel(Request $request)
    {
        $request->validate(['grade_level_id' => 'required|exists:grade_levels,id']);

        $teacher = Auth::user()->teacher;
        $gradeLevelId = (int) $request->grade_level_id;

        $subjectIds = ClassModel::where('teacher_id', $teacher->id)
            ->where('status', 'active')
            ->where('grade_level_id', $gradeLevelId)
            ->pluck('subject_id')
            ->unique()
            ->toArray();

        $subjects = Subject::active()
            ->whereIn('id', $subjectIds)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return response()->json($subjects);
    }

    public function getClassesByFilters(Request $request)
    {
        $teacher = Auth::user()->teacher;

        $query = ClassModel::where('teacher_id', $teacher->id)
            ->where('status', 'active')
            ->with(['subject', 'gradeLevel']);

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
                'grade_level_name'=> $class->gradeLevel?->name ?? 'N/A',
                'subject_name'    => $class->subject?->name ?? 'N/A',
            ];
        });

        return response()->json($classes);
    }
}
