<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\OnlineStudentRegistrationRequest;
use App\Models\User;
use App\Models\Parents;
use App\Models\Student;
use App\Models\Package;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class OnlineRegistrationController extends Controller
{
    /**
     * Show the registration landing page.
     */
    public function index()
    {
        $packages = Package::active()
            ->with('subjects')
            ->orderBy('name')
            ->get();

        return view('public.registration.index', compact('packages'));
    }

    /**
     * Show the student registration form.
     */
    public function showStudentForm(Request $request)
    {
        $packages = Package::active()
            ->with('subjects')
            ->orderBy('name')
            ->get();

        $gradeLevels = $this->getGradeLevelOptions();
        $referralCode = $request->get('ref');

        return view('public.registration.student', compact('packages', 'gradeLevels', 'referralCode'));
    }

    /**
     * Process online student registration.
     */
    public function registerStudent(OnlineStudentRegistrationRequest $request)
    {
        $validated = $request->validated();

        DB::beginTransaction();
        try {
            // Check if parent email already exists
            $existingParentUser = User::where('email', $validated['parent_email'])->first();

            if ($existingParentUser) {
                // Verify parent has parent role
                if (!$existingParentUser->hasRole('parent')) {
                    return back()->withInput()
                        ->with('error', 'This email is already registered but not as a parent account. Please use a different email or contact support.');
                }

                $parent = Parents::where('user_id', $existingParentUser->id)->first();
                $parentIsNew = false;
            } else {
                // Create new parent user
                $parentTempPassword = Str::random(8);

                $parentUser = User::create([
                    'name' => $validated['parent_name'],
                    'email' => $validated['parent_email'],
                    'phone' => $validated['parent_phone'],
                    'password' => Hash::make($parentTempPassword),
                    'status' => 'active',
                    'email_verified_at' => now(),
                ]);

                $parentUser->assignRole('parent');

                // Generate parent ID
                $parentId = 'PAR-' . date('Y') . '-' . str_pad($parentUser->id, 4, '0', STR_PAD_LEFT);

                // Create Parent profile
                $parent = Parents::create([
                    'user_id' => $parentUser->id,
                    'parent_id' => $parentId,
                    'ic_number' => $validated['parent_ic'] ?? null,
                    'address' => $validated['address'],
                    'city' => $validated['city'] ?? null,
                    'state' => $validated['state'] ?? null,
                    'postcode' => $validated['postcode'] ?? null,
                    'relationship' => $validated['relationship'],
                    'whatsapp_number' => $validated['parent_whatsapp'] ?? $validated['parent_phone'],
                    'notification_preference' => ['whatsapp' => true, 'email' => true, 'sms' => false],
                ]);

                $parentIsNew = true;
            }

            // Create student user account
            $studentTempPassword = Str::random(8);

            // Generate unique student email if not provided
            $studentEmail = $validated['student_email'] ??
                strtolower(Str::slug($validated['student_name'], '.')) . '.' . Str::random(4) . '@student.arenamatriks.edu.my';

            $studentUser = User::create([
                'name' => $validated['student_name'],
                'email' => $studentEmail,
                'phone' => $validated['student_phone'] ?? $validated['parent_phone'],
                'password' => Hash::make($studentTempPassword),
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
                'ic_number' => $validated['student_ic'],
                'date_of_birth' => $validated['date_of_birth'],
                'gender' => $validated['gender'],
                'school_name' => $validated['school_name'],
                'grade_level' => $validated['grade_level'],
                'address' => $validated['address'],
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
                'user_id' => null,
                'action' => 'create',
                'model_type' => 'Student',
                'model_id' => $student->id,
                'description' => 'Online student registration: ' . $validated['student_name'],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            // Store success data in session
            $successData = [
                'student_name' => $validated['student_name'],
                'student_id' => $studentId,
                'student_email' => $studentEmail,
                'student_temp_password' => $studentTempPassword,
                'parent_name' => $validated['parent_name'],
                'parent_email' => $validated['parent_email'],
                'parent_is_new' => $parentIsNew,
            ];

            if ($parentIsNew) {
                $successData['parent_temp_password'] = $parentTempPassword;
            }

            return redirect()->route('public.registration.success')
                ->with('registration_success', $successData);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Registration failed. Please try again. Error: ' . $e->getMessage());
        }
    }

    /**
     * Show registration success page.
     */
    public function success()
    {
        $data = session('registration_success');

        if (!$data) {
            return redirect()->route('public.registration.index');
        }

        return view('public.registration.success', compact('data'));
    }

    /**
     * Validate referral code via AJAX.
     */
    public function validateReferralCode(Request $request)
    {
        $code = $request->get('code');

        $referrer = Student::where('referral_code', $code)
            ->whereHas('user', function ($q) {
                $q->where('status', 'active');
            })
            ->with('user')
            ->first();

        if ($referrer) {
            return response()->json([
                'valid' => true,
                'referrer_name' => $referrer->user->name,
                'message' => 'Valid referral code! You will receive RM50 discount upon approval.'
            ]);
        }

        return response()->json([
            'valid' => false,
            'message' => 'Invalid referral code.'
        ]);
    }

    /**
     * Check if email already exists via AJAX.
     */
    public function checkEmail(Request $request)
    {
        $email = $request->get('email');
        $type = $request->get('type', 'parent'); // parent or student

        $exists = User::where('email', $email)->exists();

        if ($exists && $type === 'parent') {
            $user = User::where('email', $email)->first();
            $isParent = $user->hasRole('parent');

            return response()->json([
                'exists' => true,
                'is_parent' => $isParent,
                'message' => $isParent
                    ? 'This email is already registered as a parent. The student will be linked to this account.'
                    : 'This email is already registered but not as a parent account.'
            ]);
        }

        return response()->json([
            'exists' => $exists,
            'message' => $exists ? 'This email is already registered.' : ''
        ]);
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
