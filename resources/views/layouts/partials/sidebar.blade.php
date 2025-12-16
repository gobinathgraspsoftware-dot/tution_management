{{-- Admin/Super Admin Sidebar --}}
@role('super-admin|admin')
<!-- Main -->
<div class="menu-dropdown">
    <a href="#section1" class="menu-section-title sidebar-bg-color" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Main
    </a>
    <div class="collapse" id="section1">
    <a href="{{ route('admin.dashboard') }}" class="menu-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
    <i class="fas fa-home"></i> Dashboard
    </a>
    {{-- Start: User Management --}}
    </div>
</div>
<!-- User Management -->
<div class="menu-dropdown">
    <a href="#section2" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> User Management
    </a>
    <div class="collapse" id="section2">
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
    {{-- End: User Management --}}

    {{-- Start: Attendance Management --}}
    @canany(['view-student-attendance-all', 'view-teacher-attendance-all', 'mark-student-attendance', 'mark-teacher-attendance'])
    </div>
</div>

{{-- Start: Enrollment Management --}}
@if(Route::has('admin.enrollments.index'))
<div class="menu-dropdown">
    <a href="#admin_enrollment_collapse" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Enrollments
    </a>
    <div class="collapse" id="admin_enrollment_collapse">
        @can('view-enrollments')
            <a class="menu-item {{ request()->routeIs('admin.enrollments.index') ? 'active' : '' }}" href="{{ route('admin.enrollments.index') }}">
                <i class="fas fa-list"></i> All Enrollments
            </a>
        @endcan
        @can('create-enrollments')
            <a class="menu-item {{ request()->routeIs('admin.enrollments.create') ? 'active' : '' }}" href="{{ route('admin.enrollments.create') }}">
                <i class="fas fa-plus"></i> New Enrollment
            </a>
        @endcan
    </div>
</div>
@endif
{{-- End: Enrollment Management --}}

<!-- Attendance Management -->
<div class="menu-dropdown">
    <a href="#section3" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Attendance Management
    </a>
    <div class="collapse" id="section3">
    @can('view-student-attendance-all')
    <a href="{{ route('admin.attendance.index') }}" class="menu-item {{ request()->routeIs('admin.attendance.index') ? 'active' : '' }}"><i class="fas fa-users"></i> Attendance Dashboard</a>
    @endcan
    @can('mark-student-attendance')
    <a href="{{ route('admin.attendance.student.mark') }}" class="menu-item {{ request()->routeIs('admin.attendance.student.mark') ? 'active' : '' }}"><i class="fas fa-home"></i>Student Attendance</a>
    @endcan

    @can('view-student-attendance-all')
    <a href="{{ route('admin.attendance.student.calendar') }}" class="menu-item {{ request()->routeIs('admin.attendance.student.calendar') ? 'active' : '' }}"><i class="fas fa-home"></i>Student Calendar</a>
    @endcan

    @can('mark-teacher-attendance')
    <a href="{{ route('admin.attendance.teacher.mark') }}" class="menu-item {{ request()->routeIs('admin.attendance.teacher.mark') ? 'active' : '' }}"><i class="fas fa-home"></i>Teacher Attendance</a>
    @endcan

    @can('view-teacher-attendance-all')
    <a href="{{ route('admin.attendance.teacher.calendar') }}" class="menu-item {{ request()->routeIs('admin.attendance.teacher.calendar') ? 'active' : '' }}"><i class="fas fa-home"></i>Teacher Calendar</a>
    @endcan
    @endcanany
    </div>
</div>
<!-- Teacher Salary -->
<div class="menu-dropdown">
    <a href="#section4" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Teacher Salary
    </a>
    <div class="collapse" id="section4">
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
    {{-- End: Attendance Management --}}

    {{-- Start: Financial Management --}}
    @canany(['view-financial-dashboard', 'view-revenue-reports', 'view-expense-reports'])
    </div>
