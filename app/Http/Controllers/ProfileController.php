<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;


class ProfileController extends Controller
{
    /**
     * Display the user's profile.
     */
    public function index()
    {
        $user = auth()->user();
        $profile = null;
        $profileType = null;

        // Load the appropriate profile based on role
        if ($user->hasRole(['super-admin', 'admin'])) {
            $profileType = 'admin';
        } elseif ($user->hasRole('staff')) {
            $profile = $user->staff;
            $profileType = 'staff';
        } elseif ($user->hasRole('teacher')) {
            $profile = $user->teacher;
            $profileType = 'teacher';
        } elseif ($user->hasRole('parent')) {
            $profile = $user->parent;
            $profileType = 'parent';
        } elseif ($user->hasRole('student')) {
            $profile = $user->student;
            $profileType = 'student';
        }

        return view('profile.index', compact('user', 'profile', 'profileType'));
    }

    /**
     * Show the form for editing the profile.
     */
    public function edit()
    {
        $user = auth()->user();
        $profile = null;
        $profileType = null;

        // Load the appropriate profile based on role
        if ($user->hasRole(['super-admin', 'admin'])) {
            $profileType = 'admin';
        } elseif ($user->hasRole('staff')) {
            $profile = $user->staff;
            $profileType = 'staff';
        } elseif ($user->hasRole('teacher')) {
            $profile = $user->teacher;
            $profileType = 'teacher';
        } elseif ($user->hasRole('parent')) {
            $profile = $user->parent;
            $profileType = 'parent';
        } elseif ($user->hasRole('student')) {
            $profile = $user->student;
            $profileType = 'student';
        }

        return view('profile.edit', compact('user', 'profile', 'profileType'));
    }

    /**
     * Update the user's profile.
     */
    public function update(Request $request)
    {
        $user = auth()->user();

        // Base validation for all users
        $rules = [
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => 'required|string|max:20',
        ];

        // Add role-specific validation
        if ($user->hasRole('staff')) {
            $rules['address'] = 'nullable|string|max:500';
            $rules['emergency_contact'] = 'nullable|string|max:255';
            $rules['emergency_phone'] = 'nullable|string|max:20';
        } elseif ($user->hasRole('teacher')) {
            $rules['address'] = 'nullable|string|max:500';
            $rules['bio'] = 'nullable|string|max:1000';
            $rules['bank_name'] = 'nullable|string|max:100';
            $rules['bank_account'] = 'nullable|string|max:50';
        } elseif ($user->hasRole('parent')) {
            $rules['occupation'] = 'nullable|string|max:255';
            $rules['address'] = 'required|string|max:500';
            $rules['city'] = 'required|string|max:100';
            $rules['state'] = 'required|string|max:100';
            $rules['postcode'] = 'required|string|max:10';
            $rules['whatsapp_number'] = 'nullable|string|max:20';
            $rules['emergency_contact'] = 'nullable|string|max:255';
            $rules['emergency_phone'] = 'nullable|string|max:20';
            $rules['notification_preference'] = 'nullable|array';
        } elseif ($user->hasRole('student')) {
            $rules['address'] = 'nullable|string|max:500';
            $rules['school_name'] = 'required|string|max:255';
            $rules['grade_level'] = 'required|string|max:50';
        }

        $validated = $request->validate($rules);

        // Update user account
        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
        ]);

        // Update role-specific profile
        if ($user->hasRole('staff') && $user->staff) {
            $user->staff->update([
                'address' => $validated['address'] ?? null,
                'emergency_contact' => $validated['emergency_contact'] ?? null,
                'emergency_phone' => $validated['emergency_phone'] ?? null,
            ]);
        } elseif ($user->hasRole('teacher') && $user->teacher) {
            $user->teacher->update([
                'address' => $validated['address'] ?? null,
                'bio' => $validated['bio'] ?? null,
                'bank_name' => $validated['bank_name'] ?? null,
                'bank_account' => $validated['bank_account'] ?? null,
            ]);
        } elseif ($user->hasRole('parent') && $user->parent) {
            $notificationPrefs = [];
            if (isset($validated['notification_preference'])) {
                foreach (['whatsapp', 'email', 'sms'] as $channel) {
                    $notificationPrefs[$channel] = in_array($channel, $validated['notification_preference']);
                }
            } else {
                $notificationPrefs = $user->parent->notification_preference ?? ['whatsapp' => true, 'email' => true, 'sms' => false];
            }

            $user->parent->update([
                'occupation' => $validated['occupation'] ?? null,
                'address' => $validated['address'],
                'city' => $validated['city'],
                'state' => $validated['state'],
                'postcode' => $validated['postcode'],
                'whatsapp_number' => $validated['whatsapp_number'] ?? $validated['phone'],
                'emergency_contact' => $validated['emergency_contact'] ?? null,
                'emergency_phone' => $validated['emergency_phone'] ?? null,
                'notification_preference' => $notificationPrefs,
            ]);
        } elseif ($user->hasRole('student') && $user->student) {
            $user->student->update([
                'address' => $validated['address'] ?? null,
                'school_name' => $validated['school_name'],
                'grade_level' => $validated['grade_level'],
            ]);
        }

        // Log activity
        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'update',
            'model_type' => 'User',
            'model_id' => $user->id,
            'description' => 'Updated own profile',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('profile.index')
            ->with('success', 'Profile updated successfully.');
    }

    /**
     * Show the change password form.
     */
    public function showChangePasswordForm()
    {
        return view('profile.change-password');
    }

    /**
     * Update the user's password.
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed|different:current_password',
        ]);

        $user = auth()->user();

        // Verify current password
        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        // Update password
        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        // Log activity
        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'password_change',
            'model_type' => 'User',
            'model_id' => $user->id,
            'description' => 'Changed password',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('profile.index')
            ->with('success', 'Password changed successfully.');
    }

    /**
     * Update avatar (if implemented).
     */
    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = auth()->user();

        if ($request->hasFile('avatar')) {
            $avatar = $request->file('avatar');
            $filename = 'avatar_' . $user->id . '_' . time() . '.' . $avatar->getClientOriginalExtension();
            $avatar->move(public_path('uploads/avatars'), $filename);

            // Delete old avatar if exists
            if ($user->avatar && file_exists(public_path('uploads/avatars/' . $user->avatar))) {
                unlink(public_path('uploads/avatars/' . $user->avatar));
            }

            $user->update(['avatar' => $filename]);
        }

        return redirect()->route('profile.index')
            ->with('success', 'Avatar updated successfully.');
    }
}
