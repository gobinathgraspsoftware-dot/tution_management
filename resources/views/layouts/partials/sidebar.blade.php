{{-- Admin/Super Admin Sidebar --}}
@role('super-admin|admin')
<div class="menu-section-title">Main</div>
<a href="{{ route('admin.dashboard') }}" class="menu-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
    <i class="fas fa-home"></i> Dashboard
</a>
<!-- Attendance Management -->
@canany(['view-student-attendance-all', 'view-teacher-attendance-all', 'mark-student-attendance', 'mark-teacher-attendance'])
<div class="menu-section-title">Attendance Management</div>
@can('view-student-attendance-all')
    <a href="{{ route('admin.attendance.index') }}" class="menu-item {{ request()->routeIs('admin.attendance.index') ? 'active' : '' }}"><i class="fas fa-users"></i> Dashboard</a>
@endcan
@can('mark-student-attendance')
    <a href="{{ route('admin.attendance.student.mark') }}" class="menu-item {{ request()->routeIs('admin.attendance.student.mark') ? 'active' : '' }}"><i class="fas fa-home"></i>Mark Student Attendance</a>
@endcan

@can('view-student-attendance-all')
    <a href="{{ route('admin.attendance.student.calendar') }}" class="menu-item {{ request()->routeIs('admin.attendance.student.calendar') ? 'active' : '' }}"><i class="fas fa-home"></i>Student Calendar</a>
@endcan

@can('mark-teacher-attendance')
    <a href="{{ route('admin.attendance.teacher.mark') }}" class="menu-item {{ request()->routeIs('admin.attendance.teacher.mark') ? 'active' : '' }}"><i class="fas fa-home"></i>Mark Teacher Attendance</a>
@endcan

@can('view-teacher-attendance-all')
    <a href="{{ route('admin.attendance.teacher.calendar') }}" class="menu-item {{ request()->routeIs('admin.attendance.teacher.calendar') ? 'active' : '' }}"><i class="fas fa-home"></i>Teacher Calendar</a>
@endcan
@endcanany
{{-- Attendance Reports Submenu --}}
<a href="#attendanceReportsSubmenu" class="menu-item has-submenu {{ request()->routeIs('admin.attendance.reports.*') ? 'active' : '' }}" data-bs-toggle="collapse">
    <i class="fas fa-chart-bar"></i> Reports
    <i class="fas fa-chevron-down submenu-arrow ms-auto"></i>
</a>
<div class="collapse {{ request()->routeIs('admin.attendance.reports.*') ? 'show' : '' }}" id="attendanceReportsSubmenu">
    <a href="{{ route('admin.attendance.reports.index') }}" class="menu-item submenu-item {{ request()->routeIs('admin.attendance.reports.index') ? 'active' : '' }}">
        <i class="fas fa-tachometer-alt"></i> Dashboard
    </a>
    <a href="{{ route('admin.attendance.reports.student') }}" class="menu-item submenu-item {{ request()->routeIs('admin.attendance.reports.student') ? 'active' : '' }}">
        <i class="fas fa-user"></i> Student Report
    </a>
    <a href="{{ route('admin.attendance.reports.class') }}" class="menu-item submenu-item {{ request()->routeIs('admin.attendance.reports.class') ? 'active' : '' }}">
        <i class="fas fa-school"></i> Class Report
    </a>
    <a href="{{ route('admin.attendance.reports.low-attendance') }}" class="menu-item submenu-item {{ request()->routeIs('admin.attendance.reports.low-attendance') ? 'active' : '' }}">
        <i class="fas fa-exclamation-triangle"></i> Low Attendance
    </a>
    <a href="{{ route('admin.attendance.reports.history') }}" class="menu-item submenu-item {{ request()->routeIs('admin.attendance.reports.history') ? 'active' : '' }}">
        <i class="fas fa-history"></i> History
    </a>
</div>
<div class="menu-section-title">User Management</div>
{{-- <a href="{{ route('admin.students.index') }}" class="menu-item {{ request()->routeIs('admin.students.*') ? 'active' : '' }}">
    <i class="fas fa-users"></i> Students
</a> --}}
@can('view-students')
<a href="{{ route('admin.students.index') }}" class="menu-item {{ request()->routeIs('admin.students.index') ? 'active' : '' }}">
    <i class="fas fa-users"></i> All Students
</a>
@endcan
{{-- NEW: Add Pending Approvals menu item --}}
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