</div>
<!-- Financial Management -->
<div class="menu-dropdown">
    <a href="#section5" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Financial Management
    </a>
    <div class="collapse" id="section5">

    @can('view-financial-dashboard')
    <a href="{{ route('admin.financial.dashboard') }}"
       class="menu-item {{ request()->routeIs('admin.financial.dashboard') ? 'active' : '' }}">
    <i class="fas fa-chart-line"></i>
    <span>Financial Dashboard</span>
    </a>
    @endcan

    @can('view-revenue-reports')
    <a href="{{ route('admin.financial.reports') }}"
       class="menu-item {{ request()->routeIs('admin.financial.reports') ? 'active' : '' }}">
    <i class="fas fa-file-invoice-dollar"></i>
    <span>Financial Reports</span>
    </a>
    @endcan

    @can('view-profit-loss-reports')
    <a href="{{ route('admin.financial.reports.profit-loss') }}"
       class="menu-item {{ request()->routeIs('admin.financial.reports.profit-loss') ? 'active' : '' }}">
    <i class="fas fa-balance-scale"></i>
    <span>Profit & Loss</span>
    </a>
    @endcan

    @can('view-category-revenue')
    <a href="{{ route('admin.financial.reports.category-revenue') }}"
       class="menu-item {{ request()->routeIs('admin.financial.reports.category-revenue') ? 'active' : '' }}">
    <i class="fas fa-chart-pie"></i>
    <span>Revenue by Category</span>
    </a>
    @endcan

    @can('view-financial-dashboard')
    <a href="{{ route('admin.financial.reports.cash-flow') }}"
       class="menu-item {{ request()->routeIs('admin.financial.reports.cash-flow') ? 'active' : '' }}">
    <i class="fas fa-exchange-alt"></i>
    <span>Cash Flow</span>
    </a>
    @endcan
    @endcanany
    {{-- End: Financial Management --}}

    {{-- Start: Seminar Management --}}
    </div>
</div>
<!-- Seminar Management -->
<div class="menu-dropdown">
    <a href="#section6" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Seminar Management
    </a>
    <div class="collapse" id="section6">

    {{-- @can('view-seminars') --}}
    <a href="{{ route('admin.seminars.index') }}" class="menu-item {{ request()->routeIs('admin.seminars.*') ? 'active' : '' }}">
    <i class="fas fa-calendar-alt"></i> Seminars
    </a>
    {{-- @endcan --}}

    {{-- @can('view-seminar-participants')
    @if(Route::has('admin.seminars.index'))
    <a href="{{ route('admin.seminars.index', ['status' => 'open']) }}" class="menu-item {{ request()->routeIs('admin.seminars.index') && request('status') == 'open' ? 'active' : '' }}">
    <i class="fas fa-door-open"></i> Open Seminars
    @php
        $openSeminars = \App\Models\Seminar::where('status', 'open')->count();
    @endphp
    @if($openSeminars > 0)
    <span class="badge bg-success text-white ms-auto">{{ $openSeminars }}</span>
    @endif
    </a>
    @endif
    @endcan --}}

    {{-- Public Seminar Page Link --}}
    @if(Route::has('public.seminars.index'))
    <a href="{{ route('public.seminars.index') }}" class="menu-item" target="_blank">
    <i class="fas fa-external-link-alt"></i> Public Seminar Page
    </a>
    @endif
    <!-- Seminar Accounting -->
    @if(Route::has('admin.seminars.accounting.dashboard'))
    </div>
</div>
<!-- Seminar Accounting -->
<div class="menu-dropdown">
    <a href="#section7" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Seminar Accounting
    </a>
    <div class="collapse" id="section7">
    <a href="{{ route('admin.seminars.accounting.dashboard') }}" class="menu-item {{ request()->routeIs('admin.seminars.accounting.dashboard') ? 'active' : '' }}">
    <i class="fas fa-calendar-alt"></i> Dashboard
    </a>
    <a href="{{ route('admin.seminars.accounting.reports.profitability') }}" class="menu-item {{ request()->routeIs('admin.seminars.accounting.reports.profitability*') ? 'active' : '' }}">
    <i class="fas fa-file-invoice-dollar"></i> Profitability Report
    </a>
    <a href="{{ route('admin.seminars.accounting.reports.payment-status') }}" class="menu-item {{ request()->routeIs('admin.seminars.accounting.reports.payment-status') ? 'active' : '' }}">
    <i class="fas fa-sync-alt"></i> Payment Status
    </a>
    @endif
    {{-- End: Seminar Management --}}

    {{-- Start: Reports Management --}}
    </div>
</div>
<!-- Reports Management -->
<div class="menu-dropdown">
    <a href="#section8" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Reports Management
    </a>
    <div class="collapse" id="section8">
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
    {{-- End: Reports Management --}}

    {{-- Start: Referral & Trial --}}
    </div>
</div>
<!-- Referral & Trial -->
<div class="menu-dropdown">
    <a href="#section9" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Referral & Trial
    </a>
    <div class="collapse" id="section9">

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
    {{-- End: Referral & Trial --}}

    {{-- Start: Materials Management  --}}
    </div>
