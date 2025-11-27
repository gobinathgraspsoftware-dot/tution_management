<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ===================================================================
        // SUPER ADMIN - Full System Access
        // ===================================================================
        $superAdmin = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        
        // Super Admin gets ALL permissions
        $superAdmin->syncPermissions(Permission::all());
        $this->command->info('✓ Super Admin role created with ALL permissions');

        // ===================================================================
        // ADMIN - Operational Management
        // ===================================================================
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        
        $adminPermissions = [
            // Dashboard
            'view-dashboard',
            'view-admin-dashboard',

            // User Management (except roles/permissions)
            'view-users',
            'create-users',
            'edit-users',
            'delete-users',
            'export-users',
            'import-users',

            // Full Student Management
            'view-students',
            'create-students',
            'edit-students',
            'delete-students',
            'restore-students',
            'export-students',
            'import-students',
            'approve-students',
            'reject-students',
            'view-pending-students',
            'view-student-profile',
            'edit-student-profile',
            'view-student-history',
            'view-student-payments',
            'view-student-attendance',
            'view-student-materials',
            'view-student-results',
            'manage-student-enrollment',

            // Full Parent Management
            'view-parents',
            'create-parents',
            'edit-parents',
            'delete-parents',
            'restore-parents',
            'export-parents',
            'view-parent-profile',
            'edit-parent-profile',
            'view-parent-children',
            'link-parent-student',

            // Full Teacher Management
            'view-teachers',
            'create-teachers',
            'edit-teachers',
            'delete-teachers',
            'restore-teachers',
            'export-teachers',
            'view-teacher-profile',
            'edit-teacher-profile',
            'view-teacher-schedule',
            'manage-teacher-schedule',
            'view-teacher-attendance',
            'manage-teacher-attendance',
            'view-teacher-salary',
            'manage-teacher-salary',
            'generate-teacher-payslip',
            'view-teacher-performance',

            // Full Staff Management
            'view-staff',
            'create-staff',
            'edit-staff',
            'delete-staff',
            'restore-staff',
            'export-staff',
            'view-staff-profile',
            'edit-staff-profile',

            // Full Subject Management
            'view-subjects',
            'create-subjects',
            'edit-subjects',
            'delete-subjects',
            'restore-subjects',

            // Full Package Management
            'view-packages',
            'create-packages',
            'edit-packages',
            'delete-packages',
            'restore-packages',
            'manage-package-subjects',
            'view-package-pricing',
            'edit-package-pricing',

            // Full Class Management
            'view-classes',
            'create-classes',
            'edit-classes',
            'delete-classes',
            'restore-classes',
            'view-class-details',
            'manage-class-schedule',
            'assign-class-teacher',
            'view-class-students',
            'manage-class-enrollment',
            'view-class-attendance',
            'manage-class-sessions',

            // Full Enrollment Management
            'view-enrollments',
            'create-enrollments',
            'edit-enrollments',
            'delete-enrollments',
            'cancel-enrollments',
            'suspend-enrollments',
            'activate-enrollments',
            'view-enrollment-history',
            'manage-enrollment-fees',

            // Full Trial Class Management
            'view-trial-classes',
            'create-trial-classes',
            'edit-trial-classes',
            'delete-trial-classes',
            'approve-trial-classes',
            'convert-trial-classes',
            'mark-trial-attendance',

            // Full Attendance Management
            'view-student-attendance-all',
            'mark-student-attendance',
            'edit-student-attendance',
            'delete-student-attendance',
            'export-student-attendance',
            'view-attendance-reports',
            'generate-attendance-summary',
            'view-teacher-attendance-all',
            'mark-teacher-attendance',
            'edit-teacher-attendance',
            'delete-teacher-attendance',
            'export-teacher-attendance',

            // Full Invoice & Billing
            'view-invoices',
            'create-invoices',
            'edit-invoices',
            'delete-invoices',
            'cancel-invoices',
            'send-invoices',
            'export-invoices',
            'view-invoice-details',
            'generate-bulk-invoices',
            'apply-invoice-discount',
            'view-overdue-invoices',
            'manage-installments',

            // Full Payment Management
            'view-payments',
            'create-payments',
            'edit-payments',
            'delete-payments',
            'refund-payments',
            'export-payments',
            'view-payment-history',
            'process-cash-payment',
            'process-qr-payment',
            'process-online-payment',
            'verify-payments',
            'view-payment-reports',

            // Full Discount & Voucher
            'view-discounts',
            'create-discounts',
            'edit-discounts',
            'delete-discounts',
            'apply-discounts',
            'view-vouchers',
            'create-vouchers',
            'redeem-vouchers',

            // Full Referral Management
            'view-referrals',
            'create-referrals',
            'manage-referrals',
            'view-referral-vouchers',
            'generate-referral-vouchers',

            // Full Material Management
            'view-materials',
            'create-materials',
            'edit-materials',
            'delete-materials',
            'upload-materials',
            'approve-materials',
            'download-materials',
            'view-material-access',
            'manage-material-access',
            'view-physical-materials',
            'create-physical-materials',
            'edit-physical-materials',
            'delete-physical-materials',
            'manage-material-collection',
            'record-material-collection',

            // Full Exam & Result Management
            'view-exams',
            'create-exams',
            'edit-exams',
            'delete-exams',
            'view-exam-results',
            'create-exam-results',
            'edit-exam-results',
            'delete-exam-results',
            'publish-exam-results',
            'export-exam-results',
            'generate-result-cards',

            // Full Timetable Management
            'view-timetable',
            'create-timetable',
            'edit-timetable',
            'delete-timetable',
            'export-timetable',
            'view-own-timetable',

            // Full Announcement Management
            'view-announcements',
            'create-announcements',
            'edit-announcements',
            'delete-announcements',
            'publish-announcements',
            'send-announcements',

            // Full Notification Management
            'view-notifications',
            'create-notifications',
            'send-notifications',
            'manage-notification-templates',
            'view-notification-logs',
            'send-whatsapp-notifications',
            'send-email-notifications',
            'send-sms-notifications',

            // Full Seminar Management
            'view-seminars',
            'create-seminars',
            'edit-seminars',
            'delete-seminars',
            'manage-seminar-registration',
            'view-seminar-participants',
            'manage-seminar-participants',
            'export-seminar-participants',
            'view-seminar-expenses',
            'create-seminar-expenses',
            'edit-seminar-expenses',
            'delete-seminar-expenses',
            'view-seminar-reports',

            // Full Cafeteria & POS
            'view-inventory',
            'create-inventory',
            'edit-inventory',
            'delete-inventory',
            'manage-inventory-stock',
            'view-inventory-reports',
            'export-inventory',
            'view-inventory-categories',
            'create-inventory-categories',
            'edit-inventory-categories',
            'delete-inventory-categories',
            'access-pos',
            'process-pos-sale',
            'void-pos-transaction',
            'refund-pos-transaction',
            'view-pos-transactions',
            'export-pos-transactions',
            'view-daily-cash-report',
            'close-daily-cash-report',
            'manage-cash-drawer',

            // Full Expense Management
            'view-expenses',
            'create-expenses',
            'edit-expenses',
            'delete-expenses',
            'approve-expenses',
            'reject-expenses',
            'export-expenses',
            'view-expense-categories',
            'create-expense-categories',
            'edit-expense-categories',
            'delete-expense-categories',

            // Full Financial Reporting
            'view-financial-dashboard',
            'view-revenue-reports',
            'view-expense-reports',
            'view-profit-loss-reports',
            'view-arrears-reports',
            'view-collection-reports',
            'export-financial-reports',
            'view-category-revenue',
            'generate-financial-summary',

            // Calendar & Events
            'view-calendar',
            'create-events',
            'edit-events',
            'delete-events',
            'view-holidays',
            'create-holidays',
            'edit-holidays',
            'delete-holidays',

            // Reviews
            'view-reviews',
            'edit-reviews',
            'delete-reviews',
            'approve-reviews',

            // Limited Settings (no system-level)
            'view-settings',
            'edit-settings',
            'manage-general-settings',
            'manage-payment-settings',
            'manage-notification-settings',
            'view-activity-logs',

            // Reports
            'view-reports',
            'generate-reports',
            'export-reports',
            'schedule-reports',
            'view-student-reports',
            'view-teacher-reports',
            'view-class-reports',
            'view-payment-analytics',
            'view-enrollment-analytics',
        ];

        $admin->syncPermissions($adminPermissions);
        $this->command->info('✓ Admin role created with ' . count($adminPermissions) . ' permissions');

        // ===================================================================
        // STAFF - Teaching and Attendance Management
        // ===================================================================
        $staff = Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'web']);
        
        $staffPermissions = [
            // Dashboard
            'view-dashboard',
            'view-staff-dashboard',

            // Limited Student Management
            'view-students',
            'create-students',
            'view-student-profile',
            'view-student-history',
            'view-student-attendance',

            // Limited Parent Management
            'view-parents',
            'create-parents',
            'view-parent-profile',
            'view-parent-children',

            // View Teachers
            'view-teachers',
            'view-teacher-profile',
            'view-teacher-schedule',

            // View Subjects & Packages
            'view-subjects',
            'view-packages',
            'view-package-pricing',

            // View Classes
            'view-classes',
            'view-class-details',
            'view-class-students',
            'view-class-attendance',

            // Limited Enrollment
            'view-enrollments',
            'create-enrollments',
            'view-enrollment-history',

            // Trial Class Management
            'view-trial-classes',
            'create-trial-classes',
            'edit-trial-classes',
            'mark-trial-attendance',

            // Attendance (Main Responsibility)
            'view-student-attendance-all',
            'mark-student-attendance',
            'edit-student-attendance',
            'export-student-attendance',
            'view-attendance-reports',
            'view-teacher-attendance-all',
            'mark-teacher-attendance',
            'edit-teacher-attendance',

            // Limited Invoice & Payment
            'view-invoices',
            'view-invoice-details',
            'view-payments',
            'create-payments',
            'view-payment-history',
            'process-cash-payment',
            'process-qr-payment',

            // Physical Materials
            'view-physical-materials',
            'record-material-collection',

            // View Materials
            'view-materials',
            'view-material-access',

            // Timetable
            'view-timetable',
            'view-own-timetable',

            // Announcements (view only)
            'view-announcements',

            // Notifications (view)
            'view-notifications',

            // Seminars (view & register)
            'view-seminars',
            'view-seminar-participants',

            // Cafeteria & POS
            'view-inventory',
            'manage-inventory-stock',
            'access-pos',
            'process-pos-sale',
            'view-pos-transactions',
            'view-daily-cash-report',

            // Calendar
            'view-calendar',
            'view-holidays',

            // Reviews (view)
            'view-reviews',

            // Limited Reports
            'view-reports',
            'view-student-reports',
            'view-class-reports',
        ];

        $staff->syncPermissions($staffPermissions);
        $this->command->info('✓ Staff role created with ' . count($staffPermissions) . ' permissions');

        // ===================================================================
        // TEACHER - Teaching and Material Management
        // ===================================================================
        $teacher = Role::firstOrCreate(['name' => 'teacher', 'guard_name' => 'web']);
        
        $teacherPermissions = [
            // Dashboard
            'view-dashboard',
            'view-teacher-dashboard',

            // View Own Students
            'view-students',
            'view-student-profile',
            'view-student-attendance',
            'view-student-results',

            // Own Profile
            'view-teacher-profile',
            'edit-teacher-profile',
            'view-teacher-schedule',
            'view-own-timetable',

            // View Subjects
            'view-subjects',

            // Own Classes
            'view-classes',
            'view-class-details',
            'view-class-students',
            'view-class-attendance',

            // Attendance for Own Classes
            'mark-student-attendance',
            'view-attendance-reports',

            // Material Management (Main Responsibility)
            'view-materials',
            'create-materials',
            'edit-materials',
            'delete-materials',
            'upload-materials',
            'view-material-access',

            // Exam & Results for Own Classes
            'view-exams',
            'create-exams',
            'edit-exams',
            'view-exam-results',
            'create-exam-results',
            'edit-exam-results',
            'export-exam-results',

            // Timetable
            'view-timetable',

            // Announcements (view)
            'view-announcements',

            // Notifications (view)
            'view-notifications',

            // Calendar
            'view-calendar',
            'view-holidays',

            // Reviews
            'view-reviews',
            'view-own-reviews',
        ];

        $teacher->syncPermissions($teacherPermissions);
        $this->command->info('✓ Teacher role created with ' . count($teacherPermissions) . ' permissions');

        // ===================================================================
        // PARENT - View Student Progress, Make Payments
        // ===================================================================
        $parent = Role::firstOrCreate(['name' => 'parent', 'guard_name' => 'web']);
        
        $parentPermissions = [
            // Dashboard
            'view-dashboard',
            'view-parent-dashboard',

            // Own Profile
            'view-parent-profile',
            'edit-parent-profile',
            'view-parent-children',

            // View Own Children's Data
            'view-student-profile',
            'view-student-history',
            'view-student-payments',
            'view-student-attendance',
            'view-student-materials',
            'view-student-results',

            // View Classes (children's)
            'view-classes',
            'view-class-details',

            // View Enrollments (children's)
            'view-enrollments',
            'view-enrollment-history',

            // Invoices & Payments (Main Responsibility)
            'view-invoices',
            'view-invoice-details',
            'view-payments',
            'view-payment-history',
            'process-online-payment',

            // Materials (view only)
            'view-materials',

            // Exam Results (children's)
            'view-exam-results',

            // Timetable (children's)
            'view-timetable',
            'view-own-timetable',

            // Announcements
            'view-announcements',

            // Notifications
            'view-notifications',

            // Seminars (register children)
            'view-seminars',
            'register-for-seminar',

            // Calendar
            'view-calendar',
            'view-holidays',

            // Reviews
            'view-reviews',
            'create-reviews',
            'view-own-reviews',
        ];

        $parent->syncPermissions($parentPermissions);
        $this->command->info('✓ Parent role created with ' . count($parentPermissions) . ' permissions');

        // ===================================================================
        // STUDENT - Access Materials, View Schedule
        // ===================================================================
        $student = Role::firstOrCreate(['name' => 'student', 'guard_name' => 'web']);
        
        $studentPermissions = [
            // Dashboard
            'view-dashboard',
            'view-student-dashboard',

            // Own Profile
            'view-student-profile',
            'view-student-history',
            'view-student-payments',
            'view-student-attendance',
            'view-student-materials',
            'view-student-results',

            // View Classes (enrolled)
            'view-classes',
            'view-class-details',

            // View Enrollments (own)
            'view-enrollments',
            'view-enrollment-history',

            // View Invoices & Payments (own)
            'view-invoices',
            'view-invoice-details',
            'view-payments',
            'view-payment-history',

            // Materials (Main Access)
            'view-materials',

            // Exam Results (own)
            'view-exam-results',

            // Timetable (own)
            'view-timetable',
            'view-own-timetable',

            // Announcements
            'view-announcements',

            // Notifications
            'view-notifications',

            // Seminars
            'view-seminars',
            'register-for-seminar',

            // Calendar
            'view-calendar',
            'view-holidays',

            // Reviews
            'view-reviews',
            'create-reviews',
            'view-own-reviews',
        ];

        $student->syncPermissions($studentPermissions);
        $this->command->info('✓ Student role created with ' . count($studentPermissions) . ' permissions');

        $this->command->info('');
        $this->command->info('===========================================');
        $this->command->info('✓ All roles created and permissions assigned!');
        $this->command->info('===========================================');
    }
}
