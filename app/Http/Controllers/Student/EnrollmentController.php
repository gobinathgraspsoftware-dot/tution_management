<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\EnrollmentRequest;
use App\Models\Enrollment;
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
     * Display student's enrollments
     */
    public function myEnrollments()
    {
        $student = auth()->user()->student;

        if (!$student) {
            return redirect()->route('student.dashboard')
                ->with('error', 'Student profile not found.');
        }

        $enrollments = $student->enrollments()
            ->with(['package', 'class.subject', 'class.teacher.user', 'class.schedules'])
            ->latest()
            ->get();

        $stats = $this->enrollmentService->getEnrollmentStats($student);

        return view('student.enrollments.my-enrollments', compact('enrollments', 'stats'));
    }

    /**
     * Browse available classes
     */
    public function browseClasses(Request $request)
    {
        $student = auth()->user()->student;

        if (!$student) {
            return redirect()->route('student.dashboard')
                ->with('error', 'Student profile not found.');
        }

        // Get available classes
        $classes = $this->enrollmentService->getAvailableClasses($student);

        // Apply filters
        if ($request->filled('subject_id')) {
            $classes = $classes->where('subject_id', $request->subject_id);
        }

        if ($request->filled('day')) {
            $classes = $classes->filter(function ($class) use ($request) {
                return $class->schedules->contains('day_of_week', $request->day);
            });
        }

        if ($request->filled('teacher_id')) {
            $classes = $classes->where('teacher_id', $request->teacher_id);
        }

        // Get subjects for filter
        $subjects = \App\Models\Subject::active()->orderBy('name')->get();

        // Get teachers for filter
        $teachers = \App\Models\Teacher::active()
            ->with('user')
            ->get()
            ->sortBy('user.name');

        return view('student.enrollments.browse-classes', compact('classes', 'subjects', 'teachers'));
    }

    /**
     * Browse available packages
     */
    public function browsePackages(Request $request)
    {
        $student = auth()->user()->student;

        if (!$student) {
            return redirect()->route('student.dashboard')
                ->with('error', 'Student profile not found.');
        }

        // Get available packages
        $packages = $this->enrollmentService->getAvailablePackages($student);

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $packages = $packages->filter(function ($package) use ($search) {
                return stripos($package->name, $search) !== false ||
                       stripos($package->description, $search) !== false;
            });
        }

        return view('student.enrollments.browse-packages', compact('packages'));
    }

    /**
     * Show enrollment form for single class
     */
    public function enrollClass(ClassModel $class)
    {
        $student = auth()->user()->student;

        if (!$student) {
            return redirect()->route('student.dashboard')
                ->with('error', 'Student profile not found.');
        }

        // Check if can enroll
        $canEnroll = $this->enrollmentService->canEnroll($student, $class);

        if (!$canEnroll['can_enroll']) {
            return redirect()->route('student.enrollments.browse-classes')
                ->with('error', implode(' ', $canEnroll['errors']));
        }

        $class->load(['subject', 'teacher.user', 'schedules']);

        return view('student.enrollments.enroll-class', compact('class', 'student'));
    }

    /**
     * Process single class enrollment
     */
    public function storeClassEnrollment(Request $request, ClassModel $class)
    {
        $student = auth()->user()->student;

        if (!$student) {
            return redirect()->route('student.dashboard')
                ->with('error', 'Student profile not found.');
        }

        $validated = $request->validate([
            'start_date' => 'required|date|after_or_equal:today',
            'payment_cycle_day' => 'required|integer|min:1|max:28',
        ]);

        try {
            // Check if can enroll
            $canEnroll = $this->enrollmentService->canEnroll($student, $class);

            if (!$canEnroll['can_enroll']) {
                return back()->withInput()
                    ->with('error', implode(' ', $canEnroll['errors']));
            }

            $data = [
                'student_id' => $student->id,
                'class_id' => $class->id,
                'start_date' => $validated['start_date'],
                'payment_cycle_day' => $validated['payment_cycle_day'],
                'monthly_fee' => $class->monthly_fee,
                'status' => 'active',
            ];

            $enrollment = $this->enrollmentService->createEnrollment($data);

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'create',
                'model_type' => 'Enrollment',
                'model_id' => $enrollment->id,
                'description' => "Student self-enrolled in {$class->name}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return redirect()->route('student.enrollments.my-enrollments')
                ->with('success', "You have successfully enrolled in {$class->name}!");
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Failed to enroll: ' . $e->getMessage());
        }
    }

    /**
     * Show enrollment form for package
     */
    public function enrollPackage(Package $package)
    {
        $student = auth()->user()->student;

        if (!$student) {
            return redirect()->route('student.dashboard')
                ->with('error', 'Student profile not found.');
        }

        $package->load(['subjects.classes' => function ($query) {
            $query->active()->with(['subject', 'teacher.user', 'schedules']);
        }, 'discountRule']);

        // Get list of classes in the package
        $classes = $package->subjects->flatMap(function ($subject) {
            return $subject->classes()->active()->get();
        });

        if ($classes->isEmpty()) {
            return redirect()->route('student.enrollments.browse-packages')
                ->with('error', 'This package has no active classes at the moment.');
        }

        return view('student.enrollments.enroll-package', compact('package', 'classes', 'student'));
    }

    /**
     * Process package enrollment
     */
    public function storePackageEnrollment(Request $request, Package $package)
    {
        $student = auth()->user()->student;

        if (!$student) {
            return redirect()->route('student.dashboard')
                ->with('error', 'Student profile not found.');
        }

        $validated = $request->validate([
            'start_date' => 'required|date|after_or_equal:today',
            'payment_cycle_day' => 'required|integer|min:1|max:28',
        ]);

        try {
            $data = [
                'student_id' => $student->id,
                'start_date' => $validated['start_date'],
                'payment_cycle_day' => $validated['payment_cycle_day'],
            ];

            $enrollments = $this->enrollmentService->enrollInPackage($student, $package, $data);

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'create',
                'model_type' => 'Enrollment',
                'description' => "Student self-enrolled in package {$package->name}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return redirect()->route('student.enrollments.my-enrollments')
                ->with('success', "You have successfully enrolled in {$package->name} package with " . count($enrollments) . " classes!");
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Failed to enroll: ' . $e->getMessage());
        }
    }

    /**
     * Show enrollment details
     */
    public function show(Enrollment $enrollment)
    {
        $student = auth()->user()->student;

        // Verify this enrollment belongs to the student
        if ($enrollment->student_id !== $student->id) {
            abort(403, 'Unauthorized access to this enrollment.');
        }

        $enrollment->load([
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

        return view('student.enrollments.show', compact('enrollment', 'attendanceSummary'));
    }
}
