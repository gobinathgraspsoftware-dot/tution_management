<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\WhatsappService;
use App\Helpers\CountryCodeHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class ResetPasswordController extends Controller
{
    protected $whatsappService;

    public function __construct(WhatsappService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    /**
     * Show reset password form
     */
    public function showResetForm(Request $request)
    {
        $phone = $request->phone;
        $countryCode = $request->country_code ?? CountryCodeHelper::getDefaultCountryCode();

        // Extract country and phone if full number provided
        if ($phone && !$countryCode) {
            $extracted = CountryCodeHelper::extractCountryCode($phone);
            $countryCode = $extracted['country_code'];
            $phone = $extracted['number'];
        }

        $countryInfo = CountryCodeHelper::getCountryInfo($countryCode);

        return view('auth.reset-password', compact('phone', 'countryCode', 'countryInfo'));
    }

    /**
     * Reset password
     */
    public function reset(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'otp' => 'required|numeric|digits:6',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'otp.required' => 'OTP is required.',
            'otp.digits' => 'OTP must be 6 digits.',
            'password.required' => 'New password is required.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
        ]);

        // Format phone number (remove any non-digits)
        $phone = preg_replace('/[^0-9]/', '', $request->phone);

        // Check if OTP exists and is valid (within 15 minutes)
        $passwordReset = DB::table('password_reset_tokens')
            ->where('phone', 'LIKE', '%' . substr($phone, -8))
            ->where('created_at', '>=', now()->subMinutes(15))
            ->first();

        if (!$passwordReset) {
            return back()->withErrors([
                'otp' => 'OTP has expired. Please request a new one.',
            ])->withInput(['phone' => $request->phone]);
        }

        // Verify OTP
        if ($passwordReset->otp != $request->otp) {
            return back()->withErrors([
                'otp' => 'Invalid OTP. Please check and try again.',
            ])->withInput(['phone' => $request->phone]);
        }

        // Find user by phone number
        $user = User::where('phone', 'LIKE', '%' . substr($phone, -8))->first();

        if (!$user) {
            return back()->withErrors([
                'phone' => 'User not found.',
            ]);
        }

        // Update password
        $user->update([
            'password' => Hash::make($request->password),
            'password_view' => $request->password, // Store plain text password as per your project
        ]);

        // Reset failed login attempts if method exists
        if (method_exists($user, 'resetLoginAttempts')) {
            $user->resetLoginAttempts();
        }

        // Delete the password reset token
        DB::table('password_reset_tokens')
            ->where('phone', $passwordReset->phone)
            ->delete();

        // Log activity
        activity()
            ->causedBy($user)
            ->withProperties(['phone' => $user->phone])
            ->log('Password was reset via WhatsApp OTP');

        // Extract country code for confirmation message
        $extracted = CountryCodeHelper::extractCountryCode($user->phone);
        $countryInfo = CountryCodeHelper::getCountryInfo($extracted['country_code']);
        $countryFlag = $countryInfo['flag'] ?? '🌍';

        // Send confirmation via WhatsApp
        $message = "✅ *Password Reset Successful*\n\n";
        $message .= "Hello {$user->name},\n\n";
        $message .= "Your password has been successfully reset.\n";
        $message .= "You can now login with your new password.\n\n";
        $message .= "If you did not make this change, please contact us immediately.\n\n";
        $message .= "📱 Phone: {$countryFlag} {$user->phone}\n\n";
        $message .= "Thank you,\n";
        $message .= "Arena Matriks Edu Group";

        // IMPORTANT: Remove + sign from phone number for WhatsApp API
        $whatsappPhone = str_replace('+', '', $user->phone);
        $this->whatsappService->send($whatsappPhone, $message);

        return redirect()->route('login')
            ->with('success', 'Your password has been reset successfully. Please login with your new password.');
    }
}
