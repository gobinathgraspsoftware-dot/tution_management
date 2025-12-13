<?php

namespace App\Services;

use App\Models\Seminar;
use App\Models\SeminarParticipant;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;

class SeminarService
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Generate unique seminar code
     */
    public function generateSeminarCode(string $type): string
    {
        $prefix = match($type) {
            'spm' => 'SPM',
            'workshop' => 'WS',
            'bootcamp' => 'BC',
            default => 'SEM',
        };

        do {
            $code = $prefix . '-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (Seminar::where('code', $code)->exists());

        return $code;
    }

    /**
     * Get seminar statistics
     */
    public function getStatistics(Seminar $seminar): array
    {
        $participants = $seminar->participants();

        return [
            'total_participants' => $participants->count(),
            'paid_participants' => $participants->where('payment_status', 'paid')->count(),
            'pending_payments' => $participants->where('payment_status', 'pending')->count(),
            'attended' => $participants->where('attendance_status', 'attended')->count(),
            'absent' => $participants->where('attendance_status', 'absent')->count(),
            'no_show' => $participants->where('attendance_status', 'no_show')->count(),
            'total_revenue' => $participants->where('payment_status', 'paid')->sum('fee_amount'),
            'pending_revenue' => $participants->where('payment_status', 'pending')->sum('fee_amount'),
            'attendance_rate' => $this->calculateAttendanceRate($seminar),
            'payment_rate' => $this->calculatePaymentRate($seminar),
            'available_spots' => $this->getAvailableSpots($seminar),
        ];
    }

    /**
     * Calculate attendance rate
     */
    protected function calculateAttendanceRate(Seminar $seminar): float
    {
        $total = $seminar->participants()->count();
        if ($total == 0) return 0;

        $attended = $seminar->participants()
            ->where('attendance_status', 'attended')
            ->count();

        return round(($attended / $total) * 100, 2);
    }

    /**
     * Calculate payment rate
     */
    protected function calculatePaymentRate(Seminar $seminar): float
    {
        $total = $seminar->participants()->count();
        if ($total == 0) return 0;

        $paid = $seminar->participants()
            ->where('payment_status', 'paid')
            ->count();

        return round(($paid / $total) * 100, 2);
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
     * Export participants to Excel
     */
    public function exportParticipants(Seminar $seminar)
    {
        $participants = $seminar->participants()
            ->with(['student.user'])
            ->get();

        $data = [];
        $data[] = ['Participant List - ' . $seminar->name];
        $data[] = ['Date: ' . $seminar->date->format('d M Y')];
        $data[] = ['Venue: ' . ($seminar->is_online ? 'Online' : $seminar->venue)];
        $data[] = [];
        $data[] = ['No', 'Name', 'Email', 'Phone', 'School', 'Grade', 'Registration Date', 'Fee Amount', 'Payment Status', 'Payment Method', 'Attendance Status'];

        $no = 1;
        foreach ($participants as $participant) {
            $data[] = [
                $no++,
                $participant->name,
                $participant->email,
                $participant->phone,
                $participant->school,
                $participant->grade,
                $participant->registration_date->format('d/m/Y H:i'),
                'RM ' . number_format($participant->fee_amount, 2),
                ucfirst($participant->payment_status),
                $participant->payment_method ?? '-',
                $participant->attendance_status ? ucfirst($participant->attendance_status) : 'Not Marked',
            ];
        }

        // Summary
        $data[] = [];
        $data[] = ['Summary'];
        $data[] = ['Total Participants', $participants->count()];
        $data[] = ['Paid', $participants->where('payment_status', 'paid')->count()];
        $data[] = ['Pending Payment', $participants->where('payment_status', 'pending')->count()];
        $data[] = ['Total Revenue', 'RM ' . number_format($participants->where('payment_status', 'paid')->sum('fee_amount'), 2)];
        $data[] = ['Attended', $participants->where('attendance_status', 'attended')->count()];
        $data[] = ['Absent', $participants->where('attendance_status', 'absent')->count()];

        $fileName = 'participants_' . Str::slug($seminar->name) . '_' . date('YmdHis') . '.xlsx';

        return Excel::download(new class($data) implements \Maatwebsite\Excel\Concerns\FromArray {
            protected $data;

            public function __construct($data)
            {
                $this->data = $data;
            }

            public function array(): array
            {
                return $this->data;
            }
        }, $fileName);
    }

    /**
     * Send bulk notification to participants
     */
    public function sendBulkNotification(Seminar $seminar, string $message, array $channels): array
    {
        $participants = $seminar->participants()->with(['student.user'])->get();
        
        $sent = 0;
        $failed = 0;

        foreach ($participants as $participant) {
            try {
                // Get user (either from linked student or create dummy for notification)
                if ($participant->student_id && $participant->student->user) {
                    $user = $participant->student->user;
                } else {
                    // Create a temporary user object for notification
                    $user = new User([
                        'name' => $participant->name,
                        'email' => $participant->email,
                        'phone' => $participant->phone,
                    ]);
                    $user->id = 0; // Temporary ID
                }

                $data = [
                    'seminar_name' => $seminar->name,
                    'seminar_date' => $seminar->date->format('d M Y'),
                    'seminar_time' => $seminar->start_time ? $seminar->start_time->format('h:i A') : 'TBA',
                    'venue' => $seminar->is_online ? $seminar->meeting_link : $seminar->venue,
                    'participant_name' => $participant->name,
                    'message' => $message,
                ];

                $this->notificationService->send(
                    $user,
                    'seminar_notification',
                    $data,
                    $channels,
                    'normal'
                );

                $sent++;

            } catch (\Exception $e) {
                Log::error("Failed to send notification to participant {$participant->id}: " . $e->getMessage());
                $failed++;
            }
        }

        return [
            'sent' => $sent,
            'failed' => $failed,
            'total' => $participants->count(),
        ];
    }

    /**
     * Send seminar reminder (1 week before)
     */
    public function sendWeekReminder(Seminar $seminar): array
    {
        $message = "Reminder: Your seminar '{$seminar->name}' is coming up in 1 week on {$seminar->date->format('d M Y')}. Looking forward to seeing you there!";
        
        return $this->sendBulkNotification(
            $seminar,
            $message,
            ['whatsapp', 'email']
        );
    }

    /**
     * Send seminar reminder (1 day before)
     */
    public function sendDayReminder(Seminar $seminar): array
    {
        $venue = $seminar->is_online 
            ? "Meeting Link: {$seminar->meeting_link}" 
            : "Venue: {$seminar->venue}";

        $message = "Final Reminder: Your seminar '{$seminar->name}' is TOMORROW at {$seminar->start_time->format('h:i A')}. {$venue}. See you there!";
        
        return $this->sendBulkNotification(
            $seminar,
            $message,
            ['whatsapp', 'email', 'sms']
        );
    }

    /**
     * Check if seminar capacity is reached
     */
    public function isCapacityReached(Seminar $seminar): bool
    {
        if (!$seminar->capacity) {
            return false; // Unlimited capacity
        }

        return $seminar->current_participants >= $seminar->capacity;
    }

    /**
     * Auto-close registration if capacity reached
     */
    public function autoCloseIfFull(Seminar $seminar): bool
    {
        if ($this->isCapacityReached($seminar) && $seminar->status === 'open') {
            $seminar->update(['status' => 'closed']);
            
            Log::info("Seminar {$seminar->id} auto-closed due to capacity reached");
            
            return true;
        }

        return false;
    }

    /**
     * Get upcoming seminars that need reminders
     */
    public function getSeminarsForWeekReminder()
    {
        return Seminar::where('status', 'open')
            ->whereDate('date', now()->addWeek()->toDateString())
            ->get();
    }

    /**
     * Get upcoming seminars that need day reminder
     */
    public function getSeminarsForDayReminder()
    {
        return Seminar::whereIn('status', ['open', 'closed'])
            ->whereDate('date', now()->addDay()->toDateString())
            ->get();
    }
}
