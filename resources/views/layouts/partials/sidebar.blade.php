{{-- Admin/Super Admin Sidebar --}}
@role('super-admin|admin')
<div class="menu-section-title">Main</div>
<a href="{{ route('admin.dashboard') }}" class="menu-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
    <i class="fas fa-home"></i> Dashboard
</a>

<div class="menu-section-title">User Management</div>
<a href="{{ route('admin.students.index') }}" class="menu-item {{ request()->routeIs('admin.students.*') ? 'active' : '' }}">
    <i class="fas fa-users"></i> Students
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

<div class="menu-section-title">Academic</div>
<a href="#" class="menu-item">
    <i class="fas fa-book"></i> Subjects
</a>
<a href="#" class="menu-item">
    <i class="fas fa-box"></i> Packages
</a>
<a href="#" class="menu-item">
    <i class="fas fa-school"></i> Classes
</a>
<a href="#" class="menu-item">
    <i class="fas fa-calendar-alt"></i> Timetable
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
<a href="#" class="menu-item">
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
    <i class="fas fa-chart-line"></i> Reports
</a>
<a href="#" class="menu-item">
    <i class="fas fa-cog"></i> Settings
</a>

<div class="menu-section-title">Account</div>
<a href="{{ route('profile.index') }}" class="menu-item {{ request()->routeIs('profile.*') ? 'active' : '' }}">
    <i class="fas fa-user"></i> My Profile
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
    <i class="fas fa-user-plus"></i> Register Student
</a>
<a href="{{ route('staff.registration.create-parent') }}" class="menu-item {{ request()->routeIs('staff.registration.create-parent') ? 'active' : '' }}">
    <i class="fas fa-user-plus"></i> Register Parent
</a>

<div class="menu-section-title">Students</div>
<a href="#" class="menu-item">
    <i class="fas fa-users"></i> All Students
</a>
<a href="#" class="menu-item">
    <i class="fas fa-clock"></i> Pending Approvals
</a>
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
<a href="#" class="menu-item">
    <i class="fas fa-calendar-alt"></i> Timetable
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

<div class="menu-section-title">My Classes</div>
<a href="#" class="menu-item">
    <i class="fas fa-school"></i> My Classes
</a>
<a href="#" class="menu-item">
    <i class="fas fa-calendar-alt"></i> My Schedule
</a>
<a href="#" class="menu-item">
    <i class="fas fa-users"></i> My Students
</a>

<div class="menu-section-title">Teaching</div>
<a href="#" class="menu-item">
    <i class="fas fa-check-square"></i> Attendance
</a>
<a href="#" class="menu-item">
    <i class="fas fa-file-upload"></i> Upload Materials
</a>
<a href="#" class="menu-item">
    <i class="fas fa-file-alt"></i> My Materials
</a>

<div class="menu-section-title">Assessment</div>
<a href="#" class="menu-item">
    <i class="fas fa-file-signature"></i> Exams
</a>
<a href="#" class="menu-item">
    <i class="fas fa-chart-bar"></i> Results
</a>

<div class="menu-section-title">Other</div>
<a href="#" class="menu-item">
    <i class="fas fa-bullhorn"></i> Announcements
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

<div class="menu-section-title">My Children</div>
<a href="#" class="menu-item">
    <i class="fas fa-child"></i> My Children
</a>
<a href="#" class="menu-item">
    <i class="fas fa-user-plus"></i> Register New Child
</a>

<div class="menu-section-title">Academic</div>
<a href="#" class="menu-item">
    <i class="fas fa-check-square"></i> Attendance
</a>
<a href="#" class="menu-item">
    <i class="fas fa-calendar-alt"></i> Timetable
</a>
<a href="#" class="menu-item">
    <i class="fas fa-file-alt"></i> Materials
</a>
<a href="#" class="menu-item">
    <i class="fas fa-chart-bar"></i> Results
</a>

<div class="menu-section-title">Financial</div>
<a href="#" class="menu-item">
    <i class="fas fa-file-invoice"></i> Invoices
</a>
<a href="#" class="menu-item">
    <i class="fas fa-money-bill-wave"></i> Payments
</a>
<a href="#" class="menu-item">
    <i class="fas fa-history"></i> Payment History
</a>

<div class="menu-section-title">Other</div>
<a href="#" class="menu-item">
    <i class="fas fa-calendar-check"></i> Seminars
</a>
<a href="#" class="menu-item">
    <i class="fas fa-bullhorn"></i> Announcements
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
<a href="#" class="menu-item">
    <i class="fas fa-calendar-alt"></i> My Timetable
</a>
<a href="#" class="menu-item">
    <i class="fas fa-check-square"></i> My Attendance
</a>

<div class="menu-section-title">Learning</div>
<a href="#" class="menu-item">
    <i class="fas fa-file-alt"></i> Study Materials
</a>
<a href="#" class="menu-item">
    <i class="fas fa-file-signature"></i> Exams
</a>
<a href="#" class="menu-item">
    <i class="fas fa-chart-bar"></i> My Results
</a>

<div class="menu-section-title">Financial</div>
<a href="#" class="menu-item">
    <i class="fas fa-file-invoice"></i> My Invoices
</a>
<a href="#" class="menu-item">
    <i class="fas fa-history"></i> Payment History
</a>

<div class="menu-section-title">Other</div>
<a href="#" class="menu-item">
    <i class="fas fa-calendar-check"></i> Seminars
</a>
<a href="#" class="menu-item">
    <i class="fas fa-bullhorn"></i> Announcements
</a>

<div class="menu-section-title">Account</div>
<a href="{{ route('profile.index') }}" class="menu-item {{ request()->routeIs('profile.*') ? 'active' : '' }}">
    <i class="fas fa-user"></i> My Profile
</a>
@endrole
