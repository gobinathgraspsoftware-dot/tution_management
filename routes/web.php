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

    });

    /*
    |--------------------------------------------------------------------------
    | Teacher Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:teacher'])->prefix('teacher')->name('teacher.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'teacherDashboard'])->name('dashboard');

        Route::resource('materials', TeacherMaterialController::class);
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
    });
});
