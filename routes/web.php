<?php

use Illuminate\Support\Facades\Route;
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
Route::middleware(['auth', \App\Http\Middleware\CheckUserStatus::class])->group(function () {

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
            Route::get('/', [App\Http\Controllers\Admin\NotificationController::class, 'index'])->name('index');
            Route::get('/logs', [App\Http\Controllers\Admin\NotificationController::class, 'logs'])->name('logs');
            Route::get('/send', [App\Http\Controllers\Admin\NotificationController::class, 'create'])->name('create');
            Route::post('/send', [App\Http\Controllers\Admin\NotificationController::class, 'send'])->name('send');

            // Queue Management
            Route::get('/whatsapp-queue', [App\Http\Controllers\Admin\NotificationController::class, 'whatsappQueue'])->name('whatsapp-queue');
            Route::get('/email-queue', [App\Http\Controllers\Admin\NotificationController::class, 'emailQueue'])->name('email-queue');

            // Process Queues
            Route::post('/process-whatsapp', [App\Http\Controllers\Admin\NotificationController::class, 'processWhatsappQueue'])->name('process-whatsapp');
            Route::post('/process-email', [App\Http\Controllers\Admin\NotificationController::class, 'processEmailQueue'])->name('process-email');

            // Retry Failed
            Route::post('/retry-whatsapp', [App\Http\Controllers\Admin\NotificationController::class, 'retryWhatsapp'])->name('retry-whatsapp');
            Route::post('/retry-email', [App\Http\Controllers\Admin\NotificationController::class, 'retryEmail'])->name('retry-email');

            // Cancel Message
            Route::delete('/cancel/{type}/{id}', [App\Http\Controllers\Admin\NotificationController::class, 'cancelMessage'])->name('cancel');

            // Test Connections
            Route::post('/test-whatsapp', [App\Http\Controllers\Admin\NotificationController::class, 'testWhatsapp'])->name('test-whatsapp');
            Route::post('/test-email', [App\Http\Controllers\Admin\NotificationController::class, 'testEmail'])->name('test-email');

            // Settings
            Route::get('/settings', [App\Http\Controllers\Admin\NotificationController::class, 'settings'])->name('settings');
            Route::post('/settings', [App\Http\Controllers\Admin\NotificationController::class, 'updateSettings'])->name('settings.update');
        });

        // Message Templates
        Route::prefix('templates')->name('templates.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\MessageTemplateController::class, 'index'])->name('index');
            Route::get('/create', [App\Http\Controllers\Admin\MessageTemplateController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\Admin\MessageTemplateController::class, 'store'])->name('store');
            Route::get('/{template}', [App\Http\Controllers\Admin\MessageTemplateController::class, 'show'])->name('show');
            Route::get('/{template}/edit', [App\Http\Controllers\Admin\MessageTemplateController::class, 'edit'])->name('edit');
            Route::put('/{template}', [App\Http\Controllers\Admin\MessageTemplateController::class, 'update'])->name('update');
            Route::delete('/{template}', [App\Http\Controllers\Admin\MessageTemplateController::class, 'destroy'])->name('destroy');
            Route::post('/{template}/toggle-status', [App\Http\Controllers\Admin\MessageTemplateController::class, 'toggleStatus'])->name('toggle-status');
            Route::post('/{template}/duplicate', [App\Http\Controllers\Admin\MessageTemplateController::class, 'duplicate'])->name('duplicate');
            Route::get('/{template}/preview', [App\Http\Controllers\Admin\MessageTemplateController::class, 'preview'])->name('preview');
        });

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

        // Additional teacher routes will be added in subsequent chats
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

    });

    /*
    |--------------------------------------------------------------------------
    | Student Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:student'])->prefix('student')->name('student.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'studentDashboard'])->name('dashboard');

        // Additional student routes will be added in subsequent chats
    });
});
