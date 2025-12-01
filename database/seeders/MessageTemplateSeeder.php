<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\MessageTemplate;

class MessageTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            // Payment Reminders
            [
                'name' => 'Payment Reminder - First Notice',
                'category' => 'payment_reminder',
                'channel' => 'whatsapp',
                'subject' => null,
                'message_body' => "🔔 *Payment Reminder*\n\nDear {parent_name},\n\nThis is a friendly reminder that payment of *RM{amount}* for {student_name} is due on *{due_date}*.\n\nInvoice: {invoice_number}\n\nPlease make payment to avoid any service interruption.\n\nThank you,\nArena Matriks Edu Group\n📞 03-1234 5678",
                'variables' => ['parent_name', 'student_name', 'amount', 'due_date', 'invoice_number'],
                'is_active' => true,
            ],
            [
                'name' => 'Payment Reminder - Email',
                'category' => 'payment_reminder',
                'channel' => 'email',
                'subject' => 'Payment Reminder - Invoice {invoice_number}',
                'message_body' => "Dear {parent_name},\n\nThis is a reminder that payment of RM{amount} for {student_name} is due on {due_date}.\n\nInvoice Number: {invoice_number}\n\nPlease make payment at your earliest convenience to avoid any service interruption.\n\nYou can make payment via:\n- Online Banking\n- Cash at our center\n- QR Payment\n\nIf you have already made the payment, please disregard this message.\n\nThank you for your continued support.\n\nBest regards,\nArena Matriks Edu Group",
                'variables' => ['parent_name', 'student_name', 'amount', 'due_date', 'invoice_number'],
                'is_active' => true,
            ],
            [
                'name' => 'Payment Overdue Notice',
                'category' => 'payment_reminder',
                'channel' => 'whatsapp',
                'subject' => null,
                'message_body' => "⚠️ *Payment Overdue Notice*\n\nDear {parent_name},\n\nWe notice that payment of *RM{amount}* for {student_name} is now overdue.\n\nOriginal Due Date: {due_date}\nInvoice: {invoice_number}\n\nTo avoid service suspension, please settle the payment immediately.\n\nContact us if you need assistance.\n\nArena Matriks Edu Group\n📞 03-1234 5678",
                'variables' => ['parent_name', 'student_name', 'amount', 'due_date', 'invoice_number'],
                'is_active' => true,
            ],

            // Welcome Messages
            [
                'name' => 'Welcome - New Student',
                'category' => 'welcome',
                'channel' => 'whatsapp',
                'subject' => null,
                'message_body' => "🎉 *Welcome to Arena Matriks!*\n\nDear {parent_name},\n\nWe're excited to welcome {student_name} to our family!\n\n📚 Class: {class_name}\n🗓️ Start Date: {due_date}\n\nYou can now access the parent portal:\n{login_link}\n\nFor any questions, please contact us:\n📞 03-1234 5678\n\nWe look forward to supporting {student_name}'s educational journey!\n\nArena Matriks Edu Group",
                'variables' => ['parent_name', 'student_name', 'class_name', 'due_date', 'login_link'],
                'is_active' => true,
            ],
            [
                'name' => 'Welcome - New Student Email',
                'category' => 'welcome',
                'channel' => 'email',
                'subject' => 'Welcome to Arena Matriks Edu Group!',
                'message_body' => "Dear {parent_name},\n\nWe are delighted to welcome {student_name} to Arena Matriks Edu Group!\n\nHere are the enrollment details:\n\nClass: {class_name}\nStart Date: {due_date}\n\nParent Portal Access:\nYou can now log in to our parent portal to view class schedules, attendance records, and make payments.\n\nLogin Link: {login_link}\n\nWhat to Expect:\n- Quality education with experienced teachers\n- Regular progress updates\n- Comprehensive learning materials\n- Supportive learning environment\n\nIf you have any questions, please don't hesitate to contact us.\n\nWe look forward to being part of {student_name}'s educational journey!\n\nWarm regards,\nArena Matriks Edu Group",
                'variables' => ['parent_name', 'student_name', 'class_name', 'due_date', 'login_link'],
                'is_active' => true,
            ],

            // Attendance Notifications
            [
                'name' => 'Attendance - Present',
                'category' => 'attendance',
                'channel' => 'whatsapp',
                'subject' => null,
                'message_body' => "✅ *Attendance Notification*\n\nDear {parent_name},\n\n{student_name} has been marked *PRESENT* for today's class.\n\n📅 Date: {attendance_date}\n📚 Class: {class_name}\n\nThank you,\nArena Matriks",
                'variables' => ['parent_name', 'student_name', 'attendance_date', 'class_name'],
                'is_active' => true,
            ],
            [
                'name' => 'Attendance - Absent',
                'category' => 'attendance',
                'channel' => 'whatsapp',
                'subject' => null,
                'message_body' => "⚠️ *Attendance Alert*\n\nDear {parent_name},\n\n{student_name} was marked *ABSENT* from today's class.\n\n📅 Date: {attendance_date}\n📚 Class: {class_name}\n\nIf this is incorrect or you'd like to inform us about the absence, please contact us.\n\n📞 03-1234 5678\n\nArena Matriks",
                'variables' => ['parent_name', 'student_name', 'attendance_date', 'class_name'],
                'is_active' => true,
            ],

            // Exam Results
            [
                'name' => 'Exam Result Notification',
                'category' => 'exam_result',
                'channel' => 'whatsapp',
                'subject' => null,
                'message_body' => "📊 *Exam Results Available*\n\nDear {parent_name},\n\nResults for {exam_name} are now available.\n\n👤 Student: {student_name}\n📝 Subject: {subject_name}\n💯 Score: {score}\n🎖️ Grade: {grade}\n\nView detailed results in the parent portal.\n\nArena Matriks",
                'variables' => ['parent_name', 'student_name', 'exam_name', 'subject_name', 'score', 'grade'],
                'is_active' => true,
            ],
            [
                'name' => 'Exam Result Email',
                'category' => 'exam_result',
                'channel' => 'email',
                'subject' => 'Exam Results for {student_name} - {exam_name}',
                'message_body' => "Dear {parent_name},\n\nWe are pleased to inform you that the results for {exam_name} are now available.\n\nStudent: {student_name}\nSubject: {subject_name}\nScore: {score}\nGrade: {grade}\n\nPlease log in to the parent portal to view the detailed results and performance analysis.\n\nIf you have any questions about the results, please contact your child's teacher or our administration.\n\nBest regards,\nArena Matriks Edu Group",
                'variables' => ['parent_name', 'student_name', 'exam_name', 'subject_name', 'score', 'grade'],
                'is_active' => true,
            ],

            // Trial Class
            [
                'name' => 'Trial Class Confirmation',
                'category' => 'trial_class',
                'channel' => 'whatsapp',
                'subject' => null,
                'message_body' => "📅 *Trial Class Confirmed*\n\nDear {parent_name},\n\nYour trial class booking has been confirmed!\n\n👤 Student: {student_name}\n📚 Subject: {subject_name}\n📅 Date: {trial_date}\n⏰ Time: {trial_time}\n📍 Location: {center_name}\n\nPlease arrive 10 minutes early.\n\nFor any changes, contact us:\n📞 {center_phone}\n\nWe look forward to meeting you!\n\nArena Matriks",
                'variables' => ['parent_name', 'student_name', 'subject_name', 'trial_date', 'trial_time', 'center_name', 'center_phone'],
                'is_active' => true,
            ],
            [
                'name' => 'Trial Class Reminder',
                'category' => 'trial_class',
                'channel' => 'whatsapp',
                'subject' => null,
                'message_body' => "⏰ *Trial Class Reminder*\n\nDear {parent_name},\n\nThis is a reminder about {student_name}'s trial class tomorrow.\n\n📚 Subject: {subject_name}\n📅 Date: {trial_date}\n⏰ Time: {trial_time}\n📍 Location: {center_name}\n\nPlease arrive 10 minutes early.\n\nSee you tomorrow!\nArena Matriks\n📞 {center_phone}",
                'variables' => ['parent_name', 'student_name', 'subject_name', 'trial_date', 'trial_time', 'center_name', 'center_phone'],
                'is_active' => true,
            ],

            // Enrollment
            [
                'name' => 'Enrollment Approved',
                'category' => 'enrollment',
                'channel' => 'whatsapp',
                'subject' => null,
                'message_body' => "✅ *Enrollment Approved*\n\nDear {parent_name},\n\nGreat news! {student_name}'s enrollment has been approved.\n\n📚 Class: {class_name}\n📅 Start Date: {due_date}\n\nYour login credentials will be sent separately.\n\nWelcome to Arena Matriks!\n📞 03-1234 5678",
                'variables' => ['parent_name', 'student_name', 'class_name', 'due_date'],
                'is_active' => true,
            ],

            // Announcements
            [
                'name' => 'General Announcement',
                'category' => 'announcement',
                'channel' => 'all',
                'subject' => 'Important Announcement - Arena Matriks',
                'message_body' => "📢 *Announcement*\n\nDear Parents/Guardians,\n\n{message}\n\nFor more information, please contact us or check the parent portal.\n\nArena Matriks Edu Group\n📞 03-1234 5678",
                'variables' => ['message'],
                'is_active' => true,
            ],

            // Password Reset
            [
                'name' => 'Password Reset',
                'category' => 'other',
                'channel' => 'email',
                'subject' => 'Password Reset Request - Arena Matriks',
                'message_body' => "Dear {parent_name},\n\nWe received a request to reset your password.\n\nClick the link below to reset your password:\n{reset_link}\n\nThis link will expire in 60 minutes.\n\nIf you did not request this, please ignore this email.\n\nBest regards,\nArena Matriks Edu Group",
                'variables' => ['parent_name', 'reset_link'],
                'is_active' => true,
            ],
        ];

        foreach ($templates as $template) {
            MessageTemplate::updateOrCreate(
                ['name' => $template['name']],
                $template
            );
        }

        $this->command->info('Message templates seeded successfully!');
    }
}
