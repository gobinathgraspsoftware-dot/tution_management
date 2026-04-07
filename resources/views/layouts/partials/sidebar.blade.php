{{-- Admin/Super Admin Sidebar --}}
@role('super-admin|admin')

{{-- ==================== SECTION 1: MAIN DASHBOARD ==================== --}}
<div class="menu-dropdown">
    <a href="#section1" class="menu-section-title sidebar-bg-color" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Main
    </a>
    <div class="collapse" id="section1">
        <a href="{{ route('admin.dashboard') }}" class="menu-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="fas fa-home"></i> Dashboard
        </a>
    </div>
</div>
{{-- ==================== END: MAIN DASHBOARD ==================== --}}


{{-- ==================== SECTION 2: USER MANAGEMENT ==================== --}}
<div class="menu-dropdown">
    <a href="#section2" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> User Management
    </a>
    <div class="collapse" id="section2">
        <a href="{{ route('admin.roles.index') }}" class="menu-item {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
            <i class="fas fa-user-shield"></i> Roles
        </a>
        <a href="{{ route('admin.permissions.index') }}" class="menu-item {{ request()->routeIs('admin.permissions.*') ? 'active' : '' }}">
            <i class="fas fa-shield-alt"></i> Permissions
        </a>
        @can('view-students')
        <a href="{{ route('admin.students.index') }}" class="menu-item {{ request()->routeIs('admin.students.index') ? 'active' : '' }}">
            <i class="fas fa-users"></i> All Students
        </a>
        @endcan
        <a href="{{ route('admin.approvals.index') }}" class="menu-item {{ request()->routeIs('admin.approvals.*') ? 'active' : '' }}">
            <i class="fas fa-user-clock"></i> Pending Approvals
            @php
                $pendingCount = \App\Models\Student::pending()->count();
            @endphp
            @if($pendingCount > 0)
                <span class="badge bg-warning text-dark ms-auto">{{ $pendingCount }}</span>
            @endif
        </a>
        <a href="{{ route('admin.parents.index') }}" class="menu-item {{ request()->routeIs('admin.parents.*') ? 'active' : '' }}">
            <i class="fas fa-user-friends"></i> Parents
        </a>
        <a href="{{ route('admin.teachers.index') }}" class="menu-item {{ request()->routeIs('admin.teachers.*') ? 'active' : '' }}">
            <i class="fas fa-chalkboard-teacher"></i> Teachers
        </a>
        <a href="{{ route('admin.staff.index') }}" class="menu-item {{ request()->routeIs('admin.staff.*') ? 'active' : '' }}">
            <i class="fas fa-user-tie"></i> Staff
        </a>
    </div>
</div>
{{-- ==================== END: USER MANAGEMENT ==================== --}}


{{-- ==================== SECTION 3: ACADEMIC MANAGEMENT ==================== --}}
<div class="menu-dropdown">
    <a href="#section3" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Academic Management
    </a>
    <div class="collapse" id="section3">
        <a href="{{ route('admin.subjects.index') }}" class="menu-item {{ request()->routeIs('admin.subjects.*') ? 'active' : '' }}">
            <i class="fas fa-book"></i> Subjects
        </a>
        <a href="{{ route('admin.packages.index') }}" class="menu-item {{ request()->routeIs('admin.packages.*') ? 'active' : '' }}">
            <i class="fas fa-box"></i> Packages
        </a>
    </div>
</div>
{{-- ==================== END: ACADEMIC MANAGEMENT ==================== --}}


{{-- ==================== SECTION: ACADEMIC CONFIGURATION ==================== --}}
<div class="menu-dropdown">
    <a href="#sectionAcademicConfig" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Academic Configuration
    </a>
    <div class="collapse" id="sectionAcademicConfig">
        @if(Route::has('admin.grade-levels.index'))
        <a href="{{ route('admin.grade-levels.index') }}"
           class="menu-item {{ request()->routeIs('admin.grade-levels.*') ? 'active' : '' }}">
            <i class="fas fa-layer-group"></i> Grade Levels
        </a>
        @endif
    </div>
</div>
{{-- ==================== END: ACADEMIC CONFIGURATION ==================== --}}

{{-- ==================== SECTION 4: CLASS MANAGEMENT ==================== --}}
@canany(['view-classes', 'create-classes', 'manage-class-schedule'])
<div class="menu-dropdown">
    <a href="#section4" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Class Management
    </a>
    <div class="collapse" id="section4">
        @can('view-classes')
        <a href="{{ route('admin.classes.index') }}" class="menu-item {{ request()->routeIs('admin.classes.*') ? 'active' : '' }}">
            <i class="fas fa-chalkboard"></i> Classes
        </a>
        @endcan
        <a href="{{ route('timetable.index') }}" class="menu-item {{ request()->routeIs('timetable.index') ? 'active' : '' }}">
            <i class="fas fa-calendar-week"></i> Timetable
        </a>
        <a href="{{ route('admin.classes.timetable') }}" class="menu-item {{ request()->routeIs('admin.classes.timetable') ? 'active' : '' }}">
            <i class="fas fa-calendar-alt"></i> Weekly Timetable
        </a>
    </div>
</div>
@endcanany
{{-- ==================== END: CLASS MANAGEMENT ==================== --}}


{{-- ==================== SECTION 5: EXAMINATION MANAGEMENT ==================== --}}
@canany(['view-exams', 'create-exams', 'view-exam-results'])
<div class="menu-dropdown">
    <a href="#section5" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Examination Management
    </a>
    <div class="collapse" id="section5">
        @can('view-exams')
        <a href="{{ route('admin.exams.index') }}" class="menu-item {{ request()->routeIs('admin.exams.*') ? 'active' : '' }}">
            <i class="fas fa-file-alt"></i> All Exams
        </a>
        @endcan

        @can('create-exams')
        <a href="{{ route('admin.exams.create') }}" class="menu-item {{ request()->routeIs('admin.exams.create') ? 'active' : '' }}">
            <i class="fas fa-plus"></i> Create Exam
        </a>
        @endcan
    </div>
</div>
@endcanany
{{-- ==================== END: EXAMINATION MANAGEMENT ==================== --}}


{{-- ==================== SECTION 6: ENROLLMENT MANAGEMENT ==================== --}}
@if(Route::has('admin.enrollments.index'))
<div class="menu-dropdown">
    <a href="#section6" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Enrollment Management
    </a>
    <div class="collapse" id="section6">
        @can('view-enrollments')
        <a href="{{ route('admin.enrollments.index') }}" class="menu-item {{ request()->routeIs('admin.enrollments.index') ? 'active' : '' }}">
            <i class="fas fa-list"></i> All Enrollments
        </a>
        @endcan
        @can('create-enrollments')
        <a href="{{ route('admin.enrollments.create') }}" class="menu-item {{ request()->routeIs('admin.enrollments.create') ? 'active' : '' }}">
            <i class="fas fa-plus"></i> New Enrollment
        </a>
        @endcan
    </div>
</div>
@endif
{{-- ==================== END: ENROLLMENT MANAGEMENT ==================== --}}


{{-- ==================== SECTION 7: ATTENDANCE MANAGEMENT ==================== --}}
@canany(['view-student-attendance-all', 'view-teacher-attendance-all', 'mark-student-attendance', 'mark-teacher-attendance'])
<div class="menu-dropdown">
    <a href="#section7" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Attendance Management
    </a>
    <div class="collapse" id="section7">
        @can('view-student-attendance-all')
        <a href="{{ route('admin.attendance.index') }}" class="menu-item {{ request()->routeIs('admin.attendance.index') ? 'active' : '' }}">
            <i class="fas fa-tachometer-alt"></i> Attendance Dashboard
        </a>
        @endcan
        @can('mark-student-attendance')
        <a href="{{ route('admin.attendance.student.mark') }}" class="menu-item {{ request()->routeIs('admin.attendance.student.mark') ? 'active' : '' }}">
            <i class="fas fa-user-check"></i> Student Attendance
        </a>
        @endcan
        @can('view-student-attendance-all')
        <a href="{{ route('admin.attendance.student.calendar') }}" class="menu-item {{ request()->routeIs('admin.attendance.student.calendar') ? 'active' : '' }}">
            <i class="fas fa-calendar-alt"></i> Student Calendar
        </a>
        @endcan
        @can('mark-teacher-attendance')
        <a href="{{ route('admin.attendance.teacher.mark') }}" class="menu-item {{ request()->routeIs('admin.attendance.teacher.mark') ? 'active' : '' }}">
            <i class="fas fa-chalkboard-teacher"></i> Teacher Attendance
        </a>
        @endcan
        @can('view-teacher-attendance-all')
        <a href="{{ route('admin.attendance.teacher.calendar') }}" class="menu-item {{ request()->routeIs('admin.attendance.teacher.calendar') ? 'active' : '' }}">
            <i class="fas fa-calendar-week"></i> Teacher Calendar
        </a>
        @endcan
    </div>
