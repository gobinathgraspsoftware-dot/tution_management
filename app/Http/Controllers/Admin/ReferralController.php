<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Referral;
use App\Models\ReferralVoucher;
use App\Models\Student;
use App\Models\ActivityLog;
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
     * Display referral listing.
     */
    public function index(Request $request)
    {
        $query = Referral::with(['referrer.user', 'referred.user', 'vouchers']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('referral_code', 'like', "%{$search}%")
                  ->orWhereHas('referrer.user', fn($q2) => $q2->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('referred.user', fn($q2) => $q2->where('name', 'like', "%{$search}%"));
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $referrals = $query->latest()->paginate(15)->withQueryString();

        // Get statistics
        $stats = [
            'total' => Referral::count(),
            'pending' => Referral::where('status', 'pending')->count(),
            'completed' => Referral::where('status', 'completed')->count(),
            'total_vouchers_issued' => ReferralVoucher::count(),
            'total_vouchers_used' => ReferralVoucher::where('status', 'used')->count(),
            'total_voucher_value' => ReferralVoucher::where('status', 'active')->sum('amount'),
        ];

        return view('admin.referrals.index', compact('referrals', 'stats'));
    }

    /**
     * Display referral details.
     */
    public function show(Referral $referral)
    {
        $referral->load([
            'referrer.user',
            'referrer.enrollments',
            'referred.user',
            'referred.enrollments',
            'vouchers.usedOnInvoice',
        ]);

        return view('admin.referrals.show', compact('referral'));
    }

    /**
     * Complete a pending referral and generate vouchers.
     */
    public function complete(Referral $referral)
    {
        if ($referral->status !== 'pending') {
            return back()->with('error', 'This referral has already been processed.');
        }

        try {
            DB::beginTransaction();

            $this->referralService->completeReferral($referral);

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'update',
                'model_type' => 'Referral',
                'model_id' => $referral->id,
                'description' => 'Completed referral and generated RM50 voucher',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            DB::commit();
            return back()->with('success', 'Referral completed successfully. RM50 voucher generated for referrer.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to complete referral: ' . $e->getMessage());
        }
    }

    /**
     * Cancel a referral.
     */
    public function cancel(Referral $referral)
    {
        if ($referral->status === 'completed') {
            return back()->with('error', 'Cannot cancel a completed referral.');
        }

        $referral->update(['status' => 'cancelled']);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'update',
            'model_type' => 'Referral',
            'model_id' => $referral->id,
            'description' => 'Cancelled referral',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return back()->with('success', 'Referral cancelled successfully.');
    }

    /**
     * Display voucher management page.
     */
    public function vouchers(Request $request)
    {
        $query = ReferralVoucher::with(['referral', 'student.user', 'usedOnInvoice']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('voucher_code', 'like', "%{$search}%")
                  ->orWhereHas('student.user', fn($q2) => $q2->where('name', 'like', "%{$search}%"));
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $vouchers = $query->latest('created_at')->paginate(15)->withQueryString();

        // Statistics
        $stats = [
            'total' => ReferralVoucher::count(),
            'active' => ReferralVoucher::where('status', 'active')->count(),
            'used' => ReferralVoucher::where('status', 'used')->count(),
            'expired' => ReferralVoucher::where('status', 'expired')->count(),
            'active_value' => ReferralVoucher::where('status', 'active')->sum('amount'),
            'used_value' => ReferralVoucher::where('status', 'used')->sum('amount'),
        ];

        return view('admin.referrals.vouchers', compact('vouchers', 'stats'));
    }

    /**
     * Manually generate voucher for a student.
     */
    public function generateVoucher(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'amount' => 'required|numeric|min:1|max:500',
            'expires_at' => 'nullable|date|after:today',
            'reason' => 'required|string|max:255',
        ]);

        try {
            $voucher = $this->referralService->generateManualVoucher(
                $request->student_id,
                $request->amount,
                $request->expires_at,
                $request->reason
            );

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'create',
                'model_type' => 'ReferralVoucher',
                'model_id' => $voucher->id,
                'description' => "Manually generated voucher of RM{$request->amount} for student ID {$request->student_id}. Reason: {$request->reason}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return back()->with('success', "Voucher generated successfully. Code: {$voucher->voucher_code}");

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to generate voucher: ' . $e->getMessage());
        }
    }

    /**
     * Expire a voucher.
     */
    public function expireVoucher(ReferralVoucher $voucher)
    {
        if ($voucher->status !== 'active') {
            return back()->with('error', 'Only active vouchers can be expired.');
        }

        $voucher->update(['status' => 'expired']);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'update',
            'model_type' => 'ReferralVoucher',
            'model_id' => $voucher->id,
            'description' => "Manually expired voucher: {$voucher->voucher_code}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return back()->with('success', 'Voucher expired successfully.');
    }

    /**
     * Export referrals to CSV.
     */
    public function export(Request $request)
    {
        $referrals = Referral::with(['referrer.user', 'referred.user'])
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->get();

        $filename = 'referrals_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($referrals) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Referral Code', 'Referrer', 'Referred Student', 'Status', 'Completed At', 'Created At']);

            foreach ($referrals as $r) {
                fputcsv($file, [
                    $r->id,
                    $r->referral_code,
                    $r->referrer->user->name ?? 'N/A',
                    $r->referred->user->name ?? 'N/A',
                    ucfirst($r->status),
                    $r->completed_at?->format('Y-m-d H:i'),
                    $r->created_at->format('Y-m-d H:i'),
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
