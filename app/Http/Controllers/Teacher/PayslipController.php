<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\TeacherPayslip;
use Illuminate\Http\Request;

class PayslipController extends Controller
{
    /**
     * Display a listing of teacher's own payslips.
     */
    public function index(Request $request)
    {
        $teacher = auth()->user()->teacher;

        if (!$teacher) {
            abort(403, 'Teacher profile not found.');
        }

        $query = TeacherPayslip::where('teacher_id', $teacher->id);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by year
        if ($request->filled('year')) {
            $query->whereYear('period_start', $request->year);
        }

        // Search by payslip number
        if ($request->filled('search')) {
            $query->where('payslip_number', 'like', '%' . $request->search . '%');
        }

        $payslips = $query->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        // Get statistics
        $stats = [
            'total_payslips' => TeacherPayslip::where('teacher_id', $teacher->id)->count(),
            'approved' => TeacherPayslip::where('teacher_id', $teacher->id)->approved()->count(),
            'paid' => TeacherPayslip::where('teacher_id', $teacher->id)->paid()->count(),
            'total_earned' => TeacherPayslip::where('teacher_id', $teacher->id)->paid()->sum('net_pay'),
        ];

        // Get available years for filter
        $years = TeacherPayslip::where('teacher_id', $teacher->id)
            ->selectRaw('YEAR(period_start) as year')
            ->distinct()
            ->pluck('year')
            ->sort()
            ->reverse();

        return view('teacher.payslips.index', compact('payslips', 'stats', 'years'));
    }

    /**
     * Display the specified payslip.
     */
    public function show(TeacherPayslip $payslip)
    {
        $teacher = auth()->user()->teacher;

        // Ensure teacher can only view their own payslips
        if ($payslip->teacher_id !== $teacher->id) {
            abort(403, 'Unauthorized access.');
        }

        return view('teacher.payslips.show', compact('payslip'));
    }

    /**
     * Print payslip.
     */
    public function print(TeacherPayslip $payslip)
    {
        $teacher = auth()->user()->teacher;

        // Ensure teacher can only print their own payslips
        if ($payslip->teacher_id !== $teacher->id) {
            abort(403, 'Unauthorized access.');
        }

        return view('teacher.payslips.print', compact('payslip'));
    }
}
