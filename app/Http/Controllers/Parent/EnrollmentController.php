<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Student;
use App\Services\EnrollmentService;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    protected $enrollmentService;

    public function __construct(EnrollmentService $enrollmentService)
    {
        $this->enrollmentService = $enrollmentService;
    }

    /**
     * Display enrollments for parent's children
     */
    public function index(Request $request)
    {
        $parent = auth()->user()->parent;

        if (!$parent) {
            return redirect()->route('parent.dashboard')
                ->with('error', 'Parent profile not found.');
        }

        // Get all children
        $children = $parent->students()
            ->with(['user', 'enrollments' => function ($query) {
                $query->with(['package', 'class.subject', 'class.schedules'])
                      ->latest();
            }])
            ->get();

        // Filter by child if selected
        $selectedChild = null;
        if ($request->filled('student_id')) {
            $selectedChild = $children->find($request->student_id);
            if ($selectedChild) {
                $children = collect([$selectedChild]);
            }
        }

        // Get enrollment statistics for all children
        $stats = [
            'total' => 0,
            'active' => 0,
            'expiring_soon' => 0,
        ];

        foreach ($children as $child) {
            $childStats = $this->enrollmentService->getEnrollmentStats($child);
            $stats['total'] += $childStats['total'];
            $stats['active'] += $childStats['active'];
            $stats['expiring_soon'] += $childStats['expiring_soon'];
        }

        return view('parent.enrollments.index', compact('children', 'stats', 'selectedChild'));
    }

    /**
     * Show enrollment details
     */
    public function show(Enrollment $enrollment)
    {
        $parent = auth()->user()->parent;

        // Verify this enrollment belongs to parent's child
        if (!$parent->students()->where('id', $enrollment->student_id)->exists()) {
            abort(403, 'Unauthorized access to this enrollment.');
        }

        $enrollment->load([
            'student.user',
            'package.subjects',
            'class.subject',
            'class.teacher.user',
            'class.schedules',
            'invoices' => function ($query) {
                $query->latest()->limit(5);
            },
        ]);

        // Get attendance summary
        $attendanceSummary = \App\Models\StudentAttendance::whereHas('classSession', function ($q) use ($enrollment) {
                $q->where('class_id', $enrollment->class_id);
            })
            ->where('student_id', $enrollment->student_id)
            ->selectRaw('
                COUNT(*) as total_sessions,
                SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as present_count,
                SUM(CASE WHEN status = "absent" THEN 1 ELSE 0 END) as absent_count,
                SUM(CASE WHEN status = "late" THEN 1 ELSE 0 END) as late_count
            ')
            ->first();

        return view('parent.enrollments.show', compact('enrollment', 'attendanceSummary'));
    }
}
