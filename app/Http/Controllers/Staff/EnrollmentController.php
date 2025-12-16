<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\EnrollmentRequest;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\Package;
use App\Models\ClassModel;
use App\Models\ActivityLog;
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
     * Display all enrollments
     */
    public function index(Request $request)
    {
        $query = Enrollment::with(['student.user', 'package', 'class.subject']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('student.user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by class
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        $enrollments = $query->latest()->paginate(20);

        // Get filter options
        $classes = ClassModel::active()->with('subject')->orderBy('name')->get();

        // Get statistics
        $stats = $this->enrollmentService->getEnrollmentStats();

        return view('staff.enrollments.index', compact('enrollments', 'classes', 'stats'));
    }

    /**
     * Show enrollment form
     */
    public function create()
    {
        $students = Student::approved()
            ->with('user')
            ->get()
            ->sortBy('user.name');

        $packages = Package::active()->with('subjects')->get();
        $classes = ClassModel::active()->with(['subject', 'teacher.user'])->get();

        return view('staff.enrollments.create', compact('students', 'packages', 'classes'));
    }

    /**
     * Store new enrollment
     */
    public function store(EnrollmentRequest $request)
    {
        try {
            $data = $request->validated();

            // Check if enrolling in package
            if ($request->filled('package_id')) {
                $student = Student::findOrFail($data['student_id']);
                $package = Package::findOrFail($data['package_id']);

                $enrollments = $this->enrollmentService->enrollInPackage($student, $package, $data);

                ActivityLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'create',
                    'model_type' => 'Enrollment',
                    'description' => "Enrolled student {$student->user->name} in package {$package->name}",
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);

                return redirect()->route('staff.enrollments.index')
                    ->with('success', "Student successfully enrolled in {$package->name} package!");
            } else {
                // Single class enrollment
                $enrollment = $this->enrollmentService->createEnrollment($data);

                ActivityLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'create',
                    'model_type' => 'Enrollment',
                    'model_id' => $enrollment->id,
                    'description' => "Created enrollment for {$enrollment->student->user->name}",
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);

                return redirect()->route('staff.enrollments.index')
                    ->with('success', 'Enrollment created successfully!');
            }
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Failed to create enrollment: ' . $e->getMessage());
        }
    }

    /**
     * Display enrollment details
     */
    public function show(Enrollment $enrollment)
    {
        $enrollment->load([
            'student.user',
            'package.subjects',
            'class.subject',
            'class.teacher.user',
            'invoices',
        ]);

        return view('staff.enrollments.show', compact('enrollment'));
    }
}