</div>
@endcanany
{{-- ==================== END: ATTENDANCE MANAGEMENT ==================== --}}


{{-- ==================== SECTION 8: MATERIALS & CONTENT ==================== --}}
<div class="menu-dropdown">
    <a href="#section8" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Materials & Content
    </a>
    <div class="collapse" id="section8">
        <a href="{{ route('admin.materials.index') }}" class="menu-item {{ request()->routeIs('admin.materials.*') ? 'active' : '' }}">
            <i class="fas fa-file-alt"></i> Digital Materials
        </a>
        <a href="{{ route('admin.physical-materials.index') }}" class="menu-item {{ request()->routeIs('admin.physical-materials.*') ? 'active' : '' }}">
            <i class="fas fa-book"></i> Physical Materials
        </a>
        <a href="{{ route('announcements.index') }}" class="menu-item {{ request()->routeIs('announcements.index') ? 'active' : '' }}">
            <i class="fas fa-bullhorn"></i> Announcements
        </a>
    </div>
</div>
{{-- ==================== END: MATERIALS & CONTENT ==================== --}}


{{-- ==================== SECTION 9: FINANCIAL MANAGEMENT ==================== --}}
@canany(['view-financial-dashboard', 'view-revenue-reports', 'view-expense-reports'])
<div class="menu-dropdown">
    <a href="#section9" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Financial Management
    </a>
    <div class="collapse" id="section9">
        @can('view-financial-dashboard')
        <a href="{{ route('admin.financial.dashboard') }}" class="menu-item {{ request()->routeIs('admin.financial.dashboard') ? 'active' : '' }}">
            <i class="fas fa-chart-line"></i> Financial Dashboard
        </a>
        @endcan
        @can('view-revenue-reports')
        <a href="{{ route('admin.financial.reports') }}" class="menu-item {{ request()->routeIs('admin.financial.reports') ? 'active' : '' }}">
            <i class="fas fa-file-invoice-dollar"></i> Financial Reports
        </a>
        @endcan
        @can('view-profit-loss-reports')
        <a href="{{ route('admin.financial.reports.profit-loss') }}" class="menu-item {{ request()->routeIs('admin.financial.reports.profit-loss') ? 'active' : '' }}">
            <i class="fas fa-balance-scale"></i> Profit & Loss
        </a>
        @endcan
        @can('view-category-revenue')
        <a href="{{ route('admin.financial.reports.category-revenue') }}" class="menu-item {{ request()->routeIs('admin.financial.reports.category-revenue') ? 'active' : '' }}">
            <i class="fas fa-chart-pie"></i> Revenue by Category
        </a>
        @endcan
        @can('view-financial-dashboard')
        <a href="{{ route('admin.financial.reports.cash-flow') }}" class="menu-item {{ request()->routeIs('admin.financial.reports.cash-flow') ? 'active' : '' }}">
            <i class="fas fa-exchange-alt"></i> Cash Flow
        </a>
        @endcan
    </div>
</div>
@endcanany
{{-- ==================== END: FINANCIAL MANAGEMENT ==================== --}}


{{-- ==================== SECTION 10: BILLING & PAYMENTS ==================== --}}
<div class="menu-dropdown">
    <a href="#section10" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Billing & Payments
    </a>
    <div class="collapse" id="section10">
        {{-- Online/Offline stundets list --}}
        @if(Route::has('admin.billing.student-dashboard'))
        <a href="{{ route('admin.billing.student-dashboard') }}" class="menu-item {{ request()->routeIs('admin.billing.student-dashboard') ? 'active' : '' }}">
            <i class="fas fa-chart-bar"></i> Student Billing
        </a>
        @endif

        {{-- Invoices --}}
        <a href="{{ route('admin.invoices.index') }}" class="menu-item {{ request()->routeIs('admin.invoices.index') || request()->routeIs('admin.invoices.create') || request()->routeIs('admin.invoices.show') || request()->routeIs('admin.invoices.edit') ? 'active' : '' }}">
            <i class="fas fa-file-invoice-dollar"></i> Invoices
        </a>
        <a href="{{ route('admin.invoices.overdue') }}" class="menu-item {{ request()->routeIs('admin.invoices.overdue') ? 'active' : '' }}">
            <i class="fas fa-exclamation-triangle"></i> Overdue Invoices
        </a>

        {{-- Payments --}}
        @if(Route::has('admin.payments.index'))
        <a href="{{ route('admin.payments.index') }}" class="menu-item {{ request()->routeIs('admin.payments.index') ? 'active' : '' }}">
            <i class="fas fa-money-bill-wave"></i> All Payments
        </a>
        <a href="{{ route('admin.payments.create') }}" class="menu-item {{ request()->routeIs('admin.payments.create') ? 'active' : '' }}">
            <i class="fas fa-plus"></i> Record Payment
        </a>
        @if(Route::has('admin.payments.pending-verifications'))
        <a href="{{ route('admin.payments.pending-verifications') }}" class="menu-item {{ request()->routeIs('admin.payments.pending-verifications') ? 'active' : '' }}">
            <i class="fas fa-clock"></i> Pending Verifications
            @php
                $pendingPayments = \App\Models\Payment::where('status', 'pending_verification')->count();
            @endphp
            @if($pendingPayments > 0)
                <span class="badge bg-warning ms-auto">{{ $pendingPayments }}</span>
            @endif
        </a>
        @endif
        <a href="{{ route('admin.payments.history') }}" class="menu-item {{ request()->routeIs('admin.payments.history') ? 'active' : '' }}">
            <i class="fas fa-history"></i> Payment History
        </a>
        @if(Route::has('admin.payments.daily-report'))
        <a href="{{ route('admin.payments.daily-report') }}" class="menu-item {{ request()->routeIs('admin.payments.daily-report') ? 'active' : '' }}">
            <i class="fas fa-cash-register"></i> Daily Cash Report
        </a>
        @endif
        @endif

        {{-- Billing Cycles --}}
        <a href="{{ route('admin.billing.payment-cycles') }}" class="menu-item {{ request()->routeIs('admin.billing.payment-cycles') ? 'active' : '' }}">
            <i class="fas fa-sync-alt"></i> Payment Cycles
        </a>
        <a href="{{ route('admin.billing.subscription-alerts') }}" class="menu-item {{ request()->routeIs('admin.billing.subscription-alerts') ? 'active' : '' }}">
            <i class="fas fa-bell"></i> Subscription Alerts
        </a>

        {{-- Installments --}}
        <a href="{{ route('admin.installments.index') }}" class="menu-item {{ request()->routeIs('admin.installments.index') ? 'active' : '' }}">
            <i class="fas fa-calendar-check"></i> Installments
        </a>

        {{-- Arrears --}}
        <a href="{{ route('admin.arrears.index') }}" class="menu-item {{ request()->routeIs('admin.arrears.*') ? 'active' : '' }}">
            <i class="fas fa-exclamation-circle"></i> Arrears Management
        </a>

        {{-- Payment Reminders --}}
        <a href="{{ route('admin.reminders.index') }}" class="menu-item {{ request()->routeIs('admin.reminders.*') ? 'active' : '' }}">
            <i class="fas fa-paper-plane"></i> Payment Reminders
        </a>
    </div>
</div>
{{-- ==================== END: BILLING & PAYMENTS ==================== --}}


{{-- ==================== SECTION 11: EXPENSE MANAGEMENT ==================== --}}
<div class="menu-dropdown">
    <a href="#section11" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Expense Management
    </a>
    <div class="collapse" id="section11">
        @if(Route::has('admin.expenses.index'))
        <a href="{{ route('admin.expenses.index') }}" class="menu-item {{ request()->routeIs('admin.expenses.*') && !request()->routeIs('admin.expense-categories.*') ? 'active' : '' }}">
            <i class="fas fa-receipt"></i> Expense Vouchers
        </a>
        @endif
        @if(Route::has('admin.expense-categories.index'))
        <a href="{{ route('admin.expense-categories.index') }}" class="menu-item {{ request()->routeIs('admin.expense-categories.*') ? 'active' : '' }}">
            <i class="fas fa-tags"></i> Expense Categories
        </a>
        @endif
    </div>
