<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\CheckUserStatus;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\Admin\ParentController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Staff\StudentRegistrationController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\PackageController;
use App\Http\Controllers\Public\OnlineRegistrationController;
use App\Http\Controllers\Parent\ChildRegistrationController;
use App\Http\Controllers\Admin\StudentApprovalController;
use App\Http\Controllers\Admin\StudentReviewController;
use App\Http\Controllers\Admin\ClassController;
use App\Http\Controllers\Admin\ClassScheduleController;
use App\Http\Controllers\Admin\TrialClassController;
use App\Http\Controllers\Admin\StudentProfileController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\MessageTemplateController;
use App\Http\Controllers\Admin\ReferralController;
use App\Http\Controllers\Admin\MaterialController;
use App\Http\Controllers\Admin\PhysicalMaterialController;
use App\Http\Controllers\Teacher\MaterialController as TeacherMaterialController;
use App\Http\Controllers\Student\MaterialController as StudentMaterialController;
use App\Http\Controllers\Parent\MaterialController as ParentMaterialController;
use App\Http\Controllers\Admin\TimetableController;
use App\Http\Controllers\Admin\AnnouncementController;
use App\Http\Controllers\Admin\ExamController;
use App\Http\Controllers\Admin\ExamResultController;
use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\AttendanceReportController;
use App\Http\Controllers\Parent\AttendanceController as ParentAttendanceController;
use App\Http\Controllers\Admin\InvoiceController as AdminInvoiceController;
use App\Http\Controllers\Parent\InvoiceController as ParentInvoiceController;
use App\Http\Controllers\Student\InvoiceController as StudentInvoiceController;
use App\Http\Controllers\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Staff\PaymentController as StaffPaymentController;
use App\Http\Controllers\Parent\PaymentController as ParentPaymentController;
use App\Http\Controllers\Student\PaymentController as StudentPaymentController;
use App\Http\Controllers\Admin\PaymentGatewayConfigController;
use App\Http\Controllers\OnlinePaymentController;
use App\Http\Controllers\Admin\InstallmentController;
use App\Http\Controllers\Admin\PaymentReminderController;
use App\Http\Controllers\Admin\ArrearsController;
use App\Http\Controllers\Teacher\TeacherProfileController;
use App\Http\Controllers\Teacher\TeacherScheduleController;
use App\Http\Controllers\Teacher\TeacherClassController;
use App\Http\Controllers\Teacher\TeacherStudentController;
use App\Http\Controllers\Teacher\TeacherDocumentController as TeacherOwnDocumentController;
use App\Http\Controllers\Admin\TeacherDocumentController;
use App\Http\Controllers\Admin\TeacherPayslipController;
use App\Http\Controllers\Teacher\PayslipController;
use App\Http\Controllers\Admin\TeacherPerformanceController;
use App\Http\Controllers\Teacher\PerformanceController;
use App\Http\Controllers\Admin\ExpenseController;
use App\Http\Controllers\Admin\ExpenseCategoryController;
use App\Http\Controllers\Admin\RevenueController;
use App\Http\Controllers\Admin\FinancialDashboardController;
use App\Http\Controllers\Admin\SeminarController;
use App\Http\Controllers\Public\SeminarRegistrationController;
use App\Http\Controllers\Admin\SeminarAccountingController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

// Welcome/Landing Page
Route::get('/', function () {
    return view('welcome');
})->name('home');

/*
|--------------------------------------------------------------------------
| Public Online Registration Routes (No Auth Required)
|--------------------------------------------------------------------------
*/
Route::prefix('register')->name('public.registration.')->group(function () {
    // Landing page
    Route::get('/', [OnlineRegistrationController::class, 'index'])->name('index');

    // Student registration form
    Route::get('/student', [OnlineRegistrationController::class, 'showStudentForm'])->name('student');
    Route::post('/student', [OnlineRegistrationController::class, 'registerStudent'])->name('student.submit');

    // Success page
    Route::get('/success', [OnlineRegistrationController::class, 'success'])->name('success');

    // AJAX endpoints
    Route::get('/validate-referral', [OnlineRegistrationController::class, 'validateReferralCode'])->name('validate-referral');
    Route::get('/check-email', [OnlineRegistrationController::class, 'checkEmail'])->name('check-email');
});

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    // Login
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);

    // Register (Parent self-registration)
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);

    // Forgot Password
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');

    // Reset Password
    Route::get('/reset-password', [ResetPasswordController::class, 'showResetForm'])->name('password.reset.form');
    Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');
});

/*
|--------------------------------------------------------------------------
| Public seminors
|--------------------------------------------------------------------------
*/
Route::prefix('seminars')->name('public.seminars.')->group(function () {
    // Browse seminars
    Route::get('/', [SeminarRegistrationController::class, 'index'])->name('index');

    // View seminar details
    Route::get('/{seminar}', [SeminarRegistrationController::class, 'show'])->name('show');

    // Registration form
    Route::get('/{seminar}/register', [SeminarRegistrationController::class, 'register'])->name('register');
    Route::post('/{seminar}/register', [SeminarRegistrationController::class, 'submitRegistration'])->name('submit');

    // Registration success page
    Route::get('/registration/success', [SeminarRegistrationController::class, 'success'])->name('success');

    // AJAX endpoints
    Route::get('/ajax/check-email', [SeminarRegistrationController::class, 'checkEmail'])->name('check-email');
    Route::get('/ajax/{seminar}/current-fee', [SeminarRegistrationController::class, 'getCurrentFee'])->name('current-fee');
    Route::get('/ajax/{seminar}/availability', [SeminarRegistrationController::class, 'checkAvailability'])->name('availability');
});


/*
|--------------------------------------------------------------------------
| Online Payment Routes (Public - No Auth Required)
|--------------------------------------------------------------------------
| These routes handle payment callbacks and webhooks from payment gateways.
| They must be accessible without authentication.
*/

// Payment Gateway Callbacks (return URL from gateway)
Route::get('/payment/callback/{gateway}', [OnlinePaymentController::class, 'callback'])
    ->name('payment.callback');

// Payment Gateway Webhooks (server-to-server notification)
Route::post('/payment/webhook/{gateway}', [OnlinePaymentController::class, 'webhook'])
    ->name('payment.webhook')
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

// Payment Status Pages (accessible without auth for redirect from gateway)
Route::get('/payment/success', [OnlinePaymentController::class, 'success'])
    ->name('payment.success');

Route::get('/payment/failed', [OnlinePaymentController::class, 'failed'])
    ->name('payment.failed');

Route::get('/payment/pending', [OnlinePaymentController::class, 'pending'])
    ->name('payment.pending');

