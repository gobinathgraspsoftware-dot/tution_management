<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\StudentReview;
use App\Models\ClassModel;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    /**
     * Display student's reviews.
     */
    public function index(Request $request)
    {
        $student = auth()->user()->student;

        // Get student's reviews
        $query = StudentReview::where('student_id', $student->id)
            ->with(['class.subject', 'teacher.user']);

        // Filter by approval status
        $filter = $request->get('filter', 'all'); // all, approved, pending
        if ($filter === 'approved') {
            $query->where('is_approved', true);
        } elseif ($filter === 'pending') {
            $query->where('is_approved', false);
        }

        // Filter by rating
        if ($request->filled('rating')) {
            $query->where('rating', $request->rating);
        }

        // Filter by class
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        $reviews = $query->latest()->paginate(15)->withQueryString();

        // Statistics
        $stats = [
            'total' => StudentReview::where('student_id', $student->id)->count(),
            'approved' => StudentReview::where('student_id', $student->id)->where('is_approved', true)->count(),
            'pending' => StudentReview::where('student_id', $student->id)->where('is_approved', false)->count(),
            'average_rating' => round(StudentReview::where('student_id', $student->id)->avg('rating'), 1),
        ];

        // Get student's enrolled classes for filters
        $enrolledClasses = $student->enrollments()
            ->with(['class.subject'])
            ->where('status', 'active')
            ->get()
            ->pluck('class')
            ->filter();

        return view('student.reviews.index', compact('reviews', 'stats', 'enrolledClasses', 'filter'));
    }

    /**
     * Show create review form.
     */
    public function create(Request $request)
    {
        $student = auth()->user()->student;

        // Get student's enrolled classes
        $enrolledClasses = $student->enrollments()
            ->with(['class.subject', 'class.teacher.user'])
            ->where('status', 'active')
            ->get()
            ->pluck('class')
            ->filter();

        if ($enrolledClasses->isEmpty()) {
            return redirect()->route('student.reviews.index')
                ->with('error', 'You need to be enrolled in at least one class to write a review.');
        }

        // Pre-select class and teacher if provided in query params
        $selectedClassId = $request->get('class_id');
        $selectedTeacherId = $request->get('teacher_id');

        return view('student.reviews.create', compact('enrolledClasses', 'selectedClassId', 'selectedTeacherId'));
    }

    /**
     * Store new review.
     */
    public function store(Request $request)
    {
        $student = auth()->user()->student;

        $validated = $request->validate([
            'class_id' => 'required|exists:classes,id',
            'teacher_id' => 'required|exists:teachers,id',
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'required|string|min:10|max:1000',
        ]);

        // Verify student is enrolled in this class
        $enrollment = $student->enrollments()
            ->where('class_id', $validated['class_id'])
            ->where('status', 'active')
            ->first();

        if (!$enrollment) {
            return back()->with('error', 'You can only review classes you are enrolled in.');
        }

        // Check if student already reviewed this class/teacher combination
        $existingReview = StudentReview::where('student_id', $student->id)
            ->where('class_id', $validated['class_id'])
            ->where('teacher_id', $validated['teacher_id'])
            ->first();

        if ($existingReview) {
            return back()->with('error', 'You have already reviewed this class and teacher. You can edit your existing review.');
        }

        // Create review (pending approval)
        $review = StudentReview::create([
            'student_id' => $student->id,
            'class_id' => $validated['class_id'],
            'teacher_id' => $validated['teacher_id'],
            'rating' => $validated['rating'],
            'review' => $validated['review'],
            'is_approved' => false, // Pending approval by admin
        ]);

        return redirect()->route('student.reviews.index')
            ->with('success', 'Review submitted successfully! It will be visible after admin approval.');
    }

    /**
     * Display review details.
     */
    public function show(StudentReview $review)
    {
        $student = auth()->user()->student;

        // Check if review belongs to this student
        if ($review->student_id !== $student->id) {
            abort(403, 'You do not have access to this review.');
        }

        $review->load(['class.subject', 'teacher.user']);

        return view('student.reviews.show', compact('review'));
    }

    /**
     * Show edit review form.
     */
    public function edit(StudentReview $review)
    {
        $student = auth()->user()->student;

        // Check if review belongs to this student
        if ($review->student_id !== $student->id) {
            abort(403, 'You do not have access to this review.');
        }

        // Load class and teacher info
        $review->load(['class.subject', 'class.teacher.user', 'teacher.user']);

        return view('student.reviews.edit', compact('review'));
    }

    /**
     * Update review.
     */
    public function update(Request $request, StudentReview $review)
    {
        $student = auth()->user()->student;

        // Check if review belongs to this student
        if ($review->student_id !== $student->id) {
            abort(403, 'You do not have access to this review.');
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'required|string|min:10|max:1000',
        ]);

        // Update review and set to pending approval again
        $review->update([
            'rating' => $validated['rating'],
            'review' => $validated['review'],
            'is_approved' => false, // Reset approval status after edit
        ]);

        return redirect()->route('student.reviews.index')
            ->with('success', 'Review updated successfully! It will be re-approved by admin.');
    }

    /**
     * Delete review.
     */
    public function destroy(StudentReview $review)
    {
        $student = auth()->user()->student;

        // Check if review belongs to this student
        if ($review->student_id !== $student->id) {
            abort(403, 'You do not have access to this review.');
        }

        $review->delete();

        return redirect()->route('student.reviews.index')
            ->with('success', 'Review deleted successfully.');
    }

    /**
     * Get teacher info for selected class (AJAX).
     */
    public function getClassTeacher(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
        ]);

        $class = ClassModel::with(['teacher.user'])->find($request->class_id);

        if (!$class || !$class->teacher) {
            return response()->json([
                'success' => false,
                'message' => 'Teacher not found for this class.',
            ]);
        }

        return response()->json([
            'success' => true,
            'teacher' => [
                'id' => $class->teacher->id,
                'name' => $class->teacher->user->name,
            ],
        ]);
    }
}