</div>
{{-- ==================== END: EXPENSE MANAGEMENT ==================== --}}


{{-- ==================== SECTION 12: TEACHER MANAGEMENT ==================== --}}
<div class="menu-dropdown">
    <a href="#section12" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Teacher Management
    </a>
    <div class="collapse" id="section12">
        @if(Route::has('admin.teacher-payslips.index'))
        <a href="{{ route('admin.teacher-payslips.index') }}" class="menu-item {{ request()->routeIs('admin.teacher-payslips.*') ? 'active' : '' }}">
            <i class="fas fa-file-invoice-dollar"></i> Teacher Payslips
        </a>
        @endif
        @if(Route::has('admin.teacher-performance.index'))
        <a href="{{ route('admin.teacher-performance.index') }}" class="menu-item {{ request()->routeIs('admin.teacher-performance.*') ? 'active' : '' }}">
            <i class="fas fa-chart-line"></i> Teacher Performance
        </a>
        @endif
    </div>
</div>
{{-- ==================== END: TEACHER MANAGEMENT ==================== --}}


{{-- ==================== SECTION 13: INVENTORY MANAGEMENT ==================== --}}
<div class="menu-dropdown">
    <a href="#section13" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Inventory Management
    </a>
    <div class="collapse" id="section13">
        @if(Route::has('admin.inventory.index'))
        <a href="{{ route('admin.inventory.index') }}" class="menu-item {{ request()->routeIs('admin.inventory.index') || request()->routeIs('admin.inventory.show') ? 'active' : '' }}">
            <i class="fas fa-boxes"></i> All Items
        </a>
        @endif
        @if(Route::has('admin.inventory.create'))
        <a href="{{ route('admin.inventory.create') }}" class="menu-item {{ request()->routeIs('admin.inventory.create') ? 'active' : '' }}">
            <i class="fas fa-plus"></i> Add Item
        </a>
        @endif
        @if(Route::has('admin.inventory-categories.index'))
        <a href="{{ route('admin.inventory-categories.index') }}" class="menu-item {{ request()->routeIs('admin.inventory-categories.*') ? 'active' : '' }}">
            <i class="fas fa-tags"></i> Categories
        </a>
        @endif
        @if(Route::has('admin.inventory.low-stock'))
        <a href="{{ route('admin.inventory.low-stock') }}" class="menu-item {{ request()->routeIs('admin.inventory.low-stock') ? 'active' : '' }}">
            <i class="fas fa-exclamation-triangle"></i> Low Stock Alerts
        </a>
        @endif
        @if(Route::has('admin.inventory.reports'))
        <a href="{{ route('admin.inventory.reports') }}" class="menu-item {{ request()->routeIs('admin.inventory.reports*') ? 'active' : '' }}">
            <i class="fas fa-chart-bar"></i> Inventory Reports
        </a>
        @endif
    </div>
</div>
{{-- ==================== END: INVENTORY MANAGEMENT ==================== --}}


{{-- ==================== SECTION 14: POS & SALES ==================== --}}
<div class="menu-dropdown">
    <a href="#section14" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> POS & Sales
    </a>
    <div class="collapse" id="section14">
        @if(Route::has('admin.pos.index'))
        <a href="{{ route('admin.pos.index') }}" class="menu-item {{ request()->routeIs('admin.pos.index') ? 'active' : '' }}">
            <i class="fas fa-cash-register"></i> POS Terminal
        </a>
        @endif
        @if(Route::has('admin.pos.transactions'))
        <a href="{{ route('admin.pos.transactions') }}" class="menu-item {{ request()->routeIs('admin.pos.transactions*') ? 'active' : '' }}">
            <i class="fas fa-receipt"></i> POS Transactions
        </a>
        @endif
        @if(Route::has('admin.daily-cash-reports.index'))
        <a href="{{ route('admin.daily-cash-reports.index') }}" class="menu-item {{ request()->routeIs('admin.daily-cash-reports.index') || request()->routeIs('admin.daily-cash-reports.show') ? 'active' : '' }}">
            <i class="fas fa-file-invoice-dollar"></i> Daily Cash Reports
        </a>
        @endif
        @if(Route::has('admin.daily-cash-reports.summary'))
        <a href="{{ route('admin.daily-cash-reports.summary') }}" class="menu-item {{ request()->routeIs('admin.daily-cash-reports.summary') ? 'active' : '' }}">
            <i class="fas fa-chart-line"></i> Sales Summary
        </a>
        @endif
        @if(Route::has('admin.daily-cash-reports.open-drawer'))
        <a href="{{ route('admin.daily-cash-reports.open-drawer') }}" class="menu-item {{ request()->routeIs('admin.daily-cash-reports.open-drawer') ? 'active' : '' }}">
            <i class="fas fa-cash-register"></i> Open/Close Drawer
        </a>
        @endif
    </div>
</div>
{{-- ==================== END: POS & SALES ==================== --}}


{{-- ==================== SECTION 15: SEMINAR MANAGEMENT ==================== --}}
<div class="menu-dropdown">
    <a href="#section15" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Seminar Management
    </a>
    <div class="collapse" id="section15">
        <a href="{{ route('admin.seminars.index') }}" class="menu-item {{ request()->routeIs('admin.seminars.*') && !request()->routeIs('admin.seminars.accounting.*') ? 'active' : '' }}">
            <i class="fas fa-calendar-alt"></i> Seminars
        </a>
        @if(Route::has('public.seminars.index'))
        <a href="{{ route('public.seminars.index') }}" class="menu-item" target="_blank">
            <i class="fas fa-external-link-alt"></i> Public Seminar Page
        </a>
        @endif
        @if(Route::has('admin.seminars.accounting.dashboard'))
        <a href="{{ route('admin.seminars.accounting.dashboard') }}" class="menu-item {{ request()->routeIs('admin.seminars.accounting.dashboard') ? 'active' : '' }}">
            <i class="fas fa-chart-pie"></i> Seminar Accounting
        </a>
        <a href="{{ route('admin.seminars.accounting.reports.profitability') }}" class="menu-item {{ request()->routeIs('admin.seminars.accounting.reports.profitability*') ? 'active' : '' }}">
            <i class="fas fa-file-invoice-dollar"></i> Profitability Report
        </a>
        <a href="{{ route('admin.seminars.accounting.reports.payment-status') }}" class="menu-item {{ request()->routeIs('admin.seminars.accounting.reports.payment-status') ? 'active' : '' }}">
            <i class="fas fa-sync-alt"></i> Payment Status
        </a>
        @endif
    </div>
</div>
{{-- ==================== END: SEMINAR MANAGEMENT ==================== --}}


{{-- ==================== SECTION 16: REFERRAL & TRIAL ==================== --}}
<div class="menu-dropdown">
    <a href="#section16" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Referral & Trial
    </a>
    <div class="collapse" id="section16">
        @can('view-referrals')
        <a href="{{ route('admin.referrals.index') }}" class="menu-item {{ request()->routeIs('admin.referrals.*') && !request()->routeIs('admin.referrals.vouchers') ? 'active' : '' }}">
            <i class="fas fa-user-friends"></i> Referrals
        </a>
        @endcan
        @can('view-referral-vouchers')
        <a href="{{ route('admin.referrals.vouchers') }}" class="menu-item {{ request()->routeIs('admin.referrals.vouchers') ? 'active' : '' }}">
            <i class="fas fa-ticket-alt"></i> Vouchers
        </a>
        @endcan
        @can('view-trial-classes')
        <a href="{{ route('admin.trial-classes.index') }}" class="menu-item {{ request()->routeIs('admin.trial-classes.*') ? 'active' : '' }}">
            <i class="fas fa-chalkboard"></i> Trial Classes
        </a>
        @endcan
        @can('view-reviews')
        <a href="{{ route('admin.reviews.index') }}" class="menu-item {{ request()->routeIs('admin.reviews.*') ? 'active' : '' }}">
            <i class="fas fa-star"></i> Student Reviews
        </a>
        @endcan
    </div>
</div>
{{-- ==================== END: REFERRAL & TRIAL ==================== --}}


