<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Parents;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Http\Requests\StaffStudentRegistrationRequest;
use App\Models\Package;

class StudentRegistrationController extends Controller
{
    /**
     * Show staff registration dashboard.
     */
    public function index()
    {
        $pendingCount = Student::where('approval_status', 'pending')->count();
        $todayRegistrations = Student::whereDate('registration_date', today())->count();
        $recentRegistrations = Student::with(['user', 'parent.user'])
            ->latest()
            ->limit(10)
            ->get();

        return view('staff.registration.index', compact('pendingCount', 'todayRegistrations', 'recentRegistrations'));
    }

    /**
     * Show the student registration form.
     */
    public function createStudent()
    {
        $parents = Parents::with('user')
            ->whereHas('user', function ($q) {
                $q->where('status', 'active');
            })
            ->get();

        $packages = Package::active()
            ->with('subjects')
            ->orderBy('name')
            ->get();

        $gradeLevels = $this->getGradeLevelOptions();

        return view('staff.registration.create-student', compact('parents', 'packages', 'gradeLevels'));
    }

    /**
     * Store a new student registration (offline by staff).
     */
    public function storeStudent(StaffStudentRegistrationRequest $request)
    {
        $validated = $request->validated();

        DB::beginTransaction();
        try {
            // Generate temporary password
            $tempPassword = Str::random(8);

            // Create User account
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'password' => Hash::make($tempPassword),
                'status' => 'inactive', // Inactive until approved
                'email_verified_at' => null,
            ]);

            // Assign student role
            $user->assignRole('student');

            // Generate student ID
            $studentId = 'STU-' . date('Y') . '-' . str_pad($user->id, 4, '0', STR_PAD_LEFT);

            // Generate referral code
            $referralCode = strtoupper(Str::random(8));

            // Check if referred by someone
            $referredBy = null;
            if (!empty($validated['referral_code'])) {
                $referrer = Student::where('referral_code', $validated['referral_code'])->first();
                if ($referrer) {
                    $referredBy = $referrer->id;
                }
            }

            // Create Student profile (pending approval by default for staff registration)
            $student = Student::create([
                'user_id' => $user->id,
                'parent_id' => $validated['parent_id'],
                'student_id' => $studentId,
                'ic_number' => $validated['ic_number'],
                'date_of_birth' => $validated['date_of_birth'],
                'gender' => $validated['gender'],
                'school_name' => $validated['school_name'],
                'grade_level' => $validated['grade_level'],
                'address' => $validated['address'],
                'medical_conditions' => $validated['medical_conditions'] ?? null,
                'registration_type' => 'offline',
                'registration_date' => now(),
                'referral_code' => $referralCode,
                'referred_by' => $referredBy,
                'approval_status' => $validated['auto_approve'] ?? false ? 'approved' : 'pending',
                'approved_by' => $validated['auto_approve'] ?? false ? auth()->id() : null,
                'approved_at' => $validated['auto_approve'] ?? false ? now() : null,
                'notes' => $validated['notes'] ?? null,
            ]);

            // If auto-approved, activate user
            if ($validated['auto_approve'] ?? false) {
                $user->update([
                    'status' => 'active',
                    'email_verified_at' => now(),
                ]);
            }

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'create',
                'model_type' => 'Student',
                'model_id' => $student->id,
                'description' => 'Staff registered student: ' . $validated['name'],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            // Return with success and temporary password
            $statusMsg = ($validated['auto_approve'] ?? false)
                ? 'approved and activated'
                : 'pending approval';

