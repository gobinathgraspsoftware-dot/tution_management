<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\ActivityLog;
use App\Models\Enrollment;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\StudentAttendance;
use App\Models\Referral;
use App\Models\ReferralVoucher;
use App\Models\TrialClass;
use App\Models\StudentReview;
use Illuminate\Http\Request;

class StudentProfileController extends Controller
{
    /**
     * Display comprehensive student profile.
     */
    public function show(Student $student)
    {
        $student->load([
            'user',
            'parent.user',
            'referrer.user',
            'approver',
            'enrollments.class.subject',
            'enrollments.package',
            'invoices' => fn($q) => $q->latest()->take(10),
            'payments' => fn($q) => $q->latest()->take(10),
            'reviews.class',
            'reviews.teacher.user',
            'trialClasses.class',
            'referralsAsReferrer.referred.user',
            'referralVouchers',
        ]);

        // Get statistics
        $stats = [
            'total_enrollments' => $student->enrollments()->count(),
            'active_enrollments' => $student->enrollments()->where('status', 'active')->count(),
            'total_paid' => $student->payments()->where('status', 'completed')->sum('amount'),
            'pending_amount' => $student->invoices()->where('status', 'pending')->sum('total_amount'),
            'attendance_rate' => $this->calculateAttendanceRate($student),
            'total_referrals' => $student->referralsAsReferrer()->where('status', 'completed')->count(),
            'voucher_balance' => $student->referralVouchers()->where('status', 'active')->sum('amount'),
            'reviews_count' => $student->reviews()->count(),
            'average_rating' => $student->reviews()->avg('rating') ?? 0,
        ];

        // Get referred students
        $referredStudents = Student::where('referred_by', $student->id)
            ->with('user')
            ->get();

        return view('admin.students.profile', compact('student', 'stats', 'referredStudents'));
    }

    /**
     * Display student history timeline.
     */
    public function history(Student $student)
    {
        $student->load(['user', 'parent.user']);

        // Get all activity logs for this student
        $activities = ActivityLog::where(function($q) use ($student) {
            $q->where('model_type', 'Student')
              ->where('model_id', $student->id);
        })->orWhere(function($q) use ($student) {
            $q->where('model_type', 'User')
              ->where('model_id', $student->user_id);
        })->orderBy('created_at', 'desc')
          ->paginate(20);

        // Get enrollment history
        $enrollmentHistory = Enrollment::where('student_id', $student->id)
            ->with(['class.subject', 'package'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Get payment history
        $paymentHistory = Payment::where('student_id', $student->id)
            ->with('invoice')
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();

        // Get attendance summary
        $attendanceSummary = StudentAttendance::where('student_id', $student->id)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();

        // Get trial class history
        $trialHistory = TrialClass::where('student_id', $student->id)
            ->with('class.subject')
            ->orderBy('created_at', 'desc')
            ->get();

        // Get referral history
        $referralHistory = Referral::where('referrer_student_id', $student->id)
            ->with('referred.user')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.students.history', compact(
            'student',
            'activities',
            'enrollmentHistory',
            'paymentHistory',
            'attendanceSummary',
            'trialHistory',
            'referralHistory'
        ));
    }

    /**
     * Generate new referral code for student.
     */
    public function regenerateReferralCode(Student $student)
    {
        $newCode = $this->generateUniqueReferralCode($student);
        $oldCode = $student->referral_code;

        $student->update(['referral_code' => $newCode]);

        // Log activity
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'update',
            'model_type' => 'Student',
            'model_id' => $student->id,
            'description' => "Regenerated referral code from {$oldCode} to {$newCode}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return back()->with('success', 'Referral code regenerated successfully. New code: ' . $newCode);
    }

    /**
     * Export student profile as PDF.
     */
    public function exportProfile(Student $student)
    {
        $student->load([
            'user',
            'parent.user',
            'enrollments.class.subject',
            'invoices',
            'payments',
        ]);

        // For now, return JSON. PDF generation can be added later
        return response()->json([
            'student' => $student,
            'generated_at' => now()->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Calculate attendance rate for student.
     */
    protected function calculateAttendanceRate(Student $student): float
    {
        $total = StudentAttendance::where('student_id', $student->id)->count();
        if ($total === 0) return 0;

        $present = StudentAttendance::where('student_id', $student->id)
            ->whereIn('status', ['present', 'late'])
            ->count();

        return round(($present / $total) * 100, 2);
    }

    /**
     * Generate unique referral code.
     */
    protected function generateUniqueReferralCode(Student $student): string
    {
        $prefix = 'REF';
        $code = $prefix . strtoupper(substr(md5($student->id . time()), 0, 6));

        while (Student::where('referral_code', $code)->where('id', '!=', $student->id)->exists()) {
            $code = $prefix . strtoupper(substr(md5(rand()), 0, 6));
        }

        return $code;
    }
}