</div>
<!-- Materials Management -->
<div class="menu-dropdown">
    <a href="#section10" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Materials Management
    </a>
    <div class="collapse" id="section10">
    <a href="{{ route('admin.materials.index') }}" class="menu-item {{ request()->routeIs('admin.materials.*') ? 'active' : '' }}">
    <i class="fas fa-file-alt"></i> Digital Materials
    </a>
    <a href="{{ route('admin.physical-materials.index') }}" class="menu-item {{ request()->routeIs('admin.physical-materials.*') ? 'active' : '' }}">
    <i class="fas fa-book"></i> Physical Materials
    </a>
    {{-- End: Materials Management  --}}

    {{-- Start: Academic Management  --}}
    </div>
</div>
<!-- Academic Management -->
<div class="menu-dropdown">
    <a href="#section11" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Academic Management
    </a>
    <div class="collapse" id="section11">
    <a href="{{ route('admin.subjects.index') }}" class="menu-item {{ request()->routeIs('admin.subjects.*') ? 'active' : '' }}">
    <i class="fas fa-book"></i> Subjects
    </a>
    <a href="{{ route('admin.packages.index') }}" class="menu-item {{ request()->routeIs('admin.packages.*') ? 'active' : '' }}">
    <i class="fas fa-box"></i> Packages
    </a>
    {{-- End: Academic Management  --}}
    @canany(['view-classes', 'create-classes', 'manage-class-schedule'])
    </div>
</div>
<!-- CLASS MANAGEMENT -->
<div class="menu-dropdown">
    <a href="#section12" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> CLASS MANAGEMENT
    </a>
    <div class="collapse" id="section12">

    @can('view-classes')
    <a href="{{ route('admin.classes.index') }}"
       class="menu-item {{ request()->routeIs('admin.classes.*') ? 'active' : '' }}">
    <i class="fas fa-chalkboard"></i>
    Classes
    </a>
    @endcan
    @endcanany
    <a href="{{ route('timetable.index') }}"
       class="menu-item {{ request()->routeIs('timetable.index') ? 'active' : '' }}">
    <i class="fas fa-calendar-week"></i>
    Timetable
    </a>
    <a href="{{ route('admin.classes.timetable') }}"
       class="menu-item {{ request()->routeIs('admin.classes.timetable') ? 'active' : '' }}">
    <i class="fas fa-calendar-week"></i>
    Weekly Timetable
    </a>
    </div>
</div>
<!-- Operations -->
<div class="menu-dropdown">
    <a href="#section13" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Operations
    </a>
    <div class="collapse" id="section13">
    <a href="#" class="menu-item">
    <i class="fas fa-check-square"></i> Attendance
    </a>

    {{-- Start: Financial Section --}}
    </div>
</div>
<!-- Financial -->
<div class="menu-dropdown">
    <a href="#section14" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Financial
    </a>
    <div class="collapse" id="section14">
    <a href="{{ route('admin.invoices.index') }}" class="menu-item {{ request()->routeIs('admin.invoices.index') || request()->routeIs('admin.invoices.create') || request()->routeIs('admin.invoices.show') || request()->routeIs('admin.invoices.edit') ? 'active' : '' }}">
    <i class="fas fa-file-invoice-dollar"></i> Invoices
    </a>
    <a href="{{ route('admin.invoices.overdue') }}" class="menu-item {{ request()->routeIs('admin.invoices.overdue') ? 'active' : '' }}">
    <i class="fas fa-exclamation-triangle"></i> Overdue Invoices
    </a>
    <a href="{{ route('admin.billing.payment-cycles') }}" class="menu-item {{ request()->routeIs('admin.billing.payment-cycles') ? 'active' : '' }}">
    <i class="fas fa-sync-alt"></i> Payment Cycles
    </a>
    <a href="{{ route('admin.billing.subscription-alerts') }}" class="menu-item {{ request()->routeIs('admin.billing.subscription-alerts') ? 'active' : '' }}">
    <i class="fas fa-bell"></i> Subscription Alerts
    </a>
    {{-- End: Financial Section --}}

    {{-- Start: Installments Menu --}}
    </div>
</div>
<!-- Installments -->
<div class="menu-dropdown">
    <a href="#section15" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Installments
    </a>
    <div class="collapse" id="section15">
    <a href="{{ route('admin.installments.index') }}" class="menu-item ps-4 {{ request()->routeIs('admin.installments.index') ? 'active' : '' }}">
    <i class="fas fa-list me-2"></i> All Installments
    </a>
    {{-- End: Installments Menu --}}

    {{-- Start: Payment Reminders Menu --}}
    </div>