{{-- ==================== SECTION 17: REPORTS & ANALYTICS ==================== --}}
<div class="menu-dropdown">
    <a href="#section17" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Reports & Analytics
    </a>
    <div class="collapse" id="section17">
        <a href="{{ route('admin.attendance.reports.index') }}" class="menu-item {{ request()->routeIs('admin.attendance.reports.index') ? 'active' : '' }}">
            <i class="fas fa-tachometer-alt"></i> Reports Dashboard
        </a>
        <a href="{{ route('admin.attendance.reports.student') }}" class="menu-item {{ request()->routeIs('admin.attendance.reports.student') ? 'active' : '' }}">
            <i class="fas fa-user"></i> Student Report
        </a>
        <a href="{{ route('admin.attendance.reports.class') }}" class="menu-item {{ request()->routeIs('admin.attendance.reports.class') ? 'active' : '' }}">
            <i class="fas fa-school"></i> Class Report
        </a>
        <a href="{{ route('admin.attendance.reports.low-attendance') }}" class="menu-item {{ request()->routeIs('admin.attendance.reports.low-attendance') ? 'active' : '' }}">
            <i class="fas fa-exclamation-triangle"></i> Low Attendance
        </a>
        <a href="{{ route('admin.attendance.reports.history') }}" class="menu-item {{ request()->routeIs('admin.attendance.reports.history') ? 'active' : '' }}">
            <i class="fas fa-history"></i> History
        </a>
    </div>
</div>
{{-- ==================== END: REPORTS & ANALYTICS ==================== --}}


{{-- ==================== SECTION 18: COMMUNICATIONS ==================== --}}
<div class="menu-dropdown">
    <a href="#section18" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Communications
    </a>
    <div class="collapse" id="section18">
        <a href="{{ route('admin.notifications.index') }}" class="menu-item {{ request()->routeIs('admin.notifications.index') ? 'active' : '' }}">
            <i class="fas fa-bell"></i> Notification Dashboard
        </a>
        <a href="{{ route('admin.notifications.create') }}" class="menu-item {{ request()->routeIs('admin.notifications.create') ? 'active' : '' }}">
            <i class="fas fa-paper-plane"></i> Send Notification
        </a>
        <a href="{{ route('admin.notifications.logs') }}" class="menu-item {{ request()->routeIs('admin.notifications.logs') ? 'active' : '' }}">
            <i class="fas fa-history"></i> Notification Logs
        </a>
        <a href="{{ route('admin.templates.index') }}" class="menu-item {{ request()->routeIs('admin.templates.*') ? 'active' : '' }}">
            <i class="fas fa-file-alt"></i> Message Templates
        </a>
        <a href="{{ route('admin.notifications.whatsapp-queue') }}" class="menu-item {{ request()->routeIs('admin.notifications.whatsapp-queue') ? 'active' : '' }}">
            <i class="fab fa-whatsapp"></i> WhatsApp Queue
        </a>
        <a href="{{ route('admin.notifications.email-queue') }}" class="menu-item {{ request()->routeIs('admin.notifications.email-queue') ? 'active' : '' }}">
            <i class="fas fa-envelope"></i> Email Queue
        </a>
        <a href="{{ route('admin.notifications.settings') }}" class="menu-item {{ request()->routeIs('admin.notifications.settings') ? 'active' : '' }}">
            <i class="fas fa-cog"></i> Notification Settings
        </a>
    </div>
</div>
{{-- ==================== END: COMMUNICATIONS ==================== --}}

{{-- ==================== SECTION: WEBSITE MANAGEMENT ==================== --}}
<div class="menu-dropdown">
    <a href="#sectionWebsite" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Website Management
    </a>
    <div class="collapse" id="sectionWebsite">
        @if(Route::has('admin.carousel.index'))
        <a href="{{ route('admin.carousel.index') }}" class="menu-item {{ request()->routeIs('admin.carousel.*') ? 'active' : '' }}">
            <i class="fas fa-images"></i> Carousel Images
        </a>
        @endif
    </div>
</div>
{{-- ==================== END: WEBSITE MANAGEMENT ==================== --}}

{{-- ==================== SECTION 19: SETTINGS & ACCOUNT ==================== --}}
<div class="menu-dropdown">
    <a href="#section19" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Settings & Account
    </a>
    <div class="collapse" id="section19">
        <a href="{{ route('profile.index') }}" class="menu-item {{ request()->routeIs('profile.*') ? 'active' : '' }}">
            <i class="fas fa-user"></i> My Profile
        </a>
        @can('manage-payment-gateway')
        <a href="{{ route('admin.payment-gateways.index') }}" class="menu-item {{ request()->routeIs('admin.payment-gateways.*') ? 'active' : '' }}">
            <i class="fas fa-credit-card"></i> Payment Gateways
        </a>
        @endcan
    </div>
</div>
{{-- ==================== END: SETTINGS & ACCOUNT ==================== --}}
@endrole

{{-- Staff Sidebar --}}
@role('staff')
<!-- Main -->
<div class="menu-dropdown">
    <a href="#section23" class="menu-section-title sidebar-bg-color" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Main
    </a>
    <div class="collapse" id="section23">
    <a href="{{ route('staff.dashboard') }}" class="menu-item {{ request()->routeIs('staff.dashboard') ? 'active' : '' }}">
    <i class="fas fa-home"></i> Dashboard
    </a>

    </div>
</div>
<!-- Registration -->
<div class="menu-dropdown">
    <a href="#section24" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Registration
    </a>
    <div class="collapse" id="section24">
    <a href="{{ route('staff.registration.create-student') }}" class="menu-item {{ request()->routeIs('staff.registration.create-student') ? 'active' : '' }}">
    <i class="fas fa-user-graduate"></i> Register Student
    </a>
    <a href="{{ route('staff.registration.create-parent') }}" class="menu-item {{ request()->routeIs('staff.registration.create-parent') ? 'active' : '' }}">
    <i class="fas fa-user-friends"></i> Register Parent
    </a>
    <a href="{{ route('staff.registration.pending') }}" class="menu-item {{ request()->routeIs('staff.registration.pending') ? 'active' : '' }}">
    <i class="fas fa-clock"></i> Pending Approvals
    @php
        $pendingCount = \App\Models\Student::where('approval_status', 'pending')->count();
    @endphp
    @if($pendingCount > 0)
        <span class="badge bg-warning ms-auto">{{ $pendingCount }}</span>
    @endif
    </a>
    {{-- start: Meterials --}}
    </div>
</div>
<!-- Materials -->
@if(Route::has('staff.physical-materials.index'))
<div class="menu-dropdown">
    <a href="#section25" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Materials
    </a>
    <div class="collapse" id="section25">
    <a href="{{ route('staff.physical-materials.index') }}" class="menu-item {{ request()->routeIs('staff.physical-materials.*') ? 'active' : '' }}">
        <i class="fas fa-book"></i> Physical Materials
    </a>
    </div>
</div>
@endif

<!-- Students -->
<div class="menu-dropdown">
    <a href="#section26" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Students
    </a>
    <div class="collapse" id="section26">
    @if(Route::has('staff.students.index'))
    <a href="{{ route('staff.students.index') }}" class="menu-item {{ request()->routeIs('staff.students.index') ? 'active' : '' }}">
    <i class="fas fa-users"></i> All Students
    </a>
    @else
    <a href="#" class="menu-item">
    <i class="fas fa-users"></i> All Students
    </a>
    @endif

    @if(Route::has('staff.trial-classes.index'))
    <a href="{{ route('staff.trial-classes.index') }}" class="menu-item {{ request()->routeIs('staff.trial-classes.*') ? 'active' : '' }}">
    <i class="fas fa-user-graduate"></i> Trial Classes
    @php
        $todayTrialsCount = \App\Models\TrialClass::whereDate('scheduled_date', today())
            ->whereIn('status', ['pending', 'approved'])
            ->count();
    @endphp
    @if($todayTrialsCount > 0)
        <span class="badge bg-danger ms-auto">{{ $todayTrialsCount }}</span>
    @endif
    </a>
    @else
    <a href="#" class="menu-item">
    <i class="fas fa-user-graduate"></i> Trial Classes
    </a>
    @endif

    </div>
</div>

{{-- Start: Enrollment Management --}}
@if(Route::has('staff.enrollments.index'))
<div class="menu-dropdown">
    <a href="#staff_enrollment_collapse" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Enrollments
    </a>
    <div class="collapse" id="staff_enrollment_collapse">
        <a class="menu-item {{ request()->routeIs('staff.enrollments.index') ? 'active' : '' }}" href="{{ route('staff.enrollments.index') }}">
            <i class="fas fa-fw fa-user-graduate"></i> All Enrollments
        </a>
    </div>
</div>
@endif
{{-- End: Enrollment Management --}}


