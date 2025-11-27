<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class ResetPasswordController extends Controller
{
    /**
     * Show reset password form
     */
    public function showResetForm(Request $request)
    {
        return view('auth.reset-password', [
            'email' => $request->email,
        ]);
    }

    /**
     * Reset password
     */
    public function reset(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|numeric|digits:6',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Check if OTP exists and is valid (within 15 minutes)
        $passwordReset = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('created_at', '>=', now()->subMinutes(15))
            ->first();

        if (!$passwordReset) {
            return back()->withErrors([
                'otp' => 'OTP has expired. Please request a new one.',
            ]);
        }

        // Verify OTP
        if ($passwordReset->otp != $request->otp) {
            return back()->withErrors([
                'otp' => 'Invalid OTP. Please check and try again.',
            ]);
        }

        // Update password
        $user = User::where('email', $request->email)->first();
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Reset failed login attempts
        $user->resetLoginAttempts();

        // Delete the password reset token
        DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->delete();

        // Log activity
        activity()
            ->causedBy($user)
            ->log('Password was reset');

        return redirect()->route('login')
            ->with('success', 'Your password has been reset successfully. Please login with your new password.');
    }

}