</div>
<!-- Payment Reminders -->
<div class="menu-dropdown">
    <a href="#section16" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Payment Reminders
    </a>
    <div class="collapse" id="section16">
    <a href="{{ route('admin.reminders.index') }}"
    class="menu-item ps-4 {{ request()->routeIs('admin.reminders.index') ? 'active' : '' }}">
    <i class="fas fa-paper-plane me-2"></i> Send Reminders
    </a>
    @if(Route::has('admin.reminders.history'))
    <a href="{{ route('admin.reminders.history') }}"
    class="menu-item ps-4 {{ request()->routeIs('admin.reminders.history') ? 'active' : '' }}">
    <i class="fas fa-history me-2"></i> History
    </a>
    @endif
    @if(Route::has('admin.reminders.schedule'))
    <a href="{{ route('admin.reminders.schedule') }}"
    class="menu-item ps-4 {{ request()->routeIs('admin.reminders.schedule') ? 'active' : '' }}">
    <i class="fas fa-calendar-alt me-2"></i> Schedule
    </a>
    @endif
    @if(Route::has('admin.reminders.settings'))
    <a href="{{ route('admin.reminders.settings') }}"
    class="menu-item ps-4 {{ request()->routeIs('admin.reminders.settings') ? 'active' : '' }}">
    <i class="fas fa-cog me-2"></i> Settings
    </a>
    @endif
    {{-- End: Payment Reminders Menu --}}

    {{-- Start: Arrears Menu --}}
    </div>
</div>
<!-- Arrears Menu -->
<div class="menu-dropdown">
    <a href="#section17" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Arrears Menu
    </a>
    <div class="collapse" id="section17">
    <a href="{{ route('admin.arrears.index') }}"
    class="menu-item ps-4 {{ request()->routeIs('admin.arrears.index') ? 'active' : '' }}">
    <i class="fas fa-tachometer-alt me-2"></i> Dashboard
    </a>
    @if(Route::has('admin.arrears.students-list'))
    <a href="{{ route('admin.arrears.students-list') }}"
    class="menu-item ps-4 {{ request()->routeIs('admin.arrears.students-list') ? 'active' : '' }}">
    <i class="fas fa-users me-2"></i> Students List
    </a>
    @endif
    @if(Route::has('admin.arrears.by-class'))
    <a href="{{ route('admin.arrears.by-class') }}"
    class="menu-item ps-4 {{ request()->routeIs('admin.arrears.by-class') ? 'active' : '' }}">
    <i class="fas fa-school me-2"></i> By Class
    </a>
    @endif
    @if(Route::has('admin.arrears.aging-analysis'))
    <a href="{{ route('admin.arrears.aging-analysis') }}"
    class="menu-item ps-4 {{ request()->routeIs('admin.arrears.aging-analysis') ? 'active' : '' }}">
    <i class="fas fa-chart-pie me-2"></i> Aging Analysis
    </a>
    @endif
    @if(Route::has('admin.arrears.due-report'))
    <a href="{{ route('admin.arrears.due-report') }}"
    class="menu-item ps-4 {{ request()->routeIs('admin.arrears.due-report') ? 'active' : '' }}">
    <i class="fas fa-calendar-times me-2"></i> Due Report
    </a>
    @endif
    @if(Route::has('admin.arrears.forecast'))
    <a href="{{ route('admin.arrears.forecast') }}"
    class="menu-item ps-4 {{ request()->routeIs('admin.arrears.forecast') ? 'active' : '' }}">
    <i class="fas fa-chart-line me-2"></i> Forecast
    </a>
    @endif
    {{-- End: Arrears Menu --}}

    {{-- Payment Menu with Submenu --}}
    @if(Route::has('admin.payments.index'))
    </div>
</div>
<!-- Payments Management -->
<div class="menu-dropdown">
    <a href="#section18" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Payments Management
    </a>
    <div class="collapse" id="section18">
    <a href="{{ route('admin.payments.index') }}" class="menu-item {{ request()->routeIs('admin.payments.index') ? 'active' : '' }}">
    <i class="fas fa-list"></i> All Payments
    </a>
    <a href="{{ route('admin.payments.create') }}" class="menu-item {{ request()->routeIs('admin.payments.create') ? 'active' : '' }}">
    <i class="fas fa-plus"></i> Record Payment
    </a>
    @if(Route::has('admin.payments.pending-verifications'))
    <a href="{{ route('admin.payments.pending-verifications') }}" class="menu-item {{ request()->routeIs('admin.payments.pending-verifications') ? 'active' : '' }}">
    <i class="fas fa-clock"></i> Pending Verifications
    @php
        $pendingCount = \App\Models\Payment::where('status', 'pending_verification')->count();
    @endphp
    @if($pendingCount > 0)
    <span class="badge bg-warning ms-auto">{{ $pendingCount }}</span>
    @endif
    </a>
    @endif
    @if(Route::has('admin.payments.daily-report'))
    <a href="{{ route('admin.payments.daily-report') }}" class="menu-item {{ request()->routeIs('admin.payments.daily-report') ? 'active' : '' }}">
    <i class="fas fa-cash-register"></i> Daily Cash Report
    </a>
    @endif
    <a href="{{ route('admin.payments.history') }}" class="menu-item {{ request()->routeIs('admin.payments.history') ? 'active' : '' }}">
    <i class="fas fa-history"></i> Payment History
    </a>
    @else
    <a href="#" class="menu-item">
    <i class="fas fa-money-bill-wave"></i> Payments
    </a>
    @endif
    @can('manage-payment-gateway')
    <a href="{{ route('admin.payment-gateways.index') }}" class="menu-item {{ request()->routeIs('admin.payment-gateways.*') ? 'active' : '' }}">
    <i class="fas fa-credit-card"></i> Payment Gateways
    </a>
    @endcan

    </div>
