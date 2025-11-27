<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // ===================================================================
            // DASHBOARD PERMISSIONS
            // ===================================================================
            'view-dashboard',
            'view-admin-dashboard',
            'view-staff-dashboard',
            'view-teacher-dashboard',
            'view-parent-dashboard',
            'view-student-dashboard',

            // ===================================================================
            // USER MANAGEMENT PERMISSIONS
            // ===================================================================
            // General User Management
            'view-users',
            'create-users',
            'edit-users',
            'delete-users',
            'restore-users',
            'force-delete-users',
            'export-users',
            'import-users',

            // Role & Permission Management
            'view-roles',
            'create-roles',
            'edit-roles',
            'delete-roles',
            'assign-roles',
            'view-permissions',
            'create-permissions',
            'edit-permissions',
            'delete-permissions',
            'assign-permissions',

            // ===================================================================
            // STUDENT MANAGEMENT PERMISSIONS
            // ===================================================================
            'view-students',
            'create-students',
            'edit-students',
            'delete-students',
            'restore-students',
            'force-delete-students',
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

            // ===================================================================
            // PARENT MANAGEMENT PERMISSIONS
            // ===================================================================
            'view-parents',
            'create-parents',
            'edit-parents',
            'delete-parents',
            'restore-parents',
            'force-delete-parents',
            'export-parents',
            'view-parent-profile',
            'edit-parent-profile',
            'view-parent-children',
            'link-parent-student',

            // ===================================================================
            // TEACHER MANAGEMENT PERMISSIONS
            // ===================================================================
            'view-teachers',
            'create-teachers',
            'edit-teachers',
            'delete-teachers',
            'restore-teachers',
            'force-delete-teachers',
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

            // ===================================================================
            // STAFF MANAGEMENT PERMISSIONS
            // ===================================================================
            'view-staff',
            'create-staff',
            'edit-staff',
            'delete-staff',
            'restore-staff',
            'force-delete-staff',
            'export-staff',
            'view-staff-profile',
            'edit-staff-profile',

            // ===================================================================
            // SUBJECT MANAGEMENT PERMISSIONS
            // ===================================================================
            'view-subjects',
            'create-subjects',
            'edit-subjects',
            'delete-subjects',
            'restore-subjects',

            // ===================================================================
            // PACKAGE MANAGEMENT PERMISSIONS
            // ===================================================================
            'view-packages',
            'create-packages',
            'edit-packages',
            'delete-packages',
            'restore-packages',
            'manage-package-subjects',
            'view-package-pricing',
            'edit-package-pricing',

            // ===================================================================
            // CLASS MANAGEMENT PERMISSIONS
            // ===================================================================
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

            // ===================================================================
            // ENROLLMENT MANAGEMENT PERMISSIONS
            // ===================================================================
            'view-enrollments',
            'create-enrollments',
            'edit-enrollments',
            'delete-enrollments',
            'cancel-enrollments',
            'suspend-enrollments',
            'activate-enrollments',
            'view-enrollment-history',
            'manage-enrollment-fees',

            // ===================================================================
            // TRIAL CLASS PERMISSIONS
            // ===================================================================
            'view-trial-classes',
            'create-trial-classes',
            'edit-trial-classes',
            'delete-trial-classes',
            'approve-trial-classes',
            'convert-trial-classes',
            'mark-trial-attendance',

            // ===================================================================
            // ATTENDANCE MANAGEMENT PERMISSIONS
            // ===================================================================
            // Student Attendance
            'view-student-attendance-all',
            'mark-student-attendance',
            'edit-student-attendance',
            'delete-student-attendance',
            'export-student-attendance',
            'view-attendance-reports',
            'generate-attendance-summary',

            // Teacher Attendance
            'view-teacher-attendance-all',
            'mark-teacher-attendance',
            'edit-teacher-attendance',
            'delete-teacher-attendance',
            'export-teacher-attendance',

            // ===================================================================
            // INVOICE & BILLING PERMISSIONS
            // ===================================================================
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

            // ===================================================================
            // PAYMENT MANAGEMENT PERMISSIONS
            // ===================================================================
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

            // ===================================================================
            // DISCOUNT & VOUCHER PERMISSIONS
            // ===================================================================
            'view-discounts',
            'create-discounts',
            'edit-discounts',
            'delete-discounts',
            'apply-discounts',
            'view-vouchers',
            'create-vouchers',
            'redeem-vouchers',

            // ===================================================================
            // REFERRAL MANAGEMENT PERMISSIONS
            // ===================================================================
            'view-referrals',
            'create-referrals',
            'manage-referrals',
            'view-referral-vouchers',
            'generate-referral-vouchers',

            // ===================================================================
            // MATERIAL MANAGEMENT PERMISSIONS
            // ===================================================================
            'view-materials',
            'create-materials',
            'edit-materials',
            'delete-materials',
            'upload-materials',
            'approve-materials',
            'download-materials',
            'view-material-access',
            'manage-material-access',

            // Physical Materials
            'view-physical-materials',
            'create-physical-materials',
            'edit-physical-materials',
            'delete-physical-materials',
            'manage-material-collection',
            'record-material-collection',

            // ===================================================================
            // EXAM & RESULT PERMISSIONS
            // ===================================================================
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

            // ===================================================================
            // TIMETABLE PERMISSIONS
            // ===================================================================
            'view-timetable',
            'create-timetable',
            'edit-timetable',
            'delete-timetable',
            'export-timetable',
            'view-own-timetable',

            // ===================================================================
            // ANNOUNCEMENT PERMISSIONS
            // ===================================================================
            'view-announcements',
            'create-announcements',
            'edit-announcements',
            'delete-announcements',
            'publish-announcements',
            'send-announcements',

            // ===================================================================
            // NOTIFICATION PERMISSIONS
            // ===================================================================
            'view-notifications',
            'create-notifications',
            'send-notifications',
            'manage-notification-templates',
            'view-notification-logs',
            'send-whatsapp-notifications',
            'send-email-notifications',
            'send-sms-notifications',

            // ===================================================================
            // SEMINAR & PROGRAM PERMISSIONS
            // ===================================================================
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
            'register-for-seminar',

            // ===================================================================
            // CAFETERIA & POS PERMISSIONS
            // ===================================================================
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

            // ===================================================================
            // EXPENSE MANAGEMENT PERMISSIONS
            // ===================================================================
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

            // ===================================================================
            // FINANCIAL & REPORTING PERMISSIONS
            // ===================================================================
            'view-financial-dashboard',
            'view-revenue-reports',
            'view-expense-reports',
            'view-profit-loss-reports',
            'view-arrears-reports',
            'view-collection-reports',
            'export-financial-reports',
            'view-category-revenue',
            'generate-financial-summary',

            // ===================================================================
            // CALENDAR & EVENTS PERMISSIONS
            // ===================================================================
            'view-calendar',
            'create-events',
            'edit-events',
            'delete-events',
            'view-holidays',
            'create-holidays',
            'edit-holidays',
            'delete-holidays',

            // ===================================================================
            // REVIEW MANAGEMENT PERMISSIONS
            // ===================================================================
            'view-reviews',
            'create-reviews',
            'edit-reviews',
            'delete-reviews',
            'approve-reviews',
            'view-own-reviews',

            // ===================================================================
            // SYSTEM SETTINGS PERMISSIONS
            // ===================================================================
            'view-settings',
            'edit-settings',
            'manage-general-settings',
            'manage-payment-settings',
            'manage-notification-settings',
            'manage-payment-gateway',
            'view-system-logs',
            'view-activity-logs',
            'view-audit-trail',
            'manage-backups',
            'clear-cache',

            // ===================================================================
            // REPORT PERMISSIONS
            // ===================================================================
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

        // Create all permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission, 'guard_name' => 'web']
            );
        }

        $this->command->info('✓ Created ' . count($permissions) . ' permissions successfully!');
    }
}
