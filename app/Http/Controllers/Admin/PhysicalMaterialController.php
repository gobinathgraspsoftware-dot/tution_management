<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PhysicalMaterial;
use App\Models\PhysicalMaterialCollection;
use App\Models\Subject;
use App\Models\Student;
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
        $query = PhysicalMaterial::with('subject');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('grade_level', 'like', "%{$search}%")
                  ->orWhereHas('subject', fn($q2) => $q2->where('name', 'like', "%{$search}%"));
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by subject
        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
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
        $subjects = Subject::active()->orderBy('name')->get();
        $months = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];
        $years = range(date('Y') - 1, date('Y') + 1);

        return view('admin.physical-materials.index', compact('physicalMaterials', 'stats', 'subjects', 'months', 'years'));
    }

    /**
     * Show the form for creating a new physical material.
     */
    public function create()
    {
        $subjects = Subject::active()->orderBy('name')->get();
        $months = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];

        return view('admin.physical-materials.create', compact('subjects', 'months'));
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
        $physicalMaterial->load(['subject', 'collections.student.user', 'collections.staff.user']);

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
        $subjects = Subject::active()->orderBy('name')->get();
        $months = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];

        return view('admin.physical-materials.edit', compact('physicalMaterial', 'subjects', 'months'));
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
}
