<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\WhatsappService;
use App\Helpers\CountryCodeHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class ForgotPasswordController extends Controller
{
    protected $whatsappService;

    public function __construct(WhatsappService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    /**
     * Show forgot password form
     */
    public function showLinkRequestForm()
    {
        // Get all countries for dropdown (same pattern as ParentController)
        $countries = CountryCodeHelper::getAllCountries();
        $defaultCountryCode = CountryCodeHelper::getDefaultCountryCode();

        return view('auth.forgot-password', compact('countries', 'defaultCountryCode'));
    }

    /**
     * Send password reset OTP via WhatsApp
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'country_code' => 'required|string|max:5',
            'phone' => 'required|string|max:20',
        ], [
            'country_code.required' => 'Country code is required.',
            'phone.required' => 'Phone number is required.',
        ]);

        // Format phone number using helper (same pattern as ParentController)
        $phoneNumber = CountryCodeHelper::formatPhoneNumber(
            $request->country_code,
            $request->phone
        );

        // Check if user exists with this phone number
        $user = User::where('phone', $phoneNumber)
            ->orWhere('phone', 'LIKE', '%' . substr($phoneNumber, -8))
            ->first();

        if (!$user) {
            return back()->withErrors([
                'phone' => 'No account found with this phone number.',
            ])->withInput();
        }

        // Check if user is active
        if ($user->status !== 'active') {
            return back()->withErrors([
                'phone' => 'Your account is not active. Please contact administrator.',
            ])->withInput();
        }

        // Generate 6-digit OTP
        $otp = rand(100000, 999999);

        // Store OTP in password_reset_tokens table
        DB::table('password_reset_tokens')->updateOrInsert(
            ['phone' => $phoneNumber],
            [
                'email' => $user->email,
                'token' => bcrypt($otp),
                'otp' => $otp,
                'created_at' => now(),
            ]
        );

        // Get country info for WhatsApp message
        $countryInfo = CountryCodeHelper::getCountryInfo($request->country_code);
        $countryFlag = $countryInfo['flag'] ?? '🌍';

        // Build WhatsApp message
        $message = "🔐 *Arena Matriks - Password Reset OTP*\n\n";
        $message .= "Hello {$user->name},\n\n";
        $message .= "Your OTP for password reset is: *{$otp}*\n\n";
        $message .= "This OTP is valid for 15 minutes.\n";
        $message .= "If you did not request this, please ignore this message.\n\n";
        $message .= "📱 Phone: {$countryFlag} {$request->country_code} {$request->phone}\n\n";
        $message .= "Thank you,\n";
        $message .= "Arena Matriks Edu Group";

        // IMPORTANT: Remove + sign from phone number for WhatsApp API
        // WhatsappService expects: 60123456789 (not +60123456789)
        $whatsappPhone = str_replace('+', '', $phoneNumber);

        // Send OTP via WhatsApp
        $result = $this->whatsappService->send($whatsappPhone, $message);

        if ($result['success']) {
            // Log activity
            activity()
                ->causedBy($user)
                ->withProperties([
                    'phone' => $phoneNumber,
                    'country_code' => $request->country_code,
                ])
                ->log('Password reset OTP sent via WhatsApp');

            return redirect()
                ->route('password.reset.form', [
                    'phone' => $phoneNumber,
                    'country_code' => $request->country_code
                ])
                ->with('success', 'OTP has been sent to your WhatsApp number.');
        } else {
            // If WhatsApp fails, show OTP in session for demo/development
            if (config('app.debug')) {
                session()->flash('otp_demo', $otp);
                session()->flash('whatsapp_error', $result['error'] ?? 'Unknown error');
                return redirect()
                    ->route('password.reset.form', [
                        'phone' => $phoneNumber,
                        'country_code' => $request->country_code
                    ])
                    ->with('warning', 'WhatsApp service unavailable. Demo OTP displayed for testing.');
            }

            return back()->withErrors([
                'phone' => 'Failed to send OTP: ' . ($result['error'] ?? 'Unknown error'),
            ])->withInput();
        }
    }
}
