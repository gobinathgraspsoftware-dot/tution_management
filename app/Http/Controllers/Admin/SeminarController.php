<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SeminarRequest;
use App\Models\Seminar;
use App\Models\SeminarParticipant;
use App\Models\ActivityLog;
use App\Services\SeminarService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SeminarController extends Controller
{
    protected $seminarService;

    public function __construct(SeminarService $seminarService)
    {
        $this->seminarService = $seminarService;
    }

    /**
     * Display seminar listing
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

        return view('admin.seminars.index', compact('seminars', 'stats'));
    }

    /**
     * Show create seminar form
     */
    public function create()
    {
        return view('admin.seminars.create');
    }

    /**
     * Store new seminar
     */
    public function store(SeminarRequest $request)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();

            // Generate unique code
            $data['code'] = $this->seminarService->generateSeminarCode($data['type']);

            // Handle image upload
            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store('seminars', 'public');
            }

            $seminar = Seminar::create($data);

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'create',
                'model_type' => 'Seminar',
                'model_id' => $seminar->id,
                'description' => "Created seminar: {$seminar->name}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            DB::commit();
            return redirect()->route('admin.seminars.index')
                ->with('success', 'Seminar created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to create seminar: ' . $e->getMessage());
        }
    }

    /**
     * Display seminar details
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

        return view('admin.seminars.show', compact('seminar', 'participantStats'));
    }

    /**
     * Show edit seminar form
     */
    public function edit(Seminar $seminar)
    {
        return view('admin.seminars.edit', compact('seminar'));
    }

    /**
     * Update seminar
     */
    public function update(SeminarRequest $request, Seminar $seminar)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();

            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete old image
                if ($seminar->image) {
                    Storage::disk('public')->delete($seminar->image);
                }
                $data['image'] = $request->file('image')->store('seminars', 'public');
            }

            $seminar->update($data);

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'update',
                'model_type' => 'Seminar',
                'model_id' => $seminar->id,
                'description' => "Updated seminar: {$seminar->name}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            DB::commit();
            return redirect()->route('admin.seminars.show', $seminar)
                ->with('success', 'Seminar updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to update seminar: ' . $e->getMessage());
        }
    }

    /**
     * Update seminar status
     */
    public function updateStatus(Request $request, Seminar $seminar)
    {
        $request->validate([
            'status' => 'required|in:draft,open,closed,completed,cancelled'
        ]);

        try {
            $seminar->update(['status' => $request->status]);

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'update',
                'model_type' => 'Seminar',
                'model_id' => $seminar->id,
                'description' => "Changed seminar status to: {$request->status}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Seminar status updated successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete seminar
     */
    public function destroy(Seminar $seminar)
    {
        try {
            DB::beginTransaction();

            // Check if seminar has participants
            if ($seminar->participants()->count() > 0) {
                return back()->with('error', 'Cannot delete seminar with registered participants.');
            }

            // Delete image
            if ($seminar->image) {
                Storage::disk('public')->delete($seminar->image);
            }

            // Log activity before deletion
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'delete',
                'model_type' => 'Seminar',
                'model_id' => $seminar->id,
                'description' => "Deleted seminar: {$seminar->name}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            $seminar->delete();

            DB::commit();
            return redirect()->route('admin.seminars.index')
                ->with('success', 'Seminar deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete seminar: ' . $e->getMessage());
        }
    }

    /**
     * View participant list
     */
    public function participants(Seminar $seminar)
    {
        $participants = $seminar->participants()
            ->with(['student.user'])
            ->latest('registration_date')
            ->paginate(20);

        return view('admin.seminars.participants', compact('seminar', 'participants'));
    }

    /**
     * Export participants
     */
    public function exportParticipants(Seminar $seminar)
    {
        return $this->seminarService->exportParticipants($seminar);
    }

    /**
     * Mark participant attendance
     */
    public function markAttendance(Request $request, Seminar $seminar, SeminarParticipant $participant)
    {
        $request->validate([
            'attendance_status' => 'required|in:attended,absent,no_show'
        ]);

        try {
            $participant->update([
                'attendance_status' => $request->attendance_status
            ]);

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'update',
                'model_type' => 'SeminarParticipant',
                'model_id' => $participant->id,
                'description' => "Marked attendance as: {$request->attendance_status}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Attendance marked successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark attendance: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update participant payment status
     */
    public function updatePaymentStatus(Request $request, Seminar $seminar, SeminarParticipant $participant)
    {
        $request->validate([
            'payment_status' => 'required|in:pending,paid,refunded',
            'payment_method' => 'nullable|string',
            'payment_date' => 'nullable|date'
        ]);

        try {
            $data = [
                'payment_status' => $request->payment_status,
            ];

            if ($request->payment_status === 'paid') {
                $data['payment_method'] = $request->payment_method ?? 'cash';
                $data['payment_date'] = $request->payment_date ?? now();
            }

            $participant->update($data);

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'update',
                'model_type' => 'SeminarParticipant',
                'model_id' => $participant->id,
                'description' => "Updated payment status to: {$request->payment_status}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment status updated successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update payment status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send bulk communication to participants
     */
    public function sendBulkNotification(Request $request, Seminar $seminar)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'channels' => 'required|array',
            'channels.*' => 'in:email,whatsapp,sms'
        ]);

        try {
            $result = $this->seminarService->sendBulkNotification(
                $seminar,
                $request->message,
                $request->channels
            );

            return response()->json([
                'success' => true,
                'message' => "Notifications sent successfully to {$result['sent']} participants."
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send notifications: ' . $e->getMessage()
            ], 500);
        }
    }
}
