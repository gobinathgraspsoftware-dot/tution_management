<?php

namespace App\Services;

use App\Models\Student;
use App\Models\User;
use App\Models\ActivityLog;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class StudentApprovalService
{
    protected $notificationService;
    protected $whatsappService;

    public function __construct(
        NotificationService $notificationService,
        WhatsAppService $whatsappService
    ) {
        $this->notificationService = $notificationService;
        $this->whatsappService = $whatsappService;
    }

    /**
     * Approve a student registration.
     */
    public function approve(Student $student, User $approver, array $options = []): array
    {
        try {
            DB::beginTransaction();

            // Update student status
            $student->update([
                'approval_status' => 'approved',
                'approved_by' => $approver->id,
                'approved_at' => now(),
                'enrollment_date' => now(),
                'notes' => $options['notes'] ?? $student->notes,
            ]);

            // Activate user account
            $student->user->update([
                'status' => 'active',
                'email_verified_at' => $student->user->email_verified_at ?? now(),
            ]);

            // Generate referral code if not exists
            if (!$student->referral_code) {
                $student->update([
                    'referral_code' => $this->generateReferralCode($student),
                ]);
            }

            // Log activity
            ActivityLog::create([
                'user_id' => $approver->id,
                'action' => 'approve',
                'model_type' => 'Student',
                'model_id' => $student->id,
                'description' => "Approved student registration: {$student->user->name} ({$student->student_id})",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'changes' => json_encode([
                    'old' => ['approval_status' => 'pending'],
                    'new' => ['approval_status' => 'approved'],
                ]),
            ]);

            DB::commit();

            // Send notifications
            $notificationResults = [];
            if ($options['send_welcome'] ?? true) {
                $channels = [];
                if ($options['send_whatsapp'] ?? true) {
                    $channels[] = 'whatsapp';
                }
                if ($options['send_email'] ?? true) {
                    $channels[] = 'email';
                }
                $notificationResults = $this->sendWelcomeNotification($student, $channels);
            }

            // Create in-app notification for parent
            if ($student->parent) {
                $this->createInAppNotification($student->parent->user,
                    'Student Approved',
                    "Your child {$student->user->name} has been approved and activated.",
                    'student_approval'
                );
            }

            return [
                'success' => true,
                'message' => "Student {$student->user->name} approved successfully.",
                'notifications' => $notificationResults,
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Student approval failed: ' . $e->getMessage(), [
                'student_id' => $student->id,
                'approver_id' => $approver->id,
            ]);

            return [
                'success' => false,
                'message' => 'Failed to approve student: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Reject a student registration.
     */
    public function reject(Student $student, User $rejector, array $options = []): array
    {
        try {
            DB::beginTransaction();

            $rejectionReason = $options['rejection_reason'] ?? 'Application rejected';

            // Update student status
            $student->update([
                'approval_status' => 'rejected',
                'approved_by' => $rejector->id,
                'approved_at' => now(),
                'rejection_reason' => $rejectionReason,
            ]);

            // Deactivate user account
            $student->user->update([
                'status' => 'inactive',
            ]);

            // Log activity
            ActivityLog::create([
                'user_id' => $rejector->id,
                'action' => 'reject',
                'model_type' => 'Student',
                'model_id' => $student->id,
                'description' => "Rejected student registration: {$student->user->name} ({$student->student_id})",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'changes' => json_encode([
                    'old' => ['approval_status' => 'pending'],
                    'new' => ['approval_status' => 'rejected', 'rejection_reason' => $rejectionReason],
                ]),
            ]);

            DB::commit();

            // Send rejection notification
            if ($options['send_notification'] ?? true) {
                $this->sendRejectionNotification($student, $rejectionReason);
            }

            // Create in-app notification for parent
            if ($student->parent) {
                $this->createInAppNotification($student->parent->user,
                    'Registration Rejected',
                    "The registration for {$student->user->name} was not approved. Reason: {$rejectionReason}",
                    'student_rejection'
                );
            }

            return [
                'success' => true,
                'message' => "Student {$student->user->name} registration rejected.",
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Student rejection failed: ' . $e->getMessage(), [
                'student_id' => $student->id,
                'rejector_id' => $rejector->id,
            ]);

            return [
                'success' => false,
                'message' => 'Failed to reject student: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Request more information from parent.
     */
    public function requestMoreInfo(Student $student, string $infoRequest, User $requestedBy): array
    {
        try {
            // Log activity
            ActivityLog::create([
                'user_id' => $requestedBy->id,
                'action' => 'request_info',
                'model_type' => 'Student',
                'model_id' => $student->id,
                'description' => "Requested more info for student: {$student->user->name}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'changes' => json_encode(['info_request' => $infoRequest]),
            ]);

            // Send notification to parent
            $data = [
                'student_name' => $student->user->name,
                'student_id' => $student->student_id,
                'info_request' => $infoRequest,
                'requested_by' => $requestedBy->name,
            ];

            // WhatsApp notification
            if ($student->parent && $student->parent->user->phone) {
                $message = "Arena Matriks: Regarding the registration of {$student->user->name}, we need additional information:\n\n{$infoRequest}\n\nPlease contact us or reply to this message.";
                $this->whatsappService->send($student->parent->user->phone, $message);
            }

            // Email notification
            if ($student->parent && $student->parent->user->email) {
                Mail::send('emails.info-request', $data, function ($mail) use ($student) {
                    $mail->to($student->parent->user->email)
                        ->subject('Arena Matriks - Additional Information Required');
                });
            }

            // In-app notification
            if ($student->parent) {
                $this->createInAppNotification($student->parent->user,
                    'Information Required',
                    "Additional information is needed for {$student->user->name}'s registration.",
                    'info_request'
                );
            }

            return ['success' => true];

        } catch (\Exception $e) {
            Log::error('Request info failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send welcome notification to approved student.
     */
    public function sendWelcomeNotification(Student $student, array $channels = ['whatsapp', 'email']): array
    {
        $results = [];
        $student->load(['user', 'parent.user']);

        $data = [
            'student_name' => $student->user->name,
            'student_id' => $student->student_id,
            'student_email' => $student->user->email,
            'referral_code' => $student->referral_code,
            'parent_name' => $student->parent?->user?->name ?? 'Parent',
            'login_url' => url('/login'),
            'centre_name' => 'Arena Matriks Edu Group',
            'centre_phone' => config('app.centre_phone', '03-7972 3663'),
            'centre_address' => 'Wisma Arena Matriks, No.7, Jalan Kemuning Prima B33/B, 40400 Shah Alam, Selangor',
        ];

        // Send WhatsApp welcome
        if (in_array('whatsapp', $channels)) {
            $results['whatsapp'] = $this->sendWhatsAppWelcome($student, $data);
        }

        // Send Email welcome
        if (in_array('email', $channels)) {
            $results['email'] = $this->sendEmailWelcome($student, $data);
        }

        return ['success' => true, 'results' => $results];
    }

    /**
     * Send WhatsApp welcome message.
     */
    protected function sendWhatsAppWelcome(Student $student, array $data): array
    {
        try {
            // Message to student (if phone available)
            if ($student->user->phone) {
                $studentMessage = $this->buildStudentWelcomeMessage($data);
                $this->whatsappService->send($student->user->phone, $studentMessage);
            }

            // Message to parent
            if ($student->parent && $student->parent->user->phone) {
                $parentMessage = $this->buildParentWelcomeMessage($data);
                $this->whatsappService->send($student->parent->user->phone, $parentMessage);
            }

            return ['success' => true];
        } catch (\Exception $e) {
            Log::error('WhatsApp welcome failed: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Send Email welcome message.
     */
    protected function sendEmailWelcome(Student $student, array $data): array
    {
        try {
            // Email to student
            if ($student->user->email) {
                Mail::send('emails.student-welcome', $data, function ($mail) use ($student) {
                    $mail->to($student->user->email)
                        ->subject('Welcome to Arena Matriks Edu Group!');
                });
            }

            // Email to parent
            if ($student->parent && $student->parent->user->email) {
                Mail::send('emails.student-approved', $data, function ($mail) use ($student) {
                    $mail->to($student->parent->user->email)
                        ->subject('Student Registration Approved - Arena Matriks');
                });
            }

            return ['success' => true];
        } catch (\Exception $e) {
            Log::error('Email welcome failed: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Send rejection notification.
     */
    protected function sendRejectionNotification(Student $student, string $reason): void
    {
        $data = [
            'student_name' => $student->user->name,
            'rejection_reason' => $reason,
            'centre_phone' => config('app.centre_phone', '03-7972 3663'),
        ];

        // WhatsApp to parent
        if ($student->parent && $student->parent->user->phone) {
            $message = "Arena Matriks: We regret to inform you that the registration for {$student->user->name} was not approved.\n\nReason: {$reason}\n\nIf you have questions, please contact us at " . config('app.centre_phone', '03-7972 3663');
            $this->whatsappService->send($student->parent->user->phone, $message);
        }

        // Email to parent
        if ($student->parent && $student->parent->user->email) {
            try {
                Mail::send('emails.student-rejected', $data, function ($mail) use ($student) {
                    $mail->to($student->parent->user->email)
                        ->subject('Student Registration Status - Arena Matriks');
                });
            } catch (\Exception $e) {
                Log::error('Rejection email failed: ' . $e->getMessage());
            }
        }
    }

    /**
     * Build student welcome WhatsApp message.
     */
    protected function buildStudentWelcomeMessage(array $data): string
    {
        return "🎉 Welcome to Arena Matriks Edu Group!\n\n"
            . "Hi {$data['student_name']},\n\n"
            . "Your registration has been approved! Here are your details:\n\n"
            . "📋 Student ID: {$data['student_id']}\n"
            . "📧 Email: {$data['student_email']}\n"
            . "🎁 Referral Code: {$data['referral_code']}\n\n"
            . "🔗 Login at: {$data['login_url']}\n\n"
            . "Share your referral code with friends and earn RM50 voucher for each successful referral!\n\n"
            . "For any questions, contact us at {$data['centre_phone']}\n\n"
            . "We're excited to have you join us! 📚";
    }

    /**
     * Build parent welcome WhatsApp message.
     */
    protected function buildParentWelcomeMessage(array $data): string
    {
        return "🎉 Arena Matriks Edu Group\n\n"
            . "Dear {$data['parent_name']},\n\n"
            . "Great news! Your child {$data['student_name']}'s registration has been approved.\n\n"
            . "📋 Student ID: {$data['student_id']}\n"
            . "🎁 Referral Code: {$data['referral_code']}\n\n"
            . "Your child can now login at: {$data['login_url']}\n\n"
            . "Next Steps:\n"
            . "1. Check class schedule\n"
            . "2. View enrolled subjects\n"
            . "3. Access study materials\n\n"
            . "For any questions, contact us:\n"
            . "📞 {$data['centre_phone']}\n"
            . "📍 {$data['centre_address']}\n\n"
            . "Thank you for choosing Arena Matriks! 🙏";
    }

    /**
     * Generate unique referral code.
     */
    protected function generateReferralCode(Student $student): string
    {
        $prefix = 'REF';
        $code = $prefix . str_pad($student->id, 6, '0', STR_PAD_LEFT);

        // Ensure uniqueness
        while (Student::where('referral_code', $code)->exists()) {
            $code = $prefix . strtoupper(Str::random(6));
        }

        return $code;
    }

    /**
     * Create in-app notification.
     */
    protected function createInAppNotification(User $user, string $title, string $message, string $type): void
    {
        try {
            Notification::create([
                'user_id' => $user->id,
                'title' => $title,
                'message' => $message,
                'type' => $type,
                'is_read' => false,
            ]);
        } catch (\Exception $e) {
            Log::error('In-app notification failed: ' . $e->getMessage());
        }
    }
}
