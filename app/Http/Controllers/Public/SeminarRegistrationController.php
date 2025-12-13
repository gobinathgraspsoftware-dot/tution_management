<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\SeminarRegistrationRequest;
use App\Models\Seminar;
use App\Models\SeminarParticipant;
use App\Models\Student;
use App\Services\SeminarRegistrationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SeminarRegistrationController extends Controller
{
    protected $registrationService;

    public function __construct(SeminarRegistrationService $registrationService)
    {
        $this->registrationService = $registrationService;
    }

    /**
     * Display available seminars
     */
    public function index(Request $request)
    {
        $query = Seminar::open()->upcoming();

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by month
        if ($request->filled('month')) {
            $query->whereMonth('date', $request->month);
        }

        $seminars = $query->orderBy('date')->paginate(12);

        // Get upcoming featured seminars (next 3)
        $featured = Seminar::open()
            ->upcoming()
            ->orderBy('date')
            ->limit(3)
            ->get();

        return view('public.seminars.index', compact('seminars', 'featured'));
    }

    /**
     * Show seminar details
     */
    public function show(Seminar $seminar)
    {
        // Check if registration is still available
        $isRegistrationOpen = $this->registrationService->isRegistrationOpen($seminar);
        $availableSpots = $this->registrationService->getAvailableSpots($seminar);
        $currentFee = $this->registrationService->getCurrentFee($seminar);

        return view('public.seminars.show', compact(
            'seminar',
            'isRegistrationOpen',
            'availableSpots',
            'currentFee'
        ));
    }

    /**
     * Show registration form
     */
    public function register(Seminar $seminar)
    {
        // Check if registration is still open
        if (!$this->registrationService->isRegistrationOpen($seminar)) {
            return redirect()->route('public.seminars.show', $seminar)
                ->with('error', 'Registration is closed for this seminar.');
        }

        // Check if capacity is full
        if ($this->registrationService->getAvailableSpots($seminar) <= 0) {
            return redirect()->route('public.seminars.show', $seminar)
                ->with('error', 'This seminar is fully booked.');
        }

        $currentFee = $this->registrationService->getCurrentFee($seminar);

        return view('public.seminars.register', compact('seminar', 'currentFee'));
    }

    /**
     * Process registration
     */
    public function submitRegistration(SeminarRegistrationRequest $request, Seminar $seminar)
    {
        try {
            DB::beginTransaction();

            // Check if registration is still open
            if (!$this->registrationService->isRegistrationOpen($seminar)) {
                return back()->withInput()
                    ->with('error', 'Registration is closed for this seminar.');
            }

            // Check capacity
            if ($this->registrationService->getAvailableSpots($seminar) <= 0) {
                return back()->withInput()
                    ->with('error', 'This seminar is fully booked.');
            }

            // Process registration
            $participant = $this->registrationService->processRegistration($seminar, $request->validated());

            // Send confirmation notifications
            $this->registrationService->sendConfirmation($participant);

            DB::commit();

            return redirect()->route('public.seminars.success', [
                'seminar' => $seminar->id,
                'participant' => $participant->id
            ])->with('success', 'Registration completed successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Registration failed: ' . $e->getMessage());
        }
    }

    /**
     * Show registration success page
     */
    public function success(Request $request)
    {
        $seminarId = $request->query('seminar');
        $participantId = $request->query('participant');

        if (!$seminarId || !$participantId) {
            return redirect()->route('public.seminars.index');
        }

        $seminar = Seminar::findOrFail($seminarId);
        $participant = SeminarParticipant::findOrFail($participantId);

        return view('public.seminars.success', compact('seminar', 'participant'));
    }

    /**
     * Check email availability (AJAX)
     */
    public function checkEmail(Request $request)
    {
        $email = $request->query('email');
        $seminarId = $request->query('seminar_id');

        if (!$email || !$seminarId) {
            return response()->json(['exists' => false]);
        }

        $exists = SeminarParticipant::where('seminar_id', $seminarId)
            ->where('email', $email)
            ->exists();

        return response()->json(['exists' => $exists]);
    }

    /**
     * Get current fee (AJAX)
     */
    public function getCurrentFee(Seminar $seminar)
    {
        $currentFee = $this->registrationService->getCurrentFee($seminar);
        $isEarlyBird = $this->registrationService->isEarlyBirdActive($seminar);

        return response()->json([
            'fee' => $currentFee,
            'is_early_bird' => $isEarlyBird,
            'regular_fee' => $seminar->regular_fee,
            'early_bird_fee' => $seminar->early_bird_fee,
        ]);
    }

    /**
     * Check available spots (AJAX)
     */
    public function checkAvailability(Seminar $seminar)
    {
        $availableSpots = $this->registrationService->getAvailableSpots($seminar);
        $isOpen = $this->registrationService->isRegistrationOpen($seminar);

        return response()->json([
            'available_spots' => $availableSpots,
            'is_open' => $isOpen,
            'capacity' => $seminar->capacity,
            'current_participants' => $seminar->current_participants,
        ]);
    }
}
