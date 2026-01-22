<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\PhysicalMaterial;
use App\Models\PhysicalMaterialCollection;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Http\Request;

class PhysicalMaterialController extends Controller
{
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
            'available' => PhysicalMaterial::where('status', 'available')->count(),
            'out_of_stock' => PhysicalMaterial::where('status', 'out_of_stock')->count(),
            'total_quantity' => PhysicalMaterial::sum('quantity_available'),
        ];

        // Get filter options
        $subjects = Subject::orderBy('name')->get();
        $months = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];
        $years = range(date('Y') - 1, date('Y') + 1);

        return view('staff.physical-materials.index', compact('physicalMaterials', 'stats', 'subjects', 'months', 'years'));
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
        $pendingStudents = Student::whereHas('user', function($q) {
                $q->where('status', 'active');
            })
            ->where('approval_status', 'approved')
            ->whereNotIn('id', $collectedStudentIds)
            ->with('user')
            ->get();

        return view('staff.physical-materials.collections', compact('physicalMaterial', 'collections', 'pendingStudents'));
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

            // Log activity
            activity()
                ->causedBy(auth()->user())
                ->performedOn($physicalMaterial)
                ->withProperties([
                    'student_id' => $request->student_id,
                    'collected_by' => $request->collected_by_name,
                ])
                ->log('Recorded material collection');

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
