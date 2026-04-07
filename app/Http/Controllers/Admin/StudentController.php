<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Parents;
use App\Models\User;
use App\Models\GradeLevel;          // ← ADDED: for dynamic grade levels
use App\Models\ActivityLog;
use App\Models\NotificationLog;
use App\Services\WhatsAppService;
use App\Helpers\CountryCodeHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class StudentController extends Controller
{
    protected $whatsappService;

    /**
     * Create a new controller instance.
     */
    public function __construct(WhatsAppService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    /**
     * Display a listing of students.
     */
    public function index(Request $request)
    {
        $query = Student::with(['user', 'parent.user', 'gradeLevel']); // ← ADDED 'gradeLevel' eager load

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

        // Filter by grade level — ← CHANGED: now filters by grade_level_id (FK)
        if ($request->filled('grade_level')) {
            $query->where('grade_level_id', $request->grade_level);
        }

        // Filter by gender
        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        $students = $query->latest()->paginate(15)->withQueryString();

        // ← CHANGED: get GradeLevel objects from DB instead of distinct varchar strings
        $gradeLevels = GradeLevel::ordered()->get();

        return view('admin.students.index', compact('students', 'gradeLevels'));
    }

    /**
     * AJAX endpoint to search parents for Select2 dropdown
     */
    public function searchParents(Request $request)
    {
        $search = $request->get('q', '');
        $page = $request->get('page', 1);
        $perPage = 10;

        $query = Parents::with('user')
            ->whereHas('user', function ($q) {
                $q->where('status', 'active');
            });

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
        $parents = Parents::with('user')->whereHas('user', function ($q) {
            $q->where('status', 'active');
        })->take(10)->get();

        // ← CHANGED: load from DB instead of static array
        $gradeLevels = GradeLevel::ordered()->get();

        $countries = CountryCodeHelper::getAllCountries();
        $defaultCountryCode = CountryCodeHelper::getDefaultCountryCode();

        // Check if WhatsApp is enabled
        $whatsappEnabled = config('notification.whatsapp.enabled', false);

        return view('admin.students.create', compact('parents', 'gradeLevels', 'countries', 'defaultCountryCode', 'whatsappEnabled'));
    }

    /**
     * Store a newly created student.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'               => 'required|string|max:255',
            'email'              => 'required|email|unique:users,email',
            'country_code'       => 'nullable|string|max:5',
            'phone'              => 'nullable|string|max:20',
            'password'           => 'required|string|min:8|confirmed',
            'parent_id'          => 'required|exists:parents,id',
            'ic_number'          => [
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
            'date_of_birth'      => 'required|date|before:today',
            'gender'             => 'required|in:male,female',
            'school_name'        => 'required|string|max:255',
            'grade_level_id'     => 'required|exists:grade_levels,id',  // ← CHANGED: was 'grade_level' string
            'address'            => 'nullable|string|max:500',
            'medical_conditions' => 'nullable|string|max:500',
            'registration_type'  => 'required|in:online,offline',
            'notes'              => 'nullable|string|max:1000',
            'status'             => 'required|in:active,inactive',
            'send_whatsapp'      => 'nullable|in:on,1,true',
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
                'name'              => $name,
                'email'             => $validated['email'],
                'phone'             => $phoneNumber,
                'password'          => Hash::make($validated['password']),
                'password_view'     => $validated['password'],
                'status'            => $validated['status'],
                'email_verified_at' => now(),
            ]);

            // Assign student role
            $user->assignRole('student');

            // Generate student ID
            $studentId = 'STU-' . date('Y') . '-' . str_pad($user->id, 4, '0', STR_PAD_LEFT);

            // Generate referral code
            $referralCode = strtoupper(Str::random(8));

            // Create Student profile (auto-approved when created by admin)
            $student = Student::create([
                'user_id'            => $user->id,
                'parent_id'          => $validated['parent_id'],
                'student_id'         => $studentId,
                'ic_number'          => $cleanedIcNumber,
                'date_of_birth'      => $validated['date_of_birth'],
                'gender'             => $validated['gender'],
                'school_name'        => $validated['school_name'],
                'grade_level_id'     => $validated['grade_level_id'],  // ← CHANGED: was 'grade_level'
                'address'            => $validated['address'],
                'medical_conditions' => $validated['medical_conditions'],
                'registration_type'  => $validated['registration_type'],
                'registration_date'  => now(),
                'enrollment_date'    => now(),
                'referral_code'      => $referralCode,
                'notes'              => $validated['notes'],
                'approval_status'    => 'approved',
                'approved_by'        => auth()->id(),
                'approved_at'        => now(),
            ]);

            // Log activity
            ActivityLog::create([
                'user_id'     => auth()->id(),
                'action'      => 'create',
                'model_type'  => 'Student',
                'model_id'    => $student->id,
                'description' => 'Created student: ' . $name,
                'ip_address'  => $request->ip(),
                'user_agent'  => $request->userAgent(),
            ]);

            DB::commit();

            // Send WhatsApp notification if checkbox is checked
            $whatsappResult = null;
            if ($request->has('send_whatsapp') || $request->send_whatsapp) {
                $whatsappResult = $this->sendStudentRegistrationWhatsApp($student, $validated['password']);
            }

            $successMessage = 'Student created successfully.';
            if ($whatsappResult) {
                if ($whatsappResult['success']) {
                    $successMessage .= ' WhatsApp notification sent.';
                } else {
                    $successMessage .= ' WhatsApp notification failed: ' . ($whatsappResult['error'] ?? 'Unknown error');
                }
            }

            return redirect()->route('admin.students.index')
                ->with('success', $successMessage);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to create student. ' . $e->getMessage());
        }
    }

    /**
     * Send WhatsApp notification for student registration.
     */
    protected function sendStudentRegistrationWhatsApp(Student $student, string $password): array
    {
        $student->load(['user', 'parent.user', 'gradeLevel']); // ← ADDED 'gradeLevel'

        // Send to student's phone number
        $recipientPhone = $student->user->phone;
        $recipientName = $student->user->name;

        if (!$recipientPhone) {
            return [
                'success' => false,
                'error' => 'Student does not have a phone number registered.',
            ];
        }

        // Check if WhatsApp service is enabled
        if (!config('notification.whatsapp.enabled', false)) {
            return [
                'success' => false,
                'error' => 'WhatsApp service is not enabled.',
            ];
        }

        try {
            // Build WhatsApp message
            $message = $this->buildStudentRegistrationMessage($student, $password);

            // Format phone number (remove + sign for WhatsApp API)
            $whatsappPhone = str_replace('+', '', $recipientPhone);

            // Send WhatsApp message
            $result = $this->whatsappService->send($whatsappPhone, $message);

            // Log notification
            NotificationLog::create([
                'user_id'       => $student->user_id,
                'channel'       => 'whatsapp',
                'type'          => 'student_registration',
                'recipient'     => $whatsappPhone,
                'subject'       => 'Student Registration Notification',
                'message'       => $message,
                'status'        => $result['success'] ? 'sent' : 'failed',
                'error_message' => $result['error'] ?? null,
                'sent_at'       => $result['success'] ? now() : null,
            ]);

            // Log activity
            ActivityLog::create([
                'user_id'     => auth()->id(),
                'action'      => 'send_whatsapp',
                'model_type'  => 'Student',
                'model_id'    => $student->id,
                'description' => 'Sent WhatsApp registration notification for student: ' . $student->user->name,
                'ip_address'  => request()->ip(),
                'user_agent'  => request()->userAgent(),
                'changes'     => json_encode([
                    'recipient' => $whatsappPhone,
                    'status'    => $result['success'] ? 'sent' : 'failed',
                ]),
            ]);

            return $result;

        } catch (\Exception $e) {
            Log::error('Student registration WhatsApp failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Build WhatsApp message for student registration.
     */
    protected function buildStudentRegistrationMessage(Student $student, string $password): string
    {
        $loginUrl    = url('/login');
        $centreName  = config('app.name', 'Arena Matriks Edu Group');
        $centrePhone = config('app.centre_phone', '03-7972 3663');

        // ← CHANGED: use relationship name instead of varchar field
        $gradeName = $student->gradeLevel->name ?? 'N/A';

        $message  = "🎓 *{$centreName}*\n";
        $message .= "*Student Registration Confirmation*\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━━\n\n";
        $message .= "Dear *{$student->user->name}*,\n\n";
        $message .= "Welcome! Your registration has been successfully completed.\n\n";
        $message .= "📋 *Your Details:*\n";
        $message .= "• Student ID: *{$student->student_id}*\n";
        $message .= "• Grade: {$gradeName}\n";  // ← CHANGED
        $message .= "• School: {$student->school_name}\n";
        $message .= "• Referral Code: `{$student->referral_code}`\n\n";
        $message .= "🔐 *Login Credentials:*\n";
        $message .= "• Email: {$student->user->email}\n";
        $message .= "• Password: {$password}\n";
        $message .= "• Portal: {$loginUrl}\n\n";
        $message .= "⚠️ *Important:* Please keep these credentials safe and do not share them with others.\n\n";
        $message .= "For any enquiries, contact us at:\n";
        $message .= "📞 {$centrePhone}\n\n";
        $message .= "Thank you for choosing {$centreName}!\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━━";

        return $message;
    }

    /**
     * Display the specified student.
     */
    public function show(Student $student)
    {
        $student->load([
            'user',
            'parent.user',
            'gradeLevel',               // ← ADDED
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
            'total_paid'         => $student->payments()->where('status', 'completed')->sum('amount'),
            'pending_amount'     => $student->invoices()->whereIn('status', ['pending', 'partial'])->sum('total_amount'),
            'attendance_rate'    => $this->calculateAttendanceRate($student),
            'active_enrollments' => $student->enrollments()->where('status', 'active')->count(),
        ];

        // Check if WhatsApp is enabled
        $whatsappEnabled = config('notification.whatsapp.enabled', false);

        return view('admin.students.show', compact('student', 'stats', 'whatsappEnabled'));
    }

    /**
     * Show the form for editing the specified student.
     */
    public function edit(Student $student)
    {
        $student->load(['user', 'parent.user', 'gradeLevel']); // ← ADDED 'gradeLevel'

        $parents = Parents::with('user')->whereHas('user', function ($q) {
            $q->where('status', 'active');
        })->take(10)->get();

        // ← CHANGED: load from DB instead of static array
        $gradeLevels = GradeLevel::ordered()->get();

        $countries = CountryCodeHelper::getAllCountries();
        $defaultCountryCode = CountryCodeHelper::getDefaultCountryCode();

        // Extract country code and phone number
        $phoneData = CountryCodeHelper::extractCountryCode($student->user->phone);

        // Check if WhatsApp is enabled
        $whatsappEnabled = config('notification.whatsapp.enabled', false);

        return view('admin.students.edit', compact('student', 'parents', 'gradeLevels', 'countries', 'defaultCountryCode', 'phoneData', 'whatsappEnabled'));
    }

    /**
     * Update the specified student.
     */
    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'name'               => 'required|string|max:255',
            'email'              => ['required', 'email', Rule::unique('users', 'email')->ignore($student->user_id)],
            'country_code'       => 'nullable|string|max:5',
            'phone'              => 'nullable|string|max:20',
            'password'           => 'nullable|string|min:8|confirmed',
            'parent_id'          => 'required|exists:parents,id',
            'ic_number'          => [
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
            'date_of_birth'      => 'required|date|before:today',
            'gender'             => 'required|in:male,female',
            'school_name'        => 'required|string|max:255',
            'grade_level_id'     => 'required|exists:grade_levels,id',  // ← CHANGED: was 'grade_level' string
            'address'            => 'nullable|string|max:500',
            'medical_conditions' => 'nullable|string|max:500',
            'notes'              => 'nullable|string|max:1000',
            'status'             => 'required|in:active,inactive',
            'send_whatsapp'      => 'nullable|in:on,1,true',
        ]);

        DB::beginTransaction();
        try {
            $cleanedIcNumber = preg_replace('/[^0-9]/', '', $validated['ic_number']);

            $phoneNumber = null;
            if (!empty($validated['phone'])) {
                $countryCode = $validated['country_code'] ?? CountryCodeHelper::getDefaultCountryCode();
                $phoneNumber = CountryCodeHelper::formatPhoneNumber($countryCode, $validated['phone']);
            }

            $name = strtoupper($validated['name']);

            $userData = [
                'name'   => $name,
                'email'  => $validated['email'],
                'phone'  => $phoneNumber,
                'status' => $validated['status'],
            ];

            $passwordChanged = false;
            if (!empty($validated['password'])) {
                $userData['password']      = Hash::make($validated['password']);
                $userData['password_view'] = $validated['password'];
                $passwordChanged = true;
            }

            $student->user->update($userData);

            $student->update([
                'parent_id'          => $validated['parent_id'],
                'ic_number'          => $cleanedIcNumber,
                'date_of_birth'      => $validated['date_of_birth'],
                'gender'             => $validated['gender'],
                'school_name'        => $validated['school_name'],
                'grade_level_id'     => $validated['grade_level_id'],  // ← CHANGED: was 'grade_level'
                'address'            => $validated['address'],
                'medical_conditions' => $validated['medical_conditions'],
                'notes'              => $validated['notes'],
            ]);

            // Log activity
            ActivityLog::create([
                'user_id'     => auth()->id(),
                'action'      => 'update',
                'model_type'  => 'Student',
                'model_id'    => $student->id,
                'description' => 'Updated student: ' . $name,
                'ip_address'  => $request->ip(),
                'user_agent'  => $request->userAgent(),
            ]);

            DB::commit();

            // Send WhatsApp notification if password was changed and checkbox is checked
            $whatsappResult = null;
            if ($passwordChanged && ($request->has('send_whatsapp') || $request->send_whatsapp)) {
                $whatsappResult = $this->sendPasswordUpdateWhatsApp($student, $validated['password']);
            }

            $successMessage = 'Student updated successfully.';
            if ($whatsappResult) {
                if ($whatsappResult['success']) {
                    $successMessage .= ' WhatsApp notification sent with new password.';
                } else {
                    $successMessage .= ' WhatsApp notification failed: ' . ($whatsappResult['error'] ?? 'Unknown error');
                }
            }

            return redirect()->route('admin.students.index')
                ->with('success', $successMessage);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to update student. ' . $e->getMessage());
        }
    }

    /**
     * Send WhatsApp notification for password update.
     */
    protected function sendPasswordUpdateWhatsApp(Student $student, string $newPassword): array
    {
        $student->load(['user', 'parent.user']);

        // Send to student's phone number
        $recipientPhone = $student->user->phone;
        $recipientName  = $student->user->name;

        if (!$recipientPhone) {
            return [
                'success' => false,
                'error' => 'Student does not have a phone number registered.',
            ];
        }

        if (!config('notification.whatsapp.enabled', false)) {
            return [
                'success' => false,
                'error' => 'WhatsApp service is not enabled.',
            ];
        }

        try {
            $centreName    = config('app.name', 'Arena Matriks Edu Group');
            $loginUrl      = url('/login');

            $message  = "🔐 *{$centreName}*\n";
            $message .= "*Password Update Notification*\n";
            $message .= "━━━━━━━━━━━━━━━━━━━━━\n\n";
            $message .= "Dear *{$recipientName}*,\n\n";
            $message .= "Your password has been updated.\n\n";
            $message .= "🔐 *New Login Credentials:*\n";
            $message .= "• Email: {$student->user->email}\n";
            $message .= "• Password: {$newPassword}\n";
            $message .= "• Portal: {$loginUrl}\n\n";
            $message .= "⚠️ *Important:* Please keep these credentials safe.\n\n";
            $message .= "Thank you,\n{$centreName}";

            $whatsappPhone = str_replace('+', '', $recipientPhone);

            $result = $this->whatsappService->send($whatsappPhone, $message);

            // Log notification
            NotificationLog::create([
                'user_id'       => $student->user_id,
                'channel'       => 'whatsapp',
                'type'          => 'password_update',
                'recipient'     => $whatsappPhone,
                'subject'       => 'Password Update Notification',
                'message'       => $message,
                'status'        => $result['success'] ? 'sent' : 'failed',
                'error_message' => $result['error'] ?? null,
                'sent_at'       => $result['success'] ? now() : null,
            ]);

            return $result;

        } catch (\Exception $e) {
            Log::error('Student password update WhatsApp failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Resend WhatsApp credentials to student.
     */
    public function resendWhatsApp(Student $student)
    {
        $student->load(['user', 'parent.user', 'gradeLevel']); // ← ADDED 'gradeLevel'

        // Send to student's phone number
        $recipientPhone = $student->user->phone;

        if (!$recipientPhone) {
            return back()->with('error', 'Student does not have a phone number registered.');
        }

        // Check if WhatsApp service is enabled
        if (!config('notification.whatsapp.enabled', false)) {
            return back()->with('error', 'WhatsApp service is not enabled.');
        }

        // Get password from password_view
        $password = $student->user->password_view;
        if (!$password) {
            return back()->with('error', 'Cannot resend credentials. Password is not available. Please update the student with a new password.');
        }

        $result = $this->sendStudentRegistrationWhatsApp($student, $password);

        if ($result['success']) {
            return back()->with('success', 'WhatsApp notification sent successfully to student: ' . $recipientPhone);
        }

        return back()->with('error', 'Failed to send WhatsApp notification: ' . ($result['error'] ?? 'Unknown error'));
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
                'user_id'     => auth()->id(),
                'action'      => 'delete',
                'model_type'  => 'Student',
                'model_id'    => $student->id,
                'description' => 'Deleted student: ' . $studentName,
                'ip_address'  => $request->ip(),
                'user_agent'  => $request->userAgent(),
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
     * Display the student profile page.
     */
    public function profile(Student $student)
    {
        $student->load([
            'user',
            'parent.user',
            'gradeLevel',               // ← ADDED
            'enrollments.package',
            'enrollments.class.subject',
            'invoices' => function ($q) {
                $q->latest()->take(10);
            },
            'payments' => function ($q) {
                $q->latest()->take(10);
            },
            'attendance' => function ($q) {
                $q->latest()->take(10);
            },
            'trialClasses.class',
            'reviews.class',
            'reviews.teacher.user',
            'referralVouchers',
            'referrer.user',
        ]);

        // Get statistics
        $stats = [
            'total_paid'         => $student->payments()->where('status', 'completed')->sum('amount'),
            'pending_amount'     => $student->invoices()->whereIn('status', ['pending', 'partial'])->sum('total_amount'),
            'attendance_rate'    => $this->calculateAttendanceRate($student),
            'active_enrollments' => $student->enrollments()->where('status', 'active')->count(),
            'total_enrollments'  => $student->enrollments()->count(),
            'total_referrals'    => $student->referrals()->where('status', 'completed')->count(),
            'voucher_balance'    => $student->referralVouchers()->where('status', 'active')->sum('amount'),
            'reviews_count'      => $student->reviews()->count(),
            'average_rating'     => $student->reviews()->avg('rating') ?? 0,
        ];

        // Get referred students
        $referredStudents = Student::whereHas('referrer', function ($q) use ($student) {
            $q->where('referrer_id', $student->id);
        })->with('user')->get();

        // Check if WhatsApp is enabled
        $whatsappEnabled = config('notification.whatsapp.enabled', false);

        return view('admin.students.profile', compact('student', 'stats', 'referredStudents', 'whatsappEnabled'));
    }

    /**
     * Display the student history page.
     */
    public function history(Student $student)
    {
        $student->load(['user', 'parent.user']);

        // Get activity logs for this student
        $activities = ActivityLog::where('model_type', 'Student')
            ->where('model_id', $student->id)
            ->with('user')
            ->latest()
            ->paginate(20);

        // Get attendance summary
        $attendanceSummary = [
            'present' => $student->attendance()->where('status', 'present')->count(),
            'absent'  => $student->attendance()->where('status', 'absent')->count(),
            'late'    => $student->attendance()->where('status', 'late')->count(),
            'excused' => $student->attendance()->where('status', 'excused')->count(),
        ];

        // Get enrollment history
        $enrollmentHistory = $student->enrollments()
            ->with(['class.subject', 'package'])
            ->latest()
            ->get();

        // Get payment history
        $paymentHistory = $student->payments()
            ->with('invoice')
            ->latest()
            ->take(20)
            ->get();

        // Get trial class history
        $trialHistory = $student->trialClasses()
            ->with('class')
            ->latest()
            ->get();

        // Get referral history
        $referralHistory = $student->referrals()
            ->with('referred.user')
            ->latest()
            ->get();

        return view('admin.students.history', compact(
            'student',
            'activities',
            'attendanceSummary',
            'enrollmentHistory',
            'paymentHistory',
            'trialHistory',
            'referralHistory'
        ));
    }

    /**
     * Regenerate referral code for student.
     */
    public function regenerateReferral(Request $request, Student $student)
    {
        $oldCode = $student->referral_code;
        $newCode = strtoupper(Str::random(8));

        $student->update([
            'referral_code' => $newCode,
        ]);

        // Log activity
        ActivityLog::create([
            'user_id'     => auth()->id(),
            'action'      => 'update',
            'model_type'  => 'Student',
            'model_id'    => $student->id,
            'description' => 'Regenerated referral code for student: ' . $student->user->name,
            'ip_address'  => $request->ip(),
            'user_agent'  => $request->userAgent(),
            'changes'     => json_encode([
                'old' => ['referral_code' => $oldCode],
                'new' => ['referral_code' => $newCode],
            ]),
        ]);

        return back()->with('success', 'Referral code regenerated successfully. New code: ' . $newCode);
    }

    /**
     * Export students list to CSV.
     */
    public function export(Request $request)
    {
        // ← ADDED 'gradeLevel' to eager load
        $students = Student::with(['user', 'parent.user', 'gradeLevel'])->get();

        $filename = 'students_export_' . date('Y-m-d_His') . '.csv';
        $headers  = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($students) {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'Student ID', 'Name', 'Email', 'Phone', 'IC Number',
                'Gender', 'School', 'Grade', 'Parent Name', 'Registration Type',
                'Approval Status', 'Status'
            ]);

            foreach ($students as $s) {
                fputcsv($file, [
                    $s->student_id,
                    $s->user->name,
                    $s->user->email,
                    '="' . $s->user->phone . '"',
                    $this->formatIcNumber($s->ic_number),
                    $s->gender,
                    $s->school_name,
                    $s->gradeLevel->name ?? 'N/A',  // ← CHANGED: was $s->grade_level
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
