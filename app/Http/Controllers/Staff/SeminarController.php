<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Seminar;
use App\Models\SeminarParticipant;
use Illuminate\Http\Request;

class SeminarController extends Controller
{
    /**
     * Constructor - Apply permission middleware
     */
    public function __construct()
    {
        // $this->middleware('permission:view-seminars')->only(['index']);
        // $this->middleware('permission:view-seminar-participants')->only(['show', 'participants']);
    }

    /**
     * Display seminar listing (view-only)
     */
    public function index(Request $request)
    {
        $query = Seminar::with(['participants']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('facilitator', 'like', "%{$search}%");
            });
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        $seminars = $query->latest('date')->paginate(15)->withQueryString();

        // Statistics
        $stats = [
            'total' => Seminar::count(),
            'upcoming' => Seminar::upcoming()->count(),
            'open' => Seminar::where('status', 'open')->count(),
            'completed' => Seminar::where('status', 'completed')->count(),
            'total_participants' => SeminarParticipant::count(),
            'total_revenue' => SeminarParticipant::where('payment_status', 'paid')->sum('fee_amount'),
        ];

        return view('staff.seminars.index', compact('seminars', 'stats'));
    }

    /**
     * Display seminar details (view-only)
     */
    public function show(Seminar $seminar)
    {
        $seminar->load(['participants.student.user', 'expenses']);

        // Participant statistics
        $participantStats = [
            'total' => $seminar->participants()->count(),
            'paid' => $seminar->participants()->where('payment_status', 'paid')->count(),
            'pending' => $seminar->participants()->where('payment_status', 'pending')->count(),
            'attended' => $seminar->participants()->where('attendance_status', 'attended')->count(),
            'total_revenue' => $seminar->participants()->where('payment_status', 'paid')->sum('fee_amount'),
        ];

        return view('staff.seminars.show', compact('seminar', 'participantStats'));
    }

    /**
     * View participant list (view-only)
     */
    public function participants(Seminar $seminar)
    {
        $participants = $seminar->participants()
            ->with(['student.user'])
            ->latest('registration_date')
            ->paginate(20);

        // Participant statistics for this seminar
        $stats = [
            'total' => $seminar->participants()->count(),
            'paid' => $seminar->participants()->where('payment_status', 'paid')->count(),
            'pending' => $seminar->participants()->where('payment_status', 'pending')->count(),
            'attended' => $seminar->participants()->where('attendance_status', 'attended')->count(),
            'absent' => $seminar->participants()->where('attendance_status', 'absent')->count(),
            'no_show' => $seminar->participants()->where('attendance_status', 'no_show')->count(),
        ];

        return view('staff.seminars.participants', compact('seminar', 'participants', 'stats'));
    }
}
