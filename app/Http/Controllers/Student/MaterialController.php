<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\MaterialView;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MaterialController extends Controller
{
    /**
     * Display a listing of materials available to the student.
     */
    public function index(Request $request)
    {
        $student = auth()->user()->student;

        // Get student's enrolled classes
        $enrolledClassIds = $student->enrollments()->pluck('class_id');

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

        // Filter by class
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        // Filter by subject
        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        $materials = $query->latest('publish_date')->paginate(15)->withQueryString();

        // Get student's classes for filter
        $classes = \App\Models\ClassModel::whereIn('id', $enrolledClassIds)
            ->with('subject')
            ->orderBy('name')
            ->get();

        return view('student.materials.index', compact('materials', 'classes'));
    }

    /**
     * View material (non-downloadable).
     */
    public function view(Material $material)
    {
        $student = auth()->user()->student;

        // Check if student has access to this material
        $enrolledClassIds = $student->enrollments()->pluck('class_id');
        if (!$enrolledClassIds->contains($material->class_id)) {
            abort(403, 'You do not have access to this material.');
        }

        if (!$material->file_path || !Storage::disk('public')->exists($material->file_path)) {
            abort(404, 'File not found');
        }

        // Record view
        MaterialView::create([
            'material_id' => $material->id,
            'student_id' => $student->id,
            'viewed_at' => now(),
        ]);

        // Get file path
        $filePath = Storage::disk('public')->path($material->file_path);
        $fileContent = file_get_contents($filePath);
        $base64 = base64_encode($fileContent);

        return view('student.materials.viewer', compact('material', 'base64'));
    }
}
