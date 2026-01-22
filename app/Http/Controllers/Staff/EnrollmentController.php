<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\EnrollmentRequest;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\Package;
use App\Models\Subject;
use App\Models\ClassModel;
use App\Models\ActivityLog;
use App\Models\ClassAttendanceSummary;
use App\Services\EnrollmentService;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EnrollmentController extends Controller
{
    protected $enrollmentService;
    protected $subscriptionService;

    public function __construct(EnrollmentService $enrollmentService, SubscriptionService $subscriptionService)
    {
        $this->enrollmentService = $enrollmentService;
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Display all enrollments
     */
    public function index(Request $request)
    {
        $query = Enrollment::with(['student.user', 'package', 'class.subject']);

        // Search by student name or student ID
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('student', function ($q) use ($search) {
                $q->whereHas('user', function ($uq) use ($search) {
                    $uq->where('name', 'like', "%{$search}%")
                       ->orWhere('email', 'like', "%{$search}%");
                })->orWhere('student_id', 'like', "%{$search}%");
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

        $enrollments = $query->latest()->paginate(20)->withQueryString();

        // Get filter options
        $classes = ClassModel::active()->with('subject')->orderBy('name')->get();
        $packages = Package::active()->orderBy('name')->get();

        // Get statistics
        $stats = $this->enrollmentService->getEnrollmentStats();

        return view('staff.enrollments.index', compact('enrollments', 'classes', 'packages', 'stats'));
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
        $subjects = Subject::active()->orderBy('name')->get();

        return view('staff.enrollments.create', compact('students', 'packages', 'classes', 'subjects'));
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
            'student.parent.user',
            'package.subjects',
            'class.subject',
            'class.teacher.user',
            'class.schedules',
            'invoices.payments',
            'feeHistory',
        ]);

        // Get attendance summary
        $attendanceSummary = null;
        if ($enrollment->class_id) {
            $attendanceSummary = ClassAttendanceSummary::where('class_id', $enrollment->class_id)
                ->where('student_id', $enrollment->student_id)
                ->first();
        }

        return view('staff.enrollments.show', compact('enrollment', 'attendanceSummary'));
    }

    /**
     * Show edit form
     */
    public function edit(Enrollment $enrollment)
    {
        $enrollment->load(['student.user', 'package', 'class.subject']);

        $packages = Package::active()->with('subjects')->get();
        $classes = ClassModel::active()->with(['subject', 'teacher.user'])->get();

        return view('staff.enrollments.edit', compact('enrollment', 'packages', 'classes'));
    }

    /**
     * Update enrollment
     */
    public function update(Request $request, Enrollment $enrollment)
    {
        $request->validate([
            'monthly_fee' => 'required|numeric|min:0',
            'payment_cycle_day' => 'required|integer|min:1|max:28',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|in:active,suspended,expired,cancelled,trial',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $oldFee = $enrollment->monthly_fee;
            $oldStatus = $enrollment->status;

            DB::beginTransaction();

            $enrollment->update([
                'monthly_fee' => $request->monthly_fee,
                'payment_cycle_day' => $request->payment_cycle_day,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'status' => $request->status,
            ]);

            // Log fee change history
            if ($oldFee != $request->monthly_fee) {
                $enrollment->feeHistory()->create([
                    'old_fee' => $oldFee,
                    'new_fee' => $request->monthly_fee,
                    'reason' => $request->notes ?? 'Manual adjustment',
                    'changed_by' => auth()->id(),
                ]);
            }

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'update',
                'model_type' => 'Enrollment',
                'model_id' => $enrollment->id,
                'description' => "Updated enrollment for {$enrollment->student->user->name}" .
                    ($oldStatus != $request->status ? " (Status: {$oldStatus} → {$request->status})" : '') .
                    ($oldFee != $request->monthly_fee ? " (Fee: RM{$oldFee} → RM{$request->monthly_fee})" : ''),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            return redirect()->route('staff.enrollments.show', $enrollment)
                ->with('success', 'Enrollment updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to update enrollment: ' . $e->getMessage());
        }
    }

    /**
     * Delete enrollment
     */
    public function destroy(Enrollment $enrollment)
    {
        // Check if enrollment has paid invoices
        if ($enrollment->invoices()->where('paid_amount', '>', 0)->exists()) {
            return back()->with('error', 'Cannot delete enrollment with paid invoices.');
        }

        try {
            $studentName = $enrollment->student->user->name;

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'delete',
                'model_type' => 'Enrollment',
                'model_id' => $enrollment->id,
                'description' => "Deleted enrollment for {$studentName}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            $enrollment->delete();

            return redirect()->route('staff.enrollments.index')
                ->with('success', 'Enrollment deleted successfully!');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete enrollment: ' . $e->getMessage());
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
            $enrollment->update([
                'status' => 'cancelled',
                'cancellation_reason' => $request->cancellation_reason,
                'cancelled_at' => now(),
            ]);

            // Revoke material access
            $enrollment->materialAccess()->update(['revoked_at' => now()]);

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'cancel',
                'model_type' => 'Enrollment',
                'model_id' => $enrollment->id,
                'description' => "Cancelled enrollment for {$enrollment->student->user->name}. Reason: {$request->cancellation_reason}",
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
            $enrollment->update([
                'status' => 'suspended',
            ]);

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'suspend',
                'model_type' => 'Enrollment',
                'model_id' => $enrollment->id,
                'description' => "Suspended enrollment for {$enrollment->student->user->name}" .
                    ($request->reason ? ". Reason: {$request->reason}" : ''),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return back()->with('success', 'Enrollment suspended successfully!');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to suspend enrollment: ' . $e->getMessage());
        }
    }

    /**
     * Resume suspended enrollment
     */
    public function resume(Enrollment $enrollment)
    {
        try {
            $enrollment->update([
                'status' => 'active',
            ]);

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
            'generate_invoice' => 'boolean',
        ]);

        try {
            $this->subscriptionService->renewEnrollment(
                $enrollment,
                $request->months,
                $request->boolean('generate_invoice', true)
            );

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'renew',
                'model_type' => 'Enrollment',
                'model_id' => $enrollment->id,
                'description' => "Renewed enrollment for {$enrollment->student->user->name} by " .
                    ($request->months ?? $enrollment->package->duration_months ?? 1) . " month(s)",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return back()->with('success', 'Enrollment renewed successfully!');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to renew enrollment: ' . $e->getMessage());
        }
    }

    /**
     * AJAX: Search students for Select2
     */
    public function searchStudents(Request $request)
    {
        $search = $request->get('q', '');

        $students = Student::approved()
            ->with('user')
            ->where(function ($query) use ($search) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                })->orWhere('student_id', 'like', "%{$search}%");
            })
            ->limit(20)
            ->get()
            ->map(function ($student) {
                return [
                    'id' => $student->id,
                    'text' => $student->user->name . ' (' . $student->student_id . ')',
                    'student_id' => $student->student_id,
                    'email' => $student->user->email,
                    'phone' => $student->user->phone,
                ];
            });

        return response()->json(['results' => $students]);
    }

    /**
     * AJAX: Get class fee
     */
    public function getClassFee(ClassModel $class)
    {
        return response()->json([
            'fee' => $class->monthly_fee,
            'name' => $class->name,
            'subject' => $class->subject->name ?? null,
            'teacher' => $class->teacher->user->name ?? null,
            'schedule' => $class->schedules->map(function ($s) {
                return $s->day_of_week . ' ' . $s->start_time . ' - ' . $s->end_time;
            })->join(', '),
        ]);
    }

    /**
     * AJAX: Get package details
     */
    public function getPackageDetails(Package $package)
    {
        $package->load('subjects');

        return response()->json([
            'id' => $package->id,
            'name' => $package->name,
            'price' => $package->price,
            'duration_months' => $package->duration_months,
            'subjects' => $package->subjects->map(function ($subject) {
                return [
                    'id' => $subject->id,
                    'name' => $subject->name,
                ];
            }),
            'features' => $package->features ?? [],
        ]);
    }

    /**
     * AJAX: Get package subjects with classes
     */
    public function getPackageSubjectsWithClasses(Package $package)
    {
        $package->load('subjects');

        $subjectsWithClasses = $package->subjects->map(function ($subject) {
            $classes = ClassModel::where('subject_id', $subject->id)
                ->active()
                ->with(['teacher.user', 'schedules'])
                ->get()
                ->map(function ($class) {
                    return [
                        'id' => $class->id,
                        'name' => $class->name,
                        'teacher' => $class->teacher->user->name ?? 'TBA',
                        'schedule' => $class->schedules->map(function ($s) {
                            return $s->day_of_week . ' ' . substr($s->start_time, 0, 5);
                        })->join(', '),
                        'available_seats' => $class->capacity - $class->current_enrollment,
                    ];
                });

            return [
                'id' => $subject->id,
                'name' => $subject->name,
                'classes' => $classes,
            ];
        });

        return response()->json($subjectsWithClasses);
    }

    /**
     * AJAX: Get classes by subject
     */
    public function getClassesBySubject(Subject $subject)
    {
        $classes = ClassModel::where('subject_id', $subject->id)
            ->active()
            ->with(['teacher.user', 'schedules'])
            ->get()
            ->map(function ($class) {
                return [
                    'id' => $class->id,
                    'name' => $class->name,
                    'monthly_fee' => $class->monthly_fee,
                    'teacher' => $class->teacher->user->name ?? 'TBA',
                    'schedule' => $class->schedules->map(function ($s) {
                        return $s->day_of_week . ' ' . substr($s->start_time, 0, 5) . '-' . substr($s->end_time, 0, 5);
                    })->join(', '),
                    'available_seats' => $class->capacity - $class->current_enrollment,
                ];
            });

        return response()->json($classes);
    }

    /**
     * AJAX: Get student enrollments
     */
    public function getStudentEnrollments(Student $student)
    {
        $enrollments = $student->enrollments()
            ->with(['package', 'class.subject'])
            ->latest()
            ->get()
            ->map(function ($enrollment) {
                return [
                    'id' => $enrollment->id,
                    'type' => $enrollment->package_id ? 'Package' : 'Class',
                    'name' => $enrollment->package_id
                        ? $enrollment->package->name
                        : ($enrollment->class->name . ' - ' . $enrollment->class->subject->name),
                    'status' => $enrollment->status,
                    'start_date' => $enrollment->start_date->format('d M Y'),
                    'end_date' => $enrollment->end_date ? $enrollment->end_date->format('d M Y') : 'Ongoing',
                    'monthly_fee' => $enrollment->monthly_fee,
                ];
            });

        return response()->json($enrollments);
    }
}