{{-- Start: Attendance Management (Staff) --}}
<!-- Attendance Management -->
<div class="menu-dropdown">
    <a href="#staff_attendance_section" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Attendance Management
    </a>
    <div class="collapse" id="staff_attendance_section">
        @if(Route::has('staff.attendance.index'))
        <a href="{{ route('staff.attendance.index') }}" class="menu-item {{ request()->routeIs('staff.attendance.index') ? 'active' : '' }}">
            <i class="fas fa-tachometer-alt"></i> Attendance Dashboard
        </a>
        @endif

        @if(Route::has('staff.attendance.student.mark'))
        <a href="{{ route('staff.attendance.student.mark') }}" class="menu-item {{ request()->routeIs('staff.attendance.student.mark') ? 'active' : '' }}">
            <i class="fas fa-user-check"></i> Mark Student Attendance
        </a>
        @endif

        @if(Route::has('staff.attendance.student.calendar'))
        <a href="{{ route('staff.attendance.student.calendar') }}" class="menu-item {{ request()->routeIs('staff.attendance.student.calendar') ? 'active' : '' }}">
            <i class="fas fa-calendar-alt"></i> Student Calendar
        </a>
        @endif

        @if(Route::has('staff.attendance.teacher.mark'))
        <a href="{{ route('staff.attendance.teacher.mark') }}" class="menu-item {{ request()->routeIs('staff.attendance.teacher.mark') ? 'active' : '' }}">
            <i class="fas fa-chalkboard-teacher"></i> Mark Teacher Attendance
        </a>
        @endif
        @if(Route::has('staff.attendance.teacher.calendar'))
        <a href="{{ route('staff.attendance.teacher.calendar') }}" class="menu-item {{ request()->routeIs('staff.attendance.teacher.calendar') ? 'active' : '' }}">
            <i class="fas fa-calendar-check"></i> Teacher Calendar
        </a>
        @endif
        @if(Route::has('staff.attendance.reports'))
        <a href="{{ route('staff.attendance.reports') }}" class="menu-item {{ request()->routeIs('staff.attendance.reports') ? 'active' : '' }}">
            <i class="fas fa-chart-bar"></i> Attendance Reports
        </a>
        @endif
    </div>
</div>
{{-- End: Attendance Management (Staff) --}}
<!-- Payments -->
<div class="menu-dropdown">
    <a href="#sectionPayments" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Payments
    </a>
    <div class="collapse" id="sectionPayments">
        @if(Route::has('staff.payments.index'))
        <a href="{{ route('staff.payments.index') }}"
           class="menu-item {{ request()->routeIs('staff.payments.index') ? 'active' : '' }}">
            <i class="fas fa-list"></i> Today's Payments
        </a>
        @endif

        @if(Route::has('staff.payments.create'))
        <a href="{{ route('staff.payments.create') }}"
           class="menu-item {{ request()->routeIs('staff.payments.create') ? 'active' : '' }}">
            <i class="fas fa-plus"></i> Record Payment
        </a>
        @endif

        @if(Route::has('staff.payments.quick-payment'))
        <a href="{{ route('staff.payments.quick-payment') }}"
           class="menu-item {{ request()->routeIs('staff.payments.quick-payment') ? 'active' : '' }}">
            <i class="fas fa-bolt"></i> Quick Payment
        </a>
        @endif

        @if(Route::has('staff.payments.history'))
        <a href="{{ route('staff.payments.history') }}"
           class="menu-item {{ request()->routeIs('staff.payments.history') ? 'active' : '' }}">
            <i class="fas fa-history"></i> Payment History
        </a>
        @endif
    </div>
</div>

{{-- Start: Seminar Management (Staff - View Only) --}}
<div class="menu-dropdown">
    <a href="#staff_seminar_section" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Seminars
    </a>
    <div class="collapse" id="staff_seminar_section">
        @if(Route::has('staff.seminars.index'))
        <a href="{{ route('staff.seminars.index') }}" class="menu-item {{ request()->routeIs('staff.seminars.index') ? 'active' : '' }}">
            <i class="fas fa-calendar-alt"></i> All Seminars
            @php
                $openSeminarsCount = \App\Models\Seminar::where('status', 'open')->count();
            @endphp
            @if($openSeminarsCount > 0)
                <span class="badge bg-success ms-auto">{{ $openSeminarsCount }}</span>
            @endif
        </a>
        @endif
    </div>
</div>
{{-- End: Seminar Management (Staff - View Only) --}}


{{-- Start: Arrears Management (Staff - View Only) --}}
<div class="menu-dropdown">
    <a href="#staff_arrears_section" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Arrears
        @php
            $overdueCount = \App\Models\Invoice::where('status', 'overdue')->count();
        @endphp
        @if($overdueCount > 0)
            <span class="badge bg-danger ms-auto">{{ $overdueCount }}</span>
        @endif
    </a>
    <div class="collapse" id="staff_arrears_section">

        @if(Route::has('staff.arrears.index'))
        <a href="{{ route('staff.arrears.index') }}"
           class="menu-item {{ request()->routeIs('staff.arrears.index') ? 'active' : '' }}">
            <i class="fas fa-tachometer-alt"></i> Arrears Dashboard
        </a>
        @endif

        @if(Route::has('staff.arrears.students-list'))
        <a href="{{ route('staff.arrears.students-list') }}"
           class="menu-item {{ request()->routeIs('staff.arrears.students-list') ? 'active' : '' }}">
            <i class="fas fa-users"></i> Students with Arrears
            @php
                $studentsWithArrearsCount = \App\Models\Invoice::unpaid()
                    ->distinct('student_id')
                    ->count('student_id');
            @endphp
            @if($studentsWithArrearsCount > 0)
                <span class="badge bg-warning text-dark ms-auto">{{ $studentsWithArrearsCount }}</span>
            @endif
        </a>
        @endif

        @if(Route::has('staff.arrears.due-report'))
        <a href="{{ route('staff.arrears.due-report') }}"
           class="menu-item {{ request()->routeIs('staff.arrears.due-report') ? 'active' : '' }}">
            <i class="fas fa-calendar-times"></i> Due Report
        </a>
        @endif
    </div>
</div>
{{-- End: Arrears Management (Staff - View Only) --}}

<!-- Timetable (Keep separate or move to Other section) -->
<div class="menu-dropdown">
    <a href="#sectionTimetable" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Timetable
    </a>
    <div class="collapse" id="sectionTimetable">
        @if(Route::has('timetable.index'))
        <a href="{{ route('timetable.index') }}"
           class="menu-item {{ request()->routeIs('timetable.index') ? 'active' : '' }}">
            <i class="fas fa-calendar-week"></i> View Timetable
        </a>
        @endif
    </div>
</div>

<!-- Announcements -->
<div class="menu-dropdown">
    <a href="#staff_announcements_section" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Announcements
    </a>
    <div class="collapse" id="staff_announcements_section">
        @if(Route::has('staff.announcements.index'))
        <a href="{{ route('staff.announcements.index') }}"
           class="menu-item {{ request()->routeIs('staff.announcements.*') ? 'active' : '' }}">
            <i class="fas fa-bullhorn"></i> Announcements
        </a>
        @else
        <a href="#" class="menu-item disabled">
            <i class="fas fa-bullhorn"></i> Announcements
        </a>
        @endif
    </div>
</div>

<!-- POS -->
<div class="menu-dropdown">
    <a href="#sectionStaffPOS" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> POS
    </a>
    <div class="collapse" id="sectionStaffPOS">
        {{-- POS Terminal --}}
        @if(Route::has('staff.pos.index'))
        <a href="{{ route('staff.pos.index') }}" class="menu-item {{ request()->routeIs('staff.pos.index') ? 'active' : '' }}">
            <i class="fas fa-cash-register me-2"></i> POS Terminal
        </a>
        @endif
        {{-- POS Terminal --}}

        {{-- My Transactions --}}
        @if(Route::has('staff.pos.my-transactions'))
        <a href="{{ route('staff.pos.my-transactions') }}" class="menu-item {{ request()->routeIs('staff.pos.my-transactions*') ? 'active' : '' }}">
            <i class="fas fa-receipt me-2"></i> My Transactions
        </a>
        @endif
        {{-- My Transactions --}}

        {{-- Open Drawer --}}
        @if(Route::has('staff.pos.open-drawer'))
        <a href="{{ route('staff.pos.open-drawer') }}" class="menu-item {{ request()->routeIs('staff.pos.open-drawer') ? 'active' : '' }}">
            <i class="fas fa-cash-register me-2"></i> Open Drawer
        </a>
        @endif
        {{-- Open Drawer --}}
    </div>
