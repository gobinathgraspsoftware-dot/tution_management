<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\EnrollmentRequest;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\Package;
use App\Models\ClassModel;
use App\Models\Subject;
use App\Models\ActivityLog;
use App\Services\EnrollmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

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

        // Filter by package
        if ($request->filled('package_id')) {
            $query->where('package_id', $request->package_id);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('start_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('start_date', '<=', $request->date_to);
        }

        $enrollments = $query->latest()->paginate(20);

        // Get filter options
        $classes = ClassModel::active()->with('subject')->orderBy('name')->get();
        $packages = Package::active()->orderBy('name')->get();

        // Get statistics
        $stats = $this->enrollmentService->getEnrollmentStats();

        return view('admin.enrollments.index', compact('enrollments', 'classes', 'packages', 'stats'));
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

        return view('admin.enrollments.create', compact('students', 'packages', 'classes'));
    }

    /**
     * Store new enrollment
     */
    public function store(EnrollmentRequest $request)
    {
        try {
            $data = $request->validated();
            $enrollmentType = $request->input('enrollment_type', 'package');

            // Check if enrolling in package
            if ($enrollmentType === 'package' && $request->filled('package_id')) {
                $student = Student::findOrFail($data['student_id']);
                $package = Package::with('subjects')->findOrFail($data['package_id']);

                // Get selected classes for each subject
                $subjectClasses = $request->input('subject_classes', []);

                // NOTE: Removed validatePackageClassSelection() call
                // The service now handles duplicates gracefully by skipping them

                // Create enrollments with selected classes
                $result = $this->enrollmentService->enrollInPackageWithClasses(
                    $student,
                    $package,
                    $subjectClasses,
                    $data
                );

                ActivityLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'create',
                    'model_type' => 'Enrollment',
                    'description' => "Enrolled student {$student->user->name} in package {$package->name} with " . count($result['created']) . " classes",
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);

                // Build success message
                $message = "Student successfully enrolled in {$package->name} package with " . count($result['created']) . " class(es)!";

                // Add info about skipped classes
                if (!empty($result['skipped'])) {
                    $skippedNames = array_map(fn($s) => $s['class_name'] ?? 'Unknown', $result['skipped']);
                    $message .= " Skipped " . count($result['skipped']) . " class(es) (already enrolled): " . implode(', ', $skippedNames);
                }

                return redirect()->route('admin.enrollments.index')
                    ->with('success', $message);

            } else {
                // Single class enrollment - check for existing enrollment first
                $existingEnrollment = Enrollment::where('student_id', $data['student_id'])
                    ->where('class_id', $data['class_id'])
                    ->whereIn('status', ['active', 'trial', 'suspended'])
                    ->first();

                if ($existingEnrollment) {
                    $class = ClassModel::find($data['class_id']);
                    return back()->withInput()
                        ->with('error', "Student is already enrolled in class '{$class->name}'. Please select a different class.");
                }

                $enrollment = $this->enrollmentService->createEnrollment($data);

                ActivityLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'create',
                    'model_type' => 'Enrollment',
                    'model_id' => $enrollment->id,
                    'description' => "Created enrollment for {$enrollment->student->user->name} in {$enrollment->class->name}",
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);

                return redirect()->route('admin.enrollments.index')
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
            'class.schedules',
            'invoices',
            'feeHistory',
        ]);

        // Get attendance summary
        $attendanceSummary = null;
        if ($enrollment->class_id) {
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
        }

        // Get related enrollments if this is a package enrollment
        $relatedEnrollments = [];
        if ($enrollment->package_id) {
            $relatedEnrollments = Enrollment::where('student_id', $enrollment->student_id)
                ->where('package_id', $enrollment->package_id)
                ->where('id', '!=', $enrollment->id)
                ->with(['class.subject', 'class.teacher.user'])
                ->get();
        }

        return view('admin.enrollments.show', compact('enrollment', 'attendanceSummary', 'relatedEnrollments'));
    }

    /**
     * Show edit form
     */
    public function edit(Enrollment $enrollment)
    {
        $enrollment->load(['student.user', 'package', 'class', 'feeHistory']);

        $students = Student::approved()
            ->with('user')
            ->get()
            ->sortBy('user.name');

        $packages = Package::active()->with('subjects')->get();
        $classes = ClassModel::active()->with(['subject', 'teacher.user'])->get();

        return view('admin.enrollments.edit', compact('enrollment', 'students', 'packages', 'classes'));
    }

    /**
     * Update enrollment
     */
    public function update(EnrollmentRequest $request, Enrollment $enrollment)
    {
        try {
            $data = $request->validated();

            $this->enrollmentService->updateEnrollment($enrollment, $data);

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'update',
                'model_type' => 'Enrollment',
                'model_id' => $enrollment->id,
                'description' => "Updated enrollment for {$enrollment->student->user->name}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return redirect()->route('admin.enrollments.index')
                ->with('success', 'Enrollment updated successfully!');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Failed to update enrollment: ' . $e->getMessage());
        }
    }

    /**
     * Cancel enrollment
     */
    public function cancel(Request $request, Enrollment $enrollment)
    {
        $request->validate([
            'cancellation_reason' => 'required|string|max:500',
        ]);

        try {
            $this->enrollmentService->cancelEnrollment($enrollment, $request->cancellation_reason);

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'cancel',
                'model_type' => 'Enrollment',
                'model_id' => $enrollment->id,
                'description' => "Cancelled enrollment for {$enrollment->student->user->name}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return back()->with('success', 'Enrollment cancelled successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to cancel enrollment: ' . $e->getMessage());
        }
    }

    /**
     * Suspend enrollment
     */
    public function suspend(Request $request, Enrollment $enrollment)
    {
        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            $this->enrollmentService->suspendEnrollment($enrollment, $request->reason);

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'suspend',
                'model_type' => 'Enrollment',
                'model_id' => $enrollment->id,
                'description' => "Suspended enrollment for {$enrollment->student->user->name}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return back()->with('success', 'Enrollment suspended successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to suspend enrollment: ' . $e->getMessage());
        }
    }

    /**
     * Resume enrollment
     */
    public function resume(Enrollment $enrollment)
    {
        try {
            $this->enrollmentService->resumeEnrollment($enrollment);

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'resume',
                'model_type' => 'Enrollment',
                'model_id' => $enrollment->id,
                'description' => "Resumed enrollment for {$enrollment->student->user->name}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return back()->with('success', 'Enrollment resumed successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to resume enrollment: ' . $e->getMessage());
        }
    }

    /**
     * Renew enrollment
     */
    public function renew(Request $request, Enrollment $enrollment)
    {
        $request->validate([
            'months' => 'nullable|integer|min:1|max:24',
        ]);

        try {
            $this->enrollmentService->renewEnrollment($enrollment, $request->months);

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'renew',
                'model_type' => 'Enrollment',
                'model_id' => $enrollment->id,
                'description' => "Renewed enrollment for {$enrollment->student->user->name}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return back()->with('success', 'Enrollment renewed successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to renew enrollment: ' . $e->getMessage());
        }
    }

    /**
     * Delete enrollment
     */
    public function destroy(Enrollment $enrollment)
    {
        try {
            // Check if there are payments
            if ($enrollment->invoices()->where('paid_amount', '>', 0)->exists()) {
                return back()->with('error', 'Cannot delete enrollment with payment history!');
            }

            $studentName = $enrollment->student->user->name;
            $enrollment->delete();

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'delete',
                'model_type' => 'Enrollment',
                'model_id' => $enrollment->id,
                'description' => "Deleted enrollment for {$studentName}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return redirect()->route('admin.enrollments.index')
                ->with('success', 'Enrollment deleted successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete enrollment: ' . $e->getMessage());
        }
    }

    /**
     * Get class fee via AJAX
     */
    public function getClassFee($classId)
    {
        $class = ClassModel::findOrFail($classId);
        return response()->json([
            'monthly_fee' => $class->monthly_fee,
        ]);
    }

    /**
     * Get package details via AJAX (legacy - kept for backward compatibility)
     */
    public function getPackageDetails($packageId)
    {
        $package = Package::with('subjects.classes')->findOrFail($packageId);

        $classes = $package->subjects->flatMap(function ($subject) {
            return $subject->classes()->active()->get();
        });

        return response()->json([
            'price' => $package->price,
            'duration_months' => $package->duration_months,
            'classes' => $classes->map(function ($class) {
                return [
                    'id' => $class->id,
                    'name' => $class->name,
                    'subject' => $class->subject->name,
                ];
            }),
        ]);
    }

    /**
     * Get package subjects with their available classes via AJAX
     * Now includes student's existing enrollments to mark classes as enrolled
     */
    public function getPackageSubjectsWithClasses(Request $request, $packageId)
    {
        $package = Package::with(['subjects' => function($query) {
            $query->orderBy('name');
        }])->findOrFail($packageId);

        // Get student's existing enrollments if student_id is provided
        $studentId = $request->query('student_id');
        $existingEnrollments = [];

        if ($studentId) {
            $existingEnrollments = Enrollment::where('student_id', $studentId)
                ->whereIn('status', ['active', 'trial', 'suspended'])
                ->pluck('class_id')
                ->toArray();
        }

        $subjects = $package->subjects->map(function ($subject) use ($existingEnrollments) {
            // Get active classes for this subject with teacher info
            $classes = ClassModel::where('subject_id', $subject->id)
                ->where('status', 'active')
                ->with(['teacher.user'])
                ->orderBy('name')
                ->get()
                ->map(function ($class) use ($existingEnrollments) {
                    $isEnrolled = in_array($class->id, $existingEnrollments);
                    return [
                        'id' => $class->id,
                        'name' => $class->name,
                        'code' => $class->code,
                        'type' => $class->type,
                        'grade_level' => $class->grade_level,
                        'teacher_name' => $class->teacher ? $class->teacher->user->name : null,
                        'capacity' => $class->capacity,
                        'current_enrollment' => $class->current_enrollment,
                        'available_seats' => $class->capacity - $class->current_enrollment,
                        'location' => $class->location,
                        'meeting_link' => $class->meeting_link,
                        'is_enrolled' => $isEnrolled,
                    ];
                });

            return [
                'id' => $subject->id,
                'name' => $subject->name,
                'code' => $subject->code,
                'sessions_per_month' => $subject->pivot->sessions_per_month ?? 4,
                'classes' => $classes,
            ];
        });

        return response()->json([
            'id' => $package->id,
            'name' => $package->name,
            'price' => $package->price,
            'duration_months' => $package->duration_months,
            'type' => $package->type,
            'subjects' => $subjects,
            'existing_enrollments' => $existingEnrollments,
        ]);
    }

    /**
     * Get classes for a specific subject via AJAX
     */
    public function getClassesBySubject(Request $request, $subjectId)
    {
        $subject = Subject::findOrFail($subjectId);

        // Get student's existing enrollments if student_id is provided
        $studentId = $request->query('student_id');
        $existingEnrollments = [];

        if ($studentId) {
            $existingEnrollments = Enrollment::where('student_id', $studentId)
                ->whereIn('status', ['active', 'trial', 'suspended'])
                ->pluck('class_id')
                ->toArray();
        }

        $classes = ClassModel::where('subject_id', $subjectId)
            ->where('status', 'active')
            ->with(['teacher.user'])
            ->orderBy('name')
            ->get()
            ->map(function ($class) use ($existingEnrollments) {
                $isEnrolled = in_array($class->id, $existingEnrollments);
                return [
                    'id' => $class->id,
                    'name' => $class->name,
                    'code' => $class->code,
                    'type' => $class->type,
                    'grade_level' => $class->grade_level,
                    'teacher_name' => $class->teacher ? $class->teacher->user->name : null,
                    'capacity' => $class->capacity,
                    'current_enrollment' => $class->current_enrollment,
                    'available_seats' => max(0, $class->capacity - $class->current_enrollment),
                    'is_enrolled' => $isEnrolled,
                ];
            });

        return response()->json([
            'subject_id' => $subject->id,
            'subject_name' => $subject->name,
            'classes' => $classes,
        ]);
    }

    /**
     * Get student's existing active enrollments via AJAX
     */
    public function getStudentEnrollments($studentId)
    {
        $student = Student::findOrFail($studentId);

        $enrollments = Enrollment::where('student_id', $studentId)
            ->whereIn('status', ['active', 'trial', 'suspended'])
            ->with(['class.subject', 'package'])
            ->get()
            ->map(function ($enrollment) {
                return [
                    'id' => $enrollment->id,
                    'class_id' => $enrollment->class_id,
                    'package_id' => $enrollment->package_id,
                    'class_name' => $enrollment->class ? $enrollment->class->name : null,
                    'subject_name' => $enrollment->class && $enrollment->class->subject ? $enrollment->class->subject->name : null,
                    'package_name' => $enrollment->package ? $enrollment->package->name : null,
                    'status' => $enrollment->status,
                ];
            });

        return response()->json([
            'student_id' => $studentId,
            'student_name' => $student->user->name,
            'enrollments' => $enrollments,
            'enrolled_class_ids' => $enrollments->pluck('class_id')->filter()->toArray(),
        ]);
    }
}
