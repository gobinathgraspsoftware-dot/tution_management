@extends('layouts.app')

@section('title', 'Parent Dashboard')
@section('page-title', 'Parent Dashboard')

@section('content')
<div class="page-header">
    <h1>
        <i class="fas fa-user-friends me-2"></i> Parent Dashboard
    </h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item active">Dashboard</li>
        </ol>
    </nav>
</div>

<!-- Welcome Message -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0" style="background: linear-gradient(135deg, #fda530 0%, #4c4c4c 100%);">
            <div class="card-body text-white">
                <h4 class="mb-2">Welcome back, {{ auth()->user()->name }}!</h4>
                <p class="mb-0 opacity-75">Monitor your children's progress and stay updated with their learning journey.</p>
            </div>
        </div>
    </div>
</div>

<!-- My Children Overview -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0"><i class="fas fa-child me-2"></i> My Children</h5>
            @if(Route::has('parent.children.register'))
            <a href="{{ route('parent.children.register') }}" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i> Register New Child
            </a>
            @endif
        </div>
    </div>
    @forelse($children as $child)
        <div class="col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="user-avatar me-3" style="width: 60px; height: 60px; font-size: 1.5rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            {{ strtoupper(substr($child->user->name ?? 'S', 0, 1)) }}
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="mb-0">{{ $child->user->name ?? 'N/A' }}</h5>
                            <p class="text-muted mb-0">Student ID: {{ $child->student_id }}</p>
                            <p class="text-muted mb-0 small">
                                <i class="fas fa-envelope me-1"></i> {{ $child->user->email ?? 'N/A' }}
                            </p>
                        </div>
                        <span class="badge bg-{{ ($child->user->status ?? 'active') === 'active' ? 'success' : 'secondary' }}">
                            {{ ucfirst($child->user->status ?? 'Active') }}
                        </span>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <div class="p-2 bg-light rounded text-center">
                                <h6 class="mb-0">{{ $child->enrollments->count() }}</h6>
                                <small class="text-muted">Classes</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2 bg-light rounded text-center">
                                <h6 class="mb-0">{{ $child->attendance->where('status', 'present')->count() }}</h6>
                                <small class="text-muted">Attendance</small>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <h6 class="mb-2">Enrolled Classes:</h6>
                        @forelse($child->enrollments as $enrollment)
                            <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-light rounded">
                                <div>
                                    <small class="fw-bold">{{ $enrollment->package->name ?? 'N/A' }}</small>
                                    <br>
                                    <small class="text-muted">{{ $enrollment->class->name ?? 'Not assigned' }}</small>
                                </div>
                                <span class="badge bg-{{ $enrollment->status === 'active' ? 'primary' : 'secondary' }}">
                                    {{ ucfirst($enrollment->status) }}
                                </span>
                            </div>
                        @empty
                            <p class="text-muted small mb-0">No active enrollments</p>
                        @endforelse
                    </div>

                    <div class="d-grid gap-2 d-md-flex">
                        {{-- FIXED: Attendance button - was href="#" --}}
                        @if(Route::has('parent.attendance.child'))
                        <a href="{{ route('parent.attendance.child', $child) }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-calendar-check me-1"></i> Attendance
                        </a>
                        @endif

                        {{-- FIXED: Invoices button - was href="#" --}}
                        @if(Route::has('parent.invoices.index'))
                        <a href="{{ route('parent.invoices.index') }}" class="btn btn-sm btn-success">
                            <i class="fas fa-file-invoice me-1"></i> Invoices
                        </a>
                        @endif

                        {{-- FIXED: View Profile button - was Progress with href="#" --}}
                        @if(Route::has('parent.children.show'))
                        <a href="{{ route('parent.children.show', $child) }}" class="btn btn-sm btn-info">
                            <i class="fas fa-eye me-1"></i> View Profile
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                No children registered yet.
                @if(Route::has('parent.children.register'))
                <a href="{{ route('parent.children.register') }}" class="alert-link">Register your child</a> to get started.
                @else
                Please register your child to get started.
                @endif
            </div>
        </div>
    @endforelse
</div>

<!-- Financial Overview -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                <span><i class="fas fa-file-invoice-dollar me-2"></i> Pending Invoices</span>
                <span class="badge bg-white text-danger">{{ $pending_invoices->count() }}</span>
            </div>
            <div class="card-body">
                @forelse($pending_invoices as $invoice)
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                        <div class="flex-grow-1">
                            <h6 class="mb-1">{{ $invoice->student->user->name ?? 'Unknown' }}</h6>
                            <small class="text-muted">
                                Invoice #{{ $invoice->invoice_number }} • Due: {{ $invoice->due_date->format('d M Y') }}
                            </small>
                            @if($invoice->due_date->isPast())
                                <br><span class="badge bg-danger small">Overdue</span>
                            @endif
                        </div>
                        <div class="text-end">
                            <h6 class="mb-0 text-danger">RM {{ number_format($invoice->balance ?? ($invoice->total_amount - $invoice->paid_amount), 2) }}</h6>
                            {{-- FIXED: Pay Now button - was href="#" --}}
                            @if(Route::has('parent.payments.pay-online'))
                            <a href="{{ route('parent.payments.pay-online', $invoice) }}" class="btn btn-sm btn-success mt-1">
                                <i class="fas fa-credit-card"></i> Pay Now
                            </a>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center text-success py-4">
                        <i class="fas fa-check-circle fa-3x mb-2"></i>
                        <p class="mb-0">All invoices paid! Great job!</p>
                    </div>
                @endforelse

                {{-- View All Outstanding Link --}}
                @if($pending_invoices->count() > 0 && Route::has('parent.payments.outstanding'))
                <div class="text-center mt-2">
                    <a href="{{ route('parent.payments.outstanding') }}" class="btn btn-sm btn-outline-danger">
                        View All Outstanding
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-history me-2"></i> Recent Payments</span>
                {{-- FIXED: View All link - was href="#" --}}
                @if(Route::has('parent.payments.history'))
                <a href="{{ route('parent.payments.history') }}" class="btn btn-sm btn-outline-primary">View All</a>
                @endif
            </div>
            <div class="card-body">
                @forelse($recent_payments as $payment)
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                        <div class="flex-grow-1">
                            <h6 class="mb-1">{{ $payment->student->user->name ?? 'Unknown' }}</h6>
                            <small class="text-muted">
                                {{ $payment->payment_date->format('d M Y, h:i A') }}
                            </small>
                            <br>
                            <span class="badge bg-success small">{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</span>
                        </div>
                        <div class="text-end">
                            <h6 class="mb-0 text-success">RM {{ number_format($payment->amount, 2) }}</h6>
                            {{-- FIXED: Receipt button - was href="#" --}}
                            @if(Route::has('parent.payments.receipt'))
                            <a href="{{ route('parent.payments.receipt', $payment) }}" class="btn btn-sm btn-outline-primary mt-1" target="_blank">
                                <i class="fas fa-receipt"></i> Receipt
                            </a>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-muted text-center py-4">No payment history yet</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-bolt me-2"></i> Quick Actions
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        {{-- FIXED: Make Payment - was href="#" --}}
                        @if(Route::has('parent.payments.pay-online'))
                        <a href="{{ route('parent.payments.pay-online') }}" class="btn btn-primary w-100">
                            <i class="fas fa-credit-card me-2"></i> Make Payment
                        </a>
                        @else
                        <button class="btn btn-primary w-100" disabled>
                            <i class="fas fa-credit-card me-2"></i> Make Payment
                        </button>
                        @endif
                    </div>
                    <div class="col-md-3">
                        {{-- FIXED: View Attendance - was href="#" --}}
                        @if(Route::has('parent.attendance.index'))
                        <a href="{{ route('parent.attendance.index') }}" class="btn btn-success w-100">
                            <i class="fas fa-calendar-check me-2"></i> View Attendance
                        </a>
                        @else
                        <button class="btn btn-success w-100" disabled>
                            <i class="fas fa-calendar-check me-2"></i> View Attendance
                        </button>
                        @endif
                    </div>
                    <div class="col-md-3">
                        {{-- FIXED: Learning Materials - was href="#" --}}
                        @if(Route::has('parent.materials.index'))
                        <a href="{{ route('parent.materials.index') }}" class="btn btn-info w-100">
                            <i class="fas fa-book me-2"></i> Learning Materials
                        </a>
                        @else
                        <button class="btn btn-info w-100" disabled>
                            <i class="fas fa-book me-2"></i> Learning Materials
                        </button>
                        @endif
                    </div>
                    <div class="col-md-3">
                        {{-- FIXED: Changed from "Contact Teacher" to Announcements - was href="#" --}}
                        @if(Route::has('parent.announcements.index'))
                        <a href="{{ route('parent.announcements.index') }}" class="btn btn-warning w-100">
                            <i class="fas fa-bullhorn me-2"></i> Announcements
                        </a>
                        @else
                        <button class="btn btn-warning w-100" disabled>
                            <i class="fas fa-bullhorn me-2"></i> Announcements
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Announcements & Events -->
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-bullhorn me-2"></i> Latest Announcements</span>
                {{-- FIXED: View All Announcements --}}
                @if(Route::has('parent.announcements.index'))
                <a href="{{ route('parent.announcements.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                @endif
            </div>
            <div class="card-body">
                @if(isset($announcements) && $announcements->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($announcements->take(3) as $announcement)
                        <div class="list-group-item px-0">
                            <div class="d-flex justify-content-between">
                                <h6 class="mb-1">
                                    @if($announcement->priority === 'urgent')
                                    <span class="badge bg-danger me-1">Urgent</span>
                                    @elseif($announcement->priority === 'high')
                                    <span class="badge bg-warning text-dark me-1">Important</span>
                                    @endif
                                    {{ Str::limit($announcement->title, 35) }}
                                </h6>
                                <small class="text-muted">{{ $announcement->created_at->diffForHumans() }}</small>
                            </div>
                            <p class="mb-1 small text-muted">{{ Str::limit(strip_tags($announcement->content), 80) }}</p>
                            {{-- FIXED: Read More button - was href="#" --}}
                            @if(Route::has('parent.announcements.show'))
                            <a href="{{ route('parent.announcements.show', $announcement) }}" class="btn btn-sm btn-outline-primary">Read More</a>
                            @endif
                        </div>
                        @endforeach
                    </div>
                @else
                    {{-- Placeholder when no announcements variable passed --}}
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-bullhorn fa-2x mb-2"></i>
                        <p class="mb-0">No announcements yet.</p>
                        @if(Route::has('parent.announcements.index'))
                        <a href="{{ route('parent.announcements.index') }}" class="btn btn-sm btn-outline-primary mt-2">
                            Check Announcements
                        </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-calendar-alt me-2"></i> Upcoming Schedule</span>
                {{-- FIXED: Timetable link --}}
                @if(Route::has('timetable.index'))
                <a href="{{ route('timetable.index') }}" class="btn btn-sm btn-outline-primary">View Timetable</a>
                @endif
            </div>
            <div class="card-body">
                @if(isset($upcoming_classes) && count($upcoming_classes) > 0)
                    <div class="list-group list-group-flush">
                        @foreach($upcoming_classes as $schedule)
                        <div class="list-group-item px-0">
                            <div class="d-flex">
                                <div class="me-3">
                                    <div class="text-center p-2 rounded" style="background-color: #e3f2fd; min-width: 60px;">
                                        <div style="font-size: 1.2rem; font-weight: bold; color: #2196f3;">
                                            {{ $schedule['time'] ?? '09:00' }}
                                        </div>
                                        <div style="font-size: 0.75rem; color: #666;">
                                            {{ $schedule['day'] ?? 'MON' }}
                                        </div>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">{{ $schedule['class_name'] ?? 'Class' }}</h6>
                                    <p class="mb-0 small text-muted">
                                        {{ $schedule['subject'] ?? 'Subject' }} • {{ $schedule['teacher'] ?? 'Teacher' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    {{-- Default placeholder --}}
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-calendar-alt fa-2x mb-2"></i>
                        <p class="mb-0">No upcoming classes scheduled.</p>
                        @if(Route::has('timetable.index'))
                        <a href="{{ route('timetable.index') }}" class="btn btn-sm btn-outline-primary mt-2">
                            View Full Timetable
                        </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