</div>
<!-- Other -->
<div class="menu-dropdown">
    <a href="#section29" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Other
    </a>
    <div class="collapse" id="section29">
        <a href="#" class="menu-item">
            <i class="fas fa-shopping-cart"></i> Cafeteria POS
        </a>
        {{-- Inventory --}}
        @if(Route::has('staff.inventory.index'))
        <a href="{{ route('staff.inventory.index') }}" class="menu-item {{ request()->routeIs('staff.inventory.*') ? 'active' : '' }}">
            <i class="fas fa-boxes me-2"></i> Inventory
        </a>
        @endif
        {{-- Inventory --}}
    </div>
</div>
<!-- Account -->
<div class="menu-dropdown">
    <a href="#section30" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Account
    </a>
    <div class="collapse" id="section30">
    <a href="{{ route('profile.index') }}" class="menu-item {{ request()->routeIs('profile.*') ? 'active' : '' }}">
    <i class="fas fa-user"></i> My Profile
    </a>
    </div>
</div>
@endrole

{{-- Teacher Sidebar --}}
@role('teacher')
<!-- Main -->
<div class="menu-dropdown">
    <a href="#section31" class="menu-section-title sidebar-bg-color" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Main
    </a>
    <div class="collapse" id="section31">
    <a href="{{ route('teacher.dashboard') }}" class="menu-item {{ request()->routeIs('teacher.dashboard') ? 'active' : '' }}">
    <i class="fas fa-home"></i> Dashboard
    </a>
    </div>
</div>

<!-- Teaching -->
<div class="menu-dropdown">
    <a href="#section32" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Teaching
    </a>
    <div class="collapse" id="section32">
    @if(Route::has('teacher.attendance.index'))
    <a href="{{ route('teacher.attendance.index') }}" class="menu-item {{ request()->routeIs('teacher.attendance.*') ? 'active' : '' }}">
    <i class="fas fa-check-square"></i> Attendance
    </a>
    @endif
    @if(Route::has('timetable.index'))
    <a href="{{ route('timetable.index') }}" class="menu-item {{ request()->routeIs('timetable.*') ? 'active' : '' }}">
    <i class="fas fa-calendar-week"></i> Timetable
    </a>
    @endif
    </div>
</div>

<!-- Salary -->
<div class="menu-dropdown">
    <a href="#section33" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Salary
    </a>
    <div class="collapse" id="section33">
    @if(Route::has('teacher.payslips.index'))
    <a href="{{ route('teacher.payslips.index') }}" class="menu-item {{ request()->routeIs('teacher.payslips.*') ? 'active' : '' }}">
    <i class="fas fa-file-invoice-dollar"></i> My Payslips
    </a>
    @endif
    </div>
</div>

<!-- Performance -->
<div class="menu-dropdown">
    <a href="#section34" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Performance
    </a>
    <div class="collapse" id="section34">
    @if(Route::has('teacher.performance.index'))
    <a href="{{ route('teacher.performance.index') }}" class="menu-item {{ request()->routeIs('teacher.performance.index') ? 'active' : '' }}">
    <i class="fas fa-tachometer-alt"></i> My Performance
    </a>
    @endif
    @if(Route::has('teacher.performance.analytics'))
    <a href="{{ route('teacher.performance.analytics') }}" class="menu-item {{ request()->routeIs('teacher.performance.analytics') ? 'active' : '' }}">
    <i class="fas fa-chart-bar"></i> Analytics
    </a>
    @endif
    </div>
</div>

<!-- Materials -->
<div class="menu-dropdown">
    <a href="#section34a" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Materials
    </a>
    <div class="collapse" id="section34a">
    @if(Route::has('teacher.materials.index'))
    <a href="{{ route('teacher.materials.index') }}" class="menu-item {{ request()->routeIs('teacher.materials.index') || request()->routeIs('teacher.materials.show') || request()->routeIs('teacher.materials.edit') ? 'active' : '' }}">
    <i class="fas fa-book-open"></i> My Materials
    </a>
    @endif
    @if(Route::has('teacher.materials.create'))
    <a href="{{ route('teacher.materials.create') }}" class="menu-item {{ request()->routeIs('teacher.materials.create') ? 'active' : '' }}">
    <i class="fas fa-file-upload"></i> Upload Materials
    </a>
    @endif
    </div>
</div>

<!-- My Classes -->
<div class="menu-dropdown">
    <a href="#section35" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> My Classes
    </a>
    <div class="collapse" id="section35">
    @if(Route::has('teacher.classes.index'))
    <a href="{{ route('teacher.classes.index') }}" class="menu-item {{ request()->routeIs('teacher.classes.*') ? 'active' : '' }}">
    <i class="fas fa-school"></i> My Classes
    </a>
    @endif
    @if(Route::has('teacher.schedule.index'))
    <a href="{{ route('teacher.schedule.index') }}" class="menu-item {{ request()->routeIs('teacher.schedule.*') ? 'active' : '' }}">
    <i class="fas fa-calendar-alt"></i> My Schedule
    </a>
    @endif
    @if(Route::has('teacher.students.index'))
    <a href="{{ route('teacher.students.index') }}" class="menu-item {{ request()->routeIs('teacher.students.*') ? 'active' : '' }}">
    <i class="fas fa-users"></i> My Students
    </a>
    @endif
    </div>
</div>

<!-- Assessment -->
<div class="menu-dropdown">
    <a href="#section36" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Assessment
    </a>
    <div class="collapse" id="section36">
    @if(Route::has('teacher.exams.index'))
    <a href="{{ route('teacher.exams.index') }}" class="menu-item {{ request()->routeIs('teacher.exams.*') ? 'active' : '' }}">
    <i class="fas fa-file-signature"></i> Exams
    </a>
    @endif
    @if(Route::has('teacher.results.index'))
    <a href="{{ route('teacher.results.index') }}" class="menu-item {{ request()->routeIs('teacher.results.*') ? 'active' : '' }}">
    <i class="fas fa-chart-bar"></i> Results
    </a>
    @endif
    </div>
</div>

<!-- Documents -->
<div class="menu-dropdown">
    <a href="#section37" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Documents
    </a>
    <div class="collapse" id="section37">
    @if(Route::has('teacher.documents.index'))
    <a href="{{ route('teacher.documents.index') }}" class="menu-item {{ request()->routeIs('teacher.documents.*') ? 'active' : '' }}">
    <i class="fas fa-folder-open"></i> My Documents
    </a>
    @endif
    </div>
</div>

<!-- Announcements -->
<div class="menu-dropdown">
    <a href="#section38" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Announcements
    </a>
    <div class="collapse" id="section38">
    @if(Route::has('teacher.announcements.index'))
    <a href="{{ route('teacher.announcements.index') }}" class="menu-item {{ request()->routeIs('teacher.announcements.*') ? 'active' : '' }}">
    <i class="fas fa-bullhorn"></i> View Announcements
    </a>
    @endif
    @if(Route::has('teacher.announcements.create'))
    <a href="{{ route('teacher.announcements.create') }}" class="menu-item {{ request()->routeIs('teacher.announcements.create') ? 'active' : '' }}">
    <i class="fas fa-plus"></i> Create Announcement
    </a>
    @endif
    </div>
</div>

<!-- Account -->
<div class="menu-dropdown">
    <a href="#section39" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Account
    </a>
    <div class="collapse" id="section39">
    @if(Route::has('teacher.profile.index'))
    <a href="{{ route('teacher.profile.index') }}" class="menu-item {{ request()->routeIs('teacher.profile.*') ? 'active' : '' }}">
    <i class="fas fa-user"></i> My Profile
    </a>
    @else
    <a href="{{ route('profile.index') }}" class="menu-item {{ request()->routeIs('profile.*') ? 'active' : '' }}">
    <i class="fas fa-user"></i> My Profile
    </a>
    @endif
    </div>
</div>
@endrole

{{-- Parents Sidebar --}}
@role('parent')
<!-- Main -->
<div class="menu-dropdown">
    <a href="#section42" class="menu-section-title sidebar-bg-color" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Main
    </a>
    <div class="collapse" id="section42">
        <a href="{{ route('parent.dashboard') }}" class="menu-item {{ request()->routeIs('parent.dashboard') ? 'active' : '' }}">
            <i class="fas fa-home"></i> Dashboard
        </a>
    </div>
</div>

