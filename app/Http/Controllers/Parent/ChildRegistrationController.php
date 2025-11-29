<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Http\Requests\ParentChildRegistrationRequest;
use App\Models\User;
use App\Models\Parents;
use App\Models\Student;
use App\Models\Package;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ChildRegistrationController extends Controller
{
    /**
     * Display list of parent's children.
     */
    public function index()
    {
        $parent = Parents::where('user_id', auth()->id())->firstOrFail();

        $children = Student::where('parent_id', $parent->id)
            ->with(['user', 'enrollments.package'])
            ->get();

        return view('parent.children.index', compact('children', 'parent'));
    }

    /**
     * Show child registration form.
     */
    public function create()
    {
        $parent = Parents::where('user_id', auth()->id())->firstOrFail();
        $parent->load('user');

        $packages = Package::active()
            ->with('subjects')
            ->orderBy('name')
            ->get();

        $gradeLevels = $this->getGradeLevelOptions();

        return view('parent.children.register', compact('parent', 'packages', 'gradeLevels'));
    }

    /**
     * Store new child registration.
     */
    public function store(ParentChildRegistrationRequest $request)
    {
        $validated = $request->validated();

        $parent = Parents::where('user_id', auth()->id())->firstOrFail();

        DB::beginTransaction();
        try {
            // Create student user account
            $tempPassword = Str::random(8);

            // Generate unique student email
            $studentEmail = $validated['email'] ??
                strtolower(Str::slug($validated['name'], '.')) . '.' . Str::random(4) . '@student.arenamatriks.edu.my';

            $studentUser = User::create([
                'name' => $validated['name'],
                'email' => $studentEmail,
                'phone' => $validated['phone'] ?? $parent->user->phone,
                'password' => Hash::make($tempPassword),
                'status' => 'inactive', // Inactive until approved
                'email_verified_at' => null,
            ]);

            $studentUser->assignRole('student');

            // Generate student ID
            $studentId = 'STU-' . date('Y') . '-' . str_pad($studentUser->id, 4, '0', STR_PAD_LEFT);

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

            // Create Student profile (pending approval)
            $student = Student::create([
                'user_id' => $studentUser->id,
                'parent_id' => $parent->id,
                'student_id' => $studentId,
                'ic_number' => $validated['ic_number'],
                'date_of_birth' => $validated['date_of_birth'],
                'gender' => $validated['gender'],
                'school_name' => $validated['school_name'],
                'grade_level' => $validated['grade_level'],
                'address' => $validated['address'] ?? $parent->address,
                'medical_conditions' => $validated['medical_conditions'] ?? null,
                'registration_type' => 'online',
                'registration_date' => now(),
                'referral_code' => $referralCode,
                'referred_by' => $referredBy,
                'approval_status' => 'pending',
                'notes' => $validated['notes'] ?? null,
            ]);

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'create',
                'model_type' => 'Student',
                'model_id' => $student->id,
                'description' => 'Parent registered child: ' . $validated['name'],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            return redirect()->route('parent.children.index')
                ->with('success', "Child registered successfully! Student ID: {$studentId}. Temporary Password: {$tempPassword}. Registration is pending approval.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Registration failed. Please try again. Error: ' . $e->getMessage());
        }
    }

    /**
     * Show child details.
     */
    public function show(Student $student)
    {
        $parent = Parents::where('user_id', auth()->id())->firstOrFail();

        // Verify this child belongs to the parent
        if ($student->parent_id !== $parent->id) {
            abort(403, 'Unauthorized access.');
        }

        $student->load(['user', 'enrollments.package', 'attendance', 'invoices']);

        return view('parent.children.show', compact('student'));
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
