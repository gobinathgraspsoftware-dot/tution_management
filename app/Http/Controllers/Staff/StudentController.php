<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\ClassModel;
use App\Models\Enrollment;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    /**
     * Display a listing of students.
     * Staff can view all approved students with limited information.
     */
    public function index(Request $request)
    {
        $query = Student::with(['user', 'parent.user', 'enrollments.class.subject']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', "%{$search}%")
                              ->orWhere('email', 'like', "%{$search}%")
                              ->orWhere('phone', 'like', "%{$search}%");
                })
                ->orWhere('student_id', 'like', "%{$search}%")
                ->orWhere('ic_number', 'like', "%{$search}%")
                ->orWhere('school_name', 'like', "%{$search}%");
            });
        }

        // Filter by approval status
        if ($request->filled('approval_status')) {
            $query->where('approval_status', $request->approval_status);
        }

        // Filter by user status (active/inactive)
        if ($request->filled('status')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('status', $request->status);
            });
        }

        // Filter by grade level
        if ($request->filled('grade_level')) {
            $query->where('grade_level', $request->grade_level);
        }

        // Filter by gender
        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        // Filter by class
        if ($request->filled('class_id')) {
            $query->whereHas('enrollments', function ($q) use ($request) {
                $q->where('class_id', $request->class_id)
                  ->where('status', 'active');
            });
        }

        // Filter by registration type
        if ($request->filled('registration_type')) {
            $query->where('registration_type', $request->registration_type);
        }

        $students = $query->latest()->paginate(15)->withQueryString();

        // Get unique grade levels for filter dropdown
        $gradeLevels = Student::distinct()->pluck('grade_level')->filter()->sort()->values();

        // Get active classes for filter dropdown
        $classes = ClassModel::active()
            ->with('subject')
            ->orderBy('name')
            ->get();

        // Statistics
        $stats = [
            'total' => Student::count(),
            'approved' => Student::approved()->count(),
            'pending' => Student::pending()->count(),
            'active' => Student::whereHas('user', fn($q) => $q->where('status', 'active'))->count(),
            'inactive' => Student::whereHas('user', fn($q) => $q->where('status', 'inactive'))->count(),
        ];

        return view('staff.students.index', compact('students', 'gradeLevels', 'classes', 'stats'));
    }

    /**
     * Display the specified student.
     * Staff can view student details with limited sensitive information.
     */
    public function show(Student $student)
    {
        $student->load([
            'user',
            'parent.user',
            'enrollments' => function ($q) {
                $q->with(['class.subject', 'class.teacher.user', 'package'])
                  ->latest();
            },
            'invoices' => function ($q) {
                $q->latest()->take(10);
            },
            'payments' => function ($q) {
                $q->where('status', 'completed')
                  ->latest()
                  ->take(10);
            },
            'attendance' => function ($q) {
                $q->with(['classSession.class'])
                  ->latest()
                  ->take(20);
            },
        ]);

        // Calculate statistics
        $stats = [
            'total_paid' => $student->payments()->where('status', 'completed')->sum('amount'),
            'pending_amount' => $student->invoices()
                ->whereIn('status', ['pending', 'partial'])
                ->sum('total_amount') - $student->invoices()
                ->whereIn('status', ['pending', 'partial'])
                ->sum('paid_amount'),
            'attendance_rate' => $this->calculateAttendanceRate($student),
            'active_enrollments' => $student->enrollments()->where('status', 'active')->count(),
            'total_enrollments' => $student->enrollments()->count(),
        ];

        // Get active enrollments with class schedules
        $activeEnrollments = $student->enrollments()
            ->where('status', 'active')
            ->with(['class.schedules', 'class.subject', 'class.teacher.user'])
            ->get();

        return view('staff.students.show', compact('student', 'stats', 'activeEnrollments'));
    }

    /**
     * Calculate attendance rate for a student.
     */
    protected function calculateAttendanceRate(Student $student): float
    {
        $totalClasses = $student->attendance()->count();
        
        if ($totalClasses === 0) {
            return 0;
        }

        $presentClasses = $student->attendance()
            ->where('status', 'present')
            ->count();

        return round(($presentClasses / $totalClasses) * 100, 2);
    }

    /**
     * AJAX endpoint to search students for Select2 dropdown.
     */
    public function searchStudents(Request $request)
    {
        $search = $request->get('q', '');
        $page = $request->get('page', 1);
        $perPage = 10;

        $query = Student::query()
            ->with(['user', 'parent.user'])
            ->whereHas('user', fn($q) => $q->where('status', 'active'))
            ->approved();

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', "%{$search}%")
                              ->orWhere('email', 'like', "%{$search}%")
                              ->orWhere('phone', 'like', "%{$search}%");
                })
                ->orWhere('student_id', 'like', "%{$search}%");
            });
        }

        $total = $query->count();
        $students = $query->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get()
            ->map(function ($student) {
                return [
                    'id' => $student->id,
                    'text' => $student->user->name . ' (' . $student->student_id . ')',
                    'student_id' => $student->student_id,
                    'name' => $student->user->name,
                    'email' => $student->user->email,
                    'phone' => $student->user->phone,
                    'parent_name' => $student->parent->user->name ?? 'N/A',
                ];
            });

        return response()->json([
            'results' => $students,
            'pagination' => [
                'more' => ($page * $perPage) < $total
            ]
        ]);
    }

    /**
     * Get student enrollments for AJAX request.
     */
    public function getStudentEnrollments(Student $student)
    {
        $enrollments = $student->enrollments()
            ->with(['class.subject', 'package'])
            ->where('status', 'active')
            ->get()
            ->map(function ($enrollment) {
                return [
                    'id' => $enrollment->id,
                    'class_name' => $enrollment->class->name ?? 'N/A',
                    'subject' => $enrollment->class->subject->name ?? 'N/A',
                    'package' => $enrollment->package->name ?? 'N/A',
                    'start_date' => $enrollment->start_date?->format('d M Y'),
                    'end_date' => $enrollment->end_date?->format('d M Y'),
                    'status' => $enrollment->status,
                ];
            });

        return response()->json($enrollments);
    }
}
