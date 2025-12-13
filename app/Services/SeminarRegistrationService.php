<?php

namespace App\Services;

use App\Models\Seminar;
use App\Models\SeminarParticipant;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class SeminarRegistrationService
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Check if registration is open for a seminar
     */
    public function isRegistrationOpen(Seminar $seminar): bool
    {
        // Check status
        if ($seminar->status !== 'open') {
            return false;
        }

        // Check if past registration deadline
        if ($seminar->registration_deadline && now()->isAfter($seminar->registration_deadline)) {
            return false;
        }

        // Check if seminar date has passed
        if (now()->isAfter($seminar->date)) {
            return false;
        }

        // Check capacity
        if ($seminar->capacity && $seminar->current_participants >= $seminar->capacity) {
            return false;
        }

        return true;
    }

    /**
     * Get available spots
     */
    public function getAvailableSpots(Seminar $seminar): int
    {
        if (!$seminar->capacity) {
            return 999; // Unlimited
        }

        return max(0, $seminar->capacity - $seminar->current_participants);
    }

    /**
     * Get current fee (early bird or regular)
     */
    public function getCurrentFee(Seminar $seminar): float
    {
        if ($this->isEarlyBirdActive($seminar)) {
            return $seminar->early_bird_fee;
        }

        return $seminar->regular_fee;
    }

    /**
     * Check if early bird pricing is active
     */
    public function isEarlyBirdActive(Seminar $seminar): bool
    {
        if (!$seminar->early_bird_fee || !$seminar->early_bird_deadline) {
            return false;
        }

        return now()->isBefore($seminar->early_bird_deadline);
    }

    /**
     * Process seminar registration
     */
    public function processRegistration(Seminar $seminar, array $data): SeminarParticipant
    {
        // Try to find existing student by email
        $student = $this->findExistingStudent($data['email']);

        // Determine fee amount
        $feeAmount = $this->getCurrentFee($seminar);

        // Prepare participant data
        $participantData = [
            'seminar_id' => $seminar->id,
            'student_id' => $student ? $student->id : null,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'school' => $data['school'] ?? null,
            'grade' => $data['grade'] ?? null,
            'registration_date' => now(),
            'fee_amount' => $feeAmount,
            'payment_status' => 'pending',
            'payment_method' => $data['payment_method'] ?? null,
            'notes' => $data['notes'] ?? null,
        ];

        // If payment method is cash and marked as paid immediately
        if (isset($data['payment_method']) && $data['payment_method'] === 'cash' && isset($data['paid_now'])) {
            $participantData['payment_status'] = 'paid';
            $participantData['payment_date'] = now();
        }

        // Create participant
        $participant = SeminarParticipant::create($participantData);

        // Increment participant count
        $seminar->increment('current_participants');

        // Auto-close if capacity reached
        if ($seminar->capacity && $seminar->current_participants >= $seminar->capacity) {
            $seminar->update(['status' => 'closed']);
            Log::info("Seminar {$seminar->id} auto-closed: capacity reached");
        }

        return $participant;
    }

    /**
     * Find existing student by email
     */
    protected function findExistingStudent(string $email): ?Student
    {
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            return null;
        }

        return Student::where('user_id', $user->id)->first();
    }

    /**
     * Send registration confirmation
     */
    public function sendConfirmation(SeminarParticipant $participant): array
    {
        $seminar = $participant->seminar;

        // Prepare user object for notification
        if ($participant->student_id && $participant->student->user) {
            $user = $participant->student->user;
        } else {
            // Create temporary user object
            $user = new User([
                'name' => $participant->name,
                'email' => $participant->email,
                'phone' => $participant->phone,
            ]);
            $user->id = 0;
        }

        // Prepare notification data
        $data = [
            'participant_name' => $participant->name,
            'seminar_name' => $seminar->name,
            'seminar_code' => $seminar->code,
            'seminar_date' => $seminar->date->format('l, d F Y'),
            'seminar_time' => $seminar->start_time ? $seminar->start_time->format('h:i A') : 'TBA',
            'venue' => $seminar->is_online ? 'Online Seminar' : $seminar->venue,
            'meeting_link' => $seminar->is_online ? $seminar->meeting_link : null,
            'fee_amount' => number_format($participant->fee_amount, 2),
            'payment_status' => ucfirst($participant->payment_status),
            'registration_date' => $participant->registration_date->format('d M Y H:i'),
        ];

        try {
            // Send confirmation via available channels
            $result = $this->notificationService->send(
                $user,
                'seminar_registration',
                $data,
                ['email', 'whatsapp'],
                'high'
            );

            Log::info("Registration confirmation sent for participant {$participant->id}");

            return [
                'success' => true,
                'channels' => $result,
            ];

        } catch (\Exception $e) {
            Log::error("Failed to send registration confirmation: " . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check if email already registered for seminar
     */
    public function isEmailRegistered(Seminar $seminar, string $email): bool
    {
        return SeminarParticipant::where('seminar_id', $seminar->id)
            ->where('email', $email)
            ->exists();
    }

    /**
     * Validate registration data
     */
    public function validateRegistration(Seminar $seminar, array $data): array
    {
        $errors = [];

        // Check if registration is open
        if (!$this->isRegistrationOpen($seminar)) {
            $errors[] = 'Registration is closed for this seminar.';
        }

        // Check capacity
        if ($this->getAvailableSpots($seminar) <= 0) {
            $errors[] = 'This seminar is fully booked.';
        }

        // Check duplicate email
        if ($this->isEmailRegistered($seminar, $data['email'])) {
            $errors[] = 'This email is already registered for this seminar.';
        }

        return $errors;
    }

    /**
     * Get registration statistics
     */
    public function getRegistrationStats(Seminar $seminar): array
    {
        $participants = $seminar->participants();

        return [
            'total' => $participants->count(),
            'paid' => $participants->where('payment_status', 'paid')->count(),
            'pending' => $participants->where('payment_status', 'pending')->count(),
            'capacity' => $seminar->capacity,
            'available' => $this->getAvailableSpots($seminar),
            'percentage_full' => $seminar->capacity 
                ? round(($seminar->current_participants / $seminar->capacity) * 100, 2) 
                : 0,
        ];
    }
}
