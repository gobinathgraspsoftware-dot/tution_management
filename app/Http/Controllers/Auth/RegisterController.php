<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Parents;

class RegisterController extends Controller
{
    /**
     * Show registration form
     */
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    /**
     * Handle registration
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'ic_number' => 'nullable|string|max:20',
            'occupation' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postcode' => 'nullable|string|max:10',
            'whatsapp_number' => 'nullable|string|max:20',
            'emergency_contact' => 'nullable|string|max:100',
            'emergency_phone' => 'nullable|string|max:20',
            'relationship' => 'required|in:father,mother,guardian,other',
        ]);

        DB::beginTransaction();

        try {
            // Create user account
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'password' => Hash::make($validated['password']),
                'status' => 'active', // Parents are auto-activated
                'email_verified_at' => now(),
            ]);

            // Assign parent role
            $user->assignRole('parent');

            // Generate parent ID
            $parentId = 'PAR-' . str_pad($user->id, 4, '0', STR_PAD_LEFT);

            // Create parent profile
            Parents::create([
                'user_id' => $user->id,
                'parent_id' => $parentId,
                'ic_number' => $validated['ic_number'] ?? null,
                'occupation' => $validated['occupation'] ?? null,
                'address' => $validated['address'] ?? null,
                'city' => $validated['city'] ?? null,
                'state' => $validated['state'] ?? null,
                'postcode' => $validated['postcode'] ?? null,
                'whatsapp_number' => $validated['whatsapp_number'] ?? $validated['phone'],
                'emergency_contact' => $validated['emergency_contact'] ?? null,
                'emergency_phone' => $validated['emergency_phone'] ?? null,
                'relationship' => $validated['relationship'],
                'notification_preference' => json_encode([
                    'whatsapp' => true,
                    'email' => true,
                    'sms' => false,
                ]),
            ]);

            DB::commit();

            // Log activity
            activity()
                ->causedBy($user)
                ->log('Parent registered via website');

            // Auto login the user
            auth()->login($user);

            return redirect()->route('parent.dashboard')
                ->with('success', 'Registration successful! Welcome to Arena Matriks Edu Group.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->withErrors(['error' => 'Registration failed. Please try again.']);
        }
    }
}