// Logout
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', CheckUserStatus::class])->group(function () {

    // Default Dashboard (redirects based on role)
    Route::get('/dashboard', function () {
        $user = auth()->user();

        if ($user->hasRole(['super-admin', 'admin'])) {
            return redirect()->route('admin.dashboard');
        } elseif ($user->hasRole('staff')) {
            return redirect()->route('staff.dashboard');
        } elseif ($user->hasRole('teacher')) {
            return redirect()->route('teacher.dashboard');
        } elseif ($user->hasRole('parent')) {
            return redirect()->route('parent.dashboard');
        } elseif ($user->hasRole('student')) {
            return redirect()->route('student.dashboard');
        }

        return redirect()->route('login');
    })->name('dashboard');

    // Payment Checkout & Processing
    Route::middleware('auth')->group(function () {
        // Checkout page
        Route::get('/payment/checkout/{invoice}', [OnlinePaymentController::class, 'checkout'])
            ->name('payment.checkout');

        // Process payment (redirect to gateway)
        Route::post('/payment/process/{invoice}', [OnlinePaymentController::class, 'processPayment'])
            ->name('payment.process');

        // Check payment status (AJAX)
        Route::get('/payment/status/{transactionId}', [OnlinePaymentController::class, 'checkStatus'])
            ->name('payment.check-status');

        // Retry failed payment
        Route::get('/payment/retry/{transaction}', [OnlinePaymentController::class, 'retry'])
            ->name('payment.retry');

        // Cancel pending payment
        Route::post('/payment/cancel/{transaction}', [OnlinePaymentController::class, 'cancel'])
            ->name('payment.cancel');

        // Invoice payment page (for students/parents)
        Route::get('/payment/invoice/{invoice}', [OnlinePaymentController::class, 'invoicePayment'])
            ->name('payment.invoice');
    });

    /*
    |--------------------------------------------------------------------------
    | TIMETABLE ROUTES
    |--------------------------------------------------------------------------
    */
    Route::get('/timetable/export', [TimetableController::class, 'export'])->name('timetable.export');
    Route::get('/timetable', [TimetableController::class, 'index'])->name('timetable.index');
    Route::get('/timetable/filter-by-class', [TimetableController::class, 'filterByClass'])->name('timetable.filter.class');
    Route::get('/timetable/filter-by-teacher', [TimetableController::class, 'filterByTeacher'])->name('timetable.filter.teacher');
    Route::get('/timetable/print', [TimetableController::class, 'print'])->name('timetable.print');
    Route::get('/announcements', [AnnouncementController::class, 'index'])->name('announcements.index');
    Route::get('/announcements/{announcement}', [AnnouncementController::class, 'show'])->name('announcements.show');

    /*
    |--------------------------------------------------------------------------
    | Profile Routes (All Authenticated Users)
    |--------------------------------------------------------------------------
    */
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'index'])->name('index');
        Route::get('/edit', [ProfileController::class, 'edit'])->name('edit');
        Route::put('/update', [ProfileController::class, 'update'])->name('update');
        Route::get('/change-password', [ProfileController::class, 'showChangePasswordForm'])->name('change-password');
        Route::put('/change-password', [ProfileController::class, 'updatePassword'])->name('password.update');
    });

    /*
    |--------------------------------------------------------------------------
    | Admin Routes (Super Admin & Admin)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:super-admin|admin'])->prefix('admin')->name('admin.')->group(function () {
        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'adminDashboard'])->name('dashboard');

        // Staff Management
        Route::resource('staff', StaffController::class);
        Route::patch('/staff/{staff}/toggle-status', [StaffController::class, 'toggleStatus'])->name('staff.toggle-status');
        Route::get('/staff-export', [TeacherController::class, 'export'])->name('staff.export');

        // Teacher Management
        Route::resource('teachers', TeacherController::class);
        Route::patch('/teachers/{teacher}/toggle-status', [TeacherController::class, 'toggleStatus'])->name('teachers.toggle-status');
        Route::get('/teachers-export', [TeacherController::class, 'export'])->name('teachers.export');

        // Parent Management
        Route::resource('parents', ParentController::class);
        Route::patch('/parents/{parent}/toggle-status', [ParentController::class, 'toggleStatus'])->name('parents.toggle-status');
        Route::get('/parents-export', [ParentController::class, 'export'])->name('parents.export');

        // Student Management
        Route::resource('students', StudentController::class);
        Route::patch('/students/{student}/toggle-status', [StudentController::class, 'toggleStatus'])->name('students.toggle-status');
        Route::patch('/students/{student}/approve', [StudentController::class, 'approve'])->name('students.approve');
        Route::patch('/students/{student}/reject', [StudentController::class, 'reject'])->name('students.reject');
        Route::get('/students-export', [StudentController::class, 'export'])->name('students.export');

        // SUBJECT MANAGEMENT
        Route::resource('subjects', SubjectController::class);
        Route::patch('/subjects/{subject}/toggle-status', [SubjectController::class, 'toggleStatus'])->name('subjects.toggle-status');
        Route::post('/subjects/{id}/restore', [SubjectController::class, 'restore'])->name('subjects.restore');

        // PACKAGE MANAGEMENT
        Route::resource('packages', PackageController::class);
        Route::patch('/packages/{package}/toggle-status', [PackageController::class, 'toggleStatus'])->name('packages.toggle-status');
        Route::post('/packages/{id}/restore', [PackageController::class, 'restore'])->name('packages.restore');
        Route::post('/packages/{package}/duplicate', [PackageController::class, 'duplicate'])->name('packages.duplicate');
        Route::get('/packages/{package}/pricing', [PackageController::class, 'getPricing'])->name('packages.pricing');

        // Notification Management
        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/', [NotificationController::class, 'index'])->name('index');
            Route::get('/logs', [NotificationController::class, 'logs'])->name('logs');
            Route::get('/send', [NotificationController::class, 'create'])->name('create');
            Route::post('/send', [NotificationController::class, 'send'])->name('send');

            // Queue Management
            Route::get('/whatsapp-queue', [NotificationController::class, 'whatsappQueue'])->name('whatsapp-queue');
            Route::get('/email-queue', [NotificationController::class, 'emailQueue'])->name('email-queue');

            // Process Queues
            Route::post('/process-whatsapp', [NotificationController::class, 'processWhatsappQueue'])->name('process-whatsapp');
            Route::post('/process-email', [NotificationController::class, 'processEmailQueue'])->name('process-email');

            // Retry Failed
            Route::post('/retry-whatsapp', [NotificationController::class, 'retryWhatsapp'])->name('retry-whatsapp');
            Route::post('/retry-email', [NotificationController::class, 'retryEmail'])->name('retry-email');

            // Cancel Message
            Route::delete('/cancel/{type}/{id}', [NotificationController::class, 'cancelMessage'])->name('cancel');

            // Test Connections
            Route::post('/test-whatsapp', [NotificationController::class, 'testWhatsapp'])->name('test-whatsapp');
            Route::post('/test-email', [NotificationController::class, 'testEmail'])->name('test-email');

            // Settings
            Route::get('/settings', [NotificationController::class, 'settings'])->name('settings');
            Route::post('/settings', [NotificationController::class, 'updateSettings'])->name('settings.update');
        });

        // Message Templates
        Route::prefix('templates')->name('templates.')->group(function () {
            Route::get('/', [MessageTemplateController::class, 'index'])->name('index');
            Route::get('/create', [MessageTemplateController::class, 'create'])->name('create');
            Route::post('/', [MessageTemplateController::class, 'store'])->name('store');
            Route::get('/{template}', [MessageTemplateController::class, 'show'])->name('show');
            Route::get('/{template}/edit', [MessageTemplateController::class, 'edit'])->name('edit');
            Route::put('/{template}', [MessageTemplateController::class, 'update'])->name('update');
            Route::delete('/{template}', [MessageTemplateController::class, 'destroy'])->name('destroy');
            Route::post('/{template}/toggle-status', [MessageTemplateController::class, 'toggleStatus'])->name('toggle-status');
            Route::post('/{template}/duplicate', [MessageTemplateController::class, 'duplicate'])->name('duplicate');
            Route::get('/{template}/preview', [MessageTemplateController::class, 'preview'])->name('preview');
        });

        // Student Approval Queue
        Route::prefix('approvals')->name('approvals.')->group(function () {
            // Pending approvals list
            Route::get('/', [StudentApprovalController::class, 'index'])->name('index');

            // Approval history
            Route::get('/history', [StudentApprovalController::class, 'history'])->name('history');

            // Review single student
            Route::get('/{student}', [StudentApprovalController::class, 'show'])->name('show');

            // Approve student
            Route::patch('/{student}/approve', [StudentApprovalController::class, 'approve'])->name('approve');

            // Reject student
            Route::patch('/{student}/reject', [StudentApprovalController::class, 'reject'])->name('reject');

            // Request more information
            Route::post('/{student}/request-info', [StudentApprovalController::class, 'requestInfo'])->name('request-info');

            // Bulk approve
            Route::post('/bulk-approve', [StudentApprovalController::class, 'bulkApprove'])->name('bulk-approve');

            // Resend welcome notification
            Route::post('/{student}/resend-welcome', [StudentApprovalController::class, 'resendWelcome'])->name('resend-welcome');
        });

        // =====================================================================
        // STUDENT PROFILE ROUTES (Chat 9)
        // =====================================================================
        Route::prefix('students')->name('students.')->group(function () {
            Route::get('{student}/profile', [StudentProfileController::class, 'show'])->name('profile')->middleware('permission:view-students');
            Route::get('{student}/history', [StudentProfileController::class, 'history'])->name('history')->middleware('permission:view-students');
            Route::post('{student}/regenerate-referral', [StudentProfileController::class, 'regenerateReferralCode'])->name('regenerate-referral')->middleware('permission:edit-students');
            Route::get('{student}/export-profile', [StudentProfileController::class, 'exportProfile'])->name('export-profile')->middleware('permission:export-students');
        });

        // =====================================================================
        // REFERRAL MANAGEMENT ROUTES (Chat 9)
        // =====================================================================
        Route::prefix('referrals')->name('referrals.')->group(function () {
            Route::get('/', [ReferralController::class, 'index'])->name('index')->middleware('permission:view-referrals');
            Route::get('/export', [ReferralController::class, 'export'])->name('export')->middleware('permission:view-referrals');
            Route::get('/vouchers', [ReferralController::class, 'vouchers'])->name('vouchers')->middleware('permission:view-referral-vouchers');
            Route::post('/vouchers/generate', [ReferralController::class, 'generateVoucher'])->name('vouchers.generate')->middleware('permission:generate-referral-vouchers');
            Route::post('/vouchers/{voucher}/expire', [ReferralController::class, 'expireVoucher'])->name('vouchers.expire')->middleware('permission:manage-referrals');
            Route::get('/{referral}', [ReferralController::class, 'show'])->name('show')->middleware('permission:view-referrals');
            Route::post('/{referral}/complete', [ReferralController::class, 'complete'])->name('complete')->middleware('permission:manage-referrals');
            Route::post('/{referral}/cancel', [ReferralController::class, 'cancel'])->name('cancel')->middleware('permission:manage-referrals');
        });

        // =====================================================================
        // TRIAL CLASS MANAGEMENT ROUTES (Chat 9)
        // =====================================================================
        Route::prefix('trial-classes')->name('trial-classes.')->group(function () {
            Route::get('/', [TrialClassController::class, 'index'])->name('index')->middleware('permission:view-trial-classes');
            Route::get('/create', [TrialClassController::class, 'create'])->name('create')->middleware('permission:create-trial-classes');
            Route::post('/', [TrialClassController::class, 'store'])->name('store')->middleware('permission:create-trial-classes');
            Route::get('/export', [TrialClassController::class, 'export'])->name('export')->middleware('permission:view-trial-classes');
            Route::get('/{trialClass}', [TrialClassController::class, 'show'])->name('show')->middleware('permission:view-trial-classes');
            Route::post('/{trialClass}/update-status', [TrialClassController::class, 'updateStatus'])->name('update-status')->middleware('permission:edit-trial-classes');
            Route::post('/{trialClass}/mark-attendance', [TrialClassController::class, 'markAttendance'])->name('mark-attendance')->middleware('permission:mark-trial-attendance');
            Route::post('/{trialClass}/convert', [TrialClassController::class, 'convert'])->name('convert')->middleware('permission:convert-trial-classes');
            Route::post('/{trialClass}/decline', [TrialClassController::class, 'decline'])->name('decline')->middleware('permission:edit-trial-classes');
            Route::delete('/{trialClass}', [TrialClassController::class, 'destroy'])->name('destroy')->middleware('permission:delete-trial-classes');
        });

        // =====================================================================
        // STUDENT REVIEWS ROUTES (Chat 9)
        // =====================================================================
        Route::prefix('reviews')->name('reviews.')->group(function () {
            Route::get('/', [StudentReviewController::class, 'index'])->name('index')->middleware('permission:view-reviews');
            Route::get('/create', [StudentReviewController::class, 'create'])->name('create')->middleware('permission:edit-reviews');
            Route::post('/', [StudentReviewController::class, 'store'])->name('store')->middleware('permission:edit-reviews');
            Route::get('/export', [StudentReviewController::class, 'export'])->name('export')->middleware('permission:view-reviews');
            Route::post('/bulk-approve', [StudentReviewController::class, 'bulkApprove'])->name('bulk-approve')->middleware('permission:approve-reviews');
            Route::get('/{review}', [StudentReviewController::class, 'show'])->name('show')->middleware('permission:view-reviews');
            Route::get('/{review}/edit', [StudentReviewController::class, 'edit'])->name('edit')->middleware('permission:edit-reviews');
            Route::put('/{review}', [StudentReviewController::class, 'update'])->name('update')->middleware('permission:edit-reviews');
            Route::post('/{review}/approve', [StudentReviewController::class, 'approve'])->name('approve')->middleware('permission:approve-reviews');
            Route::post('/{review}/reject', [StudentReviewController::class, 'reject'])->name('reject')->middleware('permission:approve-reviews');
            Route::delete('/{review}', [StudentReviewController::class, 'destroy'])->name('destroy')->middleware('permission:delete-reviews');
        });

        // ===================================================================
        // CLASS SCHEDULE MANAGEMENT ROUTES
        // ===================================================================
        Route::get('/classes-export', [ClassController::class, 'export'])->middleware('permission:view-classes')->name('classes.export');
        Route::get('/timetable', [ClassScheduleController::class, 'timetable'])->middleware('permission:view-classes')->name('classes.timetable');
        Route::get('/classes', [ClassController::class, 'index'])->middleware('permission:view-classes')->name('classes.index');
        // CREATE ROUTE - MUST BE BEFORE {class} ROUTES!
        Route::get('/classes/create', [ClassController::class, 'create'])->middleware('permission:create-classes')->name('classes.create');
        Route::post('/classes', [ClassController::class, 'store'])->middleware('permission:create-classes')->name('classes.store');
        // EDIT ROUTE - MUST BE BEFORE {class} SHOW ROUTE!
        Route::get('/classes/{class}/edit', [ClassController::class, 'edit'])->middleware('permission:edit-classes')->name('classes.edit');
        Route::put('/classes/{class}', [ClassController::class, 'update'])->middleware('permission:edit-classes')->name('classes.update');
        Route::patch('/classes/{class}', [ClassController::class, 'update'])->middleware('permission:edit-classes');
        Route::patch('/classes/{class}/toggle-status', [ClassController::class, 'toggleStatus'])->middleware('permission:edit-classes')->name('classes.toggle-status');
        // SHOW ROUTE - MUST COME AFTER /create AND /{class}/edit!
        Route::get('/classes/{class}', [ClassController::class, 'show'])->middleware('permission:view-classes')->name('classes.show');
        Route::delete('/classes/{class}', [ClassController::class, 'destroy'])->middleware('permission:delete-classes')->name('classes.destroy');

        // Schedule routes
        Route::prefix('classes/{class}/schedule')->name('classes.schedule.')->middleware('permission:manage-class-schedule')->group(function () {
            Route::get('/data', [ClassScheduleController::class, 'getSchedule'])->name('data');
            Route::get('/', [ClassScheduleController::class, 'index'])->name('index');
            Route::post('/', [ClassScheduleController::class, 'store'])->name('store');
            Route::patch('/{schedule}/toggle-status', [ClassScheduleController::class, 'toggleStatus'])->name('toggle-status');
            Route::put('/{schedule}', [ClassScheduleController::class, 'update'])->name('update');
            Route::delete('/{schedule}', [ClassScheduleController::class, 'destroy'])->name('destroy');
        });

        // Digital Materials
        Route::resource('materials', MaterialController::class);
        Route::patch('/materials/{material}/approve', [MaterialController::class, 'approve'])->name('materials.approve');
        Route::get('/materials/{material}/download', [MaterialController::class, 'download'])->name('materials.download');

        // Physical Materials
        Route::resource('physical-materials', PhysicalMaterialController::class);
        Route::get('/physical-materials/{physicalMaterial}/collections', [PhysicalMaterialController::class, 'collections'])->name('physical-materials.collections');
        Route::post('/physical-materials/{physicalMaterial}/record-collection', [PhysicalMaterialController::class, 'recordCollection'])->name('physical-materials.record-collection');

        Route::resource('announcements', AnnouncementController::class);
        Route::post('announcements/{announcement}/publish', [AnnouncementController::class, 'publish'])->name('announcements.publish');
        Route::post('announcements/{announcement}/archive', [AnnouncementController::class, 'archive'])->name('announcements.archive');
        Route::post('announcements/{announcement}/toggle-pin', [AnnouncementController::class, 'togglePin'])->name('announcements.toggle-pin');
        Route::post('announcements/{announcement}/mark-read', [AnnouncementController::class, 'markAsRead'])->name('announcements.mark-read');
        Route::delete('announcements/{announcement}/attachment/{index}', [AnnouncementController::class, 'deleteAttachment'])->name('announcements.delete-attachment');
        Route::resource('exams', ExamController::class);
        Route::post('exams/{exam}/update-status', [ExamController::class, 'updateStatus'])->name('exams.update-status');
        Route::get('exams/{exam}/students', [ExamController::class, 'getStudents'])->name('exams.students');
        Route::post('exams/{exam}/duplicate', [ExamController::class, 'duplicate'])->name('exams.duplicate');
        Route::get('exams/{exam}/results', [ExamResultController::class, 'index'])->name('exam-results.index');
        Route::get('exams/{exam}/results/create', [ExamResultController::class, 'create'])->name('exam-results.create');
        Route::post('exams/{exam}/results/bulk-store', [ExamResultController::class, 'bulkStore'])->name('exam-results.bulk-store');
        Route::get('exam-results/{result}/edit', [ExamResultController::class, 'edit'])->name('exam-results.edit');
        Route::put('exam-results/{result}', [ExamResultController::class, 'update'])->name('exam-results.update');
        Route::delete('exam-results/{result}', [ExamResultController::class, 'destroy'])->name('exam-results.destroy');
        Route::post('exams/{exam}/results/publish', [ExamResultController::class, 'publish'])->name('exam-results.publish');
        Route::post('exams/{exam}/results/unpublish', [ExamResultController::class, 'unpublish'])->name('exam-results.unpublish');
        Route::get('exam-results/{result}/card', [ExamResultController::class, 'resultCard'])->name('exam-results.card');
        Route::get('exam-results/{result}/download', [ExamResultController::class, 'downloadResultCard'])->name('exam-results.download');
        Route::get('exams/{exam}/export', [ExamResultController::class, 'export'])->name('exam-results.export');
        Route::get('exams/{exam}/statistics', [ExamResultController::class, 'statistics'])->name('exam-results.statistics');
        Route::post('exam-results/auto-calculate', [ExamResultController::class, 'autoCalculate'])->name('exam-results.auto-calculate');

        // ==================== ATTENDANCE MANAGEMENT ROUTES ====================
        Route::prefix('attendance')->name('attendance.')->group(function () {
            // Dashboard
            Route::get('/', [AttendanceController::class, 'index'])->middleware('permission:view-student-attendance-all|view-teacher-attendance-all')->name('index');
            // Student Attendance
            Route::prefix('student')->name('student.')->group(function () {
                Route::get('/mark', [AttendanceController::class, 'markStudent'])->middleware('permission:mark-student-attendance')->name('mark');
                Route::post('/mark', [AttendanceController::class, 'storeStudent'])->middleware('permission:mark-student-attendance')->name('store');
                Route::get('/calendar', [AttendanceController::class, 'studentCalendar'])->middleware('permission:view-student-attendance-all')->name('calendar');
            });

            // Teacher Attendance
            Route::prefix('teacher')->name('teacher.')->group(function () {
                Route::get('/mark', [AttendanceController::class, 'markTeacher'])->middleware('permission:mark-teacher-attendance')->name('mark');
                Route::post('/mark', [AttendanceController::class, 'storeTeacher'])->middleware('permission:mark-teacher-attendance')->name('store');
                Route::get('/calendar', [AttendanceController::class, 'teacherCalendar'])->middleware('permission:view-teacher-attendance-all')->name('calendar');
            });

            // AJAX Endpoints
            Route::get('/get-sessions', [AttendanceController::class, 'getSessions'])->name('get-sessions');
            Route::get('/session/{session}/summary', [AttendanceController::class, 'getSessionSummary'])->name('session.summary');

            /*
            |--------------------------------------------------------------------------
            | Attendance Reports Routes
            |--------------------------------------------------------------------------
            */
            Route::prefix('reports')->name('reports.')->group(function () {
                // Reports Dashboard
                Route::get('/', [AttendanceReportController::class, 'index'])->name('index');

                // Student Reports
                Route::get('/student', [AttendanceReportController::class, 'studentReport'])->name('student');
                Route::get('/export/student', [AttendanceReportController::class, 'exportStudent'])->name('export-student');
                Route::post('/email-parent', [AttendanceReportController::class, 'emailToParent'])->name('email-parent');

                // Class Reports
                Route::get('/class', [AttendanceReportController::class, 'classReport'])->name('class');
                Route::get('/export/class', [AttendanceReportController::class, 'exportClass'])->name('export-class');

                // Low Attendance Alerts
                Route::get('/low-attendance', [AttendanceReportController::class, 'lowAttendance'])->name('low-attendance');
                Route::post('/send-alert', [AttendanceReportController::class, 'sendAlert'])->name('send-alert');
                Route::post('/bulk-alerts', [AttendanceReportController::class, 'sendBulkAlerts'])->name('bulk-alerts');

                // Attendance History
                Route::get('/history', [AttendanceReportController::class, 'history'])->name('history');
                Route::get('/export/history', [AttendanceReportController::class, 'exportHistory'])->name('export-history');

                // Resend Notification
                Route::post('/resend-notification/{attendance}', [AttendanceReportController::class, 'resendNotification'])->name('resend-notification');
            });

        });

        // Invoice Management
        Route::prefix('invoices')->name('invoices.')->group(function () {
            Route::get('/', [AdminInvoiceController::class, 'index'])->name('index');
            Route::get('/create', [AdminInvoiceController::class, 'create'])->name('create');
            Route::post('/', [AdminInvoiceController::class, 'store'])->name('store');
            Route::get('/overdue', [AdminInvoiceController::class, 'overdue'])->name('overdue');
            Route::get('/bulk-generate', [AdminInvoiceController::class, 'bulkGenerateForm'])->name('bulk-generate');
            Route::post('/bulk-generate', [AdminInvoiceController::class, 'bulkGenerate'])->name('bulk-generate.store');
            Route::get('/export', [AdminInvoiceController::class, 'export'])->name('export');
            Route::get('/{invoice}', [AdminInvoiceController::class, 'show'])->name('show');
            Route::get('/{invoice}/edit', [AdminInvoiceController::class, 'edit'])->name('edit');
            Route::put('/{invoice}', [AdminInvoiceController::class, 'update'])->name('update');
            Route::delete('/{invoice}', [AdminInvoiceController::class, 'destroy'])->name('destroy');
            Route::post('/{invoice}/send', [AdminInvoiceController::class, 'send'])->name('send');
            Route::post('/{invoice}/cancel', [AdminInvoiceController::class, 'cancel'])->name('cancel');
            Route::post('/{invoice}/reminder', [AdminInvoiceController::class, 'sendReminder'])->name('reminder');
        });

        // Billing Management
        Route::prefix('billing')->name('billing.')->group(function () {
            Route::get('/payment-cycles', [AdminInvoiceController::class, 'paymentCycles'])->name('payment-cycles');
            Route::get('/subscription-alerts', [AdminInvoiceController::class, 'subscriptionAlerts'])->name('subscription-alerts');
            Route::post('/renew-enrollment/{enrollment}', [AdminInvoiceController::class, 'renewEnrollment'])->name('renew-enrollment');
        });

        // Payment Management
        Route::prefix('payments')->name('payments.')->group(function () {
            Route::get('/', [AdminPaymentController::class, 'index'])->name('index');
            Route::get('/create', [AdminPaymentController::class, 'create'])->name('create');
            Route::post('/', [AdminPaymentController::class, 'store'])->name('store');
            Route::get('/history', [AdminPaymentController::class, 'history'])->name('history');
            Route::get('/daily-report', [AdminPaymentController::class, 'dailyReport'])->name('daily-report');
            Route::post('/daily-report/update', [AdminPaymentController::class, 'updateDailyReport'])->name('daily-report.update');
            Route::post('/daily-report/close', [AdminPaymentController::class, 'closeDailyReport'])->name('daily-report.close');
            Route::get('/pending-verifications', [AdminPaymentController::class, 'pendingVerifications'])->name('pending-verifications');
            Route::get('/export', [AdminPaymentController::class, 'export'])->name('export');

            // Student invoices (AJAX)
            Route::get('/student/{student}/invoices', [AdminPaymentController::class, 'getStudentInvoices'])->name('student.invoices');

            // Single payment operations
            Route::get('/{payment}', [AdminPaymentController::class, 'show'])->name('show');
            Route::get('/{payment}/receipt', [AdminPaymentController::class, 'receipt'])->name('receipt');
            Route::get('/{payment}/download-receipt', [AdminPaymentController::class, 'downloadReceipt'])->name('download-receipt');
            Route::get('/{payment}/print-receipt', [AdminPaymentController::class, 'printReceipt'])->name('print-receipt');
            Route::post('/{payment}/verify', [AdminPaymentController::class, 'verify'])->name('verify');
            Route::post('/{payment}/refund', [AdminPaymentController::class, 'refund'])->name('refund');
        });

        /*
        |--------------------------------------------------------------------------
        | Payment Gateway Configuration Routes (Admin)
        |--------------------------------------------------------------------------
        | These routes are for managing payment gateway configurations.
        | Add these inside your existing admin route group.
        */

        // Payment Gateway Management
        Route::prefix('payment-gateways')->name('payment-gateways.')->group(function () {
            Route::get('/', [PaymentGatewayConfigController::class, 'index'])
                // ->middleware('permission:view-payment-gateway-configs')
                ->name('index');

            Route::get('/create', [PaymentGatewayConfigController::class, 'create'])
                // ->middleware('permission:create-payment-gateway-configs')
                ->name('create');

            Route::post('/', [PaymentGatewayConfigController::class, 'store'])
                // ->middleware('permission:create-payment-gateway-configs')
                ->name('store');

            Route::get('/{paymentGateway}', [PaymentGatewayConfigController::class, 'show'])
                // ->middleware('permission:view-payment-gateway-configs')
                ->name('show');

            Route::get('/{paymentGateway}/edit', [PaymentGatewayConfigController::class, 'edit'])
                // ->middleware('permission:edit-payment-gateway-configs')
                ->name('edit');

            Route::put('/{paymentGateway}', [PaymentGatewayConfigController::class, 'update'])
                // ->middleware('permission:edit-payment-gateway-configs')
                ->name('update');

            Route::delete('/{paymentGateway}', [PaymentGatewayConfigController::class, 'destroy'])
                // ->middleware('permission:delete-payment-gateway-configs')
                ->name('destroy');

            // Additional routes
            Route::patch('/{paymentGateway}/toggle-status', [PaymentGatewayConfigController::class, 'toggleStatus'])
                // ->middleware('permission:edit-payment-gateway-configs')
                ->name('toggle-status');

            Route::get('/{paymentGateway}/transactions', [PaymentGatewayConfigController::class, 'transactions'])
                // ->middleware('permission:view-payment-gateway-configs')
                ->name('transactions');

            Route::post('/{paymentGateway}/test', [PaymentGatewayConfigController::class, 'testConnection'])
                // ->middleware('permission:edit-payment-gateway-configs')
                ->name('test');

            Route::post('/{paymentGateway}/set-default', [PaymentGatewayConfigController::class, 'setDefault'])
                // ->middleware('permission:edit-payment-gateway-configs')
                ->name('set-default');

            Route::post('/transactions/{transaction}/refresh', [PaymentGatewayConfigController::class, 'refreshTransaction'])
                // ->middleware('permission:edit-payment-gateway-configs')
                ->name('transactions.refresh');
        });

        /*
        |--------------------------------------------------------------------------
        | Installment Management Routes (Admin)
        |--------------------------------------------------------------------------
        */
        Route::prefix('installments')->name('installments.')->group(function () {
            Route::get('/overdue', [InstallmentController::class, 'overdue'])->name('overdue');
            Route::get('/export', [InstallmentController::class, 'export'])->name('export');
            Route::get('/create', [InstallmentController::class, 'create'])->name('create');
            Route::post('/', [InstallmentController::class, 'store'])->name('store');
            Route::post('/bulk-reminder', [InstallmentController::class, 'bulkReminder'])->name('bulk-reminder');
            Route::get('/student/{student}/history', [InstallmentController::class, 'studentHistory'])->name('student-history');
            Route::post('/update-overdue-status', [InstallmentController::class, 'updateOverdueStatus'])->name('update-overdue-status');
            Route::get('/', [InstallmentController::class, 'index'])->name('index');
            Route::get('/{invoice}', [InstallmentController::class, 'show'])->name('show');
            Route::delete('/{invoice}/cancel', [InstallmentController::class, 'cancel'])->name('cancel');
            Route::patch('/installment/{installment}', [InstallmentController::class, 'updateInstallment'])->name('update-installment');
            Route::post('/installment/{installment}/payment', [InstallmentController::class, 'recordPayment'])->name('record-payment');
            Route::post('/installment/{installment}/reminder', [InstallmentController::class, 'sendReminder'])->name('send-reminder');
        });

        /*
        |--------------------------------------------------------------------------
        | Payment Reminder Routes (Admin)
        |--------------------------------------------------------------------------
        */
        Route::prefix('reminders')->name('reminders.')->group(function () {
            Route::get('/', [PaymentReminderController::class, 'index'])->name('index');
            Route::get('/upcoming', [PaymentReminderController::class, 'upcoming'])->name('upcoming');
            Route::get('/logs', [PaymentReminderController::class, 'logs'])->name('logs');
            Route::get('/export', [PaymentReminderController::class, 'export'])->name('export');
            Route::get('/settings', [PaymentReminderController::class, 'settings'])->name('settings');
            Route::put('/settings', [PaymentReminderController::class, 'updateSettings'])->name('update-settings');
            Route::post('/schedule-monthly', [PaymentReminderController::class, 'scheduleMonthly'])->name('schedule-monthly');
            Route::post('/send-now', [PaymentReminderController::class, 'sendNow'])->name('send-now');
            Route::post('/send-overdue', [PaymentReminderController::class, 'sendOverdueReminders'])->name('send-overdue');
            Route::post('/retry-failed', [PaymentReminderController::class, 'retryFailed'])->name('retry-failed');
            Route::post('/bulk-cancel', [PaymentReminderController::class, 'bulkCancel'])->name('bulk-cancel');
            Route::post('/send-follow-up/{invoice}', [PaymentReminderController::class, 'sendFollowUp'])->name('send-follow-up');
            Route::get('/trigger-scheduler', [PaymentReminderController::class, 'triggerScheduler'])->name('trigger-scheduler');
            Route::get('/{reminder}', [PaymentReminderController::class, 'show'])->name('show');
            Route::post('/{reminder}/resend', [PaymentReminderController::class, 'resend'])->name('resend');
            Route::delete('/{reminder}', [PaymentReminderController::class, 'cancel'])->name('cancel');
        });

        /*
        |--------------------------------------------------------------------------
        | Arrears Management Routes (Admin)
        |--------------------------------------------------------------------------
        */
        Route::prefix('arrears')->name('arrears.')->group(function () {
            Route::get('/', [ArrearsController::class, 'index'])->name('index');
            Route::get('/student/{student}', [ArrearsController::class, 'student'])->name('student');
            Route::get('/students-list', [ArrearsController::class, 'studentsWithArrears'])->name('students-list');
            Route::get('/by-class', [ArrearsController::class, 'byClass'])->name('by-class');
            Route::get('/by-subject', [ArrearsController::class, 'bySubject'])->name('by-subject');
            Route::get('/due-report', [ArrearsController::class, 'dueReport'])->name('due-report');
            Route::get('/forecast', [ArrearsController::class, 'forecast'])->name('forecast');
            Route::get('/aging-analysis', [ArrearsController::class, 'agingAnalysis'])->name('aging-analysis');
            Route::post('/send-bulk-reminders', [ArrearsController::class, 'sendBulkReminders'])->name('send-bulk-reminders');
            Route::post('/flag-student/{student}', [ArrearsController::class, 'flagStudent'])->name('flag-student');
            Route::get('/export', [ArrearsController::class, 'export'])->name('export');
            Route::get('/print', [ArrearsController::class, 'print'])->name('print');
            Route::get('/summary', [ArrearsController::class, 'getSummary'])->name('summary');
            Route::post('/daily-update', [ArrearsController::class, 'dailyUpdate'])->name('daily-update');
        });

        // Teacher Document Management (Admin)
        Route::prefix('teachers/{teacher}/documents')->name('teachers.documents.')->group(function () {
            Route::get('/', [TeacherDocumentController::class, 'index'])
                // ->middleware('permission:view-teachers')
                ->name('index');

            Route::post('/', [TeacherDocumentController::class, 'store'])
                ->middleware('permission:edit-teachers')
                ->name('store');
        });

        // Teacher Document Actions (without teacher prefix - uses document directly)
        Route::prefix('teachers/documents')->name('teachers.documents.')->group(function () {
            Route::get('/{document}/download', [TeacherDocumentController::class, 'download'])
                ->middleware('permission:view-teachers')
                ->name('download');

            Route::get('/{document}/view', [TeacherDocumentController::class, 'view'])
                ->middleware('permission:view-teachers')
                ->name('view');

            Route::post('/{document}/verify', [TeacherDocumentController::class, 'verify'])
                ->middleware('permission:edit-teachers')
                ->name('verify');

            Route::post('/{document}/reject', [TeacherDocumentController::class, 'reject'])
                ->middleware('permission:edit-teachers')
                ->name('reject');

            Route::delete('/{document}', [TeacherDocumentController::class, 'destroy'])
                ->middleware('permission:delete-teachers')
                ->name('destroy');

            Route::post('/bulk-verify', [TeacherDocumentController::class, 'bulkVerify'])
                ->middleware('permission:edit-teachers')
                ->name('bulk-verify');
        });

        // Teacher Schedule Assignment (Admin)
        Route::prefix('teachers/{teacher}')->name('teachers.')->group(function () {
            Route::get('/schedule-assign', [TeacherController::class, 'scheduleAssign'])
                ->middleware('permission:manage-teacher-schedule')
                ->name('schedule-assign');

            Route::post('/assign-class', [TeacherController::class, 'assignClass'])
                ->middleware('permission:manage-teacher-schedule')
                ->name('assign-class');

            Route::post('/schedule/quick-add', [TeacherController::class, 'quickAddSchedule'])
                ->middleware('permission:manage-teacher-schedule')
                ->name('schedule.quick-add');
        });

        // Teacher Payslip Management (Chat 20)
        Route::prefix('teacher-payslips')->name('teacher-payslips.')->group(function () {
            Route::get('/', [TeacherPayslipController::class, 'index'])->name('index')->middleware('permission:view-teacher-salary');
            Route::get('/create', [TeacherPayslipController::class, 'create'])->name('create')->middleware('permission:generate-teacher-payslip');
            Route::post('/', [TeacherPayslipController::class, 'store'])->name('store')->middleware('permission:generate-teacher-payslip');
            Route::get('/export', [TeacherPayslipController::class, 'export'])->name('export')->middleware('permission:view-teacher-salary');
            Route::post('/calculate-preview', [TeacherPayslipController::class, 'calculatePreview'])->name('calculate-preview')->middleware('permission:generate-teacher-payslip');
            Route::get('/{payslip}', [TeacherPayslipController::class, 'show'])->name('show')->middleware('permission:view-teacher-salary');
            Route::get('/{payslip}/print', [TeacherPayslipController::class, 'print'])->name('print')->middleware('permission:view-teacher-salary');
            Route::put('/{payslip}/update-status', [TeacherPayslipController::class, 'updateStatus'])->name('update-status')->middleware('permission:manage-teacher-salary');
            Route::delete('/{payslip}', [TeacherPayslipController::class, 'destroy'])->name('destroy')->middleware('permission:manage-teacher-salary');
        });

        Route::prefix('teacher-performance')->name('teacher-performance.')->group(function () {
            Route::get('/', [TeacherPerformanceController::class, 'index'])->name('index');
            Route::get('/comparison', [TeacherPerformanceController::class, 'comparison'])->name('comparison');
            Route::get('/reports', [TeacherPerformanceController::class, 'reports'])->name('reports');
            Route::get('/export', [TeacherPerformanceController::class, 'export'])->name('export');
            Route::get('/{teacher}', [TeacherPerformanceController::class, 'show'])->name('show');
            Route::get('/{teacher}/data', [TeacherPerformanceController::class, 'getData'])->name('get-data');
        });

        /*
        |--------------------------------------------------------------------------
        | FINANCIAL MANAGEMENT ROUTES (Chat 21 - Revenue & Expense Tracking)
        |--------------------------------------------------------------------------
        */
        // Expense Management
        Route::resource('expenses', ExpenseController::class);
        Route::post('expenses/{expense}/approve', [ExpenseController::class, 'approve'])->name('expenses.approve');
        Route::post('expenses/{expense}/reject', [ExpenseController::class, 'reject'])->name('expenses.reject');
        Route::get('expenses/{expense}/download-receipt', [ExpenseController::class, 'downloadReceipt'])->name('expenses.download-receipt');
        Route::get('expenses-export', [ExpenseController::class, 'export'])->name('expenses.export');
        Route::post('expenses-generate-recurring', [ExpenseController::class, 'generateRecurring'])->name('expenses.generate-recurring');
        Route::post('expenses-sync-salaries', [ExpenseController::class, 'syncTeacherSalaries'])->name('expenses.sync-salaries');

        // Expense Categories Management
        Route::resource('expense-categories', ExpenseCategoryController::class);

        // Revenue Tracking
        Route::prefix('revenue')->name('revenue.')->group(function () {
            Route::get('/', [RevenueController::class, 'index'])->name('index');
            Route::get('/by-category', [RevenueController::class, 'byCategory'])->name('by-category');
            Route::get('/period-summary', [RevenueController::class, 'getPeriodSummary'])->name('period-summary');
            Route::get('/export', [RevenueController::class, 'export'])->name('export');
            Route::get('/chart-data', [RevenueController::class, 'getChartData'])->name('chart-data');
        });

        // Financial Dashboard & Reports
        Route::prefix('financial')->name('financial.')->group(function () {

            // Main Dashboard
            Route::get('/dashboard', [FinancialDashboardController::class, 'index'])
                ->name('dashboard')
                ->middleware('permission:view-financial-dashboard');

            // Reports Index
            Route::get('/reports', [FinancialDashboardController::class, 'reports'])
                ->name('reports')
                ->middleware('permission:view-financial-dashboard');

            // Specific Reports
            Route::prefix('reports')->name('reports.')->group(function () {

                // Profit & Loss Statement
                Route::get('/profit-loss', [FinancialDashboardController::class, 'profitLoss'])
                    ->name('profit-loss')
                    ->middleware('permission:view-profit-loss-reports');

                // Category Revenue Analysis
                Route::get('/category-revenue', [FinancialDashboardController::class, 'categoryRevenue'])
                    ->name('category-revenue')
                    ->middleware('permission:view-category-revenue');

                // Cash Flow Analysis
                Route::get('/cash-flow', [FinancialDashboardController::class, 'cashFlow'])
                    ->name('cash-flow')
                    ->middleware('permission:view-financial-dashboard');
            });

            // Excel Exports
            Route::prefix('export')->name('export.')->group(function () {

                // Comprehensive Report
                Route::get('/comprehensive', [FinancialDashboardController::class, 'exportComprehensive'])
                    ->name('comprehensive')
                    ->middleware('permission:export-financial-reports');

                // Profit & Loss Excel
                Route::get('/profit-loss', [FinancialDashboardController::class, 'exportProfitLoss'])
                    ->name('profit-loss')
                    ->middleware('permission:export-financial-reports');

                // Category Revenue Excel
                Route::get('/category-revenue', [FinancialDashboardController::class, 'exportCategoryRevenue'])
                    ->name('category-revenue')
                    ->middleware('permission:export-financial-reports');

                // Cash Flow Excel
                Route::get('/cash-flow', [FinancialDashboardController::class, 'exportCashFlow'])
                    ->name('cash-flow')
                    ->middleware('permission:export-financial-reports');

                // Legacy CSV Export
                Route::get('/summary', [FinancialDashboardController::class, 'exportSummary'])
                    ->name('summary')
                    ->middleware('permission:export-financial-reports');
            });

            // PDF Downloads
            Route::prefix('download')->name('download.')->group(function () {

                // Profit & Loss PDF
                Route::get('/profit-loss-pdf', [FinancialDashboardController::class, 'downloadProfitLossPdf'])
                    ->name('profit-loss-pdf')
                    ->middleware('permission:export-financial-reports');

                // Comprehensive PDF
                Route::get('/comprehensive-pdf', [FinancialDashboardController::class, 'downloadComprehensivePdf'])
                    ->name('comprehensive-pdf')
                    ->middleware('permission:export-financial-reports');

                // Category Revenue PDF
                Route::get('/category-revenue-pdf', [FinancialDashboardController::class, 'downloadCategoryRevenuePdf'])
                    ->name('category-revenue-pdf')
                    ->middleware('permission:export-financial-reports');
            });

            // AJAX Endpoints
            Route::get('/api/category-breakdown', [FinancialDashboardController::class, 'getCategoryBreakdown'])
                ->name('api.category-breakdown');

            Route::get('/api/cash-flow', [FinancialDashboardController::class, 'getCashFlow'])
                ->name('api.cash-flow');

            Route::get('/api/chart-data', [FinancialDashboardController::class, 'getChartData'])
                ->name('api.chart-data');
        });

        // Seminors
        Route::prefix('seminars')->name('seminars.')->group(function () {
            // Seminar CRUD
            Route::get('/', [SeminarController::class, 'index'])->name('index')->middleware('permission:view-seminars');
            Route::get('/create', [SeminarController::class, 'create'])->name('create')->middleware('permission:create-seminars');
            Route::post('/', [SeminarController::class, 'store'])->name('store')->middleware('permission:create-seminars');
            Route::get('/{seminar}', [SeminarController::class, 'show'])->name('show')->middleware('permission:view-seminar-participants');
            Route::get('/{seminar}/edit', [SeminarController::class, 'edit'])->name('edit')->middleware('permission:edit-seminars');
            Route::put('/{seminar}', [SeminarController::class, 'update'])->name('update')->middleware('permission:edit-seminars');
            Route::delete('/{seminar}', [SeminarController::class, 'destroy'])->name('destroy')->middleware('permission:delete-seminars');

            // Status management
            Route::post('/{seminar}/update-status', [SeminarController::class, 'updateStatus'])->name('update-status')->middleware('permission:edit-seminars');

            // Participant management
            Route::get('/{seminar}/participants', [SeminarController::class, 'participants'])->name('participants')->middleware('permission:view-seminar-participants');
            Route::get('/{seminar}/export-participants', [SeminarController::class, 'exportParticipants'])->name('export-participants')->middleware('permission:export-seminar-participants');
            Route::post('/{seminar}/participants/{participant}/attendance', [SeminarController::class, 'markAttendance'])->name('participants.attendance')->middleware('permission:manage-seminar-participants');
            Route::post('/{seminar}/participants/{participant}/payment-status', [SeminarController::class, 'updatePaymentStatus'])->name('participants.payment-status')->middleware('permission:manage-seminar-participants');

            // Bulk actions
            Route::post('/{seminar}/bulk-notification', [SeminarController::class, 'sendBulkNotification'])->name('bulk-notification')->middleware('permission:manage-seminar-participants');
        });

        // Seminar Accounting Dashboard
        Route::get('/seminars/accounting/dashboard', [SeminarAccountingController::class, 'dashboard'])
            ->middleware('permission:view-seminar-reports')
            ->name('seminars.accounting.dashboard');

        // Seminar Expense Management
        Route::prefix('/seminars/{seminar}/accounting')->name('seminars.accounting.')->group(function () {

            // Expense List & CRUD
            Route::get('/expenses', [SeminarAccountingController::class, 'expenses'])
                ->middleware('permission:view-seminar-expenses')
                ->name('expenses');

            Route::get('/expenses/create', [SeminarAccountingController::class, 'createExpense'])
                ->middleware('permission:create-seminar-expenses')
                ->name('expenses.create');

            Route::post('/expenses', [SeminarAccountingController::class, 'storeExpense'])
                ->middleware('permission:create-seminar-expenses')
                ->name('expenses.store');

            Route::get('/expenses/{expense}/edit', [SeminarAccountingController::class, 'editExpense'])
                ->middleware('permission:edit-seminar-expenses')
                ->name('expenses.edit');

            Route::put('/expenses/{expense}', [SeminarAccountingController::class, 'updateExpense'])
                ->middleware('permission:edit-seminar-expenses')
                ->name('expenses.update');

            Route::delete('/expenses/{expense}', [SeminarAccountingController::class, 'destroyExpense'])
                ->middleware('permission:delete-seminar-expenses')
                ->name('expenses.destroy');

            // Expense Approval/Rejection
            Route::post('/expenses/{expense}/approve', [SeminarAccountingController::class, 'approveExpense'])
                ->middleware('permission:approve-expenses')
                ->name('expenses.approve');

            Route::post('/expenses/{expense}/reject', [SeminarAccountingController::class, 'rejectExpense'])
                ->middleware('permission:reject-expenses')
                ->name('expenses.reject');

            Route::delete('/expenses/{expense}/delete-receipt', [SeminarAccountingController::class, 'deleteReceipt'])
                ->middleware('permission:edit-seminar-expenses')
                ->name('expenses.delete-receipt');

            // Export Expenses
            Route::get('/expenses/export', [SeminarAccountingController::class, 'exportExpensesExcel'])
                ->middleware('permission:export-expenses')
                ->name('expenses.export');

            // Financial Report for Single Seminar
            Route::get('/reports/financial', [SeminarAccountingController::class, 'financialReport'])
                ->middleware('permission:view-seminar-reports')
                ->name('reports.financial');

            Route::get('/reports/financial/export-excel', [SeminarAccountingController::class, 'exportFinancialExcel'])
                ->middleware('permission:export-financial-reports')
                ->name('reports.financial.export-excel');

            Route::get('/reports/financial/export-pdf', [SeminarAccountingController::class, 'exportFinancialPdf'])
                ->middleware('permission:export-financial-reports')
                ->name('reports.financial.export-pdf');
        });

        // Profitability Report (All Seminars)
        Route::get('/seminars/accounting/reports/profitability', [SeminarAccountingController::class, 'profitabilityReport'])
            ->middleware('permission:view-seminar-reports')
            ->name('seminars.accounting.reports.profitability');

        Route::get('/seminars/accounting/reports/profitability/export-excel', [SeminarAccountingController::class, 'exportProfitabilityExcel'])
            ->middleware('permission:export-financial-reports')
            ->name('seminars.accounting.reports.profitability.export-excel');

        Route::get('/seminars/accounting/reports/profitability/export-pdf', [SeminarAccountingController::class, 'exportProfitabilityPdf'])
            ->middleware('permission:export-financial-reports')
            ->name('seminars.accounting.reports.profitability.export-pdf');

        // Payment Status Report
        Route::get('/seminars/accounting/reports/payment-status', [SeminarAccountingController::class, 'paymentStatusReport'])
            ->middleware('permission:view-seminar-reports')
            ->name('seminars.accounting.reports.payment-status');

    });

    /*
    |--------------------------------------------------------------------------
    | Staff Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:staff'])->prefix('staff')->name('staff.')->group(function () {
        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'staffDashboard'])->name('dashboard');

        // Student Registration
        Route::get('/registration/student', [StudentRegistrationController::class, 'createStudent'])->name('registration.create-student');
        Route::post('/registration/student', [StudentRegistrationController::class, 'storeStudent'])->name('registration.store-student');

        // Parent Registration
        Route::get('/registration/parent', [StudentRegistrationController::class, 'createParent'])->name('registration.create-parent');
        Route::post('/registration/parent', [StudentRegistrationController::class, 'storeParent'])->name('registration.store-parent');

        // Pending Registrations
        Route::get('/registration/pending', [StudentRegistrationController::class, 'pendingList'])->name('registration.pending');

        // AJAX: Search Parents
        Route::get('/registration/search-parent', [StudentRegistrationController::class, 'searchParent'])->name('registration.search-parent');

        // Payment Management
        Route::prefix('payments')->name('payments.')->group(function () {
            Route::get('/', [StaffPaymentController::class, 'index'])->name('index');
            Route::get('/create', [StaffPaymentController::class, 'create'])->name('create');
            Route::post('/', [StaffPaymentController::class, 'store'])->name('store');
            Route::get('/quick-payment', [StaffPaymentController::class, 'quickPayment'])->name('quick-payment');
            Route::get('/today-collection', [StaffPaymentController::class, 'todayCollection'])->name('today-collection');

            // Student invoices (AJAX)
            Route::get('/student/{student}/invoices', [StaffPaymentController::class, 'getStudentInvoices'])->name('student.invoices');

            // Single payment operations
            Route::get('/{payment}', [StaffPaymentController::class, 'show'])->name('show');
            Route::get('/{payment}/receipt', [StaffPaymentController::class, 'receipt'])->name('receipt');
            Route::get('/{payment}/print-receipt', [StaffPaymentController::class, 'printReceipt'])->name('print-receipt');
        });

    });

    /*
    |--------------------------------------------------------------------------
    | Teacher Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:teacher'])->prefix('teacher')->name('teacher.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'teacherDashboard'])->name('dashboard');
        // Teacher Profile Management
        Route::prefix('profile')->name('profile.')->group(function () {
            Route::get('/', [TeacherProfileController::class, 'index'])->name('index');
            Route::get('/edit', [TeacherProfileController::class, 'edit'])->name('edit');
            Route::put('/', [TeacherProfileController::class, 'update'])->name('update');
            Route::get('/change-password', [TeacherProfileController::class, 'showChangePassword'])->name('change-password');
            Route::post('/change-password', [TeacherProfileController::class, 'changePassword'])->name('update-password');
        });

        // Teacher Schedule Management
        Route::prefix('schedule')->name('schedule.')->group(function () {
            Route::get('/', [TeacherScheduleController::class, 'index'])->name('index');
            Route::get('/daily', [TeacherScheduleController::class, 'dailySchedule'])->name('daily');
            Route::get('/weekly', [TeacherScheduleController::class, 'weeklySchedule'])->name('weekly');
            Route::get('/monthly', [TeacherScheduleController::class, 'monthlySchedule'])->name('monthly');
            Route::get('/export', [TeacherScheduleController::class, 'export'])->name('export');
            Route::get('/sync-calendar', [TeacherScheduleController::class, 'syncCalendar'])->name('sync-calendar');
            Route::get('/session/{session}', [TeacherScheduleController::class, 'sessionDetails'])->name('session');
        });

        // Teacher Classes
        Route::prefix('classes')->name('classes.')->group(function () {
            Route::get('/', [TeacherClassController::class, 'index'])->name('index');
            Route::get('/{class}', [TeacherClassController::class, 'show'])->name('show');
            Route::get('/{class}/schedule', [TeacherClassController::class, 'schedule'])->name('schedule');
            Route::get('/{class}/students', [TeacherClassController::class, 'students'])->name('students');
            Route::post('/session/{session}/complete', [TeacherClassController::class, 'completeSession'])->name('session.complete');
            Route::post('/session/{session}/notes', [TeacherClassController::class, 'addSessionNotes'])->name('session.notes');
        });

        // Teacher Students
        Route::prefix('students')->name('students.')->group(function () {
            Route::get('/', [TeacherStudentController::class, 'index'])->name('index');
            Route::get('/{student}', [TeacherStudentController::class, 'show'])->name('show');
            Route::get('/{student}/attendance', [TeacherStudentController::class, 'attendance'])->name('attendance');
            Route::get('/{student}/results', [TeacherStudentController::class, 'results'])->name('results');
        });

        // Teacher Documents (Own Documents)
        Route::prefix('documents')->name('documents.')->group(function () {
            Route::get('/', [TeacherOwnDocumentController::class, 'index'])->name('index');
            Route::get('/create', [TeacherOwnDocumentController::class, 'create'])->name('create');
            Route::post('/', [TeacherOwnDocumentController::class, 'store'])->name('store');
            Route::get('/{document}', [TeacherOwnDocumentController::class, 'show'])->name('show');
            Route::get('/{document}/download', [TeacherOwnDocumentController::class, 'download'])->name('download');
            Route::delete('/{document}', [TeacherOwnDocumentController::class, 'destroy'])->name('destroy');
        });
        Route::resource('materials', TeacherMaterialController::class);

        // Teacher Payslips (Chat 20)
        Route::prefix('payslips')->name('payslips.')->group(function () {
            Route::get('/', [PayslipController::class, 'index'])->name('index');
            Route::get('/{payslip}', [PayslipController::class, 'show'])->name('show');
            Route::get('/{payslip}/print', [PayslipController::class, 'print'])->name('print');
        });

        Route::prefix('performance')->name('performance.')->group(function () {
            Route::get('/', [PerformanceController::class, 'index'])->name('index');
            Route::get('/analytics', [PerformanceController::class, 'analytics'])->name('analytics');
            Route::get('/data', [PerformanceController::class, 'getData'])->name('get-data');
        });

    });

    /*
    |--------------------------------------------------------------------------
    | Parent Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:parent'])->prefix('parent')->name('parent.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'parentDashboard'])->name('dashboard');

        // Children Management
        Route::get('/children', [ChildRegistrationController::class, 'index'])->name('children.index');
        Route::get('/children/register', [ChildRegistrationController::class, 'create'])->name('children.register');
        Route::post('/children/register', [ChildRegistrationController::class, 'store'])->name('children.store');
        Route::get('/children/{student}', [ChildRegistrationController::class, 'show'])->name('children.show');

        Route::get('/materials', [ParentMaterialController::class, 'index'])->name('materials.index');

        /*
        |--------------------------------------------------------------------------
        | Parent Attendance Routes
        |--------------------------------------------------------------------------
        */
        Route::prefix('attendance')->name('attendance.')->group(function () {
            Route::get('/', [ParentAttendanceController::class, 'index'])->name('index');
            Route::get('/child/{student}', [ParentAttendanceController::class, 'childAttendance'])->name('child');
        });

        // Parent Invoice Routes
        Route::prefix('invoices')->name('invoices.')->group(function () {
            Route::get('/', [ParentInvoiceController::class, 'index'])->name('index');
            Route::get('/history', [ParentInvoiceController::class, 'paymentHistory'])->name('history');
            Route::get('/{invoice}', [ParentInvoiceController::class, 'show'])->name('show');
        });

        // Payment Management (View Only)
        Route::prefix('payments')->name('payments.')->group(function () {
            Route::get('/', [ParentPaymentController::class, 'index'])->name('index');
            Route::get('/history', [ParentPaymentController::class, 'history'])->name('history');
            Route::get('/outstanding', [ParentPaymentController::class, 'outstanding'])->name('outstanding');
            Route::get('/{payment}', [ParentPaymentController::class, 'show'])->name('show');
            Route::get('/{payment}/receipt', [ParentPaymentController::class, 'receipt'])->name('receipt');
            Route::get('/{payment}/download-receipt', [ParentPaymentController::class, 'downloadReceipt'])->name('download-receipt');
        });

        // Parent Online Payment
        Route::get('/payments/pay-online/{invoice?}', [OnlinePaymentController::class, 'parentPayOnline'])
            // ->middleware('permission:make-payment')
            ->name('payments.pay-online');

    });

    /*
    |--------------------------------------------------------------------------
    | Student Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:student'])->prefix('student')->name('student.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'studentDashboard'])->name('dashboard');

        Route::get('/materials', [StudentMaterialController::class, 'index'])->name('materials.index');
        Route::get('/materials/{material}/view', [StudentMaterialController::class, 'view'])->name('materials.view');

        // Student Invoice Routes
        Route::prefix('invoices')->name('invoices.')->group(function () {
            Route::get('/', [StudentInvoiceController::class, 'index'])->name('index');
            Route::get('/history', [StudentInvoiceController::class, 'paymentHistory'])->name('history');
            Route::get('/{invoice}', [StudentInvoiceController::class, 'show'])->name('show');
        });

        // Payment Management (View Only)
        Route::prefix('payments')->name('payments.')->group(function () {
            Route::get('/', [StudentPaymentController::class, 'index'])->name('index');
            Route::get('/history', [StudentPaymentController::class, 'history'])->name('history');
            Route::get('/outstanding', [StudentPaymentController::class, 'outstanding'])->name('outstanding');
            Route::get('/{payment}', [StudentPaymentController::class, 'show'])->name('show');
            Route::get('/{payment}/receipt', [StudentPaymentController::class, 'receipt'])->name('receipt');
            Route::get('/{payment}/download-receipt', [StudentPaymentController::class, 'downloadReceipt'])->name('download-receipt');
        });

        // Student Online Payment
        Route::get('/payments/pay-online/{invoice?}', [OnlinePaymentController::class, 'studentPayOnline'])
            // ->middleware('permission:make-payment')
            ->name('payments.pay-online');
    });
});