            return redirect()->route('staff.registration.create-student')
                ->with('success', "Student registered successfully ({$statusMsg}). Student ID: {$studentId}. Temporary Password: {$tempPassword}. Please provide this to the parent.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to register student. ' . $e->getMessage());
        }
    }

    /**
     * Show the parent registration form.
     */
    public function createParent()
    {
        return view('staff.registration.create-parent');
    }

    /**
     * Store a new parent registration.
     */
    public function storeParent(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20',
            'ic_number' => 'required|string|max:20|unique:parents,ic_number',
            'occupation' => 'nullable|string|max:255',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'postcode' => 'required|string|max:10',
            'relationship' => 'required|in:father,mother,guardian',
            'whatsapp_number' => 'nullable|string|max:20',
            'emergency_contact' => 'nullable|string|max:255',
            'emergency_phone' => 'nullable|string|max:20',
        ]);

        DB::beginTransaction();
        try {
            // Generate temporary password
            $tempPassword = Str::random(8);

            // Create User account
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'password' => Hash::make($tempPassword),
                'status' => 'active',
                'email_verified_at' => now(),
            ]);

            // Assign parent role
            $user->assignRole('parent');

            // Generate parent ID
            $parentId = 'PAR-' . date('Y') . '-' . str_pad($user->id, 4, '0', STR_PAD_LEFT);

            // Create Parent profile
            $parent = Parents::create([
                'user_id' => $user->id,
                'parent_id' => $parentId,
                'ic_number' => $validated['ic_number'],
                'occupation' => $validated['occupation'],
                'address' => $validated['address'],
                'city' => $validated['city'],
                'state' => $validated['state'],
                'postcode' => $validated['postcode'],
                'relationship' => $validated['relationship'],
                'whatsapp_number' => $validated['whatsapp_number'] ?? $validated['phone'],
                'emergency_contact' => $validated['emergency_contact'],
                'emergency_phone' => $validated['emergency_phone'],
                'notification_preference' => ['whatsapp' => true, 'email' => true, 'sms' => false],
            ]);

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'create',
                'model_type' => 'Parent',
                'model_id' => $parent->id,
                'description' => 'Staff registered parent: ' . $validated['name'],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            // Return with success and temporary password
            return redirect()->route('staff.registration.create-parent')
                ->with('success', "Parent registered successfully. Parent ID: {$parentId}. Temporary Password: {$tempPassword}. Please provide this to the parent.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to register parent. ' . $e->getMessage());
        }
    }

    /**
     * Show pending registrations list.
     */
    public function pendingList(Request $request)
    {
        $query = Student::where('approval_status', 'pending')
            ->with(['user', 'parent.user']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($userQ) use ($search) {
                    $userQ->where('name', 'like', "%{$search}%")
                          ->orWhere('email', 'like', "%{$search}%");
                })
                ->orWhere('student_id', 'like', "%{$search}%")
                ->orWhere('ic_number', 'like', "%{$search}%");
            });
        }

        // Filter by registration type
        if ($request->filled('registration_type')) {
            $query->where('registration_type', $request->registration_type);
        }

        $pendingStudents = $query->latest('registration_date')->paginate(15)->withQueryString();

        return view('staff.registration.pending-list', compact('pendingStudents'));
    }

    /**
     * Quick search for existing parents (AJAX).
     */
    public function searchParent(Request $request)
    {
        $search = $request->get('q');

        $parents = Parents::with('user')
            ->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            })
            ->orWhere('ic_number', 'like', "%{$search}%")
            ->limit(10)
            ->get()
            ->map(function ($parent) {
                return [
                    'id' => $parent->id,
                    'text' => $parent->user->name . ' (' . $parent->ic_number . ')',
                    'name' => $parent->user->name,
                    'email' => $parent->user->email,
                    'phone' => $parent->user->phone,
                    'address' => $parent->address,
                ];
            });

        return response()->json(['results' => $parents]);
    }

    /**
     * Get grade level options.
     */
    private function getGradeLevelOptions(): array
    {
        return [
            'Standard 1' => 'Standard 1',
            'Standard 2' => 'Standard 2',
            'Standard 3' => 'Standard 3',
            'Standard 4' => 'Standard 4',
            'Standard 5' => 'Standard 5',
            'Standard 6' => 'Standard 6',
            'Form 1' => 'Form 1',
            'Form 2' => 'Form 2',
            'Form 3' => 'Form 3',
            'Form 4' => 'Form 4',
            'Form 5' => 'Form 5',
            'Pre-University' => 'Pre-University',
        ];
    }
}