</div>
<!-- Content -->
<div class="menu-dropdown">
    <a href="#section19" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Content
    </a>
    <div class="collapse" id="section19">
    <a href="#" class="menu-item">
    <i class="fas fa-file-alt"></i> Materials
    </a>
    <a href="{{ route('announcements.index') }}" class="menu-item {{ request()->routeIs('announcements.index') ? 'active' : '' }}">
    <i class="fas fa-bullhorn"></i> Announcements
    </a>

    </div>
</div>
<!-- Other -->
<div class="menu-dropdown">
    <a href="#section20" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Other
    </a>
    <div class="collapse" id="section20">
    <a href="#" class="menu-item">
    <i class="fas fa-calendar-check"></i> Seminars
    </a>
    <a href="#" class="menu-item">
    <i class="fas fa-shopping-cart"></i> Cafeteria POS
    </a>
    <a href="#" class="menu-item">
    <i class="fas fa-cog"></i> Settings
    </a>

    </div>
</div>
<!-- Account -->
<div class="menu-dropdown">
    <a href="#section21" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Account
    </a>
    <div class="collapse" id="section21">
    <a href="{{ route('profile.index') }}" class="menu-item {{ request()->routeIs('profile.*') ? 'active' : '' }}">
    <i class="fas fa-user"></i> My Profile
    </a>

    </div>
</div>
<!-- Communications -->
<div class="menu-dropdown">
    <a href="#section22" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Communications
    </a>
    <div class="collapse" id="section22">
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
<div class="menu-dropdown">
    <a href="#section25" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Materials
    </a>
    <div class="collapse" id="section25">
    <a href="{{ route('admin.physical-materials.collections', ['physicalMaterial' => 1]) }}" class="menu-item">
    <i class="fas fa-hands"></i> Material Collection
    </a>
    {{-- End: Meterials --}}
    </div>
</div>
<!-- Students -->
<div class="menu-dropdown">
    <a href="#section26" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Students
    </a>
    <div class="collapse" id="section26">
    <a href="#" class="menu-item">
    <i class="fas fa-users"></i> All Students
    </a>
    {{-- Staff can view pending but limited actions --}}
    {{-- <a href="{{ route('admin.approvals.index') }}" class="menu-item {{ request()->routeIs('admin.approvals.*') ? 'active' : '' }}">
    <i class="fas fa-clock"></i> Pending Approvals
    </a> --}}

    <a href="#" class="menu-item">
    <i class="fas fa-user-graduate"></i> Trial Classes
    </a>

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

<!-- Operations -->
<div class="menu-dropdown">
    <a href="#section27" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Operations
    </a>
    <div class="collapse" id="section27">
    <a href="#" class="menu-item">
    <i class="fas fa-check-square"></i> Mark Attendance
    </a>
    </div>