{{-- Section Start: Referral & Trial --}}
<div class="menu-section-title">Referral & Trial</div>

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
{{-- Section End: Referral & Trial --}}
{{-- Section Start: Materials Management  --}}
<div class="menu-section-title">Materials Management</div>
<a href="{{ route('admin.materials.index') }}" class="menu-item {{ request()->routeIs('admin.materials.*') ? 'active' : '' }}">
    <i class="fas fa-file-alt"></i> Digital Materials
</a>
<a href="{{ route('admin.physical-materials.index') }}" class="menu-item {{ request()->routeIs('admin.physical-materials.*') ? 'active' : '' }}">
    <i class="fas fa-book"></i> Physical Materials
</a>
{{-- Section End: Materials Management  --}}
<div class="menu-section-title">Academic</div>
<a href="{{ route('admin.subjects.index') }}" class="menu-item {{ request()->routeIs('admin.subjects.*') ? 'active' : '' }}">
    <i class="fas fa-book"></i> Subjects
</a>
<a href="{{ route('admin.packages.index') }}" class="menu-item {{ request()->routeIs('admin.packages.*') ? 'active' : '' }}">
    <i class="fas fa-box"></i> Packages
</a>
@canany(['view-classes', 'create-classes', 'manage-class-schedule'])
<div class="menu-section-title">CLASS MANAGEMENT</div>

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
<div class="menu-section-title">Operations</div>
<a href="#" class="menu-item">
    <i class="fas fa-check-square"></i> Attendance
</a>
<a href="#" class="menu-item">
    <i class="fas fa-file-invoice-dollar"></i> Invoices
</a>
<a href="#" class="menu-item">
    <i class="fas fa-money-bill-wave"></i> Payments
</a>

<div class="menu-section-title">Content</div>
<a href="#" class="menu-item">
    <i class="fas fa-file-alt"></i> Materials
</a>
<a href="{{ route('announcements.index') }}" class="menu-item {{ request()->routeIs('announcements.index') ? 'active' : '' }}">
    <i class="fas fa-bullhorn"></i> Announcements
</a>

<div class="menu-section-title">Other</div>
<a href="#" class="menu-item">
    <i class="fas fa-calendar-check"></i> Seminars
</a>
<a href="#" class="menu-item">
    <i class="fas fa-shopping-cart"></i> Cafeteria POS
</a>
<a href="#" class="menu-item">
    <i class="fas fa-cog"></i> Settings
</a>

<div class="menu-section-title">Account</div>
<a href="{{ route('profile.index') }}" class="menu-item {{ request()->routeIs('profile.*') ? 'active' : '' }}">
    <i class="fas fa-user"></i> My Profile
</a>

<div class="menu-section-title">Communications</div>
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

@endrole

{{-- Staff Sidebar --}}
@role('staff')
<div class="menu-section-title">Main</div>
<a href="{{ route('staff.dashboard') }}" class="menu-item {{ request()->routeIs('staff.dashboard') ? 'active' : '' }}">
    <i class="fas fa-home"></i> Dashboard
</a>

<div class="menu-section-title">Registration</div>
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
<div class="menu-section-title">Materials</div>
<a href="{{ route('admin.physical-materials.collections', ['physicalMaterial' => 1]) }}" class="menu-item">
    <i class="fas fa-hands"></i> Material Collection
</a>
{{-- End: Meterials --}}
<div class="menu-section-title">Students</div>
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

<div class="menu-section-title">Operations</div>
<a href="#" class="menu-item">
    <i class="fas fa-check-square"></i> Mark Attendance
</a>
<a href="#" class="menu-item">
    <i class="fas fa-money-bill-wave"></i> Payments
</a>
<a href="{{ route('timetable.index') }}"
   class="menu-item {{ request()->routeIs('timetable.index') ? 'active' : '' }}">
    <i class="fas fa-calendar-week"></i>
    Timetable
</a>

<div class="menu-section-title">Other</div>
<a href="#" class="menu-item">
    <i class="fas fa-shopping-cart"></i> Cafeteria POS
</a>
<a href="#" class="menu-item">
    <i class="fas fa-bullhorn"></i> Announcements
</a>

<div class="menu-section-title">Account</div>
<a href="{{ route('profile.index') }}" class="menu-item {{ request()->routeIs('profile.*') ? 'active' : '' }}">
    <i class="fas fa-user"></i> My Profile
</a>
@endrole

