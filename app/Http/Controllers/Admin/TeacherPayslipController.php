<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\TeacherPayslip;
use App\Models\ActivityLog;
use App\Services\TeacherSalaryService;
use App\Http\Requests\TeacherPayslipRequest;
use App\Exports\TeacherPayslipExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class TeacherPayslipController extends Controller
{
    protected $salaryService;

    public function __construct(TeacherSalaryService $salaryService)
    {
        $this->salaryService = $salaryService;
    }

    /**
     * Display a listing of payslips.
     */
    public function index(Request $request)
    {
        $query = TeacherPayslip::with('teacher.user');

        // Filter by teacher
        if ($request->filled('teacher_id')) {
            $query->where('teacher_id', $request->teacher_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by month/year
        if ($request->filled('month') && $request->filled('year')) {
            $query->whereMonth('period_start', $request->month)
                  ->whereYear('period_start', $request->year);
        }

        // Search by payslip number
        if ($request->filled('search')) {
            $query->where('payslip_number', 'like', '%' . $request->search . '%');
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $payslips = $query->paginate(20)->withQueryString();

        // Get teachers for filter
        $teachers = Teacher::with('user')->active()->get();

        // Get statistics
        $stats = [
            'total_payslips' => TeacherPayslip::count(),
            'draft' => TeacherPayslip::draft()->count(),
            'approved' => TeacherPayslip::approved()->count(),
            'paid' => TeacherPayslip::paid()->count(),
            'total_amount' => TeacherPayslip::paid()->sum('net_pay'),
        ];

        return view('admin.teacher-payslips.index', compact('payslips', 'teachers', 'stats'));
    }

    /**
     * Show the form for creating a new payslip.
     */
    public function create(Request $request)
    {
        $teachers = Teacher::with('user')->active()->get();

        $teacher = null;
        $calculation = null;
        $periodStart = null;
        $periodEnd = null;

        // If teacher selected, calculate salary
        if ($request->filled('teacher_id') && $request->filled('period_start') && $request->filled('period_end')) {
            $teacher = Teacher::with('user')->findOrFail($request->teacher_id);
            $periodStart = $request->period_start;
            $periodEnd = $request->period_end;

            $breakdown = $this->salaryService->getSalaryBreakdown($teacher, $periodStart, $periodEnd);
            $calculation = $breakdown['calculation'];
            $calculation['net_pay'] = $breakdown['net_pay'];
        }

        return view('admin.teacher-payslips.create', compact('teachers', 'teacher', 'calculation', 'periodStart', 'periodEnd'));
    }

    /**
     * Store a newly created payslip.
     */
    public function store(TeacherPayslipRequest $request)
    {
        DB::beginTransaction();
        try {
            $teacher = Teacher::findOrFail($request->teacher_id);

            // Check if payslip already exists for this period
            $exists = TeacherPayslip::where('teacher_id', $teacher->id)
                ->where('period_start', $request->period_start)
                ->where('period_end', $request->period_end)
                ->exists();

            if ($exists) {
                return back()->with('error', 'Payslip already exists for this teacher and period.')
                    ->withInput();
            }

            // Calculate salary
            $breakdown = $this->salaryService->getSalaryBreakdown(
                $teacher,
                $request->period_start,
                $request->period_end
            );

            $calculation = $breakdown['calculation'];

            // Add manual allowances and deductions
            $allowances = $calculation['allowances'] + ($request->allowances ?? 0);
            $deductions = $calculation['deductions'] + ($request->deductions ?? 0);

            // Calculate net pay with additional allowances/deductions
            $netPay = $this->salaryService->calculateNetPay(
                $calculation,
                $request->allowances ?? 0,
                $request->deductions ?? 0
            );

            // Create payslip
            $payslip = TeacherPayslip::create([
                'teacher_id' => $teacher->id,
                'payslip_number' => TeacherPayslip::generatePayslipNumber(),
                'period_start' => $request->period_start,
                'period_end' => $request->period_end,
                'total_hours' => $calculation['total_hours'],
                'total_classes' => $calculation['total_classes'],
                'basic_pay' => $calculation['basic_pay'],
                'allowances' => $allowances,
                'deductions' => $deductions,
                'epf_employee' => $calculation['epf_employee'],
                'epf_employer' => $calculation['epf_employer'],
                'socso_employee' => $calculation['socso_employee'],
                'socso_employer' => $calculation['socso_employer'],
                'net_pay' => $netPay,
                'status' => $request->status ?? 'draft',
                'notes' => $request->notes,
            ]);

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'create',
                'model_type' => 'TeacherPayslip',
                'model_id' => $payslip->id,
                'description' => 'Generated payslip ' . $payslip->payslip_number . ' for teacher: ' . $teacher->user->name,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            return redirect()->route('admin.teacher-payslips.show', $payslip)
                ->with('success', 'Payslip generated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to generate payslip: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified payslip.
     */
    public function show(TeacherPayslip $payslip)
    {
        $payslip->load('teacher.user');

        return view('admin.teacher-payslips.show', compact('payslip'));
    }

    /**
     * Update payslip status.
     */
    public function updateStatus(Request $request, TeacherPayslip $payslip)
    {
        $request->validate([
            'status' => 'required|in:draft,approved,paid',
            'payment_date' => 'required_if:status,paid|nullable|date',
            'payment_method' => 'required_if:status,paid|nullable|string',
            'reference_number' => 'nullable|string',
        ]);

        try {
            $payslip->update([
                'status' => $request->status,
                'payment_date' => $request->payment_date,
                'payment_method' => $request->payment_method,
                'reference_number' => $request->reference_number,
            ]);

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'update',
                'model_type' => 'TeacherPayslip',
                'model_id' => $payslip->id,
                'description' => 'Updated payslip status to: ' . $request->status,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return back()->with('success', 'Payslip status updated successfully.');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update status: ' . $e->getMessage());
        }
    }

    /**
     * Print payslip.
     */
    public function print(TeacherPayslip $payslip)
    {
        $payslip->load('teacher.user');

        return view('admin.teacher-payslips.print', compact('payslip'));
    }

    /**
     * Delete payslip (only draft).
     */
    public function destroy(TeacherPayslip $payslip)
    {
        if ($payslip->status !== 'draft') {
            return back()->with('error', 'Only draft payslips can be deleted.');
        }

        try {
            $payslipNumber = $payslip->payslip_number;

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'delete',
                'model_type' => 'TeacherPayslip',
                'model_id' => $payslip->id,
                'description' => 'Deleted draft payslip: ' . $payslipNumber,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            $payslip->delete();

            return redirect()->route('admin.teacher-payslips.index')
                ->with('success', 'Draft payslip deleted successfully.');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete payslip: ' . $e->getMessage());
        }
    }

    /**
     * Export payslips to Excel.
     */
    public function export(Request $request)
    {
        $filters = [
            'teacher_id' => $request->teacher_id,
            'status' => $request->status,
            'month' => $request->month,
            'year' => $request->year,
        ];

        return Excel::download(
            new TeacherPayslipExport($filters),
            'teacher-payslips-' . date('Y-m-d') . '.xlsx'
        );
    }

    /**
     * Calculate salary preview (AJAX).
     */
    public function calculatePreview(Request $request)
    {
        $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
        ]);

        try {
            $teacher = Teacher::with('user')->findOrFail($request->teacher_id);
            $breakdown = $this->salaryService->getSalaryBreakdown(
                $teacher,
                $request->period_start,
                $request->period_end
            );

            return response()->json([
                'success' => true,
                'data' => $breakdown,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
