<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Teacher;
use App\Models\User;
use App\Models\Subject;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class TeacherController extends Controller
{
    /**
     * Display a listing of teachers.
     */
    public function index(Request $request)
    {
        $query = Teacher::with('user');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            })->orWhere('teacher_id', 'like', "%{$search}%")
              ->orWhere('ic_number', 'like', "%{$search}%")
              ->orWhere('specialization', 'like', "%{$search}%");
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by employment type
        if ($request->filled('employment_type')) {
            $query->where('employment_type', $request->employment_type);
        }

        // Filter by pay type
        if ($request->filled('pay_type')) {
            $query->where('pay_type', $request->pay_type);
        }

        $teachers = $query->latest()->paginate(15)->withQueryString();

        return view('admin.teachers.index', compact('teachers'));
    }

    /**
     * Show the form for creating a new teacher.
     */
    public function create()
    {
        $subjects = Subject::where('status', 'active')->orderBy('name')->get();
        return view('admin.teachers.create', compact('subjects'));
    }

    /**
     * Store a newly created teacher.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'ic_number' => 'required|string|max:20|unique:teachers,ic_number',
            'address' => 'nullable|string|max:500',
            'qualification' => 'nullable|string|max:500',
            'experience_years' => 'required|integer|min:0|max:50',
            'specialization' => 'required|string|max:255',
            'bio' => 'nullable|string|max:1000',
            'join_date' => 'required|date',
            'employment_type' => 'required|in:full_time,part_time,contract',
            'pay_type' => 'required|in:hourly,monthly,per_class',
            'hourly_rate' => 'nullable|numeric|min:0|required_if:pay_type,hourly',
            'monthly_salary' => 'nullable|numeric|min:0|required_if:pay_type,monthly',
            'per_class_rate' => 'nullable|numeric|min:0|required_if:pay_type,per_class',
            'bank_name' => 'nullable|string|max:100',
            'bank_account' => 'nullable|string|max:50',
            'epf_number' => 'nullable|string|max:50',
            'socso_number' => 'nullable|string|max:50',
            'status' => 'required|in:active,inactive,on_leave',
        ]);

        DB::beginTransaction();
        try {
            // Create User account
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'password' => Hash::make($validated['password']),
                'status' => $validated['status'] === 'active' ? 'active' : 'inactive',
                'email_verified_at' => now(),
            ]);

            // Assign teacher role
            $user->assignRole('teacher');

            // Generate teacher ID
            $teacherId = 'TCH-' . date('Y') . '-' . str_pad($user->id, 4, '0', STR_PAD_LEFT);

            // Create Teacher profile
            Teacher::create([
                'user_id' => $user->id,
                'teacher_id' => $teacherId,
                'ic_number' => $validated['ic_number'],
                'address' => $validated['address'],
                'qualification' => $validated['qualification'],
                'experience_years' => $validated['experience_years'],
                'specialization' => $validated['specialization'],
                'bio' => $validated['bio'],
                'join_date' => $validated['join_date'],
                'employment_type' => $validated['employment_type'],
                'pay_type' => $validated['pay_type'],
                'hourly_rate' => $validated['hourly_rate'],
                'monthly_salary' => $validated['monthly_salary'],
                'per_class_rate' => $validated['per_class_rate'],
                'bank_name' => $validated['bank_name'],
                'bank_account' => $validated['bank_account'],
                'epf_number' => $validated['epf_number'],
                'socso_number' => $validated['socso_number'],
                'status' => $validated['status'],
            ]);

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'create',
                'model_type' => 'Teacher',
                'model_id' => $user->teacher->id,
                'description' => 'Created teacher: ' . $validated['name'],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();
            return redirect()->route('admin.teachers.index')
                ->with('success', 'Teacher created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to create teacher. ' . $e->getMessage());
        }
    }

    /**
     * Display the specified teacher.
     */
    public function show(Teacher $teacher)
    {
        $teacher->load(['user', 'classes.subject', 'attendance' => function ($q) {
            $q->latest()->take(10);
        }]);

        // Get teaching statistics
        $stats = [
            'total_classes' => $teacher->classes()->count(),
            'active_classes' => $teacher->classes()->where('status', 'active')->count(),
            'total_students' => $teacher->classes()->withCount('enrollments')->get()->sum('enrollments_count'),
        ];

        return view('admin.teachers.show', compact('teacher', 'stats'));
    }

    /**
     * Show the form for editing the specified teacher.
     */
    public function edit(Teacher $teacher)
    {
        $teacher->load('user');
        $subjects = Subject::where('status', 'active')->orderBy('name')->get();
        return view('admin.teachers.edit', compact('teacher', 'subjects'));
    }

    /**
     * Update the specified teacher.
     */
    public function update(Request $request, Teacher $teacher)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($teacher->user_id)],
            'phone' => 'required|string|max:20',
            'password' => 'nullable|string|min:8|confirmed',
            'ic_number' => ['required', 'string', 'max:20', Rule::unique('teachers', 'ic_number')->ignore($teacher->id)],
            'address' => 'nullable|string|max:500',
            'qualification' => 'nullable|string|max:500',
            'experience_years' => 'required|integer|min:0|max:50',
            'specialization' => 'required|string|max:255',
            'bio' => 'nullable|string|max:1000',
            'join_date' => 'required|date',
            'employment_type' => 'required|in:full_time,part_time,contract',
            'pay_type' => 'required|in:hourly,monthly,per_class',
            'hourly_rate' => 'nullable|numeric|min:0',
            'monthly_salary' => 'nullable|numeric|min:0',
            'per_class_rate' => 'nullable|numeric|min:0',
            'bank_name' => 'nullable|string|max:100',
            'bank_account' => 'nullable|string|max:50',
            'epf_number' => 'nullable|string|max:50',
            'socso_number' => 'nullable|string|max:50',
            'status' => 'required|in:active,inactive,on_leave',
        ]);

        DB::beginTransaction();
        try {
            // Update User account
            $userData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'status' => $validated['status'] === 'active' ? 'active' : 'inactive',
            ];

            if (!empty($validated['password'])) {
                $userData['password'] = Hash::make($validated['password']);
            }

            $teacher->user->update($userData);

            // Update Teacher profile
            $teacher->update([
                'ic_number' => $validated['ic_number'],
                'address' => $validated['address'],
                'qualification' => $validated['qualification'],
                'experience_years' => $validated['experience_years'],
                'specialization' => $validated['specialization'],
                'bio' => $validated['bio'],
                'join_date' => $validated['join_date'],
                'employment_type' => $validated['employment_type'],
                'pay_type' => $validated['pay_type'],
                'hourly_rate' => $validated['hourly_rate'],
                'monthly_salary' => $validated['monthly_salary'],
                'per_class_rate' => $validated['per_class_rate'],
                'bank_name' => $validated['bank_name'],
                'bank_account' => $validated['bank_account'],
                'epf_number' => $validated['epf_number'],
                'socso_number' => $validated['socso_number'],
                'status' => $validated['status'],
            ]);

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'update',
                'model_type' => 'Teacher',
                'model_id' => $teacher->id,
                'description' => 'Updated teacher: ' . $validated['name'],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();
            return redirect()->route('admin.teachers.index')
                ->with('success', 'Teacher updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to update teacher. ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified teacher (soft delete).
     */
    public function destroy(Request $request, Teacher $teacher)
    {
        // Check if teacher has active classes
        if ($teacher->classes()->where('status', 'active')->exists()) {
            return back()->with('error', 'Cannot delete teacher with active classes. Please reassign classes first.');
        }

        DB::beginTransaction();
        try {
            $teacherName = $teacher->user->name;

            // Soft delete teacher and user
            $teacher->delete();
            $teacher->user->delete();

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'delete',
                'model_type' => 'Teacher',
                'model_id' => $teacher->id,
                'description' => 'Deleted teacher: ' . $teacherName,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();
            return redirect()->route('admin.teachers.index')
                ->with('success', 'Teacher deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete teacher. ' . $e->getMessage());
        }
    }

    /**
     * Export teachers list to CSV.
     */
    public function export(Request $request)
    {
        $teachers = Teacher::with('user')->get();

        $filename = 'teachers_export_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($teachers) {
            $file = fopen('php://output', 'w');

            // Header row
            fputcsv($file, [
                'Teacher ID', 'Name', 'Email', 'Phone', 'IC Number',
                'Specialization', 'Employment Type', 'Pay Type', 'Join Date', 'Status'
            ]);

            // Data rows
            foreach ($teachers as $t) {
                fputcsv($file, [
                    $t->teacher_id,
                    $t->user->name,
                    $t->user->email,
                    $t->user->phone,
                    $t->ic_number,
                    $t->specialization,
                    $t->employment_type,
                    $t->pay_type,
                    $t->join_date?->format('Y-m-d'),
                    $t->status,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
