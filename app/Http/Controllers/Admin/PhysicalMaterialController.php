<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PhysicalMaterial;
use App\Models\PhysicalMaterialCollection;
use App\Models\Subject;
use App\Models\Student;
use App\Models\GradeLevel;
use App\Models\ClassModel;
use App\Services\PhysicalMaterialService;
use App\Http\Requests\StorePhysicalMaterialRequest;
use App\Http\Requests\UpdatePhysicalMaterialRequest;
use Illuminate\Http\Request;

class PhysicalMaterialController extends Controller
{
    protected $physicalMaterialService;

    public function __construct(PhysicalMaterialService $physicalMaterialService)
    {
        $this->physicalMaterialService = $physicalMaterialService;
    }

    /**
     * Display a listing of physical materials.
     */
    public function index(Request $request)
    {
        $query = PhysicalMaterial::with(['subject', 'gradeLevel', 'classModel']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('subject', fn($q2) => $q2->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('gradeLevel', fn($q2) => $q2->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('classModel', fn($q2) => $q2->where('name', 'like', "%{$search}%"));
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by grade level
        if ($request->filled('grade_level_id')) {
            $query->where('grade_level_id', (int) $request->grade_level_id);
        }

        // Filter by subject
        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        // Filter by class
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        // Filter by month
        if ($request->filled('month')) {
            $query->where('month', $request->month);
        }

        // Filter by year
        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }

        $physicalMaterials = $query->latest()->paginate(15)->withQueryString();

        // Statistics
        $stats = [
            'total' => PhysicalMaterial::count(),
            'available' => PhysicalMaterial::available()->count(),
            'out_of_stock' => PhysicalMaterial::where('status', 'out_of_stock')->count(),
            'total_quantity' => PhysicalMaterial::sum('quantity_available'),
        ];

        // Get filter options
        $gradeLevels = GradeLevel::ordered()->get();
        $subjects = Subject::active()->orderBy('name')->get();
        $classes = ClassModel::active()->with(['subject', 'gradeLevel'])->orderBy('name')->get();
        $months = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];
        $years = range(date('Y') - 1, date('Y') + 1);

        return view('admin.physical-materials.index', compact(
            'physicalMaterials', 'stats', 'gradeLevels', 'subjects', 'classes', 'months', 'years'
        ));
    }

    /**
     * Show the form for creating a new physical material.
     */
    public function create()
    {
        $gradeLevels = GradeLevel::ordered()->get();
        $subjects = Subject::active()->orderBy('name')->get();
        $classes = ClassModel::active()->with(['subject', 'gradeLevel'])->orderBy('name')->get();
        $months = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];

        return view('admin.physical-materials.create', compact('gradeLevels', 'subjects', 'classes', 'months'));
    }