<!-- Children -->
<div class="menu-dropdown">
    <a href="#section43" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Children
    </a>
    <div class="collapse" id="section43">
        @if(Route::has('parent.children.index'))
        <a href="{{ route('parent.children.index') }}" class="menu-item {{ request()->routeIs('parent.children.index') || request()->routeIs('parent.children.show') ? 'active' : '' }}">
            <i class="fas fa-users"></i> My Children
        </a>
        @endif
        @if(Route::has('parent.children.register'))
        <a href="{{ route('parent.children.register') }}" class="menu-item {{ request()->routeIs('parent.children.register') ? 'active' : '' }}">
            <i class="fas fa-user-plus"></i> Register Child
        </a>
        @endif
    </div>
</div>

<!-- Academic -->
<div class="menu-dropdown">
    <a href="#section44" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Academic
    </a>
    <div class="collapse" id="section44">
        @if(Route::has('timetable.index'))
        <a href="{{ route('timetable.index') }}" class="menu-item {{ request()->routeIs('timetable.index') ? 'active' : '' }}">
            <i class="fas fa-calendar-week"></i> Timetable
        </a>
        @endif
        @if(Route::has('parent.materials.index'))
        <a href="{{ route('parent.materials.index') }}" class="menu-item {{ request()->routeIs('parent.materials.*') ? 'active' : '' }}">
            <i class="fas fa-book-open"></i> Study Materials
        </a>
        @endif
    </div>
</div>

{{-- Enrollment Management --}}
@if(Route::has('parent.enrollments.index'))
<div class="menu-dropdown">
    <a href="#parent_enrollment_collapse" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Enrollments
    </a>
    <div class="collapse" id="parent_enrollment_collapse">
        <a class="menu-item {{ request()->routeIs('parent.enrollments.index') ? 'active' : '' }}" href="{{ route('parent.enrollments.index') }}">
            <i class="fas fa-fw fa-user-graduate"></i> All Enrollments
        </a>
    </div>
</div>
@endif

{{-- ============================================================ --}}
{{-- BILLING & PAYMENTS - UNIFIED SECTION (Replaces Financial + Payments) --}}
{{-- ============================================================ --}}
<div class="menu-dropdown">
    <a href="#section_billing" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Billing & Payments
    </a>
    <div class="collapse" id="section_billing">
        {{-- Invoices --}}
        @if(Route::has('parent.invoices.index'))
        <a href="{{ route('parent.invoices.index') }}" class="menu-item {{ request()->routeIs('parent.invoices.index') || request()->routeIs('parent.invoices.show') ? 'active' : '' }}">
            <i class="fas fa-file-invoice"></i> Invoices
        </a>
        @endif

        {{-- Make Payment (Online) --}}
        @if(Route::has('parent.payments.pay-online'))
        <a href="{{ route('parent.payments.pay-online') }}" class="menu-item {{ request()->routeIs('parent.payments.pay-online') ? 'active' : '' }}">
            <i class="fas fa-credit-card"></i> Make Payment
        </a>
        @endif

        {{-- All Payments --}}
        @if(Route::has('parent.payments.index'))
        <a href="{{ route('parent.payments.index') }}" class="menu-item {{ request()->routeIs('parent.payments.index') || request()->routeIs('parent.payments.show') ? 'active' : '' }}">
            <i class="fas fa-money-bill-wave"></i> Payment Records
        </a>
        @endif

        {{-- Payment History --}}
        @if(Route::has('parent.payments.history'))
        <a href="{{ route('parent.payments.history') }}" class="menu-item {{ request()->routeIs('parent.payments.history') ? 'active' : '' }}">
            <i class="fas fa-history"></i> Payment History
        </a>
        @endif

        {{-- Outstanding --}}
        @if(Route::has('parent.payments.outstanding'))
        <a href="{{ route('parent.payments.outstanding') }}" class="menu-item {{ request()->routeIs('parent.payments.outstanding') ? 'active' : '' }}">
            <i class="fas fa-exclamation-circle text-warning"></i> Outstanding
        </a>
        @endif
    </div>
</div>

<!-- Attendance -->
<div class="menu-dropdown">
    <a href="#section47" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Attendance
    </a>
    <div class="collapse" id="section47">
        @if(Route::has('parent.attendance.index'))
        <a href="{{ route('parent.attendance.index') }}" class="menu-item {{ request()->routeIs('parent.attendance.*') ? 'active' : '' }}">
            <i class="fas fa-check-square"></i> View Attendance
        </a>
        @endif
    </div>
</div>

<!-- Other -->
<div class="menu-dropdown">
    <a href="#section48" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Other
    </a>
    <div class="collapse" id="section48">
        @if(Route::has('parent.announcements.index'))
            <a href="{{ route('parent.announcements.index') }}" class="menu-item {{ request()->routeIs('parent.announcements.*') ? 'active' : '' }}">
                <i class="fas fa-bullhorn"></i> Announcements
            </a>
        @endif
    </div>
</div>

<!-- Account -->
<div class="menu-dropdown">
    <a href="#section49" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Account
    </a>
    <div class="collapse" id="section49">
        <a href="{{ route('profile.index') }}" class="menu-item {{ request()->routeIs('profile.*') ? 'active' : '' }}">
            <i class="fas fa-user"></i> My Profile
        </a>
    </div>
</div>
@endrole

{{-- ============================================================ --}}
{{-- Student Sidebar --}}
{{-- Section IDs: section60 - section68 (avoids parent conflict) --}}
{{-- ============================================================ --}}
@role('student')

{{-- ==================== MAIN ==================== --}}
<div class="menu-dropdown">
    <a href="#section60" class="menu-section-title sidebar-bg-color" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Main
    </a>
    <div class="collapse" id="section60">
        <a href="{{ route('student.dashboard') }}" class="menu-item {{ request()->routeIs('student.dashboard') ? 'active' : '' }}">
            <i class="fas fa-home"></i> Dashboard
        </a>
    </div>
</div>

{{-- ==================== INVOICES ==================== --}}
<div class="menu-dropdown">
    <a href="#section61" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Invoices
    </a>
    <div class="collapse" id="section61">
        @if(Route::has('student.invoices.index'))
        <a href="{{ route('student.invoices.index') }}" class="menu-item {{ request()->routeIs('student.invoices.index') || request()->routeIs('student.invoices.show') ? 'active' : '' }}">
            <i class="fas fa-file-invoice"></i> My Invoices
        </a>
        @endif

        @if(Route::has('student.invoices.history'))
        <a href="{{ route('student.invoices.history') }}" class="menu-item {{ request()->routeIs('student.invoices.history') ? 'active' : '' }}">
            <i class="fas fa-history"></i> Invoice History
        </a>
        @endif
    </div>
</div>

{{-- ==================== PAYMENTS ==================== --}}
<div class="menu-dropdown">
    <a href="#section62" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Payments
    </a>
    <div class="collapse" id="section62">
        @if(Route::has('student.payments.index'))
        <a href="{{ route('student.payments.index') }}" class="menu-item {{ request()->routeIs('student.payments.index') || request()->routeIs('student.payments.show') ? 'active' : '' }}">
            <i class="fas fa-money-bill-wave"></i> My Payments
        </a>
        @endif

        @if(Route::has('student.payments.history'))
        <a href="{{ route('student.payments.history') }}" class="menu-item {{ request()->routeIs('student.payments.history') ? 'active' : '' }}">
            <i class="fas fa-history"></i> Payment History
        </a>
        @endif

        @if(Route::has('student.payments.outstanding'))
        <a href="{{ route('student.payments.outstanding') }}" class="menu-item {{ request()->routeIs('student.payments.outstanding') ? 'active' : '' }}">
            <i class="fas fa-exclamation-circle text-warning"></i> Outstanding
        </a>
        @endif

        @if(Route::has('student.payments.pay-online'))
        <a href="{{ route('student.payments.pay-online') }}" class="menu-item {{ request()->routeIs('student.payments.pay-online') ? 'active' : '' }}">
            <i class="fas fa-credit-card"></i> Pay Online
        </a>
        @endif
    </div>
</div>

