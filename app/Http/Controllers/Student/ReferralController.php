<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Referral;
use App\Models\ReferralVoucher;
use App\Models\Student;
use App\Services\ReferralService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReferralController extends Controller
{
    protected $referralService;

    public function __construct(ReferralService $referralService)
    {
        $this->referralService = $referralService;
    }

    /**
     * Display student's referrals dashboard.
     */
    public function index(Request $request)
    {
        $student = auth()->user()->student;

        // Get referral statistics
        $stats = $this->referralService->getStudentReferralStats($student);

        // Get student's referral code
        $referralCode = $student->referral_code;

        // Get referrals made by student
        $referrals = Referral::where('referrer_student_id', $student->id)
            ->with(['referred.user'])
            ->latest()
            ->paginate(10);

        // Get active vouchers
        $activeVouchers = ReferralVoucher::where('student_id', $student->id)
            ->active()
            ->with('referral.referred.user')
            ->latest()
            ->get();

        // Get used/expired vouchers
        $usedVouchers = ReferralVoucher::where('student_id', $student->id)
            ->whereIn('status', ['used', 'expired'])
            ->with(['referral.referred.user', 'usedOnInvoice'])
            ->latest('used_at')
            ->limit(5)
            ->get();

        return view('student.referrals.index', compact(
            'stats',
            'referralCode',
            'referrals',
            'activeVouchers',
            'usedVouchers'
        ));
    }

    /**
     * Show referral details.
     */
    public function show(Referral $referral)
    {
        $student = auth()->user()->student;

        // Check if student owns this referral
        if ($referral->referrer_student_id !== $student->id) {
            abort(403, 'You do not have access to this referral.');
        }

        $referral->load(['referred.user', 'vouchers']);

        return view('student.referrals.show', compact('referral'));
    }

    /**
     * Display student's vouchers.
     */
    public function vouchers(Request $request)
    {
        $student = auth()->user()->student;

        $filter = $request->get('filter', 'active'); // active, used, expired, all

        $query = ReferralVoucher::where('student_id', $student->id)
            ->with(['referral.referred.user', 'usedOnInvoice']);

        // Apply filters
        switch ($filter) {
            case 'active':
                $query->active();
                break;
            case 'used':
                $query->where('status', 'used');
                break;
            case 'expired':
                $query->where('status', 'expired');
                break;
            // 'all' - no filter
        }

        $vouchers = $query->latest('created_at')->paginate(15)->withQueryString();

        // Statistics - FIXED SYNTAX
        $voucherStats = [
            'total' => ReferralVoucher::where('student_id', $student->id)->count(),
            'active' => ReferralVoucher::where('student_id', $student->id)->active()->count(),
            'used' => ReferralVoucher::where('student_id', $student->id)->used()->count(),
            'expired' => ReferralVoucher::where('student_id', $student->id)->where('status', 'expired')->count(),
            'total_value' => ReferralVoucher::where('student_id', $student->id)->active()->sum('amount'),
            'total_earned' => ReferralVoucher::where('student_id', $student->id)->sum('amount'),
            'total_redeemed' => ReferralVoucher::where('student_id', $student->id)->used()->sum('amount'), // FIXED HERE
        ];

        return view('student.referrals.vouchers', compact('vouchers', 'voucherStats', 'filter'));
    }

    /**
     * Validate voucher code (AJAX).
     */
    public function validateVoucher(Request $request)
    {
        $request->validate([
            'voucher_code' => 'required|string',
        ]);

        $student = auth()->user()->student;
        $result = $this->referralService->validateVoucher($request->voucher_code, $student->id);

        return response()->json($result);
    }

    /**
     * Get referral link for sharing.
     */
    public function getReferralLink()
    {
        $student = auth()->user()->student;

        // Generate referral link (assuming you have a public registration page with referral code parameter)
        $referralLink = route('register') . '?ref=' . $student->referral_code;

        return response()->json([
            'success' => true,
            'referral_code' => $student->referral_code,
            'referral_link' => $referralLink,
            'whatsapp_share_link' => $this->generateWhatsAppShareLink($student),
        ]);
    }

    /**
     * Generate WhatsApp share link.
     */
    protected function generateWhatsAppShareLink(Student $student): string
    {
        $message = "🎓 Join Arena Matriks Edu Group with my referral code and we both get rewards!\n\n"
                 . "Use my code: *{$student->referral_code}*\n\n"
                 . "Register here: " . route('register') . "?ref={$student->referral_code}\n\n"
                 . "🎁 You'll get quality education and I'll earn a voucher. Win-win!";

        return 'https://wa.me/?text=' . urlencode($message);
    }

    /**
     * Copy referral code to clipboard (trigger notification).
     */
    public function copyReferralCode()
    {
        $student = auth()->user()->student;

        return response()->json([
            'success' => true,
            'message' => 'Referral code copied to clipboard!',
            'referral_code' => $student->referral_code,
        ]);
    }

    /**
     * Get referral history with filters.
     */
    public function history(Request $request)
    {
        $student = auth()->user()->student;
        $status = $request->get('status', 'all'); // all, pending, completed

        $query = Referral::where('referrer_student_id', $student->id)
            ->with(['referred.user']);

        // Apply status filter
        if ($status === 'pending') {
            $query->pending();
        } elseif ($status === 'completed') {
            $query->completed();
        }

        $referrals = $query->latest()->paginate(15)->withQueryString();

        return view('student.referrals.history', compact('referrals', 'status'));
    }

    /**
     * Download voucher as PDF (optional feature).
     */
    public function downloadVoucher(ReferralVoucher $voucher)
    {
        $student = auth()->user()->student;

        // Check if student owns this voucher
        if ($voucher->student_id !== $student->id) {
            abort(403, 'You do not have access to this voucher.');
        }

        // Generate PDF (you can implement this later if needed)
        // For now, just return a simple view
        return view('student.referrals.voucher-print', compact('voucher'));
    }
}
