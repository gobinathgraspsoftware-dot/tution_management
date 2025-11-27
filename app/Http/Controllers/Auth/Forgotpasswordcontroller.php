<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\User;

class Forgotpasswordcontroller extends Controller
{
    /**
     * Show forgot password form
     */
    public function showLinkRequestForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Send password reset link
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        // Check if user is active
        $user = User::where('email', $request->email)->first();

        if ($user->status !== 'active') {
            return back()->withErrors([
                'email' => 'Your account is not active. Please contact administrator.',
            ]);
        }

        // Generate OTP
        $otp = rand(100000, 999999);

        // Store OTP in password_reset_tokens table
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => bcrypt($otp),
                'otp' => $otp,
                'created_at' => now(),
            ]
        );

        // TODO: Send OTP via email/SMS/WhatsApp
        // For now, we'll just flash it to the session for demo
        session()->flash('otp_sent', $otp); // Remove in production!

        return redirect()->route('password.reset.form', ['email' => $request->email])
            ->with('success', 'OTP has been sent to your email/phone.');
    }
}
