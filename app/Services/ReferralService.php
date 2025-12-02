<?php

namespace App\Services;

use App\Models\Referral;
use App\Models\ReferralVoucher;
use App\Models\Student;
use App\Models\Notification;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ReferralService
{
    protected $whatsappService;
    protected $voucherAmount = 50.00; // RM50 voucher
    protected $voucherValidityDays = 90; // 90 days validity

    public function __construct(WhatsAppService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    /**
     * Process referral when new student registers with referral code.
     */
    public function processReferral(Student $referredStudent, string $referralCode): ?Referral
    {
        // Find referrer student by referral code
        $referrer = Student::where('referral_code', $referralCode)
            ->where('approval_status', 'approved')
            ->first();

        if (!$referrer) {
            Log::warning("Invalid referral code: {$referralCode}");
            return null;
        }

        // Check if student is not referring themselves
        if ($referrer->id === $referredStudent->id) {
            Log::warning("Student tried to refer themselves");
            return null;
        }

        // Check if this referral doesn't already exist
        $existingReferral = Referral::where('referrer_student_id', $referrer->id)
            ->where('referred_student_id', $referredStudent->id)
            ->first();

        if ($existingReferral) {
            Log::info("Referral already exists");
            return $existingReferral;
        }

        // Create pending referral
        $referral = Referral::create([
            'referrer_student_id' => $referrer->id,
            'referred_student_id' => $referredStudent->id,
            'referral_code' => $referralCode,
            'status' => 'pending',
        ]);

        // Update referred student's referred_by field
        $referredStudent->update(['referred_by' => $referrer->id]);

        Log::info("Referral created: {$referral->id}");
        return $referral;
    }

    /**
     * Complete referral and generate voucher (after referred student's first payment).
     */
    public function completeReferral(Referral $referral): ReferralVoucher
    {
        // Update referral status
        $referral->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        // Generate voucher for referrer
        $voucher = $this->generateVoucher($referral, $referral->referrer_student_id);

        // Send notifications
        $this->sendReferralCompletedNotification($referral, $voucher);

        return $voucher;
    }

    /**
     * Generate voucher for a student.
     */
    public function generateVoucher(Referral $referral, int $studentId): ReferralVoucher
    {
        $voucherCode = $this->generateUniqueVoucherCode();

        return ReferralVoucher::create([
            'referral_id' => $referral->id,
            'student_id' => $studentId,
            'voucher_code' => $voucherCode,
            'amount' => $this->voucherAmount,
            'status' => 'active',
            'expires_at' => now()->addDays($this->voucherValidityDays),
        ]);
    }

    /**
     * Manually generate voucher (admin function).
     */
    public function generateManualVoucher(int $studentId, float $amount, ?string $expiresAt, string $reason): ReferralVoucher
    {
        $voucherCode = $this->generateUniqueVoucherCode();

        // Create a placeholder referral for manual vouchers
        $referral = Referral::create([
            'referrer_student_id' => $studentId,
            'referred_student_id' => $studentId, // Self-reference for manual vouchers
            'referral_code' => 'MANUAL-' . Str::upper(Str::random(6)),
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        return ReferralVoucher::create([
            'referral_id' => $referral->id,
            'student_id' => $studentId,
            'voucher_code' => $voucherCode,
            'amount' => $amount,
            'status' => 'active',
            'expires_at' => $expiresAt ? \Carbon\Carbon::parse($expiresAt) : now()->addDays($this->voucherValidityDays),
        ]);
    }

    /**
     * Redeem voucher on invoice.
     */
    public function redeemVoucher(string $voucherCode, int $invoiceId): array
    {
        $voucher = ReferralVoucher::where('voucher_code', $voucherCode)
            ->where('status', 'active')
            ->where(function($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>=', now());
            })
            ->first();

        if (!$voucher) {
            return [
                'success' => false,
                'message' => 'Voucher not found, already used, or expired.',
            ];
        }

        // Mark voucher as used
        $voucher->update([
            'status' => 'used',
            'used_at' => now(),
            'used_on_invoice_id' => $invoiceId,
        ]);

        return [
            'success' => true,
            'message' => 'Voucher redeemed successfully.',
            'discount_amount' => $voucher->amount,
            'voucher' => $voucher,
        ];
    }

    /**
     * Validate voucher code.
     */
    public function validateVoucher(string $voucherCode, ?int $studentId = null): array
    {
        $query = ReferralVoucher::where('voucher_code', $voucherCode);

        // If student ID provided, check if voucher belongs to them
        if ($studentId) {
            $query->where('student_id', $studentId);
        }

        $voucher = $query->first();

        if (!$voucher) {
            return ['valid' => false, 'message' => 'Voucher not found.'];
        }

        if ($voucher->status === 'used') {
            return ['valid' => false, 'message' => 'Voucher has already been used.'];
        }

        if ($voucher->status === 'expired') {
            return ['valid' => false, 'message' => 'Voucher has expired.'];
        }

        if ($voucher->expires_at && $voucher->expires_at < now()) {
            // Auto-expire
            $voucher->update(['status' => 'expired']);
            return ['valid' => false, 'message' => 'Voucher has expired.'];
        }

        return [
            'valid' => true,
            'message' => 'Voucher is valid.',
            'amount' => $voucher->amount,
            'expires_at' => $voucher->expires_at,
            'voucher' => $voucher,
        ];
    }

    /**
     * Expire all outdated vouchers.
     */
    public function expireOutdatedVouchers(): int
    {
        return ReferralVoucher::where('status', 'active')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->update(['status' => 'expired']);
    }

    /**
     * Get referral statistics for a student.
     */
    public function getStudentReferralStats(Student $student): array
    {
        return [
            'total_referrals' => Referral::where('referrer_student_id', $student->id)->count(),
            'completed_referrals' => Referral::where('referrer_student_id', $student->id)->completed()->count(),
            'pending_referrals' => Referral::where('referrer_student_id', $student->id)->pending()->count(),
            'total_vouchers' => ReferralVoucher::where('student_id', $student->id)->count(),
            'active_vouchers' => ReferralVoucher::where('student_id', $student->id)->active()->count(),
            'total_voucher_value' => ReferralVoucher::where('student_id', $student->id)->active()->sum('amount'),
            'total_earned' => ReferralVoucher::where('student_id', $student->id)->sum('amount'),
            'total_redeemed' => ReferralVoucher::where('student_id', $student->id)->used()->sum('amount'),
        ];
    }

    /**
     * Generate unique voucher code.
     */
    protected function generateUniqueVoucherCode(): string
    {
        do {
            $code = 'VCH-' . Str::upper(Str::random(8));
        } while (ReferralVoucher::where('voucher_code', $code)->exists());

        return $code;
    }

    /**
     * Send notification when referral is completed.
     */
    protected function sendReferralCompletedNotification(Referral $referral, ReferralVoucher $voucher): void
    {
        $referrer = $referral->referrer;
        $referred = $referral->referred;

        if (!$referrer || !$referrer->user) return;

        $message = "🎉 Great news! Your referral for {$referred->user->name} has been successful!\n\n"
                 . "You've earned a RM{$voucher->amount} voucher!\n"
                 . "Voucher Code: {$voucher->voucher_code}\n"
                 . "Valid until: {$voucher->expires_at->format('d M Y')}\n\n"
                 . "Use this voucher on your next payment. Thank you for spreading the word about Arena Matriks!";

        // WhatsApp notification
        if ($referrer->user->phone) {
            try {
                $this->whatsappService->send($referrer->user->phone, $message);
            } catch (\Exception $e) {
                Log::error('WhatsApp notification failed: ' . $e->getMessage());
            }
        }

        // In-app notification
        Notification::create([
            'user_id' => $referrer->user_id,
            'type' => 'referral_completed',
            'title' => 'Referral Reward Earned!',
            'message' => "Your referral for {$referred->user->name} is complete. RM{$voucher->amount} voucher code: {$voucher->voucher_code}",
        ]);
    }
}