{{-- ==================== ACADEMIC ==================== --}}
<div class="menu-dropdown">
    <a href="#section63" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Academic
    </a>
    <div class="collapse" id="section63">
        @if(Route::has('student.classes.index'))
        <a href="{{ route('student.classes.index') }}" class="menu-item {{ request()->routeIs('student.classes.*') ? 'active' : '' }}">
            <i class="fas fa-school"></i> My Classes
        </a>
        @endif

        @if(Route::has('timetable.index'))
        <a href="{{ route('timetable.index') }}" class="menu-item {{ request()->routeIs('timetable.index') ? 'active' : '' }}">
            <i class="fas fa-calendar-week"></i> Timetable
        </a>
        @endif

        @if(Route::has('student.attendance.index'))
        <a href="{{ route('student.attendance.index') }}" class="menu-item {{ request()->routeIs('student.attendance.*') ? 'active' : '' }}">
            <i class="fas fa-check-square"></i> Attendance
        </a>
        @endif

        @if(Route::has('student.results.index'))
        <a href="{{ route('student.results.index') }}" class="menu-item {{ request()->routeIs('student.results.*') ? 'active' : '' }}">
            <i class="fas fa-clipboard-list"></i> Exam Results
        </a>
        @endif
    </div>
</div>

{{-- ==================== ENROLLMENTS ==================== --}}
@if(Route::has('student.enrollments.my-enrollments'))
<div class="menu-dropdown">
    <a href="#student_enrollment_collapse" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Enrollments
    </a>
    <div class="collapse" id="student_enrollment_collapse">
        <a class="menu-item {{ request()->routeIs('student.enrollments.my-enrollments') ? 'active' : '' }}" href="{{ route('student.enrollments.my-enrollments') }}">
            <i class="fas fa-list"></i> All Enrollments
        </a>

        @if(Route::has('student.enrollments.browse-classes'))
        <a class="menu-item {{ request()->routeIs('student.enrollments.browse-classes') ? 'active' : '' }}" href="{{ route('student.enrollments.browse-classes') }}">
            <i class="fas fa-search"></i> Browse Classes
        </a>
        @endif

        @if(Route::has('student.enrollments.browse-packages'))
        <a class="menu-item {{ request()->routeIs('student.enrollments.browse-packages') ? 'active' : '' }}" href="{{ route('student.enrollments.browse-packages') }}">
            <i class="fas fa-box-open"></i> Browse Packages
        </a>
        @endif
    </div>
</div>
@endif

{{-- ==================== LEARNING ==================== --}}
<div class="menu-dropdown">
    <a href="#section64" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Learning
    </a>
    <div class="collapse" id="section64">
        @if(Route::has('student.announcements.index'))
        <a href="{{ route('student.announcements.index') }}" class="menu-item {{ request()->routeIs('student.announcements.*') ? 'active' : '' }}">
            <i class="fas fa-bullhorn"></i> Announcements
        </a>
        @endif

        @if(Route::has('student.materials.index'))
        <a href="{{ route('student.materials.index') }}" class="menu-item {{ request()->routeIs('student.materials.*') ? 'active' : '' }}">
            <i class="fas fa-book-open"></i> Study Materials
        </a>
        @endif

        @if(Route::has('student.schedule.index'))
        <a href="{{ route('student.schedule.index') }}" class="menu-item {{ request()->routeIs('student.schedule.*') ? 'active' : '' }}">
            <i class="fas fa-calendar-alt"></i> My Schedule
        </a>
        @endif
    </div>
</div>

{{-- ==================== SEMINARS ==================== --}}
@if(Route::has('student.seminars.index') || Route::has('student.seminars.my-registrations'))
<div class="menu-dropdown">
    <a href="#section65" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Seminars
    </a>
    <div class="collapse" id="section65">
        @if(Route::has('student.seminars.index'))
        <a href="{{ route('student.seminars.index') }}" class="menu-item {{ request()->routeIs('student.seminars.index') ? 'active' : '' }}">
            <i class="fas fa-chalkboard"></i> Browse Seminars
        </a>
        @endif

        @if(Route::has('student.seminars.my-registrations'))
        <a href="{{ route('student.seminars.my-registrations') }}" class="menu-item {{ request()->routeIs('student.seminars.my-registrations') ? 'active' : '' }}">
            <i class="fas fa-ticket-alt"></i> My Registrations
        </a>
        @endif
    </div>
</div>
@endif

{{-- ==================== REFERRALS ==================== --}}
@if(Route::has('student.referrals.index'))
<div class="menu-dropdown">
    <a href="#section66" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Referrals
    </a>
    <div class="collapse" id="section66">
        <a href="{{ route('student.referrals.index') }}" class="menu-item {{ request()->routeIs('student.referrals.*') ? 'active' : '' }}">
            <i class="fas fa-user-plus"></i> My Referrals
        </a>
    </div>
</div>
@endif

{{-- ==================== REVIEWS ==================== --}}
@if(Route::has('student.reviews.index'))
<div class="menu-dropdown">
    <a href="#section67" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Reviews
    </a>
    <div class="collapse" id="section67">
        <a href="{{ route('student.reviews.index') }}" class="menu-item {{ request()->routeIs('student.reviews.*') ? 'active' : '' }}">
            <i class="fas fa-star"></i> My Reviews
        </a>
    </div>
</div>
@endif

{{-- ==================== ACCOUNT ==================== --}}
<div class="menu-dropdown">
    <a href="#section68" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Account
    </a>
    <div class="collapse" id="section68">
        <a href="{{ route('profile.index') }}" class="menu-item {{ request()->routeIs('profile.index') || request()->routeIs('profile.edit') ? 'active' : '' }}">
            <i class="fas fa-user"></i> My Profile
        </a>

        @if(Route::has('profile.change-password'))
        <a href="{{ route('profile.change-password') }}" class="menu-item {{ request()->routeIs('profile.change-password') ? 'active' : '' }}">
            <i class="fas fa-key"></i> Change Password
        </a>
        @endif
    </div>
</div>

@endrole

<style>
/* Dropdown Menu Styles */
.menu-dropdown {
    margin-bottom: 5px;
}

.menu-section-title {
    display: flex;
    align-items: center;
    padding: 10px 15px;
    color: #fff;
    /* background-color: #2c3e50; */
    cursor: pointer;
    text-decoration: none;
    font-weight: 600;
    border-radius: 5px;
    transition: background-color 0.3s;
}

.menu-section-title:hover {
    /* background-color: #34495e; */
    color: #fff;
    text-decoration: none;
}

/* Sidebar background color for Main tab */
/* .menu-section-title.sidebar-bg-color {
    background-color: #1a252f;
}

.menu-section-title.sidebar-bg-color:hover {
    background-color: #2c3e50;
} */

/* Active dropdown (contains active menu item) */
/* .menu-section-title.has-active {
    background-color: #3498db;
}

.menu-section-title.has-active:hover {
    background-color: #2980b9;
} */

.menu-section-title i.fa-chevron-down {
    margin-right: 8px;
    font-size: 12px;
    transition: transform 0.3s;
}

.menu-section-title[aria-expanded="true"] i.fa-chevron-down {
    transform: rotate(180deg);
}

.menu-dropdown .collapse {
    margin-top: 5px;
}

.menu-dropdown .menu-item {
    padding-left: 35px;
}
</style>

<script>
// Toggle chevron icon rotation and auto-open active dropdown
document.addEventListener('DOMContentLoaded', function() {
    const dropdownToggles = document.querySelectorAll('[data-bs-toggle="collapse"]');

    // Setup collapse event listeners
    dropdownToggles.forEach(toggle => {
        const targetId = toggle.getAttribute('href');
        const targetElement = document.querySelector(targetId);

        if (targetElement) {
            targetElement.addEventListener('show.bs.collapse', function() {
                toggle.setAttribute('aria-expanded', 'true');
            });

            targetElement.addEventListener('hide.bs.collapse', function() {
                toggle.setAttribute('aria-expanded', 'false');
            });
        }
    });

    // Auto-open dropdown containing active menu item
    const activeMenuItems = document.querySelectorAll('.menu-item.active');

    activeMenuItems.forEach(activeItem => {
        // Find the parent collapse div
        const parentCollapse = activeItem.closest('.collapse');

        if (parentCollapse) {
            // Get the collapse ID
            const collapseId = parentCollapse.getAttribute('id');

            // Find the toggle button for this collapse
            const toggleButton = document.querySelector(`[href="#${collapseId}"]`);

            if (toggleButton) {
                // Add has-active class to highlight the dropdown header
                toggleButton.classList.add('has-active');

                // Open the dropdown using Bootstrap's collapse
                const bsCollapse = new bootstrap.Collapse(parentCollapse, {
                    toggle: false
                });
                bsCollapse.show();

                // Set aria-expanded to true
                toggleButton.setAttribute('aria-expanded', 'true');
            }
        }
    });
});
</script>
