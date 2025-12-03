<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\Student;
use Illuminate\Http\Request;

class MaterialController extends Controller
{
    /**
     * Display a listing of materials for parent's children.
     */
    public function index(Request $request)
    {
        $parent = auth()->user()->parent;

        // Get parent's children
        $studentIds = $parent->students()->pluck('students.id');

        // Get enrolled class IDs for all children
        $enrolledClassIds = \App\Models\Enrollment::whereIn('student_id', $studentIds)
            ->pluck('class_id')
            ->unique();

        $query = Material::whereIn('class_id', $enrolledClassIds)
            ->where('status', 'published')
            ->where('is_approved', true)
            ->with(['class', 'subject', 'teacher.user']);

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

        // Filter by child
        if ($request->filled('student_id')) {
            $childEnrollments = \App\Models\Enrollment::where('student_id', $request->student_id)
                ->pluck('class_id');
            $query->whereIn('class_id', $childEnrollments);
        }

        // Filter by class
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        $materials = $query->latest('publish_date')->paginate(15)->withQueryString();

        // Get children for filter
        $children = Student::whereIn('id', $studentIds)->with('user')->get();

        // Get classes for filter
        $classes = \App\Models\ClassModel::whereIn('id', $enrolledClassIds)
            ->with('subject')
            ->orderBy('name')
            ->get();

        return view('parent.materials.index', compact('materials', 'children', 'classes'));
    }
}