    /**
     * Store a newly created physical material.
     */
    public function store(StorePhysicalMaterialRequest $request)
    {
        try {
            $physicalMaterial = PhysicalMaterial::create($request->validated());

            return redirect()
                ->route('admin.physical-materials.show', $physicalMaterial)
                ->with('success', 'Physical material created successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to create physical material: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified physical material.
     */
    public function show(PhysicalMaterial $physicalMaterial)
    {
        $physicalMaterial->load(['subject', 'gradeLevel', 'classModel', 'collections.student.user', 'collections.staff.user']);

        // Get collection statistics
        $stats = [
            'total_collections' => $physicalMaterial->collections()->count(),
            'collections_this_month' => $physicalMaterial->collections()
                ->whereMonth('collected_at', date('m'))
                ->whereYear('collected_at', date('Y'))
                ->count(),
            'pending_students' => Student::approved()->count() - $physicalMaterial->collections()->distinct('student_id')->count(),
        ];

        return view('admin.physical-materials.show', compact('physicalMaterial', 'stats'));
    }

    /**
     * Show the form for editing the specified physical material.
     */
    public function edit(PhysicalMaterial $physicalMaterial)
    {
        $physicalMaterial->load(['gradeLevel', 'subject', 'classModel']);

        $gradeLevels = GradeLevel::ordered()->get();
        $subjects = Subject::active()->orderBy('name')->get();
        $classes = ClassModel::active()->with(['subject', 'gradeLevel'])->orderBy('name')->get();
        $months = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];

        return view('admin.physical-materials.edit', compact('physicalMaterial', 'gradeLevels', 'subjects', 'classes', 'months'));
    }

    /**
     * Update the specified physical material.
     */
    public function update(UpdatePhysicalMaterialRequest $request, PhysicalMaterial $physicalMaterial)
    {
        try {
            $physicalMaterial->update($request->validated());

            return redirect()
                ->route('admin.physical-materials.show', $physicalMaterial)
                ->with('success', 'Physical material updated successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update physical material: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified physical material.
     */
    public function destroy(PhysicalMaterial $physicalMaterial)
    {
        try {
            // Check if has collections
            if ($physicalMaterial->collections()->exists()) {
                return redirect()
                    ->back()
                    ->with('error', 'Cannot delete material that has collection records.');
            }

            $physicalMaterial->delete();

            return redirect()
                ->route('admin.physical-materials.index')
                ->with('success', 'Physical material deleted successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to delete physical material: ' . $e->getMessage());
        }
    }

    /**
     * Show collection management page.
     */
    public function collections(PhysicalMaterial $physicalMaterial)
    {
        $physicalMaterial->load(['gradeLevel', 'classModel']);

        $collections = $physicalMaterial->collections()
            ->with(['student.user', 'staff.user'])
            ->latest('collected_at')
            ->paginate(15);

        // Get students who haven't collected
        $collectedStudentIds = $physicalMaterial->collections()->pluck('student_id');
        $pendingStudents = Student::approved()
            ->whereNotIn('id', $collectedStudentIds)
            ->with('user')
            ->get();

        return view('admin.physical-materials.collections', compact('physicalMaterial', 'collections', 'pendingStudents'));
    }

    /**
     * Record material collection.
     */
    public function recordCollection(Request $request, PhysicalMaterial $physicalMaterial)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'collected_by_name' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ]);

        try {
            // Check if already collected
            $exists = PhysicalMaterialCollection::where('physical_material_id', $physicalMaterial->id)
                ->where('student_id', $request->student_id)
                ->exists();

            if ($exists) {
                return redirect()
                    ->back()
                    ->with('error', 'Material already collected by this student.');
            }

            PhysicalMaterialCollection::create([
                'physical_material_id' => $physicalMaterial->id,
                'student_id' => $request->student_id,
                'collected_at' => now(),
                'collected_by_name' => $request->collected_by_name,
                'staff_id' => auth()->user()->staff->id ?? null,
                'notes' => $request->notes,
            ]);

            // Update quantity
            if ($physicalMaterial->quantity_available > 0) {
                $physicalMaterial->decrement('quantity_available');

                if ($physicalMaterial->quantity_available <= 0) {
                    $physicalMaterial->update(['status' => 'out_of_stock']);
                }
            }

            return redirect()
                ->back()
                ->with('success', 'Material collection recorded successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to record collection: ' . $e->getMessage());
        }
    }

    // ==========================================
    // AJAX ENDPOINTS (Cascading Dropdowns)
    // ==========================================

    /**
     * AJAX: Get subjects by grade level.
     *
     * Grade Level → Subject (subjects that have this grade_level_id in their JSON array).
     */
    public function getSubjectsByGradeLevel(Request $request)
    {
        $request->validate(['grade_level_id' => 'required|exists:grade_levels,id']);

        $subjects = Subject::active()
            ->whereJsonContains('grade_levels', (int) $request->grade_level_id)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return response()->json($subjects);
    }

    /**
     * AJAX: Get classes filtered by grade level and/or subject.
     *
     * Grade Level + Subject → Class list.
     */
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