</div>
<!-- Financial -->
<div class="menu-dropdown">
    <a href="#section28" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Financial
    </a>
    <div class="collapse" id="section28">
    @if(Route::has('admin.arrears.index'))
    <a href="{{ route('admin.arrears.index') }}"
       class="menu-item {{ request()->routeIs('admin.arrears.*') ? 'active' : '' }}">
    <i class="fas fa-exclamation-circle"></i> Arrears View
    </a>
    @endif
    {{-- Staff Payment Menu with Submenu --}}
    @if(Route::has('staff.payments.index'))
    <div class="menu-item-group">
    <a href="{{ route('staff.payments.index') }}" class="menu-item {{ request()->routeIs('staff.payments.*') ? 'active' : '' }}">
        <i class="fas fa-money-bill-wave"></i> Payments
        <i class="fas fa-chevron-down menu-arrow ms-auto"></i>
    </a>
    <div class="submenu {{ request()->routeIs('staff.payments.*') ? 'show' : '' }}">
        <a href="{{ route('staff.payments.index') }}" class="menu-item {{ request()->routeIs('staff.payments.index') ? 'active' : '' }}">
            <i class="fas fa-list"></i> Today's Payments
        </a>
        <a href="{{ route('staff.payments.create') }}" class="menu-item {{ request()->routeIs('staff.payments.create') ? 'active' : '' }}">
            <i class="fas fa-plus"></i> Record Payment
        </a>
        @if(Route::has('staff.payments.quick-payment'))
        <a href="{{ route('staff.payments.quick-payment') }}" class="menu-item {{ request()->routeIs('staff.payments.quick-payment') ? 'active' : '' }}">
            <i class="fas fa-bolt"></i> Quick Payment
        </a>
        @endif
    </div>
    </div>
    @else
    <a href="#" class="menu-item">
    <i class="fas fa-money-bill-wave"></i> Payments
    </a>
    @endif
    <a href="{{ route('timetable.index') }}"
       class="menu-item {{ request()->routeIs('timetable.index') ? 'active' : '' }}">
    <i class="fas fa-calendar-week"></i>
    Timetable
    </a>

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
    <a href="#" class="menu-item">
    <i class="fas fa-bullhorn"></i> Announcements
    </a>

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
    @else
    <a href="#" class="menu-item">
    <i class="fas fa-check-square"></i> Attendance
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
    @if(Route::has('teacher.materials.index'))
    <a href="{{ route('teacher.materials.index') }}" class="menu-item {{ request()->routeIs('teacher.materials.index') || request()->routeIs('teacher.materials.show') || request()->routeIs('teacher.materials.edit') ? 'active' : '' }}">
    <i class="fas fa-file-alt"></i> My Materials
    </a>
    @else
    <a href="#" class="menu-item">
    <i class="fas fa-file-alt"></i> My Materials
    </a>
    @endif

    @if(Route::has('teacher.materials.create'))
    <a href="{{ route('teacher.materials.create') }}" class="menu-item {{ request()->routeIs('teacher.materials.create') ? 'active' : '' }}">
    <i class="fas fa-file-upload"></i> Upload Materials
    </a>
    @else
    <a href="#" class="menu-item">
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
    @else
    <a href="#" class="menu-item">
    <i class="fas fa-school"></i> My Classes
    </a>
    @endif

    @if(Route::has('teacher.schedule.index'))
    <a href="{{ route('teacher.schedule.index') }}" class="menu-item {{ request()->routeIs('teacher.schedule.*') ? 'active' : '' }}">
    <i class="fas fa-calendar-alt"></i> My Schedule
    </a>
    @else
    <a href="#" class="menu-item">
    <i class="fas fa-calendar-alt"></i> My Schedule
    </a>
    @endif

    @if(Route::has('teacher.students.index'))
    <a href="{{ route('teacher.students.index') }}" class="menu-item {{ request()->routeIs('teacher.students.*') ? 'active' : '' }}">
    <i class="fas fa-users"></i> My Students
    </a>
    @else
    <a href="#" class="menu-item">
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
    @else
    <a href="#" class="menu-item">
    <i class="fas fa-file-signature"></i> Exams
    </a>
    @endif


    @if(Route::has('teacher.results.index'))
    <a href="{{ route('teacher.results.index') }}" class="menu-item {{ request()->routeIs('teacher.results.*') ? 'active' : '' }}">
    <i class="fas fa-chart-bar"></i> Results
    </a>
    @else
    <a href="#" class="menu-item">
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
    @else
    <a href="#" class="menu-item">
    <i class="fas fa-folder-open"></i> My Documents
    </a>
    @endif

    </div>
</div>
<!-- Other -->
<div class="menu-dropdown">
    <a href="#section38" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Other
    </a>
    <div class="collapse" id="section38">
    @if(Route::has('teacher.announcements.index'))
    <a href="{{ route('teacher.announcements.index') }}" class="menu-item {{ request()->routeIs('teacher.announcements.*') ? 'active' : '' }}">
    <i class="fas fa-bullhorn"></i> Announcements
    </a>
    @else
    <a href="#" class="menu-item">
    <i class="fas fa-bullhorn"></i> Announcements
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
<!-- Content -->
<div class="menu-dropdown">
    <a href="#section40" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Content
    </a>
    <div class="collapse" id="section40">
    <a href="#" class="menu-item">
    <i class="fas fa-file-alt"></i> Materials
    </a>
    <a href="#" class="menu-item">
    <i class="fas fa-clipboard-list"></i> Exams
    </a>

    </div>
