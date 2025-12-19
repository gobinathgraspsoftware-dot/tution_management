<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Parents;
use App\Models\User;
use App\Models\ActivityLog;
use App\Helpers\CountryCodeHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class StudentController extends Controller
{
    /**
     * Display a listing of students.
     */
    public function index(Request $request)
    {
        $query = Student::with(['user', 'parent.user']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            })->orWhere('student_id', 'like', "%{$search}%")
              ->orWhere('ic_number', 'like', "%{$search}%")
              ->orWhere('school_name', 'like', "%{$search}%");
        }

        // Filter by user status
        if ($request->filled('status')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('status', $request->status);
            });
        }

        // Filter by approval status
        if ($request->filled('approval_status')) {
            $query->where('approval_status', $request->approval_status);
        }

        // Filter by registration type
        if ($request->filled('registration_type')) {
            $query->where('registration_type', $request->registration_type);
        }

        // Filter by grade level
        if ($request->filled('grade_level')) {
            $query->where('grade_level', $request->grade_level);
        }

        // Filter by gender
        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        $students = $query->latest()->paginate(15)->withQueryString();

        // Get unique grade levels for filter
        $gradeLevels = Student::distinct()->pluck('grade_level')->filter()->values();

        return view('admin.students.index', compact('students', 'gradeLevels'));
    }

    /**
     * AJAX endpoint to search parents for Select2 dropdown
     */
    public function searchParents(Request $request)
    {
        $search = $request->get('q', '');
        $page = $request->get('page', 1);
        $perPage = 10; // Number of results per page

        $query = Parents::with('user')
            ->whereHas('user', function ($q) {
                $q->where('status', 'active');
            });

        // Search by name, email, phone, or IC number
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', "%{$search}%")
                              ->orWhere('email', 'like', "%{$search}%")
                              ->orWhere('phone', 'like', "%{$search}%");
                })->orWhere('ic_number', 'like', "%{$search}%");
            });
        }

        $total = $query->count();
        $parents = $query->skip(($page - 1) * $perPage)
                        ->take($perPage)
                        ->get();

        $results = $parents->map(function ($parent) {
            // Format IC number for display
            $icFormatted = $parent->ic_number;
            if (strlen($parent->ic_number) === 12) {
                $icFormatted = substr($parent->ic_number, 0, 6) . '-' .
                               substr($parent->ic_number, 6, 2) . '-' .
                               substr($parent->ic_number, 8, 4);
            }

            return [
                'id' => $parent->id,
                'text' => $parent->user->name . ' (' . $icFormatted . ')',
                'name' => $parent->user->name,
                'ic_number' => $icFormatted,
                'email' => $parent->user->email,
                'phone' => $parent->user->phone,
            ];
        });

        return response()->json([
            'results' => $results,
            'pagination' => [
                'more' => ($page * $perPage) < $total
            ]
        ]);
    }

    /**
     * Show the form for creating a new student.
     */
    public function create()
    {
        // For backward compatibility, still load parents but will be replaced by AJAX
        $parents = Parents::with('user')->whereHas('user', function ($q) {
            $q->where('status', 'active');
        })->take(10)->get(); // Load only first 10 for initial display

        // Get all countries for phone dropdown
        $countries = CountryCodeHelper::getAllCountries();
        $defaultCountryCode = CountryCodeHelper::getDefaultCountryCode();

        return view('admin.students.create', compact('parents', 'countries', 'defaultCountryCode'));
    }

    /**
     * Store a newly created student.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'country_code' => 'nullable|string|max:5',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'parent_id' => 'required|exists:parents,id',
            'ic_number' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    $cleaned = preg_replace('/[^0-9]/', '', $value);
                    if (strlen($cleaned) !== 12) {
                        $fail('The IC number must be exactly 12 digits.');
                    }
                    if (!preg_match('/^[0-9]+$/', $cleaned)) {
                        $fail('The IC number must contain only numeric digits.');
                    }
                },
                Rule::unique('students', 'ic_number')->where(function ($query) use ($request) {
                    $cleaned = preg_replace('/[^0-9]/', '', $request->ic_number);
                    return $query->where('ic_number', $cleaned);
                })
            ],
            'date_of_birth' => 'required|date|before:today',
            'gender' => 'required|in:male,female',
            'school_name' => 'required|string|max:255',
            'grade_level' => 'required|string|max:50',
            'address' => 'nullable|string|max:500',
            'medical_conditions' => 'nullable|string|max:500',
            'registration_type' => 'required|in:online,offline',
            'notes' => 'nullable|string|max:1000',
            'status' => 'required|in:active,inactive',
        ]);

        DB::beginTransaction();
        try {
            // Clean IC number (remove hyphens)
            $cleanedIcNumber = preg_replace('/[^0-9]/', '', $validated['ic_number']);

            // Format phone number with country code
            $phoneNumber = null;
            if (!empty($validated['phone'])) {
                $countryCode = $validated['country_code'] ?? CountryCodeHelper::getDefaultCountryCode();
                $phoneNumber = CountryCodeHelper::formatPhoneNumber($countryCode, $validated['phone']);
            }

            // Convert name to UPPERCASE
            $name = strtoupper($validated['name']);

            // Create User account
            $user = User::create([
                'name' => $name,
                'email' => $validated['email'],
                'phone' => $phoneNumber,
                'password' => Hash::make($validated['password']),
                'password_view' => $validated['password'], // Store plain password
                'status' => $validated['status'],
                'email_verified_at' => now(),
            ]);

            // Assign student role
            $user->assignRole('student');

            // Generate student ID
            $studentId = 'STU-' . date('Y') . '-' . str_pad($user->id, 4, '0', STR_PAD_LEFT);

            // Generate referral code
            $referralCode = strtoupper(Str::random(8));

            // Create Student profile (auto-approved when created by admin)
            Student::create([
                'user_id' => $user->id,
                'parent_id' => $validated['parent_id'],
                'student_id' => $studentId,
                'ic_number' => $cleanedIcNumber, // Store digits only
                'date_of_birth' => $validated['date_of_birth'],
                'gender' => $validated['gender'],
                'school_name' => $validated['school_name'],
                'grade_level' => $validated['grade_level'],
                'address' => $validated['address'],
                'medical_conditions' => $validated['medical_conditions'],
                'registration_type' => $validated['registration_type'],
                'registration_date' => now(),
                'enrollment_date' => now(),
                'referral_code' => $referralCode,
                'notes' => $validated['notes'],
            ]);

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'create',
                'model_type' => 'Student',
                'model_id' => $user->student->id,
                'description' => 'Created student: ' . $name,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();
            return redirect()->route('admin.students.index')
                ->with('success', 'Student created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to create student. ' . $e->getMessage());
        }
    }

    /**
     * Display the specified student.
     */
    public function show(Student $student)
    {
        $student->load([
            'user',
            'parent.user',
            'enrollments.package',
            'enrollments.class.subject',
            'invoices' => function ($q) {
                $q->latest()->take(5);
            },
            'payments' => function ($q) {
                $q->latest()->take(5);
            },
            'attendance' => function ($q) {
                $q->latest()->take(10);
            },
        ]);

        // Get statistics
        $stats = [
            'total_paid' => $student->payments()->where('status', 'completed')->sum('amount'),
            'pending_amount' => $student->invoices()->whereIn('status', ['pending', 'partial'])->sum('total_amount'),
            'attendance_rate' => $this->calculateAttendanceRate($student),
            'active_enrollments' => $student->enrollments()->where('status', 'active')->count(),
        ];

        return view('admin.students.show', compact('student', 'stats'));
    }

    /**
     * Show the form for editing the specified student.
     */
    public function edit(Student $student)
    {
        $student->load(['user', 'parent.user']);

        // For backward compatibility, still load parents but will be replaced by AJAX
        $parents = Parents::with('user')->whereHas('user', function ($q) {
            $q->where('status', 'active');
        })->take(10)->get();

        // Get all countries for phone dropdown
        $countries = CountryCodeHelper::getAllCountries();
        $defaultCountryCode = CountryCodeHelper::getDefaultCountryCode();

        // Extract country code and phone number
        $phoneData = CountryCodeHelper::extractCountryCode($student->user->phone);

        return view('admin.students.edit', compact('student', 'parents', 'countries', 'defaultCountryCode', 'phoneData'));
    }

    /**
     * Update the specified student.
     */
    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($student->user_id)],
            'country_code' => 'nullable|string|max:5',
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8|confirmed',
            'parent_id' => 'required|exists:parents,id',
            'ic_number' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    $cleaned = preg_replace('/[^0-9]/', '', $value);
                    if (strlen($cleaned) !== 12) {
                        $fail('The IC number must be exactly 12 digits.');
                    }
                    if (!preg_match('/^[0-9]+$/', $cleaned)) {
                        $fail('The IC number must contain only numeric digits.');
                    }
                },
                Rule::unique('students', 'ic_number')->where(function ($query) use ($request) {
                    $cleaned = preg_replace('/[^0-9]/', '', $request->ic_number);
                    return $query->where('ic_number', $cleaned);
                })->ignore($student->id)
            ],
            'date_of_birth' => 'required|date|before:today',
            'gender' => 'required|in:male,female',
            'school_name' => 'required|string|max:255',
            'grade_level' => 'required|string|max:50',
            'address' => 'nullable|string|max:500',
            'medical_conditions' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
            'status' => 'required|in:active,inactive',
        ]);

        DB::beginTransaction();
        try {
            // Clean IC number (remove hyphens)
            $cleanedIcNumber = preg_replace('/[^0-9]/', '', $validated['ic_number']);

            // Format phone number with country code
            $phoneNumber = null;
            if (!empty($validated['phone'])) {
                $countryCode = $validated['country_code'] ?? CountryCodeHelper::getDefaultCountryCode();
                $phoneNumber = CountryCodeHelper::formatPhoneNumber($countryCode, $validated['phone']);
            }

            // Convert name to UPPERCASE
            $name = strtoupper($validated['name']);

            // Update User account
            $userData = [
                'name' => $name,
                'email' => $validated['email'],
                'phone' => $phoneNumber,
                'status' => $validated['status'],
            ];

            if (!empty($validated['password'])) {
                $userData['password'] = Hash::make($validated['password']);
                $userData['password_view'] = $validated['password']; // Store plain password
            }

            $student->user->update($userData);

            // Update Student profile
            $student->update([
                'parent_id' => $validated['parent_id'],
                'ic_number' => $cleanedIcNumber, // Store digits only
                'date_of_birth' => $validated['date_of_birth'],
                'gender' => $validated['gender'],
                'school_name' => $validated['school_name'],
                'grade_level' => $validated['grade_level'],
                'address' => $validated['address'],
                'medical_conditions' => $validated['medical_conditions'],
                'notes' => $validated['notes'],
            ]);

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'update',
                'model_type' => 'Student',
                'model_id' => $student->id,
                'description' => 'Updated student: ' . $name,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();
            return redirect()->route('admin.students.index')
                ->with('success', 'Student updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to update student. ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified student (soft delete).
     */
    public function destroy(Request $request, Student $student)
    {
        // Check if student has active enrollments
        if ($student->enrollments()->where('status', 'active')->exists()) {
            return back()->with('error', 'Cannot delete student with active enrollments. Please cancel enrollments first.');
        }

        // Check for pending invoices
        if ($student->invoices()->whereIn('status', ['pending', 'partial'])->exists()) {
            return back()->with('error', 'Cannot delete student with pending invoices. Please settle payments first.');
        }

        DB::beginTransaction();
        try {
            $studentName = $student->user->name;

            // Soft delete student and user
            $student->delete();
            $student->user->delete();

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'delete',
                'model_type' => 'Student',
                'model_id' => $student->id,
                'description' => 'Deleted student: ' . $studentName,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();
            return redirect()->route('admin.students.index')
                ->with('success', 'Student deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete student. ' . $e->getMessage());
        }
    }

    /**
     * Export students list to CSV.
     */
    public function export(Request $request)
    {
        $students = Student::with(['user', 'parent.user'])->get();

        $filename = 'students_export_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($students) {
            $file = fopen('php://output', 'w');

            // Header row
            fputcsv($file, [
                'Student ID', 'Name', 'Email', 'Phone', 'IC Number',
                'Gender', 'School', 'Grade', 'Parent Name', 'Registration Type',
                'Approval Status', 'Status'
            ]);

            // Data rows
            foreach ($students as $s) {
                fputcsv($file, [
                    $s->student_id,
                    $s->user->name,
                    $s->user->email,
                    '="' . $s->user->phone . '"',
                    $this->formatIcNumber($s->ic_number),
                    $s->gender,
                    $s->school_name,
                    $s->grade_level,
                    $s->parent?->user->name ?? 'N/A',
                    $s->registration_type,
                    $s->approval_status,
                    $s->user->status,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Calculate attendance rate for student.
     */
    private function calculateAttendanceRate(Student $student): float
    {
        $totalClasses = $student->attendance()->count();
        if ($totalClasses === 0) {
            return 0;
        }

        $presentClasses = $student->attendance()
            ->whereIn('status', ['present', 'late'])
            ->count();

        return round(($presentClasses / $totalClasses) * 100, 1);
    }

    /**
     * Format IC number with hyphens for display
     */
    private function formatIcNumber($icNumber): string
    {
        if (empty($icNumber) || strlen($icNumber) !== 12) {
            return $icNumber;
        }

        return substr($icNumber, 0, 6) . '-' . substr($icNumber, 6, 2) . '-' . substr($icNumber, 8, 4);
    }
}
