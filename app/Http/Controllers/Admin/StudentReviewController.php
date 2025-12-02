<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StudentReviewRequest;
use App\Models\StudentReview;
use App\Models\Student;
use App\Models\ClassModel;
use App\Models\Teacher;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class StudentReviewController extends Controller
{
    /**
     * Display reviews listing.
     */
    public function index(Request $request)
    {
        $query = StudentReview::with(['student.user', 'class.subject', 'teacher.user']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('review', 'like', "%{$search}%")
                  ->orWhereHas('student.user', fn($q2) => $q2->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('class', fn($q2) => $q2->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('teacher.user', fn($q2) => $q2->where('name', 'like', "%{$search}%"));
            });
        }

        // Filter by approval status
        if ($request->filled('is_approved')) {
            $query->where('is_approved', $request->is_approved === 'yes');
        }

        // Filter by rating
        if ($request->filled('rating')) {
            $query->where('rating', $request->rating);
        }

        // Filter by class
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        // Filter by teacher
        if ($request->filled('teacher_id')) {
            $query->where('teacher_id', $request->teacher_id);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $reviews = $query->latest()->paginate(15)->withQueryString();

        // Statistics
        $stats = [
            'total' => StudentReview::count(),
            'approved' => StudentReview::where('is_approved', true)->count(),
            'pending' => StudentReview::where('is_approved', false)->count(),
            'average_rating' => round(StudentReview::avg('rating'), 1),
            'five_star' => StudentReview::where('rating', 5)->count(),
            'four_star' => StudentReview::where('rating', 4)->count(),
            'three_star' => StudentReview::where('rating', 3)->count(),
            'two_star' => StudentReview::where('rating', 2)->count(),
            'one_star' => StudentReview::where('rating', 1)->count(),
        ];

        // Get classes and teachers for filters
        $classes = ClassModel::with('subject')->get();
        $teachers = Teacher::with('user')->whereHas('user', fn($q) => $q->where('status', 'active'))->get();

        return view('admin.reviews.index', compact('reviews', 'stats', 'classes', 'teachers'));
    }

    /**
     * Show create review form.
     */
    public function create()
    {
        $students = Student::approved()
            ->with('user')
            ->whereHas('user', fn($q) => $q->where('status', 'active'))
            ->get();

        $classes = ClassModel::active()->with('subject')->get();
        $teachers = Teacher::with('user')->whereHas('user', fn($q) => $q->where('status', 'active'))->get();

        return view('admin.reviews.create', compact('students', 'classes', 'teachers'));
    }

    /**
     * Store new review.
     */
    public function store(StudentReviewRequest $request)
    {
        $data = $request->validated();
        $data['is_approved'] = $request->has('is_approved');

        $review = StudentReview::create($data);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'create',
            'model_type' => 'StudentReview',
            'model_id' => $review->id,
            'description' => 'Created student review',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()->route('admin.reviews.index')
            ->with('success', 'Review created successfully.');
    }

    /**
     * Display review details.
     */
    public function show(StudentReview $review)
    {
        $review->load([
            'student.user',
            'student.parent.user',
            'class.subject',
            'class.teacher.user',
            'teacher.user',
        ]);

        // Get other reviews from same student
        $otherReviews = StudentReview::where('student_id', $review->student_id)
            ->where('id', '!=', $review->id)
            ->with(['class', 'teacher.user'])
            ->latest()
            ->take(5)
            ->get();

        return view('admin.reviews.show', compact('review', 'otherReviews'));
    }

    /**
     * Show edit review form.
     */
    public function edit(StudentReview $review)
    {
        $students = Student::approved()
            ->with('user')
            ->whereHas('user', fn($q) => $q->where('status', 'active'))
            ->get();

        $classes = ClassModel::with('subject')->get();
        $teachers = Teacher::with('user')->get();

        return view('admin.reviews.edit', compact('review', 'students', 'classes', 'teachers'));
    }

    /**
     * Update review.
     */
    public function update(StudentReviewRequest $request, StudentReview $review)
    {
        $data = $request->validated();
        $data['is_approved'] = $request->has('is_approved');

        $review->update($data);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'update',
            'model_type' => 'StudentReview',
            'model_id' => $review->id,
            'description' => 'Updated student review',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()->route('admin.reviews.index')
            ->with('success', 'Review updated successfully.');
    }

    /**
     * Approve a review.
     */
    public function approve(StudentReview $review)
    {
        $review->update(['is_approved' => true]);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'update',
            'model_type' => 'StudentReview',
            'model_id' => $review->id,
            'description' => 'Approved student review',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return back()->with('success', 'Review approved successfully.');
    }

    /**
     * Reject/unapprove a review.
     */
    public function reject(StudentReview $review)
    {
        $review->update(['is_approved' => false]);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'update',
            'model_type' => 'StudentReview',
            'model_id' => $review->id,
            'description' => 'Rejected student review',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return back()->with('success', 'Review rejected successfully.');
    }

    /**
     * Bulk approve reviews.
     */
    public function bulkApprove(Request $request)
    {
        $request->validate([
            'review_ids' => 'required|array',
            'review_ids.*' => 'exists:student_reviews,id',
        ]);

        StudentReview::whereIn('id', $request->review_ids)->update(['is_approved' => true]);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'update',
            'model_type' => 'StudentReview',
            'model_id' => 0,
            'description' => 'Bulk approved ' . count($request->review_ids) . ' reviews',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return back()->with('success', count($request->review_ids) . ' reviews approved successfully.');
    }

    /**
     * Delete review.
     */
    public function destroy(StudentReview $review)
    {
        $review->delete();

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'delete',
            'model_type' => 'StudentReview',
            'model_id' => $review->id,
            'description' => 'Deleted student review',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()->route('admin.reviews.index')
            ->with('success', 'Review deleted successfully.');
    }

    /**
     * Export reviews to CSV.
     */
    public function export(Request $request)
    {
        $reviews = StudentReview::with(['student.user', 'class', 'teacher.user'])
            ->when($request->is_approved, fn($q, $v) => $q->where('is_approved', $v === 'yes'))
            ->get();

        $filename = 'reviews_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($reviews) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Student', 'Class', 'Teacher', 'Rating', 'Review', 'Approved', 'Date']);

            foreach ($reviews as $r) {
                fputcsv($file, [
                    $r->id,
                    $r->student->user->name ?? 'N/A',
                    $r->class->name ?? 'N/A',
                    $r->teacher->user->name ?? 'N/A',
                    $r->rating,
                    $r->review ?? '',
                    $r->is_approved ? 'Yes' : 'No',
                    $r->created_at->format('Y-m-d H:i'),
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