{{-- Teacher Sidebar --}}
@role('teacher')
<div class="menu-section-title">Main</div>
<a href="{{ route('teacher.dashboard') }}" class="menu-item {{ request()->routeIs('teacher.dashboard') ? 'active' : '' }}">
    <i class="fas fa-home"></i> Dashboard
</a>

<div class="menu-section-title">Teaching</div>
<a href="#" class="menu-item">
    <i class="fas fa-check-square"></i> Attendance
</a>
<a href="{{ route('teacher.materials.index') }}" class="menu-item {{ request()->routeIs('teacher.materials.*') ? 'active' : '' }}">
    <i class="fas fa-upload"></i> My Materials
</a>
<a href="{{ route('teacher.materials.create') }}" class="menu-item">
    <i class="fas fa-plus-circle"></i> Upload Material
</a>
<a href="#" class="menu-item">
    <i class="fas fa-school"></i> My Classes
</a>
<a href="#" class="menu-item">
    <i class="fas fa-calendar-alt"></i> My Schedule
</a>

<div class="menu-section-title">Content</div>
<a href="#" class="menu-item">
    <i class="fas fa-file-alt"></i> Materials
</a>
<a href="#" class="menu-item">
    <i class="fas fa-clipboard-list"></i> Exams
</a>

<div class="menu-section-title">Account</div>
<a href="{{ route('profile.index') }}" class="menu-item {{ request()->routeIs('profile.*') ? 'active' : '' }}">
    <i class="fas fa-user"></i> My Profile
</a>
@endrole

{{-- Parent Sidebar --}}
@role('parent')
<div class="menu-section-title">Main</div>
<a href="{{ route('parent.dashboard') }}" class="menu-item {{ request()->routeIs('parent.dashboard') ? 'active' : '' }}">
    <i class="fas fa-home"></i> Dashboard
</a>

<div class="menu-section-title">Children</div>
<a href="{{ route('parent.children.index') }}" class="menu-item {{ request()->routeIs('parent.children.index') || request()->routeIs('parent.children.show') ? 'active' : '' }}">
    <i class="fas fa-users"></i> My Children
</a>
<a href="{{ route('parent.children.register') }}" class="menu-item {{ request()->routeIs('parent.children.register') ? 'active' : '' }}">
    <i class="fas fa-user-plus"></i> Register Child
</a>

<div class="menu-section-title">Academic</div>
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
<a href="{{ route('parent.materials.index') }}" class="menu-item {{ request()->routeIs('parent.materials.*') ? 'active' : '' }}">
    <i class="fas fa-book-open"></i> Study Materials
</a>

<div class="menu-section-title">Payments</div>
<a href="#" class="menu-item">
    <i class="fas fa-file-invoice-dollar"></i> Invoices
</a>
<a href="#" class="menu-item">
    <i class="fas fa-money-bill-wave"></i> Payment History
</a>

<div class="menu-section-title">Other</div>
<a href="#" class="menu-item">
    <i class="fas fa-bullhorn"></i> Announcements
</a>
<a href="#" class="menu-item">
    <i class="fas fa-file-alt"></i> Materials
</a>

<div class="menu-section-title">Account</div>
<a href="{{ route('profile.index') }}" class="menu-item {{ request()->routeIs('profile.*') ? 'active' : '' }}">
    <i class="fas fa-user"></i> My Profile
</a>
@endrole

{{-- Student Sidebar --}}
@role('student')
<div class="menu-section-title">Main</div>
<a href="{{ route('student.dashboard') }}" class="menu-item {{ request()->routeIs('student.dashboard') ? 'active' : '' }}">
    <i class="fas fa-home"></i> Dashboard
</a>

<div class="menu-section-title">Academic</div>
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

<div class="menu-section-title">Learning</div>
<a href="#" class="menu-item">
    <i class="fas fa-bullhorn"></i> Announcements
</a>
<a href="{{ route('student.materials.index') }}" class="menu-item {{ request()->routeIs('student.materials.*') ? 'active' : '' }}">
    <i class="fas fa-book-open"></i> Study Materials
</a>
<a href="#" class="menu-item">
    <i class="fas fa-calendar-alt"></i> My Schedule
</a>

<div class="menu-section-title">Account</div>
<a href="{{ route('profile.index') }}" class="menu-item {{ request()->routeIs('profile.*') ? 'active' : '' }}">
    <i class="fas fa-user"></i> My Profile
</a>
@endrole