</div>
<!-- Account -->
<div class="menu-dropdown">
    <a href="#section41" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Account
    </a>
    <div class="collapse" id="section41">
    <a href="{{ route('profile.index') }}" class="menu-item {{ request()->routeIs('profile.*') ? 'active' : '' }}">
    <i class="fas fa-user"></i> My Profile
    </a>
    </div>
</div>
@endrole

{{-- Parent Sidebar --}}
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
    <a href="{{ route('parent.children.index') }}" class="menu-item {{ request()->routeIs('parent.children.index') || request()->routeIs('parent.children.show') ? 'active' : '' }}">
    <i class="fas fa-users"></i> My Children
    </a>
    <a href="{{ route('parent.children.register') }}" class="menu-item {{ request()->routeIs('parent.children.register') ? 'active' : '' }}">
    <i class="fas fa-user-plus"></i> Register Child
    </a>

    </div>
</div>
<!-- Academic -->
<div class="menu-dropdown">
    <a href="#section44" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Academic
    </a>
    <div class="collapse" id="section44">
    <a href="{{ route('timetable.index') }}"
       class="menu-item {{ request()->routeIs('timetable.index') ? 'active' : '' }}">
    <i class="fas fa-calendar-week"></i>
    Timetable
    </a>
    {{-- Start: Financial Section --}}
    </div>
</div>

{{-- Start: Enrollment Management --}}
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
{{-- End: Enrollment Management --}}

<!-- Financial -->
<div class="menu-dropdown">
    <a href="#section45" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Financial
    </a>
    <div class="collapse" id="section45">
    <a href="{{ route('parent.invoices.index') }}" class="menu-item {{ request()->routeIs('parent.invoices.*') ? 'active' : '' }}">
    <i class="fas fa-file-invoice"></i> Invoices
    </a>
    <a href="{{ route('parent.payments.create') }}" class="menu-item {{ request()->routeIs('parent.payments.create') ? 'active' : '' }}">
    <i class="fas fa-money-bill-wave"></i> Make Payment
    </a>
    <a href="{{ route('parent.invoices.history') }}" class="menu-item {{ request()->routeIs('parent.invoices.history') ? 'active' : '' }}">
    <i class="fas fa-history"></i> Payment History
    </a>
    {{-- End: Financial Section --}}
    @if(Route::has('parent.payments.pay-online'))
    <a href="{{ route('parent.payments.pay-online') }}" class="menu-item {{ request()->routeIs('parent.payments.pay-online') ? 'active' : '' }}">
    <i class="fas fa-globe"></i> Pay Online
    </a>
    @endif
    <a href="#" class="menu-item">
    <i class="fas fa-check-square"></i> Attendance
    </a>
    <a href="#" class="menu-item">
    <i class="fas fa-clipboard-list"></i> Results
    </a>
    <a href="{{ route('parent.materials.index') }}" class="menu-item {{ request()->routeIs('parent.materials.*') ? 'active' : '' }}">
    <i class="fas fa-book-open"></i> Study Materials
    </a>

    </div>
</div>
<!-- Payments -->
<div class="menu-dropdown">
    <a href="#section46" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Payments
    </a>
    <div class="collapse" id="section46">
    {{-- Parent Payment Menu --}}
    @if(Route::has('parent.payments.index'))
    <a href="{{ route('parent.payments.index') }}" class="menu-item {{ request()->routeIs('parent.payments.index') || request()->routeIs('parent.payments.show') ? 'active' : '' }}">
    <i class="fas fa-money-bill-wave"></i> Payments
    </a>
    @else
    <a href="#" class="menu-item">
    <i class="fas fa-money-bill-wave"></i> Payments
    </a>
    @endif
    <a href="#" class="menu-item">
    <i class="fas fa-file-invoice-dollar"></i> Invoices
    </a>
    <a href="#" class="menu-item">
    <i class="fas fa-money-bill-wave"></i> Payment History
    </a>

    </div>
</div>
<!-- Other -->
<div class="menu-dropdown">
    <a href="#section47" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Other
    </a>
    <div class="collapse" id="section47">
    <a href="#" class="menu-item">
    <i class="fas fa-bullhorn"></i> Announcements
    </a>
    <a href="#" class="menu-item">
    <i class="fas fa-file-alt"></i> Materials
    </a>

    </div>
</div>
<!-- Account -->
<div class="menu-dropdown">
    <a href="#section48" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Account
    </a>
    <div class="collapse" id="section48">
    <a href="{{ route('profile.index') }}" class="menu-item {{ request()->routeIs('profile.*') ? 'active' : '' }}">
    <i class="fas fa-user"></i> My Profile
    </a>
    </div>
</div>
@endrole

