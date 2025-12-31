<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Teacher;
use App\Models\User;
use App\Models\Subject;
use App\Models\ActivityLog;
use App\Models\NotificationLog;
use App\Services\WhatsAppService;
use App\Helpers\CountryCodeHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class TeacherController extends Controller
{
    protected $whatsappService;

    public function __construct(WhatsAppService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

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
              ->orWhere('ic_number', 'like', "%{$search}%");
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

        // Check if WhatsApp service is enabled
        $whatsappEnabled = config('notification.whatsapp.enabled', false);

        return view('admin.teachers.create', compact('subjects', 'whatsappEnabled'));
    }

    /**
     * Store a newly created teacher.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'country_code' => 'nullable|string|max:5',
            'phone' => 'required|string|max:20',
            'password' => 'required|string|min:8|confirmed',
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
                Rule::unique('teachers', 'ic_number')->where(function ($query) use ($request) {
                    $cleaned = preg_replace('/[^0-9]/', '', $request->ic_number);
                    return $query->where('ic_number', $cleaned);
                })
            ],
            'address' => 'nullable|string|max:500',
            'qualification' => 'nullable|string|max:500',
            'experience_years' => 'required|integer|min:0|max:50',
            'specialization' => 'nullable|array',
            'specialization.*' => 'exists:subjects,id',
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
            'epf_enabled' => 'nullable|boolean',
            'socso_enabled' => 'nullable|boolean',
            'socso_type' => 'nullable|in:regular,insurance_only',
            'status' => 'required|in:active,inactive,on_leave',
            'send_whatsapp' => 'nullable|boolean',
        ]);

        DB::beginTransaction();
        try {
            // Convert name to UPPERCASE
            $name = strtoupper($validated['name']);

            // Clean IC number (remove hyphens)
            $cleanedIcNumber = preg_replace('/[^0-9]/', '', $validated['ic_number']);

            // Format phone number with country code
            $phoneNumber = null;
            if (!empty($validated['phone'])) {
                $countryCode = $validated['country_code'] ?? CountryCodeHelper::getDefaultCountryCode();
                $phoneNumber = CountryCodeHelper::formatPhoneNumber($countryCode, $validated['phone']);
            }

            // Create User account
            $user = User::create([
                'name' => $name,
                'email' => $validated['email'],
                'phone' => $phoneNumber,
                'password' => Hash::make($validated['password']),
                'password_view' => $validated['password'], // Store plain password for viewing
                'status' => $validated['status'] === 'active' ? 'active' : 'inactive',
                'email_verified_at' => now(),
            ]);

            // Assign teacher role
            $user->assignRole('teacher');

            // Generate teacher ID
            $teacherId = 'TCH-' . date('Y') . '-' . str_pad($user->id, 4, '0', STR_PAD_LEFT);

            // Determine EPF and SOCSO enabled status
            $epfEnabled = $request->boolean('epf_enabled', true);
            $socsoEnabled = $request->boolean('socso_enabled', true);

            // Create Teacher profile
            $teacher = Teacher::create([
                'user_id' => $user->id,
                'teacher_id' => $teacherId,
                'ic_number' => $cleanedIcNumber,
                'address' => $validated['address'] ?? null,
                'qualification' => $validated['qualification'] ?? null,
                'experience_years' => $validated['experience_years'],
                'specialization' => $validated['specialization'] ?? [], // Store as array
                'bio' => $validated['bio'] ?? null,
                'join_date' => $validated['join_date'],
                'employment_type' => $validated['employment_type'],
                'pay_type' => $validated['pay_type'],
                'hourly_rate' => $validated['hourly_rate'] ?? null,
                'monthly_salary' => $validated['monthly_salary'] ?? null,
                'per_class_rate' => $validated['per_class_rate'] ?? null,
                'bank_name' => $validated['bank_name'] ?? null,
                'bank_account' => $validated['bank_account'] ?? null,
                // EPF fields - only use if enabled
                'epf_enabled' => $epfEnabled,
                'epf_number' => $epfEnabled ? ($validated['epf_number'] ?? null) : null,
                // SOCSO fields - only use if enabled
                'socso_enabled' => $socsoEnabled,
                'socso_number' => $socsoEnabled ? ($validated['socso_number'] ?? null) : null,
                'socso_type' => $socsoEnabled ? ($validated['socso_type'] ?? 'regular') : 'regular',
                'status' => $validated['status'],
            ]);

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'create',
                'model_type' => 'Teacher',
                'model_id' => $teacher->id,
                'description' => 'Created teacher: ' . $name,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            // Send WhatsApp notification if requested (after successful commit)
            $whatsappResult = null;
            if ($request->boolean('send_whatsapp') && $phoneNumber) {
                $whatsappResult = $this->sendTeacherWelcomeWhatsApp($user, $teacher, $validated['password']);
            }

            $successMessage = 'Teacher created successfully. Teacher ID: ' . $teacherId;
            if ($whatsappResult) {
                if ($whatsappResult['success']) {
                    $successMessage .= ' WhatsApp notification sent.';
                } else {
                    $successMessage .= ' However, WhatsApp notification failed: ' . ($whatsappResult['error'] ?? 'Unknown error');
                }
            }

            return redirect()->route('admin.teachers.index')
                ->with('success', $successMessage);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Teacher creation failed: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Failed to create teacher. ' . $e->getMessage());
        }
    }

    /**
     * Send WhatsApp welcome notification to teacher.
     */
    protected function sendTeacherWelcomeWhatsApp(User $user, Teacher $teacher, string $plainPassword): array
    {
        try {
            $message = $this->buildWelcomeMessage($user, $teacher, $plainPassword);
            $result = $this->whatsappService->send($user->phone, $message);

            // Log notification
            NotificationLog::create([
                'user_id' => $user->id,
                'channel' => 'whatsapp',
                'recipient' => $user->phone,
                'type' => 'teacher_welcome',
                'subject' => null,
                'message' => $message,
                'status' => $result['success'] ? 'sent' : 'failed',
                'error_message' => $result['error'] ?? null,
                'sent_at' => $result['success'] ? now() : null,
            ]);

            return $result;

        } catch (\Exception $e) {
            Log::error('Teacher WhatsApp notification failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Build welcome message for WhatsApp notification.
     */
    protected function buildWelcomeMessage(User $user, Teacher $teacher, string $plainPassword): string
    {
        $centerName = config('app.name', 'Arena Matriks Edu Group');
        $loginUrl = url('/login');
        $joinDate = $teacher->join_date?->format('d/m/Y') ?? 'N/A';
        $specializations = !empty($teacher->specialization_names)
            ? implode(', ', $teacher->specialization_names)
            : null;

        $message = "🎓 *Welcome to {$centerName}!*\n\n";
        $message .= "Greetings,\n\n";
        $message .= "Dear *{$user->name}*,\n\n";
        $message .= "Congratulations! You have been successfully registered as a *Teacher* at {$centerName}.\n\n";
        $message .= "📋 *Your Account Details:*\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━━\n";
        $message .= "👤 Teacher ID: *{$teacher->teacher_id}*\n";
        $message .= "📧 Email: {$user->email}\n";
        $message .= "🔑 Password: *{$plainPassword}*\n";
        $message .= "📅 Join Date: {$joinDate}\n";

        if ($specializations) {
            $message .= "📚 Subjects: {$specializations}\n";
        }

        $message .= "━━━━━━━━━━━━━━━━━━━━━\n\n";
        $message .= "🔗 *Login Portal:*\n{$loginUrl}\n\n";
        $message .= "⚠️ _Please change your password after your first login for security._\n\n";
        $message .= "If you have any questions, please contact our admin team.\n\n";
        $message .= "Thank you for joining us!\n";
        $message .= "_{$centerName}_";

        return $message;
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
            'total_students' => $teacher->classes()
                ->withCount('enrollments')
                ->get()
                ->sum('enrollments_count'),
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

        // Check if WhatsApp service is enabled
        $whatsappEnabled = config('notification.whatsapp.enabled', false);

        return view('admin.teachers.edit', compact('teacher', 'subjects', 'whatsappEnabled'));
    }

    /**
     * Update the specified teacher.
     */
    public function update(Request $request, Teacher $teacher)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($teacher->user_id)],
            'country_code' => 'nullable|string|max:5',
            'phone' => 'required|string|max:20',
            'password' => 'nullable|string|min:8|confirmed',
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
                Rule::unique('teachers', 'ic_number')->ignore($teacher->id)->where(function ($query) use ($request) {
                    $cleaned = preg_replace('/[^0-9]/', '', $request->ic_number);
                    return $query->where('ic_number', $cleaned);
                })
            ],
            'address' => 'nullable|string|max:500',
            'qualification' => 'nullable|string|max:500',
            'experience_years' => 'required|integer|min:0|max:50',
            'specialization' => 'nullable|array',
            'specialization.*' => 'exists:subjects,id',
            'bio' => 'nullable|string|max:1000',
            'join_date' => 'required|date',
            'employment_type' => 'required|in:full_time,part_time,contract',
            'pay_type' => 'required|in:hourly,monthly,per_class',
            'hourly_rate' => 'nullable|numeric|min:0',
            'monthly_salary' => 'nullable|numeric|min:0',
            'per_class_rate' => 'nullable|numeric|min:0',
            'bank_name' => 'nullable|string|max:100',
            'bank_account' => 'nullable|string|max:50',
            // EPF/SOCSO fields are nullable - may not be present when toggles are disabled
            'epf_number' => 'nullable|string|max:50',
            'socso_number' => 'nullable|string|max:50',
            'epf_enabled' => 'nullable|boolean',
            'socso_enabled' => 'nullable|boolean',
            'socso_type' => 'nullable|in:regular,insurance_only',
            'status' => 'required|in:active,inactive,on_leave',
            'send_whatsapp' => 'nullable|boolean',
        ]);

        DB::beginTransaction();
        try {
            // Convert name to UPPERCASE
            $name = strtoupper($validated['name']);

            // Clean IC number (remove hyphens)
            $cleanedIcNumber = preg_replace('/[^0-9]/', '', $validated['ic_number']);

            // Format phone number with country code
            $phoneNumber = null;
            if (!empty($validated['phone'])) {
                $countryCode = $validated['country_code'] ?? CountryCodeHelper::getDefaultCountryCode();
                $phoneNumber = CountryCodeHelper::formatPhoneNumber($countryCode, $validated['phone']);
            }

            // Update User account
            $userData = [
                'name' => $name,
                'email' => $validated['email'],
                'phone' => $phoneNumber,
                'status' => $validated['status'] === 'active' ? 'active' : 'inactive',
            ];

            // Only update password if provided
            $passwordChanged = false;
            $newPassword = null;
            if (!empty($validated['password'])) {
                $userData['password'] = Hash::make($validated['password']);
                $userData['password_view'] = $validated['password'];
                $passwordChanged = true;
                $newPassword = $validated['password'];
            }

            $teacher->user->update($userData);

            // ================================================================
            // FIX: Handle EPF/SOCSO fields safely when toggles are disabled
            // When form fields are disabled by JavaScript, they are NOT submitted
            // with the form, so we need to use null coalescing and preserve values
            // ================================================================

            // Determine EPF enabled status (checkbox sends "1" when checked, nothing when unchecked)
            $epfEnabled = $request->boolean('epf_enabled', false);

            // Determine SOCSO enabled status
            $socsoEnabled = $request->boolean('socso_enabled', false);

            // Build teacher update data
            $teacherData = [
                'ic_number' => $cleanedIcNumber,
                'address' => $validated['address'] ?? null,
                'qualification' => $validated['qualification'] ?? null,
                'experience_years' => $validated['experience_years'],
                'specialization' => $validated['specialization'] ?? [], // Store as array
                'bio' => $validated['bio'] ?? null,
                'join_date' => $validated['join_date'],
                'employment_type' => $validated['employment_type'],
                'pay_type' => $validated['pay_type'],
                'hourly_rate' => $validated['hourly_rate'] ?? null,
                'monthly_salary' => $validated['monthly_salary'] ?? null,
                'per_class_rate' => $validated['per_class_rate'] ?? null,
                'bank_name' => $validated['bank_name'] ?? null,
                'bank_account' => $validated['bank_account'] ?? null,
                'status' => $validated['status'],
                // EPF/SOCSO enabled flags
                'epf_enabled' => $epfEnabled,
                'socso_enabled' => $socsoEnabled,
            ];

            // Handle EPF number: Use submitted value if enabled, otherwise preserve existing or null
            if ($epfEnabled) {
                // EPF is enabled - use the submitted value (from validated or request)
                // Use null coalescing to safely get value (may be missing if just enabled)
                $teacherData['epf_number'] = $validated['epf_number'] ?? $request->input('epf_number') ?? $teacher->epf_number;
            } else {
                // EPF is disabled - preserve existing value (don't clear it)
                // This allows re-enabling without re-entering the number
                $teacherData['epf_number'] = $teacher->epf_number;
            }

            // Handle SOCSO fields: Use submitted values if enabled, otherwise preserve existing
            if ($socsoEnabled) {
                // SOCSO is enabled - use the submitted values
                $teacherData['socso_number'] = $validated['socso_number'] ?? $request->input('socso_number') ?? $teacher->socso_number;
                $teacherData['socso_type'] = $validated['socso_type'] ?? $request->input('socso_type') ?? $teacher->socso_type ?? 'regular';
            } else {
                // SOCSO is disabled - preserve existing values
                $teacherData['socso_number'] = $teacher->socso_number;
                $teacherData['socso_type'] = $teacher->socso_type ?? 'regular';
            }

            // Update Teacher profile
            $teacher->update($teacherData);

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'update',
                'model_type' => 'Teacher',
                'model_id' => $teacher->id,
                'description' => 'Updated teacher: ' . $name,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            // Send WhatsApp notification if password changed and requested
            $whatsappResult = null;
            if ($request->boolean('send_whatsapp') && $passwordChanged && $phoneNumber) {
                $whatsappResult = $this->sendPasswordUpdateWhatsApp($teacher->user, $teacher, $newPassword);
            }

            $successMessage = 'Teacher updated successfully.';
            if ($whatsappResult) {
                if ($whatsappResult['success']) {
                    $successMessage .= ' WhatsApp notification sent with new password.';
                } else {
                    $successMessage .= ' However, WhatsApp notification failed: ' . ($whatsappResult['error'] ?? 'Unknown error');
                }
            }

            return redirect()->route('admin.teachers.index')
                ->with('success', $successMessage);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Teacher update failed: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Failed to update teacher. ' . $e->getMessage());
        }
    }

    /**
     * Send WhatsApp notification for password update.
     */
    protected function sendPasswordUpdateWhatsApp(User $user, Teacher $teacher, string $newPassword): array
    {
        try {
            $centerName = config('app.name', 'Arena Matriks Edu Group');
            $loginUrl = url('/login');

            $message = "🔐 *Password Update Notification*\n\n";
            $message .= "Dear *{$user->name}*,\n\n";
            $message .= "Your account password has been updated by the administrator.\n\n";
            $message .= "📋 *Updated Login Details:*\n";
            $message .= "━━━━━━━━━━━━━━━━━━━━━\n";
            $message .= "📧 Email: {$user->email}\n";
            $message .= "🔑 New Password: *{$newPassword}*\n";
            $message .= "━━━━━━━━━━━━━━━━━━━━━\n\n";
            $message .= "🔗 *Login Portal:*\n{$loginUrl}\n\n";
            $message .= "⚠️ _Please change your password after login for security._\n\n";
            $message .= "_{$centerName}_";

            $result = $this->whatsappService->send($user->phone, $message);

            // Log notification
            NotificationLog::create([
                'user_id' => $user->id,
                'channel' => 'whatsapp',
                'recipient' => $user->phone,
                'type' => 'teacher_password_update',
                'subject' => null,
                'message' => $message,
                'status' => $result['success'] ? 'sent' : 'failed',
                'error_message' => $result['error'] ?? null,
                'sent_at' => $result['success'] ? now() : null,
            ]);

            return $result;

        } catch (\Exception $e) {
            Log::error('Teacher password update WhatsApp failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Resend WhatsApp credentials to teacher.
     */
    public function resendWhatsApp(Teacher $teacher)
    {
        $teacher->load('user');

        if (!$teacher->user->phone) {
            return back()->with('error', 'Teacher does not have a phone number registered.');
        }

        // Check if WhatsApp service is enabled
        if (!config('notification.whatsapp.enabled', false)) {
            return back()->with('error', 'WhatsApp service is not enabled.');
        }

        // Get password from password_view or generate new one
        $password = $teacher->user->password_view;
        if (!$password) {
            return back()->with('error', 'Cannot resend credentials. Password is not available. Please update the teacher with a new password.');
        }

        $result = $this->sendTeacherWelcomeWhatsApp($teacher->user, $teacher, $password);

        if ($result['success']) {
            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'resend_whatsapp',
                'model_type' => 'Teacher',
                'model_id' => $teacher->id,
                'description' => 'Resent WhatsApp credentials to teacher: ' . $teacher->user->name,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return back()->with('success', 'WhatsApp notification sent successfully to ' . $teacher->user->phone);
        }

        return back()->with('error', 'Failed to send WhatsApp notification: ' . ($result['error'] ?? 'Unknown error'));
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
                    '="' . $t->user->phone . '"',
                    $t->formatted_ic_number,
                    implode(', ', $t->specialization_names),
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
