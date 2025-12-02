<?php

namespace App\Services;

use App\Models\TrialClass;
use App\Models\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class TrialClassService
{
    protected $whatsappService;

    public function __construct(WhatsAppService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    /**
     * Send notification when trial class is scheduled.
     */
    public function sendTrialScheduledNotification(TrialClass $trialClass): void
    {
        $class = $trialClass->class;
        $date = $trialClass->scheduled_date->format('l, d M Y');
        $time = $trialClass->scheduled_time ? $trialClass->scheduled_time->format('h:i A') : 'TBD';

        $message = "📚 Arena Matriks - Trial Class Scheduled\n\n"
                 . "Dear {$trialClass->parent_name},\n\n"
                 . "A trial class has been scheduled for {$trialClass->student_name}!\n\n"
                 . "📖 Class: {$class->name}\n"
                 . "📅 Date: {$date}\n"
                 . "⏰ Time: {$time}\n";

        if ($class->type === 'online' && $class->meeting_link) {
            $message .= "🔗 Meeting Link: {$class->meeting_link}\n";
        } else {
            $message .= "📍 Location: " . ($class->location ?? 'Arena Matriks Centre') . "\n";
        }

        $message .= "\nPlease arrive 10 minutes early. For any questions, contact us at "
                  . config('app.centre_phone', '03-7972 3663') . ".\n\n"
                  . "We look forward to meeting you! 🌟";

        // WhatsApp notification
        if ($trialClass->parent_phone) {
            try {
                $this->whatsappService->send($trialClass->parent_phone, $message);
            } catch (\Exception $e) {
                Log::error('Trial class WhatsApp notification failed: ' . $e->getMessage());
            }
        }

        // Email notification
        if ($trialClass->parent_email) {
            try {
                Mail::send('emails.trial-scheduled', [
                    'trial_class' => $trialClass,
                    'class' => $class,
                ], function ($mail) use ($trialClass) {
                    $mail->to($trialClass->parent_email)
                         ->subject('Trial Class Scheduled - Arena Matriks');
                });
            } catch (\Exception $e) {
                Log::error('Trial class email notification failed: ' . $e->getMessage());
            }
        }

        // In-app notification if student exists
        if ($trialClass->student_id && $trialClass->student) {
            Notification::create([
                'user_id' => $trialClass->student->user_id,
                'type' => 'trial_scheduled',
                'title' => 'Trial Class Scheduled',
                'message' => "Your trial class for {$class->name} is scheduled on {$date} at {$time}.",
            ]);
        }
    }

    /**
     * Send notification when trial class is approved.
     */
    public function sendTrialApprovedNotification(TrialClass $trialClass): void
    {
        $class = $trialClass->class;
        $date = $trialClass->scheduled_date->format('l, d M Y');
        $time = $trialClass->scheduled_time ? $trialClass->scheduled_time->format('h:i A') : 'TBD';

        $message = "✅ Arena Matriks - Trial Class Approved!\n\n"
                 . "Dear {$trialClass->parent_name},\n\n"
                 . "Great news! The trial class for {$trialClass->student_name} has been approved.\n\n"
                 . "📖 Class: {$class->name}\n"
                 . "📅 Date: {$date}\n"
                 . "⏰ Time: {$time}\n";

        if ($class->type === 'online' && $class->meeting_link) {
            $message .= "🔗 Meeting Link: {$class->meeting_link}\n";
        } else {
            $message .= "📍 Location: " . ($class->location ?? 'Arena Matriks Centre') . "\n";
        }

        $message .= "\nSee you there! 🌟";

        if ($trialClass->parent_phone) {
            try {
                $this->whatsappService->send($trialClass->parent_phone, $message);
            } catch (\Exception $e) {
                Log::error('Trial approved WhatsApp notification failed: ' . $e->getMessage());
            }
        }
    }

    /**
     * Send follow-up notification after trial class.
     */
    public function sendTrialFollowUpNotification(TrialClass $trialClass): void
    {
        $class = $trialClass->class;

        if ($trialClass->status === 'attended') {
            $message = "🎉 Thank You for Attending!\n\n"
                     . "Dear {$trialClass->parent_name},\n\n"
                     . "We hope {$trialClass->student_name} enjoyed the trial class for {$class->name}!\n\n"
                     . "We'd love to have {$trialClass->student_name} join us permanently. "
                     . "Contact us to discuss enrollment options:\n"
                     . "📞 " . config('app.centre_phone', '03-7972 3663') . "\n"
                     . "📧 " . config('app.centre_email', 'info@arenamatriks.com') . "\n\n"
                     . "Special offer: Register within 7 days and get 10% off the first month! 🎁";
        } else {
            $message = "📚 We Missed You!\n\n"
                     . "Dear {$trialClass->parent_name},\n\n"
                     . "We noticed {$trialClass->student_name} couldn't make it to the trial class.\n\n"
                     . "Would you like to reschedule? We're happy to find another suitable time.\n"
                     . "Contact us:\n"
                     . "📞 " . config('app.centre_phone', '03-7972 3663') . "\n"
                     . "📧 " . config('app.centre_email', 'info@arenamatriks.com') . "\n\n"
                     . "We look forward to hearing from you! 🌟";
        }

        if ($trialClass->parent_phone) {
            try {
                $this->whatsappService->send($trialClass->parent_phone, $message);
            } catch (\Exception $e) {
                Log::error('Trial follow-up WhatsApp notification failed: ' . $e->getMessage());
            }
        }
    }

    /**
     * Send reminder notification for upcoming trial class.
     */
    public function sendTrialReminder(TrialClass $trialClass): void
    {
        if (!in_array($trialClass->status, ['pending', 'approved'])) {
            return;
        }

        $class = $trialClass->class;
        $date = $trialClass->scheduled_date->format('l, d M Y');
        $time = $trialClass->scheduled_time ? $trialClass->scheduled_time->format('h:i A') : 'TBD';

        $message = "⏰ Reminder: Trial Class Tomorrow!\n\n"
                 . "Dear {$trialClass->parent_name},\n\n"
                 . "This is a friendly reminder about {$trialClass->student_name}'s trial class:\n\n"
                 . "📖 Class: {$class->name}\n"
                 . "📅 Date: {$date}\n"
                 . "⏰ Time: {$time}\n";

        if ($class->type === 'online' && $class->meeting_link) {
            $message .= "🔗 Meeting Link: {$class->meeting_link}\n";
        } else {
            $message .= "📍 Location: " . ($class->location ?? 'Arena Matriks Centre') . "\n";
        }

        $message .= "\nPlease arrive 10 minutes early. See you tomorrow! 🌟";

        if ($trialClass->parent_phone) {
            try {
                $this->whatsappService->send($trialClass->parent_phone, $message);
            } catch (\Exception $e) {
                Log::error('Trial reminder WhatsApp notification failed: ' . $e->getMessage());
            }
        }
    }

    /**
     * Get upcoming trial classes.
     */
    public function getUpcomingTrials(int $days = 7): \Illuminate\Database\Eloquent\Collection
    {
        return TrialClass::with(['student.user', 'class.subject', 'class.teacher.user'])
            ->whereIn('status', ['pending', 'approved'])
            ->whereBetween('scheduled_date', [today(), today()->addDays($days)])
            ->orderBy('scheduled_date')
            ->orderBy('scheduled_time')
            ->get();
    }

    /**
     * Get today's trial classes.
     */
    public function getTodaysTrials(): \Illuminate\Database\Eloquent\Collection
    {
        return TrialClass::with(['student.user', 'class.subject', 'class.teacher.user'])
            ->whereIn('status', ['pending', 'approved'])
            ->whereDate('scheduled_date', today())
            ->orderBy('scheduled_time')
            ->get();
    }

    /**
     * Get trial classes needing follow-up.
     */
    public function getTrialsNeedingFollowUp(): \Illuminate\Database\Eloquent\Collection
    {
        return TrialClass::with(['student.user', 'class'])
            ->where('status', 'attended')
            ->where('conversion_status', 'pending')
            ->where('scheduled_date', '<', today())
            ->orderBy('scheduled_date', 'desc')
            ->get();
    }

    /**
     * Auto-mark no-shows for past trial classes.
     */
    public function autoMarkNoShows(): int
    {
        return TrialClass::where('status', 'approved')
            ->where('scheduled_date', '<', today())
            ->update(['status' => 'no_show']);
    }

    /**
     * Get conversion statistics.
     */
    public function getConversionStats(): array
    {
        $total = TrialClass::count();
        $attended = TrialClass::whereIn('status', ['attended', 'converted'])->count();
        $converted = TrialClass::where('conversion_status', 'converted')->count();
        $noShow = TrialClass::where('status', 'no_show')->count();

        return [
            'total_trials' => $total,
            'attended' => $attended,
            'converted' => $converted,
            'no_show' => $noShow,
            'attendance_rate' => $total > 0 ? round(($attended / $total) * 100, 2) : 0,
            'conversion_rate' => $attended > 0 ? round(($converted / $attended) * 100, 2) : 0,
            'no_show_rate' => $total > 0 ? round(($noShow / $total) * 100, 2) : 0,
        ];
    }
}