{{-- Student Sidebar --}}
@role('student')
<!-- Main -->
<div class="menu-dropdown">
    <a href="#section49" class="menu-section-title sidebar-bg-color" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Main
    </a>
    <div class="collapse" id="section49">
    <a href="{{ route('student.dashboard') }}" class="menu-item {{ request()->routeIs('student.dashboard') ? 'active' : '' }}">
    <i class="fas fa-home"></i> Dashboard
    </a>
    {{-- Start: Student Financial Section  --}}
    </div>
</div>
<!-- Financial -->
<div class="menu-dropdown">
    <a href="#section50" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Financial
    </a>
    <div class="collapse" id="section50">
    <a href="{{ route('student.invoices.index') }}" class="menu-item {{ request()->routeIs('student.invoices.*') ? 'active' : '' }}">
    <i class="fas fa-file-invoice"></i> My Invoices
    </a>
    <a href="{{ route('student.invoices.history') }}" class="menu-item {{ request()->routeIs('student.invoices.history') ? 'active' : '' }}">
    <i class="fas fa-history"></i> Payment History
    </a>
    {{-- End: Student Financial Section  --}}
    {{-- Student Payment Menu --}}
    @if(Route::has('student.payments.index'))
    <a href="{{ route('student.payments.index') }}" class="menu-item {{ request()->routeIs('student.payments.index') || request()->routeIs('student.payments.show') ? 'active' : '' }}">
    <i class="fas fa-money-bill-wave"></i> My Payments
    </a>
    @else
    <a href="#" class="menu-item">
    <i class="fas fa-money-bill-wave"></i> My Payments
    </a>
    @endif
    @if(Route::has('student.payments.pay-online'))
    <a href="{{ route('student.payments.pay-online') }}" class="menu-item {{ request()->routeIs('student.payments.pay-online') ? 'active' : '' }}">
    <i class="fas fa-globe"></i> Pay Online
    </a>
    @endif
    </div>
</div>
<!-- Academic -->
<div class="menu-dropdown">
    <a href="#section51" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Academic
    </a>
    <div class="collapse" id="section51">
    <a href="#" class="menu-item">
    <i class="fas fa-school"></i> My Classes
    </a>
    <a href="{{ route('timetable.index') }}"
       class="menu-item {{ request()->routeIs('timetable.index') ? 'active' : '' }}">
    <i class="fas fa-calendar-week"></i>
    Timetable
    </a>
    <a href="#" class="menu-item">
    <i class="fas fa-check-square"></i> Attendance
    </a>
    <a href="#" class="menu-item">
    <i class="fas fa-clipboard-list"></i> Results
    </a>

    </div>
</div>

{{-- Start: Enrollment Management --}}
@if(Route::has('student.enrollments.my-enrollments'))
<div class="menu-dropdown">
    <a href="#student_enrollment_collapse" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Enrollments
    </a>
    <div class="collapse" id="student_enrollment_collapse">
        <a class="menu-item {{ request()->routeIs('student.enrollments.my-enrollments') ? 'active' : '' }}" href="{{ route('student.enrollments.my-enrollments') }}">
            <i class="fas fa-list"></i> All Enrollments
        </a>
        <a class="menu-item {{ request()->routeIs('student.enrollments.browse-classes') ? 'active' : '' }}" href="{{ route('student.enrollments.browse-classes') }}">
            <i class="fas fa-search"></i> Browse Classes
        </a>
        <a class="menu-item {{ request()->routeIs('student.enrollments.browse-packages') ? 'active' : '' }}" href="{{ route('student.enrollments.browse-packages') }}">
            <i class="fas fa-search"></i> Browse Packages
        </a>
    </div>
</div>
@endif
{{-- End: Enrollment Management --}}

<!-- Learning -->
<div class="menu-dropdown">
    <a href="#section52" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Learning
    </a>
    <div class="collapse" id="section52">
    <a href="#" class="menu-item">
    <i class="fas fa-bullhorn"></i> Announcements
    </a>
    <a href="{{ route('student.materials.index') }}" class="menu-item {{ request()->routeIs('student.materials.*') ? 'active' : '' }}">
    <i class="fas fa-book-open"></i> Study Materials
    </a>
    <a href="#" class="menu-item">
    <i class="fas fa-calendar-alt"></i> My Schedule
    </a>

    </div>
</div>
<!-- Account -->
<div class="menu-dropdown">
    <a href="#section53" class="menu-section-title" data-bs-toggle="collapse" aria-expanded="false">
        <i class="fas fa-chevron-down"></i> Account
    </a>
    <div class="collapse" id="section53">
    <a href="{{ route('profile.index') }}" class="menu-item {{ request()->routeIs('profile.*') ? 'active' : '' }}">
    <i class="fas fa-user"></i> My Profile
    </a>
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
